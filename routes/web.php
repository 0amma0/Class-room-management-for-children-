<?php

use App\Http\Controllers\Admin\ActivityReviewController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminMasterDataController;
use App\Http\Controllers\Admin\AdminModuleController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\ClassProgressController;
use App\Http\Controllers\Admin\ContactSettingsController;
use App\Http\Controllers\Admin\DataExchangeController;
use App\Http\Controllers\Admin\LearningMaterialController as AdminLearningMaterialController;
use App\Http\Controllers\Admin\PublicActivityCardController;
use App\Http\Controllers\Admin\TeacherAccountController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Guru\ActivityController;
use App\Http\Controllers\Guru\AttendanceController;
use App\Http\Controllers\Guru\DevelopmentController;
use App\Http\Controllers\Guru\GalleryController;
use App\Http\Controllers\Guru\GuruDashboardController;
use App\Http\Controllers\Guru\LessonPlanController;
use App\Http\Controllers\Guru\ParentVisitController;
use App\Http\Controllers\Guru\StoryController;
use App\Http\Controllers\Guru\TeacherModuleController;
use App\Http\Controllers\LearningMaterialController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\PublicClassesController;
use App\Http\Controllers\PublicHomeController;
use App\Http\Controllers\PublicRegionController;
use App\Http\Controllers\ReflectionController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SupportConversationController;
use App\Http\Controllers\UserGuideController;
use Illuminate\Support\Facades\Route;

// Public Home Route (No authentication required)
Route::get('/', PublicHomeController::class)->name('home');
Route::get('/classes', [PublicClassesController::class, 'index'])->name('classes');
Route::get('/classes/module', [PublicClassesController::class, 'module'])->name('classes.module');
Route::get('/classes/module/download', [PublicClassesController::class, 'downloadModule'])->name('classes.module.download');
Route::get('/regions', [PublicRegionController::class, 'index'])->name('regions');
Route::post('/support', [SupportConversationController::class, 'storePublic'])
    ->middleware('throttle:10,1')
    ->name('support.public.store');
Route::get('/support/{token}', [SupportConversationController::class, 'showPublic'])
    ->where('token', '[A-Za-z0-9]{64}')
    ->name('support.public.show');
Route::post('/support/{token}/messages', [SupportConversationController::class, 'replyPublic'])
    ->where('token', '[A-Za-z0-9]{64}')
    ->middleware('throttle:10,1')
    ->name('support.public.messages.store');
Route::get('/materi', [LearningMaterialController::class, 'index'])->name('learning-materials.index');
Route::post('/materi/{learningMaterial}/requests', [LearningMaterialController::class, 'requestAccess'])
    ->middleware('throttle:5,1')
    ->name('learning-materials.requests.store');
Route::get('/permintaan-materi/{token}', [LearningMaterialController::class, 'showRequest'])
    ->name('learning-materials.requests.show');
Route::get('/permintaan-materi/{token}/unduh', [LearningMaterialController::class, 'downloadRequest'])
    ->name('learning-materials.requests.download');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated General Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/notifications', [NotificationsController::class, 'index'])
        ->middleware('role:admin,guru')
        ->name('notifications.index');
    Route::get('/notifications/{notification}', [NotificationsController::class, 'open'])
        ->middleware('role:admin,guru')
        ->name('notifications.open');
    Route::delete('/notifications/{notification}', [NotificationsController::class, 'destroy'])
        ->middleware('role:admin,guru')
        ->name('notifications.destroy');
    Route::get('/reflections/{reflection}', [ReflectionController::class, 'show'])
        ->middleware('role:admin,guru')
        ->name('reflections.show');
    Route::get('/staff/materials/{learningMaterial}/download', [LearningMaterialController::class, 'staffDownload'])
        ->middleware('role:admin,guru')
        ->name('learning-materials.staff-download');
    Route::get('/change-password', [ChangePasswordController::class, 'show'])->name('password.change');
    Route::post('/change-password', [ChangePasswordController::class, 'update'])->name('password.update');
});

