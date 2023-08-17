<?php

namespace ITHilbert\Module\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use ITHilbert\Module\Classes\Stub;

class CreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:create {modulName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Erstellt ein neues Modul';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $modulName = $this->argument('modulName') ?? '';
        //prüfen ob $moduleName leer ist
        if ($modulName == '') {
            $this->error('Es wurde kein Modulname angegeben!');
            return;
        }

        //modulname in kleinbuchstaben umwandeln
        $modulName = strtolower($modulName);


        //prüfen ob der ordner "module" existiert, sonst anlegen
        if (!File::exists(base_path('module'))) {
            File::makeDirectory(base_path('module'));
        }

        //prüfen ob es bereits ein modul mit dem Namen gibt
        if (File::exists(base_path('module/'.$modulName))) {
            $this->error('Es existiert bereits ein Modul mit dem Namen '.$modulName.'!');
            return;
        }

        //Modulname setzen
        Cache::put('active_modul', $modulName, now()->addDay());

        //Ordner anlegen
        $this->createFolder($modulName);

        //ServiceProvider anlegen
        $this->createServiceProvider($modulName);

        //Config anlegen
        $this->createConfig($modulName);

        //Controller anlegen
        $this->createController($modulName);

        //Routes anlegen
        $this->createRoutes();

        //Models anlegen
        $this->createModels($modulName);

        //Routes anlegen
        $this->createRoutes($modulName);

        //Gitingore anlegen
        $this->createGitingore($modulName);

        //Verzeichnisse überwachen mit npm
        $this->call('module:mix');

        $this->info("Modul wurde angelegt. Vergessen Sie nicht es in der config/app.php und in der composer.json einzutragen!");
    }

    private function createConfig($modulName)
    {
        $stub = new Stub('config');
        $stub->saveAsConfig($modulName);
    }


    private function createController($modulName)
    {
        $stub = new Stub('controller');
        $stub->saveAsController($modulName);
    }

    private function createRoutes()
    {
        $stub = new Stub('web');
        $stub->saveAsRoute('web');
    }

    private function createModels($modulName)
    {
        $stub = new Stub('model');
        $stub->setDummyName($modulName);
        $stub->saveAsModel($modulName);
    }

    private function createServiceProvider($modulName)
    {
        $stub = new Stub('serviceProvider');
        $stub->saveAsServiceProvider($modulName);
    }


    private function createFolder($modulName)
    {
        $pfadModul = base_path('module/'.$modulName .'/');

        //ordner für das modul anlegen
        File::makeDirectory($pfadModul);

        //Config
        File::makeDirectory($pfadModul .'Config');
        //Controller
        File::makeDirectory($pfadModul .'Controllers');
        //Database
        File::makeDirectory($pfadModul .'Database');
        File::makeDirectory($pfadModul .'Database/Migrations');
        File::makeDirectory($pfadModul .'Database/Seeders');
        //Models
        File::makeDirectory($pfadModul .'Models');
        //Public
        File::makeDirectory($pfadModul .'Public');
        File::makeDirectory($pfadModul .'Public/css');
        File::makeDirectory($pfadModul .'Public/js');
        File::makeDirectory($pfadModul .'Public/images');
        //Resources
        File::makeDirectory($pfadModul .'Resources');
        File::makeDirectory($pfadModul .'Resources/lang');
        File::makeDirectory($pfadModul .'Resources/lang/de');
        File::makeDirectory($pfadModul .'Resources/lang/en');
        File::makeDirectory($pfadModul .'Resources/views');
        //Routes
        File::makeDirectory($pfadModul .'Routes');
    }




    private function createGitingore($modulName)
    {
        //Database
        file_put_contents(base_path('module/'.$modulName.'/Database/Migrations/.gitignore'),'');
        file_put_contents(base_path('module/'.$modulName.'/Database/Seeders/.gitignore'),'');
        //Public
        file_put_contents(base_path('module/'.$modulName.'/Public/css/.gitignore'),'');
        file_put_contents(base_path('module/'.$modulName.'/Public/js/.gitignore'),'');
        file_put_contents(base_path('module/'.$modulName.'/Public/images/.gitignore'),'');
        //Resources
        file_put_contents(base_path('module/'.$modulName.'/Resources/lang/de/.gitignore'),'');
        file_put_contents(base_path('module/'.$modulName.'/Resources/lang/en/.gitignore'),'');
        file_put_contents(base_path('module/'.$modulName.'/Resources/views/.gitignore'),'');
    }

}
