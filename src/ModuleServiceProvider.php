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
use ITHilbert\Module\Commands\LivewireCommand;


class ModuleServiceProvider extends ServiceProvider
{

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->commands(CreateCommand::class );
        $this->commands(MixCommand::class);
        $this->commands(SetCommand::class);
        $this->commands(GetCommand::class);
        $this->commands(ModelCommand::class);
        $this->commands(ControllerCommand::class);
        $this->commands(ConfigCommand::class);
        $this->commands(LivewireCommand::class);
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


}
