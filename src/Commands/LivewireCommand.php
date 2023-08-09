<?php

namespace ITHilbert\Module\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use ITHilbert\Module\Classes\Stub;

class LivewireCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:livewire {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Erstellt eine neue Livewire Komponente.';

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

        //Stub laden Komponente
        $stub = new Stub('livewire');
        $stub->setDummyName($name);
        $stub->saveAsLivewire($name);

        //Stub laden View
        $stub = new Stub('livewire-view');
        $stub->setDummyName($name);
        $stub->saveAsLivewireView($name);


        $this->info('Liewire: ' . $name . ' wurde erstellt!');

        $ausgabe = "Livewire::component('".$modulName."::".$name ."', \\Module\\". ucfirst($modulName) ."\\Livewire\\".ucfirst($name)."::class);";
        $this->info('Füge folgende Zeile in die Datei '.ucfirst($modulName).'ServiceProvider ein:');
        $this->info($ausgabe);

    }
}
