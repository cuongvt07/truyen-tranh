@props([
    'name',                  // tên input file (vd 'avatar', 'photo', 'cover_image')
    'label' => '',
    'current' => null,       // URL ảnh hiện tại để preview (đã resolve, vd asset(...))
    'urlName' => null,       // tên ô URL ảnh ngoài (null = không có)
    'urlValue' => '',
    'accept' => 'image/jpeg,image/png,image/gif,image/webp',
    'maxMb' => 4,            // giới hạn dung lượng (MB)
    'hint' => null,
    'height' => 130,         // chiều cao khung preview (px)
    'circle' => false,       // bo tròn (avatar)
])
@php
    $uid = 'imgup_' . preg_replace('/[^a-z0-9]+/i', '_', $name) . '_' . substr(md5($name . $label), 0, 5);
    $has = !empty($current);
@endphp
<div class="imgup" data-imgup>
    @if($label !== '')<label class="imgup__label">{{ $label }}</label>@endif

    <label class="imgup__drop {{ $circle ? 'imgup__drop--circle' : '' }}" for="{{ $uid }}" style="--imgup-h:{{ $height }}px">
        <img class="imgup__img" src="{{ $current ?? '' }}" alt="" @unless($has) hidden @endunless data-imgup-preview>
        <span class="imgup__placeholder" @if($has) hidden @endif data-imgup-ph>
            <i class="fa fa-cloud-upload-alt"></i>
            <span>{{ __('messages.account.choose_file') }}</span>
        </span>
    </label>

    <input type="file" id="{{ $uid }}" name="{{ $name }}" accept="{{ $accept }}"
           data-imgup-input data-max-mb="{{ $maxMb }}" hidden>

    <div class="imgup__row">
        <span class="imgup__name" data-imgup-name></span>
        <button type="button" class="imgup__clear" data-imgup-clear @unless($has) hidden @endunless>✕</button>
    </div>
    <div class="imgup__err" data-imgup-err hidden></div>

    @if($urlName)
        <input type="text" name="{{ $urlName }}" value="{{ $urlValue }}" class="imgup__url"
               placeholder="{{ __('messages.account.or_paste_url') }}" data-imgup-url>
    @endif
    @if($hint)<div class="imgup__tip">{{ $hint }}</div>@endif
</div>

