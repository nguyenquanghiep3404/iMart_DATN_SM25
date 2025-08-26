<!DOCTYPE html>
<html lang="vi">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="">
    <title>@yield('title', 'iMart POS')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <link rel="icon" type="image/png" href="{{ asset ('assets/users/app-icons/Bản sao của iMart.svg') }}" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset ('assets/users/app-icons/Bản sao của iMart (2).svg') }}">
    @stack('styles')
</head>

<body>

    @yield('header')
    @yield('content')

    @stack('scripts')
</body>

</html>
