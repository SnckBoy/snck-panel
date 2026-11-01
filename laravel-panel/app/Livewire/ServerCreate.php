<?php

namespace App\Livewire;

use App\Models\Allocation;
use App\Models\Node;
use App\Models\User;
use App\Services\NodeClient;
use App\Services\ServerProvisioner;
use App\Services\ServerProvisioningException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ServerCreate extends Component
{
    public string $nodeId = '';
    public string $allocationId = '';
    public string $ownerId = '';
    public string $name = '';
    public string $type = 'PAPER';
    public string $version = 'latest';
    public string $javaVersion = '21';
    public int $memoryLimitMb = 2048;
    public int $cpuLimitPercent = 100;
    public int $diskLimitMb = 10240;
    public string $notice = '';
    public string $noticeType = 'success';

    public function mount(): void
    {
        abort_unless(Auth::user()?->can('create', \App\Models\Server::class), 403);
        $this->ownerId = (string) Auth::id();
    }

    public function updatedNodeId(): void
    {
        $this->allocationId = '';
    }

    public function create(NodeClient $client, ServerProvisioner $provisioner)
    {
        $this->notice = '';
        $this->validate([
            'nodeId' => ['required', 'string'],
            'allocationId' => ['required', 'string'],
            'ownerId' => ['required', 'string'],
            'name' => ['required', 'string', 'min:1', 'max:120'],
            'type' => ['required', 'in:PAPER,VANILLA,FABRIC,FORGE,NEOFORGE,SPIGOT,PURPUR,VELOCITY,BUNGEECORD,WATERFALL'],
            'version' => ['nullable', 'string', 'max:64'],
            'javaVersion' => ['nullable', 'in:8,11,17,21,25'],
            'memoryLimitMb' => ['required', 'integer', 'min:128', 'max:1048576'],
            'cpuLimitPercent' => ['required', 'integer', 'min:1', 'max:10000'],
            'diskLimitMb' => ['required', 'integer', 'min:512', 'max:1099511627776'],
        ]);

        $payload = [
            'node_id' => $this->nodeId,
            'allocation_id' => $this->allocationId,
            'owner_id' => $this->ownerId,
            'name' => trim($this->name),
            'type' => $this->type,
            'version' => trim($this->version) ?: 'latest',
            'java_version' => $this->javaVersion ?: null,
            'memory_limit_mb' => $this->memoryLimitMb,
            'cpu_limit_percent' => $this->cpuLimitPercent,
            'disk_limit_mb' => $this->diskLimitMb,
        ];

        try {
            $result = $provisioner->provision(Auth::user(), $payload, $client);
            return redirect()->route('servers.show', (string) data_get($result, 'server.id'));
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (ServerProvisioningException $exception) {
            report($exception);
            $this->notice = $exception->getMessage();
            $this->noticeType = 'error';
        } catch (HttpExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            $this->notice = $exception->getMessage() ?: 'Server creation failed.';
            $this->noticeType = 'error';
        }
    }

    public function render(): View
    {
        $user = Auth::user();
        $nodes = Node::query()
            ->where('disabled', false)
            ->where('maintenance', false)
            ->orderBy('name')
            ->get(['id', 'name', 'status', 'docker_available', 'last_heartbeat_at']);
        $allocations = Allocation::query()
            ->whereNull('assigned_server_id')
            ->when($this->nodeId !== '', fn ($query) => $query->where('node_id', $this->nodeId))
            ->with('node:id,name')
            ->orderBy('node_id')->orderBy('port')->get(['id', 'node_id', 'ip', 'port']);
        $owners = $user->isStaff() ? User::query()->where('disabled', false)->orderBy('username')->get(['id', 'username', 'email']) : collect([$user]);

        return view('livewire.servers.create', compact('nodes', 'allocations', 'owners'))
            ->layout('layouts.app', ['title' => 'Create server']);
    }
}
