# Module

Zum erstellen und verwalten von Laravel Modulen


## Installation
```
composer require ithilbert/module
```

### config/app.php
Den Punkt Providers um folgenden Eintrag ergänzen:
```
\ITHilbert\Module\ModuleServiceProvider::class,
```

## Neues Modul erstellen
```
php artisan module:create modulname
```

## Verzeichnisse überwachen
```
php artisan module:mix
```
Fügt Einträge in die "webpack.mix.js" ein. So werden bestimmte Änderungen im Modul direkt nach Laravel kopiert.


### ToDo


### Author
IT-Hilbert GmbH

Access, Excel, VBA und Web Programmierungen

Homepage: [IT-Hilbert.com](https://www.IT-Hilbert.com) 
