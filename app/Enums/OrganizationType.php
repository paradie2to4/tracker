<?php

namespace App\Enums;

enum OrganizationType: string
{
    case Manufacturer = 'manufacturer';
    case Distributor = 'distributor';
    case Wholesaler = 'wholesaler';
    case Retailer = 'retailer';
    case LogisticsProvider = 'logistics_provider';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Manufacturer => 'Manufacturer',
            self::Distributor => 'Distributor',
            self::Wholesaler => 'Wholesaler',
            self::Retailer => 'Retailer',
            self::LogisticsProvider => 'Logistics provider',
            self::Other => 'Other',
        };
    }
}
