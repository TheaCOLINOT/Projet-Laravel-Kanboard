<?php

use App\Http\Controllers\CalendarExportController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InvitationController;
use Illuminate\Support\Facades\Route;

// Routes publiques
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Routes protégées (projets à la racine)
Route::middleware('auth')->group(function () {
    Route::get('/', [ProjectController::class, 'index'])->name('home');
    Route::get('/create', [ProjectController::class, 'creationForm'])->name('projects.create');
    Route::post('/store', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/{project}', [ProjectController::class, 'show'])->name('projects.show')->middleware('project.member');
    Route::patch('/{project}/update', [ProjectController::class, 'update'])->name('projects.update')->middleware('project.member');
    Route::get('/{project}/kanban', [ProjectController::class, 'kanban'])->name('projects.kanban')->middleware('project.member');
    Route::get('/{project}/list', [TaskController::class, 'list'])->name('tasks.list')->middleware('project.member');
    Route::post('/{project}/{column}/tasks', [TaskController::class, 'store'])->name('tasks.store')->middleware('project.member');
    Route::post('/{project}/tasks', [TaskController::class, 'storeFromList'])->name('tasks.storeFromList')->middleware('project.member');
    Route::post('/{project}/invite', [ProjectController::class, 'invite'])->name('projects.invite')->middleware('project.member');
    Route::get('/{project}/users', [ProjectController::class, 'showUsers'])->name('projects.users')->middleware('project.member');
    Route::patch('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'delete'])->name('tasks.delete');
    Route::post('/{project}/columns', [ColumnController::class, 'store'])->name('columns.store')->middleware('project.member');
    Route::post('/tasks/reorder', [TaskController::class, 'reorder'])->name('tasks.reorder');
    Route::get('/{project}/calendar', [TaskController::class, 'calendar'])->name('projects.calendar')->middleware('project.member');
    Route::get('/calendar/{project}.ics', [CalendarExportController::class, 'export'])->name('calendar.export')->middleware('project.member');
    Route::get('/invitations/accept/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');
});


