<?php

declare(strict_types=1);

namespace ITHilbert\Module\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use ITHilbert\Module\Classes\Stub;
use ITHilbert\Module\Tests\TestCase;

/**
 * Charakterisierung der Klasse Stub (Ist-Stand, keine Spec).
 */
final class StubTest extends TestCase
{
    // OFFEN-1 (roter Test, Defekt): strtolower(Cache::get('active_modul')) bekommt null und wirft
    // (strict_types) einen TypeError, bevor die gedachte RuntimeException erreicht wird.
    public function test_wirft_ohne_aktives_modul_runtime_exception(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Es wurde kein Modul ausgewählt');

        new Stub('config');
    }

    // Charakterisierung
    public function test_ersetzt_modul_und_name_platzhalter_und_speichert_im_modulordner(): void
    {
        $basis = $this->nutzeTemporaeresProjekt();
        Cache::put('active_modul', 'Kunde');

        $stub = new Stub('model');
        $stub->setDummyName('Rechnung');
        $stub->saveAsModel('rechnung');

        $pfad = $basis.'/module/kunde/Domain/Models/Rechnung.php';
        $this->assertFileExists($pfad);
        $inhalt = (string) file_get_contents($pfad);
        $this->assertStringContainsString('namespace Module\Kunde\Domain\Models;', $inhalt);
        $this->assertStringContainsString('final class Rechnung', $inhalt);
    }

    // Charakterisierung: fehlende Zielordner werden rekursiv angelegt
    public function test_legt_fehlende_zielordner_an(): void
    {
        $basis = $this->nutzeTemporaeresProjekt();
        Cache::put('active_modul', 'kunde');

        $stub = new Stub('livewire-view');
        $stub->saveAsLivewireView('liste');

        $this->assertFileExists($basis.'/module/kunde/resources/views/livewire/liste.blade.php');
    }

    // Charakterisierung: Suffixe werden nur ergänzt, wenn sie fehlen
    public function test_ergaenzt_suffixe_nur_wenn_sie_fehlen(): void
    {
        $basis = $this->nutzeTemporaeresProjekt();
        Cache::put('active_modul', 'kunde');

        $stub = new Stub('controller');
        $stub->setDummyName('x');
        $stub->saveAsController('AController');
        $stub->saveAsController('B');
        $stub = new Stub('serviceProvider');
        $stub->saveAsServiceProvider('kundeServiceProvider');

        $this->assertFileExists($basis.'/module/kunde/Controllers/AController.php');
        $this->assertFileExists($basis.'/module/kunde/Controllers/BController.php');
        $this->assertFileExists($basis.'/module/kunde/KundeServiceProvider.php');
    }

    // Charakterisierung: Use-Case-Dateiname bekommt .php nur wenn nötig
    public function test_usecase_dateiname_erhaelt_php_endung_einmal(): void
    {
        $basis = $this->nutzeTemporaeresProjekt();
        Cache::put('active_modul', 'kunde');

        $stub = new Stub('usecase');
        $stub->saveAsUseCase('A.php');
        $stub->saveAsUseCase('B');

        $this->assertFileExists($basis.'/module/kunde/Application/UseCases/A.php');
        $this->assertFileExists($basis.'/module/kunde/Application/UseCases/B.php');
    }
}
