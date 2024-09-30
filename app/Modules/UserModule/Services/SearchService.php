<?php


namespace App\Modules\UserModule\Services;


use App\Models\BusinessManager;
use App\Models\Player;
use App\Models\User;
use App\Models\School;
use App\Models\RecentSearch;
use App\Models\SaveSearch;
use App\Traits\AzureBlobStorage;

use App\Models\ConnectionRequest;
use DB;
class SearchService
{
    use AzureBlobStorage;

    public function search (array $data){
        $per_page_items = array_key_exists("per_page_items",$data)?$data['per_page_items']:0;
        $user_role = array_key_exists("user_role",$data)?$data['user_role']:0;
        $search_key = array_key_exists("search_key",$data)?$data['search_key']:null;

        $state = array_key_exists("state",$data)?$data['state']:null;
        $city = array_key_exists("city",$data)?$data['city']:null;
        $tuition_in_state_min =array_key_exists("tuition_in_state_min",$data)?$data['tuition_in_state_min']:null;
        $tuition_in_state_max =array_key_exists("tuition_in_state_max",$data)?$data['tuition_in_state_max']:null;
        $tuition_out_state_min =array_key_exists("tuition_out_state_min",$data)?$data['tuition_out_state_min']:null;
        $tuition_out_state_max =array_key_exists("tuition_out_state_max",$data)?$data['tuition_out_state_max']:null;

        $gender = array_key_exists("gender",$data)?$data['gender']:null;
        $graduation_month = array_key_exists("graduation_month",$data)?$data['graduation_month']:null;
        $graduation_year = array_key_exists("graduation_year",$data)?$data['graduation_year']:null;
        $country_id = array_key_exists("country_id",$data)?$data['country_id']:null;
        $handedness = array_key_exists("handedness",$data)?$data['handedness']:null;
        $utr_min =array_key_exists("utr_min",$data)?$data['utr_min']:null;
        $utr_max =array_key_exists("utr_max",$data)?$data['utr_max']:null;
        $wtn_min =array_key_exists("wtn_min",$data)?$data['wtn_min']:null;
        $wtn_max =array_key_exists("wtn_max",$data)?$data['wtn_max']:null;
        $atp_ranking =array_key_exists("atp_ranking",$data)?$data['atp_ranking']:null;
        $itf_ranking =array_key_exists("itf_ranking",$data)?$data['itf_ranking']:null;
        $national_ranking =array_key_exists("national_ranking",$data)?$data['national_ranking']:null;
        $type =array_key_exists("type",$data)?$data['type']:null;

        $dataSet= [];
        if($type != 'school'){
            $query = User::connect(config('database.secondary'))
            ->join('user_roles', 'user_roles.id', '=' ,'users.user_role_id')
            ->join('user_types', 'user_types.id', '=' ,'users.user_type_id')
            ->leftJoin('players','players.user_id','=','users.id')
            ->leftJoin('user_addresses','user_addresses.user_id','=','users.id')
            ->leftJoin('countries','countries.id','=','user_addresses.country_id')
            ->where('users.id', '!=', auth()->user()->id)
            ->where('users.user_role_id', '!=', config('app.user_roles.default'))
            ->where('users.user_role_id', '!=', config('app.user_roles.admin'))
            ->select(
                'users.id',
                'users.id as userId',
                'users.first_name',
                'users.last_name',
                'users.display_name',
                'users.slug as slug',
                'users.bio as bio',
                'users.email',
                'users.slug',
                'user_roles.name as user_role',
                'user_types.name as user_type',
                'users.created_at as joined_at',
                'users.last_logged_at as last_seen_at',
                'players.*',
                'user_addresses.*',
                'countries.name as country'
            );

            if($user_role != 0){
                $query->where('users.user_role_id', $user_role);
            }

            if ($search_key != null) {
                $query->where('users.display_name', 'LIKE', '%' . $search_key . '%');
            }

            if($country_id != null){
                $query->where('user_addresses.country_id', $country_id);
            }

            if($graduation_month != null){
                $query->whereMonth('players.graduation_month_year', $graduation_month);
            }

            if($graduation_year != null){
                $query->whereMonth('players.graduation_year', $graduation_year);
            }
        
            if($gender != null){
                $query->where('users.gender', $gender);
            }

            if($handedness != null){
                $query->whereJsonContains('players.other_data->handedness', $handedness);
            }

            if($utr_min != null){
                $query->whereRaw(
                    'CAST(JSON_UNQUOTE(JSON_EXTRACT(players.other_data, "$.utr")) AS DECIMAL(10, 2)) >= ?',
                    [$utr_min]
                );
           }

            if($utr_max != null){
                $query->whereRaw(
                    'CAST(JSON_UNQUOTE(JSON_EXTRACT(players.other_data, "$.utr")) AS DECIMAL(10, 2)) <= ?',
                    [$utr_max]
                );
            }

            if($wtn_min != null){
                $query->whereRaw(
                    'CAST(JSON_UNQUOTE(JSON_EXTRACT(players.other_data, "$.wtn_score_manual")) AS DECIMAL(10, 2)) >= ?',
                    [$wtn_min]
                );
            }

            if($wtn_max != null){
                $query->whereRaw(
                    'CAST(JSON_UNQUOTE(JSON_EXTRACT(players.other_data, "$.wtn_score_manual")) AS DECIMAL(10, 2)) <= ?',
                    [$wtn_max]
                );
            }

            if($atp_ranking != null){
                $query->whereJsonContains('players.other_data->atp_ranking', $atp_ranking);

            }

            if($itf_ranking != null){
                $query->whereJsonContains('players.other_data->itf_ranking', $itf_ranking);

            }

            if($national_ranking != null){
                $query->whereJsonContains('players.other_data->national_ranking', $$national_ranking);
            }

    
            $dataSet = $query->get();

            foreach( $dataSet as $key=> $data){
                $profile_picture = $this->getSingleFileByEntityId($data->userId,'user_profile_picture');
                $dataSet[$key]['profile_picture'] =$profile_picture;
            }
        }

        $schoolDataSet =[];
        if($type != 'user'){
            $school_query = School::connect(config('database.secondary'))
                                ->select(
                                    'id',
                                    'name',
                                    'bio',
                                    'other_data',
                                    'created_at'
                                );
            if ($search_key != null) {
                $school_query->where('name', 'LIKE', '%' . $search_key . '%');
            }

            if($city != null){
                $school_query->where('other_data->city',$city);
            }

            if($state != null){
                $school_query->where('other_data->state', $state);
            }

            if($tuition_in_state_min != null){
                $school_query->where('other_data->tuition_in_state','>=',$tuition_in_state_min); 
            }

            if($tuition_in_state_max != null){
                $school_query->where('other_data->tuition_in_state','<=',$tuition_in_state_max); 
            }

            if($tuition_out_state_min != null){
                $school_query->where('other_data->tuition_out_state','>=',$tuition_out_state_min); 
            }

            if($tuition_out_state_max != null){
                $school_query->where('other_data->tuition_out_state','<=',$tuition_out_state_max); 
            }
            
            $schoolDataSet =  $school_query->get();
        }

       if($search_key != null){
           RecentSearch::connect(config('database.default'))
                ->create([
                    'user_id' => auth()->id(),
                    'name' => $search_key
               ]);
       }
       $authId = auth()->id();
       $connectionList = ConnectionRequest::connect(config('database.secondary'))
                        ->where(function ($query) {
                             $query->where('sender_id', auth()->id())
                             ->orWhere('receiver_id', auth()->id());
                          })

                        ->whereIn('connection_status', ['accepted', 'pending'])
                        ->select(DB::raw("IF(sender_id = '$authId', receiver_id, sender_id) as user_id"), 'connection_status','id')
                        ->get();
        return [
            'users' => $dataSet,
            'school' =>  $schoolDataSet,
            'connections' => $connectionList
        ];

    }


    public function getRecentSearch(){

        return RecentSearch::connect(config('database.secondary'))
                ->where('user_id',auth()->id())
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();
    }


    public function saveSearch(array $data){
        SaveSearch::connect(config('database.default'))
            ->create([
                'user_id' => auth()->id(),
                'name' => $data['name'],
                'search_data' => $data['search_data']
           ]);
    }

    public function getSaveSearch(){
        return SaveSearch::connect(config('database.secondary'))
                 ->where('user_id',auth()->id())
                 ->get();
    }

    public function deleteSaveSearch($search_id){
        SaveSearch::connect(config('database.default'))->destroy($search_id);
    }

    
   
    

}
