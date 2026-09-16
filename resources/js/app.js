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
