<?php

namespace App\Livewire;

use App\Models\Theme;
use App\Services\ThemeCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class ThemeManager extends Component
{
    use WithFileUploads;

    public string $themeId = '';
    public string $name = '';
    public string $author = 'Administrator';
    public string $description = '';
    public string $version = '1.0.0';
    public array $config = [];
    public array $layout = [];
    public string $activeId = 'snck-default';
    public string $shortcutLabel = '';
    public string $shortcutUrl = '';
    public string $shortcutTarget = '';
    public $package = null;
    public string $notice = '';
    public string $noticeType = 'success';

    public function mount(ThemeCatalog $catalog): void
    {
        $this->requireStaff();
        $this->resetEditor($catalog);
        $this->activeId = $catalog->activeId();
    }

    public function edit(string $id, ThemeCatalog $catalog): void
    {
        $this->requireStaff();
        $theme = Theme::query()->findOrFail($id);
        $this->themeId = $theme->id;
        $this->name = $theme->name;
        $this->author = $theme->author ?: 'Administrator';
        $this->description = $theme->description ?: '';
        $this->version = $theme->version;
        $this->config = $theme->config ?: ThemeCatalog::DEFAULT_CONFIG;
        $this->layout = $catalog->normalizeLayout($theme->layout);
        $this->notice = '';
    }

    public function newTheme(ThemeCatalog $catalog): void
    {
        $this->requireStaff();
        $this->resetEditor($catalog);
    }

    public function save(ThemeCatalog $catalog): void
    {
        $this->requireStaff();
        try {
            $data = $catalog->validateManifest([
                'id' => $this->themeId,
                'name' => $this->name,
                'author' => $this->author,
                'description' => $this->description,
                'version' => $this->version,
                'config' => $this->config,
                'layout' => $this->layout,
            ]);
            $existing = Theme::query()->find($data['id']);
            if ($existing && ($existing->built_in || $catalog->isBuiltInId($existing->id))) throw new \RuntimeException('Built-in themes are protected; clone one to customize it.');
            if ($existing) $existing->update($data);
            else Theme::query()->create($data + ['enabled' => true, 'built_in' => false, 'protected' => false]);
            $this->notice = $existing ? 'Theme saved.' : 'Theme created.';
            $this->noticeType = 'success';
        } catch (Throwable $exception) {
            $this->notice = $exception->getMessage();
            $this->noticeType = 'error';
        }
    }

    public function cloneTheme(string $id, ThemeCatalog $catalog): void
    {
        $this->requireStaff();
        $theme = collect($catalog->all())->firstWhere('id', $id);
        if (!$theme) { $this->flashError('Theme not found.'); return; }
        $base = Str::slug($theme['name']).'-copy';
        $newId = substr($base, 0, 40);
        $suffix = 2;
        while (Theme::query()->whereKey($newId)->exists()) $newId = substr($base, 0, 44).'-'.$suffix++;
        $this->themeId = $newId;
        $this->name = $theme['name'].' Copy';
        $this->author = auth()->user()->username ?: 'Administrator';
        $this->description = $theme['description'] ?? '';
        $this->version = '1.0.0';
        $this->config = $theme['config'];
        $this->layout = $catalog->normalizeLayout($theme['layout'] ?? null);
        $this->notice = 'Theme copied into the editor. Save to create your custom theme.';
        $this->noticeType = 'success';
    }

    public function toggle(string $id): void
    {
        $this->requireStaff();
        $theme = Theme::query()->findOrFail($id);
        $selectedId = app(ThemeCatalog::class)->activeId();
        if ($theme->id === 'snck-default' && $theme->enabled) { $this->flashError('Snck Default must remain enabled.'); return; }
        $theme->update(['enabled' => !$theme->enabled]);
        if (!$theme->enabled && $selectedId === $theme->id) {
            app(ThemeCatalog::class)->storeActive('snck-default');
            $this->activeId = 'snck-default';
        }
        $this->notice = 'Theme availability updated.';
        $this->noticeType = 'success';
    }

    public function apply(string $id, ThemeCatalog $catalog): void
    {
        $this->requireStaff();
        try { $catalog->apply($id); $this->activeId = $id; $this->notice = 'Theme applied.'; $this->noticeType = 'success'; }
        catch (Throwable $exception) { $this->flashError($exception->getMessage()); }
    }

    public function restoreDefault(ThemeCatalog $catalog): void
    {
        $this->requireStaff();
        $catalog->storeActive('snck-default');
        $this->activeId = 'snck-default';
        $this->notice = 'Snck Default restored.';
        $this->noticeType = 'success';
    }

    public function delete(string $id): void
    {
        $this->requireStaff();
        $theme = Theme::query()->findOrFail($id);
        if ($theme->built_in || $theme->protected || app(ThemeCatalog::class)->isBuiltInId($theme->id)) { $this->flashError('System themes are protected.'); return; }
        DB::transaction(function () use ($theme): void {
            if (app(ThemeCatalog::class)->activeId() === $theme->id) app(ThemeCatalog::class)->storeActive('snck-default');
            $theme->delete();
        });
        $this->activeId = app(ThemeCatalog::class)->activeId();
        $this->notice = 'Custom theme deleted.';
        $this->noticeType = 'success';
    }

    public function toggleHidden(string $collection, string $item): void
    {
        $this->requireStaff();
        $map = ['navigation' => ['navigationOrder', 'hiddenNavigation'], 'dashboard' => ['dashboardOrder', 'hiddenDashboard'], 'metrics' => ['metricsOrder', 'hiddenMetrics'], 'server' => ['structure.serverNavigationOrder', 'structure.hiddenServerNavigation']];
        abort_unless(isset($map[$collection]), 422);
        [$orderKey, $hiddenKey] = $map[$collection];
        $order = data_get($this->layout, $orderKey, []);
        abort_unless(in_array($item, $order, true), 422);
        $hidden = data_get($this->layout, $hiddenKey, []);
        $hidden = in_array($item, $hidden, true) ? array_values(array_diff($hidden, [$item])) : array_values(array_merge($hidden, [$item]));
        data_set($this->layout, $hiddenKey, $hidden);
    }

    public function moveItem(string $collection, string $item, int $direction): void
    {
        $this->requireStaff();
        abort_unless(in_array($direction, [-1, 1], true), 422);
        $map = ['navigation' => 'navigationOrder', 'dashboard' => 'dashboardOrder', 'metrics' => 'metricsOrder', 'server' => 'structure.serverNavigationOrder'];
        abort_unless(isset($map[$collection]), 422);
        $key = $map[$collection];
        $items = data_get($this->layout, $key, []);
        $index = array_search($item, $items, true);
        abort_unless($index !== false, 422);
        $target = $index + $direction;
        if ($target < 0 || $target >= count($items)) return;
        [$items[$index], $items[$target]] = [$items[$target], $items[$index]];
        data_set($this->layout, $key, $items);
    }

    public function setLabel(string $id, string $label): void
    {
        $this->requireStaff();
        $labels = $this->layout['labels'] ?? [];
        if (trim($label) === '') unset($labels[$id]); else $labels[$id] = mb_substr(trim($label), 0, 32);
        $this->layout['labels'] = $labels;
    }

    public function addShortcut(): void
    {
        $this->requireStaff();
        $label = trim($this->shortcutLabel);
        $url = trim($this->shortcutUrl);
        $target = $this->shortcutTarget;
        if ($label === '' || mb_strlen($label) > 32 || count($this->layout['customNavigation'] ?? []) >= 8 || (($url === '') === ($target === ''))) { $this->flashError('Provide a label and exactly one HTTPS URL or internal navigation target.'); return; }
        if ($url !== '') {
            $parsed = parse_url($url);
            if (($parsed['scheme'] ?? '') !== 'https' || isset($parsed['user']) || isset($parsed['pass']) || strlen($url) > 2048) { $this->flashError('Custom links must be HTTPS and may not contain credentials.'); return; }
        } elseif (!in_array($target, ThemeCatalog::NAV, true)) { $this->flashError('Choose a supported internal target.'); return; }
        $id = 'custom-'.Str::lower(Str::random(8));
        $item = ['id' => $id, 'label' => $label];
        if ($url !== '') $item['url'] = $url; else $item['target'] = $target;
        $this->layout['customNavigation'][] = $item;
        $this->shortcutLabel = $this->shortcutUrl = $this->shortcutTarget = '';
    }

    public function removeShortcut(string $id): void
    {
        $this->requireStaff();
        $this->layout['customNavigation'] = array_values(array_filter($this->layout['customNavigation'] ?? [], fn ($item) => $item['id'] !== $id));
    }

    public function import(ThemeCatalog $catalog): void
    {
        $this->requireStaff();
        $this->validate(['package' => ['required', 'file', 'max:2048']]);
        if (!str_ends_with(strtolower($this->package->getClientOriginalName()), '.zip')) { $this->flashError('Upload a ZIP theme package.'); return; }
        try {
            $theme = $catalog->importZip($this->package->getRealPath());
            $this->package = null;
            $this->notice = 'Imported '.$theme->name.'.';
            $this->noticeType = 'success';
        } catch (Throwable $exception) { $this->flashError($exception->getMessage()); }
    }

    public function render(ThemeCatalog $catalog): View
    {
        $this->requireStaff();
        return view('livewire.themes.manager', ['themes' => $catalog->all(), 'catalog' => $catalog])
            ->layout('layouts.app', ['title' => 'Snck Theme']);
    }

    private function resetEditor(ThemeCatalog $catalog): void
    {
        $this->themeId = '';
        $this->name = '';
        $this->author = auth()->user()->username ?: 'Administrator';
        $this->description = '';
        $this->version = '1.0.0';
        $this->config = ThemeCatalog::DEFAULT_CONFIG;
        $this->layout = $catalog->defaultLayout();
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
