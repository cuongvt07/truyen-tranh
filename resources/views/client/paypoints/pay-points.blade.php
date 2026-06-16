@extends('layout.novelight')

@section('template_title', __('messages.pay.topup_and_buy_vip'))

@section('content')
<div class="container">
    <header class="header-manga" style="margin-bottom:10px">
        <div class="container">
            <h1><i class="fa fa-credit-card"></i> {{ __('messages.pay.deposit_to_account') }}</h1>
            @auth<p class="meta-color" style="font-size:14px;margin-top:4px">{{ __('messages.pay.current_balance') }} <strong style="color:var(--primary)">{{ number_format(auth()->user()->points ?? 0) }}</strong> {{ coin_name() }}</p>@endauth
        </div>
    </header>

    <div class="container">
        <div class="row box">
            <!-- Cột trái: các nút nạp -->
            <div class="col-md-4 mb-4">
                <div class="d-grid gap-3" style="display: flex; flex-direction: column; gap: 14px;">
                    <button class="btn btn-primary py-3" id="deposit20k">💸 {{ __('messages.pay.deposit_amount', ['amount' => '20.000đ']) }}</button>
                    <button class="btn btn-primary py-3" id="deposit50k">💸 {{ __('messages.pay.deposit_amount', ['amount' => '50.000đ']) }}</button>
                    <button class="btn btn-primary py-3" id="deposit100k">💸 {{ __('messages.pay.deposit_amount', ['amount' => '100.000đ']) }}</button>
                </div>
            </div>

            <!-- Cột phải: QR + thông tin -->
            <div class="col-md-6">
                <div id="paymentInfo" style="display:none;">
                    <div class="card shadow p-3">
                        <h5 class="mb-3 text-primary fw-bold" style="font-size: 24px; text-align: center;">💳 {{ __('messages.pay.transfer_info') }}</h5>

                        <div class="row align-items-center box2">
                            <!-- Cột trái: thông tin -->
                            <div class="col-md-7">
                                <ul class="list-group list-group-flush small">
                                    <li class="list-group-item px-0"><strong>{{ __('messages.pay.transaction_code') }}</strong> <span
                                            id="chargeId"></span></li>
                                    <li class="list-group-item px-0"><strong>{{ __('messages.pay.amount') }}</strong> <span id="amount"></span>
                                    </li>
                                    <li class="list-group-item px-0"><strong>{{ __('messages.pay.bank') }}</strong> <span id="bankName"></span>
                                    </li>
                                    <li class="list-group-item px-0"><strong>{{ __('messages.pay.account_number') }}</strong> <span
                                            id="accountNumber"></span></li>
                                    <li class="list-group-item px-0"><strong>{{ __('messages.pay.account_holder') }}</strong> <span
                                            id="accountHolder"></span></li>
                                </ul>
                            </div>

                            <!-- Cột phải: QR + trạng thái -->
                            <div class="text-center"> 
                                <div id="qrCodeContainer" style="display:none; display: block;width: 350px;">
                                    <img src="" id="qrCodeImage" alt="QR Code"
                                        style="max-width: 100%; border: 1px solid #ddd; padding: 5px; border-radius: 10px;">

                                    <!-- Đang kiểm tra -->
                                    <p class="text-muted mt-2" id="checkingText" style="font-size: 14px;">
                                        {{ __('messages.pay.checking') }}<span id="dots">.</span>
                                    </p>

                                    <!-- Dấu tích khi thành công -->
                                    <div id="successCheck" class="mt-2" style="display: none;">
                                        <span style="font-size: 40px; color: green;">✔️</span>
                                        <p class="text-success fw-bold mt-1">{{ __('messages.pay.payment_success') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <hr>

        <div class="row box">
            <div class="col-md-12 mb-4">
                <div class="d-grid gap-3" style="display: flex; flex-direction: column; gap: 14px;">
                     <h5 class="mb-3 text-primary fw-bold" style="font-size: 24px; text-align: center;">🌟 {{ __('messages.pay.buy_vip_package') }}</h5>
                    <div style="display: flex; gap: 14px; flex-wrap: wrap; justify-content: center;">
                        @foreach(getPremiumPackages() as $i => $package)
                            <div class="package-option d-flex flex-column align-items-center text-center p-4" id="deposit{{$package['coins']}}" onclick="selectVipPackage({{ $i }})">
                                <div class="package-header">
                                    <h5 class="package-name">{{ $package['name'] }} 💎</h5>
                                    <p class="package-price">{{ number_format($package['coins']) }} xu</p>
                                </div>
                                <div class="package-duration" style="font-size: 14px; color: #6c757d;">
                                    <span>{{ $package['days'] }} {{ __('messages.pay.vip_days') }}</span>
                                </div>
                                <div class="package-status" style="margin-top: 10px;">
                                    <?php
                                        $userPoints = auth()->user()->points;
                                    ?>
                                    @if(isset($userPoints))
                                        @if($userPoints >= $package['coins'])
                                            <span class="status-available">{{ __('messages.pay.enough_points') }}</span>
                                        @else
                                            <span class="status-unavailable">{{ __('messages.pay.not_enough_points') }}</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div id="vipConfirmPopup" class="popup-overlay" style="display: none;">
                <div class="popup-content">
                    <p id="vipConfirmMessage"></p>
                    <div class="d-flex justify-content-center">
                        <button id="confirmVipBtn" class="btn btn-success">{{ __('messages.pay.confirm') }}</button>
                        <button id="cancelVipBtn" class="btn btn-danger">{{ __('messages.pay.cancel') }}</button>
                    </div>
                </div>
            </div>

            <!-- Thông tin Gói VIP -->
            <div class="col-md-12" id="vipInfo" style="display: none;">
                <div class="card shadow p-3">
                    <h5 class="mb-3 text-primary fw-bold" style="font-size: 24px; text-align: center;">💳 {{ __('messages.pay.vip_package_info') }}</h5>

                    <div class="row align-items-center box2">
                        <div class="col-md-7">
                            <ul class="list-group list-group-flush small">
                                <li class="list-group-item px-0"><strong>{{ __('messages.pay.transaction_code') }}</strong> <span id="vipChargeId"></span></li>
                                <li class="list-group-item px-0"><strong>{{ __('messages.pay.amount') }}</strong> <span id="vipAmount"></span></li>
                                <li class="list-group-item px-0"><strong>{{ __('messages.pay.package_type') }}</strong> <span id="vipPackage"></span></li>
                            </ul>
                        </div>

                        <!-- Cột phải: QR + trạng thái -->
                        <div class="text-center">
                            <div id="vipQrCodeContainer" style="display:none; display: block;width: 350px;">
                                <img src="" id="vipQrCodeImage" alt="QR Code"
                                    style="max-width: 100%; border: 1px solid #ddd; padding: 5px; border-radius: 10px;">
                                <p class="text-muted mt-2" id="vipCheckingText" style="font-size: 14px;">{{ __('messages.pay.checking') }}<span id="vipDots">.</span></p>

                                <!-- Dấu tích khi thành công -->
                                <div id="vipSuccessCheck" class="mt-2" style="display: none;">
                                    <span style="font-size: 40px; color: green;">✔️</span>
                                    <p class="text-success fw-bold mt-1">{{ __('messages.pay.vip_payment_success') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
<style>
    .package-option {
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 12px;
        width: 200px;
        cursor: pointer;
        transition: transform 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 20px;
        text-align: center;
        margin-bottom: 20px;
    }

    .package-option:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }

    .package-header {
        margin-bottom: 10px;
    }

    .package-name {
        font-size: 18px;
        font-weight: bold;
        color: #2c3e50;
    }

    .package-price {
        font-size: 16px;
        color: #f39c12;
        font-weight: 600;
    }

    .package-duration {
        font-size: 14px;
        color: #6c757d;
    }

    .package-status {
        margin-top: 10px;
    }

    .status-available {
        font-size: 14px;
        color: green;
        font-weight: bold;
    }

    .status-unavailable {
        font-size: 14px;
        color: red;
        font-weight: bold;
    }

    .popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .popup-content {
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        width: 400px;
        text-align: center;
    }

    .popup-content button {
        margin: 10px;
        padding: 10px 20px;
        font-size: 16px;
    }
</style>
<style>
    .box2 {
        display: flex;
        align-items: center;
        background: #fff;
        padding: 10px;
    }

    .box {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
    }

    @media (max-width: 480px) {
        .box {
            flex-direction: column;
        }

        .box2 {
            flex-direction: column-reverse;
        }
    }
</style>

<script>
    let countdownTimer;
    let dotInterval;

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('deposit20k')?.addEventListener('click', () => processDeposit(20000));
        document.getElementById('deposit50k')?.addEventListener('click', () => processDeposit(50000));
        document.getElementById('deposit100k')?.addEventListener('click', () => processDeposit(100000));
    });

    function processDeposit(amount) {
        fetch('{{ route('generate.qr') }}', {
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
            .catch(error => console.error('Lỗi:', error));
    }

    function startPolling(chargeId) {
        clearInterval(countdownTimer);
        countdownTimer = setInterval(() => {
            checkTransactionStatus(chargeId);
        }, 1000);
    }

    function checkTransactionStatus(chargeId) {
        fetch('{{ route('sepay.transactions.check') }}', {
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

                    setTimeout(() => {
                        location.reload();
                    }, 2500);
                }
            })
            .catch(error => console.error('Lỗi:', error));
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
</script>
<script>
function selectVipPackage(packageId) {
    const package = @json(getPremiumPackages());
    const selectedPackage = package[packageId];

    const coins = selectedPackage.coins;
    const name = selectedPackage.name;
    const days = selectedPackage.days;

    const userPoints = {{ auth()->user()->points ?? 0 }};
    if (userPoints >= coins) {
        showVipConfirmPopup(packageId, coins);
    } else {
        const confirmation = confirm('{{ __('messages.pay.not_enough_points_topup_prompt') }}');
        if (confirmation) {
            window.location.href = '{{ route('client.paypoints') }}';
        }
    }
}

function showVipConfirmPopup(packageId, coins) {
    const package = @json(getPremiumPackages());
    const selectedPackage = package[packageId];

    const message = @json(__('messages.pay.confirm_buy_title')) + '\n'
        + @json(__('messages.pay.label_package')) + ' ' + selectedPackage.name + '\n'
        + @json(__('messages.pay.label_price')) + ' ' + selectedPackage.coins + ' ' + @json(coin_name()) + '\n'
        + @json(__('messages.pay.label_vip_days')) + ' ' + selectedPackage.days;

    document.getElementById('vipConfirmMessage').innerText = message;

    document.getElementById('vipConfirmPopup').style.display = 'flex';

    document.getElementById('confirmVipBtn').addEventListener('click', function () {
        purchaseVip(packageId);
        document.getElementById('vipConfirmPopup').style.display = 'none'; 
    });

    document.getElementById('cancelVipBtn').addEventListener('click', function () {
        document.getElementById('vipConfirmPopup').style.display = 'none'; 
    });
}
function purchaseVip(packageId) {
    fetch('{{ route('vip.buy') }}', {
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
    .catch(err => {
        alert(@json(__('messages.pay.error_occurred')) + ' ' + err);
    });
}

</script>