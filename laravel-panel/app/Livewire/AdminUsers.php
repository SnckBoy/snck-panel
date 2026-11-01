<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
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
        abort_unless(auth()->user()?->isStaff(), 403);
    }

    public function edit(string $id): void
    {
        $user = User::query()->findOrFail($id);
        $this->editing = $user->id;
        $this->role = $user->role;
        $this->disabled = $user->disabled;
        $this->password = '';
    }

    public function save(): void
    {
        $actor = auth()->user();
        $user = User::query()->findOrFail($this->editing);
        $data = $this->validate(['role' => ['required', Rule::in(['user', 'admin', 'owner'])], 'disabled' => ['boolean'], 'password' => ['nullable', 'string', 'min:12', 'max:200']]);
        abort_if($user->is($actor) && ($data['disabled'] || $data['role'] !== $user->role), 422, 'You cannot disable or demote your own account.');
        abort_if($data['role'] === 'owner' && ! $actor->isOwner(), 403);
        if ($data['password'] !== '') {
            $user->password = Hash::make($data['password']);
            $user->password_version++;
        }
        $user->role = $data['role'];
        $user->disabled = $data['disabled'];
        $user->save();
        $this->editing = '';
        $this->password = '';
        session()->flash('notice', 'User settings saved.');
    }

    public function render()
    {
        return view('livewire.admin.users', ['users' => User::query()->orderBy('username')->get()])->layout('layouts.app', ['title' => 'Administrator users']);
    }
}
