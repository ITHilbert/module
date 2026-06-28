<?php

declare(strict_types=1);

namespace ITHilbert\Module\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use ITHilbert\Module\Classes\Stub;

class CreateCommand extends Command
{
    /** @var string */
    protected $signature = 'module:create {modulName}';

    /** @var string */
    protected $description = 'Erstellt ein neues Modul mit Clean-Architecture-Schichten';

    public function handle(): void
    {
        $modulName = $this->argument('modulName') ?? '';

        if ($modulName === '') {
            $this->error('Es wurde kein Modulname angegeben!');

            return;
        }

        $modulName = strtolower($modulName);

        if (! File::exists(base_path('module'))) {
            File::makeDirectory(base_path('module'));
        }

        if (File::exists(base_path('module/' . $modulName))) {
            $this->error('Es existiert bereits ein Modul mit dem Namen ' . $modulName . '!');

            return;
        }

        Cache::put('active_modul', $modulName, now()->addDay());

        $this->createFolder($modulName);
        $this->createServiceProvider($modulName);
        $this->createConfig($modulName);
        $this->createController($modulName);
        $this->createRoutes();
        $this->createModels($modulName);
        $this->createExampleUseCase($modulName);
        $this->createRequests($modulName);
        $this->createGitkeep($modulName);

        $this->info('Modul "' . $modulName . '" wurde angelegt.');
        $this->info('Vergessen Sie nicht, es in der config/app.php und in der composer.json einzutragen!');
    }

    private function createConfig(string $modulName): void
    {
        $stub = new Stub('config');
        $stub->saveAsConfig($modulName);
    }

    private function createController(string $modulName): void
    {
        $stub = new Stub('controller');
        $stub->setDummyName($modulName);
        $stub->saveAsController($modulName);
    }

    private function createRoutes(): void
    {
        $stub = new Stub('web');
        $stub->saveAsRoute('web');
    }

    private function createModels(string $modulName): void
    {
        $stub = new Stub('model');
        $stub->setDummyName($modulName);
        $stub->saveAsModel($modulName);
    }

    private function createExampleUseCase(string $modulName): void
    {
        $stub = new Stub('usecase');
        $stub->setDummyName($modulName);
        // Dateiname: <ModulName>Anlegen.php  z.B. KundeAnlegen.php
        $fileName = ucfirst($modulName) . 'Anlegen.php';
        $stub->saveAsUseCase($fileName);
    }

    private function createRequests(string $modulName): void
    {
        // Store<ModulName>Request und Update<ModulName>Request generieren
        // saveAsRequest() lädt den Stub intern neu — kein geteilter Zustand zwischen den Aufrufen
        $stub = new Stub;
        $stub->setDummyName($modulName);
        $stub->saveAsRequest('Store', $modulName);

        $stub->saveAsRequest('Update', $modulName);
    }

    private function createServiceProvider(string $modulName): void
    {
        $stub = new Stub('serviceProvider');
        $stub->saveAsServiceProvider($modulName);
    }

    private function createFolder(string $modulName): void
    {
        $base = base_path('module/' . $modulName . '/');

        File::makeDirectory($base);

        // Bestehende MVC-Ordner
        File::makeDirectory($base . 'config');
        File::makeDirectory($base . 'controllers');
        File::makeDirectory($base . 'Requests');
        File::makeDirectory($base . 'database');
        File::makeDirectory($base . 'database/migrations');
        File::makeDirectory($base . 'database/seeders');
        File::makeDirectory($base . 'public');
        File::makeDirectory($base . 'public/css');
        File::makeDirectory($base . 'public/js');
        File::makeDirectory($base . 'public/images');
        File::makeDirectory($base . 'resources');
        File::makeDirectory($base . 'resources/lang');
        File::makeDirectory($base . 'resources/lang/de');
        File::makeDirectory($base . 'resources/lang/en');
        File::makeDirectory($base . 'resources/views');
        File::makeDirectory($base . 'routes');

        // Clean-Architecture-Schichten (B22)
        File::makeDirectory($base . 'Domain');
        File::makeDirectory($base . 'Domain/Models');
        File::makeDirectory($base . 'Domain/Enums');
        File::makeDirectory($base . 'Domain/ValueObjects');
        File::makeDirectory($base . 'Domain/Events');
        File::makeDirectory($base . 'Domain/Exceptions');

        File::makeDirectory($base . 'Application');
        File::makeDirectory($base . 'Application/UseCases');
        File::makeDirectory($base . 'Application/Ports');
        File::makeDirectory($base . 'Application/DTOs');

        File::makeDirectory($base . 'Infrastructure');
        File::makeDirectory($base . 'Infrastructure/Adapters');
        File::makeDirectory($base . 'Infrastructure/Repositories');
    }

    private function createGitkeep(string $modulName): void
    {
        $base = base_path('module/' . $modulName . '/');

        // Leere Schicht-Ordner mit .gitkeep versehen
        $gitkeepDirs = [
            'database/migrations',
            'database/seeders',
            'public/css',
            'public/js',
            'public/images',
            'resources/lang/de',
            'resources/lang/en',
            'resources/views',
            'Domain/Enums',
            'Domain/ValueObjects',
            'Domain/Events',
            'Domain/Exceptions',
            'Application/Ports',
            'Application/DTOs',
            'Infrastructure/Adapters',
            'Infrastructure/Repositories',
        ];

        foreach ($gitkeepDirs as $dir) {
            File::put($base . $dir . '/.gitkeep', '');
        }
    }
}
