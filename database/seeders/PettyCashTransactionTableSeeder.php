<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PettyCashTransactionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transactions = [
            // Cash In Transactions
            [
                'transaction_date' => Carbon::now()->subDays(10),
                'bs_date' => '2080-12-15', // Nepali date format
                'pan_bill_no' => 'PAN123456',
                'est_bill_no' => 'EST789012',
                'category_id' => 1, // Cash on Hand
                'particulars' => 'Initial cash deposit',
                'cash_in' => 50000.00,
                'cash_out' => 0.00,
                'vat_amount' => 0.00,
                'vat_percentage' => 0,
                'is_vat_included' => false,
                'reference_no' => 'REF-INIT-001',
                'notes' => 'Initial petty cash fund setup',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transaction_date' => Carbon::now()->subDays(8),
                'bs_date' => '2080-12-17',
                'pan_bill_no' => 'PAN234567',
                'est_bill_no' => 'EST890123',
                'category_id' => 14, // Advance Return
                'particulars' => 'Employee advance return',
                'cash_in' => 5000.00,
                'cash_out' => 0.00,
                'vat_amount' => 0.00,
                'vat_percentage' => 0,
                'is_vat_included' => false,
                'reference_no' => 'REF-ADV-001',
                'notes' => 'Return of advance by John Doe',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transaction_date' => Carbon::now()->subDays(5),
                'bs_date' => '2080-12-20',
                'pan_bill_no' => 'PAN345678',
                'est_bill_no' => 'EST901234',
                'category_id' => 26, // Refund
                'particulars' => 'Supplier refund',
                'cash_in' => 2500.00,
                'cash_out' => 0.00,
                'vat_amount' => 0.00,
                'vat_percentage' => 0,
                'is_vat_included' => false,
                'reference_no' => 'REF-SUP-001',
                'notes' => 'Refund from ABC Supplies for overcharged items',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Cash Out Transactions
            [
                'transaction_date' => Carbon::now()->subDays(9),
                'bs_date' => '2080-12-16',
                'pan_bill_no' => 'PAN456789',
                'est_bill_no' => 'EST012345',
                'category_id' => 2, // Kitchen Expenses
                'particulars' => 'Kitchen supplies purchase',
                'cash_in' => 0.00,
                'cash_out' => 3500.00,
                'vat_amount' => 350.00,
                'vat_percentage' => 10,
                'is_vat_included' => true,
                'reference_no' => 'REF-KIT-001',
                'notes' => 'Purchase of kitchen supplies from XYZ Store',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transaction_date' => Carbon::now()->subDays(7),
                'bs_date' => '2080-12-18',
                'pan_bill_no' => 'PAN567890',
                'est_bill_no' => 'EST123456',
                'category_id' => 3, // Water
                'particulars' => 'Water bill payment',
                'cash_in' => 0.00,
                'cash_out' => 1200.00,
                'vat_amount' => 120.00,
                'vat_percentage' => 10,
                'is_vat_included' => true,
                'reference_no' => 'REF-WAT-001',
                'notes' => 'Monthly water bill payment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transaction_date' => Carbon::now()->subDays(6),
                'bs_date' => '2080-12-19',
                'pan_bill_no' => 'PAN678901',
                'est_bill_no' => 'EST234567',
                'category_id' => 4, // Fuel
                'particulars' => 'Vehicle fuel',
                'cash_in' => 0.00,
                'cash_out' => 2500.00,
                'vat_amount' => 250.00,
                'vat_percentage' => 10,
                'is_vat_included' => true,
                'reference_no' => 'REF-FUE-001',
                'notes' => 'Fuel for company vehicles',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transaction_date' => Carbon::now()->subDays(4),
                'bs_date' => '2080-12-21',
                'pan_bill_no' => 'PAN789012',
                'est_bill_no' => 'EST345678',
                'category_id' => 5, // Hospitality
                'particulars' => 'Client meeting refreshments',
                'cash_in' => 0.00,
                'cash_out' => 3000.00,
                'vat_amount' => 300.00,
                'vat_percentage' => 10,
                'is_vat_included' => true,
                'reference_no' => 'REF-HOS-001',
                'notes' => 'Refreshments for client meeting',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transaction_date' => Carbon::now()->subDays(3),
                'bs_date' => '2080-12-22',
                'pan_bill_no' => 'PAN890123',
                'est_bill_no' => 'EST456789',
                'category_id' => 6, // Production Supplies
                'particulars' => 'Raw materials purchase',
                'cash_in' => 0.00,
                'cash_out' => 15000.00,
                'vat_amount' => 1500.00,
                'vat_percentage' => 10,
                'is_vat_included' => true,
                'reference_no' => 'REF-PRO-001',
                'notes' => 'Purchase of raw materials for production',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transaction_date' => Carbon::now()->subDays(2),
                'bs_date' => '2080-12-23',
                'pan_bill_no' => 'PAN901234',
                'est_bill_no' => 'EST567890',
                'category_id' => 7, // Repair and Maintenance
                'particulars' => 'Equipment repair',
                'cash_in' => 0.00,
                'cash_out' => 4500.00,
                'vat_amount' => 450.00,
                'vat_percentage' => 10,
                'is_vat_included' => true,
                'reference_no' => 'REF-REP-001',
                'notes' => 'Repair of production equipment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transaction_date' => Carbon::now()->subDay(),
                'bs_date' => '2080-12-24',
                'pan_bill_no' => 'PAN012345',
                'est_bill_no' => 'EST678901',
                'category_id' => 9, // Wages
                'particulars' => 'Daily wage payment',
                'cash_in' => 0.00,
                'cash_out' => 8000.00,
                'vat_amount' => 0.00,
                'vat_percentage' => 0,
                'is_vat_included' => false,
                'reference_no' => 'REF-WAG-001',
                'notes' => 'Payment of daily wages to workers',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('petty_cash_transactions')->insert($transactions);
    }
}
