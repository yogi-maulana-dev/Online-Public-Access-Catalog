<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publisher_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('slug')->unique();
            $table->string('isbn', 20)->nullable()->index();
            $table->string('eisbn', 20)->nullable();
            $table->string('doi')->nullable()->index();

            $table->text('abstract')->nullable();
            $table->longText('description')->nullable();

            $table->unsignedSmallInteger('year')->nullable()->index();
            $table->date('publication_date')->nullable();
            $table->string('edition')->nullable();
            $table->string('language', 30)->default('Indonesia');
            $table->unsignedInteger('pages')->nullable();
            $table->string('format', 30)->default('PDF');     // PDF, E-Book, Hardcover, Paperback
            $table->string('series')->nullable();
            $table->string('license', 60)->nullable();        // e.g. CC BY 4.0

            $table->string('cover_image')->nullable();
            $table->boolean('is_open_access')->default(true);
            $table->decimal('price', 12, 2)->nullable();

            // Catalog visibility status (workflow lives in submissions table)
            $table->string('status', 20)->default('draft');   // draft, published, archived
            $table->boolean('is_featured')->default(false);

            $table->unsignedBigInteger('view_count')->default(0);
            $table->unsignedBigInteger('download_count')->default(0);
            $table->unsignedBigInteger('citation_count')->default(0);

            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
        });

        // Full-text index for the database/MySQL search fallback (no Meilisearch required).
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE books ADD FULLTEXT books_fulltext (title, subtitle, abstract, description)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
