<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function getSystemHealth(Request $request)
    {
        return $this->adminService->getSystemHealth();
    }

    public function getSystemMetrics(Request $request)
    {
        return $this->adminService->getSystemMetrics();
    }

    public function getSystemStatus(Request $request)
    {
        return $this->adminService->getSystemStatus();
    }

    public function enableMaintenance(Request $request)
    {
        return $this->adminService->enableMaintenance();
    }

    public function disableMaintenance(Request $request)
    {
        return $this->adminService->disableMaintenance();
    }

    public function getUsers(Request $request)
    {
        return $this->adminService->getUsers();
    }

    public function createUser(Request $request)
    {
        return $this->adminService->createUser($request->all());
    }

    public function getUser(Request $request, $id)
    {
        return $this->adminService->getUser($id);
    }

    public function updateUser(Request $request, $id)
    {
        return $this->adminService->updateUser($id, $request->all());
    }

    public function deleteUser(Request $request, $id)
    {
        return $this->adminService->deleteUser($id);
    }

    public function suspendUser(Request $request, $id)
    {
        return $this->adminService->suspendUser($id);
    }

    public function unsuspendUser(Request $request, $id)
    {
        return $this->adminService->unsuspendUser($id);
    }

    public function getWorkflows(Request $request)
    {
        return $this->adminService->getWorkflows();
    }

    public function forceStopWorkflow(Request $request, $id)
    {
        return $this->adminService->forceStopWorkflow($id);
    }

    public function forceDeleteWorkflow(Request $request, $id)
    {
        return $this->adminService->forceDeleteWorkflow($id);
    }

    public function getAuditLogs(Request $request)
    {
        return $this->adminService->getAuditLogs();
    }

    public function exportAuditLogs(Request $request)
    {
        return $this->adminService->exportAuditLogs();
    }

    public function getConfig(Request $request)
    {
        return $this->adminService->getConfig();
    }

    public function updateConfig(Request $request)
    {
        return $this->adminService->updateConfig($request->all());
    }

    public function backup(Request $request)
    {
        return $this->adminService->backup();
    }

    public function getBackups(Request $request)
    {
        return $this->adminService->getBackups();
    }

    public function restore(Request $request, $backupId)
    {
        return $this->adminService->restore($backupId);
    }
}
