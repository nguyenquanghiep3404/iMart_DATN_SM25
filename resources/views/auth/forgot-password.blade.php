<!DOCTYPE html>
<html lang="en">

<!-- Mirrored from html.hixstudio.net/ebazer/login.html by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 25 May 2025 14:06:53 GMT -->
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Bán sản phẩm iMart</title>
    <link rel="shortcut icon" href="assets/admin/img/logo/favicon.png" type="image/x-icon">

    <!-- css links -->
    <link rel="stylesheet" href="assets/admin/css/perfect-scrollbar.css">
    <link rel="stylesheet" href="assets/admin/css/choices.css">
    <link rel="stylesheet" href="assets/admin/css/apexcharts.css">
    <link rel="stylesheet" href="assets/admin/css/quill.css">
    <link rel="stylesheet" href="assets/admin/css/rangeslider.css">
    <link rel="stylesheet" href="assets/admin/css/custom.css">
    <link rel="stylesheet" href="assets/admin/css/main.css">
</head>
<body>
<div class="tp-main-wrapper h-screen">
    <div class="container mx-auto my-auto h-full flex items-center justify-center">
        <div class="pt-[120px] pb-[120px]">
            <div class="grid grid-cols-12 shadow-lg bg-white overflow-hidden rounded-md">
                <div class="col-span-4 lg:col-span-6 relative h-full hidden lg:block">
                    <div class="data-bg absolute top-0 left-0 w-full h-full bg-cover bg-no-repeat" data-bg="assets/admin/img/bg/login-bg.jpg"></div>
                </div>
                <div class="col-span-12 lg:col-span-6 md:w-[500px] mx-auto my-auto pt-[50px] py-[60px] px-5 md:px-[60px]">
                    <div class="text-center">
                        <h4 class="text-[24px] mb-1">Forgot Password</h4>
                        <p>No problem. Just enter your email and we'll send a reset link.</p>
                    </div>

                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="mb-5">
                            <p class="mb-0 text-base text-black">Email <span class="text-red">*</span></p>
                            <input
                                id="email"
                                class="input w-full h-[49px] rounded-md border border-gray6 px-6 text-base @error('email') border-red-500 @enderror"
                                type="email"
                                name="email"
                                :value="old('email')"
                                required
                                autofocus
                                placeholder="Enter Your Email"
                            >
                            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-600" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <button class="tp-btn h-[49px] w-full justify-center">
                                {{ __('Email Password Reset Link') }}
                            </button>
                        </div>
                    </form>

                    <div class="mt-6 text-center">
                        <a href="{{ route('login') }}" class="text-sm text-theme hover:underline">
                            Back to login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


    <script src="assets/admin/js/alpine.js"></script>
    <script src="assets/admin/js/perfect-scrollbar.js"></script>
    <script src="assets/admin/js/choices.js"></script>
    <script src="assets/admin/js/chart.js"></script>
    <script src="assets/admin/js/apexchart.js"></script>
    <script src="assets/admin/js/quill.js"></script>
    <script src="assets/admin/js/rangeslider.min.js"></script>
    <script src="assets/admin/js/main.js"></script>
    
</body>

<!-- Mirrored from html.hixstudio.net/ebazer/login.html by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 25 May 2025 14:06:53 GMT -->
</html>
