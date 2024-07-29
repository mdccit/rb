<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CreateNewModulePack extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:new-module-pack {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Init Command constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filesystem = new Filesystem();
        $moduleName = $this->argument('name');
        $modulePath = app_path("Modules/{$moduleName}");

        if ($filesystem->exists($modulePath)) {
            $this->error("Module {$moduleName} already exists!");
            return;
        }

        $filesystem->makeDirectory($modulePath, 0755, true, true);
        $filesystem->makeDirectory("{$modulePath}/Controllers", 0755, true, true);
//        $filesystem->makeDirectory("{$modulePath}/Models", 0755, true, true);
        $filesystem->makeDirectory("{$modulePath}/Views", 0755, true, true);
        $filesystem->makeDirectory("{$modulePath}/lang/en", 0755, true, true);
        $filesystem->makeDirectory("{$modulePath}/lang/es", 0755, true, true);

        $routeContent = '<?php
        
use Illuminate\Support\Facades\Route;';
        $filesystem->put("{$modulePath}/routes.php", $routeContent, false);

        $this->info("Module {$moduleName} created successfully!");
    }
}
