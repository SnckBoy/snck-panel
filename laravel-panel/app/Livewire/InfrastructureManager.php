<?php

namespace App\Livewire;

use App\Models\Allocation;
use App\Models\Node;
use App\Models\Server;
use App\Services\NodeClient;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Throwable;

class InfrastructureManager extends Component
{
    public string $tab = 'nodes';

    public string $nodeId = '';

    public string $nodeName = '';

    public string $host = '';

    public string $fqdn = '';

    public int $daemonPort = 6768;

    public string $credential = '';

    public string $nodeInstallCommand = '';

    public string $nodeIdForAllocation = '';

    public string $allocationIp = '';

    public int $portStart = 25565;

    public int $portEnd = 25565;

    public string $allocationId = '';

    public string $assignServerId = '';

    public string $notice = '';

    public string $noticeType = 'success';

    public function mount(string $tab = 'nodes'): void
    {
        $this->requireStaff();
        $this->tab = in_array($tab, ['nodes', 'allocations'], true) ? $tab : 'nodes';
    }

    public function setTab(string $tab): void
    {
        $this->requireStaff();
        abort_unless(in_array($tab, ['nodes', 'allocations'], true), 404);
        $this->tab = $tab;
        $this->notice = '';
    }

    public function createNode(): void
    {
        $this->requireStaff();
        $data = $this->validate([
            'nodeId' => ['required', 'string', 'regex:/^[a-zA-Z0-9_-]{1,64}$/', 'unique:nodes,id'],
            'nodeName' => ['required', 'string', 'max:128'],
            'host' => ['nullable', 'string', 'max:255'],
            'fqdn' => ['required', 'string', 'max:253', 'regex:/^(?=.{1,253}$)(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,63}$/'],
            'daemonPort' => ['required', 'integer', 'between:1,65535'],
            'credential' => ['nullable', 'string', 'min:32', 'max:512'],
        ]);
        $host = trim((string) ($data['host'] ?: $data['fqdn']));
        $fqdn = trim($data['fqdn']);
        $credential = trim((string) ($data['credential'] ?? ''));
        if ($credential === '') {
            $credential = bin2hex(random_bytes(32));
        }
        $panelUrl = rtrim((string) config('app.url'), '/');
        abort_unless(str_starts_with($panelUrl, 'https://'), 422, 'Set APP_URL to the public HTTPS panel URL before registering nodes.');
        foreach ([$host, $fqdn] as $hostname) {
            abort_if(preg_match('/[\r\n\/@?#]/', $hostname), 422, 'Enter a hostname or IP without scheme or path.');
        }
        abort_if(Node::query()->where('credential_hash', hash('sha256', $credential))->exists(), 422, 'This daemon bearer token is already registered to another node.');
        Node::query()->create([
            'id' => $data['nodeId'], 'name' => $data['nodeName'], 'host' => $host, 'fqdn' => $fqdn,
            'protocol' => 'https', 'daemon_port' => $data['daemonPort'], 'api_port' => $data['daemonPort'],
            'credential_encrypted' => Crypt::encryptString($credential), 'credential_hash' => hash('sha256', $credential),
            'status' => 'offline', 'docker_available' => false, 'disabled' => false,
        ]);
        $installer = 'https://raw.githubusercontent.com/SnckBoy/snck-panel/main/node-install.sh';
        $this->nodeInstallCommand = 'curl -fsSL '.escapeshellarg($installer).' | sudo bash -s --'
            .' --panel '.escapeshellarg($panelUrl)
            .' --node-id '.escapeshellarg($data['nodeId'])
            .' --fqdn '.escapeshellarg($fqdn)
            .' --credential '.escapeshellarg($credential)
            .' --port '.(int) $data['daemonPort'];
        $this->credential = '';
        $this->nodeId = $this->nodeName = $this->host = $this->fqdn = '';
        $this->notice = 'Node saved with HTTPS and certificate verification enabled. Status remains offline until a real probe or heartbeat.';
        $this->noticeType = 'success';
    }


    public function dismissNodeInstallCommand(): void
    {
        $this->requireStaff();
        $this->nodeInstallCommand = '';
    }

