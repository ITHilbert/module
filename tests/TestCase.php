<?php

declare(strict_types=1);

namespace ITHilbert\Module\Tests;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use ITHilbert\Module\ModuleServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Basis aller Paket-Tests: eigene Testbench-App, unabhängig vom Host.
 */
abstract class TestCase extends Orchestra
{
    /** Temporäres Projektverzeichnis (base_path) für Tests, die Dateien erzeugen. */
    protected ?string $tmpBasis = null;

    protected function tearDown(): void
    {
        if ($this->tmpBasis !== null) {
            (new Filesystem)->deleteDirectory($this->tmpBasis);
            $this->tmpBasis = null;
        }

        parent::tearDown();
    }

    /**
     * Legt ein leeres temporäres Projektverzeichnis an und setzt es als base_path().
     */
    protected function nutzeTemporaeresProjekt(): string
    {
        $this->tmpBasis = sys_get_temp_dir().'/module-test-'.bin2hex(random_bytes(6));
        mkdir($this->tmpBasis, 0777, true);
        $this->app->setBasePath($this->tmpBasis);

        return $this->tmpBasis;
    }

    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            ModuleServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        $app['config']->set('session.driver', 'array');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('queue.default', 'sync');
    }
}
