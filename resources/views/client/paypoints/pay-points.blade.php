@extends('layout.client')

@section('content')
    <h2>Nạp Tiền vào Tài Khoản</h2>
    
    <!-- Các mức nạp tiền -->
    <div>
        <button class="btn btn-primary" id="deposit20k">Nạp 20k</button>
        <button class="btn btn-primary" id="deposit50k">Nạp 50k</button>
        <button class="btn btn-primary" id="deposit100k">Nạp 100k</button>
    </div>

    <!-- Hiển thị mã QR -->
    <div id="paymentInfo" style="display:none;">
        <h5>💳 Thông tin chuyển khoản</h5>
        <div id="qrCodeContainer" style="display:none;">
            <img src="" id="qrCodeImage" alt="QR Code">
        </div>
        <p id="chargeId"></p>
        <p id="amount"></p>
        <p id="bankName"></p>
        <p id="accountNumber"></p>
        <p id="accountHolder"></p>
    </div>
    <div id="countdown" style="font-size: 18px; color: red; margin-top: 20px;">5:00</div>
@endsection

<script>
    // Đảm bảo mã chỉ chạy khi DOM đã được tải
    document.addEventListener('DOMContentLoaded', function () {
        // Lắng nghe các sự kiện click trên các nút nạp tiền
        document.getElementById('deposit20k')?.addEventListener('click', function () {
            processDeposit(20000);
        });

        document.getElementById('deposit50k')?.addEventListener('click', function () {
            processDeposit(50000);
        });

        document.getElementById('deposit100k')?.addEventListener('click', function () {
            processDeposit(100000);
        });
    });

    // Hàm xử lý nạp tiền
    function processDeposit(amount) {
        // Gửi yêu cầu đến API để tạo mã QR
        fetch('https://1c2f-117-4-246-38.ngrok-free.app/generate-qr', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                amount: amount,
                chargeId: 'WEB' + Math.floor(Math.random() * 1000000)  // Tạo chargeId ngẫu nhiên
            })
        })
        .then(response => response.json())
        .then(data => {
            // Hiển thị thông tin mã QR
            document.getElementById('qrCodeImage').src = data.qr_code_url;
            document.getElementById('qrCodeContainer').style.display = 'block';
            document.getElementById('paymentInfo').style.display = 'block';
            document.getElementById('chargeId').innerHTML = 'Mã giao dịch: ' + data.charge_id;
            document.getElementById('amount').innerHTML = 'Số tiền: ' + data.amount;
            document.getElementById('bankName').innerHTML = 'Ngân hàng: ' + data.bank_name;
            document.getElementById('accountNumber').innerHTML = 'Số tài khoản: ' + data.account_number;
            document.getElementById('accountHolder').innerHTML = 'Chủ tài khoản: ' + data.account_holder;

            // Bắt đầu bộ đếm thời gian ngược
            startCountdown(data.charge_id);
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    let countdownTimer;
    let countdownTime = 300;  // 5 phút (300 giây)

    // Bắt đầu bộ đếm thời gian ngược
    function startCountdown(chargeId) {
        // Hiển thị bộ đếm ngay khi bắt đầu
        countdownTimer = setInterval(function() {
            let minutes = Math.floor(countdownTime / 60);
            let seconds = countdownTime % 60;
            if (seconds < 10) {
                seconds = '0' + seconds;
            }

            // Cập nhật bộ đếm
            document.getElementById('countdown').innerHTML = `${minutes}:${seconds}`;

            // Kiểm tra nếu hết thời gian
            if (countdownTime <= 0) {
                clearInterval(countdownTimer);
                alert('Thời gian kiểm tra giao dịch đã hết!');
                return;
            }

            countdownTime--;

            // Gọi API kiểm tra trạng thái giao dịch mỗi giây
            checkTransactionStatus(chargeId);
        }, 1000);
    }

    // Kiểm tra trạng thái giao dịch
    function checkTransactionStatus(chargeId) {
        fetch('https://1c2f-117-4-246-38.ngrok-free.app/transactions/check', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                charge_id: chargeId
            })
        })
        .then(response => response.json())
        .then(data => {
            // Kiểm tra trạng thái của giao dịch
            if (data.status === 'completed') {
                clearInterval(countdownTimer); // Dừng bộ đếm
                alert('Thanh toán thành công!');
                // Bạn có thể chuyển trang hoặc hiển thị thông báo thành công tại đây
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
</script>
