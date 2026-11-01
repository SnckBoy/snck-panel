<?php

namespace App\Livewire;

use App\Models\User;
use App\Services\AdminUserRoleService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AdminUsers extends Component
{
    public string $editing = '';
    public string $role = 'user';
    public bool $disabled = false;
    public string $password = '';

    public function mount(): void
    {
        $this->authorizeStaff();
    }

    public function edit(string $id): void
    {
        $actor = $this->authorizeStaff();
        $user = User::query()->findOrFail($id);

        abort_if($user->isOwner() && ! $actor->isOwner(), 403, 'Admins cannot edit an Owner account.');

        $this->editing = $user->id;
        $this->role = $user->role;
        $this->disabled = (bool) $user->disabled;
        $this->password = '';
    }

    public function save(AdminUserRoleService $roleService): void
    {
        $actor = $this->authorizeStaff();
        $user = User::query()->findOrFail($this->editing);

        $data = $this->validate([
            'role' => ['required', Rule::in(['user', 'admin', 'owner'])],
            'disabled' => ['boolean'],
            'password' => ['nullable', 'string', 'min:12', 'max:200'],
        ]);

        $roleService->update($actor, $user, [
            'role' => $data['role'],
            'disabled' => $data['disabled'],
            'password' => $data['password'] ?? '',
        ]);

        $this->editing = '';
        $this->password = '';
        session()->flash('notice', 'User settings saved.');
    }

    public function render()
    {
        $this->authorizeStaff();

        return view('livewire.admin.users', [
            'users' => User::query()->orderBy('username')->get(),
        ])->layout('layouts.app', ['title' => 'Administrator users']);
    }

    private function authorizeStaff(): User
    {
        $actor = auth()->user();
        abort_unless($actor instanceof User && $actor->isStaff(), 403, 'Staff access is required.');

        return $actor;
    }
}
