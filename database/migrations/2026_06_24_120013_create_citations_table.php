<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('style', 20);              // apa, mla, chicago, bibtex, ris
            $table->longText('content');               // rendered citation string
            $table->timestamps();

            $table->unique(['book_id', 'style']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citations');
    }
};
