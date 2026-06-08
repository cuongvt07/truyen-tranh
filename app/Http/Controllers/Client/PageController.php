<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\CreditPackage;

class PageController extends Controller
{
    private function render(string $title, string $icon, string $content)
    {
        return view('client.pages.static', [
            'pageTitle'   => $title,
            'pageIcon'    => $icon,
            'pageContent' => $content,
        ]);
    }

    public function faq()
    {
        $app = config('app.name');
        return $this->render('Câu hỏi thường gặp', '<i class="fa fa-circle-question"></i>', <<<HTML
            <h2>Tài khoản</h2>
            <p class="faq-q">Làm sao để đăng ký tài khoản?</p>
            <p>Bấm nút <strong>Đăng ký</strong> ở góc trên bên phải, điền tên hiển thị, username, email và mật khẩu là xong.</p>
            <p class="faq-q">Tôi quên mật khẩu thì làm thế nào?</p>
            <p>Vào trang <strong>Đăng nhập</strong> và bấm <em>Quên mật khẩu</em>, hệ thống sẽ gửi hướng dẫn đặt lại qua email.</p>

            <h2>Đọc truyện</h2>
            <p class="faq-q">$app có miễn phí không?</p>
            <p>Phần lớn truyện đọc hoàn toàn miễn phí. Một số chương đặc biệt có thể yêu cầu xu hoặc tài khoản VIP.</p>
            <p class="faq-q">Làm sao để theo dõi truyện yêu thích?</p>
            <p>Mở trang truyện và bấm <strong>Theo dõi</strong>. Truyện sẽ xuất hiện trong mục <em>Đang theo dõi</em> ở trang cá nhân.</p>

            <h2>Xu &amp; VIP</h2>
            <p class="faq-q">Xu dùng để làm gì?</p>
            <p>Xu dùng để mở khoá chương VIP hoặc mua gói Premium ẩn quảng cáo. Bạn có thể nạp xu trong mục <strong>Nạp xu / VIP</strong>.</p>
        HTML);
    }

    public function rules()
    {
        return $this->render('Nội quy', '<i class="fa fa-gavel"></i>', <<<HTML
            <h2>Quy định chung khi bình luận</h2>
            <ul>
                <li>Không spam, quảng cáo, hoặc đăng nội dung không liên quan.</li>
                <li>Tôn trọng người khác, không công kích cá nhân, không phân biệt đối xử.</li>
                <li>Không tiết lộ nội dung quan trọng (spoiler) mà không cảnh báo trước.</li>
                <li>Không đăng link vi phạm bản quyền hoặc nội dung độc hại.</li>
            </ul>
            <h2>Xử lý vi phạm</h2>
            <p>Tài khoản vi phạm nội quy có thể bị cảnh cáo, ẩn bình luận, hoặc cấm vĩnh viễn tuỳ mức độ.</p>
        HTML);
    }

    public function dmca()
    {
        $app = config('app.name');
        return $this->render('DMCA', '<i class="fa fa-shield-halved"></i>', <<<HTML
            <p>$app tôn trọng quyền sở hữu trí tuệ. Nếu bạn là chủ sở hữu bản quyền và cho rằng nội dung trên website vi phạm,
            vui lòng gửi yêu cầu gỡ bỏ kèm các thông tin sau:</p>
            <ul>
                <li>Thông tin liên hệ của bạn (tên, email).</li>
                <li>Mô tả tác phẩm có bản quyền bị vi phạm.</li>
                <li>Đường dẫn cụ thể tới nội dung vi phạm trên website.</li>
                <li>Tuyên bố rằng bạn là chủ sở hữu hoặc được uỷ quyền hợp pháp.</li>
            </ul>
            <p>Chúng tôi sẽ xem xét và xử lý trong thời gian sớm nhất.</p>
        HTML);
    }

    public function terms()
    {
        $app = config('app.name');
        return $this->render('Điều khoản sử dụng', '<i class="fa fa-file-contract"></i>', <<<HTML
            <h2>1. Chấp nhận điều khoản</h2>
            <p>Khi sử dụng $app, bạn đồng ý tuân thủ các điều khoản dưới đây.</p>
            <h2>2. Tài khoản người dùng</h2>
            <p>Bạn chịu trách nhiệm bảo mật thông tin tài khoản và mọi hoạt động phát sinh từ tài khoản của mình.</p>
            <h2>3. Nội dung</h2>
            <p>Nội dung truyện thuộc về tác giả/đơn vị dịch tương ứng. Người dùng không được sao chép, phân phối lại khi chưa được phép.</p>
            <h2>4. Thanh toán</h2>
            <p>Các giao dịch nạp xu và mua VIP là tự nguyện và không hoàn lại, trừ trường hợp lỗi hệ thống.</p>
        HTML);
    }

    public function pricing()
    {
        $pkgs = CreditPackage::active()->get();
        $featuredPkg = $pkgs->firstWhere('is_featured', true) ?? $pkgs->get(2) ?? $pkgs->first();

        $coinPacks = $pkgs->map(fn($p) => [
            'id'        => $p->id,
            'name'      => $p->name,
            'coins'     => $p->coins,
            'price'     => $p->display_price,
            'price_usd' => (float) $p->price_usd,
            'icon'      => $p->icon ?? 'media/payments/1000.jpg',
        ])->toArray();

        $featured = $featuredPkg ? [
            'name'      => $featuredPkg->name,
            'coins'     => $featuredPkg->coins,
            'price'     => $featuredPkg->display_price,
            'price_usd' => (float) $featuredPkg->price_usd,
            'icon'      => $featuredPkg->icon ?? 'media/payments/1000.jpg',
        ] : ($coinPacks[0] ?? null);

        $premiumPackages = getPremiumPackages();
        $premium = !empty($premiumPackages) ? [
            'name'  => $premiumPackages[0]['name'] ?? 'Thành viên Premium',
            'desc'  => ($premiumPackages[0]['days'] ?? 30) . ' ngày',
            'price' => number_format(($premiumPackages[0]['coins'] ?? 700), 0, ',', '.') . ' xu',
            'icon'  => 'media/payments/1.webp',
        ] : [
            'name'  => 'Thành viên Premium',
            'desc'  => '30 ngày',
            'price' => '70.000đ',
            'icon'  => 'media/payments/1.webp',
        ];

        return view('client.pages.pricing', compact('coinPacks', 'premium', 'featured'));
    }

    public function feedback()
    {
        return $this->render('Góp ý', '<i class="fa fa-comment-dots"></i>', <<<HTML
            <p>Chúng tôi luôn lắng nghe ý kiến của bạn để cải thiện trải nghiệm đọc truyện.</p>
            <p>Nếu bạn gặp lỗi, có đề xuất tính năng, hoặc muốn yêu cầu truyện mới, vui lòng liên hệ qua email hỗ trợ
            hoặc để lại bình luận trong các trang truyện tương ứng.</p>
        HTML);
    }
}
