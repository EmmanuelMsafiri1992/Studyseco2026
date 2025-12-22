<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Models\PaymentMethod;
use Illuminate\Console\Command;

class CheckExtensionIssue extends Command
{
    protected $signature = 'debug:extension';
    protected $description = 'Debug extension payment methods issue';

    public function handle()
    {
        $enrollment = Enrollment::first();
        
        if (!$enrollment) {
            $this->error('No enrollment found');
            return;
        }
        
        $this->info('Enrollment details:');
        $this->info("- Region: {$enrollment->region}");
        $this->info("- Currency: {$enrollment->currency}");
        
        // Fix empty region
        if (empty($enrollment->region)) {
            $this->warn('Enrollment has empty region. Setting to malawi...');
            $enrollment->region = 'malawi';
            $enrollment->save();
            $this->info('Updated enrollment region to: malawi');
        }
        
        // Fix currency mismatch - if region is malawi but currency is not MWK
        if ($enrollment->region === 'malawi' && $enrollment->currency !== 'MWK') {
            $this->warn("Currency mismatch! Region: {$enrollment->region}, Currency: {$enrollment->currency}");
            $this->warn('Updating currency to MWK for Malawi region...');
            $enrollment->currency = 'MWK';
            $enrollment->save();
            $this->info('Updated enrollment currency to: MWK');
        }
        
        $this->info("\nAll payment methods:");
        PaymentMethod::all(['id', 'name', 'region', 'is_active'])->each(function($pm) {
            $status = $pm->is_active ? 'Active' : 'Inactive';
            $this->info("- ID: {$pm->id}, Name: {$pm->name}, Region: {$pm->region}, Status: {$status}");
        });
        
        $this->info("\nFiltered payment methods for this enrollment:");
        $paymentMethods = PaymentMethod::where('region', $enrollment->region)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'region', 'is_active']);
            
        if ($paymentMethods->isEmpty()) {
            $this->error('No payment methods found for region: ' . $enrollment->region);
        } else {
            $paymentMethods->each(function($pm) {
                $this->info("- ID: {$pm->id}, Name: {$pm->name}, Region: {$pm->region}");
            });
        }
        
        return Command::SUCCESS;
    }
}