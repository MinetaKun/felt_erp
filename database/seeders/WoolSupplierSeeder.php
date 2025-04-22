<?php

namespace Database\Seeders;

use App\Models\WoolSupplier;
use Illuminate\Database\Seeder;

class WoolSupplierSeeder extends Seeder
{
    public function run()
    {
        WoolSupplier::create([
            'name' => 'Rajan Dying Wool Supplier',
            'contact_person' => 'Rajan Kumar',
            'phone' => '+91 9876543210',
            'email' => 'rajan@dyingwool.com',
            'address' => '123 Wool Street, Textile Area, Ludhiana, Punjab, India',
            'tax_id' => 'GSTIN123456789',
            'bank_account' => '1234567890123456',
            'notes' => 'Primary wool supplier for high-quality dyed wool',
            'is_active' => true
        ]);

        // Add a few more sample suppliers
        WoolSupplier::create([
            'name' => 'Premium Wool Traders',
            'contact_person' => 'Amit Sharma',
            'phone' => '+91 9876543211',
            'email' => 'amit@premiumwool.com',
            'address' => '456 Wool Market, Industrial Area, Amritsar, Punjab, India',
            'tax_id' => 'GSTIN123456788',
            'bank_account' => '1234567890123457',
            'notes' => 'Specializes in premium quality wool',
            'is_active' => true
        ]);

        WoolSupplier::create([
            'name' => 'Natural Wool Mills',
            'contact_person' => 'Priya Singh',
            'phone' => '+91 9876543212',
            'email' => 'priya@naturalwool.com',
            'address' => '789 Wool Road, Manufacturing Zone, Jalandhar, Punjab, India',
            'tax_id' => 'GSTIN123456787',
            'bank_account' => '1234567890123458',
            'notes' => 'Eco-friendly wool processing',
            'is_active' => true
        ]);
    }
}
