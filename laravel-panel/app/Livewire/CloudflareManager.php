<?php

namespace App\Livewire;

use App\Services\CloudflareService;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Throwable;

class CloudflareManager extends Component
{
    public array $zones = [];

    public array $records = [];

    public string $zoneId = '';

    public string $recordId = '';

    public string $type = 'A';

    public string $name = '';

    public string $content = '';

    public int $ttl = 1;

    public bool $proxied = false;

    public string $priority = '10';

    public string $dataService = '';

    public string $dataProto = '_tcp';

    public string $dataName = '';

    public string $dataWeight = '5';

    public string $dataPort = '25565';

    public string $dataTarget = '';

    public string $message = '';

    public string $error = '';

    public function mount(CloudflareService $cloudflare): void
    {
        abort_unless(auth()->user()?->isStaff(), 403);
        try {
            $this->zones = $cloudflare->zones();
            $this->zoneId = (string) ($this->zones[0]['id'] ?? '');
            $this->loadRecords($cloudflare);
        } catch (Throwable $exception) {
            $this->error = $exception->getMessage();
        }
    }

    public function selectZone(CloudflareService $cloudflare): void
    {
        $this->resetEditor();
        $this->loadRecords($cloudflare);
    }

    public function editRecord(array $record): void
    {
        $this->recordId = (string) ($record['id'] ?? '');
        $this->type = (string) ($record['type'] ?? 'A');
        $this->name = (string) ($record['name'] ?? '');
        $this->content = (string) ($record['content'] ?? '');
        $this->ttl = (int) ($record['ttl'] ?? 1);
        $this->proxied = (bool) ($record['proxied'] ?? false);
        $this->priority = (string) ($record['priority'] ?? '10');
        $data = is_array($record['data'] ?? null) ? $record['data'] : [];
        $this->dataService = (string) ($data['service'] ?? '');
        $this->dataProto = (string) ($data['proto'] ?? '_tcp');
        $this->dataName = (string) ($data['name'] ?? '');
        $this->dataWeight = (string) ($data['weight'] ?? '5');
        $this->dataPort = (string) ($data['port'] ?? '25565');
        $this->dataTarget = (string) ($data['target'] ?? '');
    }

    public function save(CloudflareService $cloudflare): void
    {
        $this->validate([
            'zoneId' => ['required', 'string'], 'type' => ['required', 'in:A,AAAA,CNAME,TXT,MX,SRV'], 'name' => ['required', 'string', 'max:255'],
            'content' => ['required_unless:type,SRV', 'nullable', 'string', 'max:2048'], 'ttl' => ['required', 'integer', 'min:60', 'max:86400'],
            'priority' => ['required_if:type,MX', 'nullable', 'integer', 'min:0', 'max:65535'], 'dataService' => ['required_if:type,SRV', 'nullable', 'string', 'max:255'],
            'dataProto' => ['required_if:type,SRV', 'nullable', 'string', 'max:32'], 'dataName' => ['required_if:type,SRV', 'nullable', 'string', 'max:255'],
            'dataWeight' => ['required_if:type,SRV', 'nullable', 'integer', 'min:0', 'max:65535'], 'dataPort' => ['required_if:type,SRV', 'nullable', 'integer', 'min:1', 'max:65535'], 'dataTarget' => ['required_if:type,SRV', 'nullable', 'string', 'max:255'],
        ]);
        $payload = ['type' => $this->type, 'name' => $this->name, 'ttl' => $this->ttl];
        if ($this->type === 'SRV') {
            $payload['data'] = ['service' => $this->dataService, 'proto' => $this->dataProto, 'name' => $this->dataName, 'priority' => (int) $this->priority, 'weight' => (int) $this->dataWeight, 'port' => (int) $this->dataPort, 'target' => $this->dataTarget];
        } else {
            $payload['content'] = $this->content;
            if (in_array($this->type, ['MX', 'TXT'], true)) {
                $payload['priority'] = $this->type === 'MX' ? (int) $this->priority : null;
            }
            if (! in_array($this->type, ['MX', 'TXT'], true)) {
                $payload['proxied'] = $this->proxied;
            }
        }
        try {
            $this->recordId === '' ? $cloudflare->createDns($this->zoneId, $payload) : $cloudflare->updateDns($this->zoneId, $this->recordId, $payload);
            $this->resetEditor();
            $this->loadRecords($cloudflare);
            $this->message = 'DNS record saved.';
            $this->error = '';
        } catch (Throwable $exception) {
            $this->error = $exception->getMessage();
            $this->message = '';
        }
    }

    public function delete(CloudflareService $cloudflare, string $id): void
    {
        try {
            $cloudflare->deleteDns($this->zoneId, $id);
            $this->loadRecords($cloudflare);
            $this->message = 'DNS record deleted.';
            $this->error = '';
        } catch (Throwable $exception) {
            $this->error = $exception->getMessage();
        }
    }

    public function fillMinecraftSrv(): void
    {
        $this->type = 'SRV';
        $this->dataService = '_minecraft';
        $this->dataProto = '_tcp';
        $this->dataWeight = '5';
        $this->dataPort = '25565';
    }

    private function loadRecords(CloudflareService $cloudflare): void
    {
        $this->records = $this->zoneId === '' ? [] : $cloudflare->dns($this->zoneId);
    }

    private function resetEditor(): void
    {
        $this->recordId = '';
        $this->type = 'A';
        $this->name = '';
        $this->content = '';
        $this->ttl = 1;
        $this->proxied = false;
        $this->priority = '10';
        $this->dataService = '';
        $this->dataProto = '_tcp';
        $this->dataName = '';
        $this->dataWeight = '5';
        $this->dataPort = '25565';
        $this->dataTarget = '';
    }

    public function render(): View
    {
        return view('livewire.cloudflare.manager')->layout('layouts.app');
    }
}
