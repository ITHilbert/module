<?php

declare(strict_types=1);

namespace ITHilbert\Module\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

/**
 * Testmodell für ViewCreater und Livewire-Creater (Tabelle wird im Test angelegt).
 */
final class Beispiel extends Model
{
    protected $table = 'beispiele';
}
