<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }
    
    /**
     * Handle login attempt
     */
    public function login(Request $request)
    {
        Log::info('Tentativa de login iniciada', ['username' => $request->input('username')]);
        
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $username = $request->input('username');
        $password = $request->input('password');

        // Buscar usuário no banco do Moodle
        try {
            Log::info('Conectando ao banco de dados do Moodle');
            
            $user = DB::connection('moodle')
                ->table('mdl_user')
                ->where('username', $username)
                ->first();
                
            if ($user) {
                Log::info('Usuário encontrado no banco do Moodle', ['username' => $username]);
            } else {
                Log::warning('Usuário não encontrado no banco do Moodle', ['username' => $username]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao conectar ao banco do Moodle', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors([
                'connection' => 'Erro ao conectar ao banco de dados do Moodle: ' . $e->getMessage(),
            ])->withInput($request->except('password'));
        }

        if (!$user) {
            return back()->withErrors([
                'username' => 'Usuário não encontrado',
            ])->withInput($request->except('password'));
        }

        // Verificar a senha usando a lógica do Moodle
        $passwordValid = $this->checkMoodlePassword($password, $user->password);
        Log::info('Verificação de senha', [
            'username' => $username,
            'valid' => $passwordValid ? 'sim' : 'não'
        ]);
        
        if ($passwordValid) {
            // Se a autenticação for bem-sucedida, armazene as informações do usuário na sessão
            Session::put('moodle_user_id', $user->id);
            Session::put('moodle_username', $user->username);
            Session::put('moodle_name', $user->firstname . ' ' . $user->lastname);
            Session::put('moodle_email', $user->email);
            Session::put('logged_in', true);
            
            Log::info('Login bem-sucedido', ['username' => $username]);
            
            return redirect()->intended('/dashboard');
        }

        Log::warning('Senha incorreta', ['username' => $username]);
        
        return back()->withErrors([
            'password' => 'Senha incorreta',
        ])->withInput($request->except('password'));
    }

    /**
     * Logout the user
     */
    public function logout(Request $request)
    {
        Log::info('Logout do usuário', ['username' => Session::get('moodle_username')]);
        
        Session::flush();
        return redirect()->route('login');
    }

    /**
     * Check password against Moodle's hashing algorithm
     */
    protected function checkMoodlePassword($password, $hashedPassword)
    {
        Log::debug('Verificando senha com hash do Moodle', [
            'hash_format' => substr($hashedPassword, 0, 10) . '...'
        ]);
        
        // Formato hash do Moodle: $algorithm$iterations$salt$hash
        $parts = explode('$', $hashedPassword);
        
        if (count($parts) !== 4) {
            Log::warning('Formato de hash não reconhecido', [
                'parts_count' => count($parts)
            ]);
            return false;
        }
        
        $algorithm = $parts[1];
        Log::debug('Algoritmo de hash detectado', ['algorithm' => $algorithm]);
        
        // Versões do Moodle mais recentes
        if ($algorithm === '2y') {
            // Usando bcrypt
            $result = password_verify($password, $hashedPassword);
            Log::debug('Verificação bcrypt', ['result' => $result ? 'válido' : 'inválido']);
            return $result;
        }
        
        // Para outras versões do Moodle
        // Implemente a lógica específica para o algoritmo usado pelo seu Moodle
        
        Log::warning('Algoritmo de hash não implementado', ['algorithm' => $algorithm]);
        return false;
    }
}