<?php

declare(strict_types=1);

namespace ITHilbert\Module\Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use ITHilbert\Module\Tests\TestCase;

/**
 * Charakterisierung der übrigen module:*-Befehle (Ist-Stand, keine Spec).
 */
final class ModulBefehleTest extends TestCase
{
    private function modulVorbereiten(string $name = 'kunde'): string
    {
        $basis = $this->nutzeTemporaeresProjekt();
        $this->artisan('module:create', ['modulName' => $name])->assertSuccessful();

        return $basis.'/module/'.$name;
    }

    // Charakterisierung
    public function test_set_setzt_aktives_modul_in_kleinbuchstaben(): void
    {
        $basis = $this->nutzeTemporaeresProjekt();
        mkdir($basis.'/module/kunde', 0777, true);

        $this->artisan('module:set', ['modulName' => 'KUNDE'])
            ->expectsOutputToContain('Modulname wurde gesetzt!')
            ->assertSuccessful();

        $this->assertSame('kunde', Cache::get('active_modul'));
    }

    // Charakterisierung
    public function test_set_meldet_fehlenden_ordner_und_aendert_nichts(): void
    {
        $this->nutzeTemporaeresProjekt();

        $this->artisan('module:set', ['modulName' => 'gibtsnicht'])
            ->expectsOutputToContain('Es existiert kein Ordner "gibtsnicht"!')
            ->assertSuccessful();

        $this->assertNull(Cache::get('active_modul'));
    }

    // Charakterisierung
    public function test_get_gibt_aktives_modul_aus(): void
    {
        Cache::put('active_modul', 'kunde');

        $this->artisan('module:get')->expectsOutputToContain('Modulname: kunde')->assertSuccessful();
    }

    // Charakterisierung
    public function test_controller_befehl_legt_controller_mit_suffix_an(): void
    {
        $modul = $this->modulVorbereiten();

        $this->artisan('module:controller', ['name' => 'rechnung'])
            ->expectsOutputToContain('Controller: rechnung wurde erstellt!')
            ->assertSuccessful();

        $this->assertFileExists($modul.'/Controllers/RechnungController.php');
    }

    // Charakterisierung: der Controller-Stub hängt am Modulnamen, nicht am angegebenen Namen
    public function test_controller_stub_nutzt_modulnamen_fuer_klassennamen(): void
    {
        $modul = $this->modulVorbereiten();

        $this->artisan('module:controller', ['name' => 'rechnung'])->assertSuccessful();

        $inhalt = (string) file_get_contents($modul.'/Controllers/RechnungController.php');
        $this->assertStringContainsString('final class KundeController', $inhalt);
    }

    // Charakterisierung
    public function test_config_befehl_legt_config_an(): void
    {
        $modul = $this->modulVorbereiten();

        $this->artisan('module:config', ['name' => 'extra'])->assertSuccessful();

        $this->assertFileExists($modul.'/config/extra.php');
    }

    // Charakterisierung
    public function test_model_befehl_legt_model_mit_namen_an(): void
    {
        $modul = $this->modulVorbereiten();

        $this->artisan('module:model', ['modelName' => 'Rechnung'])->assertSuccessful();

        $inhalt = (string) file_get_contents($modul.'/Domain/Models/Rechnung.php');
        $this->assertStringContainsString('namespace Module\Kunde\Domain\Models;', $inhalt);
        $this->assertStringContainsString('final class Rechnung extends Model', $inhalt);
        $this->assertStringContainsString("protected \$table = 'rechnungs';", $inhalt);
    }

    // Charakterisierung
    public function test_migration_befehl_legt_zeitgestempelte_migration_an(): void
    {
        $modul = $this->modulVorbereiten();

        $this->artisan('module:migration', ['name' => 'rechnung'])->assertSuccessful();

        $dateien = glob($modul.'/database/migrations/*_rechnung.php');
        $this->assertCount(1, $dateien);
        $this->assertMatchesRegularExpression('/\d{4}_\d{2}_\d{2}_\d{6}_rechnung\.php$/', $dateien[0]);
        $this->assertStringContainsString("Schema::create('rechnungs'", (string) file_get_contents($dateien[0]));
    }

