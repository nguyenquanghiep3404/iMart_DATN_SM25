<?php
namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

// logic status: banned
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // 1. Tìm người dùng bằng email
        $user = User::where('email', $this->string('email'))->first();

        // 2. Kiểm tra user có tồn tại và mật khẩu có đúng không
        if (! $user || ! Hash::check($this->string('password'), $user->password)) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'), // hiển thị lỗi nếu đăng nhập sai
            ]);
        }

        // 3. Kiểm tra trạng thái (status)
        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => 'Tài khoản của bạn đã bị khóa, cấm hoặc chưa được kích hoạt.',
            ]);
        }

        // 4. Mọi thứ hợp lệ, tiến hành đăng nhập
        Auth::login($user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
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
        return Str::lower($this->input('email')).'|'.$this->ip();
    }
}
