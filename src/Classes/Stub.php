<?php

declare(strict_types=1);

namespace ITHilbert\Module\Classes;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class Stub
{
    private string $stub;

    private string $dummyModul;

    private string $dummyName = 'dummyName';

    private string $DummyName = 'DummyName';

    public function __construct(?string $stubName = null)
    {
        $this->dummyModul = strtolower(Cache::get('active_modul'));
        if (! $this->dummyModul) {
            throw new \RuntimeException('Es wurde kein Modul ausgewählt');
        }

        if ($stubName) {
            $this->load($stubName);
        }
    }

    public function setDummyName(string $dummyName): void
    {
        $dummyName = strtolower($dummyName);
        $this->dummyName = $dummyName;
        $this->DummyName = ucfirst($dummyName);
    }

    public function load(string $stubName): void
    {
        $this->stub = File::get(__DIR__ . '/../Stubs/' . $stubName . '.stub');
    }

    private function save(string $fileName): void
    {
        $this->replaceStub();

        $pfad = base_path('module/' . $this->dummyModul . '/' . $fileName);

        if (! File::exists(dirname($pfad))) {
            File::makeDirectory(dirname($pfad), 0755, true);
        }

        File::put($pfad, $this->stub);
    }

    public function saveAsConfig(string $fileName): void
    {
        $this->save('config/' . $fileName . '.php');
    }

    public function saveAsMigration(string $fileName): string
    {
        $pfad = 'database/migrations/' . date('Y_m_d_His') . '_' . $fileName . '.php';
        $this->save($pfad);

        return $pfad;
    }

    public function saveAsSeeders(string $fileName): void
    {
        $this->save('database/seeders/' . $fileName . '.php');
    }

    public function saveAsController(string $fileName): void
    {
        if (! str_ends_with($fileName, 'Controller')) {
            $fileName .= 'Controller';
        }

        $this->save('controllers/' . ucfirst($fileName) . '.php');
    }

    /**
     * Speichert ein Model in Domain/Models/ (Clean Architecture).
     */
    public function saveAsModel(string $fileName): void
    {
        $this->save('Domain/Models/' . ucfirst($fileName) . '.php');
    }

    /**
     * Speichert einen UseCase in Application/UseCases/ (Clean Architecture).
     */
    public function saveAsUseCase(string $fileName): void
    {
        if (! str_ends_with($fileName, '.php')) {
            $fileName .= '.php';
        }

        $this->save('Application/UseCases/' . $fileName);
    }

    /**
     * Speichert einen Form Request in Requests/ (HTTP-Schicht).
     * Lädt den Stub vor jedem Aufruf neu, damit Store + Update unabhängig generiert werden.
     *
     * @param string $action   Verb-Präfix für den Klassennamen, z.B. 'Store' oder 'Update'
     * @param string $fileName Basisname ohne Präfix und ohne .php, z.B. 'Kunde'
     */
    public function saveAsRequest(string $action, string $fileName): void
    {
        // Frisch laden, damit mehrere Aufrufe (Store/Update) nicht interferieren
        $this->load('request');

        $className = $action . ucfirst($fileName) . 'Request';

        // DummyAction im Stub durch das konkrete Verb ersetzen, bevor replaceStub() greift
        $this->stub = str_replace('DummyAction', $action, $this->stub);

        $this->save('Requests/' . $className . '.php');
    }

    public function saveAsView(string $fileName): void
    {
        $this->save('resources/views/' . $fileName . '.blade.php');
    }

    public function saveAsRoute(string $fileName): void
    {
        $this->save('routes/' . $fileName . '.php');
    }

    public function saveAsServiceProvider(string $fileName): void
    {
        if (! str_ends_with($fileName, 'ServiceProvider')) {
            $fileName .= 'ServiceProvider';
        }

        $this->save(ucfirst($fileName) . '.php');
    }

    public function saveAsLivewire(string $fileName): void
    {
        $this->save('livewire/' . ucfirst($fileName) . '.php');
    }

    public function saveAsLivewireView(string $fileName): void
    {
        $this->save('resources/views/livewire/' . $fileName . '.blade.php');
    }

    private function replaceStub(): void
    {
        $dummyModul = $this->dummyModul;
        $DummyModul = ucfirst($dummyModul);

        $stub = $this->stub;
        $stub = str_replace('DummyModul', $DummyModul, $stub);
        $stub = str_replace('dummyModul', $dummyModul, $stub);
        if ($this->dummyName) {
            $stub = str_replace('dummyName', $this->dummyName, $stub);
        }
        if ($this->DummyName) {
            $stub = str_replace('DummyName', $this->DummyName, $stub);
        }

        $this->stub = $stub;
    }
}
