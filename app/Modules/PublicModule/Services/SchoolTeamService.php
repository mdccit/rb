<?php


namespace App\Modules\PublicModule\Services;


use App\Models\School;
use App\Models\SchoolTeam;
use App\Models\SchoolTeamUser;

class SchoolTeamService
{
    public function getSchoolTeam ($school_id){

        $team = SchoolTeam::connect(config('database.secondary'))
                 ->join('schools','schools.id','=','school_teams.school_id')
                 ->where('school_id',$school_id)
                 ->select(
                    'school_teams.name',
                    'school_teams.school_id',
                    'schools.name'
                  )
                  ->first();
       

        return [
                'team' => $team
              ];

    }

    public function getSchoolTeamInfo ($team_id){

        $team = SchoolTeam::connect(config('database.secondary'))
                 ->join('schools','schools.id','=','school_teams.school_id')
                 ->where('id',$team_id)
                 ->select(
                    'school_teams.name',
                    'school_teams.school_id',
                    'schools.name'
                  )
                  ->first();
        $team_users = SchoolTeam::connect(config('database.secondary'))
                    ->join('users','users.id','=','school_team_users.user_id')
                    ->leftJoin('players','users.id','=','school_team_users.player_id')
                    ->leftJoin('coaches','users.id','=','school_team_users.coache_id')
                    ->where('team_id',$team_id)
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
                        'school_id' => $data['schoolId'],
                    ]);
        foreach( $data['team_user'] as $team_user){
            SchoolTeamUser::connect(config('database.default'))
                ->create([
                    'team_id' => $team->id,
                    'user_id' => $team_user->userId,
                    'status'  => $team_user->status,
                    'player_id' => $team_user->playerId ?? null,
                    'coache_id' => $team_user->playerId ?? null
                ]);
        }
    }

    public function updateSchoolTeam (array $data, $team_id){
       
    }

    public function  destroy($team_id){

        SchoolTeam::connect(config('database.default'))->destroy($team_id);
    }
}
