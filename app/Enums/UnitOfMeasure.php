<?php

namespace App\Enums;

enum UnitOfMeasure: string
{
    case Piece = 'pcs';
    case Kilogram = 'kg';
    case Gram = 'g';
    case Tonne = 't';
    case Litre = 'l';
    case Millilitre = 'ml';
    case Box = 'box';
    case Carton = 'carton';
    case Bag = 'bag';
    case Bottle = 'bottle';

    public function label(): string
    {
        return match ($this) {
            self::Piece => 'Pieces (pcs)',
            self::Kilogram => 'Kilograms (kg)',
            self::Gram => 'Grams (g)',
            self::Tonne => 'Tonnes (t)',
            self::Litre => 'Litres (l)',
            self::Millilitre => 'Millilitres (ml)',
            self::Box => 'Boxes',
            self::Carton => 'Cartons',
            self::Bag => 'Bags',
            self::Bottle => 'Bottles',
        };
    }
}
