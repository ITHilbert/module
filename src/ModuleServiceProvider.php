<?php

namespace ITHilbert\Module;

use Illuminate\Support\ServiceProvider;
use ITHilbert\Module\Commands\CreateCommand;
use ITHilbert\Module\Commands\MixCommand;
use ITHilbert\Module\Commands\SetCommand;
use ITHilbert\Module\Commands\GetCommand;
use ITHilbert\Module\Commands\ModelCommand;
use ITHilbert\Module\Commands\ConfigCommand;
use ITHilbert\Module\Commands\ControllerCommand;
//use ITHilbert\Module\Commands\LivewireCommand;
use ITHilbert\Module\Commands\ViewModelCommand;
use ITHilbert\Module\Commands\LivewireModelCommand;
use ITHilbert\Module\Commands\MigrationCommand;

class ModuleServiceProvider extends ServiceProvider
{

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
        $this->registerCommands();
    }

    public function registerCommands(){
        $this->commands(CreateCommand::class );
        $this->commands(MixCommand::class);
        $this->commands(SetCommand::class);
        $this->commands(GetCommand::class);
        $this->commands(ModelCommand::class);
        $this->commands(ControllerCommand::class);
        $this->commands(ConfigCommand::class);
        //$this->commands(LivewireCommand::class);
        $this->commands(ViewModelCommand::class);
        $this->commands(LivewireModelCommand::class);
        $this->commands(MigrationCommand::class);
    }


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__ .'/Config/module.php' => config_path('module.php')
        ]);
    }


}
