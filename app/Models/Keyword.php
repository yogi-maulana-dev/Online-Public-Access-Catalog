<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Keyword extends Model
{
    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::creating(fn (Keyword $k) => $k->slug ??= Str::slug($k->name));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class);
    }

    public static function findOrCreateByName(string $name): self
    {
        return static::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => trim($name)],
        );
    }
}
