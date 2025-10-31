<?php

namespace App\Enums;

enum ExecutionStatus: string
{
    case WAITING = 'waiting';
    case RUNNING = 'running';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case RETRYING = 'retrying';
}
