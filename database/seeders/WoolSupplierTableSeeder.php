<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WoolSupplierTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Highland Wool Co.',
                'contact_person' => 'Robert MacLeod',
                'phone' => '+44 20 7123 4567',
                'email' => 'robert@highlandwool.com',
                'address' => '123 Highland Road, Edinburgh, Scotland',
                'tax_id' => 'GB123456789',
                'bank_account' => 'GB29NWBK60161331926819',
                'notes' => 'Premium merino wool supplier, 20+ years experience',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'Alpaca Wool Ltd',
                'contact_person' => 'Maria Rodriguez',
                'phone' => '+51 1 234 5678',
                'email' => 'maria@alpacawool.com',
                'address' => '456 Alpaca Street, Lima, Peru',
                'tax_id' => 'PE12345678901',
                'bank_account' => 'PE12345678901234567890',
                'notes' => 'Specializes in baby alpaca wool, sustainable farming',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'Cashmere World',
                'contact_person' => 'Ahmed Hassan',
                'phone' => '+976 11 234 567',
                'email' => 'ahmed@cashmereworld.com',
                'address' => '789 Cashmere Road, Ulaanbaatar, Mongolia',
                'tax_id' => 'MN123456789',
                'bank_account' => 'MN1234567890123456',
                'notes' => 'Premium cashmere wool, direct from herders',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'Merino Wool Trading',
                'contact_person' => 'Sarah Williams',
                'phone' => '+61 2 3456 7890',
                'email' => 'sarah@merinowool.com',
                'address' => '321 Merino Street, Melbourne, Australia',
                'tax_id' => 'AU12345678901',
                'bank_account' => 'AU12345678901234567890',
                'notes' => 'Fine merino wool, organic certified',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'Wool Craft International',
                'contact_person' => 'John Smith',
                'phone' => '+1 212 345 6789',
                'email' => 'john@woolcraft.com',
                'address' => '555 Wool Avenue, New York, USA',
                'tax_id' => 'US123456789',
                'bank_account' => 'US12345678901234567890',
                'notes' => 'Wholesale wool distributor, multiple sources',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'Shetland Wool Co.',
                'contact_person' => 'Fiona MacDonald',
                'phone' => '+44 1595 123456',
                'email' => 'fiona@shetlandwool.com',
                'address' => '789 Shetland Road, Lerwick, Scotland',
                'tax_id' => 'GB987654321',
                'bank_account' => 'GB29NWBK60161331926820',
                'notes' => 'Traditional Shetland wool, hand-spun available',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'Wool Blend Solutions',
                'contact_person' => 'Michael Chen',
                'phone' => '+86 10 1234 5678',
                'email' => 'michael@woolblend.com',
                'address' => '888 Wool Street, Beijing, China',
                'tax_id' => 'CN123456789012',
                'bank_account' => 'CN12345678901234567890',
                'notes' => 'Specializes in wool blends and mixed fibers',
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => Carbon::now()->subDays(30),
            ],
        ];

        DB::table('wool_suppliers')->insert($suppliers);
    }
}
