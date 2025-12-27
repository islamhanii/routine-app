<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @include('layouts.head')
</head>

<body>
    <div class="main-wrapper">
        @include('layouts.header')
        <div class="container py-5">
            @yield('content')
        </div>
    </div>

    @include('layouts.script')
</body>

</html>
