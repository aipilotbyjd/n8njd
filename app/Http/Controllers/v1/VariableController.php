<?php

namespace App\Http\Controllers\Api\V1;

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
        return $this->variableService->getVariablesByOrg($request->user()->org_id);
    }

    public function store(StoreVariableRequest $request)
    {
        $data = $request->validated();
        $data['org_id'] = $request->user()->org_id;
        $data['user_id'] = $request->user()->id;

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
        return $this->variableService->getEnvironments($request->user()->org_id);
    }

    public function createEnvironment(StoreEnvironmentRequest $request)
    {
        $data = $request->validated();
        $data['org_id'] = $request->user()->org_id;
        $data['user_id'] = $request->user()->id;

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
        return $this->variableService->getSecrets($request->user()->org_id);
    }

    public function createSecret(StoreSecretRequest $request)
    {
        $data = $request->validated();
        $data['org_id'] = $request->user()->org_id;
        $data['user_id'] = $request->user()->id;

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
