<?php

namespace App\Enums;

enum BankTransactionStatus: string
{
    /** Matched a deposit code and amount; balance credited automatically. */
    case Matched = 'matched';

    /** Matched a deposit code but the amount differs; deposit moved to "Đang xác minh" for an admin. */
    case AmountMismatch = 'amount_mismatch';

    /** Matched a deposit that is no longer open (cancelled/failed); needs an admin. */
    case DepositClosed = 'deposit_closed';

    /** No deposit code found in the transfer content; needs an admin. */
    case Unmatched = 'unmatched';

    /** Outgoing transfer or otherwise not a top-up; stored for the record only. */
    case Ignored = 'ignored';

    public function label(): string
    {
        return match ($this) {
            self::Matched => 'Đã cộng tự động',
            self::AmountMismatch => 'Sai số tiền',
            self::DepositClosed => 'Lệnh nạp đã đóng',
            self::Unmatched => 'Không khớp nội dung',
            self::Ignored => 'Bỏ qua',
        };
    }

    public function needsReview(): bool
    {
        return in_array($this, [self::AmountMismatch, self::DepositClosed, self::Unmatched], true);
    }
}
