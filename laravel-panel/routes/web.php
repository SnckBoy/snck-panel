<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ThemeExportController;
use App\Livewire\Dashboard;
use App\Livewire\AdminUsers;
use App\Livewire\InfrastructureManager;
use App\Livewire\ServerCreate;
use App\Livewire\ServerDetail;
use App\Livewire\ServerIndex;
use App\Livewire\ThemeManager;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(User::query()->exists() ? 'login' : 'setup'))->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/setup', [AuthController::class, 'setupForm'])->middleware('throttle:setup')->name('setup');
    Route::post('/setup', [AuthController::class, 'setup'])->middleware('throttle:setup')->name('setup.store');
    Route::get('/install', [AuthController::class, 'setupForm'])->middleware('throttle:setup')->name('install');
    Route::post('/install', [AuthController::class, 'setup'])->middleware('throttle:setup')->name('install.store');
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.store');
    Route::get('/register', [AuthController::class, 'registerForm'])->middleware('throttle:register')->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register')->name('register.store');
    Route::get('/auth/google/redirect', [AuthController::class, 'googleRedirect'])->middleware('throttle:google-auth')->name('google.redirect');
    Route::get('/auth/google/callback', [AuthController::class, 'googleCallback'])->middleware('throttle:google-auth')->name('google.callback');
});

Route::middleware(['auth', 'account.enabled'])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/servers', ServerIndex::class)->name('servers.index');
    Route::get('/servers/create', ServerCreate::class)->name('servers.create');
    Route::get('/servers/{server}', ServerDetail::class)->name('servers.show');
    Route::middleware('role:owner,admin')->group(function (): void {
        Route::get('/themes', ThemeManager::class)->name('themes.index');
        Route::get('/themes/{theme}/export', ThemeExportController::class)->name('themes.export');
        Route::get('/nodes', InfrastructureManager::class)->name('nodes.index');
        Route::get('/allocations', InfrastructureManager::class)->defaults('tab', 'allocations')->name('allocations.index');
        Route::get('/admin/users', AdminUsers::class)->name('admin.users');
    });
});
