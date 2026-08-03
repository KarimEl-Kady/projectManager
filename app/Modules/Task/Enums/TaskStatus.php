<?php

namespace App\Modules\Task\Enums;

enum TaskStatus: int
{
    case Todo = 1;
    case InProgress = 2;
    case Done = 3;

    public function label(): string
    {
        return match ($this) {
            self::Todo => 'todo',
            self::InProgress => 'in_progress',
            self::Done => 'done',
        };
    }

    public static function valueFromLabel(mixed $value): mixed
    {
        if (! is_string($value) || is_numeric($value)) {
            return $value;
        }

        foreach (self::cases() as $status) {
            if ($status->label() === str($value)->lower()->replace(' ', '_')->toString()) {
                return $status->value;
            }
        }

        return $value;
    }
}
