<?php

use App\Http\Controllers\v1\AdminController;
use App\Http\Controllers\v1\AiController;
use App\Http\Controllers\v1\AnalyticsController;
use App\Http\Controllers\v1\AuthController;
use App\Http\Controllers\v1\CollaborationController;
use App\Http\Controllers\v1\CredentialController;
use App\Http\Controllers\v1\ExecutionController;
use App\Http\Controllers\v1\NodeController;
use App\Http\Controllers\v1\NotificationController;
use App\Http\Controllers\v1\OrganizationController;
use App\Http\Controllers\v1\StorageController;
use App\Http\Controllers\v1\TemplateController;
use App\Http\Controllers\v1\VariableController;
use App\Http\Controllers\v1\WebhookController;
use App\Http\Controllers\v1\WorkflowController;
use App\Http\Middleware\EnsureOrganizationContext;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public webhook receivers
Route::prefix('webhook')->group(function () {
    Route::match(['post', 'get', 'put', 'patch', 'delete'], '{workflowId}/{path}', [WebhookController::class, 'handleIncomingWebhook'])->where('path', '.*');
});

Route::prefix('v1')->group(function () {

    // 1. AUTH SERVICE API
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::post('register', 'register');
        Route::post('login', 'login');
        Route::post('logout', 'logout')->middleware('auth:api');
        Route::post('refresh', 'refresh')->middleware('auth:api');
        Route::get('verify-email/{id}/{hash}', 'verifyEmail')->name('verification.verify');
        Route::post('resend-verification', 'resendVerification');
        Route::post('forgot-password', 'forgotPassword');
        Route::post('reset-password', 'resetPassword');
        Route::post('change-password', 'changePassword')->middleware('auth:api');
        Route::get('oauth/{provider}', 'oauthRedirect');
        Route::get('oauth/{provider}/callback', 'oauthCallback');
        Route::post('saml/login', 'samlLogin');
        Route::post('saml/acs', 'samlAcs');
        Route::post('mfa/enable', 'mfaEnable')->middleware('auth:api');
        Route::post('mfa/verify', 'mfaVerify')->middleware('auth:api');
        Route::post('mfa/disable', 'mfaDisable')->middleware('auth:api');
        Route::get('me', 'me')->middleware('auth:api');
        Route::put('profile', 'updateProfile')->middleware('auth:api');
        Route::delete('account', 'deleteAccount')->middleware('auth:api');
        Route::get('sessions', 'getSessions')->middleware('auth:api');
        Route::delete('sessions/{id}', 'deleteSession')->middleware('auth:api');
        Route::get('api-keys', 'getApiKeys')->middleware('auth:api');
        Route::post('api-keys', 'createApiKey')->middleware('auth:api');
        Route::delete('api-keys/{id}', 'deleteApiKey')->middleware('auth:api');
    });

    // 2. WORKFLOW SERVICE API
    Route::apiResource('workflows', WorkflowController::class)->middleware(['auth:api', EnsureOrganizationContext::class]);
    Route::prefix('workflows')->middleware(['auth:api', EnsureOrganizationContext::class])->controller(WorkflowController::class)->group(function () {
        Route::post('{id}/duplicate', 'duplicate');
        Route::patch('{id}/activate', 'activate');
        Route::patch('{id}/deactivate', 'deactivate');
        Route::post('import', 'import');
        Route::get('{id}/export', 'export');
        Route::post('bulk-import', 'bulkImport');
        Route::post('bulk-delete', 'bulkDelete');
        Route::post('bulk-activate', 'bulkActivate');
        Route::get('{id}/versions', 'versions');
        Route::get('{id}/versions/{versionId}', 'getVersion');
        Route::post('{id}/versions', 'createVersion');
        Route::post('{id}/versions/{versionId}/restore', 'restoreVersion');
        Route::get('{id}/versions/{v1}/compare/{v2}', 'compareVersions');
        Route::get('{id}/shares', 'getShares');
        Route::post('{id}/shares', 'createShare');
        Route::delete('{id}/shares/{userId}', 'deleteShare');
        Route::patch('{id}/shares/{userId}/permissions', 'updateSharePermissions');
        Route::get('{id}/comments', 'getComments');
        Route::post('{id}/comments', 'createComment');
        Route::put('{id}/comments/{commentId}', 'updateComment');
        Route::delete('{id}/comments/{commentId}', 'deleteComment');
        Route::get('{id}/sub-workflows', 'getSubWorkflows');
        Route::post('{id}/sub-workflows/link', 'linkSubWorkflow');
        Route::delete('{id}/sub-workflows/{subId}', 'unlinkSubWorkflow');
        Route::get('{id}/dependencies', 'getDependencies');
        Route::get('{id}/dependents', 'getDependents');
        Route::get('{id}/impact-analysis', 'getImpactAnalysis');
        Route::post('{id}/validate', 'validateWorkflow');
        Route::post('{id}/test-run', 'testRun');
        Route::get('{id}/health-check', 'healthCheck');
    });

    // 3. NODE REGISTRY SERVICE API
    Route::prefix('nodes')->middleware(['auth:api', EnsureOrganizationContext::class])->controller(NodeController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('categories', 'getCategories');
        Route::get('tags', 'getTags');
        Route::get('custom', 'getCustomNodes');
        Route::post('custom', 'createCustomNode');
        Route::put('custom/{id}', 'updateCustomNode');
        Route::delete('custom/{id}', 'deleteCustomNode');
        Route::post('custom/{id}/publish', 'publishCustomNode');
        Route::get('usage/stats', 'getUsageStats');
        Route::get('{type}', 'show');
        Route::get('{type}/schema', 'getSchema');
        Route::post('{type}/test', 'testNode');
        Route::post('{type}/validate-config', 'validateConfig');
        Route::get('{type}/parameters/dynamic', 'getDynamicParameters');
        Route::post('{type}/parameters/resolve', 'resolveParameters');
        Route::get('{type}/usage', 'getNodeUsage');
    });

    // 4. EXECUTION SERVICE API
    Route::prefix('executions')->middleware(['auth:api', EnsureOrganizationContext::class])->controller(ExecutionController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('{id}', 'show');
        Route::delete('{id}', 'destroy');
        Route::post('bulk-delete', 'bulkDelete');
        Route::post('{id}/stop', 'stop');
        Route::post('{id}/retry', 'retry');
        Route::post('{id}/resume', 'resume');
        Route::post('bulk-retry', 'bulkRetry');
        Route::get('{id}/nodes', 'getNodes');
        Route::get('{id}/nodes/{nodeId}', 'getNode');
        Route::get('{id}/logs', 'getLogs');
        Route::get('{id}/timeline', 'getTimeline');
        Route::get('{id}/data', 'getData');
        Route::get('{id}/errors', 'getErrors');
        Route::get('waiting', 'getWaiting');
        Route::post('{id}/wait/continue', 'continueWaiting');
        Route::post('{id}/wait/cancel', 'cancelWaiting');
        Route::get('stats', 'getStats');
        Route::get('stats/daily', 'getDailyStats');
        Route::get('stats/by-workflow', 'getStatsByWorkflow');
        Route::get('stats/by-status', 'getStatsByStatus');
        Route::get('stats/performance', 'getPerformanceStats');
        Route::get('queue/status', 'getQueueStatus');
        Route::get('queue/metrics', 'getQueueMetrics');
        Route::post('queue/clear', 'clearQueue');
        Route::post('queue/priority/{id}', 'setQueuePriority');
    });
    Route::post('workflows/{id}/execute', [ExecutionController::class, 'executeWorkflow'])->middleware(['auth:api', EnsureOrganizationContext::class]);
    Route::post('workflows/{id}/test-execute', [ExecutionController::class, 'testExecuteWorkflow'])->middleware(['auth:api', EnsureOrganizationContext::class]);

    // 5. CREDENTIALS SERVICE API
    Route::apiResource('credentials', CredentialController::class)->middleware(['auth:api', EnsureOrganizationContext::class]);
    Route::prefix('credentials')->middleware(['auth:api', EnsureOrganizationContext::class])->controller(CredentialController::class)->group(function () {
        Route::get('types', 'getTypes');
        Route::get('types/{type}/schema', 'getTypeSchema');
        Route::post('{id}/test', 'test');
        Route::get('{id}/test-status', 'getTestStatus');
        Route::get('{id}/oauth/authorize', 'oauthAuthorize');
        Route::get('{id}/oauth/callback', 'oauthCallback');
        Route::post('{id}/oauth/refresh', 'oauthRefresh');
        Route::get('{id}/shares', 'getShares');
        Route::post('{id}/shares', 'createShare');
        Route::delete('{id}/shares/{userId}', 'deleteShare');
        Route::get('{id}/usage', 'getUsage');
        Route::get('{id}/workflows', 'getWorkflows');
    });

    // 6. WEBHOOK SERVICE API
    Route::apiResource('webhooks', WebhookController::class)->middleware(['auth:api', EnsureOrganizationContext::class]);
    Route::prefix('webhooks')->middleware(['auth:api', EnsureOrganizationContext::class])->controller(WebhookController::class)->group(function () {
        Route::post('{id}/test', 'test');
        Route::get('{id}/test-url', 'getTestUrl');
        Route::get('{id}/logs', 'getLogs');
        Route::get('{id}/stats', 'getStats');
        Route::post('{id}/regenerate-token', 'regenerateToken');
        Route::put('{id}/ip-whitelist', 'updateIpWhitelist');
    });

    // 7. TEMPLATE/MARKETPLACE SERVICE API
    Route::prefix('templates')->middleware(['auth:api', EnsureOrganizationContext::class])->controller(TemplateController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('featured', 'getFeatured');
        Route::get('trending', 'getTrending');
        Route::get('categories', 'getCategories');
        Route::get('search', 'search');
        Route::get('favorites', 'getFavorites');
        Route::get('{id}', 'show');
        Route::post('{id}/use', 'useTemplate');
        Route::post('{id}/clone', 'cloneTemplate');
        Route::post('{id}/favorite', 'favoriteTemplate');
        Route::delete('{id}/favorite', 'unfavoriteTemplate');
        Route::post('publish', 'publish');
        Route::put('{id}', 'update');
        Route::delete('{id}', 'destroy');
        Route::get('{id}/reviews', 'getReviews');
        Route::post('{id}/reviews', 'createReview');
        Route::put('{id}/reviews/{reviewId}', 'updateReview');
        Route::get('{id}/stats', 'getStats');
        Route::post('{id}/track-usage', 'trackUsage');
    });

    // 8. ANALYTICS SERVICE API
    Route::prefix('analytics')->middleware(['auth:api', EnsureOrganizationContext::class])->controller(AnalyticsController::class)->group(function () {
        Route::get('dashboard', 'getDashboard');
        Route::get('overview', 'getOverview');
        Route::get('workflows/performance', 'getWorkflowPerformance');
        Route::get('workflows/success-rate', 'getWorkflowSuccessRate');
        Route::get('workflows/execution-time', 'getWorkflowExecutionTime');
        Route::get('workflows/most-used', 'getMostUsedWorkflows');
        Route::get('workflows/{id}/metrics', 'getWorkflowMetrics');
        Route::get('executions/timeline', 'getExecutionTimeline');
        Route::get('executions/status-breakdown', 'getExecutionStatusBreakdown');
        Route::get('executions/error-rate', 'getExecutionErrorRate');
        Route::get('executions/resource-usage', 'getExecutionResourceUsage');
        Route::get('nodes/usage', 'getNodeUsage');
        Route::get('nodes/performance', 'getNodePerformance');
        Route::get('nodes/error-rate', 'getNodeErrorRate');
        Route::get('cost/breakdown', 'getCostBreakdown');
        Route::get('cost/trends', 'getCostTrends');
        Route::get('cost/by-workflow', 'getCostByWorkflow');
        Route::get('reports', 'getReports');
        Route::post('reports', 'createReport');
        Route::get('reports/{id}', 'getReport');
        Route::post('reports/{id}/export', 'exportReport');
    });

    // 9. NOTIFICATION SERVICE API
    Route::prefix('notifications')->middleware(['auth:api', EnsureOrganizationContext::class])->controller(NotificationController::class)->group(function () {
        Route::get('/', 'index');
        Route::put('{id}/read', 'markAsRead');
        Route::post('mark-all-read', 'markAllAsRead');
        Route::delete('{id}', 'destroy');
        Route::get('settings', 'getSettings');
        Route::put('settings', 'updateSettings');
        Route::get('channels', 'getChannels');
        Route::post('channels', 'createChannel');
        Route::delete('channels/{id}', 'deleteChannel');
    });

    // 10. STORAGE SERVICE API
    Route::prefix('storage')->middleware(['auth:api', EnsureOrganizationContext::class])->controller(StorageController::class)->group(function () {
        Route::post('upload', 'upload');
        Route::post('upload/multipart/init', 'initMultipartUpload');
        Route::post('upload/multipart/{id}/part', 'uploadPart');
        Route::post('upload/multipart/{id}/complete', 'completeMultipartUpload');
        Route::get('files', 'getFiles');
        Route::get('files/{id}', 'getFile');
        Route::delete('files/{id}', 'deleteFile');
        Route::get('files/{id}/download', 'downloadFile');
        Route::post('files/{id}/share', 'shareFile');
    });

    // 11. ORGANIZATION SERVICE API
    Route::apiResource('organizations', OrganizationController::class)->middleware(['auth:api', EnsureOrganizationContext::class]);
    Route::prefix('organizations/{id}')->middleware(['auth:api', EnsureOrganizationContext::class])->controller(OrganizationController::class)->group(function () {
        Route::get('members', 'getMembers');
        Route::post('members', 'addMember');
        Route::delete('members/{userId}', 'removeMember');
        Route::patch('members/{userId}/role', 'updateMemberRole');
        Route::get('teams', 'getTeams');
        Route::post('teams', 'createTeam');
        Route::put('teams/{teamId}', 'updateTeam');
        Route::delete('teams/{teamId}', 'deleteTeam');
        Route::get('settings', 'getSettings');
        Route::put('settings', 'updateSettings');
        Route::get('usage', 'getUsage');
        Route::get('billing', 'getBilling');
    });

    // 12. AI/ML SERVICE API
    Route::prefix('ai')->middleware(['auth:api', EnsureOrganizationContext::class])->controller(AiController::class)->group(function () {
        Route::post('suggest-nodes', 'suggestNodes');
        Route::post('suggest-connections', 'suggestConnections');
        Route::post('optimize-workflow', 'optimizeWorkflow');
        Route::post('generate-workflow', 'generateWorkflow');
        Route::post('explain-error', 'explainError');
        Route::post('chat', 'chat');
        Route::post('generate-expression', 'generateExpression');
        Route::post('generate-code', 'generateCode');
    });

    // 13. ADMIN SERVICE API
    Route::prefix('admin')->middleware(['auth:api', EnsureOrganizationContext::class])->controller(AdminController::class)->group(function () {
        Route::get('system/health', 'getSystemHealth');
        Route::get('system/metrics', 'getSystemMetrics');
        Route::get('system/status', 'getSystemStatus');
        Route::post('system/maintenance/enable', 'enableMaintenance');
        Route::post('system/maintenance/disable', 'disableMaintenance');
        Route::get('users', 'getUsers');
        Route::post('users', 'createUser');
        Route::get('users/{id}', 'getUser');
        Route::put('users/{id}', 'updateUser');
        Route::delete('users/{id}', 'deleteUser');
        Route::post('users/{id}/suspend', 'suspendUser');
        Route::post('users/{id}/unsuspend', 'unsuspendUser');
        Route::get('workflows', 'getWorkflows');
        Route::post('workflows/{id}/force-stop', 'forceStopWorkflow');
        Route::delete('workflows/{id}/force-delete', 'forceDeleteWorkflow');
        Route::get('audit-logs', 'getAuditLogs');
        Route::get('audit-logs/export', 'exportAuditLogs');
        Route::get('config', 'getConfig');
        Route::put('config', 'updateConfig');
        Route::post('backup', 'backup');
        Route::get('backups', 'getBackups');
        Route::post('restore/{backupId}', 'restore');
    });

    // 14. VARIABLES & ENVIRONMENT SERVICE
    Route::apiResource('variables', VariableController::class)->middleware(['auth:api', EnsureOrganizationContext::class]);
    Route::prefix('environments')->middleware(['auth:api', EnsureOrganizationContext::class])->controller(VariableController::class)->group(function () {
        Route::get('/', 'getEnvironments');
        Route::post('/', 'createEnvironment');
        Route::put('{id}', 'updateEnvironment');
        Route::delete('{id}', 'deleteEnvironment');
        Route::post('{id}/activate', 'activateEnvironment');
    });
    Route::prefix('secrets')->middleware(['auth:api', EnsureOrganizationContext::class])->controller(VariableController::class)->group(function () {
        Route::get('/', 'getSecrets');
        Route::post('/', 'createSecret');
        Route::get('{id}', 'getSecret');
        Route::delete('{id}', 'deleteSecret');
    });

    // 15. REAL-TIME COLLABORATION SERVICE
    Route::prefix('workflows/{id}')->middleware(['auth:api', EnsureOrganizationContext::class])->controller(CollaborationController::class)->group(function () {
        Route::get('presence', 'getPresence');
        Route::post('presence/join', 'joinPresence');
        Route::post('presence/leave', 'leavePresence');
        Route::post('operations', 'submitOperation');
        Route::get('operations/{cursor}', 'getOperations');
        Route::post('lock', 'lock');
        Route::post('unlock', 'unlock');
    });
});
