<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Platform</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <!-- BOOTSTRAP 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <!-- BOOTSTRAP ICONS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
</body>
</html>