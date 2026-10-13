<?php

namespace App\Enums;

enum CitationStyle: string
{
    case APA = 'apa';
    case MLA = 'mla';
    case Chicago = 'chicago';
    case BibTeX = 'bibtex';
    case RIS = 'ris';

    public function label(): string
    {
        return match ($this) {
            self::APA => 'APA 7th',
            self::MLA => 'MLA 9th',
            self::Chicago => 'Chicago',
            self::BibTeX => 'BibTeX',
            self::RIS => 'RIS',
        };
    }

    public function isMachineFormat(): bool
    {
        return in_array($this, [self::BibTeX, self::RIS], true);
    }
}
