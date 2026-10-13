<?php

namespace App\Enums;

enum RoleName: string
{
    case SuperAdmin = 'Super Admin';
    case PublisherAdmin = 'Publisher Admin';
    case Editor = 'Editor';
    case Reviewer = 'Reviewer';
    case Author = 'Author';
    case Reader = 'Reader';

    /** @return string[] */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
