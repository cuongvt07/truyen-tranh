{{-- Head ads (display_mode = head): chèn script/HTML nguyên văn vào trong <head>.
     Dùng cho loader/verify/analytics... Lọc theo trang + lịch + ẩn-VIP qua ads_for(). --}}
@foreach((ads_for()['head'] ?? collect()) as $__headAd)
    @if($__headAd->script_code)
        {!! $__headAd->script_code !!}
    @endif
@endforeach
