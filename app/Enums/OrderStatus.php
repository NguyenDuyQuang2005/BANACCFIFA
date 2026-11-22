<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Completed => 'Hoàn tất',
            self::Cancelled => 'Đã hủy',
        };
    }
}
