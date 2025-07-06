@extends('layout.client')
@section('template_title')
    {{ __($chapter->title) }}
@endsection
@section('content')
    <div id="chapter-big-container" class="container chapter">
        <div class="row">
            <div class="col-xs-12" style="height: auto !important; min-height: 0px !important;">
                <a class="truyen-title" href="{{ route('articles.show', $article->id) }}"
                   title="{{ $article->title }}">
                    {{ $article->title }}
                </a>
                <h2>
                    <a class="chapter-title"
                       href="{{ route('articles.chapters.show', [$article->id, $chapter->number]) }}"
                       title="{{ $chapter->title }}">
                    <span class="chapter-text">
                        <span>
                            {{ $chapter->number_text }}
                        </span>
                    </span>
                    </a>
                    @if($isUserLoggedIn && ($currentUser->is_admin || $currentUser->id === $user->id))
                        <a href="{{ route('admin.articles.edit_chapter', [$article->id, $chapter->id]) }}"
                           class="btn btn-block btn-primary btn-border" style="margin-top: 10px">
                            <span class="glyphicon glyphicon-edit"></span> Sửa chương
                        </a>
                        <a href="{{ route('admin.articles.create_chapter', $article->id) }}"
                           class="btn btn-warning btn-border" style="margin-top: 10px">
                            <span class="glyphicon glyphicon-plus"></span> Chương mới
                        </a>
                    @endif
                </h2>
                <hr class="chapter-hr"/>
                <div class="chapter-nav" id="chapter-nav-top">
                    @include('client.partials.select-chapter')
                </div>

                <div id="chapter-c" class="chapter-c" style="height: auto !important;">
                    {!! nl2br($chapter->content) !!}
                </div>
                <hr class="chapter-hr"/>
                <div class="chapter-nav" id="chapter-nav-bot">
                    @include('client.partials.select-chapter')
                    <div class="text-center">
                        <button type="button" class="btn btn-warning" id="chapter_error">
                            <span class="glyphicon glyphicon-flag"></span> Báo lỗi chương
                        </button>
                        <button class="btn btn-info" data-toggle="collapse" data-target="#demo">
                            <span class="glyphicon glyphicon-comment"></span> Bình Luận
                        </button>
                    </div>
                </div>
                <div class="bg-info text-center visible-md visible-lg box-notice">
                    Tip: Bạn có thể sử dụng phím trái, phải, A và D để chuyển giữa các chương.
                </div>

                <div class="col-xs-12">
                    <div id="demo" class="collapse">
                        @include('client.partials.comment')
                    </div>
                    <div class="row" id="chapter_comment">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ad Popup Modal -->
    @if($showPopup)
        <div id="adPopup" class="ad-popup-overlay">
            <div class="ad-popup-content">
                <div class="ad-popup-header">
                    <h4>🎯 Hỗ trợ website</h4>
                    <p>Vui lòng click quảng cáo để mở đọc truyện hoặc nâng cấp Premium để tiếp tục đọc truyện miễn phí. Xin lỗi về sự bất tiện này!</p>
                </div>
                <div class="ad-popup-body mb-3">
                    <a href="{{$affiLink ?? ''}}" target="_blank" id="adLink" class="ad-link">
                        <div class="ad-banner" style="
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                ">
                            <img src="{{$affiImage ?? ''}}" alt="Quảng cáo">
                            <div class="ad-text">
                                <strong>🎁 Ưu đãi đặc biệt!</strong>
                                <p>Click để xem chi tiết</p>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="ad-packages mb-3 text-start">
                    <?php
                        if (\Illuminate\Support\Facades\Auth::check()) {
                            $userPoints = \Illuminate\Support\Facades\Auth::user()->points ?? 0;
                        ?>
                            <p style="font-size: 14px; color: #666; text-align: left;">Bạn có <strong style="color: red;">{{ number_format($userPoints) }}</strong> xu</p>
                        <?php
                        }
                    ?>
                    <h5 style="margin-bottom:10px; text-align: left;">🎁 Hoặc nâng cấp Premium (Ẩn quảng cáo):</h5>
                    <form id="vipForm" style="display: flex; flex-direction: column;">
                        <div class="package-container d-flex flex-wrap justify-content-center gap-3" style="
                        display: flex;
                        gap: 10px;
                        margin-bottom: 10px;">
                            @foreach(getPremiumPackages() as $i => $package)
                            <div class="package-option d-flex flex-column align-items-center text-center p-3" style="border: 1px solid #ddd; border-radius: 6px; width: 250px;" onclick="$(this).find('input[type=radio]').prop('checked', true).trigger('change');">
                                <input type="radio" name="vip_package" value="{{ $i }}" class="form-check-input mb-2">
                                <div>
                                    <div><strong>{{ $package['name'] }}</strong> - {{ number_format($package['coins']) }} xu</div>
                                    <div style="font-size:13px;color:#888;">{{ $package['days'] }} ngày VIP</div>
                                    @if(isset($userPoints))
                                        @if($userPoints >= $package['coins'])
                                            <span style="font-size: 12px; color: green;">Đủ điểm để đăng ký!</span>
                                        @else
                                            <span style="font-size: 12px; color: red;">Không đủ điểm để đăng ký!</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </form>
                </div>

                <button type="button" class="btn btn-primary w-100 mb-2" id="buyVipBtn">Mua ngay</button>
                <button type="button" class="btn btn-success w-100" id="depositBtn" style="display:none;">Nạp thêm</button>

                <!-- Thông tin chuyển khoản -->
                <div id="paymentInfo" style="display:none; margin-top:15px; text-align:left;">
                    <h5>💳 Thông tin chuyển khoản</h5>
                    <p>Vui lòng nạp thêm vào tài khoản <strong>một trong các tài khoản dưới đây</strong>:</p>

                    @php
                        $bankAccounts = [];

                        if(setting('bank1_name') || setting('bank1_account_number')){
                            $bankAccounts[] = [
                                'name' => setting('bank1_name'),
                                'account_number' => setting('bank1_account_number'),
                                'account_holder' => setting('bank1_account_name'),
                                'qr' => setting('bank1_qr_image')
                            ];
                        }

                        if(setting('bank2_name') || setting('bank2_account_number')){
                            $bankAccounts[] = [
                                'name' => setting('bank2_name'),
                                'account_number' => setting('bank2_account_number'),
                                'account_holder' => setting('bank2_account_name'),
                                'qr' => setting('bank2_qr_image')
                            ];
                        }
                    @endphp

                    @if(count($bankAccounts))
                    <div class="row">
                        @foreach($bankAccounts as $bank)
                        <div class="col-md-6 mb-3">
                            <div style="border:1px solid #ddd; border-radius:6px; padding:10px;">
                                <div style="margin-bottom:8px;">
                                    Ngân hàng: <strong>{{ $bank['name'] }}</strong><br>
                                    Số TK: <strong>{{ $bank['account_number'] }}</strong><br>
                                    Chủ TK: <strong>{{ $bank['account_holder'] }}</strong><br>
                                    Nội dung chuyển khoản: <strong>Nạp thêm - {{ auth()->user()->username ?? 'Khách' }}</strong>
                                </div>
                                @if(!empty($bank['qr']))
                                <img src="{{ asset('storage/' . $bank['qr']) }}" style="max-width:100%;border:1px solid #eee;">
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p>Hiện chưa có thông tin tài khoản ngân hàng.</p>
                    @endif

                    <p class="mt-2"><small>Chờ trong ít phút, admin sẽ kiểm tra và kích hoạt VIP cho bạn.</small></p>
                </div>
            </div>
        </div>
    @endif

    <style>
        .related-box .realted-body.row img {
            width: 200px;
        }

        .related-box {
            background-color: #e6e6e6;
            padding: 10px;
        }

        .related-box .related-head-title {
            font-weight: bold;
            font-size: 16px;
        }

        .related-box .related-head {
            margin: 10px 0;
            text-align: left;
        }

        .related-box .title {
            padding: 5px 0;
        }

        .related-box .background-FFF {
            background: #fff;
        }

        .related-box .col-md-3.text-center {
            font-weight: bold;
        }

        @media screen and (max-width: 769px) {
            .related-box .realted-body.row img {
                width: 100%;
            }
        }

        #demo {
            text-align: left;
        }

        .package-option {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 8px 10px;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s;
        }
        .package-option:hover {
            background: #f9f9f9;
        }
        .package-option input[type="radio"] {
            margin-top: 0;
        }


        .ad-popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: auto;
        }

        .ad-popup-content {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            width: 90vw;
            max-width: 100%;
            height: 90vh;
            max-height: 90vh;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            gap: 20px;
        }

        @media screen and (max-width: 768px) {
            .ad-popup-content {
                width: 100vw;
                height: 100vh;
                max-height: 100vh;
                border-radius: 0;
                padding: 15px;
                overflow-y: auto;
            }

            .ad-banner {
                flex-direction: column;
            }

            .ad-banner img {
                width: 100% !important;
                max-width: 300px;
                margin: 0 auto;
            }

            .package-container {
                flex-direction: column !important;
                align-items: stretch;
            }

            .package-option {
                width: 40% !important;
            }
        }
        .ad-popup-header h4 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }

        .ad-popup-header p {
            margin: 5px 0 15px;
            color: #666;
        }

        .ad-popup-body {
            margin-bottom: 15px;
        }

        .ad-banner {
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
            transition: transform 0.3s;
        }

        .ad-banner:hover {
            transform: scale(1.03);
        }

        .ad-banner img {
            width: 30%;
            display: block;
        }

        .ad-text {
            padding: 10px;
            background: #f9f9f9;
        }

        .ad-text strong {
            display: block;
            color: #222;
            margin-bottom: 4px;
        }

        .ad-popup-footer small {
            color: #999;
        }

        .ad-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }
    </style>

