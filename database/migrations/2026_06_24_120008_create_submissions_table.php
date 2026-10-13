<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();   // submitter (account)
            $table->foreignId('editor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('reference', 30)->unique();   // human-friendly id e.g. SUB-2026-0001
            $table->string('title');
            $table->text('abstract')->nullable();

            // pending -> under_review -> revisions_requested -> accepted/rejected -> published
            $table->string('status', 30)->default('pending');
            $table->string('stage', 30)->default('submission'); // submission, review, copyediting, production
            $table->unsignedTinyInteger('current_round')->default(1);

            $table->text('author_note')->nullable();
            $table->text('editor_note')->nullable();
            $table->text('decision_note')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
