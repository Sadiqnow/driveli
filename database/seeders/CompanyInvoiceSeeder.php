<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\CompanyMatch;
use App\Models\CompanyInvoice;

class CompanyInvoiceSeeder extends Seeder
{
    public function run()
    {
        $companies = Company::all();
        $matches = CompanyMatch::where('status', 'completed')->get();

        if ($companies->isEmpty()) {
            $this->command->info('No companies found. Skipping invoice seeding.');
            return;
        }

        // Create some sample invoices for companies
        foreach ($companies as $company) {
            for ($i = 1; $i <= rand(2, 5); $i++) {
                $amount = rand(50000, 500000);
                $taxAmount = $amount * 0.075; // 7.5% VAT
                $totalAmount = $amount + $taxAmount;

                CompanyInvoice::create([
                    'invoice_number' => 'INV-' . $company->id . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'company_id' => $company->id,
                    'company_match_id' => $matches->isNotEmpty() ? $matches->random()->id : null,
                    'amount' => $amount,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => 0,
                    'total_amount' => $totalAmount,
                    'currency' => 'NGN',
                    'status' => ['draft', 'sent', 'paid'][array_rand(['draft', 'sent', 'paid'])],
                    'due_date' => now()->addDays(rand(7, 30)),
                    'paid_at' => null,
                    'payment_method' => null,
                    'transaction_reference' => null,
                    'description' => 'Transportation services for ' . now()->format('F Y'),
                    'line_items' => json_encode([
                        [
                            'description' => 'Driver assignment fee',
                            'quantity' => 1,
                            'unit_price' => $amount,
                            'total' => $amount,
                        ]
                    ]),
                    'notes' => 'Please pay within due date to avoid penalties.',
                    'created_by' => \App\Models\AdminUser::first()->id ?? 1,
                ]);
            }
        }

        $this->command->info('Company invoices seeded successfully!');
    }
}
