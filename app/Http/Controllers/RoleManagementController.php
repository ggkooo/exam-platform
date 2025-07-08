<?php

namespace App\Http\Controllers;

use App\Helpers\MoodleRoleManager;
use App\Helpers\RoleHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RoleManagementController extends Controller
{
    public function index()
    {
        if (!RoleHelper::isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Acesso negado. Apenas administradores podem gerenciar roles.');
        }
        
        $availableRoles = MoodleRoleManager::getAvailableRoles();
        $currentUserId = Session::get('moodle_user_id');
        $userAssignments = MoodleRoleManager::getUserRoleAssignments($currentUserId);
        
        return view('admin.roles', compact('availableRoles', 'userAssignments', 'currentUserId'));
    }
    
    public function users(Request $request)
    {
        if (!RoleHelper::isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Acesso negado.');
        }
        
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        try {
            $query = DB::connection('moodle')
                ->table('mdl_user')
                ->where('deleted', 0)
                ->where('suspended', 0);
                
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('username', 'LIKE', "%{$search}%")
                      ->orWhere('firstname', 'LIKE', "%{$search}%")
                      ->orWhere('lastname', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
                });
            }
            
            $totalUsers = $query->count();
            $users = $query->select(['id', 'username', 'firstname', 'lastname', 'email', 'timecreated'])
                          ->orderBy('lastname')
                          ->orderBy('firstname')
                          ->limit($perPage)
                          ->offset($offset)
                          ->get();
            
            $totalPages = ceil($totalUsers / $perPage);
            
            return view('admin.users', compact('users', 'search', 'page', 'totalPages', 'totalUsers'));
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar usuários', ['error' => $e->getMessage()]);
            return redirect()->route('admin.roles')->with('error', 'Erro ao carregar usuários: ' . $e->getMessage());
        }
    }
    
    public function userRoles($userId)
    {
        if (!RoleHelper::isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Acesso negado.');
        }
        
        try {
            $user = DB::connection('moodle')
                ->table('mdl_user')
                ->where('id', $userId)
                ->where('deleted', 0)
                ->first();
                
            if (!$user) {
                return redirect()->route('admin.users')->with('error', 'Usuário não encontrado.');
            }
            
            $availableRoles = MoodleRoleManager::getAvailableRoles();
            $userAssignments = MoodleRoleManager::getUserRoleAssignments($userId);
            
            return view('admin.user-roles', compact('user', 'availableRoles', 'userAssignments'));
            
        } catch (\Exception $e) {
            Log::error('Erro ao carregar roles do usuário', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return redirect()->route('admin.users')->with('error', 'Erro ao carregar dados do usuário.');
        }
    }
    
    public function assignRoleToUser(Request $request)
    {
        if (!RoleHelper::isAdmin()) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        
        $request->validate([
            'user_id' => 'required|integer',
            'role_shortname' => 'required|string'
        ]);
        
        $userId = $request->input('user_id');
        $roleShortname = $request->input('role_shortname');
        $assignerId = Session::get('moodle_user_id');
        
        if (in_array($roleShortname, ['admin', 'siteadmin'])) {
            $success = MoodleRoleManager::addSiteAdmin($userId);
            
            if ($success) {
                $currentUserId = Session::get('moodle_user_id');
                if ($userId == $currentUserId) {
                    Session::forget(['moodle_user_roles', 'moodle_primary_role']);
                    $loginController = new \App\Http\Controllers\Auth\LoginController();
                    $newRoles = $loginController->getUserRoles($userId);
                    $newPrimaryRole = $loginController->getPrimaryRole($newRoles);
                    
                    Session::put('moodle_user_roles', $newRoles);
                    Session::put('moodle_primary_role', $newPrimaryRole);
                }
                
                return response()->json(['success' => 'Site admin atribuído com sucesso']);
            } else {
                return response()->json(['error' => 'Erro ao atribuir site admin'], 500);
            }
        }
        
        $roleId = MoodleRoleManager::getRoleIdByShortname($roleShortname);
        
        if (!$roleId) {
            return response()->json(['error' => 'Role não encontrado'], 404);
        }
        
        $success = MoodleRoleManager::assignRoleToUser($userId, $roleId, null, $assignerId);
        
        if ($success) {
            $currentUserId = Session::get('moodle_user_id');
            if ($userId == $currentUserId) {
                Session::forget(['moodle_user_roles', 'moodle_primary_role']);
                
                $loginController = new \App\Http\Controllers\Auth\LoginController();
                $newRoles = $loginController->getUserRoles($userId);
                $newPrimaryRole = $loginController->getPrimaryRole($newRoles);
                
                Session::put('moodle_user_roles', $newRoles);
                Session::put('moodle_primary_role', $newPrimaryRole);
            }
            
            return response()->json(['success' => 'Role atribuído com sucesso']);
        } else {
            return response()->json(['error' => 'Erro ao atribuir role'], 500);
        }
    }
    
    public function removeRoleFromUser(Request $request)
    {
        if (!RoleHelper::isAdmin()) {
            return response()->json(['error' => 'Acesso negado'], 403);
        }
        
        $request->validate([
            'user_id' => 'required|integer',
            'role_shortname' => 'required|string'
        ]);
        
        $userId = $request->input('user_id');
        $roleShortname = $request->input('role_shortname');
        
        // Casos especiais para admin
        if (in_array($roleShortname, ['admin', 'siteadmin'])) {
            $success = MoodleRoleManager::removeSiteAdmin($userId);
            
            if ($success) {
                $currentUserId = Session::get('moodle_user_id');
                if ($userId == $currentUserId) {
                    Session::forget(['moodle_user_roles', 'moodle_primary_role']);
                    
                    $loginController = new \App\Http\Controllers\Auth\LoginController();
                    $newRoles = $loginController->getUserRoles($userId);
                    $newPrimaryRole = $loginController->getPrimaryRole($newRoles);
                    
                    Session::put('moodle_user_roles', $newRoles);
                    Session::put('moodle_primary_role', $newPrimaryRole);
                }
                
                return response()->json(['success' => 'Site admin removido com sucesso']);
            } else {
                return response()->json(['error' => 'Erro ao remover site admin'], 500);
            }
        }
        
        $roleId = MoodleRoleManager::getRoleIdByShortname($roleShortname);
        
        if (!$roleId) {
            return response()->json(['error' => 'Role não encontrado: ' . $roleShortname], 404);
        }

        if ($roleShortname === 'editingteacher') {
            $success = MoodleRoleManager::removeRoleByShortname($userId, $roleShortname);
        } else {
            $success = MoodleRoleManager::removeRoleFromUser($userId, $roleId);
        }
        
        if ($success) {
            $currentUserId = Session::get('moodle_user_id');
            if ($userId == $currentUserId) {
                Session::forget(['moodle_user_roles', 'moodle_primary_role']);
                
                $loginController = new \App\Http\Controllers\Auth\LoginController();
                $newRoles = $loginController->getUserRoles($userId);
                $newPrimaryRole = $loginController->getPrimaryRole($newRoles);
                
                Session::put('moodle_user_roles', $newRoles);
                Session::put('moodle_primary_role', $newPrimaryRole);
            }
            
            return response()->json(['success' => 'Role removido com sucesso']);
        } else {
            return response()->json(['error' => 'Erro ao remover role. Verifique os logs para mais detalhes.'], 500);
        }
    }
    
    public function assignRole(Request $request)
    {
        $request->merge(['user_id' => Session::get('moodle_user_id')]);
        return $this->assignRoleToUser($request);
    }
    
    public function removeRole(Request $request)
    {
        $request->merge(['user_id' => Session::get('moodle_user_id')]);
        return $this->removeRoleFromUser($request);
    }
}
