# IT-Hilbert Module für Laravel

Dieses Paket dient zum Erstellen und Verwalten von Modulen in einer Laravel-Applikation.
Es erzeugt Modul-Strukturen nach dem Clean-Architecture-Prinzip (Domain / Application / Infrastructure).

## Installation

1. Installiere das Paket über Composer:
```bash
composer require ithilbert/module
```

2. Registriere den ServiceProvider in `config/app.php` (falls Autodiscovery nicht genutzt wird):
```php
ITHilbert\Module\ModuleServiceProvider::class,
```

3. Veröffentliche die Konfiguration:
```bash
php artisan vendor:publish --provider="ITHilbert\Module\ModuleServiceProvider"
```

## Modul-Speicherort

Module werden unter `/module/{name}/` (Projektroot, kleingeschrieben) abgelegt — **nicht** unter `app/Modules/`.

In der `composer.json` des Projekts muss der PSR-4-Eintrag ergänzt werden:
```json
"autoload": {
    "psr-4": {
        "Module\\": "module/"
    }
}
```

## Erzeugte Verzeichnisstruktur

```
module/{name}/
├── config/
├── controllers/               # Dünne HTTP-Controller (5–10 Zeilen/Methode)
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   └── web.php
├── resources/
│   ├── lang/de/
│   ├── lang/en/
│   └── views/
├── public/
│
├── Domain/                    # Fachdomäne — kennt weder HTTP noch DB-Details
│   ├── Models/                # Eloquent-Models (final, $fillable, SoftDeletes)
│   ├── Enums/                 # Backed Enums für fachliche Zustände
│   ├── ValueObjects/          # Wertobjekte (final readonly)
│   ├── Events/                # Domain-Events
│   └── Exceptions/            # Fachliche Exceptions
│
├── Application/               # Anwendungsfälle — orchestriert Domain
│   ├── UseCases/              # Ein UseCase = ein Anwendungsfall (final, execute())
│   ├── Ports/                 # Interfaces für externe Dienste
│   ├── DTOs/                  # Datentransfer-Objekte (final readonly)
│   └── Requests/              # Form Requests (Validierung)
│
└── Infrastructure/            # Implementierungen der Ports
    ├── Adapters/              # Konkrete Adapter (Mail, Payment, Storage ...)
    └── Repositories/          # Eloquent-Repository-Implementierungen
```

## Namespace-Konvention

```
Module\<ModulName>\Domain\Models\<ClassName>
Module\<ModulName>\Domain\Enums\<ClassName>
Module\<ModulName>\Application\UseCases\<ClassName>
Module\<ModulName>\Application\Ports\<ClassName>
Module\<ModulName>\Infrastructure\Adapters\<ClassName>
Module\<ModulName>\Controllers\<ClassName>
```

## Artisan Befehle

### Modul-Verwaltung
- `php artisan module:create {Name}`: Erstellt eine neue Modul-Struktur unter `/module/{name}/` inklusive aller Clean-Architecture-Schichten.
- `php artisan module:set {Name}`: Setzt das Modul, mit dem aktuell gearbeitet wird.
- `php artisan module:get`: Zeigt den Namen des aktuell gesetzten Moduls an.

### Generatoren (beziehen sich auf das aktive Modul)
- `php artisan module:model {Name}`: Erstellt ein Model im aktiven Modul (`Domain/Models/`).
- `php artisan module:controller {Name}`: Erstellt einen dünnen Controller im aktiven Modul.
- `php artisan module:migration {Name}`: Erstellt eine Migration für das aktive Modul.
- `php artisan module:view {Name}`: Erstellt eine Blade-View im Modul.
- `php artisan module:config {Name}`: Erstellt eine Konfigurationsdatei für das Modul.

## Konfiguration

In der `config/module.php` können Standard-CSS-Klassen für generierte Formulare und Komponenten festgelegt werden:

- `row_classes`: Klassen für Reihen.
- `col_label_classes`: Klassen für Label-Spalten.
- `col_input_classes`: Klassen für Input-Spalten.

## Architektur-Regeln (bindend)

- **Controller dünn:** max. 5–10 Zeilen pro Methode, delegiert immer an einen UseCase.
- **UseCases:** `final`-Klasse, eine `execute()`-Methode = ein Anwendungsfall.
- **Models:** `final`, `$fillable` (kein `$guarded`), `SoftDeletes`, kein Geschäftslogik.
- **Enums** statt magischer Strings für fachliche Zustände.
- **Form Requests** für alle Validierungen — kein `$request->all()`.
- **Ports + Adapters** für externe Dienste (Mail, Payment, Storage etc.).