@once
<style>
.imgup { margin-bottom: 4px; }
.imgup__label { display:block; font-size:13px; margin-bottom:6px; color:var(--meta-color,#999); font-weight:500; }
.imgup__drop {
    display:flex; align-items:center; justify-content:center;
    width:100%; max-width:240px; height:var(--imgup-h,130px);
    border:1px dashed var(--input-border-color,#d7d7d7); border-radius:8px;
    background:var(--input-background-color,#fff); cursor:pointer; overflow:hidden;
    transition:border-color .15s, background .15s;
}
.imgup__drop:hover { border-color:var(--btn-primary-color,#0084d1); background:#f6f9fc; }
.imgup__drop--circle { width:130px; max-width:130px; border-radius:50%; }
.imgup__img { width:100%; height:100%; object-fit:cover; }
.imgup__placeholder { display:flex; flex-direction:column; align-items:center; gap:6px; color:var(--meta-color,#999); font-size:13px; text-align:center; padding:8px; }
.imgup__placeholder i { font-size:26px; opacity:.6; }
.imgup__row { display:flex; align-items:center; gap:10px; margin-top:6px; min-height:18px; }
.imgup__name { font-size:12px; color:var(--meta-color,#999); word-break:break-all; }
.imgup__clear { border:0; background:transparent; color:#e3342f; cursor:pointer; font-size:13px; padding:0; }
.imgup__err { font-size:13px; color:#e3342f; margin-top:4px; font-weight:500; }
.imgup__url { width:100%; max-width:400px; margin-top:6px; padding:8px 12px; border-radius:5px;
    border:1px solid var(--input-border-color,#d7d7d7); background:var(--input-background-color,#fff); color:var(--text-color,#272727); font-size:14px; }
.imgup__tip { font-size:12px; color:var(--meta-color,#999); margin-top:6px; }
</style>
<script>
(function () {
    var ALLOWED = ['image/jpeg','image/png','image/gif','image/webp'];
    var MSG_TYPE = @json(__('messages.account.upload_invalid_type'));
    var MSG_SIZE = @json(__('messages.account.upload_too_large'));

    function el(wrap, sel){ return wrap.querySelector(sel); }
    function showErr(wrap, msg){ var e=el(wrap,'[data-imgup-err]'); if(e){ e.textContent=msg; e.hidden=false; } }
    function clearErr(wrap){ var e=el(wrap,'[data-imgup-err]'); if(e){ e.hidden=true; } }

    function setPreview(wrap, src, fname){
        var img=el(wrap,'[data-imgup-preview]'), ph=el(wrap,'[data-imgup-ph]'),
            clr=el(wrap,'[data-imgup-clear]'), nm=el(wrap,'[data-imgup-name]');
        if(img){ if(img.dataset.obj){URL.revokeObjectURL(img.dataset.obj);img.dataset.obj='';} img.src=src; img.hidden=!src; }
        if(ph) ph.hidden=!!src;
        if(clr) clr.hidden=!src;
        if(nm) nm.textContent=fname||'';
    }

    // Chọn file từ máy -> validate + preview
    document.addEventListener('change', function(e){
        var input=e.target.closest('[data-imgup] [data-imgup-input]'); if(!input) return;
        var wrap=input.closest('[data-imgup]'); clearErr(wrap);
        var f=input.files&&input.files[0]; if(!f) return;
        if(ALLOWED.indexOf(f.type)===-1){ showErr(wrap,MSG_TYPE); input.value=''; input.setAttribute('data-invalid','1'); return; }
        var max=(parseFloat(input.getAttribute('data-max-mb'))||4)*1024*1024;
        if(f.size>max){ showErr(wrap,MSG_SIZE.replace(':size',(f.size/1048576).toFixed(1)+'MB').replace(':max',(max/1048576)+'MB')); input.value=''; input.setAttribute('data-invalid','1'); return; }
        input.removeAttribute('data-invalid');
        var img=el(wrap,'[data-imgup-preview]');
        var u=URL.createObjectURL(f); if(img) img.dataset.obj=u;
        setPreview(wrap,u,f.name);
        var url=el(wrap,'[data-imgup-url]'); if(url) url.value='';   // ưu tiên file -> bỏ URL
    });

    // Dán URL -> preview luôn
    document.addEventListener('input', function(e){
        var url=e.target.closest('[data-imgup] [data-imgup-url]'); if(!url) return;
        var wrap=url.closest('[data-imgup]'); var v=url.value.trim();
        if(v){ var inp=el(wrap,'[data-imgup-input]'); if(inp) inp.value=''; setPreview(wrap,v,''); }
    });

    // Xoá
    document.addEventListener('click', function(e){
        var btn=e.target.closest('[data-imgup] [data-imgup-clear]'); if(!btn) return;
        var wrap=btn.closest('[data-imgup]');
        var inp=el(wrap,'[data-imgup-input]'); if(inp){ inp.value=''; inp.removeAttribute('data-invalid'); }
        var url=el(wrap,'[data-imgup-url]'); if(url) url.value='';
        clearErr(wrap); setPreview(wrap,'','');
    });

    // Chặn submit nếu còn file không hợp lệ
    document.addEventListener('submit', function(e){
        var bad=e.target.querySelector('[data-imgup-input][data-invalid="1"]');
        if(bad){ e.preventDefault(); bad.closest('[data-imgup]').scrollIntoView({block:'center'}); }
    }, true);
})();
</script>
@endonce
