<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SeedDailyComments extends Command
{
    protected $signature = 'comments:seed-daily {--count=10}';
    protected $description = 'Tạo N bình luận/ngày: N người (role user) khác nhau, N comment khác nhau, N truyện KHÁC NHAU.';

    public function handle(): int
    {
        $count = max(1, (int) $this->option('count'));

        $pool = $this->loadPool();
        if (empty($pool)) {
            $this->error('Pool comment rỗng (resources/data/seed_comments.json).');
            return self::FAILURE;
        }

        // N truyện KHÁC NHAU (đã duyệt -> ApprovedArticleScope tự lọc status).
        $articles = Article::query()->inRandomOrder()->limit($count)->pluck('id')->all();
        if (count($articles) < $count) {
            $this->warn('Chỉ có '.count($articles).' truyện -> tạo bấy nhiêu comment.');
            $count = count($articles);
        }

        // N người dùng KHÁC NHAU — chỉ dùng tài khoản seed có email @example.
        $users = User::where('email', 'like', '%@example%')->inRandomOrder()->limit($count)->pluck('id')->all();
        if (empty($users)) {
            $this->error('Không có tài khoản email @example để gán comment.');
            return self::FAILURE;
        }

        shuffle($pool);
        $usedContent = [];          // comment đã dùng trong batch hôm nay (10 comment khác nhau)
        $poolIdx = 0;
        $now = Carbon::now();
        $startOfDay = Carbon::today();
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $articleId = $articles[$i];
            $userId = $users[$i % count($users)];   // mỗi truyện 1 người (cycle nếu thiếu user)

            // Chọn comment: khác các comment đã dùng hôm nay + chưa từng có trên CHÍNH truyện đó.
            $content = null;
            for ($scan = 0; $scan < count($pool); $scan++) {
                $cand = $pool[($poolIdx + $scan) % count($pool)];
                if (isset($usedContent[$cand])) {
                    continue;
                }
                $dupOnArticle = Comment::where('article_id', $articleId)->where('content', $cand)->exists();
                if ($dupOnArticle) {
                    continue;
                }
                $content = $cand;
                $poolIdx = ($poolIdx + $scan + 1) % count($pool);
                break;
            }
            if ($content === null) {
                continue; // hết comment khả dụng
            }
            $usedContent[$content] = true;

            // created_at rải ngẫu nhiên trong NGÀY hôm nay (00:00 -> bây giờ) cho tự nhiên.
            $span = max(1, $startOfDay->diffInSeconds($now));
            $createdAt = (clone $startOfDay)->addSeconds(random_int(0, $span));

            $rows[] = [
                'user_id'    => $userId,
                'article_id' => $articleId,
                'parent_id'  => null,
                'content'    => $content,
                'score'      => 0,
                'is_hidden'  => 0,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }

        if (empty($rows)) {
            $this->warn('Không tạo được comment nào (hết comment khả dụng).');
            return self::SUCCESS;
        }

        DB::table('comments')->insert($rows);
        $this->info('Đã tạo '.count($rows).' comment trên '.count($rows).' truyện khác nhau.');
        return self::SUCCESS;
    }

    /** @return array<int,string> */
    private function loadPool(): array
    {
        $path = resource_path('data/seed_comments.json');
        if (!is_file($path)) {
            return [];
        }
        $data = json_decode((string) file_get_contents($path), true);
        return is_array($data) ? array_values(array_filter($data, fn ($s) => is_string($s) && strlen(trim($s)) > 1)) : [];
    }
}
