<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - iMart</title>
    <link rel="shortcut icon" href="{{ asset('assets/admin/img/logo/favicon.png') }}" type="image/x-icon">

    <!-- css links -->
    <link rel="stylesheet" href="{{asset('assets/admin/css/perfect-scrollbar.css')}}">
    <link rel="stylesheet" href="{{asset('assets/admin/css/choices.css') }}">
    <link rel="stylesheet" href="{{asset('assets/admin/css/apexcharts.css') }}">
    <link rel="stylesheet" href="{{asset('assets/admin/css/quill.css') }}">
    <link rel="stylesheet" href="{{asset('assets/admin/css/rangeslider.css') }}">
    <link rel="stylesheet" href="{{asset('assets/admin/css/custom.css') }}">
    <link rel="stylesheet" href="{{asset('assets/admin/css/main.css') }}">
</head>

<body>

    <div class="tp-main-wrapper h-screen">
        <div class="container mx-auto my-auto h-full flex items-center justify-center">
            <div class="pt-[120px] pb-[120px]">
                <div class="grid grid-cols-12 shadow-lg bg-white overflow-hidden rounded-md">
                    <!-- Ảnh nền, chỉ hiện trên LG trở lên -->
                    <div class="col-span-4 lg:col-span-6 relative h-full hidden lg:block">
                        <div class="absolute top-0 left-0 w-full h-full bg-cover bg-no-repeat"><img src="{{ asset('assets/admin/img/bg/login-bg.jpg') }}" alt="Background Image" />
                        </div>
                    </div>
                    <!-- Form luôn hiển thị -->
                    <div class="col-span-12 lg:col-span-6 md:w-[500px] mx-auto my-auto pt-[50px] py-[60px] px-5 md:px-[60px]">
                        <div class="text-center mb-5">
                            <h4 class="text-[24px] mb-1">Reset Your Password</h4>
                            <p>Already have an account? <a href="{{ route('login') }}" class="text-theme">Login</a></p>
                        </div>

                        <!-- Laravel Password Reset Form -->
                        <form method="POST" action="{{ route('password.store') }}">
                            @csrf

                            <!-- Token -->
                            <input type="hidden" name="token" value="{{ $request->route('token') }}">

                            <!-- Email -->
                            <div class="mb-5">
                                <p class="mb-0 text-base text-black">Your Email <span class="text-red">*</span></p>
                                <input name="email" type="email" value="{{ old('email', $request->email) }}"
                                    class="input w-full h-[46px] rounded-md border border-gray6 px-6 text-base @error('email') border-red-500 @enderror"
                                    placeholder="Enter Your Email" required>
                                @error('email')
                                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="mb-5">
                                <p class="mb-0 text-base text-black">New Password <span class="text-red">*</span></p>
                                <input name="password" type="password"
                                    class="input w-full h-[46px] rounded-md border border-gray6 px-6 text-base @error('password') border-red-500 @enderror"
                                    placeholder="Enter New Password" required>
                                @error('password')
                                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-5">
                                <p class="mb-0 text-base text-black">Confirm Password <span class="text-red">*</span></p>
                                <input name="password_confirmation" type="password"
                                    class="input w-full h-[46px] rounded-md border border-gray6 px-6 text-base"
                                    placeholder="Confirm New Password" required>
                                @error('password_confirmation')
                                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" class="tp-btn h-[49px] w-full justify-center">Reset Password</button>
                        </form>
                        <!-- End Form -->
                    </div>
                </div>
            </div>
        </div>

        <script src="{{ asset('assets/admin/js/vendors/jquery/dist/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/admin/js/vendors/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
        @stack('scripts')
        <script src="{{ asset('assets/admin/js/alpine.js') }}"></script>
        <script src="{{ asset('assets/admin/js/perfect-scrollbar.js') }}"></script>
        <script src="{{ asset('assets/admin/js/choices.js') }}"></script>
        <script src="{{ asset('assets/admin/js/chart.js') }}"></script>
        <script src="{{ asset('assets/admin/js/apexchart.js') }}"></script>
        <script src="{{ asset('assets/admin/js/quill.js') }}"></script>
        <script src="{{ asset('assets/admin/js/rangeslider.min.js') }}"></script>
        <script src="{{ asset('assets/admin/js/main.js') }}"></script>
</body>
</html>