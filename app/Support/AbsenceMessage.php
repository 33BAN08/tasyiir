<?php

namespace App\Support;

use App\Models\AttendanceRecord;
use App\Models\Tenant;
use Illuminate\Support\Carbon;

/**
 * The text a center sends a parent when a student was absent or late.
 *
 * The owner may rewrite it in Settings → معلومات المركز, in whatever language
 * they speak to their parents in; only the placeholders are substituted. The
 * built-in default is Arabic and adapts to the student's gender, which is why
 * the gender logic lives in defaultTemplate() and never touches a custom
 * template — rewriting someone's sentence would be worse than a neutral one.
 */
class AbsenceMessage
{
    /** tenant settings key per attendance state. */
    public const SETTING_KEYS = [
        'غائب' => 'absence_message',
        'متأخر' => 'late_message',
    ];

    public const PLACEHOLDERS = [
        '{student}', '{parent_greeting}', '{group}', '{course}',
        '{date}', '{day}', '{time}', '{center}', '{center_phone}',
    ];

    public const GREETING = 'السلام عليكم';

    /** Arabic weekday names — the message goes to a parent, not to the UI user. */
    public const DAYS = [
        'Sunday' => 'الأحد', 'Monday' => 'الاثنين', 'Tuesday' => 'الثلاثاء', 'Wednesday' => 'الأربعاء',
        'Thursday' => 'الخميس', 'Friday' => 'الجمعة', 'Saturday' => 'السبت',
    ];

    /**
     * The built-in text. $gender is 'male', 'female' or null (unknown), and
     * only changes the wording — never the structure.
     */
    public static function defaultTemplate(string $state, ?string $gender = null, bool $withPhone = true): string
    {
        $child = match ($gender) {
            'male' => 'ابنكم',
            'female' => 'ابنتكم',
            default => 'ابنكم/ابنتكم',
        };

        // A center that has not filled in its phone number would otherwise be
        // asking parents to call "on the number .".
        $contact = $withPhone
            ? ' المرجو التواصل مع {center} على الرقم {center_phone}. شكراً.'
            : ' المرجو التواصل مع {center}. شكراً.';

        if ($state === 'متأخر') {
            $verb = $gender === 'female' ? 'تأخرت' : 'تأخر';

            return '{parent_greeting}، نخبركم أن '.$child.' {student} '.$verb
                .' عن حصة {group} ({time}) اليوم {day} {date}.'.$contact;
        }

        $verb = $gender === 'female' ? 'كانت غائبة' : 'كان غائباً';

        return '{parent_greeting}، نخبركم أن '.$child.' {student} '.$verb
            .' اليوم {day} {date} عن حصة {group} ({time}).'.$contact;
    }

    /** The template actually in force: the owner's, or the built-in one. */
    public static function templateFor(string $state, ?Tenant $tenant = null, ?string $gender = null): string
    {
        $key = self::SETTING_KEYS[$state] ?? self::SETTING_KEYS['غائب'];
        $custom = trim((string) $tenant?->setting($key));

        return $custom !== ''
            ? $custom
            : self::defaultTemplate($state, $gender, trim((string) $tenant?->setting('phone')) !== '');
    }

    /** Substitute the placeholders and tidy up what an empty value leaves behind. */
    public static function render(string $template, array $values): string
    {
        $text = strtr($template, $values);

        // An unknown class time would otherwise print "حصة A ()", and a custom
        // template whose placeholder came back empty would leave " ." hanging.
        $text = preg_replace('/\s*\(\s*\)/u', '', $text) ?? $text;
        $text = preg_replace('/\s+([.،,])/u', '$1', $text) ?? $text;

        return trim(preg_replace('/[ \t]{2,}/u', ' ', $text) ?? $text);
    }

    /** The finished message for one attendance record. */
    public static function for(AttendanceRecord $record, ?Tenant $tenant = null): string
    {
        $tenant ??= $record->tenant ?? auth()->user()?->tenant;

        $student = $record->student;
        $group = $record->group;
        $date = $record->date instanceof Carbon ? $record->date : Carbon::parse($record->date);

        $day = self::DAYS[$date->format('l')] ?? '';
        $time = $group?->scheduleSlots->firstWhere('day', $day)?->time
            ?: (string) $group?->schedule;

        return self::render(
            self::templateFor($record->state, $tenant, $student?->gender),
            [
                '{student}' => (string) $student?->name,
                '{parent_greeting}' => self::GREETING,
                '{group}' => (string) $group?->name,
                '{course}' => (string) $group?->course?->name,
                '{date}' => $date->format('Y-m-d'),
                '{day}' => $day,
                '{time}' => $time,
                '{center}' => (string) $tenant?->name,
                '{center_phone}' => (string) $tenant?->setting('phone'),
            ],
        );
    }

    /**
     * A made-up record for the live preview in Settings, so the owner sees
     * their wording filled in without needing a real absence.
     */
    public static function preview(string $state, Tenant $tenant, string $template): string
    {
        return self::render($template, [
            '{student}' => 'سارة العلوي',
            '{parent_greeting}' => self::GREETING,
            '{group}' => 'English A1 - A',
            '{course}' => 'English A1',
            '{date}' => today()->format('Y-m-d'),
            '{day}' => self::DAYS[today()->format('l')] ?? '',
            '{time}' => '16:00 - 17:30',
            '{center}' => $tenant->name,
            '{center_phone}' => (string) $tenant->setting('phone'),
        ]);
    }
}
