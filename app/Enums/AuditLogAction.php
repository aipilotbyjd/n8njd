<?php

namespace App\Enums;

enum AuditLogAction: string
{
    // User Actions
    case USER_LOGGED_IN = 'user.logged_in';
    case USER_REGISTERED = 'user.registered';
    case USER_DELETED = 'user.deleted';

    // Workflow Actions
    case WORKFLOW_CREATED = 'workflow.created';
    case WORKFLOW_DELETED = 'workflow.deleted';
    case WORKFLOW_EXECUTED = 'workflow.executed';

    // Credential Actions
    case CREDENTIAL_CREATED = 'credential.created';
    case CREDENTIAL_DELETED = 'credential.deleted';
    case CREDENTIAL_TESTED = 'credential.tested';
}
