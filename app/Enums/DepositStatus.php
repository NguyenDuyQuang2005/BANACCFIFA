<?php

namespace App\Enums;

enum DepositStatus: string
{
    case Pending = 'pending';
    case Verifying = 'verifying';
    case Success = 'success';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Chờ thanh toán',
            self::Verifying => 'Đang xác minh',
            self::Success => 'Thành công',
            self::Failed => 'Thất bại',
            self::Cancelled => 'Đã hủy',
        };
    }
}
