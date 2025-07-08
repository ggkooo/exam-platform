<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MoodleRoleManager
{
    public static function getAvailableRoles()
    {
        try {
            return DB::connection('moodle')
                ->table('mdl_role')
                ->select(['id', 'shortname', 'name', 'description'])
                ->orderBy('sortorder')
                ->get();
        } catch (\Exception $e) {
            Log::error('Erro ao buscar roles disponíveis', ['error' => $e->getMessage()]);
            return collect();
        }
    }
    
    public static function getSystemContextId()
    {
        try {
            $context = DB::connection('moodle')
                ->table('mdl_context')
                ->where('contextlevel', 10) // System context
                ->where('instanceid', 0)
                ->first();
                
            return $context ? $context->id : 1;
        } catch (\Exception $e) {
            Log::error('Erro ao buscar contexto do sistema', ['error' => $e->getMessage()]);
            return 1;
        }
    }
    
    public static function userHasRoleAssignment($userId, $roleId, $contextId = null)
    {
        try {
            $contextId = $contextId ?? self::getSystemContextId();
            
            $assignment = DB::connection('moodle')
                ->table('mdl_role_assignments')
                ->where('userid', $userId)
                ->where('roleid', $roleId)
                ->where('contextid', $contextId)
                ->first();
                
            return !is_null($assignment);
        } catch (\Exception $e) {
            Log::error('Erro ao verificar assignment de role', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    public static function assignRoleToUser($userId, $roleId, $contextId = null, $assignerId = null)
    {
        try {
            $contextId = $contextId ?? self::getSystemContextId();
            $assignerId = $assignerId ?? $userId; // Se não especificado, assume auto-atribuição
            
            // Verificar se já tem o role
            if (self::userHasRoleAssignment($userId, $roleId, $contextId)) {
                Log::info('Usuário já possui este role', [
                    'user_id' => $userId,
                    'role_id' => $roleId,
                    'context_id' => $contextId
                ]);
                return true;
            }
            
            // Inserir nova atribuição
            $result = DB::connection('moodle')
                ->table('mdl_role_assignments')
                ->insert([
                    'roleid' => $roleId,
                    'contextid' => $contextId,
                    'userid' => $userId,
                    'timemodified' => time(),
                    'modifierid' => $assignerId,
                    'component' => '',
                    'itemid' => 0,
                    'sortorder' => 0
                ]);
                
            if ($result) {
                Log::info('Role atribuído com sucesso', [
                    'user_id' => $userId,
                    'role_id' => $roleId,
                    'context_id' => $contextId,
                    'assigner_id' => $assignerId
                ]);
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::error('Erro ao atribuir role', [
                'user_id' => $userId,
                'role_id' => $roleId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    public static function removeRoleFromUser($userId, $roleId, $contextId = null)
    {
        try {
            // Se contexto não especificado, buscar TODOS os assignments para este role
            if ($contextId === null) {
                // Buscar todos os assignments deste role para este usuário
                $existingAssignments = DB::connection('moodle')
                    ->table('mdl_role_assignments')
                    ->where('userid', $userId)
                    ->where('roleid', $roleId)
                    ->get();
                
                if ($existingAssignments->isEmpty()) {
                    return true; // Não há nada para remover
                }
                
                // Remover TODOS os assignments deste role para este usuário
                $result = DB::connection('moodle')
                    ->table('mdl_role_assignments')
                    ->where('userid', $userId)
                    ->where('roleid', $roleId)
                    ->delete();
                
                return $result > 0;
            } else {
                // Contexto específico fornecido
                // Verificar se o assignment existe
                $existingAssignment = DB::connection('moodle')
                    ->table('mdl_role_assignments')
                    ->where('userid', $userId)
                    ->where('roleid', $roleId)
                    ->where('contextid', $contextId)
                    ->first();
                    
                if (!$existingAssignment) {
                    return true; // Não há nada para remover
                }
                
                // Remover assignment específico
                $result = DB::connection('moodle')
                    ->table('mdl_role_assignments')
                    ->where('userid', $userId)
                    ->where('roleid', $roleId)
                    ->where('contextid', $contextId)
                    ->delete();
                
                return $result > 0;
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao remover role', [
                'user_id' => $userId,
                'role_id' => $roleId,
                'context_id' => $contextId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    public static function addSiteAdmin($userId)
    {
        try {
            // Buscar configuração atual
            $config = DB::connection('moodle')
                ->table('mdl_config')
                ->where('name', 'siteadmins')
                ->first();
                
            if (!$config) {
                // Criar configuração se não existir
                DB::connection('moodle')
                    ->table('mdl_config')
                    ->insert([
                        'name' => 'siteadmins',
                        'value' => (string)$userId
                    ]);
                    
                Log::info('Usuário adicionado como site admin (novo config)', ['user_id' => $userId]);
                return true;
            }
            
            // Verificar se já é admin
            $currentAdmins = explode(',', $config->value);
            if (in_array((string)$userId, $currentAdmins)) {
                Log::info('Usuário já é site admin', ['user_id' => $userId]);
                return true;
            }
            
            // Adicionar à lista
            $currentAdmins[] = (string)$userId;
            $newValue = implode(',', array_filter($currentAdmins));
            
            $result = DB::connection('moodle')
                ->table('mdl_config')
                ->where('name', 'siteadmins')
                ->update(['value' => $newValue]);
                
            if ($result) {
                Log::info('Usuário adicionado como site admin', [
                    'user_id' => $userId,
                    'new_admins_list' => $newValue
                ]);
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::error('Erro ao adicionar site admin', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    public static function removeSiteAdmin($userId)
    {
        try {
            $config = DB::connection('moodle')
                ->table('mdl_config')
                ->where('name', 'siteadmins')
                ->first();
                
            if (!$config) {
                return true; // Não existe config, usuário não é admin
            }
            
            $currentAdmins = explode(',', $config->value);
            $newAdmins = array_filter($currentAdmins, function($id) use ($userId) {
                return $id != (string)$userId;
            });
            
            $newValue = implode(',', $newAdmins);
            
            $result = DB::connection('moodle')
                ->table('mdl_config')
                ->where('name', 'siteadmins')
                ->update(['value' => $newValue]);
                
            Log::info('Usuário removido dos site admins', [
                'user_id' => $userId,
                'new_admins_list' => $newValue
            ]);
            
            return $result !== false;
            
        } catch (\Exception $e) {
            Log::error('Erro ao remover site admin', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    public static function getRoleIdByShortname($shortname)
    {
        try {
            $role = DB::connection('moodle')
                ->table('mdl_role')
                ->where('shortname', $shortname)
                ->first();
                
            return $role ? $role->id : null;
        } catch (\Exception $e) {
            Log::error('Erro ao buscar role por shortname', [
                'shortname' => $shortname,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    public static function getUserRoleAssignments($userId)
    {
        try {
            return DB::connection('moodle')
                ->table('mdl_role_assignments as ra')
                ->join('mdl_role as r', 'ra.roleid', '=', 'r.id')
                ->join('mdl_context as c', 'ra.contextid', '=', 'c.id')
                ->where('ra.userid', $userId)
                ->select([
                    'ra.id as assignment_id',
                    'r.id as role_id',
                    'r.shortname',
                    'r.name as role_name',
                    'c.contextlevel',
                    'c.instanceid',
                    'ra.timemodified'
                ])
                ->orderBy('c.contextlevel')
                ->orderBy('r.sortorder')
                ->get();
        } catch (\Exception $e) {
            Log::error('Erro ao buscar assignments do usuário', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }
    
    public static function removeRoleByShortname($userId, $roleShortname)
    {
        try {
            // Buscar o role ID
            $roleId = self::getRoleIdByShortname($roleShortname);
            
            if (!$roleId) {
                return false;
            }
            
            // Remover todos os assignments deste role para este usuário
            $deleted = DB::connection('moodle')
                ->table('mdl_role_assignments')
                ->where('userid', $userId)
                ->where('roleid', $roleId)
                ->delete();
            
            return $deleted > 0;
            
        } catch (\Exception $e) {
            Log::error('Erro ao remover role por shortname', [
                'user_id' => $userId,
                'role_shortname' => $roleShortname,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
