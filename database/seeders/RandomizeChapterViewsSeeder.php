<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RandomizeChapterViewsSeeder extends Seeder
{
    private const MIN_VIEW = 100;
    private const MAX_VIEW = 350;

    public function run(): void
    {
        if (! Schema::hasTable('chapters') || ! Schema::hasColumn('chapters', 'view')) {
            if ($this->command) {
                $this->command->warn('Skipped: chapters.view column not found.');
            }
            return;
        }

        $total = DB::table('chapters')->count();

        if ($total === 0) {
            if ($this->command) {
                $this->command->info('Skipped: no chapters found.');
            }
            return;
        }

        DB::table('chapters')
            ->orderBy('id')
            ->select('id')
            ->chunkById(500, function ($chapters): void {
                foreach ($chapters as $chapter) {
                    DB::table('chapters')
                        ->where('id', $chapter->id)
                        ->update(['view' => random_int(self::MIN_VIEW, self::MAX_VIEW)]);
                }
            });

        $stats = DB::table('chapters')
            ->selectRaw('COUNT(*) as total, MIN(view) as min_view, MAX(view) as max_view')
            ->first();

        if ($this->command) {
            $this->command->info(sprintf(
                'Randomized chapter views: %d chapters, range %d-%d.',
                $stats->total,
                $stats->min_view,
                $stats->max_view
            ));
        }
    }
}
