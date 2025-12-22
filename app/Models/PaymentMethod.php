<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type',
        'region',
        'currency',
        'instructions',
        'requirements',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'requirements' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'icon', 
        'color',
        'description',
        'account_details'
    ];

    // Scope for active payment methods
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    // Get icon based on method code
    public function getIconAttribute()
    {
        return match($this->code) {
            'tnm_mpamba' => '📱',
            'airtel_money' => '📱', 
            'bank_transfer' => '🏦',
            'mukuru' => '💰',
            'hello_paisa' => '💳',
            'worldremit' => '🌍',
            'remitly' => '💸',
            'western_union' => '⚡',
            'moneygram' => '💵',
            'paypal' => '🅿️',
            default => '💳'
        };
    }

    // Get color theme based on method code
    public function getColorAttribute()
    {
        return match($this->code) {
            'tnm_mpamba' => 'from-blue-500 to-blue-600',
            'airtel_money' => 'from-red-500 to-red-600',
            'bank_transfer' => 'from-green-500 to-green-600',
            'mukuru' => 'from-yellow-500 to-yellow-600',
            'hello_paisa' => 'from-purple-500 to-purple-600',
            'worldremit' => 'from-indigo-500 to-indigo-600',
            'remitly' => 'from-teal-500 to-teal-600',
            'western_union' => 'from-yellow-600 to-orange-600',
            'moneygram' => 'from-blue-600 to-purple-600',
            'paypal' => 'from-blue-400 to-blue-500',
            default => 'from-gray-500 to-gray-600'
        };
    }

    // Scope for region-specific methods
    public function scopeForRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    // Get enrollment payments using this payment method
    public function enrollmentPayments(): HasMany
    {
        return $this->hasMany(EnrollmentPayment::class);
    }

    // Get price per subject for this payment method's currency
    public function getSubjectPriceAttribute()
    {
        return match($this->currency) {
            'MWK' => 50000,
            'ZAR' => 350,
            'USD' => 25,
            default => 50000
        };
    }

    // Get description based on method
    public function getDescriptionAttribute()
    {
        return match($this->code) {
            'tnm_mpamba' => 'Send money via TNM Mpamba mobile money',
            'airtel_money' => 'Send money via Airtel Money mobile service',
            'bank_transfer' => 'Transfer money directly to our bank account',
            'mukuru' => 'Send money via Mukuru money transfer service',
            'hello_paisa' => 'Send money via Hello Paisa service',
            'worldremit' => 'International money transfer via WorldRemit',
            'remitly' => 'International money transfer via Remitly',
            'western_union' => 'Send money via Western Union',
            'moneygram' => 'Send money via MoneyGram',
            'paypal' => 'Pay securely with your PayPal account',
            default => 'Complete payment using this method'
        };
    }

    // Get account details based on method
    public function getAccountDetailsAttribute()
    {
        return match($this->code) {
            'tnm_mpamba' => 'Send to: +265 99 123 4567 (StudySeco)',
            'airtel_money' => 'Send to: +265 88 123 4567 (StudySeco)',
            'bank_transfer' => 'Account: 1234567890, Bank: Standard Bank',
            'mukuru' => 'Recipient: StudySeco, Country: Malawi',
            'hello_paisa' => 'Recipient: StudySeco Education',
            'worldremit' => 'Recipient: StudySeco, Malawi',
            'remitly' => 'Recipient: StudySeco Education',
            'western_union' => 'Recipient: StudySeco, Lilongwe, Malawi',
            'moneygram' => 'Recipient: StudySeco, Lilongwe, Malawi',
            'paypal' => 'Pay to: payments@studyseco.com',
            default => 'Contact support for payment details'
        };
    }
}
