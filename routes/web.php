<?php
/**
 * Application Routes
 * 
 * Define all routes for the application.
 */

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\ActivityController;
use App\Controllers\QuizController;
use App\Controllers\NotificationController;
use App\Controllers\VersionController;
use App\Controllers\AdminController;
use App\Controllers\DashboardController;
use App\Controllers\HealthController;
use App\Controllers\ApiController;
use App\Controllers\LandingController;
use App\Controllers\EventController;
use App\Controllers\HomeController;
use App\Controllers\SupportController;
use App\Controllers\DevSupportController;
use App\Controllers\SpecialtyController;
use App\Controllers\UnitController;
use App\Controllers\CategoryController;
use App\Controllers\ProgramController;
use App\Controllers\LearningController;
use App\Controllers\ApprovalController;
use App\Controllers\AnalyticsController;
use App\Middleware\TenantMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Controllers\FixController;

$router = new Router();

// Global public routes (no tenant)
$router->get('/', [HomeController::class, 'index']);
$router->get('/cadastrar-clube', [HomeController::class, 'showRegisterClub']);
$router->post('/cadastrar-clube', [HomeController::class, 'registerClub']);
$router->get('/health', [HealthController::class, 'index']);
$router->get('/health/ping', [HealthController::class, 'ping']);
$router->get('/health/detailed', [HealthController::class, 'detailed']);
$router->get('/api', [ApiController::class, 'info']);
$router->get('/api/docs', [ApiController::class, 'docs']);
$router->get('/api/clubs', [ApiController::class, 'clubs']);

// Developer Support Panel (global - must be before /{tenant} routes)
$router->get('/dev/login', [DevSupportController::class, 'showLogin']);
$router->post('/dev/login', [DevSupportController::class, 'login']);
$router->get('/dev/logout', [DevSupportController::class, 'logout']);
$router->get('/dev/suporte', [DevSupportController::class, 'dashboard']);
$router->get('/dev/suporte/{id}', [DevSupportController::class, 'show']);
$router->post('/dev/suporte/{id}/responder', [DevSupportController::class, 'reply']);
$router->post('/dev/suporte/{id}/status', [DevSupportController::class, 'updateStatus']);

// Public landing page (per tenant)
$router->get('/{tenant}', [LandingController::class, 'index'], [TenantMiddleware::class]);

// Public routes (with tenant resolution)
$router->get('/{tenant}/login', [AuthController::class, 'showLogin'], [TenantMiddleware::class]);
$router->post('/{tenant}/login', [AuthController::class, 'login'], [TenantMiddleware::class]);
$router->get('/{tenant}/register', [AuthController::class, 'showRegister'], [TenantMiddleware::class]);
$router->post('/{tenant}/register', [AuthController::class, 'register'], [TenantMiddleware::class]);
$router->post('/{tenant}/logout', [AuthController::class, 'logout'], [TenantMiddleware::class]);
$router->get('/{tenant}/logout', [AuthController::class, 'logout'], [TenantMiddleware::class]);

