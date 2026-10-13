<?php

namespace App\Enums;

enum ReviewRecommendation: string
{
    case Accept = 'accept';
    case MinorRevisions = 'minor_revisions';
    case MajorRevisions = 'major_revisions';
    case Reject = 'reject';

    public function label(): string
    {
        return match ($this) {
            self::Accept => 'Terima',
            self::MinorRevisions => 'Revisi Minor',
            self::MajorRevisions => 'Revisi Mayor',
            self::Reject => 'Tolak',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Accept => 'success',
            self::MinorRevisions => 'info',
            self::MajorRevisions => 'warning',
            self::Reject => 'danger',
        };
    }
}
