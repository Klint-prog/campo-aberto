<?php

namespace App\Http\Requests\Auth;

use App\Models\AccessLog;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $user = config('auth.providers.users.model')::query()
            ->where('email', $this->string('email')->lower()->value())
            ->first();

        if (! $user || ! Hash::check($this->string('password')->value(), $user->password) || ! $user->is_active) {
            RateLimiter::hit($this->throttleKey());
            $this->logAccess('login_failed', $user?->id, $user?->tenant_id);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        Auth::login($user, $this->boolean('remember'));
        RateLimiter::clear($this->throttleKey());
        $this->logAccess('login_success', $user->id, $user->tenant_id);
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')->value()).'|'.$this->ip());
    }

    private function logAccess(string $event, ?string $userId = null, ?string $tenantId = null): void
    {
        AccessLog::query()->create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'event' => $event,
            'ip_address' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'context' => ['email' => $this->string('email')->lower()->value()],
            'created_at' => now(),
        ]);
    }
}
