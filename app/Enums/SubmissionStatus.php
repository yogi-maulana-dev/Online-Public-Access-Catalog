<?php

namespace App\Enums;

enum SubmissionStatus: string
{
    case Pending = 'pending';
    case UnderReview = 'under_review';
    case RevisionsRequested = 'revisions_requested';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::UnderReview => 'Sedang Direview',
            self::RevisionsRequested => 'Perlu Revisi',
            self::Accepted => 'Diterima',
            self::Rejected => 'Ditolak',
            self::Published => 'Terbit',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::UnderReview => 'info',
            self::RevisionsRequested => 'warning',
            self::Accepted => 'success',
            self::Rejected => 'danger',
            self::Published => 'success',
        };
    }
}
