<?php

namespace App\Enums;

enum ProductCategory: string
{
    case FoodAndBeverages = 'food_beverages';
    case Pharmaceuticals = 'pharmaceuticals';
    case Agriculture = 'agriculture';
    case Cosmetics = 'cosmetics';
    case Chemicals = 'chemicals';
    case Textiles = 'textiles';
    case Electronics = 'electronics';
    case ConstructionMaterials = 'construction_materials';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::FoodAndBeverages => 'Food & beverages',
            self::Pharmaceuticals => 'Pharmaceuticals',
            self::Agriculture => 'Agricultural produce',
            self::Cosmetics => 'Cosmetics & personal care',
            self::Chemicals => 'Chemicals',
            self::Textiles => 'Textiles & apparel',
            self::Electronics => 'Electronics',
            self::ConstructionMaterials => 'Construction materials',
            self::Other => 'Other',
        };
    }
}
