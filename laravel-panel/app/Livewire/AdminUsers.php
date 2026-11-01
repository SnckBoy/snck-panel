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
        $this->staffUser();
    }

    public function edit(string $id, UserRoleManagementService $roles): void
    {
        $actor = $this->staffUser();
        $user = User::query()->findOrFail($id);
        $roles->assertCanEdit($actor, $user);

        $this->editing = $user->id;
        $this->role = $user->role;
        $this->disabled = $user->disabled;
        $this->password = '';
    }

    public function save(UserRoleManagementService $roles): void
    {
        $actor = $this->staffUser();

        $data = $this->validate([
            'role' => ['required', Rule::in(['user', 'admin', 'owner'])],
            'disabled' => ['boolean'],
            'password' => ['nullable', 'string', 'min:12', 'max:200'],
        ]);

        $user = User::query()->findOrFail($this->editing);
        $roles->update($actor, $user, $data);

        $this->editing = '';
        $this->password = '';
        session()->flash('notice', 'User settings saved.');
    }

    public function render()
    {
        $this->staffUser();

        return view('livewire.admin.users', ['users' => User::query()->orderBy('username')->get()])
            ->layout('layouts.app', ['title' => 'Administrator users']);
    }

    private function staffUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->isStaff() && ! $user->disabled, 403, 'Staff access is required.');

        return $user;
    }
}
