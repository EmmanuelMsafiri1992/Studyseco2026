<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Skip if payment methods already exist
        if (\App\Models\PaymentMethod::count() > 0) {
            $this->command->info('Payment methods already exist. Skipping.');
            return;
        }
        
        $paymentMethods = [
            // Malawi payment methods
            [
                'name' => 'TNM Mpamba',
                'code' => 'tnm_mpamba',
                'type' => 'mobile_money',
                'region' => 'malawi',
                'currency' => 'MWK',
                'instructions' => 'Send payment to 0991234567. Use your enrollment number as reference.',
                'requirements' => ['reference_number', 'screenshot'],
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Airtel Money',
                'code' => 'airtel_money',
                'type' => 'mobile_money',
                'region' => 'malawi',
                'currency' => 'MWK',
                'instructions' => 'Send payment to 0991234568. Use your enrollment number as reference.',
                'requirements' => ['reference_number', 'screenshot'],
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Bank Transfer',
                'code' => 'bank_transfer',
                'type' => 'bank',
                'region' => 'malawi',
                'currency' => 'MWK',
                'instructions' => 'Transfer to Account: StudySeco Ltd, Account Number: 123456789, Bank: Standard Bank Malawi. Use enrollment number as reference.',
                'requirements' => ['reference_number', 'bank_slip'],
                'is_active' => true,
                'sort_order' => 3
            ],
            
            // South Africa payment methods
            [
                'name' => 'Mukuru',
                'code' => 'mukuru',
                'type' => 'remittance',
                'region' => 'south_africa',
                'currency' => 'ZAR',
                'instructions' => 'Send payment through Mukuru to StudySeco Ltd. Contact support for collection details.',
                'requirements' => ['reference_number', 'receipt'],
                'is_active' => true,
                'sort_order' => 10
            ],
            [
                'name' => 'Hello Paisa',
                'code' => 'hello_paisa',
                'type' => 'digital_wallet',
                'region' => 'south_africa',
                'currency' => 'ZAR',
                'instructions' => 'Send payment through Hello Paisa to StudySeco account.',
                'requirements' => ['reference_number', 'screenshot'],
                'is_active' => true,
                'sort_order' => 11
            ],
            
            // International payment methods
            [
                'name' => 'WorldRemit',
                'code' => 'worldremit',
                'type' => 'remittance',
                'region' => 'international',
                'currency' => 'USD',
                'instructions' => 'Send payment through WorldRemit to StudySeco Ltd, Malawi. Contact support for recipient details.',
                'requirements' => ['reference_number', 'receipt'],
                'is_active' => true,
                'sort_order' => 20
            ],
            [
                'name' => 'Remitly',
                'code' => 'remitly',
                'type' => 'remittance',
                'region' => 'international',
                'currency' => 'USD',
                'instructions' => 'Send payment through Remitly to StudySeco Ltd, Malawi. Contact support for recipient details.',
                'requirements' => ['reference_number', 'receipt'],
                'is_active' => true,
                'sort_order' => 21
            ],
            [
                'name' => 'Western Union',
                'code' => 'western_union',
                'type' => 'remittance',
                'region' => 'international',
                'currency' => 'USD',
                'instructions' => 'Send payment through Western Union to StudySeco Ltd, Malawi. Contact support for recipient details.',
                'requirements' => ['reference_number', 'mtcn'],
                'is_active' => true,
                'sort_order' => 22
            ],
            [
                'name' => 'MoneyGram',
                'code' => 'moneygram',
                'type' => 'remittance',
                'region' => 'international',
                'currency' => 'USD',
                'instructions' => 'Send payment through MoneyGram to StudySeco Ltd, Malawi. Contact support for recipient details.',
                'requirements' => ['reference_number', 'receipt'],
                'is_active' => true,
                'sort_order' => 23
            ],
            [
                'name' => 'PayPal',
                'code' => 'paypal',
                'type' => 'digital_wallet',
                'region' => 'international',
                'currency' => 'USD',
                'instructions' => 'Send payment to payments@studyseco.com through PayPal. Include enrollment number in notes.',
                'requirements' => ['transaction_id', 'screenshot'],
                'is_active' => true,
                'sort_order' => 24
            ]
        ];

        foreach ($paymentMethods as $method) {
            \App\Models\PaymentMethod::create($method);
        }
    }
}
