<?php

namespace Modules\Projects\Enums;

/** Board columns, in order. */
enum TaskStatus: string
{
    case Backlog = 'backlog';
    case Todo = 'todo';
    case Doing = 'doing';
    case Review = 'review';
    case Done = 'done';
}
