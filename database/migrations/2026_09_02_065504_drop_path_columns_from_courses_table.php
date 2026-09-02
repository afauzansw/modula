<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['thumbnail_path', 'certificate_template_path']);
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('thumbnail_path')->nullable()->after('description');
            $table->string('certificate_template_path')->nullable()->after('status');
        });
    }
};
