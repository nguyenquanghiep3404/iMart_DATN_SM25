@extends('users.layouts.app')

@section('content')
    <div class="container py-5 text-center">
        @if (session('error'))
            <div class="alert alert-danger mb-4">
                {{ session('error') }}
            </div>
        @elseif(session('message'))
            <div class="alert alert-success mb-4">
                {{ session('message') }}
            </div>
        @endif

        <a href="{{ url('/') }}" class="btn btn-primary">Quay lại trang chủ</a>
    </div>
@endsection
