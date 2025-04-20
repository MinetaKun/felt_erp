<?php

namespace Database\Seeders;

use App\Models\PettyCashCategory;
use App\Models\PettyCashTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PettyCashTransactionSeeder extends Seeder
{
    public function run()
    {
        // Get all categories
        $categories = PettyCashCategory::all();

        // Get some users
        $users = User::limit(5)->get();

        if ($users->isEmpty()) {
            throw new \Exception("No users found. Please seed users first.");
        }

        if ($categories->isEmpty()) {
            throw new \Exception("No categories found. Please seed petty cash categories first.");
        }

        // Starting balance
        $startingBalance = 50000.00;
        $currentBalance = $startingBalance;

        // Create opening balance transaction
        PettyCashTransaction::create([
            'transaction_date' => Carbon::now()->subMonth(),
            'bs_date' => Carbon::now()->subMonth()->format('Y-m-d'),
            'category_id' => $categories->where('name', 'CASH ON HAND')->first()->id,
            'particulars' => 'Opening Balance',
            'cash_in' => $startingBalance,
            'cash_out' => 0,

        ]);

        // Generate random transactions for the past 3 months
        $transactionTypes = [
            ['type' => 'expense', 'categories' => $categories->where('type', 'expense')],
            ['type' => 'income', 'categories' => $categories->where('type', 'income')],
        ];

        $particulars = [
            'Payment for office supplies',
            'Water bill payment',
            'Fuel purchase',
            'Electricity bill',
            'Production materials',
            'Equipment maintenance',
            'Staff wages',
            'Employee advance',
            'Monthly salary payment',
            'Office expenses',
            'Stationery purchase',
            'Advance return',
            'Performance bonus',
            'Asset purchase',
            'Miscellaneous expenses',
        ];

        $vatItems = [
            'PRODUCTION SUPPLIES',
            'FIXED ASSETS',
            'STATIONERY',
            'CURRENT ASSETS'
        ];

        for ($i = 0; $i < 100; $i++) {
            $daysAgo = rand(1, 90);
            $transactionDate = Carbon::now()->subDays($daysAgo);

            $type = $transactionTypes[rand(0, 1)];
            $category = $type['categories']->random();

            $isExpense = $type['type'] === 'expense';
            $amount = $isExpense ? rand(100, 10000) : rand(1000, 50000);

            // Apply VAT for certain expense categories
            $vatPercentage = 0;
            $vatAmount = 0;
            $isVatIncluded = false;

            if ($isExpense && in_array($category->name, $vatItems)) {  // Fixed: Added missing closing parenthesis
                $vatPercentage = 13;
                $isVatIncluded = (bool)rand(0, 1);

                if ($isVatIncluded) {
                    // VAT is included in the amount
                    $vatAmount = $amount - ($amount / (1 + ($vatPercentage / 100)));
                } else {
                    // VAT is added to the amount
                    $vatAmount = $amount * ($vatPercentage / 100);
                }
            }

            $transaction = [
                'transaction_date' => $transactionDate,
                'bs_date' => $transactionDate->format('Y-m-d'),
                'pan_bill_no' => rand(0, 1) ? 'PAN-' . rand(1000, 9999) : null,
                'est_bill_no' => rand(0, 1) ? 'EST-' . rand(1000, 9999) : null,
                'category_id' => $category->id,
                'particulars' => $particulars[array_rand($particulars)] . ' - ' . $transactionDate->format('M Y'),
                'cash_in' => $isExpense ? 0 : $amount,
                'cash_out' => $isExpense ? $amount : 0,
                'vat_percentage' => $vatPercentage > 0 ? $vatPercentage : null,
                'vat_amount' => $vatAmount,
                'is_vat_included' => $isVatIncluded,
                'reference_no' => 'REF-' . strtoupper(uniqid()),
                'notes' => rand(0, 1) ? 'Transaction notes for reference' : null,

            ];

            PettyCashTransaction::create($transaction);

            // Update balance (just for simulation, actual balance would be calculated in the app)
            $currentBalance += $transaction['cash_in'] - $transaction['cash_out'];
        }

        $this->command->info('Successfully seeded petty cash transactions with opening balance of Rs. ' . number_format($startingBalance, 2));
        $this->command->info('Final simulated balance: Rs. ' . number_format($currentBalance, 2));
    }
}