// Admin Routes (Restricted strictly to role: admin)
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::get('/regions', [AdminModuleController::class, 'regions'])->name('regions');
        Route::get('/clusters', [AdminModuleController::class, 'clusters'])->name('clusters');
        Route::get('/environments', [AdminModuleController::class, 'environments'])->name('environments');
        Route::get('/classes', [AdminModuleController::class, 'classes'])->name('classes');
        Route::get('/class-progress', [ClassProgressController::class, 'index'])->name('class-progress');
        Route::put('/class-progress', [ClassProgressController::class, 'update'])->name('class-progress.update');
        Route::get('/support', [SupportConversationController::class, 'adminIndex'])->name('support');
        Route::post('/support/{conversation}/messages', [SupportConversationController::class, 'replyAdmin'])
            ->name('support.messages.store');
        Route::delete('/support/{conversation}', [SupportConversationController::class, 'destroyAdmin'])
            ->name('support.destroy');
        Route::get('/materials', [AdminLearningMaterialController::class, 'index'])->name('materials');
        Route::post('/materials', [AdminLearningMaterialController::class, 'store'])->name('materials.store');
        Route::post('/material-requests/{downloadRequest}/decision', [AdminLearningMaterialController::class, 'decide'])
            ->name('materials.requests.decide');
        Route::delete('/material-requests/{downloadRequest}', [AdminLearningMaterialController::class, 'destroy'])
            ->name('materials.requests.destroy');
        Route::get('/teachers', [TeacherAccountController::class, 'index'])->name('teachers');
        Route::post('/teachers', [TeacherAccountController::class, 'store'])->name('teachers.store');
        Route::put('/teachers/{teacherId}', [TeacherAccountController::class, 'update'])->name('teachers.update');
        Route::delete('/teachers/{teacherId}', [TeacherAccountController::class, 'deactivate'])->name('teachers.deactivate');
        Route::delete('/teachers/{teacherId}/permanent', [TeacherAccountController::class, 'destroy'])->name('teachers.destroy');
        Route::put('/teachers/{teacherId}/reactivate', [TeacherAccountController::class, 'reactivate'])->name('teachers.reactivate');
        Route::post('/teachers/{teacherId}/reset-password', [TeacherAccountController::class, 'resetPassword'])->name('teachers.reset-password');
        Route::get('/backups', [BackupController::class, 'index'])->name('backups');
        Route::post('/backups', [BackupController::class, 'store'])->name('backups.store');
        Route::get('/backups/export', [DataExchangeController::class, 'export'])->name('backups.export');
        Route::get('/backups/{filename}', [BackupController::class, 'download'])->name('backups.download');
        Route::get('/activity-cards', [PublicActivityCardController::class, 'index'])->name('activity-cards');
        Route::post('/activity-cards', [PublicActivityCardController::class, 'store'])->name('activity-cards.store');
        Route::put('/activity-cards/{card}', [PublicActivityCardController::class, 'update'])->name('activity-cards.update');
        Route::delete('/activity-cards/{card}', [PublicActivityCardController::class, 'destroy'])->name('activity-cards.destroy');
        Route::get('/contacts', [ContactSettingsController::class, 'index'])->name('contacts');
        Route::put('/contacts', [ContactSettingsController::class, 'update'])->name('contacts.update');
        Route::get('/user-guide', [UserGuideController::class, 'admin'])->name('user-guide');
        Route::get('/activities', [ActivityReviewController::class, 'index'])->name('activities');
        Route::put('/activities/{activity}', [ActivityReviewController::class, 'update'])->name('activities.update');
        Route::get('/students', [StudentController::class, 'index'])->name('students');
        Route::get('/students/export', [DataExchangeController::class, 'export'])->name('students.export');
        Route::get('/students/template', [DataExchangeController::class, 'template'])->name('students.template');
        Route::post('/students/import', [DataExchangeController::class, 'import'])->name('students.import');
        Route::post('/students', [StudentController::class, 'store'])->name('students.store');
        Route::put('/students/{studentId}', [StudentController::class, 'update'])->name('students.update');
        Route::delete('/students/{studentId}', [StudentController::class, 'deactivate'])->name('students.deactivate');
        Route::delete('/students/{studentId}/permanent', [StudentController::class, 'destroy'])->name('students.destroy');
        Route::post('/{resource}', [AdminMasterDataController::class, 'store'])
            ->whereIn('resource', ['regions', 'clusters', 'environments', 'classes'])
            ->name('master-data.store');
        Route::put('/{resource}/{id}', [AdminMasterDataController::class, 'update'])
            ->whereIn('resource', ['regions', 'clusters', 'environments', 'classes'])
            ->name('master-data.update');
        Route::delete('/{resource}/{id}', [AdminMasterDataController::class, 'deactivate'])
            ->whereIn('resource', ['regions', 'clusters', 'environments', 'classes'])
            ->name('master-data.deactivate');
        Route::delete('/{resource}/{id}/permanent', [AdminMasterDataController::class, 'destroy'])
            ->whereIn('resource', ['regions', 'clusters', 'environments', 'classes'])
            ->name('master-data.destroy');
    });

