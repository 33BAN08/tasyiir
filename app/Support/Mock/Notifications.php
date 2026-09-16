<?php

namespace App\Support\Mock;

class Notifications
{
    public static function all(): array
    {
        return [
            ['id' => 1, 'title' => 'تم تسجيل طالب جديد', 'body' => 'قامت فاطمة العلوي بتسجيل ابنها في دورة English A1.', 'icon' => 'user-round-plus', 'tone' => 'brand', 'time' => 'منذ 10 دقائق', 'read' => false, 'category' => 'الطلاب'],
            ['id' => 2, 'title' => 'تم استلام دفعة جديدة', 'body' => 'دفعة بقيمة 900 MAD من الطالب يوسف العلوي.', 'icon' => 'wallet', 'tone' => 'blue', 'time' => 'منذ 32 دقيقة', 'read' => false, 'category' => 'المالية'],
            ['id' => 3, 'title' => 'لديك طلاب لم يؤدوا الشهر الحالي', 'body' => 'يوجد 18 طالباً لم يؤدوا رسوم الاشتراك لهذا الشهر.', 'icon' => 'triangle-alert', 'tone' => 'amber', 'time' => 'منذ 3 ساعات', 'read' => false, 'category' => 'المالية'],
            ['id' => 4, 'title' => 'لديك حصة قادمة', 'body' => 'حصة English B1 - A تبدأ بعد 30 دقيقة في القاعة 2.', 'icon' => 'calendar-check', 'tone' => 'violet', 'time' => 'منذ 5 ساعات', 'read' => false, 'category' => 'الجدول'],
            ['id' => 5, 'title' => 'تسجيل جديد في IELTS', 'body' => 'انضم الطالب طارق بناني إلى مجموعة IELTS - A.', 'icon' => 'clipboard-list', 'tone' => 'brand', 'time' => 'أمس، 18:20', 'read' => true, 'category' => 'التسجيلات'],
            ['id' => 6, 'title' => 'تحديث في جدول الأستاذ كريم العلوي', 'body' => 'تم تعديل توقيت مجموعة Français A1 - B.', 'icon' => 'calendar-days', 'tone' => 'blue', 'time' => 'أمس، 11:05', 'read' => true, 'category' => 'الجدول'],
            ['id' => 7, 'title' => 'تم دفع أجرة الأستاذة سلمى برادة', 'body' => 'تم صرف 3520 MAD مقابل ساعات شتنبر.', 'icon' => 'banknote', 'tone' => 'brand', 'time' => 'منذ يومين', 'read' => true, 'category' => 'المالية'],
            ['id' => 8, 'title' => 'مصروف جديد تم تسجيله', 'body' => 'إضافة مصروف "كراء المحل - شتنبر" بقيمة 12,000 MAD.', 'icon' => 'receipt', 'tone' => 'rose', 'time' => 'منذ 3 أيام', 'read' => true, 'category' => 'المالية'],
            ['id' => 9, 'title' => 'انخفاض في نسبة الحضور', 'body' => 'نسبة حضور مجموعة Mathématiques - B أقل من 80% هذا الأسبوع.', 'icon' => 'calendar-check', 'tone' => 'amber', 'time' => 'منذ 4 أيام', 'read' => true, 'category' => 'الحضور'],
            ['id' => 10, 'title' => 'مجموعة جديدة تم إنشاؤها', 'body' => 'تم إنشاء مجموعة Espagnol A1 - A بسعة 18 طالباً.', 'icon' => 'users-round', 'tone' => 'violet', 'time' => 'منذ أسبوع', 'read' => true, 'category' => 'المجموعات'],
        ];
    }

    public static function unreadCount(): int
    {
        return count(array_filter(static::all(), fn ($n) => !$n['read']));
    }
}
