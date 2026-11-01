<?php

namespace App\Livewire;

use App\Models\User;
use App\Services\UserAdministrationService;
use Illuminate\Contracts\View\View;
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
        app(UserAdministrationService::class)->assertCanManage($actor, $user);

        $this->editing = $user->id;
        $this->role = $user->role;
        $this->disabled = $user->disabled;
        $this->password = '';
    }

    public function save(): void
    {
        $actor = $this->authorizeStaff();
        $data = $this->validate([
            'role' => ['required', Rule::in(['user', 'admin', 'owner'])],
            'disabled' => ['boolean'],
            'password' => ['nullable', 'string', 'min:12', 'max:200'],
        ]);

        $user = User::query()->findOrFail($this->editing);
        app(UserAdministrationService::class)->update($actor, $user, $data);

        $this->editing = '';
        $this->password = '';
        session()->flash('notice', 'User settings saved.');
    }

    public function delete(string $id): void
    {
        $actor = $this->authorizeStaff();
        $user = User::query()->findOrFail($id);
        app(UserAdministrationService::class)->delete($actor, $user);

        if ($this->editing === $id) {
            $this->editing = '';
            $this->password = '';
        }

        session()->flash('notice', 'User deleted.');
    }

    public function render(): View
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
