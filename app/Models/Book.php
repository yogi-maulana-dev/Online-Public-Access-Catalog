<?php

namespace App\Models;

use App\Enums\BookStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Book extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'publication_date' => 'date',
            'published_at' => 'datetime',
            'is_open_access' => 'boolean',
            'is_featured' => 'boolean',
            'price' => 'decimal:2',
            'rating' => 'decimal:1',
            'status' => BookStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Book $book) {
            $book->slug ??= static::uniqueSlug($book->title);
        });
    }

    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;
        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ----------------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------------

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'book_author')
            ->withPivot(['role', 'sequence'])
            ->withTimestamps()
            ->orderByPivot('sequence');
    }

    public function keywords(): BelongsToMany
    {
        return $this->belongsToMany(Keyword::class);
    }

    public function submission(): HasOne
    {
        return $this->hasOne(Submission::class);
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(Download::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(BookView::class);
    }

    public function citations(): HasMany
    {
        return $this->hasMany(Citation::class);
    }

    // ----------------------------------------------------------------------
    // Scopes
    // ----------------------------------------------------------------------

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', BookStatus::Published->value);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    // ----------------------------------------------------------------------
    // Accessors / helpers
    // ----------------------------------------------------------------------

    public function getCoverUrlAttribute(): string
    {
        if ($this->cover_image) {
            return Storage::disk('public')->url($this->cover_image);
        }

        // Deterministic placeholder cover based on title.
        return 'https://placehold.co/400x600/1e3a5f/ffffff?text='.urlencode(Str::limit($this->title, 30));
    }

    public function pdfFile(): ?File
    {
        return $this->files->firstWhere('type', 'pdf') ?? $this->files->firstWhere('type', 'manuscript');
    }

    public function authorNames(): string
    {
        return $this->authors->pluck('name')->join(', ');
    }

    // ----------------------------------------------------------------------
    // Scout
    // ----------------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $this->loadMissing(['authors', 'keywords', 'category', 'publisher']);

        return [
            'id' => (int) $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'abstract' => $this->abstract,
            'isbn' => $this->isbn,
            'doi' => $this->doi,
            'year' => (int) $this->year,
            'authors' => $this->authors->pluck('name')->join(', '),
            'keywords' => $this->keywords->pluck('name')->join(', '),
            'category' => $this->category?->name,
            'publisher' => $this->publisher?->name,
            'status' => $this->status?->value,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === BookStatus::Published;
    }
}
