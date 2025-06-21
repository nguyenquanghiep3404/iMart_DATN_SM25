<x-mail::layout>
    {{-- Header --}}
    <x-slot:header>
        <x-mail::header :url="config('app.url')">
            <span style="font-size: 24px; font-weight: bold; color: #0d9488;">
                {{ config('app.name') }}
            </span>
        </x-mail::header>
    </x-slot:header>

    {{-- Body --}}
    <div style="font-family: 'Segoe UI', Roboto, sans-serif; font-size: 15px; color: #1f2937;">
        {!! $slot !!}
    </div>

    {{-- Subcopy --}}
    @isset($subcopy)
    <x-slot:subcopy>
        <x-mail::subcopy>
            <div style="font-size: 13px; color: #6b7280;">
                {!! $subcopy !!}
            </div>
        </x-mail::subcopy>
    </x-slot:subcopy>
    @endisset

    {{-- Footer --}}
    <x-slot:footer>
        <x-mail::footer>
            <div style="color: #9ca3af; font-size: 13px;">
                © {{ date('Y') }} {{ config('app.name') }}. Đã đăng ký bản quyền.
            </div>
        </x-mail::footer>
    </x-slot:footer>
</x-mail::layout>