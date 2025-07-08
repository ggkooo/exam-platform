<!DOCTYPE html>
<html lang="pt-BR">
<head>
    @include('partials.head')
</head>
<body class="bg-light">
    @include('partials.navbar')

    <div class="container my-5">
        @include('partials.alerts')
        
        @yield('content')
    </div>

    @include('partials.scripts')
</body>
</html>
