# IT-Hilbert Module für Laravel

Dieses Paket dient zum Erstellen und Verwalten von Modulen in einer Laravel-Applikation.
Es erzeugt Modul-Strukturen nach dem Clean-Architecture-Prinzip (Domain / Application / Infrastructure).

## Installation

1. Installiere das Paket über Composer:
```bash
composer require ithilbert/module
```

2. Der ServiceProvider des Packages wird per Package-Discovery geladen. Ohne Discovery: in `bootstrap/providers.php` eintragen:
```php
ITHilbert\Module\ModuleServiceProvider::class,
```

3. Veröffentliche die Konfiguration:
```bash
php artisan vendor:publish --provider="ITHilbert\Module\ModuleServiceProvider"
```

## Modul-Speicherort

Module werden unter `/module/{name}/` (Projektroot, kleingeschrieben) abgelegt — **nicht** unter `app/Modules/`. Der Namespace beginnt groß: `module:create Rechnung` erzeugt den Ordner `module/rechnung/` mit dem Namespace `Module\Rechnung\`.

Nach `module:create` je Modul zwei Einträge (der Generator gibt beide aus):

1. **PSR-4** in der `composer.json`, danach `composer dump-autoload`:
   ```json
   "autoload": {
       "psr-4": {
           "Module\\Rechnung\\": "module/rechnung/"
       }
   }
   ```
   Ein gemeinsamer Eintrag `"Module\\": "module/"` funktioniert nicht: Er sucht `Module\Rechnung` unter `module/Rechnung/`, der Ordner heißt aber `module/rechnung/`.
2. **ServiceProvider** in `bootstrap/providers.php`: `Module\Rechnung\RechnungServiceProvider::class`.

Ordner mit PHP-Klassen heißen exakt wie ihr Namespace (`Controllers/`, `Requests/`, `Domain/Models/` …) — PSR-4 unterscheidet unter Linux Groß/Klein. Ordner ohne Klassen (`config`, `database`, `public`, `resources`, `routes`) sind kleingeschrieben.

## Erzeugte Verzeichnisstruktur

```
module/{name}/
├── config/
├── Controllers/               # Dünne HTTP-Controller (5–10 Zeilen/Methode)
├── Requests/                  # Form Requests (Validierung)
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
│   ├── Models/                # Eloquent-Models (final, $fillable)
│   ├── Enums/                 # Backed Enums für fachliche Zustände
│   ├── ValueObjects/          # Wertobjekte (final readonly)
│   ├── Events/                # Domain-Events
│   └── Exceptions/            # Fachliche Exceptions
│
├── Application/               # Anwendungsfälle — orchestriert Domain
│   ├── UseCases/              # Ein UseCase = ein Anwendungsfall (final, execute())
│   ├── Ports/                 # Interfaces für externe Dienste
│   └── DTOs/                  # Datentransfer-Objekte (final readonly)
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
Module\<ModulName>\Requests\<ClassName>
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
- `php artisan module:view {viewName} {modelName}`: Erstellt eine Blade-View im Modul.
- `php artisan module:config {Name}`: Erstellt eine Konfigurationsdatei für das Modul.

## Konfiguration

In der `config/module.php` können Standard-CSS-Klassen für generierte Formulare und Komponenten festgelegt werden:

- `row_classes`: Klassen für Reihen.
- `col_label_classes`: Klassen für Label-Spalten.
- `col_input_classes`: Klassen für Input-Spalten.

## Architektur-Regeln (bindend)

- **Controller dünn:** max. 5–10 Zeilen pro Methode, delegiert immer an einen UseCase.
- **UseCases:** `final`-Klasse, eine `execute()`-Methode = ein Anwendungsfall.
- **Models:** `final`, `$fillable` (kein `$guarded`), keine Geschäftslogik. `SoftDeletes` nur bei fachlichem Bedarf — dann auch `$table->softDeletes()` in der Migration.
- **Enums** statt magischer Strings für fachliche Zustände.
- **Form Requests** für alle Validierungen — kein `$request->all()`.
- **Ports + Adapters** für externe Dienste (Mail, Payment, Storage etc.).
