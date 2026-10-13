<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('photo')->nullable();
            $table->string('title')->nullable();           // gelar akademik, e.g. "Dr., M.Kom."
            $table->string('affiliation')->nullable();      // perguruan tinggi / instansi
            $table->string('faculty')->nullable();
            $table->string('department')->nullable();
            $table->string('orcid', 40)->nullable();
            $table->string('scholar_url')->nullable();
            $table->string('website')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
