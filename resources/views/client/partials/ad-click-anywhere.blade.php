@php
    $__clickAd = (ads_for()['click_anywhere'] ?? collect())->first(fn ($a) => $a->link);
@endphp
@if($__clickAd)
<script type="application/json" id="siteAdClickData">@php
    $__clickData = [
        'id' => $__clickAd->id,
        'link' => $__clickAd->link,
        'frequency' => $__clickAd->frequency,
        'frequency_value' => $__clickAd->frequency_value,
        'after_click' => $__clickAd->after_click,
        'cooldown' => $__clickAd->cooldown_seconds,
    ];
@endphp{!! json_encode($__clickData) !!}</script>
<script>
(function () {
    var el = document.getElementById('siteAdClickData');
    if (!el) return;
    var cad;
    try { cad = JSON.parse(el.textContent); } catch (e) { return; }
    if (!cad || !cad.link) return;

    function now() { return Date.now(); }

    // Chặn sau khi đã click
    function afterClickAllows() {
        if (cad.after_click === 'stop_session') return !sessionStorage.getItem('ad_clicked_s_' + cad.id);
        if (cad.after_click === 'cooldown') {
            var until = parseInt(localStorage.getItem('ad_cooldown_until_' + cad.id) || '0', 10) || 0;
            return now() >= until;
        }
        return true;
    }
    function markClicked() {
        if (cad.after_click === 'stop_session') sessionStorage.setItem('ad_clicked_s_' + cad.id, '1');
        else if (cad.after_click === 'cooldown') {
            var sec = parseInt(cad.cooldown, 10) || 0;
            localStorage.setItem('ad_cooldown_until_' + cad.id, String(now() + sec * 1000));
        }
    }
    // Tần suất hiển thị — kiểm 1 lần lúc tải trang (KHÔNG gồm cooldown sau-click)
    function frequencyAllowed() {
        if (cad.frequency === 'once_session') return !sessionStorage.getItem('ad_seen_' + cad.id);
        if (cad.frequency === 'every_n_views') {
            var k = 'ad_views_' + cad.id;
            var n = (parseInt(localStorage.getItem(k) || '0', 10) || 0) + 1;
            localStorage.setItem(k, n);
            var step = parseInt(cad.frequency_value, 10) || 1;
            return (n % step) === 0;
        }
        return true; // every_load / mặc định
    }
    function markSeen() {
        if (cad.frequency === 'once_session') sessionStorage.setItem('ad_seen_' + cad.id, '1');
    }

    // Trang này không cho hiện theo tần suất → thôi.
    if (!frequencyAllowed()) return;
    markSeen();

    // Mở tab bằng anchor click — đáng tin cậy hơn window.open(..,'noopener') (ít bị popup-blocker chặn)
    function openAd(url) {
        var a = document.createElement('a');
        a.href = url;
        a.target = '_blank';
        a.rel = 'noopener nofollow';
        document.body.appendChild(a);
        a.click();
        a.remove();
    }

    var firedOnce = false; // chỉ dùng cho mode 'none' (1 lần / trang)
    function handler(e) {
        if (cad.after_click === 'none' && firedOnce) return;
        // Cooldown / stop_session: kiểm MỖI click → qua đủ interval mới bắn lại
        if (!afterClickAllows()) return;
        // bỏ qua khi click vào phần tử tương tác (link/nút/ô nhập...) để không cướp hành vi gốc
        if (e.target.closest && e.target.closest('a, button, input, select, textarea, label, [role="button"]')) return;

        markClicked();          // đặt mốc cooldown mới → lần lặp kế tiếp cách 'cooldown' giây
        openAd(cad.link);
        firedOnce = true;

        // 'none' = 1 lần/trang; 'cooldown'/'stop_session' giữ listener để còn lặp lại
        if (cad.after_click === 'none') document.removeEventListener('click', handler, true);
    }
    document.addEventListener('click', handler, true);
})();
</script>
@endif
