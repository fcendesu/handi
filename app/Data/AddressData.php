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

    // Level 3: Neighborhoods by City and District
    public static array $neighborhoods = [
        'GÜZELYURT' => [
            'MERKEZ' => [
                'AKÇAY',
                'AYDINKÖY',
                'AŞAĞI BOSTANCI',
                'GAYRETKÖY',
                'GÜNEŞKÖY',
                'KALKANLI',
                'MEVLEVİ',
                'SERHATKÖY',
                'YUKARI BOSTANCI',
                'YUVACIK',
                'ZÜMRÜTKÖY',
                'İSMET PAŞA',
                'ŞAHİNLER',
            ],
        ],
        'GİRNE' => [
            'ALSANCAK' => [
                'ILGAZ',
                'MALATYA - İNCESU',
                'YAYLA',
                'YEŞİLOVA',
                'YEŞİLTEPE',
            ],
            'ÇATALKÖY' => [
                'ARAPKÖY',
                'KÜÇÜK ESNAF SANAYİ BÖLGESİ',
                'MERKEZ',
            ],
            'DİKMEN' => [
                'AKÇİÇEK',
                'AĞIRDAĞ',
                'AŞAĞI DİKMEN',
                'AŞAĞI TAŞKENT',
                'BOĞAZKÖY',
                'DAĞYOLU',
                'GÖÇERİ',
                'GÜNGÖR',
                'KÖMÜRCÜ',
                'PINARBAŞI',
                'YUKARI DİKMEN',
                'YUKARI TAŞKENT',
                'ŞİRİNEVLER',
            ],
            'ESENTEPE' => [
                'BAHÇELİ',
                'BEŞPARMAK',
                'KARAAĞAÇ',
                'MERKEZ',
            ],
            'KARMİ' => [
                'KARŞIYAKA',
            ],
            'LAPTA' => [
                'ALSANCAK',
                'CAMLIBEL',
                'KARŞIYAKA',
                'KAYALAR',
                'KÜÇÜK KAYMAKLI',
                'MERKEZ',
                'SADRAZAMKÖY',
                'TATLISU',
                'YEDIKONUK',
            ],
            'MERKEZ' => [
                'BELLAPAIS',
                'ÇIFTLIKKÖY',
                'DOĞANKÖY',
                'EDREMIT',
                'KARAMAN',
                'KARAOĞLANOĞLU',
                'KARAOĞLANOĞLU KÜÇÜK ESNAF SANAYİ BÖLGESİ',
                'KAYALAR',
                'KÜÇÜK KAYMAKLI',
                'OZANKÖY',
                'TATLISU',
                'ZEYTINLIK',
            ],
        ],
        'LEFKE' => [
            'MERKEZ' => [
                'APLICI',
                'BOSTANCI',
                'GEMİKONAĞI',
                'GÜNDOĞAN',
                'GÜZELYALI',
                'KALKITLI',
                'KAPLICA',
                'LEFKE',
                'PERGAMOS',
                'SOLİ',
                'TATLISU',
                'TEMBLOSos',
                'XEROS',
            ],
        ],
        'LEFKOŞA' => [
            'AKINCILAR' => [
                'TÜRKMENKÖY',
            ],
            'ALAYKÖY' => [
                'BİRİNCİ MİL',
                'GÖKHAN',
                'HASPOLAT',
                'İKİNCİ MİL',
                'ORTAKÖY',
                'SÖĞÜTLÜKÖY',
                'ŞAHINLER',
            ],
            'DEĞİRMENLİK' => [
                'BOĞAZ',
                'ERENKÖY',
                'GEÇİTKÖPRÜ',
                'GÖNYELI',
                'HAMİTKÖY',
                'KIONELI',
                'KİONELLI',
                'KÜÇÜK KAYMAKLİ',
                'MINARELIKÖY',
                'OMORFİTA',
                'SULTANÇİFTLİĞİ',
                'TARLABASi',
                'TEMBLOSos',
                'YENİDOĞAN',
            ],
            'GÖNYELİ' => [
                'ATHALASSA',
                'BOSTANCI',
                'FERRİCELER',
                'GÖNYELI',
                'HAMİTKÖY',
                'KİLİTLİBAHÇE',
                'KIONELI',
                'KİONELLI',
                'KÜÇÜK KAYMAKLİ',
                'LOKMACI',
                'METEHANŞEHİR',
                'ORTAKÖY',
                'PİNAR',
                'SÖĞÜTLÜKÖY',
                'TARLABASi',
                'YENİDOĞAN',
            ],
            'MERKEZ' => [
                'ATHALASSA',
                'BAYRAKLİ',
                'BEYLERBEY',
                'CENGİZ TOPEL',
                'FERRİCELER',
                'GÖÇMENLİK',
                'GÖLBASİ',
                'HAMİTKÖY',
                'KÜÇÜK KAYMAKLİ',
                'LEFKOŞA',
                'METEHANŞEHİR',
                'NEOTORİYOS',
                'OMORFİTA',
                'PALOURIOTISSA',
                'SARAYKÖY',
                'SULTANÇİFTLİĞİ',
                'TAKSIM',
                'TARLABASi',
                'TURUNCLU',
                'TÜRK MUKAVEMET TEŞKİLATI',
                'YENİDOĞAN',
                'YUKARİ AYIOS NIKOLAOS',
            ],
        ],
        'MAĞUSA' => [
            'AKDOĞAN' => [
                'AKOVA',
                'AVGOROU',
                'DERİNEIA',
                'KAPLICA',
                'KOMA TOU YIALOU',
                'LEFKONIKO',
                'PRASTIO AVDIMOU',
                'PYLA',
                'STYLLOI',
                'TEMBLOSos',
                'VOUNI',
                'YENİBOĞAZİÇİ',
            ],
            'BEYARMUDU' => [
                'BOGAZ',
                'FRENAROSsa',
                'İSKELE',
                'KOMA TOU YIALOU',
                'KOUKLIA',
                'PARAALİMNİ',
                'PRASTİO AVDIMOU',
                'PYLA',
                'RİZOKARPASO',
                'SİNJİRLİ',
                'STROVİLOSsa',
                'TURKARİA',
                'YENİBOĞAZİÇİ',
            ],
            'GEÇİTKALE' => [
                'ASPROKREMMOSsa',
                'DİKOMO',
                'FRENAROSsa',
                'GEÇITKALE',
                'GİALOUSA',
                'KANTARAos',
                'KOMA TOU YIALOU',
                'KOUKLIA',
                'KTİMA VRYSI',
                'LARNAKAos',
                'MELANARGOSsa',
                'PARAALİMNİ',
                'PRASTİO AVDIMOU',
                'RİZOKARPASO',
                'SYNTRINIos',
                'YALOUSA',
            ],
            'İNÖNÜ' => [
                'FRENAROSsa',
                'GİALOUSA',
                'İNÖNÜ',
                'KANTARAos',
                'KOMA TOU YIALOU',
                'KOUKLIA',
                'LEFKONIKO',
                'PRASTİO AVDIMOU',
                'PYLA',
                'SALAMIS',
                'SYNTRINIos',
                'TEMBLOSos',
                'YALOUSA',
            ],
            'MERKEZ' => [
                'FRENAROSsa',
                'KOMA TOU YIALOU',
                'KOUKLIA',
                'LEFKONIKO',
                'MAĞUSAos',
                'PRASTİO AVDIMOU',
                'PYLA',
                'SALAMIS',
                'TEMBLOSos',
                'YALOUSA',
            ],
        ],
        'İSKELE' => [
            'BÜYÜKKONUK' => [
                'GİALOUSA',
                'KANTARAos',
                'MELANARGOSsa',
                'RİZOKARPASO',
                'SALAMIS',
                'SYNTRINIos',
                'YENİERENKÖY',
            ],
            'DİPKARPAZ' => [
                'DİPKARPAZ',
                'GİALOUSA',
                'KANTARAos',
                'LEFKONIKO',
                'MELANARGOSsa',
                'RİZOKARPASO',
                'YENİERENKÖY',
            ],
            'KANTARA' => [
                'GİALOUSA',
                'KANTARAos',
                'LEFKONIKO',
                'MELANARGOSsa',
                'RİZOKARPASO',
                'YENİERENKÖY',
            ],
            'MEHMETÇİK' => [
                'GİALOUSA',
                'KANTARAos',
                'LEFKONIKO',
                'MEHMETÇİK',
                'MELANARGOSsa',
                'RİZOKARPASO',
                'SALAMIS',
                'SYNTRINIos',
                'YENİERENKÖY',
            ],
            'MERKEZ' => [
                'AKOVA',
                'GİALOUSA',
                'İSKELE',
                'KANTARAos',
                'LEFKONIKO',
                'MELANARGOSsa',
                'RİZOKARPASO',
                'SALAMIS',
                'SYNTRINIos',
                'YENİERENKÖY',
            ],
            'YENİERENKÖY' => [
                'FRENAROSsa',
                'GİALOUSA',
                'KANTARAos',
                'LEFKONIKO',
                'MELANARGOSsa',
                'RİZOKARPASO',
                'SALAMIS',
                'SYNTRINIos',
                'YALOUSA',
                'YENİERENKÖY',
            ],
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

    /**
     * Get neighborhoods for a specific city and district
     */
    public static function getNeighborhoods(string $city, string $district): array
    {
        return self::$neighborhoods[$city][$district] ?? [];
    }

    /**
     * Get all neighborhoods for a specific city
     */
    public static function getNeighborhoodsByCity(string $city): array
    {
        return self::$neighborhoods[$city] ?? [];
    }

    /**
     * Get all neighborhoods formatted for dropdown
     */
    public static function getAllNeighborhoods(): array
    {
        return self::$neighborhoods;
    }

    /**
     * Check if a neighborhood exists for given city and district
     */
    public static function neighborhoodExists(string $city, string $district, string $neighborhood): bool
    {
        $neighborhoods = self::getNeighborhoods($city, $district);
        return in_array($neighborhood, $neighborhoods, true);
    }

    /**
     * Get neighborhood count for a specific city and district
     */
    public static function getNeighborhoodCount(string $city, string $district): int
    {
        return count(self::getNeighborhoods($city, $district));
    }
}
