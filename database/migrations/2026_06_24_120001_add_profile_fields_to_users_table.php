<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
            $table->string('title')->nullable()->after('avatar');         // gelar akademik
            $table->string('affiliation')->nullable()->after('title');    // instansi/fakultas
            $table->string('orcid', 40)->nullable()->after('affiliation');
            $table->text('bio')->nullable()->after('orcid');
            $table->boolean('is_active')->default(true)->after('bio');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar', 'title', 'affiliation', 'orcid', 'bio', 'is_active', 'last_login_at',
            ]);
        });
    }
};
