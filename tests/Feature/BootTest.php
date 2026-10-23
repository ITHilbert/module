<?php

declare(strict_types=1);

namespace ITHilbert\Module\Tests\Feature;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use ITHilbert\Module\Commands\LivewireModelCommand;
use ITHilbert\Module\ModuleServiceProvider;
use ITHilbert\Module\Tests\TestCase;

/**
 * Charakterisierung des Boot-Verhaltens (Ist-Stand, keine Spec).
 */
final class BootTest extends TestCase
{
    // Charakterisierung
    public function test_provider_ist_registriert(): void
    {
        $this->assertArrayHasKey(ModuleServiceProvider::class, $this->app->getLoadedProviders());
    }

    // Charakterisierung
    public function test_zehn_befehle_sind_registriert(): void
    {
        $befehle = array_keys($this->app->make(Kernel::class)->all());

        foreach ([
            'module:create', 'module:mix', 'module:set', 'module:get', 'module:model',
            'module:controller', 'module:config', 'module:view', 'module:livewire', 'module:migration',
        ] as $name) {
            $this->assertContains($name, $befehle, $name);
        }
    }

    // Charakterisierung: LivewireCommand ist auskommentiert, nur LivewireModelCommand (module:livewire) ist aktiv
    public function test_livewire_befehl_ist_der_modell_befehl(): void
    {
        $befehl = $this->app->make(Kernel::class)->all()['module:livewire'];

        $this->assertInstanceOf(LivewireModelCommand::class, $befehl);
    }

    // Charakterisierung: Config wird nur veröffentlicht, nicht zusammengeführt
    public function test_config_ist_nur_publishbar_und_nicht_zusammengefuehrt(): void
    {
        $pfade = ServiceProvider::pathsToPublish(ModuleServiceProvider::class);

        $this->assertContains(config_path('module.php'), $pfade);
        $this->assertNull(config('module.row_classes'));
    }

    // Charakterisierung: Config-Vorlage
    public function test_config_vorlage_enthaelt_formularklassen(): void
    {
        $config = require __DIR__.'/../../src/Config/module.php';

        $this->assertSame('row', $config['row_classes']);
        $this->assertSame('col-xs-8 col-md-6', $config['col_input_classes']);
        $this->assertArrayHasKey('col_label_classes', $config);
    }

    // Charakterisierung: das Paket bringt weder Views noch Routen noch Migrationen mit
    public function test_keine_routen_keine_views_keine_migrationen(): void
    {
        $uris = array_map(static fn ($route): string => $route->uri(), Route::getRoutes()->getRoutes());

        $this->assertSame([], array_values(array_filter($uris, static fn (string $uri): bool => str_contains($uri, 'modul'))));
        $this->assertSame([], $this->app['migrator']->paths());
    }

    // Charakterisierung: alle Stubs, die die Befehle laden, sind vorhanden
    public function test_alle_stubs_sind_vorhanden(): void
    {
        foreach (['config', 'controller', 'model', 'migration', 'usecase', 'request', 'serviceProvider', 'web', 'livewire', 'livewire-view'] as $stub) {
            $this->assertFileExists(__DIR__.'/../../src/Stubs/'.$stub.'.stub', $stub);
        }
    }
}
