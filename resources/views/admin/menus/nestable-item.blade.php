<li class="dd-item" data-id="{{ $it->id }}">
    <div class="mi-wrap {{ $it->is_active ? '' : 'mi-inactive' }}">
        <span class="dd-handle"><i class="fas fa-grip-vertical"></i></span>
        <div class="mi-content">
            @if($it->icon)<i class="{{ $it->icon }}"></i>@endif
            <span class="mi-title">{{ $it->label }}</span>
            <code class="mi-url">{{ $it->url }}</code>
            @if($it->label_key)<span class="badge badge-info" title="{{ $it->label_key }}"><i class="fas fa-language"></i></span>@endif
            @unless($it->is_active)<span class="badge badge-secondary">Ẩn</span>@endunless
            <span class="mi-actions">
                <button type="button" class="btn btn-xs btn-outline-primary btn-edit-item"
                    data-action="{{ route('admin.menus.items.update', $it->id) }}"
                    data-label="{{ $it->label }}"
                    data-label-key="{{ $it->label_key }}"
                    data-url="{{ $it->url }}"
                    data-icon="{{ $it->icon }}"
                    data-target="{{ $it->target }}"
                    data-active="{{ $it->is_active ? 1 : 0 }}"><i class="fas fa-edit"></i></button>
                <form action="{{ route('admin.menus.items.destroy', $it->id) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Xoá mục này{{ $it->children->count() ? ' (kèm mục con)' : '' }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                </form>
            </span>
        </div>
    </div>
    @if($it->children->count())
        <ol class="dd-list">
            @foreach($it->children as $child)
                @include('admin.menus.nestable-item', ['it' => $child])
            @endforeach
        </ol>
    @endif
</li>