<script>
$(document).ready(function () {
    // Ẩn hiện select chương
    $('select.chapter_jump').hide();
    $('button.chapter_jump').click(function () {
        $('button.chapter_jump').hide();
        $('select.chapter_jump').show();
    });

    @if($showPopup)
    // Click vào quảng cáo
    $('#adLink').click(function () {
        $.ajax({
            url: '{{ route('articles.chapters.markAdClicked', [$article->id, $chapter->number]) }}?t=' + Date.now(),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                if (response.success) {
                    $('#adPopup').hide();
                } else {
                    alert('Không tìm thấy chương hoặc có lỗi xảy ra.');
                }
            },
            error: function (xhr) {
                alert('Lỗi: ' + (xhr.responseJSON?.error || 'Không thể kết nối đến server.'));
            }
        });
    });

    // Ngăn click chương khi popup đang hiện
    $('select.chapter_jump, a[href*="chapters"]').click(function (e) {
        if ($('#adPopup').is(':visible')) {
            e.preventDefault();
            alert('Vui lòng click vào quảng cáo hoặc nâng cấp Premium để tiếp tục!');
        }
    });
    @endif
});

// Phím tắt chuyển chương
$(document).keydown(function (event) {
    @if($showPopup)
        return;
    @endif

    if (event.keyCode == 65 || event.keyCode == 37) {
        window.location.href = '{{ $chapter->previous?->number ?? "#" }}';
    } else if (event.keyCode == 68 || event.keyCode == 39) {
        window.location.href = '{{ $chapter->next?->number ?? "#" }}';
    }
});

