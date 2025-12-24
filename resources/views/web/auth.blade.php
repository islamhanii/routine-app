@extends('layouts.master')

@section('title')
    Sign in
@endsection

@section('css')
    <link href="{{ URL::asset('assets/css/auth.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="auth-card card-box shadow">
        <h3 class="text-center mb-2">Welcome 👋</h3>
        <p class="text-center text-muted mb-4">
            Sign in or create an account using Google
        </p>

        <!-- Google Login -->
        <a href="{{ route('google.login') }}" class="btn btn-google w-100">
            <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google">
            Continue with Google
        </a>

        <p class="text-center text-muted small mt-4">
            By continuing, you agree to our Terms & Privacy Policy
        </p>
    </div>
@endsection
