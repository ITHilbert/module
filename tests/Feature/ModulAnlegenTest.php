<?php

declare(strict_types=1);

namespace ITHilbert\Module\Tests\Feature;

use Illuminate\Support\Facades\Cache;
use ITHilbert\Module\Tests\TestCase;
use Module\Kunde\KundeServiceProvider;

/**
 * Charakterisierung von module:create (Ist-Stand, keine Spec). Dateisystem: temporäres base_path().
 */
final class ModulAnlegenTest extends TestCase
{
    // Charakterisierung
    public function test_legt_verzeichnisstruktur_mit_clean_architecture_schichten_an(): void
    {
        $basis = $this->nutzeTemporaeresProjekt();

        $this->artisan('module:create', ['modulName' => 'Kunde'])->assertSuccessful();

        foreach ([
            'config', 'Controllers', 'Requests', 'database/migrations', 'database/seeders', 'public/css',
            'public/js', 'public/images', 'resources/lang/de', 'resources/lang/en', 'resources/views', 'routes',
            'Domain/Models', 'Domain/Enums', 'Domain/ValueObjects', 'Domain/Events', 'Domain/Exceptions',
            'Application/UseCases', 'Application/Ports', 'Application/DTOs',
            'Infrastructure/Adapters', 'Infrastructure/Repositories',
        ] as $ordner) {
            $this->assertDirectoryExists($basis.'/module/kunde/'.$ordner, $ordner);
        }
    }

    // Charakterisierung: Modulname wird kleingeschrieben, Namespace großgeschrieben
    public function test_erzeugt_dateien_mit_ersetzten_platzhaltern(): void
    {
        $basis = $this->nutzeTemporaeresProjekt();

        $this->artisan('module:create', ['modulName' => 'Kunde'])->assertSuccessful();

        $dateien = [
            'KundeServiceProvider.php', 'config/kunde.php', 'Controllers/KundeController.php',
            'routes/web.php', 'Domain/Models/Kunde.php', 'Application/UseCases/KundeAnlegen.php',
            'Requests/StoreKundeRequest.php', 'Requests/UpdateKundeRequest.php',
        ];

        foreach ($dateien as $datei) {
            $pfad = $basis.'/module/kunde/'.$datei;
            $this->assertFileExists($pfad, $datei);
            $inhalt = (string) file_get_contents($pfad);
            $this->assertStringNotContainsString('Dummy', $inhalt, $datei);
            $this->assertStringNotContainsString('dummy', $inhalt, $datei);
        }

        $provider = (string) file_get_contents($basis.'/module/kunde/KundeServiceProvider.php');
        $this->assertStringContainsString('namespace Module\Kunde;', $provider);
        $this->assertStringContainsString('class KundeServiceProvider extends ServiceProvider', $provider);
        $this->assertStringContainsString("loadViewsFrom(__DIR__ . '/resources/views', 'kunde')", $provider);

        $this->assertStringContainsString("'name' => 'Kunde'", (string) file_get_contents($basis.'/module/kunde/config/kunde.php'));
    }

    // Charakterisierung: Store- und Update-Request werden unabhängig voneinander erzeugt
    public function test_store_und_update_request_tragen_je_ihren_klassennamen(): void
    {
        $basis = $this->nutzeTemporaeresProjekt();

        $this->artisan('module:create', ['modulName' => 'kunde'])->assertSuccessful();

        $this->assertStringContainsString('final class StoreKundeRequest', (string) file_get_contents($basis.'/module/kunde/Requests/StoreKundeRequest.php'));
        $this->assertStringContainsString('final class UpdateKundeRequest', (string) file_get_contents($basis.'/module/kunde/Requests/UpdateKundeRequest.php'));
    }

