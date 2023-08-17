<?php

namespace ITHilbert\Module\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use ITHilbert\Module\Classes\Stub;

class ModelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:model {modelName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Erstellt ein neues Model.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $modulName =  Cache::get('active_modul');
        $model = $this->argument('modelName') ?? '';

        //prüfen ob $modelName leer ist
        if ($model == '') {
            $this->error('Es wurde kein Modelname angegeben!');
            return;
        }

        //prüfen ob $modulName leer ist
        if ($modulName == '') {
            $this->error('Es wurde kein Modulname angegeben!');
            return;
        }

        //Stub laden
        $stub = new Stub('model');
        $stub->setDummyName($model);
        $stub->saveAsModel($model);

        $this->info('Model: ' . $modulName . ' wurde erstellt!');
    }
}
