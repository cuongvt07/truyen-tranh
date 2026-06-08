@php $href = $mi->href; $label = $mi->display_label; $icon = $mi->icon; $kids = $mi->activeChildren ?? collect(); @endphp
@if($kids->count())
    {{-- Mục có con -> dropdown (tippy) --}}
    <li class="header-nav__list">
        <div class="header-btn tippy-submenu" data-submenu="submenu-{{ $mi->id }}">@if($icon)<i class="{{ $icon }}"></i> @endif{{ $label }} <i class="fa fa-caret-down"></i></div>
    </li>
@elseif($href === '#browse')
    <li class="header-nav__list">
        <div class="header-btn header-browse tippy-browse">@if($icon)<i class="{{ $icon }}"></i> @endif{{ $label }} <i class="fa fa-caret-down"></i></div>
    </li>
@elseif($href === '#search')
    <li>
        <a href="#" id="open-live-search" class="header-btn no-link open-close" p-target="fullscreen-search">@if($icon)<i class="{{ $icon }}"></i> @endif{{ $label }}</a>
    </li>
@else
    <li>
        <a href="{{ $href }}" class="header-btn no-link"@if($mi->target === '_blank') target="_blank"@endif>@if($icon)<i class="{{ $icon }}"></i> @endif{{ $label }}</a>
    </li>
@endif
