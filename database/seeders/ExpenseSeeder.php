<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

/** Phase 1's Mock\Expenses::all() items, per tenant, spread across the current month. */
class ExpenseSeeder extends Seeder
{
    protected array $items = [
        ['category' => 'الكراء', 'label' => 'كراء المحل - شتنبر', 'amount' => 12000, 'day' => 1, 'method' => 'تحويل بنكي'],
        ['category' => 'الكهرباء', 'label' => 'فاتورة الكهرباء - غشت', 'amount' => 1450, 'day' => 3, 'method' => 'نقداً'],
        ['category' => 'الإنترنت', 'label' => 'اشتراك الأنترنت الشهري', 'amount' => 599, 'day' => 3, 'method' => 'بطاقة بنكية'],
        ['category' => 'المعدات', 'label' => 'شراء طاولات وكراسي', 'amount' => 2400, 'day' => 5, 'method' => 'نقداً'],
        ['category' => 'المعدات', 'label' => 'جهاز عرض (بروجيكتور)', 'amount' => 800, 'day' => 6, 'method' => 'تحويل بنكي'],
        ['category' => 'التسويق', 'label' => 'إعلانات فيسبوك وانستغرام', 'amount' => 1500, 'day' => 8, 'method' => 'بطاقة بنكية'],
        ['category' => 'التسويق', 'label' => 'طباعة منشورات ولافتات', 'amount' => 600, 'day' => 9, 'method' => 'نقداً'],
        ['category' => 'المستلزمات', 'label' => 'أوراق ومستلزمات مكتبية', 'amount' => 870, 'day' => 10, 'method' => 'نقداً'],
        ['category' => 'الصيانة', 'label' => 'صيانة نظام التكييف', 'amount' => 640, 'day' => 12, 'method' => 'نقداً'],
        ['category' => 'أخرى', 'label' => 'مصاريف متنوعة', 'amount' => 410, 'day' => 14, 'method' => 'نقداً'],
    ];

    public function run(): void
    {
        $month = now()->startOfMonth();
        $lastDay = now()->daysInMonth;

        Tenant::all()->each(function (Tenant $tenant) use ($month, $lastDay) {
            foreach ($this->items as $item) {
                Expense::create([
                    'tenant_id' => $tenant->id,
                    'category' => $item['category'],
                    'label' => $item['label'],
                    'amount' => $item['amount'],
                    'date' => $month->copy()->day(min($item['day'], $lastDay)),
                    'method' => $item['method'],
                ]);
            }
        });
    }
}
