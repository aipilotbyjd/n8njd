<?php

namespace App\Enums;

enum WorkflowStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case DRAFT = 'draft';
}
