<?php

declare(strict_types=1);

namespace App\Data;

/**
 * Parsed Address Data from CSV
 * Generated from Address.csv file
 */
class AddressData
{
    // Level 1: Cities
    public static array $cities = [
        'GİRNE',
        'GÜZELYURT',
        'İSKELE',
        'LEFKE',
        'LEFKOŞA',
        'MAĞUSA',
    ];

    // Level 2: Districts by City
    public static array $districts = [
        'GİRNE' => [
            'ALSANCAK',
            'ÇATALKÖY',
            'DİKMEN',
            'ESENTEPE',
            'KARMİ',
            'LAPTA',
            'MERKEZ',
        ],
        'GÜZELYURT' => [
            'MERKEZ',
        ],
        'İSKELE' => [
            'BÜYÜKKONUK',
            'DİPKARPAZ',
            'KANTARA',
            'MEHMETÇİK',
            'MERKEZ',
            'YENİERENKÖY',
        ],
        'LEFKE' => [
            'MERKEZ',
        ],
        'LEFKOŞA' => [
            'AKINCILAR',
            'ALAYKÖY',
            'DEĞİRMENLİK',
            'GÖNYELİ',
            'MERKEZ',
        ],
        'MAĞUSA' => [
            'AKDOĞAN',
            'BEYARMUDU',
            'GEÇİTKALE',
            'İNÖNÜ',
            'MERKEZ',
            'PAŞAKÖY',
            'PİLE',
            'SERDARLI',
            'TATLISU',
            'VADİLİ',
            'YENİBOĞAZİÇİ',
        ],
    ];

    /**
     * Get all cities
     */
    public static function getCities(): array
    {
        return self::$cities;
    }

    /**
     * Get districts for a specific city
     */
    public static function getDistricts(string $city): array
    {
        return self::$districts[$city] ?? [];
    }    /**
         * Get all districts formatted for dropdown
         */
    public static function getAllDistricts(): array
    {
        return self::$districts;
    }
}
