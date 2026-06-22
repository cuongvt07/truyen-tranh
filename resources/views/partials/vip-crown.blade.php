{{-- Khung VIP (viền + vương miện + ruy băng) phủ quanh avatar. Wrapper phải có class .vip-ring.
     Ẩn an toàn nếu thiếu file ảnh (onerror). $userId = id chủ avatar. --}}
@if(user_is_vip($userId ?? null))
<img src="{{ asset('static/core/images/vip-frame.png') }}" class="vip-frame" alt="VIP"
     loading="lazy" onerror="this.style.display='none'">
@endif
