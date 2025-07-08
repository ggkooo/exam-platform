<!DOCTYPE html>
<html lang="pt-BR">
<head>
    @include('partials.head')
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="container">
        <div class="container border p-5 shadow-lg rounded w-50 mx-auto">
            <img src="/assets/images/unijui-logo.jpg" class="mx-auto d-block" width="350" alt="Image">
            <form action="{{ route('login.post') }}" method="POST" class="mt-5">
                @csrf
                
                @if ($errors->any())
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                
                <div class="form-floating mb-3">
                    <input type="text" class="form-control @error('username') is-invalid @enderror" 
                           id="floatingInputName" name="username" placeholder="johndoe" 
                           value="{{ old('username') }}">
                    <label for="floatingInputName">Usuário</label>
                    @error('username')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="form-floating">
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="floatingInputPassword" name="password" placeholder="Senha">
                    <label for="floatingInputPassword">Senha</label>
                    @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="mt-2 text-end">
                    <a href="#" class="forgot-password-link">
                        <small>Esqueceu a senha?</small>
                    </a>
                </div>
                <div class="mt-4">
                    <button class="w-100 btn btn-lg btn-primary py-3" type="submit">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Entrar
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    @include('partials.scripts')
</body>
</html>