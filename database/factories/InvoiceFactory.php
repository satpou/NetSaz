<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'invoice_number' => 'INV-' . $this->faker->unique()->numerify('######'),
            'customer_id' => Customer::factory(),
            'package_id' => Package::factory(),
            'period_start' => Carbon::now()->startOfMonth(),
            'period_end' => Carbon::now()->endOfMonth(),
            'amount' => $this->faker->randomElement([50000, 75000, 100000, 150000]),
            'discount' => 0,
            'tax' => 0,
            'total_amount' => $this->faker->randomElement([50000, 75000, 100000, 150000]),
            'due_date' => Carbon::now()->addDays(7),
            'status' => $this->faker->randomElement(['unpaid', 'paid', 'overdue', 'partial']),
        ];
    }

    public function unpaid(): static
    {
        return $this->state(['status' => 'unpaid']);
    }

    public function paid(): static
    {
        return $this->state(['status' => 'paid']);
    }

    public function overdue(): static
    {
        return $this->state(['status' => 'overdue', 'due_date' => Carbon::now()->subDays(1)]);
    }
}