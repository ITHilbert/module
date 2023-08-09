<?php

namespace ITHilbert\Module\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MixCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:mix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fügt zur "webpack.mix.js" die Überwachung der Module hinzu';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Inhalte aus webpack.mix.js einlesen
        $mixFilePath = base_path('webpack.mix.js');
        $mixFileContent = File::get($mixFilePath);

        // 2. Alles nach dem Marker löschen oder Marker hinzufügen, falls er nicht existiert
        $marker = '//Module (keine manuellen Einträge darunter einfügen)';
        $markerPosition = strpos($mixFileContent, $marker);

        if ($markerPosition !== false) {
            // Wenn Marker gefunden, alles nach ihm löschen
            $mixFileContent = substr($mixFileContent, 0, $markerPosition + strlen($marker)) . "\n";
        } else {
            // Wenn Marker nicht gefunden, ihn am Ende hinzufügen
            $mixFileContent .= $marker . "\n";
        }

        // 3. Den generierten Code erstellen
        $directories = File::directories(base_path('module'));

        if(!empty($directories)) {
            $modules = array_map(function($directory) {
                return basename($directory);
            }, $directories);

            foreach($modules as $modul) {
                $mixFileContent .= 'mix.copy("module/' . $modul .'/Config", "config");'."\n";
                $mixFileContent .= 'mix.copy("module/' . $modul .'/Public", "public");'."\n";
            }
        }

        // 4. Die aktualisierten Inhalte zurück in webpack.mix.js schreiben
        File::put($mixFilePath, $mixFileContent);

        $this->info("webpack.mix.js angepasst");
    }
}
