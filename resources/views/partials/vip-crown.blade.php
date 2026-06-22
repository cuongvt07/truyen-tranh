{{-- Vương miện VIP, chèn vào trong wrapper avatar (wrapper phải có class .vip-ring để định vị).
     Ẩn an toàn nếu file ảnh chưa được upload (onerror). $userId = id chủ avatar. --}}
@if(user_is_vip($userId ?? null))
<img src="{{ asset('static/core/images/vip-crown.png') }}" class="vip-crown" alt="VIP"
     loading="lazy" onerror="this.style.display='none'">
@endif
