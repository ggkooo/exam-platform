<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RoleManagementController;
use App\Http\Controllers\SessionController;
use \App\Helpers\MoodleAuth;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/dashboard', function () {
    $redirect = MoodleAuth::require();
    if ($redirect) {
        return $redirect;
    }
    return view('dashboard');
})->name('dashboard');

Route::post('/session/refresh-roles', [SessionController::class, 'refreshRoles'])->name('session.refresh.roles');

Route::middleware(['web'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/roles', [RoleManagementController::class, 'index'])->name('roles');
    Route::post('/roles/assign', [RoleManagementController::class, 'assignRole'])->name('roles.assign');
    Route::post('/roles/remove', [RoleManagementController::class, 'removeRole'])->name('roles.remove');
    
    Route::get('/users', [RoleManagementController::class, 'users'])->name('users');
    Route::get('/users/{id}/roles', [RoleManagementController::class, 'userRoles'])->name('user.roles');
    Route::post('/users/roles/assign', [RoleManagementController::class, 'assignRoleToUser'])->name('user.roles.assign');
    Route::post('/users/roles/remove', [RoleManagementController::class, 'removeRoleFromUser'])->name('user.roles.remove');
});
