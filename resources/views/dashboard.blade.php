<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Dashboard</span>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-danger">Sair</button>
                            </form>
                        </div>
                    </div>

                    <div class="card-body">
                        <h2>Bem-vindo, {{ \App\Helpers\MoodleAuth::user()->name }}!</h2>
                        <p>Seu email: {{ \App\Helpers\MoodleAuth::user()->email }}</p>
                        <p>Seu nome de usuário: {{ \App\Helpers\MoodleAuth::user()->username }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>