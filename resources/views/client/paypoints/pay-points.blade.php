@extends('layout.novelight')

@section('template_title', __('messages.pay.topup_and_buy_vip'))

@section('content')
@php
    $premiumPackages = getPremiumPackages();
    $userPoints = auth()->user()->points ?? 0;
@endphp

<div class="alpha-workspace alpha-topup-page">
    <div class="container">
        <section class="alpha-workspace-hero">
            <div class="alpha-workspace-hero__row">
                <div>
                    <small>Wallet</small>
                    <h1>{{ __('messages.pay.deposit_to_account') }}</h1>
                    <p>{{ __('messages.pay.current_balance') }} <strong>{{ number_format($userPoints) }}</strong> {{ coin_name() }}</p>
                </div>
                <a href="{{ route_path('pages.pricing') }}" class="alpha-btn">{{ __('messages.pay.store') }}</a>
            </div>
        </section>

        <div class="alpha-topup-grid">
            <section class="alpha-panel alpha-panel--pad">
                <h2 class="alpha-section-title">{{ __('messages.pay.deposit_to_account') }}</h2>
                <div class="alpha-topup-amounts">
                    <button class="alpha-btn alpha-btn--primary" id="deposit20k" type="button">
                        <span>{{ __('messages.pay.deposit_amount', ['amount' => '20.000đ']) }}</span><i class="fa fa-chevron-right"></i>
                    </button>
                    <button class="alpha-btn alpha-btn--primary" id="deposit50k" type="button">
                        <span>{{ __('messages.pay.deposit_amount', ['amount' => '50.000đ']) }}</span><i class="fa fa-chevron-right"></i>
                    </button>
                    <button class="alpha-btn alpha-btn--primary" id="deposit100k" type="button">
                        <span>{{ __('messages.pay.deposit_amount', ['amount' => '100.000đ']) }}</span><i class="fa fa-chevron-right"></i>
                    </button>
                </div>
            </section>

            <section class="alpha-panel alpha-panel--pad" id="paymentInfo" style="display:none">
                <h2 class="alpha-section-title">{{ __('messages.pay.transfer_info') }}</h2>
                <div class="alpha-transfer-grid">
                    <ul class="alpha-transfer-list">
                        <li><span>{{ __('messages.pay.transaction_code') }}</span><strong id="chargeId"></strong></li>
                        <li><span>{{ __('messages.pay.amount') }}</span><strong id="amount"></strong></li>
                        <li><span>{{ __('messages.pay.bank') }}</span><strong id="bankName"></strong></li>
                        <li><span>{{ __('messages.pay.account_number') }}</span><strong id="accountNumber"></strong></li>
                        <li><span>{{ __('messages.pay.account_holder') }}</span><strong id="accountHolder"></strong></li>
                    </ul>
                    <div class="alpha-qr-card" id="qrCodeContainer" style="display:none">
                        <img src="" id="qrCodeImage" alt="QR Code">
                        <p class="meta-color" id="checkingText">{{ __('messages.pay.checking') }}<span id="dots">.</span></p>
                        <div id="successCheck" style="display:none">
                            <div class="alpha-order-icon" style="margin:0 auto 8px"><i class="fa fa-check"></i></div>
                            <p style="color:#16a34a;font-weight:800">{{ __('messages.pay.payment_success') }}</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <section class="alpha-panel alpha-panel--pad" style="margin-top:22px">
            <h2 class="alpha-section-title">{{ __('messages.pay.buy_vip_package') }}</h2>
            <div class="alpha-vip-grid">
                @foreach($premiumPackages as $i => $package)
                    <button class="alpha-vip-card" id="deposit{{ $package['coins'] }}" onclick="selectVipPackage({{ $i }})" type="button">
                        <span>VIP</span>
                        <h3>{{ $package['name'] }}</h3>
                        <strong>{{ number_format($package['coins']) }} {{ coin_name() }}</strong>
                        <span>{{ $package['days'] }} {{ __('messages.pay.vip_days') }}</span>
                        @if($userPoints >= $package['coins'])
                            <span style="color:#16a34a;font-weight:800">{{ __('messages.pay.enough_points') }}</span>
                        @else
                            <span style="color:#dc2626;font-weight:800">{{ __('messages.pay.not_enough_points') }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </section>

        <div id="vipConfirmPopup" class="alpha-modal" style="display:none">
            <div class="alpha-modal__content">
                <p id="vipConfirmMessage" style="white-space:pre-line;line-height:1.7"></p>
                <div class="alpha-form-actions" style="justify-content:center">
                    <button id="confirmVipBtn" class="alpha-btn alpha-btn--primary" type="button">{{ __('messages.pay.confirm') }}</button>
                    <button id="cancelVipBtn" class="alpha-btn" type="button">{{ __('messages.pay.cancel') }}</button>
                </div>
            </div>
        </div>

        <section class="alpha-panel alpha-panel--pad" id="vipInfo" style="display:none;margin-top:22px">
            <h2 class="alpha-section-title">{{ __('messages.pay.vip_package_info') }}</h2>
            <div class="alpha-transfer-grid">
                <ul class="alpha-transfer-list">
                    <li><span>{{ __('messages.pay.transaction_code') }}</span><strong id="vipChargeId"></strong></li>
                    <li><span>{{ __('messages.pay.amount') }}</span><strong id="vipAmount"></strong></li>
                    <li><span>{{ __('messages.pay.package_type') }}</span><strong id="vipPackage"></strong></li>
                </ul>
                <div class="alpha-qr-card" id="vipQrCodeContainer" style="display:none">
                    <img src="" id="vipQrCodeImage" alt="QR Code">
                    <p class="meta-color" id="vipCheckingText">{{ __('messages.pay.checking') }}<span id="vipDots">.</span></p>
                    <div id="vipSuccessCheck" style="display:none">
                        <div class="alpha-order-icon" style="margin:0 auto 8px"><i class="fa fa-check"></i></div>
                        <p style="color:#16a34a;font-weight:800">{{ __('messages.pay.vip_payment_success') }}</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@section('page_js')
<script>
    let countdownTimer;
    let dotInterval;

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('deposit20k')?.addEventListener('click', () => processDeposit(20000));
        document.getElementById('deposit50k')?.addEventListener('click', () => processDeposit(50000));
        document.getElementById('deposit100k')?.addEventListener('click', () => processDeposit(100000));
    });

    function processDeposit(amount) {
        fetch('{{ route_path('generate.qr') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                amount: amount,
                chargeId: 'WEB' + String(Math.floor(Math.random() * 1000000)).padStart(5, '0')
            })
        })
            .then(res => res.json())
            .then(data => {
                document.getElementById('qrCodeImage').src = data.qr_code_url;
                document.getElementById('qrCodeContainer').style.display = 'block';
                document.getElementById('paymentInfo').style.display = 'block';
                document.getElementById('chargeId').innerText = data.charge_id;
                document.getElementById('amount').innerText = data.amount;
                document.getElementById('bankName').innerText = data.bank_name;
                document.getElementById('accountNumber').innerText = data.account_number;
                document.getElementById('accountHolder').innerText = data.account_holder;
                document.getElementById('successCheck').style.display = 'none';
                document.getElementById('checkingText').style.display = 'block';
                startDotAnimation();
                startPolling(data.charge_id);
            })
            .catch(error => console.error('Error:', error));
    }

    function startPolling(chargeId) {
        clearInterval(countdownTimer);
        countdownTimer = setInterval(() => checkTransactionStatus(chargeId), 1000);
    }

    function checkTransactionStatus(chargeId) {
        fetch('{{ route_path('sepay.transactions.check') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ charge_id: chargeId })
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'completed') {
                    clearInterval(countdownTimer);
                    stopDotAnimation();
                    document.getElementById('checkingText').style.display = 'none';
                    document.getElementById('successCheck').style.display = 'block';
                    setTimeout(() => location.reload(), 2500);
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function startDotAnimation() {
        const dotsEl = document.getElementById('dots');
        let state = 1;
        clearInterval(dotInterval);
        dotInterval = setInterval(() => {
            state = state % 4;
            dotsEl.textContent = '.'.repeat(state);
            state++;
        }, 500);
    }

    function stopDotAnimation() {
        clearInterval(dotInterval);
        document.getElementById('dots').textContent = '';
    }

    function selectVipPackage(packageId) {
        const vipPackages = @json($premiumPackages);
        const selectedPackage = vipPackages[packageId];
        const userPoints = {{ $userPoints }};

        if (userPoints >= selectedPackage.coins) {
            showVipConfirmPopup(packageId);
        } else if (confirm('{{ __('messages.pay.not_enough_points_topup_prompt') }}')) {
            window.location.href = '{{ route_path('client.paypoints') }}';
        }
    }

    function showVipConfirmPopup(packageId) {
        const vipPackages = @json($premiumPackages);
        const selectedPackage = vipPackages[packageId];
        const message = @json(__('messages.pay.confirm_buy_title')) + '\n'
            + @json(__('messages.pay.label_package')) + ' ' + selectedPackage.name + '\n'
            + @json(__('messages.pay.label_price')) + ' ' + selectedPackage.coins + ' ' + @json(coin_name()) + '\n'
            + @json(__('messages.pay.label_vip_days')) + ' ' + selectedPackage.days;

        document.getElementById('vipConfirmMessage').innerText = message;
        document.getElementById('vipConfirmPopup').style.display = 'flex';
        document.getElementById('confirmVipBtn').onclick = function () {
            purchaseVip(packageId);
            document.getElementById('vipConfirmPopup').style.display = 'none';
        };
        document.getElementById('cancelVipBtn').onclick = function () {
            document.getElementById('vipConfirmPopup').style.display = 'none';
        };
    }

    function purchaseVip(packageId) {
        fetch('{{ route_path('vip.buy') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ package_id: packageId })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message + ' ' + @json(__('messages.pay.vip_deadline')) + ' ' + data.vip_end);
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(err => alert(@json(__('messages.pay.error_occurred')) + ' ' + err));
    }
</script>
@endsection
