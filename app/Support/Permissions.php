<?php

namespace App\Support;

/**
 * The permission catalog and the three built-in roles. Permission keys are
 * what routes, the sidebar and Livewire components check; labels are what the
 * Roles panel shows.
 */
class Permissions
{
    public const OWNER_ROLE = 'مدير المركز';

    public const RECEPTION_ROLE = 'إدارية / استقبال';

    public const ACCOUNTANT_ROLE = 'محاسب';

    /** permission key => Arabic label, in the order the checklist shows them. */
    public const LABELS = [
        'manage-students' => 'الطلاب',
        'manage-enrollments' => 'التسجيلات',
        'manage-payments' => 'أداءات الطلاب',
        'manage-attendance' => 'الحضور',
        'manage-schedule' => 'الجدول',
        'manage-courses-groups-teachers' => 'الأساتذة والدورات والمجموعات',
        'manage-expenses' => 'المصاريف',
        'manage-salaries' => 'أجور الأساتذة',
        'view-reports' => 'التقارير والإحصائيات',
        'manage-settings' => 'معلومات المركز',
        'manage-users' => 'المستخدمون والصلاحيات',
    ];

    public const BUILT_IN_ROLES = [
        self::OWNER_ROLE => [
            'description' => 'صلاحية كاملة على جميع وحدات النظام، بما فيها إدارة حسابات الموظفين',
            'permissions' => '*',
        ],
        self::RECEPTION_ROLE => [
            'description' => 'الطلاب، التسجيلات، المدفوعات، الحضور',
            'permissions' => ['manage-students', 'manage-enrollments', 'manage-payments', 'manage-attendance'],
        ],
        self::ACCOUNTANT_ROLE => [
            'description' => 'المصاريف، أجور الأساتذة، التقارير المالية',
            'permissions' => ['manage-expenses', 'manage-salaries', 'view-reports'],
        ],
    ];

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::LABELS);
    }

    /** @return list<string> */
    public static function forBuiltInRole(string $role): array
    {
        $set = self::BUILT_IN_ROLES[$role]['permissions'] ?? [];

        return $set === '*' ? self::keys() : $set;
    }

    public static function label(string $permission): string
    {
        return self::LABELS[$permission] ?? $permission;
    }
}
