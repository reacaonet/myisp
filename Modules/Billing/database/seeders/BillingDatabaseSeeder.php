<?php

namespace Modules\Billing\Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Modules\Billing\Models\Invoice;
use Modules\CRM\Models\Contract;

class BillingDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        $contracts = Contract::with('plan', 'client')->whereIn('status', ['active', 'suspended'])->get();

        foreach ($contracts as $contract) {
            $monthsToGenerate = $faker->numberBetween(3, 12);
            $baseDate = new \DateTime($contract->activation_date);

            for ($i = 0; $i < $monthsToGenerate; $i++) {
                $dueDate = (clone $baseDate)->modify('+' . $i . ' months');
                $dueDate->setDate($dueDate->format('Y'), $dueDate->format('m'), min($contract->due_day, 28));

                $amount = $contract->plan->price;
                $discount = $contract->discount ?? 0;
                $total = $amount - $discount;

                $isFuture = $dueDate > new \DateTime();
                $isPaid = !$isFuture && $faker->boolean(60);
                $isOverdue = !$isFuture && !$isPaid && $dueDate < new \DateTime() && $faker->boolean(70);

                if ($isFuture) {
                    $status = 'pending';
                    $paidDate = null;
                    $paymentMethod = null;
                    $transactionId = null;
                } elseif ($isPaid) {
                    $status = 'paid';
                    $paidDate = $faker->dateTimeBetween($dueDate, $dueDate->format('Y-m-d') . ' +15 days')->format('Y-m-d');
                    $paymentMethod = $faker->randomElement(['pix', 'boleto', 'credit_card', 'cash']);
                    $transactionId = strtoupper($faker->bothify('TXN-##########'));
                } elseif ($isOverdue) {
                    $status = 'overdue';
                    $paidDate = null;
                    $paymentMethod = null;
                    $transactionId = null;
                } else {
                    $status = 'pending';
                    $paidDate = null;
                    $paymentMethod = null;
                    $transactionId = null;
                }

                $invoiceNumber = 'INV-' . str_pad($contract->client_id, 3, '0', STR_PAD_LEFT) . '-' . $dueDate->format('Ym') . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT);

                Invoice::create([
                    'client_id' => $contract->client_id,
                    'contract_id' => $contract->id,
                    'invoice_number' => $invoiceNumber,
                    'amount' => $amount,
                    'discount' => $discount,
                    'total' => $total,
                    'due_date' => $dueDate->format('Y-m-d'),
                    'dia' => $dueDate->format('d'),
                    'mes' => $dueDate->format('m'),
                    'ano' => $dueDate->format('Y'),
                    'paid_date' => $paidDate,
                    'status' => $status,
                    'payment_method' => $paymentMethod,
                    'transaction_id' => $transactionId,
                    'link_boleto' => $faker->boolean(30) ? $faker->url() : null,
                    'boleto_numero' => $faker->boolean(30) ? $faker->numerify('####################') : null,
                    'motivo' => $faker->boolean(10) ? $faker->sentence(3) : null,
                    'mes_parcela' => $faker->boolean(10) ? $faker->numberBetween(1, 12) : null,
                    'avulso' => $faker->boolean(5),
                    'ref_os' => $faker->boolean(5) ? $faker->numerify('OS-####') : null,
                    'notes' => $faker->boolean(20) ? $faker->sentence() : null,
                ]);
            }
        }
    }
}
