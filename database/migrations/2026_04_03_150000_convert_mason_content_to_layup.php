<?php

use App\Support\PageBuilderContent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->convertTable('pages');
        $this->convertTable('posts');
    }

    public function down(): void
    {
        //
    }

    protected function convertTable(string $table): void
    {
        DB::table($table)
            ->select(['id', 'content'])
            ->whereNotNull('content')
            ->orderBy('id')
            ->lazyById()
            ->each(function (object $record) use ($table): void {
                $normalized = PageBuilderContent::encode($record->content);

                if ($normalized === null || $normalized === $record->content) {
                    return;
                }

                DB::table($table)
                    ->where('id', $record->id)
                    ->update([
                        'content' => $normalized,
                    ]);
            });
    }
};