// Mua VIP
document.getElementById('buyVipBtn').addEventListener('click', function () {
    const selected = document.querySelector('input[name="vip_package"]:checked');
    if (!selected) {
        alert('Vui lòng chọn gói VIP muốn mua!');
        return;
    }

    const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
    if (!isLoggedIn) {
        // Đặt cờ login từ chương truyện
        fetch('{{ route('setLoginReason') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ reason: 'from_chapter_buy_vip' })
        }).then(() => {
            window.location.href = '{{ route('login') }}';
        });
        return;
    }

    const packageId = selected.value;
    const coinsText = selected.closest('.package-option').querySelector('div > div').innerText;
    const coins = parseInt(coinsText.match(/([\d,]+)/)[1].replace(/,/g, ''));
    const userPoints = {{ isset($userPoints) ? $userPoints : 0 }};

    if (userPoints >= coins) {
        purchaseVip(packageId);
    } else {
        const confirmation = confirm('Bạn không đủ điểm để đăng ký VIP. Bạn có muốn nạp thêm không?');
        if (confirmation) {
            // Nếu chưa login khi nạp
            if (!isLoggedIn) {
                fetch('{{ route('setLoginReason') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ reason: 'from_chapter_buy_vip' })
                }).then(() => {
                    window.location.href = '{{ route('login') }}';
                });
            } else {
                window.location.href = '{{ route('client.paypoints') }}';
            }
        }
    }
});

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
            alert(data.message + ' Hạn VIP: ' + data.vip_end);
            document.getElementById('adPopup').style.display = 'none';
            // Reload sau 0.5s
            setTimeout(() => window.location.reload(), 500);
        } else {
            document.getElementById('paymentInfo').style.display = 'block';
        }
    })
    .catch(err => {
        alert('Có lỗi xảy ra: ' + err);
    });
}


// Nút Nạp thêm
document.getElementById('depositBtn').addEventListener('click', function(){
    const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
    if (!isLoggedIn) {
        fetch('{{ route('setLoginReason') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ reason: 'from_chapter_buy_vip' })
        }).then(() => {
            window.location.href = '{{ route('login') }}';
        });
        return;
    }
    document.getElementById('paymentInfo').style.display = 'block';
});

// Khi chọn gói thì đổi nút
document.querySelectorAll('input[name="vip_package"]').forEach(function(radio){
    radio.addEventListener('change', function(){
        const coinsText = this.closest('.package-option').querySelector('div > div').innerText;
        const coins = parseInt(coinsText.match(/([\d,]+)/)[1].replace(/,/g,''));
        const userPoints = {{ isset($userPoints) ? $userPoints : 0 }};

        if(userPoints >= coins){
            document.getElementById('buyVipBtn').style.display = 'block';
            document.getElementById('depositBtn').style.display = 'none';
        } else {
            document.getElementById('buyVipBtn').style.display = 'none';
            document.getElementById('depositBtn').style.display = 'block';
        }
    });
});

</script>

@endsection