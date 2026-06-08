# Prompt: Nâng cấp giao diện AdminLTE v3.2.0 — Hệ thống Quản trị Truyện

> **Mục tiêu**: Giữ nguyên cấu trúc AdminLTE 3.2.0 (Bootstrap 4), chỉ override CSS/JS để giao diện chuyên nghiệp hơn, phù hợp với nội dung text + ảnh nhiều (quản lý truyện, chương, nhân vật, upload ảnh bìa).

---

## PROMPT CHÍNH (dùng với AI hoặc dev)

```
Bạn là frontend developer chuyên nâng cấp giao diện AdminLTE v3.2.0.
Nhiệm vụ: Tạo file CSS override + JS enhancement để làm đẹp hơn mà KHÔNG thay đổi cấu trúc HTML hiện có.

Hệ thống: Trang quản trị truyện (novel management)
Stack: AdminLTE 3.2.0 + Bootstrap 4 + jQuery
Yêu cầu đặc thù:
- Nội dung nhiều text và ảnh bìa (thumbnail)
- Cần đọc dễ, ít mỏi mắt
- Card/table hiển thị danh sách truyện + chương phải gọn, rõ ràng
- Upload ảnh bìa cần khu vực preview rõ ràng

Tạo cho tôi:
1. File `custom-theme.css` — override màu sắc, typography, spacing của AdminLTE
2. File `custom-ui.js` — enhance UX (tooltip, lazy load ảnh, table highlight, sidebar toggle memory)
3. Hướng dẫn chèn vào layout AdminLTE đúng chỗ

Nguyên tắc thiết kế:
- Màu chủ đạo: deep navy #1e2a3a hoặc dark slate #2d3748 (thay màu mặc định xanh lá AdminLTE)
- Font: 'Be Vietnam Pro' hoặc 'IBM Plex Sans' (Google Fonts) thay Arial mặc định
- Sidebar: dark sidebar, active item có highlight rõ, không dùng màu xanh lá #00c0ef mặc định
- Card: shadow nhẹ, border-radius 8px, header card thanh lịch
- Table: row hover highlight, sticky header, zebra striping nhẹ
- Badge/Label: bo tròn pill style
- Button: flat style, không dùng gradient mặc định Bootstrap

Giữ nguyên: tất cả class AdminLTE (.card, .nav-sidebar, .content-wrapper, v.v.), chỉ override visual
```

---

## CHI TIẾT TỪNG PHẦN

### 1. Màu sắc & Theme Token

```
Override các CSS variable và class màu AdminLTE:

Màu chủ: #1e2a3a (navy dark) — dùng cho sidebar, header
Accent: #4f8ef7 (blue modern) — dùng cho link, active, CTA button  
Success: #38a169 — thay #00a65a
Warning: #d69e2e — thay #f39c12
Danger: #e53e3e — thay #dd4b39
Background body: #f7f8fc — sáng hơn mặc định #f4f6f9
Text chính: #2d3748
Text phụ: #718096
Border: #e2e8f0

Sidebar background: #1a2332
Sidebar text: #a0aec0
Sidebar active: background #4f8ef7, text #ffffff
Navbar-top: #1e2a3a
```

### 2. Typography

```
Google Font import (chèn trước CSS AdminLTE):
  'Be Vietnam Pro' weights: 400, 500, 600, 700
  hoặc 'IBM Plex Sans' weights: 400, 500, 600

Áp dụng:
- body, p, td, li: font-size 14px, line-height 1.65, font-weight 400
- Heading (h1-h4): font-weight 600, letter-spacing -0.01em
- .card-title: font-size 15px, font-weight 600, color #2d3748
- Code/slug field: font-family monospace, background #f0f4f8, border-radius 4px
- Nội dung preview truyện/chương: font-size 15px, line-height 1.8 (dễ đọc)
```

### 3. Sidebar

```
Override .main-sidebar:
- background-color: #1a2332
- width: 240px (thay 250px mặc định nếu muốn gọn hơn)

.nav-sidebar .nav-link:
- border-radius: 6px
- margin: 2px 8px
- padding: 8px 12px
- color: #a0aec0
- transition: all 0.2s ease

.nav-sidebar .nav-link.active, .nav-sidebar .nav-link:hover:
- background: #4f8ef7
- color: #ffffff

.nav-sidebar .nav-link i:
- width: 20px
- color: inherit

.brand-link:
- border-bottom: 1px solid rgba(255,255,255,0.08)
- background: #141c29

Badge count trong sidebar: màu #4f8ef7, bo tròn pill
```

