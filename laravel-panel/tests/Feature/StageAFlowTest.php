<?php

namespace Tests\Feature;

use App\Livewire\ServerDetail;
use App\Models\User;
use App\Services\ThemeCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Attributes\Locked;
use ReflectionProperty;
use Tests\TestCase;

class StageAFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_open_the_browser_server_creation_page(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)
            ->get('/servers/create')
            ->assertOk()
            ->assertSee('Create server')
            ->assertSee('Choose a Docker-ready node');
    }

    public function test_server_detail_identifier_is_locked_and_unported_navigation_is_not_catalogued(): void
    {
        $property = new ReflectionProperty(ServerDetail::class, 'serverId');
        $this->assertCount(1, $property->getAttributes(Locked::class));
        $this->assertNotContains('admin-control', ThemeCatalog::NAV);
        $this->assertNotContains('api-keys', ThemeCatalog::NAV);
        $this->assertNotContains('cloudflare', ThemeCatalog::NAV);
        $this->assertContains('players', ThemeCatalog::SERVER_NAV);
        $this->assertContains('properties', ThemeCatalog::SERVER_NAV);
        $this->assertNotContains('plugins', ThemeCatalog::SERVER_NAV);
        $this->assertNotContains('playit', ThemeCatalog::SERVER_NAV);
    }
}
