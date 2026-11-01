<?php

namespace Tests\Feature;

use App\Livewire\InfrastructureManager;
use App\Models\Node;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InfrastructureManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_node_creation_shows_a_one_time_install_command_with_https_and_sftp_setup(): void
    {
        config(['app.url' => 'https://panel.example.test']);
        $admin = User::query()->create([
            'id' => 'infra-admin',
            'name' => 'Infrastructure admin',
            'username' => 'infra-admin',
            'email' => 'infra-admin@example.test',
            'role' => 'admin',
            'password' => 'correct horse battery staple',
        ]);
        $credential = str_repeat('a', 64);

        $component = Livewire::actingAs($admin)->test(InfrastructureManager::class)
            ->set('nodeId', 'node-01')
            ->set('nodeName', 'EU Node')
            ->set('host', '203.0.113.10')
            ->set('fqdn', 'node.example.test')
            ->set('daemonPort', 6768)
            ->set('credential', $credential)
            ->call('createNode');

        $node = Node::query()->findOrFail('node-01');
        $this->assertSame(hash('sha256', $credential), $node->credential_hash);
        $this->assertNotSame($credential, $node->credential_encrypted);

        $command = (string) $component->get('nodeInstallCommand');
        $this->assertStringContainsString('--panel', $command);
        $this->assertStringContainsString('https://panel.example.test', $command);
        $this->assertStringContainsString('--node-id', $command);
        $this->assertStringContainsString('--fqdn', $command);
        $this->assertStringContainsString('node.example.test', $command);
        $this->assertStringContainsString('--credential', $command);
        $this->assertStringContainsString($credential, $command);

        $component->call('dismissNodeInstallCommand')->assertSet('nodeInstallCommand', '');
    }

    public function test_regular_user_cannot_create_a_node_or_get_an_install_command(): void
    {
        $user = User::query()->create([
            'id' => 'infra-user',
            'name' => 'Regular user',
            'username' => 'infra-user',
            'email' => 'infra-user@example.test',
            'role' => 'user',
            'password' => 'correct horse battery staple',
        ]);

        Livewire::actingAs($user)->test(InfrastructureManager::class)->assertForbidden();
        $this->assertDatabaseCount('nodes', 0);
    }
}
