// PlanZeen — small Alpine.js glue code shared across the app.
// Runs before Alpine boots (registers global stores) and after (page-level helpers).

document.addEventListener('alpine:init', () => {
  // Global toast notification store — used by data-toast-trigger buttons and mock actions.
  Alpine.store('toasts', {
    items: [],
    push(message, type = 'success') {
      const id = Date.now() + Math.random();
      this.items.push({ id, message, type });
      setTimeout(() => this.dismiss(id), 3500);
    },
    dismiss(id) {
      this.items = this.items.filter((t) => t.id !== id);
    },
  });

  // Global command-palette / search modal visibility store.
  Alpine.store('ui', {
    sidebarOpen: false,
    searchOpen: false,
  });
});

// Delegate clicks on any [data-toast] element to push a mock toast — used across
// "mock behavior" buttons (Add student, Save settings, etc.) per the Phase 1 spec.
document.addEventListener('click', (e) => {
  const trigger = e.target.closest('[data-toast]');
  if (trigger && window.Alpine) {
    const message = trigger.getAttribute('data-toast') || 'تم تنفيذ الإجراء بنجاح';
    const type = trigger.getAttribute('data-toast-type') || 'success';
    window.Alpine.store('toasts').push(message, type);
  }
});

// Real toasts dispatched from Livewire components (Students CRUD, etc.) and
// from a `session('toast')` flash message rendered once on full page loads.
document.addEventListener('livewire:init', () => {
  Livewire.on('toast', ({ message, type }) => {
    window.Alpine.store('toasts').push(message, type || 'success');
  });

  // A 419 means the session behind this page is gone (expired, or the DB was
  // reseeded). Reloading sends the user to /login instead of Livewire's raw
  // "PAGE EXPIRED" dialog.
  Livewire.hook('request', ({ fail }) => {
    fail(({ status, preventDefault }) => {
      if (status === 419) {
        preventDefault();
        window.location.reload();
      }
    });
  });

  if (window.__flashToast) {
    window.Alpine.store('toasts').push(window.__flashToast);
    window.__flashToast = null;
  }
});