    // Charakterisierung: die erzeugte Migration läuft auf SQLite durch
    public function test_erzeugte_migration_laeuft_auf_sqlite(): void
    {
        $modul = $this->modulVorbereiten();
        $this->artisan('module:migration', ['name' => 'rechnung'])->assertSuccessful();

        $this->artisan('migrate', ['--path' => $modul.'/database/migrations', '--realpath' => true])->assertSuccessful();

        $this->assertTrue(Schema::hasTable('rechnungs'));
    }

    // Charakterisierung
    public function test_befehle_ohne_aktives_modul_melden_fehler_und_schreiben_nichts(): void
    {
        $basis = $this->nutzeTemporaeresProjekt();

        $this->artisan('module:controller', ['name' => 'x'])
            ->expectsOutputToContain('Es wurde kein Modulname gesetzt!')
            ->assertSuccessful();
        $this->artisan('module:config', ['name' => 'x'])->expectsOutputToContain('Es wurde kein Modulname gesetzt!')->assertSuccessful();
        $this->artisan('module:migration', ['name' => 'x'])->expectsOutputToContain('Es wurde kein Modulname gesetzt!')->assertSuccessful();
        $this->artisan('module:model', ['modelName' => 'X'])->expectsOutputToContain('Es wurde kein Modulname angegeben!')->assertSuccessful();

        $this->assertDirectoryDoesNotExist($basis.'/module');
    }

    // Charakterisierung
    public function test_mix_befehl_haengt_marker_und_modul_eintraege_an(): void
    {
        $modul = $this->modulVorbereiten();
        $basis = dirname($modul, 2);
        file_put_contents($basis.'/webpack.mix.js', "const mix = require('laravel-mix');\n");

        $this->artisan('module:mix')->expectsOutputToContain('webpack.mix.js angepasst')->assertSuccessful();

        $inhalt = (string) file_get_contents($basis.'/webpack.mix.js');
        $this->assertStringContainsString("//Module (keine manuellen Einträge darunter einfügen)\n", $inhalt);
        $this->assertStringContainsString('mix.copy("module/kunde/Config", "config");', $inhalt);
        $this->assertStringContainsString('mix.copy("module/kunde/Public", "public/module/kunde");', $inhalt);
    }

    // Charakterisierung: zweiter Lauf erzeugt keine Doppelungen
    public function test_mix_befehl_ist_wiederholbar(): void
    {
        $modul = $this->modulVorbereiten();
        $basis = dirname($modul, 2);
        file_put_contents($basis.'/webpack.mix.js', "const mix = require('laravel-mix');\n");

        $this->artisan('module:mix')->assertSuccessful();
        $erster = (string) file_get_contents($basis.'/webpack.mix.js');
        $this->artisan('module:mix')->assertSuccessful();

        $this->assertSame($erster, (string) file_get_contents($basis.'/webpack.mix.js'));
    }

    // Charakterisierung: ViewModel-/Livewire-Befehle brechen bei unbekanntem Model kontrolliert ab
    public function test_view_und_livewire_befehl_melden_unbekanntes_model(): void
    {
        $this->modulVorbereiten();

        $this->artisan('module:view', ['viewName' => 'kunde/index', 'modelName' => 'App\\Models\\GibtEsNicht'])
            ->expectsOutputToContain('Model nicht gefunden!')
            ->assertSuccessful();
        $this->artisan('module:livewire', ['viewName' => 'kunde/index', 'modelName' => 'App\\Models\\GibtEsNicht'])
            ->expectsOutputToContain('Model nicht gefunden!')
            ->assertSuccessful();
    }
}
