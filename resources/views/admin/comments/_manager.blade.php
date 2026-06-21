@php
    $filterOptions = $filterOptions ?? collect();
    $filterName = $filterName ?? null;
@endphp

<div class="content">
    <div class="container-fluid">
        @include('admin.partials.flash')

        <div class="card card-outline card-primary mb-3">
            <div class="card-header">
                <h3 class="card-title">{{ $title }}</h3>
            </div>
            <div class="card-body py-2">
                <form method="GET" class="form-row align-items-center">
                    <div class="col-md-5 mb-2">
                        <div class="input-group input-group-sm">
                            <input type="text" name="q" class="form-control"
                                   placeholder="{{ __('messages.admin_comments.search_placeholder') }}"
                                   value="{{ request('q') }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit" title="{{ __('messages.common.filter') }}">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @if($filterName)
                        <div class="col-md-5 mb-2">
                            <select name="{{ $filterName }}" class="form-control form-control-sm" onchange="this.form.submit()">
                                <option value="">{{ __('messages.admin_comments.all_sources') }}</option>
                                @foreach($filterOptions as $option)
                                    @php
                                        $optionValue = is_object($option) ? $option->id : $option;
                                        $optionLabel = is_object($option)
                                            ? ($option->title_en ?? $option->title ?? $optionValue)
                                            : $option;
                                    @endphp
                                    <option value="{{ $optionValue }}" @selected((string) request($filterName) === (string) $optionValue)>
                                        {{ \Illuminate\Support\Str::limit($optionLabel, 70) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-md-2 mb-2">
                        <a href="{{ route($routePrefix . '.index') }}" class="btn btn-sm btn-outline-secondary btn-block">
                            <i class="fas fa-times"></i> {{ __('messages.common.reset') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <form method="POST" action="{{ route($routePrefix . '.bulk_destroy') }}" id="bulk-comment-form"
              onsubmit="return confirm(@js(__('messages.admin_comments.confirm_bulk_delete')))">
            @csrf
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span class="text-muted small">
                        {{ trans_choice('messages.admin_comments.total', $comments->total(), ['count' => number_format($comments->total())]) }}
                    </span>
                    <button type="submit" class="btn btn-sm btn-danger" id="bulk-comment-delete" hidden>
                        <i class="fas fa-trash"></i>
                        {{ __('messages.admin_comments.delete_selected') }}
                        (<span id="selected-comment-count">0</span>)
                    </button>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="36"><input type="checkbox" id="check-all-comments"></th>
                                <th>{{ __('messages.admin_comments.content') }}</th>
                                <th width="150">{{ __('messages.admin_comments.author') }}</th>
                                <th width="220">{{ __('messages.admin_comments.source') }}</th>
                                <th width="105">{{ __('messages.admin_comments.created') }}</th>
                                <th width="190" class="text-center">{{ __('messages.admin_comments.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($comments as $comment)
                                @php
                                    $source = match ($commentType) {
                                        'story' => optional($comment->article)->title,
                                        'forum' => optional($comment->post)->title_en,
                                        'faq' => optional($comment->article)->title_en,
                                        default => optional($comment->page)->title_en,
                                    };
                                @endphp
                                <tr class="{{ $comment->is_hidden ? 'table-secondary' : '' }}">
                                    <td><input type="checkbox" name="ids[]" value="{{ $comment->id }}" class="comment-check"></td>
                                    <td>
                                        @if($comment->parent_id)
                                            <span class="badge badge-light mr-1"><i class="fas fa-reply"></i> {{ __('messages.admin_comments.reply_badge') }}</span>
                                        @endif
                                        @if($comment->is_hidden)
                                            <span class="badge badge-secondary mr-1"><i class="fas fa-eye-slash"></i> {{ __('messages.admin_comments.hidden') }}</span>
                                        @endif
                                        {{ \Illuminate\Support\Str::limit($comment->content, 160) }}
                                    </td>
                                    <td class="text-muted small">
                                        {{ optional($comment->user)->name ?? optional($comment->user)->username ?? __('messages.comments.anonymous') }}
                                    </td>
                                    <td class="small">{{ \Illuminate\Support\Str::limit($source ?: '-', 55) }}</td>
                                    <td class="text-muted small">{{ optional($comment->created_at)->format('d/m/Y H:i') }}</td>
                                    <td class="text-center text-nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-toggle="modal" data-target="#reply-comment-{{ $comment->id }}"
                                                title="{{ __('messages.admin_comments.reply') }}">
                                            <i class="fas fa-reply"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-info"
                                                data-toggle="modal" data-target="#edit-comment-{{ $comment->id }}"
                                                title="{{ __('messages.common.edit') }}">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                                onclick="document.getElementById('toggle-comment-{{ $comment->id }}').submit()"
                                                title="{{ $comment->is_hidden ? __('messages.admin_comments.show') : __('messages.admin_comments.hide') }}">
                                            <i class="fas {{ $comment->is_hidden ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="if(confirm(@js(__('messages.admin_comments.confirm_delete')))){document.getElementById('delete-comment-{{ $comment->id }}').submit()}"
                                                title="{{ __('messages.common.delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-comments fa-3x mb-3 d-block"></i>
                                        {{ __('messages.admin_comments.empty') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($comments->hasPages())
                    <div class="card-footer">{{ $comments->links() }}</div>
                @endif
            </div>
        </form>

        @foreach($comments as $comment)
            <form id="toggle-comment-{{ $comment->id }}" method="POST"
                  action="{{ route($routePrefix . '.toggle_hidden', $comment) }}" class="d-none">
                @csrf @method('PATCH')
            </form>
            <form id="delete-comment-{{ $comment->id }}" method="POST"
                  action="{{ route($routePrefix . '.destroy', $comment) }}" class="d-none">
                @csrf @method('DELETE')
            </form>

            <div class="modal fade" id="edit-comment-{{ $comment->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="POST" action="{{ route($routePrefix . '.update', $comment) }}" class="modal-content">
                        @csrf @method('PATCH')
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('messages.admin_comments.edit_title') }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('messages.common.cancel') }}">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <textarea name="content" rows="6" maxlength="5000" required class="form-control">{{ $comment->content }}</textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('messages.common.cancel') }}</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ __('messages.common.save') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal fade" id="reply-comment-{{ $comment->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="POST" action="{{ route($routePrefix . '.reply', $comment) }}" class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('messages.admin_comments.reply_title') }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('messages.common.cancel') }}">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p class="small text-muted">{{ \Illuminate\Support\Str::limit($comment->content, 180) }}</p>
                            <textarea name="content" rows="5" maxlength="5000" required class="form-control"
                                      placeholder="{{ __('messages.admin_comments.reply_placeholder') }}"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('messages.common.cancel') }}</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-reply"></i> {{ __('messages.admin_comments.reply') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var checkAll = document.getElementById('check-all-comments');
    var deleteButton = document.getElementById('bulk-comment-delete');
    var count = document.getElementById('selected-comment-count');

    function refreshSelection() {
        var selected = document.querySelectorAll('.comment-check:checked').length;
        count.textContent = selected;
        deleteButton.hidden = selected === 0;
    }

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            document.querySelectorAll('.comment-check').forEach(function (checkbox) {
                checkbox.checked = checkAll.checked;
            });
            refreshSelection();
        });
    }

    document.querySelectorAll('.comment-check').forEach(function (checkbox) {
        checkbox.addEventListener('change', refreshSelection);
    });
});
</script>
