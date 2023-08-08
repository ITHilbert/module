<?php

namespace Module\Module;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Module\Module\Commands\Create;
use Module\Module\Commands\Mix;

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
