<?php

namespace App\Livewire\Attendance;

use App\Models\AttendanceRecord;
use App\Support\AbsenceMessage;
use App\Support\WhatsApp;
use Livewire\Component;

/**
 * One "warn the parent on WhatsApp" button, used by the attendance sheet and
 * by the student profile so both behave identically.
 *
 * The record is held by id and re-read through the tenant scope on every
 * request, so another center's id simply 404s instead of leaking a name or a
 * phone number into a link.
 */
class NotifyButton extends Component
{
    public int $recordId;

    public function mount(AttendanceRecord|int $record): void
    {
        $this->recordId = $record instanceof AttendanceRecord ? $record->id : $record;
    }

    protected function record(): AttendanceRecord
    {
        return AttendanceRecord::with(['student', 'group.course', 'group.scheduleSlots', 'notifier'])
            ->findOrFail($this->recordId);
    }

    /**
     * Stamp who warned the parent. The link is a real href opened by the
     * browser, so WhatsApp comes up whether or not this request has finished.
     */
    public function markNotified(): void
    {
        abort_unless(auth()->user()?->can('manage-attendance'), 403);

        $record = $this->record();

        abort_unless($record->isNotifiable(), 404);

        $record->forceFill([
            'notified_at' => now(),
            'notified_by' => auth()->id(),
        ])->save();

        $this->dispatch('parent-notified');
    }

    public function render()
    {
        $record = $this->record();
        ['phone' => $phone, 'isGuardian' => $isGuardian] = $record->notifyPhone();

        $message = AbsenceMessage::for($record);

        return view('livewire.attendance.notify-button', [
            'record' => $record,
            'phone' => $phone,
            'isGuardian' => $isGuardian,
            'link' => WhatsApp::link($phone, $message),
            'message' => $message,
            'canNotify' => auth()->user()?->can('manage-attendance') ?? false,
        ]);
    }
}
