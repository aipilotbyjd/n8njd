<?php

namespace App\Enums;

enum CredentialType: string
{
    case API_KEY = 'apiKey';
    case OAUTH2 = 'oauth2';
    case GENERIC = 'generic';
    case AWS = 'aws';
}
