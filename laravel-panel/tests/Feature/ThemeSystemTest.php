<?php

namespace Tests\Feature;

use App\Models\Theme;
use App\Models\Node;
use App\Models\Server;
use App\Models\User;
use App\Services\ThemeCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;
use ZipArchive;

class ThemeSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_contains_seven_protected_builtins_and_applies_enabled_theme(): void
    {
        $catalog = app(ThemeCatalog::class);
        $themes = $catalog->all();
        $this->assertCount(7, array_filter($themes, fn (array $theme) => $theme['built_in']));
        $catalog->apply('midnight');
        $this->assertSame('midnight', $catalog->activeId());
        $this->assertSame('midnight', DB::table('settings')->where('key', 'snckThemeId')->value('value') ? json_decode(DB::table('settings')->where('key', 'snckThemeId')->value('value'), true) : null);
    }

    public function test_theme_export_contains_only_theme_json_and_import_round_trips_manifest(): void
    {
        $catalog = app(ThemeCatalog::class);
        $theme = Theme::query()->create([
            'id' => 'custom-night', 'name' => 'Custom Night', 'version' => '1.0.0', 'author' => 'Tester',
            'description' => 'A test theme.', 'enabled' => true, 'built_in' => false, 'protected' => false,
            'config' => ThemeCatalog::DEFAULT_CONFIG, 'layout' => $catalog->defaultLayout(),
        ]);
        $path = $catalog->export($theme->id);
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);
        $this->assertSame(1, $zip->numFiles);
        $this->assertSame('custom-night/theme.json', $zip->getNameIndex(0));
        $manifest = json_decode($zip->getFromIndex(0), true, 32, JSON_THROW_ON_ERROR);
        $zip->close();
        $manifest['id'] = 'imported-night';
        $manifest['name'] = 'Imported Night';
        $manifest['config'] = ThemeCatalog::DEFAULT_CONFIG;
        $manifest['supportedPanelVersion'] = '>=0.2.0';
        $package = $this->zipWithManifest('imported-night/theme.json', json_encode($manifest, JSON_THROW_ON_ERROR));
        $imported = $catalog->importZip($package);
        $this->assertSame('Imported Night', $imported->name);
        $this->assertFalse($imported->built_in);
        $this->assertSame(ThemeCatalog::DEFAULT_CONFIG['primary'], $imported->config['primary']);
        @unlink($path);
        @unlink($package);
    }

    public function test_theme_import_rejects_assets_traversal_and_unsupported_manifest_fields(): void
    {
        $catalog = app(ThemeCatalog::class);
        $manifest = ['id'=>'secure-test','name'=>'Secure Test','version'=>'1.0.0','author'=>'Tester','description'=>'','supportedPanelVersion'=>'>=0.2.0','config'=>ThemeCatalog::DEFAULT_CONFIG,'layout'=>$catalog->defaultLayout()];
        $assetPackage = $this->zipWithManifest('secure-test/theme.json', json_encode($manifest, JSON_THROW_ON_ERROR), ['secure-test/theme.css' => 'body{}']);
        try { $catalog->importZip($assetPackage); $this->fail('An archive containing non-JSON assets must be rejected.'); }
        catch (RuntimeException $exception) { $this->assertStringContainsString('Only one JSON', $exception->getMessage()); }
        @unlink($assetPackage);

        $manifest['unsupported'] = true;
        $badFields = $this->zipWithManifest('secure-test/theme.json', json_encode($manifest, JSON_THROW_ON_ERROR));
        try { $catalog->importZip($badFields); $this->fail('Unsupported fields must be rejected.'); }
        catch (RuntimeException $exception) { $this->assertStringContainsString('unsupported fields', $exception->getMessage()); }
        @unlink($badFields);
    }

    public function test_partial_legacy_theme_config_is_merged_with_canonical_defaults(): void
    {
        $catalog = app(ThemeCatalog::class);
        $manifest = ['id'=>'legacy-partial','name'=>'Legacy Partial','version'=>'1.0.0','author'=>'Tester','description'=>'','supportedPanelVersion'=>'>=0.2.0','config'=>['primary'=>'#123456']];
        $package = $this->zipWithManifest('legacy-partial/theme.json', json_encode($manifest, JSON_THROW_ON_ERROR));
        $theme = $catalog->importZip($package);
        $this->assertSame('#123456', $theme->config['primary']);
        $this->assertSame(ThemeCatalog::DEFAULT_CONFIG['surface'], $theme->config['surface']);
        $this->assertSame($catalog->defaultLayout()['navigationOrder'], $theme->layout['navigationOrder']);
        @unlink($package);
    }

    public function test_theme_import_rejects_path_traversal_and_symlink_entries(): void
    {
        $catalog = app(ThemeCatalog::class);
        $manifest = ['id'=>'safe-package','name'=>'Safe Package','version'=>'1.0.0','author'=>'Tester','description'=>'','supportedPanelVersion'=>'>=0.2.0','config'=>ThemeCatalog::DEFAULT_CONFIG];
        $traversal = $this->zipWithManifest('../safe-package/theme.json', json_encode($manifest, JSON_THROW_ON_ERROR));
        try { $catalog->importZip($traversal); $this->fail('Traversal must be rejected.'); }
        catch (RuntimeException $exception) { $this->assertStringContainsString('Unsafe theme archive path', $exception->getMessage()); }
        @unlink($traversal);

        $path = tempnam(sys_get_temp_dir(), 'snck-theme-link-');
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true);
        $zip->addFromString('safe-package/', '');
        $zip->addFromString('safe-package/theme.json', json_encode($manifest, JSON_THROW_ON_ERROR));
        $zip->addFromString('safe-package/link', 'target');
        $this->assertTrue($zip->setExternalAttributesName('safe-package/link', ZipArchive::OPSYS_UNIX, 0120777 << 16));
        $zip->close();
        try { $catalog->importZip($path); $this->fail('Symlinks must be rejected.'); }
        catch (RuntimeException $exception) { $this->assertStringContainsString('symlinks', $exception->getMessage()); }
        @unlink($path);
    }

    public function test_theme_builder_is_staff_only_and_users_cannot_access_export(): void
    {
        $member = User::factory()->create(['role'=>'user']);
        $this->actingAs($member)->get('/themes')->assertForbidden();
        $this->actingAs($member)->get('/themes/snck-default/export')->assertForbidden();
    }

    public function test_staff_can_render_phase_four_management_pages(): void
    {
        $owner = User::factory()->create(['role'=>'owner']);
        $node = Node::query()->create(['id'=>'phase4-node','name'=>'Phase 4 node']);
        $server = Server::query()->create(['id'=>'phase4-server','identifier'=>'phase4-server','owner_id'=>$owner->id,'node_id'=>$node->id,'name'=>'Phase 4 server']);
        $this->actingAs($owner)->get('/themes')->assertOk()->assertSee('Theme builder');
        $this->actingAs($owner)->get('/nodes')->assertOk()->assertSee('Register daemon node');
        $this->actingAs($owner)->get('/allocations')->assertOk()->assertSee('Create port range');
        $this->actingAs($owner)->get('/servers/'.$server->id)->assertOk()->assertSee('Phase 4 server');
    }

    private function zipWithManifest(string $manifestPath, string $manifest, array $extra = []): string
    {
        $path = tempnam(sys_get_temp_dir(), 'snck-theme-test-');
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true);
        $zip->addFromString(dirname($manifestPath).'/', '');
        $zip->addFromString($manifestPath, $manifest);
        foreach ($extra as $name => $contents) $zip->addFromString($name, $contents);
        $zip->close();
        return $path;
    }
}
