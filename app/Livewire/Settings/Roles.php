<?php

namespace App\Livewire\Settings;

use App\Models\Role;
use App\Support\Permissions;
use Livewire\Component;

/**
 * The three built-in roles (read-only) plus this center's own custom roles.
 * Custom roles are stored under a tenant-namespaced name so two centers can
 * both have a "مسؤول التسويق" without colliding or seeing each other's.
 */
class Roles extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    public ?int $confirmingDeleteId = null;

    // Form fields
    public string $name = '';

    /** @var array<int, string> selected permission keys */
    public array $permissions = [];

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:60'],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['string', 'in:'.implode(',', Permissions::keys())],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => __('اسم الدور'),
            'permissions' => __('الصلاحيات'),
        ];
    }

    protected function messages(): array
    {
        return [
            'permissions.required' => __('اختر صلاحية واحدة على الأقل.'),
            'permissions.min' => __('اختر صلاحية واحدة على الأقل.'),
        ];
    }

    public function mount(): void
    {
        $this->authorizeManage();
    }

    protected function authorizeManage(): void
    {
        abort_unless(auth()->user()->can('manage-users'), 403);
    }

    protected function tenantId(): int
    {
        return (int) auth()->user()->tenant_id;
    }

    /** A custom role of THIS center, or 404 — built-in and other tenants' roles are never editable. */
    protected function ownCustomRole(int $id): Role
    {
        return Role::where('tenant_id', $this->tenantId())->findOrFail($id);
    }

    public function openCreate(): void
    {
        $this->authorizeManage();
        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $this->authorizeManage();
        $role = $this->ownCustomRole($id);

        $this->resetForm();
        $this->editingId = $role->id;
        $this->name = $role->label;
        $this->permissions = $role->permissions->pluck('name')->all();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    protected function resetForm(): void
    {
        $this->reset(['name', 'permissions']);
    }

    public function save(): void
    {
        $this->authorizeManage();
        $data = $this->validate();
        $label = trim($data['name']);

        // Unique among this center's roles and never a built-in name.
        $taken = in_array($label, array_keys(Permissions::BUILT_IN_ROLES), true)
            || Role::where('tenant_id', $this->tenantId())
                ->where('display_name', $label)
                ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))
                ->exists();
        if ($taken) {
            $this->addError('name', __('يوجد دور بهذا الاسم بالفعل.'));

            return;
        }

        if ($this->editingId) {
            $role = $this->ownCustomRole($this->editingId);
            $role->name = Role::namespacedName($this->tenantId(), $label);
            $role->display_name = $label;
            $role->save();
            $message = __('تم تحديث الدور بنجاح');
        } else {
            $role = Role::create([
                'tenant_id' => $this->tenantId(),
                'name' => Role::namespacedName($this->tenantId(), $label),
                'display_name' => $label,
                'guard_name' => 'web',
            ]);
            $message = __('تم إنشاء الدور بنجاح');
        }

        $role->syncPermissions(array_values($data['permissions']));

        $this->dispatch('toast', message: $message);
        $this->closeModal();
    }

    public function confirmDelete(int $id): void
    {
        $this->authorizeManage();
        $this->confirmingDeleteId = $this->ownCustomRole($id)->id;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
    }

    /** Only an unused custom role can go; reassign its users first. */
    public function delete(): void
    {
        $this->authorizeManage();
        if ($this->confirmingDeleteId) {
            $role = $this->ownCustomRole($this->confirmingDeleteId);
            if ($role->users()->exists()) {
                $this->confirmingDeleteId = null;
                $this->dispatch('toast', message: __('لا يمكن حذف دور لا يزال مسنداً إلى مستخدمين.'));

                return;
            }
            $role->delete();
            $this->dispatch('toast', message: __('تم حذف الدور'));
        }
        $this->confirmingDeleteId = null;
    }

    public function render()
    {
        // users_count goes through the User model, so it is this center's users only.
        $roles = Role::forTenant($this->tenantId())
            ->with('permissions')
            ->withCount('users')
            ->orderByRaw('tenant_id IS NOT NULL')->orderBy('id')
            ->get();

        return view('livewire.settings.roles', [
            'roles' => $roles,
            'catalog' => Permissions::LABELS,
        ]);
    }
}
