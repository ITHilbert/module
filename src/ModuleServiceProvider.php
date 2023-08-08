<?php

namespace ITHilbert\Module;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use ITHilbert\Module\Commands\Create;
use ITHilbert\Module\Commands\Mix;

class ModuleServiceProvider extends ServiceProvider
{

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->commands( Create::class );
        $this->commands( Mix::class);
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
