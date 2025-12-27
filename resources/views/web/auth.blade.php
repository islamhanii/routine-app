@extends('layouts.master')

@section('title')
    {{ __('web.app_name') }}
@endsection

@section('css')
    <link href="{{ URL::asset('assets/css/auth.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="auth-card card-box shadow">
        <h3 class="text-center mb-2">{{ __('web.welcome') }} 👋</h3>
        <p class="text-center text-muted mb-4">
            {{ __('web.signin_google') }}
        </p>

        <!-- Google Login -->
        <a href="{{ route('google.login') }}" class="btn btn-google w-100">
            <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google">
            {{ __('web.continue_with_google') }}
        </a>

        <p class="text-center text-muted small mt-4">
            {{ __('web.terms_privacy') }}
        </p>
    </div>
@endsection
