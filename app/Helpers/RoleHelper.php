<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Session;

class RoleHelper
{
    public static function getPrimaryRole()
    {
        return Session::get('moodle_primary_role', 'user');
    }
    
    public static function getAllRoles()
    {
        return Session::get('moodle_user_roles', []);
    }
    
    public static function hasRole($roleName)
    {
        $userRoles = self::getAllRoles();
        
        foreach ($userRoles as $role) {
            if (strtolower($role->role_shortname) === strtolower($roleName)) {
                return true;
            }
        }
        
        return false;
    }
    
    public static function isAdmin()
    {
        return self::hasRole('admin') || 
               self::hasRole('administrator') || 
               self::hasRole('siteadmin') || 
               self::hasRole('manager') ||
               self::getPrimaryRole() === 'administrador';
    }
    
    public static function isRealAdmin()
    {
        return self::hasRole('admin') || 
               self::hasRole('administrator') || 
               self::hasRole('siteadmin') || 
               self::hasRole('manager') ||
               self::getPrimaryRole() === 'administrador';
    }
    
    public static function isTeacher()
    {
        return self::hasRole('editingteacher') || self::hasRole('teacher');
    }
    
    public static function isStudent()
    {
        return self::hasRole('student');
    }
    
    public static function getRoleDisplayName($role = null)
    {
        $role = $role ?? self::getPrimaryRole();
        
        $displayNames = [
            'administrador' => 'Administrador',
            'coordenador' => 'Coordenador',
            'professor' => 'Professor',
            'aluno' => 'Aluno',
            'visitante' => 'Visitante',
            'user' => 'Usuário'
        ];
        
        return $displayNames[$role] ?? ucfirst($role);
    }
    
    public static function getRoleIcon($role = null)
    {
        $role = $role ?? self::getPrimaryRole();
        
        $icons = [
            'administrador' => 'bi-shield-check',
            'coordenador' => 'bi-gear',
            'professor' => 'bi-mortarboard',
            'aluno' => 'bi-person',
            'visitante' => 'bi-eye',
            'user' => 'bi-person-circle'
        ];
        
        return $icons[$role] ?? 'bi-person-circle';
    }
    
    public static function getRoleBadgeClass($role = null)
    {
        $role = $role ?? self::getPrimaryRole();
        
        $classes = [
            'administrador' => 'badge bg-danger',
            'coordenador' => 'badge bg-warning',
            'professor' => 'badge bg-primary',
            'aluno' => 'badge bg-success',
            'visitante' => 'badge bg-secondary',
            'user' => 'badge bg-light text-dark'
        ];
        
        return $classes[$role] ?? 'badge bg-light text-dark';
    }
}