### 4. Card (quan trọng — dùng nhiều cho danh sách truyện)

```
.card:
- border: none
- border-radius: 10px
- box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04)
- transition: box-shadow 0.2s ease

.card:hover (card danh sách truyện):
- box-shadow: 0 4px 12px rgba(0,0,0,0.12)

.card-header:
- background: #ffffff
- border-bottom: 1px solid #e2e8f0
- padding: 14px 20px
- font-weight: 600
- font-size: 14px

.card-body:
- padding: 20px

.card-footer:
- background: #f7f8fc
- border-top: 1px solid #e2e8f0
```

### 5. Table (danh sách truyện/chương)

```
.table thead th:
- background: #f7f8fc
- border-bottom: 2px solid #e2e8f0
- font-size: 12px
- font-weight: 600
- text-transform: uppercase
- letter-spacing: 0.05em
- color: #718096
- padding: 10px 16px
- position: sticky (nếu cần)
- top: 0

.table tbody td:
- padding: 12px 16px
- border-bottom: 1px solid #f0f2f5
- vertical-align: middle
- font-size: 14px

.table tbody tr:hover:
- background: #f7f9ff

.table tbody tr:nth-child(even):
- background: #fafbfc (zebra nhẹ)

Cột ảnh bìa trong table:
- width: 48px height: 64px
- border-radius: 4px
- object-fit: cover
- box-shadow: 0 1px 4px rgba(0,0,0,0.15)

Cột tên truyện:
- font-weight: 500
- color: #2d3748
- max-width: 280px
- overflow: hidden, text-overflow: ellipsis, white-space: nowrap

Cột trạng thái: dùng badge pill
.badge-pill: border-radius: 999px, padding: 3px 10px, font-size: 11px, font-weight: 600
```

### 6. Form & Upload Ảnh Bìa

```
.form-control, .form-select:
- border: 1px solid #e2e8f0
- border-radius: 6px
- padding: 8px 12px
- font-size: 14px
- transition: border-color 0.2s, box-shadow 0.2s

.form-control:focus:
- border-color: #4f8ef7
- box-shadow: 0 0 0 3px rgba(79,142,247,0.15)
- outline: none

.form-group label:
- font-weight: 500
- font-size: 13px
- color: #4a5568
- margin-bottom: 6px

Khu vực upload ảnh bìa:
- Tạo div.cover-upload-zone
- border: 2px dashed #cbd5e0
- border-radius: 10px
- padding: 20px
- text-align: center
- cursor: pointer
- background: #f7f8fc
- transition: all 0.2s

.cover-upload-zone:hover:
- border-color: #4f8ef7
- background: #ebf4ff

Preview ảnh bìa:
- width: 120px, height: 160px (tỷ lệ bìa truyện 3:4)
- border-radius: 8px
- object-fit: cover
- box-shadow: 0 4px 12px rgba(0,0,0,0.2)
- border: 2px solid #e2e8f0
```

### 7. Buttons

```
.btn:
- border-radius: 6px
- font-weight: 500
- font-size: 13px
- padding: 7px 16px
- transition: all 0.2s ease
- letter-spacing: 0.01em

.btn-primary:
- background: #4f8ef7
- border: none
- color: #fff

.btn-primary:hover:
- background: #3a7de8
- transform: translateY(-1px)
- box-shadow: 0 4px 8px rgba(79,142,247,0.3)

.btn-secondary:
- background: #f7f8fc
- border: 1px solid #e2e8f0
- color: #4a5568

.btn-danger: background #e53e3e
.btn-success: background #38a169
.btn-warning: background #d69e2e

.btn-sm: padding 4px 10px, font-size 12px
Icon + text trong button: gap 6px, align-items center
```

### 8. Breadcrumb & Page Header

