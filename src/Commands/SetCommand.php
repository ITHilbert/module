<?php

namespace ITHilbert\Module\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class SetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:set {modulName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Modulname setzen.';

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

        //prüfen ob der ordner existiert
        if (!File::exists(base_path('module/'.$modulName))) {
            $this->error('Es existiert kein Ordner "'.$modulName.'"!');
            return;
        }

        //Modulname setzen
        Cache::put('active_modul', $modulName, now()->addDay());

        $this->info('Modulname wurde gesetzt!');
    }
}
