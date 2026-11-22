<?php

namespace App\Enums;

enum UserRole: string
{
    case Member = 'member';
    case Manager = 'manager';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Member => 'Thành viên',
            self::Manager => 'Admin quản lý',
            self::Admin => 'Admin dev',
        };
    }

    /**
     * Staff roles can open the admin area.
     */
    public function isStaff(): bool
    {
        return $this !== self::Member;
    }
}
