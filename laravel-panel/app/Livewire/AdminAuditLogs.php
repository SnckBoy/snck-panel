<?php

namespace App\Livewire;

use App\Models\AuditEvent;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class AdminAuditLogs extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterResult = 'all';

    public string $filterAction = 'all';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterResult(): void
    {
        $this->resetPage();
    }

    public function updatedFilterAction(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        abort_unless(auth()->user()?->isStaff(), 403);

        $search = trim(mb_substr($this->search, 0, 100));
        $result = in_array($this->filterResult, ['all', 'success', 'failure'], true)
            ? $this->filterResult
            : 'all';

        $events = AuditEvent::query()
            ->with('user:id,name,username,email')
            ->when($result !== 'all', fn (Builder $query) => $query->where('result', $result))
            ->when($this->filterAction !== 'all', function (Builder $query): void {
                $actions = AuditEvent::query()->distinct()->pluck('action')->all();
                if (in_array($this->filterAction, $actions, true)) {
                    $query->where('action', $this->filterAction);
                }
            })
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $nested) use ($search): void {
                    $like = '%'.$search.'%';
                    $nested->where('action', 'like', $like)
                        ->orWhere('resource_type', 'like', $like)
                        ->orWhere('resource_id', 'like', $like)
                        ->orWhere('ip_address', 'like', $like)
                        ->orWhere('request_id', 'like', $like)
                        ->orWhereHas('user', function (Builder $user) use ($like): void {
                            $user->where('name', 'like', $like)
                                ->orWhere('username', 'like', $like)
                                ->orWhere('email', 'like', $like);
                        });
                });
            })
            ->latest('id')
            ->paginate(25);

        return view('livewire.admin.audit-logs', [
            'events' => $events,
            'actions' => AuditEvent::query()->distinct()->orderBy('action')->pluck('action'),
        ])->layout('layouts.app');
    }
}
