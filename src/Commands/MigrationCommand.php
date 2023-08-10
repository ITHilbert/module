<?php

namespace ITHilbert\Module\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use ITHilbert\Module\Classes\Stub;

class MigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:migration {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Erstellt eine neue Migration Datei.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $modulName =  Cache::get('active_modul');
        $name = $this->argument('name') ?? '';

        //prüfen ob $name leer ist
        if ($name == '') {
            $this->error('Es wurde kein Name angegeben!');
            return;
        }

        //prüfen ob $modulName leer ist
        if ($modulName == '') {
            $this->error('Es wurde kein Modulname gesetzt!');
            return;
        }


        //Stub laden
        $stub = new Stub('migration');
        $stub->setDummyName($name);
        $pfad = $stub->saveAsMigration($name);

        $this->info('Migration: "' . $pfad . '" wurde erstellt!');
    }
}
