<section>
    @if (session('status') === 'password-updated')
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 2000)"
        x-show="show"
        x-transition
        class="fixed top-4 right-4 z-50 bg-green-100 border border-green-300 text-green-800 px-4 py-2 rounded shadow-lg text-sm"
        role="alert">
        ✅ Đổi mật khẩu thành công!
    </div>
    @endif

    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Cập nhật mật khẩu
') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Đảm bảo tài khoản của bạn sử dụng mật khẩu dài và ngẫu nhiên để đảm bảo an toàn.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div x-data="{ show: false }" class="relative">
            <x-input-label for="update_password_current_password" :value="__('Mật khẩu hiện tại')" />
            <x-text-input
                x-bind:type="show ? 'text' : 'password'"
                name="current_password"
                id="update_password_current_password"
                class="mt-1 block w-full pr-10"
                autocomplete="current-password" />
            <button type="button"
                class="absolute right-2 top-[38px] text-gray-500"
                @click="show = !show"
                :aria-label="show ? 'Ẩn' : 'Hiện'">
                <i :class="show ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
            </button>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>


        <div x-data="{ show: false }" class="relative">
            <x-input-label for="update_password_password" :value="__('Mật khẩu mới')" />
            <x-text-input
                x-bind:type="show ? 'text' : 'password'"
                name="password"
                id="update_password_password"
                class="mt-1 block w-full pr-10"
                autocomplete="new-password" />
            <button type="button"
                class="absolute right-2 top-[38px] text-gray-500"
                @click="show = !show"
                :aria-label="show ? 'Ẩn' : 'Hiện'">
                <i :class="show ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
            </button>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>


        <div x-data="{ show: false }" class="relative">
            <x-input-label for="update_password_password_confirmation" :value="__('Xác nhận mật khẩu')" />
            <x-text-input
                x-bind:type="show ? 'text' : 'password'"
                name="password_confirmation"
                id="update_password_password_confirmation"
                class="mt-1 block w-full pr-10"
                autocomplete="new-password" />
            <button type="button"
                class="absolute right-2 top-[38px] text-gray-500"
                @click="show = !show"
                :aria-label="show ? 'Ẩn' : 'Hiện'">
                <i :class="show ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
            </button>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>


        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Lưu') }}</x-primary-button>

            @if (session('status') === 'password-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-gray-600 dark:text-gray-400">{{ __('Đã Lưu.') }}</p>
            @endif
        </div>
    </form>
</section>