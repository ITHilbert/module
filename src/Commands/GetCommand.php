<?php

namespace ITHilbert\Module\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class GetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:get';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gibt den gesetzten Modulname zurück.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $modulName =  Cache::get('active_modul');
        $this->info('Modulname: ' . $modulName);
    }
}
