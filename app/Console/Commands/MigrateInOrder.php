<?php

namespace App\Console\Commands;

use Database\Seeders\ConferenceSeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\DivisionSeeder;
use Database\Seeders\NationalitySeeder;
use Database\Seeders\PlayerBudgetSeeder;
use Database\Seeders\SportSeeder;
use Database\Seeders\UserRoleSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\UserTypeSeeder;
use Database\Seeders\MediaInformationSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
            '2024_08_01_141538_create_countries_table.php',
            '2024_08_06_045702_create_nationalities_table.php',
            '2024_08_06_050114_create_currencies_table.php',
            '2014_10_12_000000_create_users_table.php',
            '2024_08_12_065634_create_user_sessions_table.php',
            '2024_08_05_143549_create_user_addresses_table.php',
            '2024_08_05_143620_create_user_phones_table.php',
            '2014_10_12_100000_create_password_reset_tokens_table.php',
            '2016_06_01_000001_create_oauth_auth_codes_table.php',
            '2016_06_01_000002_create_oauth_access_tokens_table.php',
            '2016_06_01_000003_create_oauth_refresh_tokens_table.php',
            '2016_06_01_000004_create_oauth_clients_table.php',
            '2016_06_01_000005_create_oauth_personal_access_clients_table.php',
            '2019_08_19_000000_create_failed_jobs_table.php',
            '2019_12_14_000001_create_personal_access_tokens_table.php',
            '2024_07_30_112343_alter_user_table_relevant_with_socialite.php',
            '2024_08_08_101927_create_password_resets_table.php',
            '2024_08_01_133328_create_player_parents_table.php',
            '2024_08_06_045014_create_player_budgets_table.php',
            '2024_08_01_055112_create_players_table.php',
            '2024_08_15_132902_create_conferences_table.php',
            '2024_08_15_133010_create_divisions_table.php',
            '2024_08_05_162756_create_schools_table.php',
            '2024_08_15_150542_create_school_users_table.php',
            '2024_08_05_162815_create_businesses_table.php',
            '2024_08_05_162739_create_coaches_table.php',
            '2024_08_05_162830_create_business_managers_table.php',
            '2024_08_13_065442_create_resource_categories_table.php',
            '2024_08_13_100733_create_resources_table.php',
            '2024_09_04_064002_create_connection_requests_table.php',
            '2024_08_21_051101_create_conversations_table.php',
            '2024_08_20_061356_create_chat_messages_table.php',
            '2024_08_14_070409_create_transfer_players_table.php',
            '2024_08_13_070939_create_posts_table.php',
            '2024_08_14_041548_create_comments_table.php',
            '2024_08_14_041613_create_likes_table.php',
            '2024_08_28_062152_create_sync_logs_table.php',
            '2024_08_31_041541_create_sync_settings_table.php',
            '2024_08_30_064700_create_moderation_requests_table.php',
            '2024_08_30_064727_create_moderation_comments_table.php',
            '2024_08_30_105500_add_slug_column_to_users_table.php',
            '2024_09_03_111301_add_slug_column_to_schools_table.php',
            '2024_09_04_035811_add_slug_column_to_businesses_table.php',
            '2024_09_04_050449_add_preferred_gender_type_column_to_coaches_table.php',
            '2024_09_06_052537_create_moderation_logs_table.php',
            '2024_09_04_100828_create_recent_searches_table.php',
            '2024_09_04_101938_create_save_searches_table.php',
            '2024_09_06_052537_create_moderation_logs_table.php',
            '2024_09_15_153755_create_school_teams_table.php',
            '2024_09_20_153806_create_school_team_users_table.php',
            '2024_09_19_100207_create_sports_table.php',
            '2024_09_19_100453_add_new_sport_id_column_to_players_table.php',
            '2024_09_19_103015_add_new_sport_id_column_to_coaches_table.php',
            '2024_09_16_200041_create_media_information_table.php',
            '2024_09_16_200133_create_media_table.php',
            '2024_10_11_002816_create_transcripts_table.php',
            '2024_09_20_063604_exist_school_team_users_table.php',
            '2024_09_20_153806_create_school_team_users_table.php',
            '2024_10_01_233654_create_subscriptions_table.php',
            '2024_10_15_030838_add_other_data_column_to_transfer_player_table.php',

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
        $this->call(CountrySeeder::class);
        $this->call(NationalitySeeder::class);
        $this->call(CurrencySeeder::class);
        $this->call(PlayerBudgetSeeder::class);
        $this->call(ConferenceSeeder::class);
        $this->call(DivisionSeeder::class);
        $this->call(SyncSettingSeeder::class);

        $this->call(UserSeeder::class);
        $this->call(SportSeeder::class);
        $this->call(MediaInformationSeeder::class);

        //Passport Config
        //Create personal client for 'RecruitedProV2'
        //php artisan passport:client --personal

        $this->call('passport:client', [
            '--name' => config('app.name') ,
            '--personal' => null
        ]);
    }
}
