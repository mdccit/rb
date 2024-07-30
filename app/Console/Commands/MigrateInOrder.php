<?php

namespace App\Console\Commands;

use Database\Seeders\UserRoleSeeder;
use Database\Seeders\UserTypeSeeder;
use Illuminate\Console\Command;

class MigrateInOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate_in_order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute the migrations in the order specified in the file app/Console/Commands/MigrateInOrder.php \n Drop all the table in db before execute the command.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /** Specify the names of the migrations files in the order you want to
         * loaded
         * $migrations =[
         *               'xxxx_xx_xx_000000_create_nameTable_table.php',
         *    ];
         */
        $migrations = [
            '2024_07_19_105012_create_user_roles_table.php',
            '2024_07_19_110343_create_user_types_table.php',
            '2014_10_12_000000_create_users_table.php',
            '2014_10_12_100000_create_password_reset_tokens_table.php',
            '2016_06_01_000001_create_oauth_auth_codes_table.php',
            '2016_06_01_000002_create_oauth_access_tokens_table.php',
            '2016_06_01_000003_create_oauth_refresh_tokens_table.php',
            '2016_06_01_000004_create_oauth_clients_table.php',
            '2016_06_01_000005_create_oauth_personal_access_clients_table.php',
            '2019_08_19_000000_create_failed_jobs_table.php',
            '2019_12_14_000001_create_personal_access_tokens_table.php',
            '2024_07_30_112343_alter_user_table_relevant_with_socialite.php',
        ];

        foreach($migrations as $migration)
        {
            $basePath = 'database/migrations/';
            $migrationName = trim($migration);
            $path = $basePath.$migrationName;
            $this->call('migrate', [
                '--path' => $path ,
            ]);
        }

        //Execute Seeders
        $this->call(UserRoleSeeder::class);
        $this->call(UserTypeSeeder::class);

        //Passport Config
        //Create personal client for 'RecruitedProV2'
        //php artisan passport:client --personal

        $this->call('passport:client', [
            '--name' => config('app.name') ,
            '--personal' => null
        ]);
    }
}