// Guru Routes (Restricted strictly to role: guru)
Route::prefix('guru')
    ->name('guru.')
    ->middleware(['auth', 'role:guru'])
    ->group(function () {
        Route::get('/dashboard', [GuruDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/user-guide', [UserGuideController::class, 'guru'])->name('user-guide');
        Route::get('/classes', [GuruDashboardController::class, 'classes'])->name('classes');
        Route::get('/support', [SupportConversationController::class, 'guruIndex'])->name('support');
        Route::post('/support', [SupportConversationController::class, 'storeGuru'])->name('support.store');
        Route::post('/support/{conversation}/messages', [SupportConversationController::class, 'replyGuru'])
            ->name('support.messages.store');
        Route::delete('/support/{conversation}', [SupportConversationController::class, 'destroyGuru'])
            ->name('support.destroy');
        Route::get('/class-progress', [ClassProgressController::class, 'index'])->name('class-progress');
        Route::put('/class-progress', [ClassProgressController::class, 'update'])->name('class-progress.update');
        Route::get('/materials', [LearningMaterialController::class, 'staffIndex'])->name('materials');
        Route::get('/students', [StudentController::class, 'index'])->name('students');
        Route::get('/students/export', [DataExchangeController::class, 'exportGuru'])->name('students.export');
        Route::post('/students', [StudentController::class, 'store'])->name('students.store');
        Route::put('/students/{studentId}', [StudentController::class, 'update'])->name('students.update');
        Route::delete('/students/{studentId}', [StudentController::class, 'deactivate'])->name('students.deactivate');
        Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances');
        Route::post('/attendances', [AttendanceController::class, 'store'])->name('attendances.store');
        Route::delete('/attendances/{attendance}', [AttendanceController::class, 'destroy'])->name('attendances.destroy');
        Route::get('/developments', [DevelopmentController::class, 'index'])->name('developments');
        Route::post('/developments', [DevelopmentController::class, 'store'])->name('developments.store');
        Route::put('/developments/{development}', [DevelopmentController::class, 'update'])->name('developments.update');
        Route::delete('/developments/{development}', [DevelopmentController::class, 'destroy'])->name('developments.destroy');
        Route::get('/activities', [ActivityController::class, 'index'])->name('activities');
        Route::post('/activities', [ActivityController::class, 'store'])->name('activities.store');
        Route::put('/activities/{activity}', [ActivityController::class, 'update'])->name('activities.update');
        Route::delete('/activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');
        Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery');
        Route::post('/gallery', [GalleryController::class, 'store'])->name('gallery.store');
        Route::get('/gallery/{photo}/file', [GalleryController::class, 'file'])->name('gallery.file');
        Route::get('/lesson-plans', [LessonPlanController::class, 'index'])->name('lesson-plans');
        Route::post('/lesson-plans', [LessonPlanController::class, 'store'])->name('lesson-plans.store');
        Route::put('/lesson-plans/{lessonPlan}', [LessonPlanController::class, 'update'])->name('lesson-plans.update');
        Route::delete('/lesson-plans/{lessonPlan}', [LessonPlanController::class, 'destroy'])->name('lesson-plans.destroy');
        Route::get('/stories', [StoryController::class, 'index'])->name('stories');
        Route::post('/stories', [StoryController::class, 'store'])->name('stories.store');
        Route::put('/stories/{story}', [StoryController::class, 'update'])->name('stories.update');
        Route::post('/stories/{story}/illustration', [StoryController::class, 'generate'])
            ->middleware('throttle:3,1')
            ->name('stories.generate');
        Route::get('/stories/{story}/image', [StoryController::class, 'image'])->name('stories.image');
        Route::get('/stories/{story}/image/download', [StoryController::class, 'downloadImage'])->name('stories.image.download');
        Route::get('/parent-visits', [ParentVisitController::class, 'index'])->name('parent-visits');
        Route::post('/parent-visits', [ParentVisitController::class, 'store'])->name('parent-visits.store');
        Route::put('/parent-visits/{parentVisit}', [ParentVisitController::class, 'update'])->name('parent-visits.update');
        Route::delete('/parent-visits/{parentVisit}', [ParentVisitController::class, 'destroy'])->name('parent-visits.destroy');
        Route::get('/teacher-module', [TeacherModuleController::class, 'index'])->name('teacher-module');
        Route::post('/teacher-module/reflections', [TeacherModuleController::class, 'storeReflection'])->name('teacher-module.reflections.store');
        Route::put('/teacher-module/reflections/{reflection}', [TeacherModuleController::class, 'updateReflection'])->name('teacher-module.reflections.update');
        Route::delete('/teacher-module/reflections/{reflection}', [TeacherModuleController::class, 'destroyReflection'])->name('teacher-module.reflections.destroy');
        Route::get('/teacher-module/pdf', [TeacherModuleController::class, 'pdf'])->name('teacher-module.pdf');
    });