    public function checkNode(string $id, NodeClient $client): void
    {
        $this->requireStaff();
        $node = Node::query()->findOrFail($id);
        try {
            $health = $client->health($node);
            $node->forceFill(['status' => 'online', 'docker_available' => (bool) $health['docker'], 'daemon_version' => $health['daemonVersion'] ?? $node->daemon_version, 'last_health_check' => ['checkedAt' => now()->toISOString(), 'ok' => true, 'docker' => $health['docker']]])->save();
            $this->notice = 'Live node health probe succeeded.';
            $this->noticeType = 'success';
        } catch (Throwable $exception) {
            $node->forceFill(['status' => 'offline', 'docker_available' => false, 'last_health_check' => ['checkedAt' => now()->toISOString(), 'ok' => false]])->save();
            Log::warning('Livewire node health probe failed.', ['node_id' => $node->id, 'exception' => $exception::class]);
            $this->flashError('Live node health probe failed; node was marked offline.');
        }
    }

    public function restartNode(string $id, NodeClient $client): void
    {
        $this->requireStaff();
        $node = Node::query()->findOrFail($id);
        try {
            $client->restartNode($node);
            $this->notice = 'Daemon restart request accepted.';
            $this->noticeType = 'success';
        } catch (Throwable $exception) {
            Log::warning('Livewire node restart failed.', ['node_id' => $node->id, 'exception' => $exception::class]);
            $this->flashError('Daemon restart request failed.');
        }
    }

    public function createAllocations(): void
    {
        $this->requireStaff();
        $data = $this->validate([
            'nodeIdForAllocation' => ['required', 'string', 'exists:nodes,id'],
            'allocationIp' => ['required', 'string', 'max:64'],
            'portStart' => ['required', 'integer', 'between:1,65535'],
            'portEnd' => ['required', 'integer', 'between:1,65535', 'gte:portStart'],
        ]);
        $count = $data['portEnd'] - $data['portStart'] + 1;
        abort_if($count > 1000, 422, 'Limit one allocation batch to 1,000 ports.');
        if (! filter_var($data['allocationIp'], FILTER_VALIDATE_IP) && ! preg_match('/^[a-zA-Z0-9.-]{1,64}$/', $data['allocationIp'])) {
            $this->flashError('Enter a valid IP address or safe hostname.');

            return;
        }
        $ports = range($data['portStart'], $data['portEnd']);
        DB::transaction(function () use ($data, $ports): void {
            $existing = Allocation::query()->where('node_id', $data['nodeIdForAllocation'])->where('ip', $data['allocationIp'])->whereIn('port', $ports)->lockForUpdate()->exists();
            abort_if($existing, 409, 'One or more ports already exist for this node and IP.');
            foreach ($ports as $port) {
                Allocation::query()->create(['id' => (string) Str::uuid(), 'node_id' => $data['nodeIdForAllocation'], 'ip' => $data['allocationIp'], 'ip_version' => filter_var($data['allocationIp'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? 6 : (filter_var($data['allocationIp'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? 4 : null), 'port' => $port]);
            }
        });
        $this->notice = "Created {$count} allocation(s).";
        $this->noticeType = 'success';
    }

    public function assignAllocation(): void
    {
        $this->requireStaff();
        $data = $this->validate(['allocationId' => ['required', 'string', 'exists:allocations,id'], 'assignServerId' => ['required', 'string', 'exists:servers,id']]);
        DB::transaction(function () use ($data): void {
            $allocation = Allocation::query()->lockForUpdate()->findOrFail($data['allocationId']);
            $server = Server::query()->lockForUpdate()->findOrFail($data['assignServerId']);
            abort_unless($allocation->node_id === $server->node_id, 422, 'Allocation and server must belong to the same node.');
            abort_if(Allocation::query()->where('assigned_server_id', $server->id)->where('id', '!=', $allocation->id)->exists(), 409, 'Server already has an allocation.');
            $allocation->update(['assigned_server_id' => $server->id]);
        });
        $this->notice = 'Allocation assigned.';
        $this->noticeType = 'success';
    }

    public function unassignAllocation(string $id): void
    {
        $this->requireStaff();
        Allocation::query()->whereKey($id)->update(['assigned_server_id' => null]);
        $this->notice = 'Allocation unassigned.';
        $this->noticeType = 'success';
    }

    public function render(): View
    {
        $this->requireStaff();
        $nodes = Node::query()->withCount('servers')->orderBy('name')->get();
        $allocations = Allocation::query()->with(['node', 'server'])->orderBy('node_id')->orderBy('port')->paginate(40);
        $servers = Server::query()->orderBy('name')->get(['id', 'name', 'node_id']);

        return view('livewire.infrastructure.manager', compact('nodes', 'allocations', 'servers'))
            ->layout('layouts.app', ['title' => 'Nodes & allocations']);
    }

    private function requireStaff(): void
    {
        abort_unless(auth()->check() && auth()->user()->isStaff(), 403);
    }

    private function flashError(string $message): void
    {
        $this->notice = $message;
        $this->noticeType = 'error';
    }
}
