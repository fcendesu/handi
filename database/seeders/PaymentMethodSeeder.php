<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'name' => 'Nakit',
                'description' => 'Nakit ödeme',
            ],
            [
                'name' => 'Kredi Kartı',
                'description' => 'Kredi kartı ile ödeme',
            ],
            [
                'name' => 'Banka Transferi',
                'description' => 'Banka havalesi ile ödeme',
            ],
            [
                'name' => 'Çek',
                'description' => 'Çek ile ödeme',
            ],
            [
                'name' => 'Taksit',
                'description' => 'Taksitli ödeme',
            ],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::firstOrCreate(
                ['name' => $method['name']],
                $method
            );
        }
    }
}
