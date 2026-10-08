<?php

namespace App\Livewire\Notifications;

use App\Models\Notification;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'unread', history: true)]
    public bool $unreadOnly = false;

    public function updatingUnreadOnly(): void
    {
        $this->resetPage();
    }

    public function markRead(int $id): void
    {
        Notification::visibleTo(auth()->user())->whereKey($id)->update(['read' => true]);
    }

    public function markAllRead(): void
    {
        $count = Notification::visibleTo(auth()->user())->unread()->update(['read' => true]);
        $this->dispatch('toast', message: $count ? __('تم تعليم الكل كمقروء') : __('لا توجد إشعارات غير مقروءة'));
    }

    public function render()
    {
        $user = auth()->user();

        $notifications = Notification::visibleTo($user)
            ->when($this->unreadOnly, fn ($q) => $q->unread())
            ->latest()->latest('id')
            ->paginate(20);

        return view('livewire.notifications.index', [
            'notifications' => $notifications,
            'unread' => Notification::visibleTo($user)->unread()->count(),
        ])->extends('layouts.app')->section('content')->title(__('الإشعارات'));
    }
}
