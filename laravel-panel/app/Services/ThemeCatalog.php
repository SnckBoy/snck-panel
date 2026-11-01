<?php

namespace App\Services;

use App\Models\Theme;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;
use ZipArchive;

class ThemeCatalog
{
    public const DEFAULT_CONFIG = [
        'primary' => '#9275ff', 'secondary' => '#5943b8', 'accent' => '#b29cff', 'background' => '#0a0a0f',
        'surface' => '#14141d', 'sidebar' => '#09090e', 'navbar' => '#111119', 'text' => '#f4f3fa', 'muted' => '#aaa7b8',
        'border' => '#302d3b', 'success' => '#39d98a', 'warning' => '#f0b24a', 'error' => '#f26b78', 'radius' => 16,
        'fontFamily' => 'inter', 'fontScale' => 1, 'density' => 'comfortable', 'elevation' => 'soft', 'surfaceOpacity' => 100,
    ];

    // Only expose destinations that have a working route in this migration stage.
    public const NAV = ['overview', 'servers', 'deploy', 'nodes', 'snck-theme'];
    public const DASH = ['welcome', 'quick-actions', 'metrics', 'system-health', 'server-inventory'];
    public const METRICS = ['cpu', 'ram', 'net', 'nodes'];
    public const SERVER_NAV = ['overview', 'console', 'players', 'properties', 'files', 'subusers', 'backups'];

    public function defaultLayout(): array
    {
        return [
            'navigationOrder' => self::NAV, 'hiddenNavigation' => [],
            'dashboardOrder' => self::DASH, 'hiddenDashboard' => ['quick-actions', 'system-health'],
            'metricsOrder' => self::METRICS, 'hiddenMetrics' => [], 'labels' => [], 'customNavigation' => [],
            'structure' => [
                'shell' => 'snck', 'navigationMode' => 'sidebar', 'sidebarMode' => 'expanded', 'contentWidth' => 'standard',
                'dashboardMode' => 'overview', 'serverNavigationMode' => 'sidebar',
                'serverNavigationOrder' => self::SERVER_NAV, 'hiddenServerNavigation' => [],
            ],
        ];
    }

    public function builtIns(): array
    {
        $presets = [
            ['snck-default', 'Snck Default', '#9275ff', '#0a0a0f', '#14141d'],
            ['midnight', 'Midnight', '#7099d8', '#080e1a', '#111a2a'],
            ['royal-purple', 'Royal Purple', '#a481da', '#160d25', '#28183e'],
            ['nebula', 'Nebula', '#9a7ceb', '#120d1b', '#20182e'],
            ['cyber-blue', 'Cyber Blue', '#57b5ba', '#071315', '#102326'],
            ['aurora', 'Aurora', '#7c6af0', '#0a0a0f', '#111117'],
            ['obsidian', 'Obsidian', '#b8b8c2', '#08080d', '#111117'],
        ];
        return array_map(function (array $item): array {
            [$id, $name, $primary, $background, $surface] = $item;
            $config = self::DEFAULT_CONFIG;
            $config['primary'] = $primary;
            $config['secondary'] = $primary;
            $config['accent'] = $primary;
            $config['background'] = $background;
            $config['sidebar'] = $background;
            $config['surface'] = $surface;
            $config['navbar'] = $surface;
            return ['id' => $id, 'name' => $name, 'version' => '1.0.0', 'author' => 'Snck', 'description' => $name.' color theme for Snck Panel.', 'enabled' => true, 'built_in' => true, 'protected' => true, 'config' => $config, 'layout' => $this->defaultLayout()];
        }, $presets);
    }

    public function isBuiltInId(string $id): bool
    {
        return in_array($id, array_column($this->builtIns(), 'id'), true);
    }

    public function all(): array
    {
        foreach ($this->builtIns() as $builtIn) {
            Theme::query()->firstOrCreate(['id' => $builtIn['id']], $builtIn);
        }
        $themes = Theme::query()->orderBy('built_in', 'desc')->orderBy('name')->get()->map(function (Theme $theme): ?array {
            $id = (string) $theme->id;
            $isBuiltIn = in_array($id, array_column($this->builtIns(), 'id'), true);
            if (!$isBuiltIn && !preg_match('/^[a-z0-9][a-z0-9-]{1,47}$/', $id)) return null;
            $config = $isBuiltIn ? null : $this->normalizeConfig($theme->config);
            if (!$isBuiltIn && !$config) return null;
            try { $layout = $this->normalizeLayout($theme->layout, true); }
            catch (Throwable) { $layout = $this->defaultLayout(); }
            $row = $theme->toArray();
            $row['built_in'] = $isBuiltIn;
            $row['protected'] = $isBuiltIn;
            $row['config'] = $isBuiltIn ? $row['config'] : $config;
            $row['layout'] = $layout;
            return $row;
        })->filter()->values()->all();
        $known = array_column($themes, null, 'id');
        foreach ($this->builtIns() as $builtIn) {
            if (isset($known[$builtIn['id']])) {
                $known[$builtIn['id']] = array_merge($builtIn, $known[$builtIn['id']], ['config' => $builtIn['config'], 'layout' => $this->normalizeLayout($known[$builtIn['id']]['layout'] ?? null)]);
            }
        }
        return array_values($known);
    }

