<?php

declare(strict_types=1);

namespace ITHilbert\Module\Classes;

use Illuminate\Support\Facades\Schema;

/**
 * Zentrale Zuordnung "Spaltentyp -> Feldart" fuer ViewCreater, LivewireViewCreater und LivewireClassCreater.
 *
 * Versteht die alten Doctrine-DBAL-Namen (boolean, decimal, string, ...) und die nativen Typnamen
 * seit Laravel 11 (SQLite, MariaDB/MySQL, PostgreSQL).
 */
enum Feldart: string
{
    case Id = 'id';
    case Zahl = 'zahl';
    case Text = 'text';
    case Datum = 'datum';
    case Datumzeit = 'datumzeit';
    case Boolean = 'boolean';
    case Euro = 'euro';

    /**
     * Spaltenname => Typ (Typname, bei tinyint(1) / bool der Boolean-Typ) fuer alle Spalten einer Tabelle.
     *
     * @return array<string, string>
     */
    public static function typenFuerTabelle(string $tabelle): array
    {
        $types = [];
        foreach (Schema::getColumns($tabelle) as $spalte) {
            $name = (string) $spalte['name'];
            $voll = strtolower((string) ($spalte['type'] ?? ''));
            $typeName = strtolower((string) ($spalte['type_name'] ?? $voll));
            $types[$name] = preg_match('/^tinyint\(1\)/', $voll) === 1 ? 'boolean' : $typeName;
        }

        return $types;
    }

    /**
     * Ordnet einen Spaltentyp (alt oder nativ, auch mit Laenge wie "tinyint(1)" / "decimal(10,2)") einer Feldart zu.
     */
    public static function fuerTyp(string $type): self
    {
        $type = strtolower(trim($type));

        if ($type === 'tinyint(1)') {
            return self::Boolean;
        }

        // Laenge/Praezision und Zusaetze entfernen: "decimal(10,2) unsigned" => "decimal"
        $basis = trim((string) preg_replace('/\(.*?\)|\bunsigned\b|\bzerofill\b/', '', $type));

        return match ($basis) {
            'id' => self::Id,
            'boolean', 'bool' => self::Boolean,
            'decimal', 'numeric', 'euro', 'money' => self::Euro,
            'tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint', 'int2', 'int4', 'int8' => self::Zahl,
            'date' => self::Datum,
            'datetime', 'datetimetz', 'timestamp', 'timestamptz',
            'timestamp without time zone', 'timestamp with time zone' => self::Datumzeit,
            default => self::Text,
        };
    }
}
