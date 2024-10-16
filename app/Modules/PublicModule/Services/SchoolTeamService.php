<?php


namespace App\Modules\PublicModule\Services;


use App\Models\School;
use App\Models\SchoolTeam;
use App\Models\SchoolTeamUser;

class SchoolTeamService
{
    public function getSchoolTeam ($school_id){

        $teams = SchoolTeam::connect(config('database.secondary'))
                 ->join('schools','schools.id','=','school_teams.school_id')
                 ->where('school_id',$school_id)
                 ->select(
                    'school_teams.id as team_id',
                    'school_teams.name as team_name',
                    'school_teams.school_id',
                    'schools.name as school_name'
                  )
                  ->get();
        
        foreach( $teams as $key=> $data){
            $team_users = SchoolTeamUser::connect(config('database.secondary'))
                          ->where('school_team_users.team_id',$data->team_id)
                          ->join('users','users.id','=','school_team_users.user_id')
                          ->select(
                            'school_team_users.id as id',
                            'school_team_users.user_id as user_id',
                            'school_team_users.status',
                            'school_team_users.player_id',
                            'school_team_users.coache_id',
                            'users.display_name as name',
                            'users.user_role_id as role_id'
                          )
                         ->get();
            $teams[$key]['team_users'] =$team_users;
        }
       

        return [
                'team' => $teams
              ];

    }

    public function getSchoolTeamInfo ($team_id){

        $team = SchoolTeam::connect(config('database.secondary'))
                 ->join('schools','schools.id','=','school_teams.school_id')
                 ->where('school_teams.id','=',$team_id)
                 ->select(
                    'school_teams.id as id',
                    'school_teams.name as team',
                    'school_teams.school_id',
                    'schools.name'
                  )
                  ->first();

        $team_users = SchoolTeamUser::connect(config('database.secondary'))
                      ->where('school_team_users.team_id',$team_id)
                      ->get();

        return [
                'team_info' => $team,
                'team_users_info' => $team_users,
              ];

    }


    public function createSchoolTeam (array $data){
        $team = SchoolTeam::connect(config('database.default'))
                    ->create([
                        'name' => $data['name'],
                        'school_id' => $data['school_id'],
                    ]);

        foreach( $data['team_user'] as $team_user){
            SchoolTeamUser::connect(config('database.default'))
                ->create([
                    'team_id' => $team->id,
                    'user_id' => $team_user['user_id'],
                    'status'  => $team_user['status'],
                    'player_id' => $team_user['player_id'] ?? null,
                    'coache_id' => $team_user['coache_id'] ?? null
                ]);
        }
    }

    public function updateSchoolTeam (array $data, $team_id){
       
    }

    public function  destroy($team_id){

        SchoolTeam::connect(config('database.default'))->destroy($team_id);
    }
}
