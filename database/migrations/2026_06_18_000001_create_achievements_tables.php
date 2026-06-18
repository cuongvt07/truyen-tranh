<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->text('description')->nullable();
            $table->text('description_en')->nullable();
            $table->string('category')->default('reading'); // reading, social, support
            $table->string('metric')->default('chapters_read'); // chapters_read, comments_posted, bookmarks, deposit_total
            $table->unsignedInteger('target')->default(1);
            $table->unsignedInteger('reward_credits')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('user_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('achievement_id')->constrained()->cascadeOnDelete();
            $table->timestamp('unlocked_at')->useCurrent();
            $table->unique(['user_id', 'achievement_id']);
        });

        // Seed mặc định
        $now = now();
        DB::table('achievements')->insert([
            // Reading
            ['key'=>'first_chapter',    'name'=>'Bước đầu đọc truyện', 'name_en'=>'First Chapter',     'description'=>'Đọc 1 chương đầu tiên.',            'description_en'=>'Read your first chapter.',            'category'=>'reading', 'metric'=>'chapters_read',    'target'=>1,    'reward_credits'=>1,  'sort_order'=>1,  'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'reader_5',         'name'=>'Đọc chăm chỉ',        'name_en'=>'Getting Started',   'description'=>'Đọc 5 chương.',                       'description_en'=>'Read 5 chapters.',                    'category'=>'reading', 'metric'=>'chapters_read',    'target'=>5,    'reward_credits'=>2,  'sort_order'=>2,  'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'reader_50',        'name'=>'Mọt sách',             'name_en'=>'Bookworm',          'description'=>'Đọc 50 chương.',                      'description_en'=>'Read 50 chapters.',                   'category'=>'reading', 'metric'=>'chapters_read',    'target'=>50,   'reward_credits'=>5,  'sort_order'=>3,  'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'reader_200',       'name'=>'Người đọc tích cực',   'name_en'=>'Avid Reader',       'description'=>'Đọc 200 chương.',                     'description_en'=>'Read 200 chapters.',                  'category'=>'reading', 'metric'=>'chapters_read',    'target'=>200,  'reward_credits'=>10, 'sort_order'=>4,  'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'reader_1000',      'name'=>'Người đọc không ngừng','name_en'=>'Marathon Reader',   'description'=>'Đọc 1000 chương.',                    'description_en'=>'Read 1000 chapters.',                 'category'=>'reading', 'metric'=>'chapters_read',    'target'=>1000, 'reward_credits'=>30, 'sort_order'=>5,  'created_at'=>$now,'updated_at'=>$now],

            // Social – comments
            ['key'=>'first_comment',    'name'=>'Bình luận đầu tiên',   'name_en'=>'First Comment',     'description'=>'Đăng bình luận đầu tiên.',            'description_en'=>'Post your first comment.',            'category'=>'social',   'metric'=>'comments_posted',  'target'=>1,    'reward_credits'=>1,  'sort_order'=>10, 'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'commenter_10',     'name'=>'Tích cực bình luận',   'name_en'=>'Active Commenter',  'description'=>'Đăng 10 bình luận.',                  'description_en'=>'Post 10 comments.',                   'category'=>'social',   'metric'=>'comments_posted',  'target'=>10,   'reward_credits'=>3,  'sort_order'=>11, 'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'commenter_50',     'name'=>'Trụ cột cộng đồng',    'name_en'=>'Community Pillar',  'description'=>'Đăng 50 bình luận.',                  'description_en'=>'Post 50 comments.',                   'category'=>'social',   'metric'=>'comments_posted',  'target'=>50,   'reward_credits'=>10, 'sort_order'=>12, 'created_at'=>$now,'updated_at'=>$now],

            // Social – bookmarks
            ['key'=>'first_bookmark',   'name'=>'Đánh dấu truyện',     'name_en'=>'First Bookmark',    'description'=>'Theo dõi 1 truyện.',                  'description_en'=>'Follow your first story.',            'category'=>'social',   'metric'=>'bookmarks',        'target'=>1,    'reward_credits'=>1,  'sort_order'=>15, 'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'bookmark_10',      'name'=>'Tủ sách riêng',        'name_en'=>'Bookshelf',         'description'=>'Theo dõi 10 truyện.',                 'description_en'=>'Follow 10 stories.',                  'category'=>'social',   'metric'=>'bookmarks',        'target'=>10,   'reward_credits'=>3,  'sort_order'=>16, 'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'bookmark_50',      'name'=>'Thư viện cá nhân',     'name_en'=>'Personal Library',  'description'=>'Theo dõi 50 truyện.',                 'description_en'=>'Follow 50 stories.',                  'category'=>'social',   'metric'=>'bookmarks',        'target'=>50,   'reward_credits'=>10, 'sort_order'=>17, 'created_at'=>$now,'updated_at'=>$now],

            // Support – deposit
            ['key'=>'first_deposit',    'name'=>'Nhà đầu tư mới',       'name_en'=>'New Investor',      'description'=>'Nạp xu lần đầu tiên.',                'description_en'=>'Make your first deposit.',            'category'=>'support',  'metric'=>'deposit_count',    'target'=>1,    'reward_credits'=>20, 'sort_order'=>20, 'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'deposit_3',        'name'=>'Người ủng hộ',          'name_en'=>'Supporter',         'description'=>'Nạp xu 3 lần.',                       'description_en'=>'Make 3 deposits.',                    'category'=>'support',  'metric'=>'deposit_count',    'target'=>3,    'reward_credits'=>30, 'sort_order'=>21, 'created_at'=>$now,'updated_at'=>$now],
            ['key'=>'big_spender',      'name'=>'Người bảo trợ lớn',    'name_en'=>'Big Spender',       'description'=>'Tổng nạp ≥ 500.000 VNĐ.',            'description_en'=>'Total deposit ≥ 500,000 VND.',        'category'=>'support',  'metric'=>'deposit_total',    'target'=>500000,'reward_credits'=>100,'sort_order'=>22, 'created_at'=>$now,'updated_at'=>$now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('user_achievements');
        Schema::dropIfExists('achievements');
    }
};
