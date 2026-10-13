<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('round')->default(1);

            // assigned -> accepted -> completed (or declined)
            $table->string('status', 20)->default('assigned');
            $table->string('recommendation', 30)->nullable(); // accept, minor_revisions, major_revisions, reject
            $table->unsignedTinyInteger('rating')->nullable();  // 1-5 overall quality

            $table->text('comments_author')->nullable();   // visible to author
            $table->text('comments_editor')->nullable();    // confidential to editor

            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['submission_id', 'reviewer_id', 'round']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
