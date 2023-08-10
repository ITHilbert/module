<?php

namespace ITHilbert\Module\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use ITHilbert\Module\Classes\LivewireClassCreater;
use ITHilbert\Module\Classes\LivewireViewCreater;

class LivewireModelCommand extends Command
{
    protected $signature = 'module:livewire {viewName} {modelName}';
    protected $description = 'Erstellt eine neue Livewire View und Komponente {viewName - z.B. user/index} {modelName - z.B. App\\Models\\User}}';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $viewName = $this->argument('viewName');
        $modelName = $this->argument('modelName');  // adjust namespace if needed

        $modulName = Cache::get('active_modul');
        if (!$modulName) {
            $this->error('Es wurde kein Modulname gesetzt!');
            return;
        }

        //dd($modelName, class_exists($modelName));
        if (!class_exists($modelName)) {
            $this->error('Model nicht gefunden! Bitte so angeben: App\\Models\\User');
            return;
        }

        // view erstellen
        $vc = new LivewireViewCreater($modelName);
        $content = $vc->getForm();

        // Create the file
        $path = base_path("/module/{$modulName}/Resources/views/livewire/{$viewName}.blade.php");
        if (!File::exists(dirname($path))) {
            File::makeDirectory(dirname($path), 0777, true, true);
        }

        File::put($path, $content);

        // Class erstellen
        $lvcc = new LivewireClassCreater($modulName, $viewName, $modelName);
        $content = $lvcc->getClass();

        // Create the file
        $path = base_path("/module/{$modulName}/Livewire/" . ucfirst($viewName)."LW.php");
        if (!File::exists(dirname($path))) {
            File::makeDirectory(dirname($path), 0777, true, true);
        }

        File::put($path, $content);

        $this->info("Livewire Komponente wurde erstellt!");
    }

}
