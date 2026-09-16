<div class="fixed bottom-5 inset-x-0 sm:inset-x-auto sm:end-5 z-[100] flex flex-col items-center sm:items-end gap-2 px-4 sm:px-0 pointer-events-none">
    <template x-for="toast in $store.toasts.items" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto w-full sm:w-auto max-w-sm flex items-center gap-2.5 rounded-xl bg-ink-900 text-white shadow-popover px-4 py-3 text-sm font-medium"
        >
            <span x-show="toast.type === 'success'" class="text-emerald-400">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 6 9 17l-5-5"/></svg>
            </span>
            <span x-text="toast.message"></span>
            <button type="button" class="ms-auto text-ink-400 hover:text-white" x-on:click="$store.toasts.dismiss(toast.id)" aria-label="إغلاق">✕</button>
        </div>
    </template>
</div>
