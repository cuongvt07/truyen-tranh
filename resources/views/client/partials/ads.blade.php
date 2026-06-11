@php
    $__adGroups = ads_for();
    $__banners  = ($__adGroups['banner'] ?? collect())->filter(fn ($a) => $a->image);
    $__popup    = ($__adGroups['popup'] ?? collect())->first(fn ($a) => $a->image || $a->link);
@endphp

@if($__banners->isNotEmpty() || $__popup)
<style>
    .site-ad-banner{position:relative;display:inline-block;line-height:0}
    .site-ad-banner img{max-width:100%;height:auto;border-radius:4px}
    .site-ad-banner .site-ad-close{position:absolute;top:-8px;right:-8px;width:22px;height:22px;border-radius:50%;
        background:rgba(0,0,0,.65);color:#fff;border:none;font-size:13px;line-height:22px;cursor:pointer;z-index:2}
    .site-ad-fixed{position:fixed;z-index:9000;text-align:center}
    .site-ad-fixed.top{top:0;left:0;right:0;background:rgba(0,0,0,.04);padding:6px 0}
    .site-ad-fixed.bottom{bottom:0;left:0;right:0;background:rgba(0,0,0,.04);padding:6px 0}
    .site-ad-fixed.float_left{left:10px;bottom:10px;max-width:200px}
    .site-ad-fixed.float_right{right:10px;bottom:10px;max-width:200px}
    .site-ad-inline{text-align:center;margin:20px auto}

    .site-ad-popup-overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;display:none;
        align-items:center;justify-content:center;padding:16px}
    .site-ad-popup-box{background:#fff;border-radius:10px;max-width:min(560px,94vw);max-height:90vh;overflow:auto;
        position:relative;box-shadow:0 10px 40px rgba(0,0,0,.3)}
    .site-ad-popup-box img{display:block;max-width:100%;height:auto;border-radius:10px 10px 0 0}
    .site-ad-popup-close{position:absolute;top:8px;right:10px;background:rgba(0,0,0,.55);color:#fff;border:none;
        width:30px;height:30px;border-radius:50%;font-size:16px;cursor:pointer;z-index:2}
    .site-ad-popup-close:disabled{opacity:.5;cursor:not-allowed}
    .site-ad-popup-name{padding:12px 16px;text-align:center;font-weight:600;color:#333}
</style>

{{-- Banner cố định / nổi --}}
@foreach($__banners as $ad)
    @php $place = $ad->placement ?: 'in_content'; $fixed = in_array($place, ['top','bottom','float_left','float_right']); @endphp
    <div class="site-ad {{ $fixed ? 'site-ad-fixed '.$place : 'site-ad-inline' }}" data-ad-id="{{ $ad->id }}">
        <span class="site-ad-banner">
            <button type="button" class="site-ad-close" onclick="this.closest('.site-ad').remove()">×</button>
            @if($ad->link)<a href="{{ $ad->link }}" target="_blank" rel="nofollow noopener">@endif
                <img src="{{ $ad->image }}" alt="{{ $ad->name }}">
            @if($ad->link)</a>@endif
        </span>
    </div>
@endforeach

{{-- Popup --}}
@if($__popup)
<div class="site-ad-popup-overlay" id="siteAdPopup"
     data-ad-id="{{ $__popup->id }}"
     data-frequency="{{ $__popup->frequency }}"
     data-frequency-value="{{ $__popup->frequency_value }}"
     data-delay="{{ $__popup->delay_seconds }}"
     data-after-click="{{ $__popup->after_click }}"
     data-cooldown="{{ $__popup->cooldown_seconds }}">
    <div class="site-ad-popup-box">
        <button type="button" class="site-ad-popup-close" id="siteAdPopupClose">×</button>
        @if($__popup->link)<a href="{{ $__popup->link }}" target="_blank" rel="nofollow noopener" id="siteAdPopupLink">@endif
            @if($__popup->image)<img src="{{ $__popup->image }}" alt="{{ $__popup->name }}">@endif
        @if($__popup->link)</a>@endif
        <div class="site-ad-popup-name">{{ $__popup->name }}</div>
    </div>
</div>
@endif

<script>
(function () {
    function now() { return new Date().getTime(); }

    // ===== Chặn sau khi đã click (link đã chạy) =====
    // stop_session: ẩn hết trong phiên. cooldown: chờ N giây mới chạy lại.
    function afterClickAllows(ad) {
        if (ad.after_click === 'stop_session') {
            return !sessionStorage.getItem('ad_clicked_s_' + ad.id);
        }
        if (ad.after_click === 'cooldown') {
            var until = parseInt(localStorage.getItem('ad_cooldown_until_' + ad.id) || '0', 10) || 0;
            return now() >= until;
        }
        return true;
    }
    function markClicked(ad) {
        if (ad.after_click === 'stop_session') {
            sessionStorage.setItem('ad_clicked_s_' + ad.id, '1');
        } else if (ad.after_click === 'cooldown') {
            var sec = parseInt(ad.cooldown, 10) || 0;
            localStorage.setItem('ad_cooldown_until_' + ad.id, String(now() + sec * 1000));
        }
    }

    // ===== Tần suất hiển thị (local/sessionStorage) =====
    function adAllowed(ad) {
        if (!afterClickAllows(ad)) return false;        // ưu tiên chặn sau-click trước
        if (ad.frequency === 'every_load') return true;
        if (ad.frequency === 'once_session') {
            return !sessionStorage.getItem('ad_seen_' + ad.id);
        }
        if (ad.frequency === 'every_n_views') {
            var k = 'ad_views_' + ad.id;
            var n = (parseInt(localStorage.getItem(k) || '0', 10) || 0) + 1;
            localStorage.setItem(k, n);
            var step = parseInt(ad.frequency_value, 10) || 1;
            return (n % step) === 0;
        }
        return true;
    }
    function adMarkSeen(ad) {
        if (ad.frequency === 'once_session') sessionStorage.setItem('ad_seen_' + ad.id, '1');
    }

    // ===== Popup =====
    var popup = document.getElementById('siteAdPopup');
    if (popup) {
        var ad = {
            id: popup.dataset.adId,
            frequency: popup.dataset.frequency,
            frequency_value: popup.dataset.frequencyValue,
            after_click: popup.dataset.afterClick,
            cooldown: popup.dataset.cooldown
        };
        if (adAllowed(ad)) {
            popup.style.display = 'flex';
            adMarkSeen(ad);
            // ghi nhận khi user bấm vào link quảng cáo
            var popupLink = document.getElementById('siteAdPopupLink');
            if (popupLink) popupLink.addEventListener('click', function () { markClicked(ad); });

            var closeBtn = document.getElementById('siteAdPopupClose');
            var delay = parseInt(popup.dataset.delay, 10) || 0;
            function closePopup() { popup.style.display = 'none'; }
            if (delay > 0) {
                closeBtn.disabled = true;
                var left = delay;
                closeBtn.textContent = left;
                var timer = setInterval(function () {
                    left--;
                    if (left <= 0) { clearInterval(timer); closeBtn.disabled = false; closeBtn.textContent = '×'; }
                    else { closeBtn.textContent = left; }
                }, 1000);
            }
            closeBtn.addEventListener('click', function () { if (!closeBtn.disabled) closePopup(); });
            popup.addEventListener('click', function (e) { if (e.target === popup && !closeBtn.disabled) closePopup(); });
        }
    }
})();
</script>
@endif

{{-- Click bất kỳ đâu (partial tái dùng — chạy cả khi không có banner/popup) --}}
@include('client.partials.ad-click-anywhere')
