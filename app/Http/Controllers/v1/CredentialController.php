<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreCredentialRequest;
use App\Http\Requests\V1\UpdateCredentialRequest;
use App\Services\Credential\CredentialService;
use Illuminate\Http\Request;

class CredentialController extends Controller
{
    protected $credentialService;

    public function __construct(CredentialService $credentialService)
    {
        $this->credentialService = $credentialService;
    }

    public function index(Request $request)
    {
        return $this->credentialService->getCredentialsByOrg($request->user()->org_id);
    }

    public function store(StoreCredentialRequest $request)
    {
        return $this->credentialService->createCredential($request->validated(), $request->user()->org_id, $request->user()->id);
    }

    public function show(Request $request, $id)
    {
        return $this->credentialService->getCredential($id);
    }

    public function update(UpdateCredentialRequest $request, $id)
    {
        return $this->credentialService->updateCredential($id, $request->validated());
    }

    public function destroy(Request $request, $id)
    {
        return $this->credentialService->deleteCredential($id);
    }

    public function getTypes(Request $request)
    {
        return $this->credentialService->getTypes();
    }

    public function getTypeSchema(Request $request, $type)
    {
        return $this->credentialService->getTypeSchema($type);
    }

    public function test(Request $request, $id)
    {
        return $this->credentialService->test($id);
    }

    public function getTestStatus(Request $request, $id)
    {
        return $this->credentialService->getTestStatus($id);
    }

    public function oauthAuthorize(Request $request, $id)
    {
        return $this->credentialService->oauthAuthorize($id);
    }

    public function oauthCallback(Request $request, $id)
    {
        return $this->credentialService->oauthCallback($id, $request->all());
    }

    public function oauthRefresh(Request $request, $id)
    {
        return $this->credentialService->oauthRefresh($id);
    }

    public function getShares(Request $request, $id)
    {
        return $this->credentialService->getShares($id);
    }

    public function createShare(Request $request, $id)
    {
        return $this->credentialService->createShare($id, $request->input('user_id'));
    }

    public function deleteShare(Request $request, $id, $userId)
    {
        return $this->credentialService->deleteShare($id, $userId);
    }

    public function getUsage(Request $request, $id)
    {
        return $this->credentialService->getUsage($id);
    }

    public function getWorkflows(Request $request, $id)
    {
        return $this->credentialService->getWorkflows($id);
    }
}
