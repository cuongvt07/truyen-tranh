{{-- Icon vương miện VIP (vàng) ở góc trên-trái. Wrapper avatar phải có class .vip-ring. --}}
@if(user_is_vip($userId ?? null))
<i class="fa fa-crown vip-crown" aria-hidden="true"></i>
@endif
