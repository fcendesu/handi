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
                'AŞAĞI BOSTANCI',
                'AYDINKÖY',
                'GAYRETKÖY',
                'GÜNEŞKÖY',
                'İSMET PAŞA',
                'KALKANLI',
                'MEVLEVİ',
                'SERHATKÖY',
                'ŞAHİNLER',
                'YUKARI BOSTANCI',
                'YUVACIK',
                'ZÜMRÜTKÖY',
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
                'AĞIRDAĞ',
                'AKÇİÇEK',
                'AŞAĞI DİKMEN',
                'AŞAĞI TAŞKENT',
                'BOĞAZKÖY',
                'DAĞYOLU',
                'GÖÇERİ',
                'GÜNGÖR',
                'KÖMÜRCÜ',
                'PINARBAŞI',
                'ŞİRİNEVLER',
                'YUKARI DİKMEN',
                'YUKARI TAŞKENT',
            ],
            'ESENTEPE' => [
                'BAHÇELİ',
                'BEŞPARMAK',
                'MERKEZ',
                'KARAAĞAÇ',
            ],
            'KARMİ' => [
                'KARAMAN',
            ],
            'LAPTA' => [
                'ADATEPE',
                'AKDENİZ',
                'ALEMDAĞ',
                'ÇAMLIBEL',
                'GEÇİTKÖY',
                'HİSARKÖY',
                'KARPAŞA',
                'KARŞIYAKA',
                'KAYALAR',
                'KILIÇARSLAN',
                'KOCATEPE',
                'KORUÇAM',
                'KOZAN',
                'ÖZHAN',
                'SADRAZAMKÖY',
                'TEPEBAŞI',
                'TINAZTEPE',
                'TÜRK',
                'YAVUZ',
            ],
            'MERKEZ' => [
                'AŞAĞI GİRNE',
                'AŞAĞI KARAMAN',
                'BEYLERBEYİ',
                'DOĞANKÖY',
                'EDREMİT',
                'KARAKUM',
                'KARAOĞLANOĞLU',
                'OZANKÖY',
                'YUKARI GİRNE',
                'ZEYTİNLİK KESİM',
                'ZEYTİNLİK KÖY',
            ],
        ],
        'LEFKE' => [
            'MERKEZ' => [
                'BADEMLİKÖY',
                'BAĞLIKÖY',
                'CENGİZKÖY',
                'ÇAMLIKÖY',
                'DENİZLİ',
                'DOĞANCI',
                'GAZİVEREN',
                'GEMİKONAĞI',
                'LEFKE',
                'TAŞPINAR',
                'YEDİDALGA',
                'YEŞİLIRMAK',
                'YEŞİLYURT',
            ],
        ],
        'LEFKOŞA' => [
            'AKINCILAR' => [
                'MERKEZ',
            ],
            'ALAYKÖY' => [
                'ALAYKÖY ORGANİZE SANAYİ BÖLGESİ',
                'MERKEZ',
                'TÜRKELİ',
                'YILMAZKÖY',
            ],
            'DEĞİRMENLİK' => [
                'BAHÇELİEVLER',
                'BALIKESİR',
                'BAŞPINAR',
                'BEYKÖY',
                'CAMİALTI',
                'CİHANGİR',
                'ÇUKUROVA',
                'DEMİRHAN',
                'DİLEKKAYA',
                'DÜZOVA',
                'ERDEMLİ',
                'GAZİKÖY',
                'GÖKHAN',
                'KALAVAÇ',
                'KIRIKKALE',
                'KIRKLAR',
                'MEHMETÇİK',
                'MERİÇ',
                'MİNARELİKÖY',
                'SARAY',
                'TEPEBAŞI',
                'YENİCEKÖY',
                'YİĞİTLER',
            ],
            'GÖNYELİ' => [
                'KANLIKÖY',
                'MERKEZ',
                'YENİKENT',
            ],
            'MERKEZ' => [
                'ABDİ ÇAVUŞ',
                'AKKAVUK',
                'ARABAHMET',
                'AYYILDIZ',
                'ÇAĞLAYAN',
                'GÖÇMENKÖY',
                'HAMİTKÖY',
                'HASPOLAT',
                'HASPOLAT ORGANİZE SANAYİ BÖLGESİ',
                'HAYDARPAŞA',
                'İBRAHİMPAŞA',
                'İPLİKPAZARI',
                'KAFESLİ',
                'KARAMANZADE',
                'KIZILBAŞ (KIZILAY)',
                'KÖŞKLÜÇİFTLİK',
                'KUMSAL',
                'KÜÇÜK ESNAF SANAYİ BÖLGESİ',
                'KÜÇÜK KAYMAKLI',
                'MAHMUTPAŞA',
                'MARMARA',
                'METEHAN',
                'ORGANİZE SANAYİ BÖLGESİ',
                'ORTAKÖY',
                'SELİMİYE',
                'SURLARİÇİ KÜÇÜK ESNAF SANAYİ BÖLGESİ',
                'TAŞKINKÖY',
                'YENİCAMİ',
                'YENİŞEHİR',

            ],
        ],
        'MAĞUSA' => [
            'AKDOĞAN' => [
                'MERKEZ',
            ],
            'BEYARMUDU' => [
                'GÜVENCİNLİK ORGANİZE SANAYİ BÖLGESİ',
                'MERKEZ',
                'ÇAYÖNÜ',
                'GÜVERCİNLİK',
                'İNCİRLİ',
                'KÖPRÜLÜ',
                'TÜRKMENKÖY',
            ],
            'GEÇİTKALE' => [
                'ÇAMLICA',
                'ÇINARLI',
                'MERKEZ',
                'MALLIDAĞ',
                'NERGİSLİ',
                'SÜTLÜCE',
                'YAMAÇKÖY',
            ],
            'İNÖNÜ' => [
                'DÖRTYOL',
                'KORKUTELİ',
                'MERKEZ',
                'PİRHAN',

            ],
            'MERKEZ' => [
                'ANADOLU',
                'BAYKAL',
                'CANBULAT',
                'ÇANAKKALE',
                'DUMLUPINAR',
                'HARİKA',
                'KARAKOL',
                'LALA MUSTAFA PAŞA',
                'MARAŞ',
                'MUTLUYAKA',
                'NAMIK KEMAL',
                'ORGANİZE SANAYİ BÖLGESİ',
                'PERTEV PAŞA',
                'PİYALE PAŞA',
                'SAKARYA',
                'SURİÇİ',
                'TUZLA',
                'ZAFER',
                'KÜÇÜK SANAYİ BÖLGESİ',

            ],
            'PAŞAKÖY' => [
                'ASLANKÖY',
                'MERKEZ',
                'KURUDERE',
                'ULUKIŞLA',
            ],
            'PİLE' => [
                'MERKEZ',
            ],
            'SERDARLI' => [
                'ERGENEKON',
                'GÖNENDERE',
                'GÖRNEÇ',
                'PINARLI',
                'MERKEZ',
                'TİRMEN',
            ],
            'TATLISU' => [
                'AKTUNÇ',
                'KÜÇÜKERENKÖY',
                'YALI',
            ],
            'VADİLİ' => [
                'MERKEZ',
                'TURUNÇLU',
            ],
            'YENİBOĞAZİÇİ' => [
                'AKOVA',
                'ALANİÇİ',
                'ATLILAR',
                'KÜÇÜK ESNAF SANAYİ BÖLGESİ',
                'MORMENEKŞE',
                'MURATAĞA',
                'SANDALLAR',
                'MERKEZ',
                'YILDIRIM',
                'KÜÇÜK SANAYİ BÖLGESİ',
            ],
            'İSKELE' => [
                'BÜYÜKKONUK' => [
                    'MERKEZ',
                    'KAPLICA',
                    'KİLİTKAYA',
                    'MERSİNLİK',
                    'SAZLIKÖY',
                    'TUZLUCA',
                    'YEDİKONUK',
                    'ZEYBEKKÖY',
                ],
                'DİPKARPAZ' => [
                    'ERSİN PAŞA',
                    'KALEBURNU',
                    'POLAT PAŞA',
                    'SANCAR PAŞA',
                ],
                'KANTARA' => [
                    'MERKEZ',
                ],
                'MEHMETÇİK' => [
                    'BAFRA',
                    'BALALAN',
                    'ÇAYIROVA',
                    'KUMYALI',
                ],
                'MERKEZ' => [
                    'AĞILLAR',
                    'ALTINOVA',
                    'ARDAHAN',
                    'AYGÜN',
                    'BAHÇELER',
                    'BOĞAZ',
                    'BOĞAZİÇİ',
                    'BOĞAZTEPE',
                    'CEVİZLİ',
                    'ERGAZİ',
                    'İSKELE',
                    'KARŞIYAKA',
                    'KALECİK',
                    'KURTULUŞ',
                    'KUZUCUK',
                    'ÖTÜKEN',
                    'SINIRÜSTÜ',
                    'TOPÇUKÖY',
                    'TURNALAR',
                    'YARKÖY',
                ],
                'YENİERENKÖY' => [
                    'ADAÇAY',
                    'AVTEPE',
                    'BOLTAŞLI',
                    'DERİNCE',
                    'ESENKÖY',
                    'GELİNCİK',
                    'KURUOVA',
                    'SİPAHİ',
                    'TAŞLICA',
                    'MERKEZ',
                    'YEŞİLKÖY',
                    'ZİYAMET',
                ],
            ],
        ]
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
