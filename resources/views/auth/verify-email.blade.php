<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - iMart</title>
    <link rel="shortcut icon" href="{{ asset('assets/admin/img/logo/favicon.png') }}" type="image/x-icon">

    <!-- css links -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/perfect-scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/choices.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/apexcharts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/quill.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/rangeslider.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/main.css') }}">
</head>
<body>
    <div class="tp-main-wrapper h-screen bg-gray-100">
        <div class="container mx-auto h-full flex items-center justify-center">
            <div class="md:w-[500px] mx-auto shadow-lg bg-white pt-[50px] pb-[50px] px-10 sm:px-[60px] rounded-lg">
                
                <!-- Laravel verification message -->
                <div class="mb-4 text-sm text-gray-600 text-center">
                    {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
                </div>

                @if (session('status') == 'verification-link-sent')
                    <div class="mb-4 font-medium text-sm text-green-600 text-center">
                        {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                    </div>
                @endif

                <!-- Resend Verification Email Form -->
                <div class="mt-6 flex items-center justify-center gap-4">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 transition">
                            {{ __('Resend Verification Email') }}
                        </button>
                    </form>

                    <!-- Logout Form -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900">
                            {{ __('Log Out') }}
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- JS scripts -->
    <script src="{{ asset('assets/admin/js/alpine.js') }}"></script>
    <script src="{{ asset('assets/admin/admin/js/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/admin/js/choices.js') }}"></script>
    <script src="{{ asset('assets/admin/js/chart.js') }}"></script>
    <script src="{{ asset('assets/admin/js/apexchart.js') }}"></script>
    <script src="{{ asset('assets/admin/js/quill.js') }}"></script>
    <script src="{{ asset('assets/admin/js/rangeslider.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/main.js') }}"></script>
</body>
</html>
