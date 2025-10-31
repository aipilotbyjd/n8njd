<?php

namespace App\Enums;

enum NodeType: string
{
    case TRIGGER = 'trigger';
    case ACTION = 'action';
    case LOGIC = 'logic';
    case WAIT = 'wait';
    case TRANSFORM = 'transform';
    case FILTER = 'filter';
}
