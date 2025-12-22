<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\AccessDuration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Skip payment methods if they already exist
        if (PaymentMethod::count() == 0) {
            // Create default payment methods
            PaymentMethod::create([
            'name' => 'TNM Mpamba',
            'code' => 'tnm_mpamba',
            'type' => 'mobile_money',
            'region' => 'malawi',
            'currency' => 'MWK',
            'instructions' => 'Send money to the TNM Mpamba number below and upload screenshot of the transaction.',
            'requirements' => [
                'phone_number' => '+265123456789',
                'merchant_code' => '12345',
            ],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        PaymentMethod::create([
            'name' => 'Airtel Money',
            'code' => 'airtel_money',
            'type' => 'mobile_money',
            'region' => 'malawi',
            'currency' => 'MWK',
            'instructions' => 'Send money to the Airtel Money number below and upload screenshot of the transaction.',
            'requirements' => [
                'phone_number' => '+265987654321',
                'merchant_code' => '54321',
            ],
            'is_active' => true,
            'sort_order' => 2,
        ]);

        PaymentMethod::create([
            'name' => 'Bank Transfer',
            'code' => 'bank_transfer',
            'type' => 'bank_transfer',
            'region' => 'malawi',
            'currency' => 'MWK',
            'instructions' => 'Transfer money to the bank account below and upload screenshot of the transaction or enter reference number.',
            'requirements' => [
                'bank_name' => 'National Bank of Malawi',
                'account_name' => 'StudySeco Secondary School',
                'account_number' => '1234567890123',
                'branch' => 'Blantyre Branch',
            ],
            'is_active' => true,
            'sort_order' => 3,
        ]);
        }

        // Create default access durations if they don't exist
        if (AccessDuration::count() == 0) {
        AccessDuration::create([
            'name' => '1 Week Access',
            'description' => 'Perfect for trying out our platform',
            'days' => 7,
            'price' => 500.00,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        AccessDuration::create([
            'name' => '1 Month Access',
            'description' => 'Most popular option for students',
            'days' => 30,
            'price' => 1500.00,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        AccessDuration::create([
            'name' => '3 Months Access',
            'description' => 'Great value for term-long access',
            'days' => 90,
            'price' => 4000.00,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        AccessDuration::create([
            'name' => '6 Months Access',
            'description' => 'Best for semester preparation',
            'days' => 180,
            'price' => 7500.00,
            'is_active' => true,
            'sort_order' => 4,
        ]);

        AccessDuration::create([
            'name' => '1 Year Access',
            'description' => 'Complete academic year access',
            'days' => 365,
            'price' => 12000.00,
            'is_active' => true,
            'sort_order' => 5,
        ]);
        }
    }
}
