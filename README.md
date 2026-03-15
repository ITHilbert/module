# IT-Hilbert Module für Laravel

Dieses Paket dient zum Erstellen und Verwalten von Modulen in einer Laravel-Applikation. Es ermöglicht eine saubere Trennung von Funktionalitäten in eigenständige Modul-Ordner.

## Installation

1. Füge das Paket zu deiner `composer.json` hinzu:
```json
"require": {
    "ithilbert/module": "dev-main"
}
```

2. Registriere den ServiceProvider in `config/app.php` (falls Autodiscovery nicht genutzt wird):
```php
ITHilbert\Module\ModuleServiceProvider::class,
```

3. Veröffentliche die Konfiguration:
```bash
php artisan vendor:publish --provider="ITHilbert\Module\ModuleServiceProvider"
```

## Grundlagen: Das aktive Modul

Viele Befehle dieses Pakets beziehen sich auf das "aktive Modul". Dies verhindert, dass bei jedem Befehl der Modulname erneut eingegeben werden muss.

- **Modul setzen**: `php artisan module:set {Name}`
- **Aktuelles Modul abfragen**: `php artisan module:get`

## Artisan Befehle

### Modul-Verwaltung
- `php artisan module:create {Name}`: Erstellt eine neue Modul-Struktur unter `app/Modules/{Name}`.
- `php artisan module:set {Name}`: Setzt das Modul, mit dem aktuell gearbeitet wird.
- `php artisan module:get`: Zeigt den Namen des aktuell gesetzten Moduls an.

### Generatoren (beziehen sich auf das aktive Modul)
- `php artisan module:model {Name}`: Erstellt ein Model im aktiven Modul.
- `php artisan module:controller {Name}`: Erstellt einen Controller im aktiven Modul.
- `php artisan module:migration {Name}`: Erstellt eine Migration für das aktive Modul.
- `php artisan module:view {Name}`: Erstellt eine Blade-View im Modul.
- `php artisan module:config {Name}`: Erstellt eine Konfigurationsdatei für das Modul.
- `php artisan module:livewire {Name}`: Erstellt eine Livewire-Komponente inklusive View.

### Build-Prozess
- `php artisan module:mix`: Fügt die Modul-Assets zur `webpack.mix.js` hinzu, um sie automatisch zu überwachen und zu kompilieren.

## Konfiguration

In der `config/module.php` können Standard-CSS-Klassen für generierte Formulare und Komponenten festgelegt werden:

- `row_classes`: Klassen für Reihen.
- `col_label_classes`: Klassen für Label-Spalten.
- `col_input_classes`: Klassen für Input-Spalten.
