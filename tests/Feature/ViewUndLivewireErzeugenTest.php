<?php

declare(strict_types=1);

namespace ITHilbert\Module\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ITHilbert\Module\Classes\LivewireClassCreater;
use ITHilbert\Module\Classes\LivewireViewCreater;
use ITHilbert\Module\Classes\ViewCreater;
use ITHilbert\Module\Tests\Fixtures\Beispiel;
use ITHilbert\Module\Tests\TestCase;

/**
 * Charakterisierung der Formular-Generatoren, die das Schema einer Tabelle lesen (Ist-Stand, keine Spec).
 */
final class ViewUndLivewireErzeugenTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('beispiele', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('beschreibung')->nullable();
            $table->integer('menge')->default(0);
            $table->boolean('aktiv')->default(true);
            $table->date('datum')->nullable();
            $table->decimal('preis', 8, 2)->nullable();
            $table->unsignedBigInteger('kunde_id')->nullable();
            $table->timestamps();
        });
    }

    // Charakterisierung
    public function test_view_creater_erzeugt_formular_ohne_id_und_zeitstempel(): void
    {
        $inhalt = (new ViewCreater(Beispiel::class))->getForm();

        $this->assertStringContainsString('@extends("layouts.card")', $inhalt);
        $this->assertStringContainsString("route('beispiel.store')", $inhalt);
        $this->assertStringContainsString('<input-text name="name"', $inhalt);
        $this->assertStringContainsString('<select name="kunde_id"', $inhalt);
        $this->assertStringNotContainsString('name="id"', $inhalt);
        $this->assertStringNotContainsString('created_at', $inhalt);
        $this->assertStringNotContainsString('updated_at', $inhalt);
    }

    // Charakterisierung: je Spaltentyp das passende Eingabeelement
    public function test_view_creater_waehlt_eingabeelement_je_spaltentyp(): void
    {
        $inhalt = (new ViewCreater(Beispiel::class))->getForm();

        $this->assertStringContainsString('<input-number name="menge"', $inhalt);
        $this->assertStringContainsString('<input-date name="datum"', $inhalt);
        $this->assertStringContainsString('<input-text name="beschreibung"', $inhalt);
    }

    // OFFEN-2 (roter Test, Defekt): Schema::getColumnType() liefert seit dem Wegfall von Doctrine DBAL
    // 'tinyint' statt 'boolean' (SQLite: 'numeric' statt 'decimal'; MariaDB: 'int'/'varchar'). Der switch in getInput() kennt nur
    // die alten DBAL-Namen, eine boolean-Spalte erhaelt daher nie die Checkbox.
    public function test_view_creater_erzeugt_checkbox_und_euro_feld_fuer_boolean_und_decimal_spalte(): void
    {
        $inhalt = (new ViewCreater(Beispiel::class))->getForm();

        $this->assertStringContainsString('<checkbox name="aktiv"', $inhalt);
        $this->assertStringContainsString('<input-euro name="preis"', $inhalt);
    }

    // Charakterisierung: Label ohne "_id"-Suffix
    public function test_view_creater_bildet_labels_aus_spaltennamen(): void
    {
        $inhalt = (new ViewCreater(Beispiel::class))->getForm();

        $this->assertStringContainsString('<label for="kunde_id">Kunde</label>', $inhalt);
    }

    // Charakterisierung
    public function test_livewire_view_creater_erzeugt_formular(): void
    {
        $inhalt = (new LivewireViewCreater(Beispiel::class))->getForm();

        $this->assertStringStartsWith("<div>\n", $inhalt);
        $this->assertStringContainsString('wire:submit.prevent="formSubmit"', $inhalt);
        $this->assertStringContainsString('name', $inhalt);
    }

    // Charakterisierung
    public function test_livewire_class_creater_erzeugt_gueltige_php_klasse(): void
    {
        $inhalt = (new LivewireClassCreater('kunde', 'Liste', Beispiel::class))->getClass();

        $this->assertStringContainsString('namespace Module\Kunde\Livewire;', $inhalt);
        $this->assertStringContainsString('class ListeLW extends Component', $inhalt);
        token_get_all($inhalt, TOKEN_PARSE);
    }

    // Charakterisierung: module:view schreibt die View in den Ordner des aktiven Moduls
    public function test_view_befehl_schreibt_blade_datei(): void
    {
        $basis = $this->nutzeTemporaeresProjekt();
        $this->artisan('module:create', ['modulName' => 'kunde'])->assertSuccessful();

        $this->artisan('module:view', ['viewName' => 'beispiel/form', 'modelName' => Beispiel::class])->assertSuccessful();

        $this->assertFileExists($basis.'/module/kunde/Resources/views/beispiel/form.blade.php');
    }

    // Charakterisierung: module:livewire schreibt View und Klasse
    public function test_livewire_befehl_schreibt_view_und_klasse(): void
    {
        $basis = $this->nutzeTemporaeresProjekt();
        $this->artisan('module:create', ['modulName' => 'kunde'])->assertSuccessful();

        $this->artisan('module:livewire', ['viewName' => 'liste', 'modelName' => Beispiel::class])
            ->expectsOutputToContain('Livewire Komponente wurde erstellt!')
            ->assertSuccessful();

        $this->assertFileExists($basis.'/module/kunde/Resources/views/livewire/liste.blade.php');
        $this->assertFileExists($basis.'/module/kunde/Livewire/ListeLW.php');
    }
}
