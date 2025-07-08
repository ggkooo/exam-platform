<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;

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
            
            // Buscar roles/papéis do usuário no Moodle
            $userRoles = $this->getUserRoles($user->id);
            Session::put('moodle_user_roles', $userRoles);
            
            // Determinar o papel principal do usuário
            $primaryRole = $this->getPrimaryRole($userRoles);
            Session::put('moodle_primary_role', $primaryRole);
            
            Log::info('Login bem-sucedido', [
                'username' => $username,
                'roles' => $userRoles,
                'primary_role' => $primaryRole
            ]);
            
            // Criar cookie personalizado para o usuário logado
            $cookieValue = json_encode([
                'user_id' => $user->id,
                'username' => $user->username,
                'login_time' => now()->timestamp,
                'primary_role' => $primaryRole,
                'remember_token' => hash('sha256', $user->id . $user->username . now()->timestamp)
            ]);
            
            // Cookie válido por 30 dias (43200 minutos)
            $cookie = Cookie::make(
                'exam_platform_user', 
                $cookieValue, 
                43200, // 30 dias em minutos
                '/', // path
                null, // domain
                true, // secure (apenas HTTPS)
                true // httpOnly (não acessível via JavaScript)
            );
            
            return redirect()->intended('/dashboard')->withCookie($cookie);
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
        
        // Remover o cookie personalizado no logout
        $cookie = Cookie::forget('exam_platform_user');
        
        return redirect()->route('login')->withCookie($cookie);
    }

    /**
     * Check password against Moodle's hashing algorithm
     */
    protected function checkMoodlePassword($password, $hashedPassword)
    {
        // Formato hash do Moodle: $algorithm$iterations$salt$hash
        $parts = explode('$', $hashedPassword);
        
        if (count($parts) !== 4) {
            Log::warning('Formato de hash não reconhecido', [
                'parts_count' => count($parts)
            ]);
            return false;
        }
        
        $algorithm = $parts[1];
        
        // Versões do Moodle mais recentes
        if ($algorithm === '2y') {
            // Usando bcrypt
            return password_verify($password, $hashedPassword);
        }
        
        // Para outras versões do Moodle
        // Implemente a lógica específica para o algoritmo usado pelo seu Moodle
        
        Log::warning('Algoritmo de hash não implementado', ['algorithm' => $algorithm]);
        return false;
    }
    
    /**
     * Check if user has valid login cookie
     */
    public function checkLoginCookie(Request $request)
    {
        $cookieValue = $request->cookie('exam_platform_user');
        
        if (!$cookieValue) {
            return null;
        }
        
        try {
            $cookieData = json_decode($cookieValue, true);
            
            if (!$cookieData || !isset($cookieData['user_id'], $cookieData['username'])) {
                return null;
            }
            
            // Verificar se o usuário ainda existe no banco
            $user = DB::connection('moodle')
                ->table('mdl_user')
                ->where('id', $cookieData['user_id'])
                ->where('username', $cookieData['username'])
                ->first();
                
            return $user ? $cookieData : null;
            
        } catch (\Exception $e) {
            Log::error('Erro ao verificar cookie de login', ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * Get user roles from Moodle database
     */
    public function getUserRoles($userId)
    {
        try {
            Log::info('Buscando roles para usuário', ['user_id' => $userId]);
            
            // Buscar roles do usuário em todos os contextos
            $roles = DB::connection('moodle')
                ->table('mdl_role_assignments as ra')
                ->join('mdl_role as r', 'ra.roleid', '=', 'r.id')
                ->join('mdl_context as c', 'ra.contextid', '=', 'c.id')
                ->where('ra.userid', $userId)
                ->select([
                    'r.id as role_id',
                    'r.shortname as role_shortname', 
                    'r.name as role_name',
                    'c.contextlevel',
                    'c.instanceid',
                    'ra.contextid'
                ])
                ->orderBy('c.contextlevel', 'asc') // Priorizar contextos de sistema
                ->get()
                ->toArray();
                
            Log::info('Roles encontrados para o usuário', [
                'user_id' => $userId,
                'roles_count' => count($roles),
                'roles_details' => array_map(function($role) {
                    return [
                        'id' => $role->role_id,
                        'shortname' => $role->role_shortname,
                        'name' => $role->role_name,
                        'contextlevel' => $role->contextlevel
                    ];
                }, $roles)
            ]);
            
            // Se não encontrar roles através de assignments, verificar se é admin do site
            if (empty($roles)) {
                Log::info('Nenhum role encontrado via assignments, verificando admin do site');
                
                // Verificar se o usuário está na tabela de admins do site
                $siteAdmin = DB::connection('moodle')
                    ->table('mdl_config')
                    ->where('name', 'siteadmins')
                    ->first();
                    
                if ($siteAdmin && strpos($siteAdmin->value, (string)$userId) !== false) {
                    Log::info('Usuário encontrado como site admin', ['user_id' => $userId]);
                    
                    // Criar um role artificial para site admin
                    $roles[] = (object)[
                        'role_id' => 1,
                        'role_shortname' => 'admin',
                        'role_name' => 'Site Administrator',
                        'contextlevel' => 10,
                        'instanceid' => 0,
                        'contextid' => 1
                    ];
                }
            }
            
            return $roles;
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar roles do usuário', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }
    
    /**
     * Determine primary role based on role hierarchy
     */
    public function getPrimaryRole($userRoles)
    {
        if (empty($userRoles)) {
            return 'user'; // Role padrão
        }
        
        // Hierarquia de roles (do mais importante para o menos importante)
        $roleHierarchy = [
            'admin' => 'administrador',
            'administrator' => 'administrador',
            'siteadmin' => 'administrador',
            'manager' => 'administrador',
            'coursecreator' => 'coordenador',
            'editingteacher' => 'professor',
            'teacher' => 'professor',
            'student' => 'aluno',
            'guest' => 'visitante',
            'user' => 'usuario',
            'authenticated' => 'usuario'
        ];
        
        Log::info('Determinando role principal', [
            'available_roles' => array_map(function($role) {
                return $role->role_shortname;
            }, $userRoles),
            'hierarchy_check' => $roleHierarchy
        ]);
        
        // Buscar o role de maior prioridade
        foreach ($roleHierarchy as $moodleRole => $appRole) {
            foreach ($userRoles as $role) {
                if (strtolower($role->role_shortname) === $moodleRole) {
                    Log::info('Role principal determinado', [
                        'moodle_role' => $role->role_shortname,
                        'app_role' => $appRole,
                        'context_level' => $role->contextlevel
                    ]);
                    return $appRole;
                }
            }
        }
        
        // Se não encontrar um role conhecido, usar o primeiro role encontrado
        if (!empty($userRoles)) {
            $firstRole = strtolower($userRoles[0]->role_shortname);
            Log::info('Usando primeiro role encontrado', ['role' => $firstRole]);
            return $firstRole;
        }
        
        return 'user';
    }
    
    /**
     * Check if user has specific role
     */
    public function hasRole($roleName)
    {
        $userRoles = Session::get('moodle_user_roles', []);
        
        foreach ($userRoles as $role) {
            if (strtolower($role->role_shortname) === strtolower($roleName)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if user is admin/manager
     */
    public function isAdmin()
    {
        return $this->hasRole('admin') || $this->hasRole('manager');
    }
    
    /**
     * Check if user is teacher
     */
    public function isTeacher()
    {
        return $this->hasRole('editingteacher') || $this->hasRole('teacher');
    }
    
    /**
     * Check if user is student
     */
    public function isStudent()
    {
        return $this->hasRole('student');
    }
}