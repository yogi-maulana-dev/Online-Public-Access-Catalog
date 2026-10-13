<?php

namespace App\Models;

use App\Enums\CitationStyle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Citation extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'style' => CitationStyle::class,
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
