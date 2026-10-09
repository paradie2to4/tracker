<?php

namespace App\Enums;

enum LocationType: string
{
    case Factory = 'factory';
    case Warehouse = 'warehouse';
    case DistributionCentre = 'distribution_centre';
    case Store = 'store';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Factory => 'Factory / production site',
            self::Warehouse => 'Warehouse',
            self::DistributionCentre => 'Distribution centre',
            self::Store => 'Shop / pharmacy',
            self::Other => 'Other',
        };
    }
}
