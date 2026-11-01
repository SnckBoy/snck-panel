<?php

namespace App\Livewire;

use App\Models\User;
use App\Services\UserRoleManagementService;
use Illuminate\Support\Facades\Auth;
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

    public function edit(string $id, UserRoleManagementService $roles): void
    {
        $this->authorizeStaff();
        $user = User::query()->findOrFail($id);
        $roles->assertCanEdit(Auth::user(), $user);

        $this->editing = $user->id;
        $this->role = $user->role;
        $this->disabled = $user->disabled;
        $this->password = '';
    }

    public function save(UserRoleManagementService $roles): void
    {
        $this->authorizeStaff();

        $data = $this->validate([
            'role' => ['required', Rule::in(['user', 'admin', 'owner'])],
            'disabled' => ['boolean'],
            'password' => ['nullable', 'string', 'min:12', 'max:200'],
        ]);

        $user = User::query()->findOrFail($this->editing);
        $roles->update(Auth::user(), $user, $data);

        $this->editing = '';
        $this->password = '';
        session()->flash('notice', 'User settings saved.');
    }

    public function render()
    {
        $this->authorizeStaff();

        return view('livewire.admin.users', ['users' => User::query()->orderBy('username')->get()])
            ->layout('layouts.app', ['title' => 'Administrator users']);
    }

    private function authorizeStaff(): void
    {
        abort_unless(Auth::user()?->isStaff(), 403, 'Staff access is required.');
    }
}
