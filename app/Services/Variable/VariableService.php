<?php

namespace App\Services\Variable;

use App\Models\Variable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class VariableService
{
    public function createVariable(array $data): Variable
    {
        if ($data['is_secret']) {
            $data['encrypted_value'] = Crypt::encryptString($data['value']);
            unset($data['value']);
        }
        $data['id'] = Str::uuid();

        return Variable::create($data);
    }

    public function getVariable(string $id): ?Variable
    {
        $variable = Variable::find($id);

        if ($variable && $variable->is_secret) {
            $variable->value = Crypt::decryptString($variable->encrypted_value);
        }

        return $variable;
    }

    public function updateVariable(string $id, array $data): ?Variable
    {
        $variable = Variable::find($id);

        if (! $variable) {
            return null;
        }

        if (isset($data['is_secret']) && $data['is_secret']) {
            $data['encrypted_value'] = Crypt::encryptString($data['value']);
            unset($data['value']);
        }

        $variable->update($data);

        return $variable;
    }

    public function deleteVariable(string $id): bool
    {
        $variable = Variable::find($id);

        if (! $variable) {
            return false;
        }

        return $variable->delete();
    }

    public function getVariablesByOrg(string $orgId)
    {
        $variables = Variable::where('org_id', $orgId)->get();

        foreach ($variables as $variable) {
            if ($variable->is_secret) {
                $variable->value = Crypt::decryptString($variable->encrypted_value);
            }
        }

        return $variables;
    }

    public function getEnvironments(string $orgId)
    {
        return Variable::where('org_id', $orgId)->where('scope', 'environment')->get();
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

    public function getSecrets(string $orgId)
    {
        $secrets = Variable::where('org_id', $orgId)->where('is_secret', true)->get();
        foreach ($secrets as $secret) {
            $secret->value = Crypt::decryptString($secret->encrypted_value);
        }

        return $secrets;
    }

    public function createSecret(array $data)
    {
        $data['is_secret'] = true;

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
