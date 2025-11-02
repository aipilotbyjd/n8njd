<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreEnvironmentRequest;
use App\Http\Requests\V1\StoreSecretRequest;
use App\Http\Requests\V1\StoreVariableRequest;
use App\Http\Requests\V1\UpdateEnvironmentRequest;
use App\Http\Requests\V1\UpdateVariableRequest;
use App\Services\Variable\VariableService;
use Illuminate\Http\Request;

class VariableController extends Controller
{
    protected $variableService;

    public function __construct(VariableService $variableService)
    {
        $this->variableService = $variableService;
    }

    public function index(Request $request)
    {
        $orgId = $request->user()->organizations()->first()->id ?? null;
        return $this->variableService->getVariablesByOrg($orgId);
    }

    public function store(StoreVariableRequest $request)
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organizations()->first()->id ?? null;
        $data['created_by'] = $request->user()->id;

        return $this->variableService->createVariable($data);
    }

    public function show(string $id)
    {
        return $this->variableService->getVariable($id);
    }

    public function update(UpdateVariableRequest $request, string $id)
    {
        return $this->variableService->updateVariable($id, $request->validated());
    }

    public function destroy(string $id)
    {
        return $this->variableService->deleteVariable($id);
    }

    public function getEnvironments(Request $request)
    {
        $orgId = $request->user()->organizations()->first()->id ?? null;
        return $this->variableService->getEnvironments($orgId);
    }

    public function createEnvironment(StoreEnvironmentRequest $request)
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organizations()->first()->id ?? null;
        $data['created_by'] = $request->user()->id;

        return $this->variableService->createEnvironment($data);
    }

    public function updateEnvironment(UpdateEnvironmentRequest $request, string $id)
    {
        return $this->variableService->updateEnvironment($id, $request->validated());
    }

    public function deleteEnvironment(string $id)
    {
        return $this->variableService->deleteEnvironment($id);
    }

    public function activateEnvironment(string $id)
    {
        return $this->variableService->activateEnvironment($id);
    }

    public function getSecrets(Request $request)
    {
        $orgId = $request->user()->organizations()->first()->id ?? null;
        return $this->variableService->getSecrets($orgId);
    }

    public function createSecret(StoreSecretRequest $request)
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organizations()->first()->id ?? null;
        $data['created_by'] = $request->user()->id;

        return $this->variableService->createSecret($data);
    }

    public function getSecret(string $id)
    {
        return $this->variableService->getSecret($id);
    }

    public function deleteSecret(string $id)
    {
        return $this->variableService->deleteSecret($id);
    }
}
