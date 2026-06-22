<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('characters', 'slug')) {
            Schema::table('characters', function (Blueprint $table) {
                $table->string('slug')->nullable()->unique()->after('name');
            });
        }

        // Backfill slug cho nhân vật cũ (slug ổn định, không đổi khi rename về sau).
        $used = [];
        foreach (DB::table('characters')->select('id', 'name', 'slug')->orderBy('id')->get() as $c) {
            if (!empty($c->slug)) {
                $used[$c->slug] = true;
                continue;
            }
            $base = Str::slug($c->name) ?: 'character';
            $slug = $base;
            $i = 2;
            while (isset($used[$slug]) || DB::table('characters')->where('slug', $slug)->exists()) {
                $slug = $base . '-' . $i++;
            }
            $used[$slug] = true;
            DB::table('characters')->where('id', $c->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('characters', 'slug')) {
            Schema::table('characters', function (Blueprint $table) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            });
        }
    }
};
