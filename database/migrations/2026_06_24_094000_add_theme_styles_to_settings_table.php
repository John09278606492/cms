<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('brand_primary')->nullable()->after('social_links');
            $table->string('brand_accent')->nullable()->after('brand_primary');
            $table->string('heading_font')->nullable()->after('brand_accent');
            $table->string('body_font')->nullable()->after('heading_font');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['brand_primary', 'brand_accent', 'heading_font', 'body_font']);
        });
    }
};
