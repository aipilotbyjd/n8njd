<?php

namespace App\Services\Node\Execution;

use App\Mail\WorkflowEmail;
use App\Services\Credential\CredentialResolver;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailNodeExecutor extends NodeExecutor
{
    public function execute(array $inputData = [])
    {
        $properties = $this->node->properties;

        $to = $this->replacePlaceholders($properties['to'] ?? '', $inputData);
        $subject = $this->replacePlaceholders($properties['subject'] ?? '', $inputData);
        $body = $this->replacePlaceholders($properties['body'] ?? '', $inputData);
        $credentialId = $properties['credential_id'] ?? null;

        Log::debug('Sending email', [
            'to' => $to,
            'subject' => $subject,
            'has_credential' => !empty($credentialId),
        ]);

        $credentials = CredentialResolver::resolveForEmail($credentialId);

        if ($credentials['smtp']) {
            $this->configureDynamicSmtp($credentials['smtp']);
        }

        try {
            $mailer = Mail::mailer();

            if ($credentials['from']) {
                $mailer->to($to)
                    ->send(new WorkflowEmail($subject, $body, $credentials['from'], $credentials['from_name']));
            } else {
                $mailer->to($to)
                    ->send(new WorkflowEmail($subject, $body));
            }

            Log::info('Email sent successfully', ['to' => $to]);

            return array_merge($inputData, [
                'email_sent' => true,
                'to' => $to,
                'subject' => $subject,
            ]);
        } catch (\Exception $e) {
            Log::error('Email sending failed', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception("Email sending failed: {$e->getMessage()}");
        }
    }

    private function configureDynamicSmtp(array $smtp): void
    {
        Config::set('mail.mailers.smtp.host', $smtp['host']);
        Config::set('mail.mailers.smtp.port', $smtp['port']);
        Config::set('mail.mailers.smtp.encryption', $smtp['encryption']);
        Config::set('mail.mailers.smtp.username', $smtp['username']);
        Config::set('mail.mailers.smtp.password', $smtp['password']);
    }

    private function replacePlaceholders($data, array $inputData)
    {
        if (is_string($data)) {
            return $this->replaceStringPlaceholders($data, $inputData);
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->replacePlaceholders($value, $inputData);
            }
        }

        return $data;
    }

    private function replaceStringPlaceholders(string $string, array $inputData): string
    {
        return preg_replace_callback('/{{\s*\$json\.([^\s}]+)\s*}}/', function ($matches) use ($inputData) {
            return data_get($inputData, $matches[1]);
        }, $string);
    }
}
