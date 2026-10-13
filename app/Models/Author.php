<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Author extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::creating(function (Author $a) {
            $a->slug ??= static::uniqueSlug($a->name);
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'book_author')
            ->withPivot(['role', 'sequence'])
            ->withTimestamps();
    }

    public function publishedBooks(): BelongsToMany
    {
        return $this->books()->where('status', 'published');
    }

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo) {
            return Storage::disk('public')->url($this->photo);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&size=256&background=c8a45c&color=fff';
    }

    public function getDisplayNameAttribute(): string
    {
        return trim(($this->title ? $this->title.' ' : '').$this->name);
    }
}
