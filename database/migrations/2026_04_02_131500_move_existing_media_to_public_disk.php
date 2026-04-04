<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('media')) {
            return;
        }

        $mediaItems = DB::table('media')
            ->where('disk', 'local')
            ->orWhere('conversions_disk', 'local')
            ->get(['id', 'disk', 'conversions_disk']);

        foreach ($mediaItems as $media) {
            $directory = (string) $media->id;

            if (! Storage::disk('local')->directoryExists($directory)) {
                continue;
            }

            foreach (Storage::disk('local')->allFiles($directory) as $path) {
                $stream = Storage::disk('local')->readStream($path);

                if ($stream === false) {
                    continue;
                }

                Storage::disk('public')->writeStream($path, $stream);

                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }

        DB::table('media')
            ->where('disk', 'local')
            ->update(['disk' => 'public']);

        DB::table('media')
            ->where('conversions_disk', 'local')
            ->update(['conversions_disk' => 'public']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('media')) {
            return;
        }

        $mediaItems = DB::table('media')
            ->where('disk', 'public')
            ->orWhere('conversions_disk', 'public')
            ->get(['id', 'disk', 'conversions_disk']);

        foreach ($mediaItems as $media) {
            $directory = (string) $media->id;

            if (! Storage::disk('public')->directoryExists($directory)) {
                continue;
            }

            foreach (Storage::disk('public')->allFiles($directory) as $path) {
                $stream = Storage::disk('public')->readStream($path);

                if ($stream === false) {
                    continue;
                }

                Storage::disk('local')->writeStream($path, $stream);

                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }

        DB::table('media')
            ->where('disk', 'public')
            ->update(['disk' => 'local']);

        DB::table('media')
            ->where('conversions_disk', 'public')
            ->update(['conversions_disk' => 'local']);
    }
};