    // Charakterisierung: alle erzeugten PHP-Dateien sind syntaktisch gültig
    public function test_erzeugte_php_dateien_sind_syntaktisch_gueltig(): void
    {
        $basis = $this->nutzeTemporaeresProjekt();

        $this->artisan('module:create', ['modulName' => 'kunde'])->assertSuccessful();

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($basis.'/module/kunde', \FilesystemIterator::SKIP_DOTS));
        $anzahl = 0;
        foreach ($iterator as $datei) {
            if ($datei->getExtension() !== 'php') {
                continue;
            }
            $anzahl++;
            // Wirft ParseError bei ungültigem PHP
            token_get_all((string) file_get_contents($datei->getPathname()), TOKEN_PARSE);
        }

        $this->assertSame(8, $anzahl);
    }

    // Charakterisierung: leere Schicht-Ordner erhalten .gitkeep
    public function test_leere_ordner_erhalten_gitkeep(): void
    {
        $basis = $this->nutzeTemporaeresProjekt();

        $this->artisan('module:create', ['modulName' => 'kunde'])->assertSuccessful();

        $this->assertFileExists($basis.'/module/kunde/Domain/Enums/.gitkeep');
        $this->assertFileExists($basis.'/module/kunde/Infrastructure/Repositories/.gitkeep');
        $this->assertFileExists($basis.'/module/kunde/resources/views/.gitkeep');
        $this->assertFileDoesNotExist($basis.'/module/kunde/Controllers/.gitkeep');
    }

    // Charakterisierung: das neue Modul wird als aktives Modul gemerkt
    public function test_setzt_aktives_modul_im_cache(): void
    {
        $this->nutzeTemporaeresProjekt();

        $this->artisan('module:create', ['modulName' => 'Kunde'])->assertSuccessful();

        $this->assertSame('kunde', Cache::get('active_modul'));
    }

    // Charakterisierung: Hinweis auf manuelle Schritte
    public function test_gibt_hinweis_auf_composer_und_providers(): void
    {
        $this->nutzeTemporaeresProjekt();

        $this->artisan('module:create', ['modulName' => 'kunde'])
            ->expectsOutputToContain('Modul "kunde" wurde angelegt.')
            ->expectsOutputToContain('bootstrap/providers.php: Module\Kunde\KundeServiceProvider::class')
            ->assertSuccessful();
    }

    // Charakterisierung: existiert das Modul schon, bleibt es unangetastet
    public function test_lehnt_bereits_vorhandenes_modul_ab(): void
    {
        $basis = $this->nutzeTemporaeresProjekt();
        mkdir($basis.'/module/kunde', 0777, true);
        file_put_contents($basis.'/module/kunde/marker.txt', 'x');

        $this->artisan('module:create', ['modulName' => 'kunde'])
            ->expectsOutputToContain('Es existiert bereits ein Modul mit dem Namen kunde!')
            ->assertSuccessful();

        $this->assertSame(['marker.txt'], array_values(array_diff(scandir($basis.'/module/kunde'), ['.', '..'])));
    }

    // Charakterisierung: der Ordner module/ wird bei Bedarf angelegt
    public function test_legt_modul_ordner_an_wenn_er_fehlt(): void
    {
        $basis = $this->nutzeTemporaeresProjekt();

        $this->assertDirectoryDoesNotExist($basis.'/module');

        $this->artisan('module:create', ['modulName' => 'kunde'])->assertSuccessful();

        $this->assertDirectoryExists($basis.'/module');
    }

    // Charakterisierung: ein erzeugter ServiceProvider lässt sich laden und registriert die Views des Moduls
    public function test_erzeugter_serviceprovider_registriert_views_und_routen_pfade(): void
    {
        $basis = $this->nutzeTemporaeresProjekt();

        $this->artisan('module:create', ['modulName' => 'kunde'])->assertSuccessful();
        file_put_contents($basis.'/module/kunde/resources/views/index.blade.php', 'Liste');

        require_once $basis.'/module/kunde/KundeServiceProvider.php';
        $this->app->register(KundeServiceProvider::class);

        $this->assertTrue(view()->exists('kunde::index'));
        $this->assertTrue($this->app['router']->has('kunde.index'));
    }
}
