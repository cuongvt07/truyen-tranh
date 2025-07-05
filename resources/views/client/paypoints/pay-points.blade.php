@extends('layout.client')

@section('content')
<div class="container">
    <div class="d-flex justify-content-center mb-4" style="
    display: flex;
    justify-content: center;
    margin-bottom: 15px;
">
        <h1 style="font-size: 24px;">Nạp Tiền vào Tài Khoản</h1>
    </div>

    <div class="container">
        <div class="row">
            <!-- Cột trái: các nút nạp -->
            <div class="col-md-6 mb-4">
                <div class="d-grid gap-3" style="display: flex; flex-direction: column; gap: 14px;">
                    <button class="btn btn-primary py-3" id="deposit20k">💸 Nạp 20.000đ</button>
                    <button class="btn btn-primary py-3" id="deposit50k">💸 Nạp 50.000đ</button>
                    <button class="btn btn-primary py-3" id="deposit100k">💸 Nạp 100.000đ</button>
                </div>
            </div>

            <!-- Cột phải: QR + thông tin -->
            <div class="col-md-6">
                <div id="paymentInfo" style="display:none;">
                    <div class="card shadow p-3">
                        <h5 class="mb-3 text-primary fw-bold">💳 Thông tin chuyển khoản</h5>

                        <div class="row align-items-center">
                            <!-- Cột trái: thông tin -->
                            <div class="col-md-7">
                                <ul class="list-group list-group-flush small">
                                    <li class="list-group-item px-0"><strong>Mã giao dịch:</strong> <span
                                            id="chargeId"></span></li>
                                    <li class="list-group-item px-0"><strong>Số tiền:</strong> <span id="amount"></span>
                                    </li>
                                    <li class="list-group-item px-0"><strong>Ngân hàng:</strong> <span id="bankName"></span>
                                    </li>
                                    <li class="list-group-item px-0"><strong>Số tài khoản:</strong> <span
                                            id="accountNumber"></span></li>
                                    <li class="list-group-item px-0"><strong>Chủ tài khoản:</strong> <span
                                            id="accountHolder"></span></li>
                                </ul>
                            </div>

                            <!-- Cột phải: QR + trạng thái -->
                            <div class="col-md-5 text-center">
                                <div id="qrCodeContainer" style="display:none;">
                                    <img src="" id="qrCodeImage" alt="QR Code"
                                        style="max-width: 100%; border: 1px solid #ddd; padding: 5px; border-radius: 10px;">

                                    <!-- Đang kiểm tra -->
                                    <p class="text-muted mt-2" id="checkingText" style="font-size: 14px;">
                                        Đang kiểm tra<span id="dots">.</span>
                                    </p>

                                    <!-- Dấu tích khi thành công -->
                                    <div id="successCheck" class="mt-2" style="display: none;">
                                        <span style="font-size: 40px; color: green;">✔️</span>
                                        <p class="text-success fw-bold mt-1">Thanh toán thành công!</p>
                                    </div>
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
                chargeId: 'WEB' + Math.floor(Math.random() * 1000000)
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