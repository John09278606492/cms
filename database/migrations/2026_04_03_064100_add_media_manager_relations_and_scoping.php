<?php

use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Slimani\MediaManager\Models\File as ManagedFile;
use Slimani\MediaManager\Models\Folder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('media_folders', 'site_id')) {
            Schema::table('media_folders', function (Blueprint $table): void {
                $table->foreignId('site_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('sites')
                    ->cascadeOnDelete();
            });
        }

        if (! Schema::hasColumn('media_files', 'site_id')) {
            Schema::table('media_files', function (Blueprint $table): void {
                $table->foreignId('site_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('sites')
                    ->cascadeOnDelete();
            });
        }

        if (! Schema::hasColumn('posts', 'featured_image_id')) {
            Schema::table('posts', function (Blueprint $table): void {
                $table->foreignId('featured_image_id')
                    ->nullable()
                    ->after('is_featured')
                    ->constrained('media_files')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('pages', 'featured_image_id')) {
            Schema::table('pages', function (Blueprint $table): void {
                $table->foreignId('featured_image_id')
                    ->nullable()
                    ->after('sort_order')
                    ->constrained('media_files')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('settings', 'site_logo_id')) {
            Schema::table('settings', function (Blueprint $table): void {
                $table->foreignId('site_logo_id')
                    ->nullable()
                    ->after('site_id')
                    ->constrained('media_files')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('settings', 'site_favicon_id')) {
            Schema::table('settings', function (Blueprint $table): void {
                $table->foreignId('site_favicon_id')
                    ->nullable()
                    ->after('site_phone')
                    ->constrained('media_files')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('settings', 'default_og_image_id')) {
            Schema::table('settings', function (Blueprint $table): void {
                $table->foreignId('default_og_image_id')
                    ->nullable()
                    ->after('site_favicon_id')
                    ->constrained('media_files')
                    ->nullOnDelete();
            });
        }

        $this->backfillCollection(
            modelClass: Post::class,
            relationColumn: 'featured_image_id',
            legacyCollection: 'featured-images',
            folderPath: 'Posts/Featured Images',
            uploadedByColumn: 'user_id',
        );

        $this->backfillCollection(
            modelClass: Page::class,
            relationColumn: 'featured_image_id',
            legacyCollection: 'featured-images',
            folderPath: 'Pages/Featured Images',
            uploadedByColumn: 'user_id',
        );

        $this->backfillCollection(
            modelClass: Setting::class,
            relationColumn: 'site_logo_id',
            legacyCollection: 'site-logo',
            folderPath: 'Brand/Logo',
        );

        $this->backfillCollection(
            modelClass: Setting::class,
            relationColumn: 'site_favicon_id',
            legacyCollection: 'site-favicon',
            folderPath: 'Brand/Favicon',
        );

        $this->backfillCollection(
            modelClass: Setting::class,
            relationColumn: 'default_og_image_id',
            legacyCollection: 'default-og-image',
            folderPath: 'Brand/Sharing',
        );
    }

    public function down(): void
    {
        if (Schema::hasColumn('settings', 'default_og_image_id')) {
            Schema::table('settings', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('default_og_image_id');
            });
        }

        if (Schema::hasColumn('settings', 'site_favicon_id')) {
            Schema::table('settings', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('site_favicon_id');
            });
        }

        if (Schema::hasColumn('settings', 'site_logo_id')) {
            Schema::table('settings', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('site_logo_id');
            });
        }

        if (Schema::hasColumn('pages', 'featured_image_id')) {
            Schema::table('pages', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('featured_image_id');
            });
        }

        if (Schema::hasColumn('posts', 'featured_image_id')) {
            Schema::table('posts', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('featured_image_id');
            });
        }

        if (Schema::hasColumn('media_files', 'site_id')) {
            Schema::table('media_files', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('site_id');
            });
        }

        if (Schema::hasColumn('media_folders', 'site_id')) {
            Schema::table('media_folders', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('site_id');
            });
        }
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model&\Spatie\MediaLibrary\HasMedia>  $modelClass
     */
    protected function backfillCollection(
        string $modelClass,
        string $relationColumn,
        string $legacyCollection,
        string $folderPath,
        ?string $uploadedByColumn = null,
    ): void {
        $modelClass::query()->chunkById(50, function ($records) use ($relationColumn, $legacyCollection, $folderPath, $uploadedByColumn): void {
            foreach ($records as $record) {
                if (filled($record->getAttribute($relationColumn))) {
                    continue;
                }

                $legacyMedia = $record->getFirstMedia($legacyCollection);

                if (! $legacyMedia instanceof Media) {
                    continue;
                }

                $managedFile = $this->copyLegacyMediaToManagedFile(
                    legacyMedia: $legacyMedia,
                    siteId: $record->getAttribute('site_id'),
                    folderPath: $folderPath,
                    uploadedByUserId: $uploadedByColumn ? $record->getAttribute($uploadedByColumn) : null,
                );

                if (! $managedFile instanceof ManagedFile) {
                    continue;
                }

                $record->forceFill([
                    $relationColumn => $managedFile->getKey(),
                ])->saveQuietly();
            }
        });
    }

    protected function copyLegacyMediaToManagedFile(
        Media $legacyMedia,
        ?int $siteId,
        string $folderPath,
        ?int $uploadedByUserId = null,
    ): ?ManagedFile {
        $managedFile = new ManagedFile([
            'uploaded_by_user_id' => $uploadedByUserId,
            'folder_id' => $this->ensureFolderPath($folderPath, $siteId),
            'name' => $legacyMedia->name ?: pathinfo($legacyMedia->file_name, PATHINFO_FILENAME),
            'caption' => $legacyMedia->getCustomProperty('caption'),
            'alt_text' => $legacyMedia->getCustomProperty('alt_text') ?: $legacyMedia->getCustomProperty('alt'),
            'size' => $legacyMedia->size,
            'extension' => $legacyMedia->extension,
            'mime_type' => $legacyMedia->mime_type,
            'width' => $legacyMedia->getCustomProperty('width'),
            'height' => $legacyMedia->getCustomProperty('height'),
        ]);

        $managedFile->site_id = $siteId;
        $managedFile->save();

        $newMedia = Media::create([
            'model_type' => ManagedFile::class,
            'model_id' => $managedFile->getKey(),
            'uuid' => (string) Str::uuid(),
            'collection_name' => 'default',
            'name' => $legacyMedia->name,
            'file_name' => $legacyMedia->file_name,
            'mime_type' => $legacyMedia->mime_type,
            'disk' => $legacyMedia->disk ?: 'public',
            'conversions_disk' => $legacyMedia->conversions_disk,
            'size' => $legacyMedia->size,
            'manipulations' => $legacyMedia->manipulations,
            'custom_properties' => $legacyMedia->custom_properties,
            'generated_conversions' => $legacyMedia->generated_conversions,
            'responsive_images' => $legacyMedia->responsive_images,
            'order_column' => $legacyMedia->order_column,
            'created_at' => $legacyMedia->created_at,
            'updated_at' => $legacyMedia->updated_at,
        ]);

        $this->copyMediaDirectory($legacyMedia, $newMedia, $legacyMedia->disk ?: 'public');

        if (filled($legacyMedia->conversions_disk) && $legacyMedia->conversions_disk !== $legacyMedia->disk) {
            $this->copyMediaDirectory($legacyMedia, $newMedia, $legacyMedia->conversions_disk);
        }

        $managedFile->update([
            'size' => $legacyMedia->size,
            'extension' => $legacyMedia->extension,
            'mime_type' => $legacyMedia->mime_type,
            'width' => $legacyMedia->getCustomProperty('width'),
            'height' => $legacyMedia->getCustomProperty('height'),
        ]);

        return $managedFile->fresh();
    }

    protected function ensureFolderPath(string $folderPath, ?int $siteId): ?int
    {
        $parentId = null;

        foreach (array_filter(explode('/', trim($folderPath, '/'))) as $segment) {
            $folder = Folder::query()
                ->where('site_id', $siteId)
                ->where('parent_id', $parentId)
                ->where('name', $segment)
                ->first();

            if (! $folder instanceof Folder) {
                $folder = new Folder([
                    'name' => $segment,
                    'parent_id' => $parentId,
                ]);
                $folder->site_id = $siteId;
                $folder->save();
            }

            $parentId = $folder->getKey();
        }

        return $parentId;
    }

    protected function copyMediaDirectory(Media $sourceMedia, Media $targetMedia, string $diskName): void
    {
        $disk = Storage::disk($diskName);
        $pathGenerator = PathGeneratorFactory::create($sourceMedia);
        $sourcePath = rtrim($pathGenerator->getPath($sourceMedia), '/');
        $targetPath = rtrim(PathGeneratorFactory::create($targetMedia)->getPath($targetMedia), '/');

        if (! $disk->directoryExists($sourcePath)) {
            return;
        }

        foreach ($disk->allFiles($sourcePath) as $sourceFile) {
            $relativePath = ltrim(Str::after($sourceFile, $sourcePath), '/');
            $targetFile = $targetPath . '/' . $relativePath;

            $disk->makeDirectory(dirname($targetFile));
            $disk->copy($sourceFile, $targetFile);
        }
    }
};