// Activity routes (protected)
$router->get('/{tenant}/api/activities', [ActivityController::class, 'index'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/api/activities/{id}', [ActivityController::class, 'show'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/api/activities/{id}/start', [ActivityController::class, 'start'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/api/proofs/submit', [ActivityController::class, 'submitProof'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/api/progress', [ActivityController::class, 'myProgress'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/api/leaderboard', [ActivityController::class, 'leaderboard'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/api/proofs/pending', [ActivityController::class, 'pendingProofs'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/api/proofs/{id}/review', [ActivityController::class, 'reviewProof'], [TenantMiddleware::class, AuthMiddleware::class]);

// Quiz routes (protected)
$router->get('/{tenant}/api/quizzes/activity/{activity_id}', [QuizController::class, 'show'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/api/quizzes/{id}/start', [QuizController::class, 'start'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/api/quizzes/attempts/{attempt_id}/submit', [QuizController::class, 'submit'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/api/quizzes/{id}/attempts', [QuizController::class, 'myAttempts'], [TenantMiddleware::class, AuthMiddleware::class]);

// Notification routes (protected)
$router->get('/{tenant}/api/notifications', [NotificationController::class, 'index'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/api/notifications/unread', [NotificationController::class, 'unread'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/api/notifications/{id}/read', [NotificationController::class, 'markRead'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/api/notifications/read-all', [NotificationController::class, 'markAllRead'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/api/notifications/preferences', [NotificationController::class, 'updatePreferences'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/api/notifications/broadcast', [NotificationController::class, 'broadcast'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/api/push/subscribe', [NotificationController::class, 'subscribe'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/api/push/unsubscribe', [NotificationController::class, 'unsubscribe'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/api/push/test', [NotificationController::class, 'testPush'], [TenantMiddleware::class, AuthMiddleware::class]);

// Version routes (protected)
$router->get('/{tenant}/api/version', [VersionController::class, 'current'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/api/version/check', [VersionController::class, 'check'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/api/versions', [VersionController::class, 'index'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/api/versions', [VersionController::class, 'create'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/api/versions/{id}/promote', [VersionController::class, 'promote'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/api/versions/rollout', [VersionController::class, 'rollout'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/api/versions/rollback', [VersionController::class, 'rollback'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/api/versions/canary', [VersionController::class, 'canary'], [TenantMiddleware::class, AuthMiddleware::class]);

// Feature flag routes (protected)
$router->get('/{tenant}/api/features', [VersionController::class, 'featureFlags'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/api/features/{key}', [VersionController::class, 'checkFeature'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/api/features/{id}', [VersionController::class, 'updateFeature'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/api/features/tenant-override', [VersionController::class, 'setTenantFeature'], [TenantMiddleware::class, AuthMiddleware::class]);

// Admin panel routes (protected)
$router->get('/{tenant}/admin', [AdminController::class, 'dashboard'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/dashboard', [AdminController::class, 'dashboard'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/atividades', [AdminController::class, 'activities'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/activities', [AdminController::class, 'createActivity'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/activities/{id}', [AdminController::class, 'updateActivity'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/usuarios', [AdminController::class, 'users'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/usuarios/{id}/role', [AdminController::class, 'updateUserRole'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/provas', [AdminController::class, 'proofs'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/proofs/{id}/review', [AdminController::class, 'reviewUnifiedProof'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/versoes', [AdminController::class, 'versions'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/features', [AdminController::class, 'features'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/notificacoes', [AdminController::class, 'notifications'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/notifications/broadcast', [AdminController::class, 'sendBroadcast'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/quizzes', [AdminController::class, 'quizzes'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/quizzes', [AdminController::class, 'createQuiz'], [TenantMiddleware::class, AuthMiddleware::class]);

// Mission Control creation/management routes
$router->post('/{tenant}/admin/mission-control/specialty', [SpecialtyController::class, 'storeSpecialtyComplete'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/mission-control/class', [AdminController::class, 'storeClass'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/mission-control/class/{id}', [AdminController::class, 'updateClass'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/mission-control/class/{id}/delete', [AdminController::class, 'deleteClass'], [TenantMiddleware::class, AuthMiddleware::class]);

// Unit routes
$router->get('/{tenant}/admin/unidades', [UnitController::class, 'index'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/unidades/criar', [UnitController::class, 'create'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/unidades', [UnitController::class, 'store'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/unidades/{id}', [UnitController::class, 'edit'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/unidades/{id}', [UnitController::class, 'update'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/unidades/{id}/delete', [UnitController::class, 'delete'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/unidades/{id}/counselor', [UnitController::class, 'addCounselor'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/unidades/{id}/counselor/{user_id}/remove', [UnitController::class, 'removeCounselor'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/unidades/{id}/member', [UnitController::class, 'addMember'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/unidades/member/{user_id}/remove', [UnitController::class, 'removeMember'], [TenantMiddleware::class, AuthMiddleware::class]);

// Classes routes
$router->get('/{tenant}/admin/classes', [AdminController::class, 'classes'], [TenantMiddleware::class, AuthMiddleware::class]);

// Category management routes (Learning Engine)
$router->get('/{tenant}/admin/categorias', [CategoryController::class, 'index'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/categorias/{id}', [CategoryController::class, 'show'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/categorias', [CategoryController::class, 'store'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/categorias/{id}', [CategoryController::class, 'update'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/categorias/{id}/delete', [CategoryController::class, 'delete'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/categorias/reorder', [CategoryController::class, 'reorder'], [TenantMiddleware::class, AuthMiddleware::class]);

// Program management routes (Learning Engine)
$router->get('/{tenant}/admin/programas', [ProgramController::class, 'index'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/programas/criar', [ProgramController::class, 'create'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/programas', [ProgramController::class, 'store'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/programas/{id}/editar', [ProgramController::class, 'edit'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/programas/{id}', [ProgramController::class, 'update'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/programas/{id}/steps', [ProgramController::class, 'saveSteps'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/programas/{id}/publish', [ProgramController::class, 'publish'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/programas/{id}/delete', [ProgramController::class, 'delete'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/programas/{id}/users', [ProgramController::class, 'getUsers'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/programas/{id}/assign', [ProgramController::class, 'assign'], [TenantMiddleware::class, AuthMiddleware::class]);

// Approval management routes (Learning Engine)
$router->get('/{tenant}/admin/aprovacoes', [ApprovalController::class, 'index'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/aprovacoes/{id}/review', [ApprovalController::class, 'review'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/aprovacoes/{id}/approve', [ApprovalController::class, 'approve'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/aprovacoes/{id}/reject', [ApprovalController::class, 'reject'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/aprovacoes/{id}/bulk-approve-program', [ApprovalController::class, 'bulkApproveProgram'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/aprovacoes/bulk-approve', [ApprovalController::class, 'bulkApprove'], [TenantMiddleware::class, AuthMiddleware::class]);

// Analytics routes (Learning Engine)
$router->get('/{tenant}/admin/analytics', [AnalyticsController::class, 'index'], [TenantMiddleware::class, AuthMiddleware::class]);

// Permission management routes
$router->get('/{tenant}/admin/permissoes', [AdminController::class, 'permissions'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/permissoes', [AdminController::class, 'savePermissions'], [TenantMiddleware::class, AuthMiddleware::class]);

// Dashboard routes (Pathfinder App)
$router->get('/{tenant}/dashboard', [DashboardController::class, 'index'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/dashboard/mission/{id}/details', [DashboardController::class, 'missionDetails'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/atividades', [DashboardController::class, 'activities'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/atividades/{id}', [DashboardController::class, 'activityDetail'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/conquistas', [DashboardController::class, 'achievements'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/ranking', [DashboardController::class, 'leaderboard'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/perfil', [DashboardController::class, 'profile'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/provas', [DashboardController::class, 'proofs'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/notificacoes', [DashboardController::class, 'notifications'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/notificacoes/limpar', [DashboardController::class, 'clearNotifications'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/classes', [DashboardController::class, 'classes'], [TenantMiddleware::class, AuthMiddleware::class]);

// Learning Engine routes (Pathfinder)
$router->get('/{tenant}/aprendizado', [LearningController::class, 'index'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/aprendizado/{id}', [LearningController::class, 'show'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/aprendizado/step/{step_id}/modal', [LearningController::class, 'stepModal'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/aprendizado/step/{step_id}/submit', [LearningController::class, 'submitStep'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/aprendizado/{id}/submit-all', [LearningController::class, 'submitProgram'], [TenantMiddleware::class, AuthMiddleware::class]);

// Event routes
$router->get('/{tenant}/eventos', [EventController::class, 'index'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/eventos/{id}/inscrever', [EventController::class, 'enroll'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/eventos/{id}/cancelar', [EventController::class, 'cancel'], [TenantMiddleware::class, AuthMiddleware::class]);

// Category routes (Admin)
$router->get('/{tenant}/admin/categorias', [\App\Controllers\CategoryController::class, 'index'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/categorias/{id}', [\App\Controllers\CategoryController::class, 'show'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/categorias', [\App\Controllers\CategoryController::class, 'store'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/categorias/{id}', [\App\Controllers\CategoryController::class, 'update'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/categorias/{id}/delete', [\App\Controllers\CategoryController::class, 'delete'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/categorias/{id}/delete-cascade', [\App\Controllers\CategoryController::class, 'deleteCascade'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/categorias/reorder', [\App\Controllers\CategoryController::class, 'reorder'], [TenantMiddleware::class, AuthMiddleware::class]);

// Admin Specialty routes
$router->get('/{tenant}/admin/especialidades', [SpecialtyController::class, 'repository'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/especialidades/categoria/{id}', [SpecialtyController::class, 'repositoryByCategory'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/especialidades/atribuicoes', [SpecialtyController::class, 'assignments'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/especialidades/god-mode', [SpecialtyController::class, 'godMode'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/especialidades/god-mode/matrix', [SpecialtyController::class, 'godModeMatrix'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/especialidades/{id}/atribuir', [SpecialtyController::class, 'showAssign'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/especialidades/{id}/atribuir', [SpecialtyController::class, 'assign'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/especialidades/criar', [SpecialtyController::class, 'storeSpecialty'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/especialidades/{id}/requisitos', [SpecialtyController::class, 'editRequirements'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/especialidades/{id}/requisitos', [SpecialtyController::class, 'saveRequirements'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/especialidades/{id}/publicar', [SpecialtyController::class, 'publish'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/especialidades/atribuicao/{id}', [SpecialtyController::class, 'reviewAssignment'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/especialidades/requisito/{id}/aprovar', [SpecialtyController::class, 'adminApproveRequirement'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/especialidades/requisito/{id}/rejeitar', [SpecialtyController::class, 'adminRejectRequirement'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/especialidades/atribuicao/delete', [SpecialtyController::class, 'deleteAssignment'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/especialidades/atribuicao/{id}/detalhes', [SpecialtyController::class, 'assignmentDetails'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/especialidades/atribuicao/{id}/concluir', [SpecialtyController::class, 'adminCompleteAssignment'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/api/specialties/search', [SpecialtyController::class, 'search'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/api/specialties/{id}/delete', [SpecialtyController::class, 'delete'], [TenantMiddleware::class, AuthMiddleware::class]);

// Pathfinder Specialty routes
$router->get('/{tenant}/especialidades', [SpecialtyController::class, 'mySpecialties'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/especialidades/{id}', [SpecialtyController::class, 'show'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/especialidades/{id}/aprender', [SpecialtyController::class, 'learn'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/especialidades/{id}/responder', [SpecialtyController::class, 'submitAnswer'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/especialidades/{id}/proximo', [SpecialtyController::class, 'nextRequirement'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/especialidades/{id}/iniciar', [SpecialtyController::class, 'start'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/especialidades/{id}/prova', [SpecialtyController::class, 'submitProof'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/especialidades/{id}/requisito/prova', [SpecialtyController::class, 'submitRequirementProof'], [TenantMiddleware::class, AuthMiddleware::class]);

// Quiz routes
$router->get('/{tenant}/quiz/{id}', [QuizController::class, 'show'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/quiz/{id}/enviar', [QuizController::class, 'submit'], [TenantMiddleware::class, AuthMiddleware::class]);

// Support routes
$router->get('/{tenant}/suporte', [SupportController::class, 'index'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/suporte/novo', [SupportController::class, 'create'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/suporte', [SupportController::class, 'store'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/suporte/{id}', [SupportController::class, 'show'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/suporte/{id}/responder', [SupportController::class, 'reply'], [TenantMiddleware::class, AuthMiddleware::class]);

// Invitation routes (public - accept invitation)
$router->get('/{tenant}/convite/{token}', [\App\Controllers\InvitationController::class, 'accept'], [TenantMiddleware::class]);
$router->post('/{tenant}/convite/{token}', [\App\Controllers\InvitationController::class, 'accept'], [TenantMiddleware::class]);

// Invitation routes (admin)
$router->get('/{tenant}/admin/convites', [\App\Controllers\InvitationController::class, 'index'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/convites/novo', [\App\Controllers\InvitationController::class, 'create'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/convites/enviar', [\App\Controllers\InvitationController::class, 'store'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/convites/{id}/reenviar', [\App\Controllers\InvitationController::class, 'resend'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/convites/{id}/revogar', [\App\Controllers\InvitationController::class, 'revoke'], [TenantMiddleware::class, AuthMiddleware::class]);

// Email routes (admin)
$router->get('/{tenant}/admin/email/inbox', [\App\Controllers\EmailController::class, 'inbox'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/email/compose', [\App\Controllers\EmailController::class, 'compose'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/email/send', [\App\Controllers\EmailController::class, 'send'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/email/settings', [\App\Controllers\EmailController::class, 'settings'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/email/settings', [\App\Controllers\EmailController::class, 'settings'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/email/test', [\App\Controllers\EmailController::class, 'testConnection'], [TenantMiddleware::class, AuthMiddleware::class]);

// Member invitation routes (public - accept)
$router->get('/{tenant}/entrar/{token}', [\App\Controllers\InvitationController::class, 'membersAccept'], [TenantMiddleware::class]);
$router->post('/{tenant}/entrar/{token}', [\App\Controllers\InvitationController::class, 'membersAccept'], [TenantMiddleware::class]);

// Member invitation routes (admin)
$router->get('/{tenant}/admin/convites/membros', [\App\Controllers\InvitationController::class, 'membersIndex'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/convites/membros/novo', [\App\Controllers\InvitationController::class, 'membersCreate'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/convites/membros/enviar', [\App\Controllers\InvitationController::class, 'membersStore'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/convites/membros/{id}/reenviar', [\App\Controllers\InvitationController::class, 'membersResend'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/convites/membros/{id}/revogar', [\App\Controllers\InvitationController::class, 'membersRevoke'], [TenantMiddleware::class, AuthMiddleware::class]);

// Stripe payment routes (admin)
$router->get('/{tenant}/admin/financeiro', [\App\Controllers\StripeController::class, 'settings'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/pagamentos/conectar', [\App\Controllers\StripeController::class, 'connect'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/pagamentos/callback', [\App\Controllers\StripeController::class, 'callback'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->post('/{tenant}/admin/pagamentos/desconectar', [\App\Controllers\StripeController::class, 'disconnect'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/pagamentos/historico', [\App\Controllers\StripeController::class, 'history'], [TenantMiddleware::class, AuthMiddleware::class]);
$router->get('/{tenant}/admin/pagamentos/dashboard', [\App\Controllers\StripeController::class, 'dashboard'], [TenantMiddleware::class, AuthMiddleware::class]);

// Stripe webhook (public, no auth)
$router->post('/{tenant}/webhook/stripe', [\App\Controllers\StripeController::class, 'webhook'], [TenantMiddleware::class]);

// Temporary fix route
$router->get('/{tenant}/fix-xp', [FixController::class, 'run'], [TenantMiddleware::class, AuthMiddleware::class]);

return $router;