```
.content-header:
- padding: 12px 20px 8px
- border-bottom: 1px solid #e2e8f0
- background: #ffffff
- margin-bottom: 0

.content-header h1:
- font-size: 18px
- font-weight: 600
- color: #2d3748
- margin: 0

.breadcrumb:
- background: transparent
- margin: 0
- padding: 0
- font-size: 12px

.breadcrumb-item + .breadcrumb-item::before:
- content: "/"
- color: #a0aec0

.breadcrumb-item.active:
- color: #4f8ef7
```

### 9. Stats Widget (Dashboard truyện)

```
Thay .info-box mặc định bằng style mới:
.info-box:
- border-radius: 10px
- box-shadow: 0 1px 3px rgba(0,0,0,0.08)
- border: none
- overflow: hidden

.info-box-icon:
- border-radius: 10px 0 0 10px
- width: 70px
- display: flex
- align-items: center
- justify-content: center
- font-size: 26px

.info-box-content:
- padding: 10px 14px

.info-box-text:
- font-size: 12px
- font-weight: 600
- text-transform: uppercase
- letter-spacing: 0.05em
- color: #718096

.info-box-number:
- font-size: 24px
- font-weight: 700
- color: #2d3748
```

### 10. JS Enhancement

```javascript
// Dán vào custom-ui.js

// 1. Nhớ trạng thái sidebar (mở/đóng)
$(document).ready(function() {
  const sidebarState = localStorage.getItem('sidebar_state');
  if (sidebarState === 'closed') $('body').addClass('sidebar-collapse');
  
  $('[data-widget="pushmenu"]').on('click', function() {
    const isClosed = $('body').hasClass('sidebar-collapse');
    localStorage.setItem('sidebar_state', isClosed ? 'open' : 'closed');
  });
});

// 2. Lazy load ảnh bìa trong table/card
document.addEventListener("DOMContentLoaded", function() {
  const lazyImages = document.querySelectorAll('img[data-src]');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.src = e.target.dataset.src;
        e.target.classList.add('loaded');
        observer.unobserve(e.target);
      }
    });
  });
  lazyImages.forEach(img => observer.observe(img));
});

// 3. Confirm xóa truyện/chương
document.querySelectorAll('.btn-delete').forEach(btn => {
  btn.addEventListener('click', function(e) {
    if (!confirm('Bạn chắc chắn muốn xóa? Hành động này không thể hoàn tác.')) {
      e.preventDefault();
    }
  });
});

// 4. Preview ảnh bìa trước khi upload
document.querySelector('#cover-input')?.addEventListener('change', function() {
  const file = this.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    document.querySelector('#cover-preview').src = e.target.result;
    document.querySelector('#cover-preview').style.display = 'block';
  };
  reader.readAsDataURL(file);
});

// 5. Table row click để vào chi tiết (nếu cần)
document.querySelectorAll('table.clickable-row tbody tr').forEach(row => {
  row.style.cursor = 'pointer';
  row.addEventListener('click', function() {
    const url = this.dataset.href;
    if (url) window.location.href = url;
  });
});
```

---

## CÁCH CHÈN VÀO LAYOUT ADMINLTE

```html
<!-- Trong <head>, SAU các CSS của AdminLTE -->
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/custom-theme.css') }}">

<!-- Trước </body>, SAU các JS của AdminLTE -->
<script src="{{ asset('js/custom-ui.js') }}"></script>
```

> **Lưu ý**: Luôn đặt file custom **sau** CSS/JS gốc của AdminLTE để override đúng cách. Không cần sửa file gốc trong `node_modules` hay `dist/`.

---

## CHECKLIST KHI TRIỂN KHAI

- [ ] Import Google Font `Be Vietnam Pro` hoặc `IBM Plex Sans`
- [ ] Tạo `custom-theme.css` với các override theo phần 1–9
- [ ] Tạo `custom-ui.js` với các enhancement phần 10
- [ ] Test sidebar collapse memory trên mobile
- [ ] Test lazy load ảnh bìa với danh sách truyện nhiều trang
- [ ] Kiểm tra responsive table trên màn hình nhỏ
- [ ] Đảm bảo contrast màu chữ/nền đạt WCAG AA (4.5:1)
- [ ] Kiểm tra form upload ảnh preview đúng tỷ lệ bìa 3:4
