<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @include('layouts.head')
</head>

<body>
    <div class="container py-5">
            @yield('content')
    </div>

    @include('layouts.script')
</body>

</html>
