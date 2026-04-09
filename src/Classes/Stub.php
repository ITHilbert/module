<?php

namespace ITHilbert\Module\Classes;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class Stub{

    private string $stub;
    private string $dummyModul;
    private string $dummyName = 'dummyName';
    private string $DummyName = 'DummyName';


    public function __construct(string $stubName = null)
    {
        $this->dummyModul = strtolower(Cache::get('active_modul'));
        if(!$this->dummyModul){
            throw new \Exception('Es wurde kein Modul ausgewählt');
        }

        //Stub laden
        if($stubName) $this->load($stubName);
    }

    public function setDummyName(string $dummyName){
        $dummyName = strtolower($dummyName);
        $this->dummyName = $dummyName;
        $this->DummyName = ucfirst($dummyName);
    }



    public function load($stubName){
        $this->stub = File::get(__DIR__.'/../Stubs/'.$stubName.'.stub');
    }

    private function save($fileName){

        $this->replaceStub();

        $pfad = base_path('module/'.$this->dummyModul.'/'.$fileName);

        //Prüfen ob Ordner existiert, sonst anlegen
        if(!File::exists(dirname($pfad))){
            File::makeDirectory(dirname($pfad),0755,true);
        }
        File::put($pfad, $this->stub);
    }

    public function saveAsConfig($fileName){
        $this->save('config/'.$fileName.'.php');
    }

    public function saveAsMigration($fileName){
        $pfad = 'database/migrations/'.date('Y_m_d_His') . '_' . $fileName .'.php';
        $this->save( $pfad);
        return $pfad;
    }

    public function saveAsSeeders($fileName){
        $this->save('database/seeders/'.$fileName.'.php');
    }

    public function saveAsController($fileName){
        //prüfen ob $fileName mit Controller endet
        if(!str_ends_with($fileName, 'Controller')){
            $fileName .= 'Controller';
        }

        $this->save('controllers/'. ucfirst($fileName).'.php');
    }

    public function saveAsModel($fileName){
        $this->save('models/'. ucfirst($fileName).'.php');
    }

    public function saveAsView($fileName){
        $this->save('resources/views/'. $fileName.'.blade.php');
    }

    public function saveAsRoute($fileName){
        $this->save('routes/'.$fileName.'.php');
    }

    public function saveAsServiceProvider($fileName){
        //prüfen ob $fileName mit ServiceProvider endet
        if(!str_ends_with($fileName, 'ServiceProvider')){
            $fileName .= 'ServiceProvider';
        }

        $this->save(ucfirst($fileName).'.php');
    }

    public function saveAsLivewire($fileName){
        $this->save('livewire/'. ucfirst($fileName).'.php');
    }

    public function saveAsLivewireView($fileName){
        $this->save('resources/views/livewire/'. $fileName.'.blade.php');
    }

    /**
     * Ersetzt die Variablen im Stub
     *
     * @param [type] $this->modulName
     * @param [type] $stub
     * @return void
     */
    private function replaceStub(){
        $dummyModul =  $this->dummyModul;
        $DummyModul = ucfirst($dummyModul);

        $stub = $this->stub;
        $stub = str_replace('DummyModul', $DummyModul, $stub);
        $stub = str_replace('dummyModul', $dummyModul, $stub);
        if($this->dummyName) $stub = str_replace('dummyName', $this->dummyName, $stub);
        if($this->DummyName) $stub = str_replace('DummyName', $this->DummyName, $stub);

        $this->stub = $stub;
    }


}
