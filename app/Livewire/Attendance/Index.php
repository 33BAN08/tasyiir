<?php

namespace App\Livewire\Attendance;

use App\Models\AttendanceRecord;
use App\Models\Group;
use App\Models\Student;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(as: 'group', history: true)]
    public string $groupId = '';

    #[Url(history: true)]
    public string $date = '';

    /** student_id => 'حاضر' | 'متأخر' | 'غائب' for the current group/date, edited in memory until save(). */
    public array $states = [];

    public function mount(): void
    {
        if (! $this->date) {
            $this->date = today()->toDateString();
        }

        if (! $this->groupId || ! Group::whereKey($this->groupId)->exists()) {
            $this->groupId = (string) (Group::orderBy('name')->value('id') ?? '');
        }

        $this->loadStates();
    }

    public function updatedGroupId(): void
    {
        $this->loadStates();
    }

    public function updatedDate(): void
    {
        if (! $this->date) {
            $this->date = today()->toDateString();
        }
        $this->loadStates();
    }

    /**
     * A child NotifyButton just stamped a record. Nothing to do here beyond
     * re-rendering, which is what refreshes the "تم إشعار 3 من 5" counter.
     */
    #[On('parent-notified')]
    public function refreshNotifications(): void
    {
        //
    }

    public function setState(int $studentId, string $state): void
    {
        if (array_key_exists($studentId, $this->states) && in_array($state, AttendanceRecord::STATES, true)) {
            $this->states[$studentId] = $state;
        }
    }

    public function markAll(string $state): void
    {
        if (in_array($state, AttendanceRecord::STATES, true)) {
            $this->states = array_fill_keys(array_keys($this->states), $state);
        }
    }

    public function save(): void
    {
        if (! $this->groupId || ! $this->date) {
            return;
        }

        $tenantId = auth()->user()->tenant_id;

        foreach ($this->states as $studentId => $state) {
            // The date cast stores a full datetime, so match on the date part —
            // a plain updateOrCreate(['date' => ...]) would miss and hit the unique index.
            $record = AttendanceRecord::where('student_id', $studentId)
                ->whereDate('date', $this->date)
                ->first() ?? new AttendanceRecord(['student_id' => $studentId, 'date' => $this->date]);

            $attributes = ['tenant_id' => $tenantId, 'group_id' => (int) $this->groupId, 'state' => $state];

            // A corrected state is a different fact about the student, so the
            // old notification no longer describes it: the parent has to be
            // told again (or, if they are present now, not at all).
            if ($record->exists && $record->state !== $state) {
                $attributes['notified_at'] = null;
                $attributes['notified_by'] = null;
            }

            $record->fill($attributes)->save();
        }

        $this->dispatch('toast', message: __('تم حفظ الحضور بنجاح'));
    }

    protected function roster()
    {
        return $this->groupId
            ? Student::where('group_id', $this->groupId)->orderBy('name')->get()
            : collect();
    }

    /** Seed $states from saved records; students without one default to حاضر (the UI default). */
    protected function loadStates(): void
    {
        $roster = $this->roster();

        $saved = $this->groupId && $this->date
            ? AttendanceRecord::whereIn('student_id', $roster->pluck('id'))
                ->whereDate('date', $this->date)
                ->pluck('state', 'student_id')
            : collect();

        $this->states = $roster->mapWithKeys(fn ($s) => [$s->id => $saved[$s->id] ?? 'حاضر'])->all();
    }

    public function render()
    {
        $groups = Group::with(['teacher', 'course'])->orderBy('name')->get();
        $group = $this->groupId ? $groups->firstWhere('id', (int) $this->groupId) : null;
        $roster = $this->roster();

        $counts = array_count_values($this->states);
        $summary = [
            'total' => count($this->states),
            'present' => $counts['حاضر'] ?? 0,
            'late' => $counts['متأخر'] ?? 0,
            'absent' => $counts['غائب'] ?? 0,
        ];

        $savedCount = $this->groupId && $this->date
            ? AttendanceRecord::whereIn('student_id', $roster->pluck('id'))->whereDate('date', $this->date)->count()
            : 0;

        // Driven by what is SAVED, not by the states being edited: before the
        // sheet is saved there is nothing to notify about yet, and a past date
        // that was saved weeks ago still offers its buttons.
        $toNotify = $this->groupId && $this->date
            ? AttendanceRecord::with(['student', 'notifier'])
                ->whereIn('student_id', $roster->pluck('id'))
                ->whereDate('date', $this->date)
                ->notifiable()
                ->get()
                ->sortBy(fn ($r) => $r->student?->name)
                ->values()
            : collect();

        return view('livewire.attendance.index', [
            'groups' => $groups,
            'group' => $group,
            'roster' => $roster,
            'summary' => $summary,
            'savedCount' => $savedCount,
            'toNotify' => $toNotify,
            'notifiedCount' => $toNotify->whereNotNull('notified_at')->count(),
        ])->extends('layouts.app')->section('content')->title(__('الحضور'));
    }
}
