<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Ẩn các chương HẸN GIỜ (published_at ở tương lai) khỏi mọi truy vấn public.
 * Fail-closed: chương chỉ hiện khi published_at NULL (đăng ngay) hoặc đã tới giờ.
 *
 * Trang quản trị/poster muốn thấy chương hẹn giờ thì gọi
 * ->withoutGlobalScope(\App\Scopes\PublishedChapterScope::class).
 */
class PublishedChapterScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where(function (Builder $q) use ($model) {
            $col = $model->getTable() . '.published_at';
            $q->whereNull($col)->orWhere($col, '<=', now());
        });
    }
}
