<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkoutController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\NutritionController;
use App\Http\Controllers\CoachController;
use App\Http\Controllers\UserWorkoutController;
use App\Http\Controllers\UserProgramController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ============================================================================
// 1. PUBLIC ROUTES (No Auth Required)
// ============================================================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/plans', [PlanController::class, 'index']);
Route::get('/plans/{plan}', [PlanController::class, 'show']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/workouts', [WorkoutController::class, 'index']);
Route::get('/workouts/{workout}', [WorkoutController::class, 'show']);
Route::get('/programs', [ProgramController::class, 'index']);
Route::get('/programs/{program}', [ProgramController::class, 'show']);
Route::get('/exercises', [ExerciseController::class, 'index']);
Route::get('/coaches', [CoachController::class, 'index']);
Route::get('/coaches/{coach}', [CoachController::class, 'show']);

// ============================================================================
// 2. AUTHENTICATED ROUTES (Token Required)
// ============================================================================
Route::middleware('auth:sanctum')->group(function() {
    
    // Auth & Profile
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::put('/password', [ProfileController::class, 'updatePassword']);
        Route::put('/settings', [ProfileController::class, 'updateSettings']);
        Route::delete('/', [ProfileController::class, 'destroy']);
    });

    // CLIENT MEMBERSHIP ACTION
    // This allows clients to request a plan. 
    // We move this OUT of the Admin group so clients can actually use it.
    Route::post('/memberships', [MembershipController::class, 'store']);
    Route::get('/memberships/me', [MembershipController::class, 'me']); 


    // Attendance
    Route::prefix('attendance')->group(function(){
        Route::post('/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/check-out', [AttendanceController::class, 'checkOut']);
        Route::get('/history', [AttendanceController::class, 'history']);
        Route::get('/active', [AttendanceController::class, 'active']);
    });

    // Orders
    Route::apiResource('orders', OrderController::class);
    Route::get('/orders/statistics', [OrderController::class, 'statistics']);
    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);

    // Client Features
    Route::prefix('user-workouts')->group(function() {
        Route::get('/', [UserWorkoutController::class, 'index']);
        Route::post('/start', [UserWorkoutController::class, 'start']);
        Route::post('/{id}/progress', [UserWorkoutController::class, 'updateProgress']);
        Route::post('/{id}/complete', [UserWorkoutController::class, 'complete']);
        Route::get('/statistics', [UserWorkoutController::class, 'statistics']);
    });

    Route::prefix('user-programs')->group(function() {
        Route::get('/', [UserProgramController::class, 'index']);
        Route::get('/active', [UserProgramController::class, 'active']);
        Route::post('/enroll', [UserProgramController::class, 'enroll']);
        Route::post('/{id}/progress', [UserProgramController::class, 'progress']);
        Route::post('/{id}/complete', [UserProgramController::class, 'complete']);
    });

    Route::prefix('nutrition')->group(function() {
        Route::get('/', [NutritionController::class, 'index']);
        Route::get('/{date}', [NutritionController::class, 'show']);
        Route::post('/', [NutritionController::class, 'store']);
        Route::post('/meals', [NutritionController::class, 'addMeal']);
        Route::put('/meals/{id}', [NutritionController::class, 'updateMeal']);
        Route::delete('/meals/{id}', [NutritionController::class, 'deleteMeal']);
    });

    Route::prefix('coach')->group(function() {
        Route::get('/my-coach', [CoachController::class, 'myCoach']);
        Route::post('/assign', [CoachController::class, 'assignCoach']);
    });

    // ============================================================================
    // 3. ADMIN ONLY AUTH ROUTES
    // ============================================================================
    Route::middleware('role:admin')->group(function(){
        
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/trends', [DashboardController::class, 'trends']);

        Route::apiResource('users', UserController::class);
        Route::get('/members', [UserController::class, 'index']);
        Route::get('/members/{user}', [UserController::class, 'show']);

        // Plan Management
        Route::post('/plans', [PlanController::class, 'store']);
        Route::put('/plans/{plan}', [PlanController::class, 'update']);
        Route::delete('/plans/{plan}', [PlanController::class, 'destroy']);

        // Membership Management (ADMIN SIDE)
        Route::get('/memberships/pending', [MembershipController::class, 'pending']);

        Route::get('/memberships', [MembershipController::class, 'index']);
        Route::get('/memberships/{membership}', [MembershipController::class, 'show']);
        Route::put('/memberships/{membership}', [MembershipController::class, 'update']);
        Route::delete('/memberships/{membership}', [MembershipController::class, 'destroy']);
        

        Route::get('/attendance', [AttendanceController::class, 'index']);
        Route::get('/attendance/active', [AttendanceController::class, 'active']);

        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
        Route::put('/products/{product}/stock', [ProductController::class, 'updateStock']);

        Route::apiResource('schedules', ScheduleController::class);

        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        Route::post('/workouts', [WorkoutController::class, 'store']);
        Route::put('/workouts/{workout}', [WorkoutController::class, 'update']);
        Route::delete('/workouts/{workout}', [WorkoutController::class, 'destroy']);

        Route::post('/exercises', [ExerciseController::class, 'store']);
        Route::put('/exercises/{exercise}', [ExerciseController::class, 'update']);
        Route::delete('/exercises/{exercise}', [ExerciseController::class, 'destroy']);

        Route::post('/programs', [ProgramController::class, 'store']);
        Route::put('/programs/{program}', [ProgramController::class, 'update']);
        Route::delete('/programs/{program}', [ProgramController::class, 'destroy']);

        Route::post('/coaches', [CoachController::class, 'store']);
        Route::put('/coaches/{coach}', [CoachController::class, 'update']);
    });
});

Route::get('/test', function() {
    return response()->json(['message' => 'API is working!']);
});
