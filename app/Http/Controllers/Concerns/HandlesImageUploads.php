<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

trait HandlesImageUploads
{
    /**
     * Tên file an toàn: slug phần tên + giữ đuôi mở rộng.
     * Vd "My Photo (1).JPG" -> "my-photo-1.jpg"
     */
    protected function safeUploadName(string $original): string
    {
        $ext  = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $base = Str::slug(pathinfo($original, PATHINFO_FILENAME)) ?: 'file';

        return $ext !== '' ? "{$base}.{$ext}" : $base;
    }

    /**
     * Lưu ảnh vào Laravel public storage (storage/app/public/$dir) và trả URL "/storage/...".
     * Nhờ symlink `public/storage`, nginx phục vụ được file dù app ghi ở filesystem khác.
     *
     * @param  string  $dir  thư mục trong public disk, vd "images/articles"
     * @return string  vd "/storage/images/articles/1700000000-ten-anh.jpg"
     */
    protected function storePublicImage(UploadedFile $file, string $dir): string
    {
        $fileName = time() . '-' . $this->safeUploadName($file->getClientOriginalName());
        $path = $file->storeAs($dir, $fileName, 'public');

        return '/storage/' . $path;
    }
}
