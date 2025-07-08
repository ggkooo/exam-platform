<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class SessionController extends Controller
{
    /**
     * Refresh user roles from Moodle database
     */
    public function refreshRoles(Request $request)
    {
        $userId = Session::get('moodle_user_id');
        
        if (!$userId) {
            return response()->json(['error' => 'Usuário não logado'], 401);
        }
        
        try {
            Log::info('Atualizando roles do usuário', ['user_id' => $userId]);
            
            // Usar os métodos do LoginController para recarregar roles
            $loginController = new LoginController();
            $newRoles = $loginController->getUserRoles($userId);
            $newPrimaryRole = $loginController->getPrimaryRole($newRoles);
            
            // Atualizar sessão
            Session::put('moodle_user_roles', $newRoles);
            Session::put('moodle_primary_role', $newPrimaryRole);
            
            Log::info('Roles atualizados com sucesso', [
                'user_id' => $userId,
                'new_primary_role' => $newPrimaryRole,
                'roles_count' => count($newRoles)
            ]);
            
            return response()->json([
                'success' => 'Roles atualizados com sucesso',
                'primary_role' => $newPrimaryRole,
                'roles_count' => count($newRoles)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar roles', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Erro ao atualizar roles: ' . $e->getMessage()], 500);
        }
    }
}
