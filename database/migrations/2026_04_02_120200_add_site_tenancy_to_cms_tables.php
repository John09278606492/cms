<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->dropUnique('categories_slug_unique');
            $table->unique(['site_id', 'slug']);
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->dropUnique('tags_slug_unique');
            $table->unique(['site_id', 'slug']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->dropUnique('posts_slug_unique');
            $table->unique(['site_id', 'slug']);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->dropUnique('pages_slug_unique');
            $table->unique(['site_id', 'slug']);
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->unique('site_id');
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('menu_locations', function (Blueprint $table) {
            $table->dropUnique('menu_locations_location_unique');
            $table->unique(['menu_id', 'location']);
        });
    }

    public function down(): void
    {
        Schema::table('menu_locations', function (Blueprint $table) {
            $table->dropUnique('menu_locations_menu_id_location_unique');
            $table->unique('location');
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->dropConstrainedForeignId('site_id');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique('settings_site_id_unique');
            $table->dropConstrainedForeignId('site_id');
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropUnique('pages_site_id_slug_unique');
            $table->unique('slug');
            $table->dropConstrainedForeignId('site_id');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropUnique('posts_site_id_slug_unique');
            $table->unique('slug');
            $table->dropConstrainedForeignId('site_id');
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->dropUnique('tags_site_id_slug_unique');
            $table->unique('slug');
            $table->dropConstrainedForeignId('site_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_site_id_slug_unique');
            $table->unique('slug');
            $table->dropConstrainedForeignId('site_id');
        });
    }
};
