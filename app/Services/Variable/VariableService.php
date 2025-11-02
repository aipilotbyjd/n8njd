<?php

namespace App\Services\Variable;

use App\Models\Variable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class VariableService
{
    public function createVariable(array $data): Variable
    {
        if (!empty($data['is_secret'])) {
            $data['is_encrypted'] = true;
            $data['type'] = 'encrypted';
        }

        return Variable::create($data);
    }

    public function getVariable(string $id): ?Variable
    {
        return Variable::find($id);
    }

    public function updateVariable(string $id, array $data): ?Variable
    {
        $variable = Variable::find($id);

        if (!$variable) {
            return null;
        }

        if (!empty($data['is_secret'])) {
            $data['is_encrypted'] = true;
            $data['type'] = 'encrypted';
        }

        $variable->update($data);

        return $variable;
    }

    public function deleteVariable(string $id): bool
    {
        $variable = Variable::find($id);

        if (!$variable) {
            return false;
        }

        return $variable->delete();
    }

    public function getVariablesByOrg(?string $orgId)
    {
        if (!$orgId) return [];
        
        return Variable::where('organization_id', $orgId)->get();
    }

    public function getEnvironments(?string $orgId)
    {
        if (!$orgId) return [];
        return Variable::where('organization_id', $orgId)->where('scope', 'environment')->get();
    }

    public function createEnvironment(array $data)
    {
        $data['scope'] = 'environment';

        return $this->createVariable($data);
    }

    public function updateEnvironment(string $id, array $data)
    {
        return $this->updateVariable($id, $data);
    }

    public function deleteEnvironment(string $id)
    {
        return $this->deleteVariable($id);
    }

    public function activateEnvironment(string $id)
    {
        // This needs more complex logic related to which environment is active for a user/org.
        // For now, we can just return the environment.
        return $this->getVariable($id);
    }

    public function getSecrets(?string $orgId)
    {
        if (!$orgId) return [];
        
        return Variable::where('organization_id', $orgId)->where('is_encrypted', true)->get();
    }

    public function createSecret(array $data)
    {
        $data['is_encrypted'] = true;
        $data['type'] = 'encrypted';

        return $this->createVariable($data);
    }

    public function getSecret(string $id)
    {
        return $this->getVariable($id);
    }

    public function deleteSecret(string $id)
    {
        return $this->deleteVariable($id);
    }
}
