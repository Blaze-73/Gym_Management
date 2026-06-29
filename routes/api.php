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
use App\Http\Controllers\CoachReviewController;
use App\Http\Controllers\SiteReviewController;
use App\Http\Controllers\UserWorkoutController;
use App\Http\Controllers\UserProgramController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayPalConfigController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\EntitlementController;
use App\Http\Controllers\AdminNotificationController;
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
Route::get('/coaches/{coach}/reviews', [CoachReviewController::class, 'index']);
Route::get('/coaches/{coach}', [CoachController::class, 'show']);
Route::get('/site-reviews', [SiteReviewController::class, 'index']);

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

    // Subscriptions & PayPal payments
    Route::get('/entitlements/me', [EntitlementController::class, 'me']);
    Route::get('/subscriptions/me', [SubscriptionController::class, 'me']);
    Route::get('/subscriptions/alerts', [SubscriptionController::class, 'alerts']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);
    Route::get('/subscriptions/history', [SubscriptionController::class, 'history']);
    Route::post('/subscriptions/cancel', [SubscriptionController::class, 'cancel']);
    Route::get('/payments/status', [PaymentController::class, 'status']);
    Route::post('/payments/plan', [PaymentController::class, 'checkoutPlan']);
    Route::post('/payments/store', [PaymentController::class, 'checkoutStore']);
    Route::post('/payments/capture', [PaymentController::class, 'capture']);
    Route::post('/payments/cancel', [PaymentController::class, 'cancel']);

    // Attendance
        Route::prefix('attendance')->group(function(){
        Route::get('/today', [AttendanceController::class, 'today']);
        Route::post('/scan-gym', [AttendanceController::class, 'scanGym']);
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
        Route::get('/history', [NutritionController::class, 'history']);
        Route::get('/{date}', [NutritionController::class, 'show']);
        Route::post('/', [NutritionController::class, 'store']);
        Route::post('/meals', [NutritionController::class, 'addMeal']);
        Route::put('/meals/{id}', [NutritionController::class, 'updateMeal']);
        Route::delete('/meals/{id}', [NutritionController::class, 'deleteMeal']);
    });

        Route::prefix('coach')->group(function() {
        Route::get('/my-coach', [CoachController::class, 'myCoach']);
        Route::post('/assign', [CoachController::class, 'assignCoach']);
        Route::post('/cancel-request', [CoachController::class, 'cancelRequest']);
        Route::post('/end-assignment', [CoachController::class, 'requestLeaveAssignment']);
        Route::post('/cancel-leave', [CoachController::class, 'cancelLeaveRequest']);
        Route::post('/change', [CoachController::class, 'changeCoach']);
        Route::get('/is-staff', [CoachController::class, 'isStaff']);
        Route::get('/clients', [CoachController::class, 'myClients']);
        Route::get('/clients/{userId}/nutrition', [CoachController::class, 'clientNutrition']);
        Route::get('/clients/{userId}/workouts', [CoachController::class, 'clientWorkouts']);
        Route::get('/clients/{userId}/deliverables', [CoachController::class, 'clientDeliverables']);
        Route::post('/clients/{userId}/deliverables', [CoachController::class, 'sendDeliverable']);
        Route::get('/inbox', [CoachController::class, 'myInbox']);
        Route::post('/messages', [CoachController::class, 'sendMessageToCoach']);
        Route::patch('/deliverables/{id}/read', [CoachController::class, 'markDeliverableRead']);
        Route::get('/my-classes', [ScheduleController::class, 'coachClasses']);
        Route::get('/reviews/coach/{coach}', [CoachReviewController::class, 'myReview']);
        Route::post('/reviews', [CoachReviewController::class, 'store']);
        Route::put('/reviews/{review}', [CoachReviewController::class, 'update']);
    });

    Route::get('/site-reviews/me', [SiteReviewController::class, 'me']);
    Route::post('/site-reviews', [SiteReviewController::class, 'store']);
    Route::put('/site-reviews/{review}', [SiteReviewController::class, 'update']);

    Route::get('/schedules', [ScheduleController::class, 'index']);

    // ============================================================================
    // 3. ADMIN ONLY ROUTES
    // ============================================================================
        Route::middleware('role:admin')->group(function(){
        
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/admin/notifications', [AdminNotificationController::class, 'index']);
        Route::get('/dashboard/trends', [DashboardController::class, 'trends']);
        Route::get('/dashboard/export-report', [DashboardController::class, 'exportReport']);

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
        

        Route::get('/admin/store-orders', [AdminOrderController::class, 'storeOrders']);
        Route::put('/admin/store-orders/{order}/status', [AdminOrderController::class, 'updateStoreOrderStatus']);
        Route::get('/admin/plan-payments', [AdminOrderController::class, 'planPayments']);
        Route::get('/admin/subscriptions', [SubscriptionController::class, 'adminIndex']);
        Route::post('/admin/subscriptions/{subscription}/terminate', [SubscriptionController::class, 'adminTerminate']);

        Route::get('/attendance', [AttendanceController::class, 'index']);
        Route::get('/attendance/active', [AttendanceController::class, 'active']);
        Route::get('/admin/attendance/qr', [AttendanceController::class, 'gymQrCode']);
        Route::post('/admin/attendance/qr/regenerate', [AttendanceController::class, 'regenerateGymQr']);
        Route::get('/admin/attendance/daily', [AttendanceController::class, 'dailyStats']);

        Route::get('/admin/products', [ProductController::class, 'adminIndex']);

        Route::post('/products', [ProductController::class, 'store']);
        Route::post('/products/{product}', [ProductController::class, 'update']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
        Route::put('/products/{product}/stock', [ProductController::class, 'updateStock']);

        Route::get('/admin/schedules', [ScheduleController::class, 'adminIndex']);
        Route::post('/schedules', [ScheduleController::class, 'store']);
        Route::put('/schedules/{schedule}', [ScheduleController::class, 'update']);
        Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy']);

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

        Route::get('/admin/coaches', [CoachController::class, 'adminIndex']);
        Route::get('/admin/coach-requests', [CoachController::class, 'adminRequests']);
        Route::post('/admin/coach-requests/{id}/approve', [CoachController::class, 'adminApprove']);
        Route::post('/admin/coach-requests/{id}/reject', [CoachController::class, 'adminReject']);
        Route::get('/admin/coach-leave-requests', [CoachController::class, 'adminLeaveRequests']);
        Route::post('/admin/coach-leave-requests/{id}/approve', [CoachController::class, 'adminApproveLeave']);
        Route::post('/admin/coach-leave-requests/{id}/reject', [CoachController::class, 'adminRejectLeave']);
        Route::post('/admin/coach-assignments/{id}/end', [CoachController::class, 'adminEndAssignment']);
        Route::post('/admin/coach-assignments/assign', [CoachController::class, 'adminAssignClient']);
        Route::get('/admin/members/{userId}/coach-assignment', [CoachController::class, 'adminMemberAssignment']);
        Route::get('/admin/members/{userId}/nutrition', [CoachController::class, 'clientNutrition']);
        Route::get('/admin/members/{userId}/workouts', [CoachController::class, 'clientWorkouts']);

        Route::post('/coaches', [CoachController::class, 'store']);
        Route::put('/coaches/{coach}', [CoachController::class, 'update']);
        Route::delete('/coaches/{coach}', [CoachController::class, 'destroy']);
    });
});

Route::get('/test', function() {
    return response()->json(['message' => 'API is working!']);
});

// Local only: save PayPal Sandbox credentials to .env (visit /paypal-setup in frontend)
if (app()->environment('local')) {
    Route::get('/payments/setup-status', [PayPalConfigController::class, 'status']);
    Route::post('/payments/configure', [PayPalConfigController::class, 'store']);
}
