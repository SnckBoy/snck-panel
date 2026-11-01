<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function setupForm(): View|RedirectResponse
    {
        if (User::query()->exists()) {
            return redirect()->route('login');
        }

        return view('auth.setup');
    }

    public function setup(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:32', 'regex:/^[a-zA-Z0-9._-]{3,32}$/', 'unique:users,username'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'string', 'min:8', 'max:256'],
        ]);
        $data['username'] = Str::lower(trim($data['username']));
        $data['email'] = Str::lower(trim($data['email']));

        try {
            $user = DB::transaction(function () use ($data): User {
                $state = DB::table('setup_state')->where('id', 1)->lockForUpdate()->first();
                abort_unless($state, 503, 'Panel setup is not initialized.');
                if (User::query()->exists() || $state->completed_at !== null) {
                    throw ValidationException::withMessages(['setup' => 'Initial setup has already been completed.']);
                }

                $user = User::create([
                    'name' => $data['username'],
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'role' => 'owner',
                    'password' => $data['password'],
                ]);
                DB::table('setup_state')->where('id', 1)->update([
                    'owner_user_id' => $user->id,
                    'completed_at' => now(),
                ]);

                return $user;
            });
        } catch (QueryException $exception) {
            if (in_array((string) ($exception->errorInfo[0] ?? $exception->getCode()), ['23505', '23000'], true)) {
                return redirect()->route('login')->withErrors(['setup' => 'Initial setup has already been completed.']);
            }
            throw $exception;
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function loginForm(): View|RedirectResponse
    {
        if (! User::query()->exists()) {
            return redirect()->route('setup');
        }
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login', ['registrationEnabled' => $this->registrationEnabled()]);
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'identity' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:256'],
            'remember' => ['sometimes', 'boolean'],
        ]);
        $identity = Str::lower(trim($data['identity']));
        $user = User::query()->where(function ($query) use ($identity): void {
            $query->where('username', $identity)->orWhere('email', $identity);
        })->first();

        if (! $user || $user->disabled || ! Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['identity' => 'The credentials are invalid or the account is disabled.'])->onlyInput('identity');
        }

        Auth::login($user, (bool) ($data['remember'] ?? false));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function registerForm(): View|RedirectResponse
    {
        if (! User::query()->exists()) {
            return redirect()->route('setup');
        }
        if (! $this->registrationEnabled()) {
            return redirect()->route('login')->withErrors(['registration' => 'Registration is disabled by the panel owner.']);
        }

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        abort_unless(User::query()->exists(), 403, 'Complete first-run setup before registering users.');
        abort_unless($this->registrationEnabled(), 403, 'Registration is disabled by the panel owner.');

        $data = $request->validate([
            'username' => ['required', 'string', 'max:32', 'regex:/^[a-zA-Z0-9._-]{3,32}$/', 'unique:users,username'],
            'email' => ['nullable', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'string', 'min:8', 'max:256'],
        ]);
        $username = Str::lower(trim($data['username']));
        $email = isset($data['email']) && $data['email'] !== '' ? Str::lower(trim($data['email'])) : null;
        $user = User::create([
            'name' => $username,
            'username' => $username,
            'email' => $email,
            'role' => 'user',
            'password' => $data['password'],
        ]);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function googleRedirect(): RedirectResponse
    {
        abort_unless(config('services.google.client_id') && config('services.google.client_secret'), 404);

        return Socialite::driver('google')->scopes(['openid', 'email', 'profile'])->redirect();
    }

    public function googleCallback(Request $request): RedirectResponse
    {
        abort_unless(config('services.google.client_id') && config('services.google.client_secret'), 404);
        if (! User::query()->exists()) {
            return redirect()->route('setup');
        }

        $google = Socialite::driver('google')->user();
        $raw = $google->getRaw();
        $email = Str::lower(trim((string) $google->getEmail()));
        $emailVerified = filter_var($raw['email_verified'] ?? false, FILTER_VALIDATE_BOOL);
        abort_unless(filter_var($email, FILTER_VALIDATE_EMAIL) && $emailVerified, 403, 'Google must return a verified email address.');

        $user = DB::transaction(function () use ($google, $email): ?User {
            $user = User::query()->where('google_id', (string) $google->getId())
                ->orWhere('email', $email)->lockForUpdate()->first();

            if (! $user) {
                if (! $this->registrationEnabled()) {
                    return null;
                }
                $username = $this->availableUsername(Str::before($email, '@'));
                $user = User::create([
                    'name' => (string) ($google->getName() ?: $username),
                    'username' => $username,
                    'email' => $email,
                    'role' => 'user',
                    'password' => Str::random(64),
                    'google_id' => (string) $google->getId(),
                    'email_verified_at' => now(),
                ]);
            } elseif ($user->google_id && $user->google_id !== (string) $google->getId()) {
                return null;
            } else {
                $user->forceFill([
                    'google_id' => (string) $google->getId(),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();
            }

            return $user;
        });

        if (! $user || $user->disabled) {
            return redirect()->route('login')->withErrors(['identity' => 'This Google account cannot access the panel.']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function registrationEnabled(): bool
    {
        $value = DB::table('settings')->where('key', 'enableRegistration')->value('value');
        if (is_bool($value)) {
            return $value;
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return $decoded === true || $decoded === 1 || $decoded === 'true';
        }

        return false;
    }

    private function availableUsername(string $candidate): string
    {
        $base = Str::lower(preg_replace('/[^a-zA-Z0-9._-]/', '-', $candidate) ?: 'player');
        $base = trim(substr($base, 0, 24), '.-_') ?: 'player';
        $name = $base;
        $counter = 1;
        while (User::query()->where('username', $name)->exists()) {
            $name = $base.'-'.$counter++;
        }

        return $name;
    }
}