    public function activeId(): string
    {
        if (!Schema::hasTable('settings') || !Schema::hasTable('themes')) return 'snck-default';
        $setting = DB::table('settings')->where('key', 'snckThemeId')->value('value');
        $id = is_string($setting) ? json_decode($setting, true) : null;
        foreach ($this->all() as $theme) if ($theme['id'] === $id && $theme['enabled']) return $id;
        return 'snck-default';
    }

    public function activeTheme(): array
    {
        $fallback = $this->builtIns()[0];
        if (!Schema::hasTable('themes') || !Schema::hasTable('settings')) return $fallback;
        $id = $this->activeId();
        return collect($this->all())->firstWhere('id', $id) ?? $fallback;
    }

    public function apply(string $id): void
    {
        $theme = collect($this->all())->firstWhere('id', $id);
        if (!$theme || !$theme['enabled']) throw new RuntimeException('Theme is unavailable or disabled.');
        $this->storeActive($id);
    }

    public function storeActive(string $id): void
    {
        DB::table('settings')->updateOrInsert(['key' => 'snckThemeId'], ['value' => json_encode($id, JSON_THROW_ON_ERROR), 'is_encrypted' => false, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function validateManifest(array $input, ?string $fixedId = null): array
    {
        $allowed = ['id', 'name', 'version', 'author', 'description', 'config', 'layout', 'supportedPanelVersion'];
        if (array_diff(array_keys($input), $allowed)) throw new RuntimeException('Theme manifest contains unsupported fields.');
        $id = strtolower(trim((string) ($fixedId ?? $input['id'] ?? '')));
        if (!preg_match('/^[a-z0-9][a-z0-9-]{1,47}$/', $id) || in_array($id, array_column($this->builtIns(), 'id'), true)) throw new RuntimeException('Theme ID must be 2–48 lowercase letters, numbers, or hyphens and cannot match a system theme.');
        $name = trim((string) ($input['name'] ?? ''));
        $author = trim((string) ($input['author'] ?? 'Administrator'));
        $description = trim((string) ($input['description'] ?? ''));
        $version = trim((string) ($input['version'] ?? '1.0.0'));
        if ($name === '' || mb_strlen($name) > 64 || $author === '' || mb_strlen($author) > 64 || mb_strlen($description) > 240 || !preg_match('/^\d+\.\d+\.\d+(?:[-+][a-zA-Z0-9.-]+)?$/', $version)) throw new RuntimeException('Theme name, author, description, or semantic version is invalid.');
        $config = $this->normalizeConfig($input['config'] ?? null);
        if (!$config) throw new RuntimeException('Theme configuration is invalid.');
        $layout = array_key_exists('layout', $input) ? $this->normalizeLayout($input['layout'], true) : $this->defaultLayout();
        return compact('id', 'name', 'author', 'description', 'version', 'config', 'layout');
    }

    public function normalizeLayout(mixed $value, bool $strict = false): array
    {
        $default = $this->defaultLayout();
        if (!is_array($value)) {
            if ($strict) throw new RuntimeException('Theme layout is invalid.');
            return $default;
        }
        $required = ['navigationOrder', 'hiddenNavigation', 'dashboardOrder', 'hiddenDashboard', 'metricsOrder', 'hiddenMetrics'];
        $allowed = array_merge($required, ['structure', 'labels', 'customNavigation']);
        if (array_diff($required, array_keys($value)) || array_diff(array_keys($value), $allowed)) throw new RuntimeException('Theme layout contains unsupported fields.');
        foreach ([['navigationOrder', self::NAV, true], ['hiddenNavigation', self::NAV, false], ['dashboardOrder', self::DASH, true], ['hiddenDashboard', self::DASH, false], ['metricsOrder', self::METRICS, true], ['hiddenMetrics', self::METRICS, false]] as [$key, $catalog, $permutation]) if (!$this->validList($value[$key], $catalog, $permutation)) throw new RuntimeException('Theme layout contains invalid or duplicate items.');
        if (array_key_exists('structure', $value) && !is_array($value['structure'])) throw new RuntimeException('Theme structure is invalid.');
        $rawStructure = $value['structure'] ?? [];
        $structure = array_merge($default['structure'], $rawStructure);
        $structureKeys = array_keys($default['structure']);
        if (array_diff(array_keys($structure), $structureKeys) || array_diff($structureKeys, array_keys($structure))) throw new RuntimeException('Theme structure has unsupported fields.');
        foreach (['shell' => ['snck', 'pterodactyl'], 'navigationMode' => ['sidebar', 'topbar'], 'sidebarMode' => ['expanded', 'compact'], 'contentWidth' => ['standard', 'wide', 'full'], 'dashboardMode' => ['overview', 'server-first'], 'serverNavigationMode' => ['sidebar', 'tabs']] as $key => $options) if (!in_array($structure[$key], $options, true)) throw new RuntimeException('Theme structure contains an unsupported value.');
        if (!$this->validList($structure['serverNavigationOrder'], self::SERVER_NAV, true) || !$this->validList($structure['hiddenServerNavigation'], self::SERVER_NAV, false) || !array_diff($structure['serverNavigationOrder'], $structure['hiddenServerNavigation'])) throw new RuntimeException('Server navigation layout is invalid.');
        $labels = $value['labels'] ?? [];
        $labelIds = array_unique(array_merge(self::NAV, self::SERVER_NAV));
        if (!is_array($labels) || count($labels) > count($labelIds)) throw new RuntimeException('Theme labels are invalid.');
        foreach ($labels as $key => $label) if (!in_array($key, $labelIds, true) || !is_string($label) || trim($label) === '' || mb_strlen(trim($label)) > 32) throw new RuntimeException('Theme labels are invalid.');
        $custom = $value['customNavigation'] ?? [];
        if (!is_array($custom) || count($custom) > 8) throw new RuntimeException('Theme custom navigation is invalid.');
        $seen = [];
        foreach ($custom as $item) {
            if (!is_array($item) || array_diff(array_keys($item), ['id', 'label', 'url', 'target']) || !isset($item['id'], $item['label']) || (isset($item['url']) === isset($item['target']))) throw new RuntimeException('Theme custom navigation is invalid.');
            if (!preg_match('/^custom-[a-z0-9-]{1,32}$/', (string) $item['id']) || isset($seen[$item['id']]) || !is_string($item['label']) || trim($item['label']) === '' || mb_strlen(trim($item['label'])) > 32) throw new RuntimeException('Theme custom navigation is invalid.');
            if (isset($item['target']) && !in_array($item['target'], self::NAV, true)) throw new RuntimeException('Theme custom navigation target is invalid.');
            if (isset($item['url'])) { if (!is_string($item['url'])) throw new RuntimeException('Theme shortcuts must use safe HTTPS URLs.'); $url = parse_url($item['url']); if (strlen($item['url']) > 2048 || ($url['scheme'] ?? '') !== 'https' || empty($url['host']) || isset($url['user']) || isset($url['pass'])) throw new RuntimeException('Theme shortcuts must use safe HTTPS URLs.'); }
            $seen[$item['id']] = true;
        }
        $result = array_merge(array_intersect_key($default, array_flip($required)), array_intersect_key($value, array_flip($required)));
        $result['structure'] = $structure;
        $result['labels'] = $labels;
        $result['customNavigation'] = $custom;
        if (!array_intersect(array_diff(self::NAV, $result['hiddenNavigation']), ['overview', 'servers', 'nodes']) || !array_diff($result['dashboardOrder'], $result['hiddenDashboard'])) throw new RuntimeException('At least one public navigation item and dashboard section must remain visible.');
        return $result;
    }

    public function export(string $id): string
    {
        $theme = Theme::query()->findOrFail($id);
        $safeTheme = collect($this->all())->firstWhere('id', $theme->id);
        if (!$safeTheme) throw new RuntimeException('Theme data is invalid and cannot be exported.');
        $directory = storage_path('app/private/theme-packages');
        if (is_link($directory)) throw new RuntimeException('Theme export directory may not be a symbolic link.');
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) throw new RuntimeException('Unable to create private theme export directory.');
        chmod($directory, 0700);
        $path = tempnam($directory, 'theme-');
        if (!$path) throw new RuntimeException('Unable to create theme package.');
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) { @unlink($path); throw new RuntimeException('Unable to create theme ZIP.'); }
        $manifest = ['id' => $theme->id, 'name' => $theme->name, 'version' => $theme->version, 'author' => $theme->author ?: 'Administrator', 'description' => $theme->description ?: '', 'supportedPanelVersion' => '>=0.2.0', 'config' => $safeTheme['config'], 'layout' => $safeTheme['layout']];
        $zip->addFromString($theme->id.'/theme.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        $zip->close();
        chmod($path, 0600);
        return $path;
    }

    public function importZip(string $path): Theme
    {
        if (!is_file($path) || filesize($path) > 2 * 1024 * 1024) throw new RuntimeException('Theme ZIP must not exceed 2 MiB.');
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) throw new RuntimeException('Invalid theme ZIP archive.');
        try {
            if ($zip->numFiles < 1 || $zip->numFiles > 100) throw new RuntimeException('Theme archive must contain at most 100 entries.');
            $manifest = null;
            $expanded = 0;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i, ZipArchive::FL_UNCHANGED);
                if (!$stat) throw new RuntimeException('Invalid ZIP entry.');
                $name = str_replace('\\', '/', $stat['name']);
                if ($name === '' || str_starts_with($name, '/') || preg_match('/^[A-Za-z]:/', $name) || in_array('..', explode('/', $name), true) || str_contains($name, "\0")) throw new RuntimeException('Unsafe theme archive path.');
                if ($zip->getExternalAttributesIndex($i, $opsys, $attributes) && $opsys === ZipArchive::OPSYS_UNIX) {
                    $mode = ($attributes >> 16) & 0170000;
                    if ($mode === 0120000) throw new RuntimeException('Theme archives may not contain symlinks.');
                }
                $expanded += (int) $stat['size'];
                if ($expanded > 32768 || (int) $stat['comp_size'] > 2 * 1024 * 1024) throw new RuntimeException('Theme manifest or expanded archive is too large.');
                if (str_ends_with($name, '/')) continue;
                if ($manifest !== null || !preg_match('~^[a-z0-9][a-z0-9-]{1,47}/theme\.json$~', $name)) throw new RuntimeException('Only one JSON theme.json manifest is accepted; assets, styles, and scripts are rejected.');
                $contents = $zip->getFromIndex($i);
                if (!is_string($contents) || strlen($contents) > 32768) throw new RuntimeException('Theme manifest is too large or unreadable.');
                $manifest = json_decode($contents, true, 32, JSON_THROW_ON_ERROR);
                if (!is_array($manifest) || basename(dirname($name)) !== ($manifest['id'] ?? null)) throw new RuntimeException('Theme manifest path and ID do not match.');
            }
        } finally { $zip->close(); }
        if (!is_array($manifest) || !is_string($manifest['supportedPanelVersion'] ?? null) || !preg_match('/^\s*(?:\^|>=)?\s*0\.2(?:\.\d+)?(?:\s|$)/', $manifest['supportedPanelVersion'])) throw new RuntimeException('Theme package does not declare compatibility with this Snck Panel version.');
        $data = $this->validateManifest($manifest);
        if (Theme::query()->whereKey($data['id'])->exists()) throw new RuntimeException('A theme with that ID already exists.');
        return Theme::query()->create($data + ['enabled' => true, 'built_in' => false, 'protected' => false]);
    }

    private function validConfig(mixed $config): bool
    {
        if (!is_array($config) || array_diff(array_keys($config), array_keys(self::DEFAULT_CONFIG)) || array_diff(array_keys(self::DEFAULT_CONFIG), array_keys($config))) return false;
        foreach (['primary','secondary','accent','background','surface','sidebar','navbar','text','muted','border','success','warning','error'] as $key) if (!is_string($config[$key]) || !preg_match('/^#[0-9a-fA-F]{6}$/', $config[$key])) return false;
        return is_numeric($config['radius']) && $config['radius'] >= 0 && $config['radius'] <= 32
            && in_array($config['fontFamily'], ['system','inter','mono'], true)
            && is_numeric($config['fontScale']) && $config['fontScale'] >= .85 && $config['fontScale'] <= 1.25
            && in_array($config['density'], ['compact','comfortable','spacious'], true)
            && in_array($config['elevation'], ['flat','soft','glow'], true)
            && is_int($config['surfaceOpacity']) && $config['surfaceOpacity'] >= 55 && $config['surfaceOpacity'] <= 100;
    }

    private function normalizeConfig(mixed $config): ?array
    {
        if (!is_array($config) || array_diff(array_keys($config), array_keys(self::DEFAULT_CONFIG))) return null;
        $config = array_merge(self::DEFAULT_CONFIG, $config);
        if (!is_numeric($config['radius'] ?? null) || !is_numeric($config['fontScale'] ?? null) || !is_numeric($config['surfaceOpacity'] ?? null) || (int) $config['surfaceOpacity'] != $config['surfaceOpacity']) return null;
        $config['radius'] = (float) $config['radius'];
        $config['fontScale'] = (float) $config['fontScale'];
        $config['surfaceOpacity'] = (int) $config['surfaceOpacity'];
        return $this->validConfig($config) ? $config : null;
    }

    private function validList(mixed $value, array $catalog, bool $permutation): bool
    {
        if (!is_array($value) || !array_is_list($value)) return false;
        foreach ($value as $item) if (!is_string($item)) return false;
        return count(array_unique($value)) === count($value) && count(array_diff($value, $catalog)) === 0 && (!$permutation || count($value) === count($catalog));
    }
}
