<?php

namespace Database\Seeders;

use App\Models\SalaryPayment;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

/**
 * Give each teacher a starting `paid` balance (paid / partial / unpaid mix)
 * against their computed salary, so /salaries isn't 100% unpaid on first view.
 * Must run after PaymentSeeder (commission salaries depend on payments).
 */
class SalaryPaymentSeeder extends Seeder
{
    protected array $ratios = [1.0, 0.5, 0.0, 1.0, 0.25];

    public function run(): void
    {
        Teacher::withoutGlobalScopes()->orderBy('id')->get()->each(function (Teacher $teacher, int $i) {
            $row = SalaryPayment::refreshFor($teacher);
            $row->paid = (int) round($row->salary * $this->ratios[$i % count($this->ratios)]);
            $row->settle();
        });
    }
}
