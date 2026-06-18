@php
    $__clickAdsAll   = ads_for();
    $__clickAdsGroup = $__clickAdsAll['click_anywhere'] ?? collect();
    $__clickAd       = $__clickAdsGroup->first(fn ($a) => $a->link);
@endphp
{{-- DEBUG: server-side diagnosis (remove after confirmed working) --}}
<!-- [AD-CLICK] page_type={{ current_ad_page_type() ?? 'null' }} | is_vip={{ user_has_active_vip() ? '1' : '0' }} | click_anywhere_count={{ $__clickAdsGroup->count() }} | chosen_id={{ $__clickAd?->id ?? 'none' }} | chosen_link={{ $__clickAd?->link ?? 'none' }} -->
@if($__clickAd)
<script type="application/json" id="siteAdClickData">@php
    $__clickData = [
        'id'              => $__clickAd->id,
        'link'            => $__clickAd->link,
        'frequency'       => $__clickAd->frequency,
        'frequency_value' => $__clickAd->frequency_value,
        'after_click'     => $__clickAd->after_click,
        'cooldown'        => $__clickAd->cooldown_seconds,
    ];
@endphp{!! json_encode($__clickData) !!}</script>
<script>
(function () {
    var LOG = '[AD-CLICK]';
    var el = document.getElementById('siteAdClickData');
    if (!el) { console.warn(LOG, 'JSON element not found'); return; }
    var cad;
    try { cad = JSON.parse(el.textContent); } catch (e) { console.error(LOG, 'JSON parse error', e); return; }
    if (!cad || !cad.link) { console.warn(LOG, 'no link in config', cad); return; }

    console.log(LOG, 'config loaded', cad);

    function now() { return Date.now(); }

    function afterClickAllows() {
        if (cad.after_click === 'stop_session') {
            var blocked = !!sessionStorage.getItem('ad_clicked_s_' + cad.id);
            if (blocked) console.log(LOG, 'blocked: stop_session already clicked this session');
            return !blocked;
        }
        if (cad.after_click === 'cooldown') {
            var until = parseInt(localStorage.getItem('ad_cooldown_until_' + cad.id) || '0', 10) || 0;
            var remaining = Math.max(0, until - now());
            if (remaining > 0) {
                console.log(LOG, 'blocked: cooldown, ' + Math.ceil(remaining / 1000) + 's remaining');
                return false;
            }
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

    function frequencyAllowed() {
        if (cad.frequency === 'once_session') {
            var seen = !!sessionStorage.getItem('ad_seen_' + cad.id);
            if (seen) console.log(LOG, 'skipped: once_session already seen — clear sessionStorage to reset');
            return !seen;
        }
        if (cad.frequency === 'every_n_views') {
            var k = 'ad_views_' + cad.id;
            var n = (parseInt(localStorage.getItem(k) || '0', 10) || 0) + 1;
            localStorage.setItem(k, n);
            var step = parseInt(cad.frequency_value, 10) || 1;
            var ok = (n % step) === 0;
            console.log(LOG, 'every_n_views: view #' + n + ', step=' + step + ', fire=' + ok);
            return ok;
        }
        return true; // every_load
    }
    function markSeen() {
        if (cad.frequency === 'once_session') sessionStorage.setItem('ad_seen_' + cad.id, '1');
    }

    if (!frequencyAllowed()) {
        console.log(LOG, 'frequency check failed — ad listener NOT attached');
        return;
    }
    markSeen();
    console.log(LOG, 'frequency OK — click listener attached, waiting for first click...');

    function openAd(url) {
        console.log(LOG, 'opening', url);
        var w = window.open(url, '_blank', 'noopener,noreferrer');
        if (!w) console.warn(LOG, 'window.open() returned null — likely blocked by popup blocker!');
    }

    var firedOnce = false;
    function handler(e) {
        if (cad.after_click === 'none' && firedOnce) return;
        if (!afterClickAllows()) return;
        if (e.target.closest && e.target.closest('input, select, textarea')) {
            console.log(LOG, 'click ignored: input/select/textarea target');
            return;
        }
        console.log(LOG, 'click fired on', e.target.tagName, e.target.className || '');
        markClicked();
        openAd(cad.link);
        firedOnce = true;
        if (cad.after_click === 'none') document.removeEventListener('click', handler, true);
    }
    document.addEventListener('click', handler, true);
})();
</script>
@else
<!-- [AD-CLICK] no active click_anywhere ad found — check: is_active=1, link set, pages include current page, schedule valid -->
@endif
