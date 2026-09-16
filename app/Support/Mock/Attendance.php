<?php

namespace App\Support\Mock;

class Attendance
{
    protected const STATES = ['حاضر', 'حاضر', 'حاضر', 'متأخر', 'غائب'];

    /** Students enrolled in the given group, each with a deterministic mock attendance state. */
    public static function forGroup(int $groupId): array
    {
        $students = array_values(array_filter(Students::all(), fn ($s) => $s['group_id'] === $groupId));

        $rows = [];
        foreach ($students as $i => $s) {
            $rows[] = [
                'id' => $s['id'],
                'name' => $s['name'],
                'phone' => $s['phone'],
                'state' => self::STATES[$i % count(self::STATES)],
            ];
        }

        return $rows;
    }

    public static function summary(array $rows): array
    {
        return [
            'present' => count(array_filter($rows, fn ($r) => $r['state'] === 'حاضر')),
            'late' => count(array_filter($rows, fn ($r) => $r['state'] === 'متأخر')),
            'absent' => count(array_filter($rows, fn ($r) => $r['state'] === 'غائب')),
            'total' => count($rows),
        ];
    }

    /** A short recent-session history for a student's profile tab. */
    public static function historyForStudent(int $studentId): array
    {
        $states = self::STATES;
        $history = [];
        for ($i = 0; $i < 8; $i++) {
            $day = 28 - ($i * 3);
            $month = $day > 0 ? 9 : 8;
            $day = $day > 0 ? $day : $day + 30;
            $history[] = [
                'date' => sprintf('2026-%02d-%02d', $month, $day),
                'state' => $states[($studentId + $i) % count($states)],
            ];
        }
        return $history;
    }
}
