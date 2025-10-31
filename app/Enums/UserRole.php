<?php

namespace App\Enums;

// These values should correspond to the names of roles you create in your database.
enum UserRole: string
{
    case ADMIN = 'admin';
    case MEMBER = 'member';
    case VIEWER = 'viewer';
}
