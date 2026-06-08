@php $href = $mi->href; $label = $mi->display_label; $icon = $mi->icon; $kids = $mi->activeChildren ?? collect(); @endphp
@if($kids->count())
    {{-- Mục có con: hiện nhãn + danh sách con thụt vào --}}
    <li>
        <span class="mobile-parent">@if($icon)<i class="{{ $icon }}"></i> @endif{{ $label }}</span>
        <ul class="mobile-submenu" style="padding-left:18px">
            @foreach($kids as $c)
                <li><a href="{{ $c->href }}"@if($c->target === '_blank') target="_blank"@endif>@if($c->icon)<i class="{{ $c->icon }}"></i> @endif{{ $c->display_label }}</a></li>
            @endforeach
        </ul>
    </li>
@elseif($href === '#browse')
    <li>
        <div class="tippy-browse"><span>@if($icon)<i class="{{ $icon }}"></i> @endif{{ $label }}</span> <i class="fa fa-caret-down"></i></div>
    </li>
@elseif($href === '#search')
    <li>
        <a href="#" class="open-close" p-target="fullscreen-search">@if($icon)<i class="{{ $icon }}"></i> @endif{{ $label }}</a>
    </li>
@else
    <li>
        <a href="{{ $href }}"@if($mi->target === '_blank') target="_blank"@endif>@if($icon)<i class="{{ $icon }}"></i> @endif{{ $label }}</a>
    </li>
@endif
