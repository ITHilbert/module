<?php

namespace ITHilbert\Module\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class Create extends Command
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

        //Ordner anlegen
        $this->createFolder($modulName);

        //ServiceProvider anlegen
        $this->createServiceProvider($modulName);

        //Config anlegen
        $this->createConfig($modulName);

        //Controller anlegen
        $this->createController($modulName);

        //Routes anlegen
        $this->createRoutes($modulName);

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
        // Stub-Datei laden und variablen ersetzen
        $stub = File::get(__DIR__.'/../Stubs/config.stub');
        $stub = $this->replaceStub($modulName, $stub);

        //ServiceProvider anlegen
        $pfad = base_path('module/'.$modulName.'/Config/'.$modulName.'.php');
        File::put($pfad, $stub);
    }


    private function createController($modulName)
    {
        //Modulname mit ersten Buchstaben groß
        $modulNameGross = ucfirst($modulName);

        // Stub-Datei laden und variablen ersetzen
        $stub = File::get(__DIR__.'/../Stubs/Controller.stub');
        $stub = $this->replaceStub($modulName, $stub);

        //ServiceProvider anlegen
        $pfad = base_path('module/'.$modulName.'/Controllers/'.$modulNameGross.'Controller.php');
        File::put($pfad, $stub);
    }

    private function createRoutes($modulName)
    {
        // Stub-Datei laden und variablen ersetzen
        $stub = File::get(__DIR__.'/../Stubs/web.stub');
        $stub = $this->replaceStub($modulName, $stub);

        //ServiceProvider anlegen
        $pfad = base_path('module/'.$modulName.'/Routes/web.php');
        File::put($pfad, $stub);
    }

    private function createModels($modulName)
    {
        //Modulname mit ersten Buchstaben groß
        $modulNameGross = ucfirst($modulName);

        // Stub-Datei laden und variablen ersetzen
        $stub = File::get(__DIR__.'/../Stubs/Model.stub');
        $stub = $this->replaceStub($modulName, $stub);

        //ServiceProvider anlegen
        $pfad = base_path('module/'.$modulName.'/Models/'.$modulNameGross.'.php');
        File::put($pfad, $stub);
    }



    private function createServiceProvider($modulName)
    {
        //Modulname mit ersten Buchstaben groß
        $modulNameGross = ucfirst($modulName);

        // Stub-Datei laden und variablen ersetzen
        $stub = File::get(__DIR__.'/../Stubs/ServiceProvider.stub');
        $stub = $this->replaceStub($modulName, $stub);

        //ServiceProvider anlegen
        $pfad = base_path('module/'.$modulName.'/'.$modulNameGross.'ServiceProvider.php');
        File::put($pfad, $stub);
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

    /**
     * Ersetzt die Variablen im Stub
     *
     * @param [type] $modulName
     * @param [type] $stub
     * @return void
     */
    private function replaceStub($modulName, $stub){
        //Modulname mit ersten Buchstaben groß
        $modulNameGross = ucfirst($modulName);

        $stub = str_replace('modulNameGross', $modulNameGross, $stub);
        $stub = str_replace('modulName', $modulName, $stub);
        return $stub;
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
