<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">
            <i class="bi bi-mortarboard me-2"></i>
            Exam Platform
        </a>
        <div class="d-flex align-items-center">
            @yield('nav-actions')
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>
                    Sair
                </button>
            </form>
        </div>
    </div>
</nav>
