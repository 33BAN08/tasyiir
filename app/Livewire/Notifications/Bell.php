<?php

namespace App\Livewire\Notifications;

use App\Models\Notification;
use Livewire\Attributes\On;
use Livewire\Component;

class Bell extends Component
{
    /** Any module that creates a notification dispatches this so the badge updates at once. */
    #[On('notification-created')]
    public function refresh(): void
    {
    }

    public function markRead(int $id): void
    {
        Notification::visibleTo(auth()->user())->whereKey($id)->update(['read' => true]);
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.notifications.bell', [
            'items' => Notification::visibleTo($user)->latest()->limit(5)->get(),
            'unread' => Notification::visibleTo($user)->unread()->count(),
        ]);
    }
}
