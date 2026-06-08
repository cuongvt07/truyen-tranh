# Prompt: Thiết kế Module Chuẩn — Hệ thống Admin Quản trị Truyện
> Stack: AdminLTE v3.2.0 + Bootstrap 4 + jQuery | Nhóm: Admin + Editor (2–5 người)

---

## PHẦN 1 — NGUYÊN TẮC CHUNG CHO MỌI MODULE

```
Mỗi module trong hệ thống admin truyện phải đầy đủ 4 lớp giao diện:

[LIST]   → Trang danh sách (index)
[FORM]   → Trang thêm mới / chỉnh sửa (create / edit)
[DETAIL] → Trang xem chi tiết / preview (show) — nếu cần
[TRASH]  → Xử lý xóa: soft delete + khôi phục

Mỗi lớp giao diện phải có đủ các thành phần sau:
1. Page Header     — tiêu đề + breadcrumb + action button chính
2. Filter & Search — thanh lọc dữ liệu ngay đầu trang
3. Content Area    — bảng / card / form chứa dữ liệu
4. Bulk Actions    — thao tác hàng loạt (với list)
5. Pagination      — phân trang (với list)
6. Feedback UI     — toast / alert khi thao tác thành công/thất bại
7. Empty State     — giao diện khi không có dữ liệu
8. Loading State   — skeleton / spinner khi đang tải
9. Permission Gate — ẩn/khóa nút theo role (admin vs editor)
```

---

## PHẦN 2 — LAYOUT CHUẨN CHO TRANG DANH SÁCH `[LIST]`

```html
<!-- Cấu trúc HTML chuẩn cho mọi trang index -->

<!-- 1. PAGE HEADER -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-sm-6">
        <h1 class="page-title">[Tên Module]</h1>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
          <li class="breadcrumb-item active">[Tên Module]</li>
        </ol>
      </div>
      <div class="col-sm-6 text-right">
        <!-- Action buttons nhóm -->
        <div class="btn-group">
          <a href="[module]/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm mới
          </a>
          <button class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown">
            <i class="fas fa-ellipsis-v"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-right">
            <a class="dropdown-item" href="#" id="btn-export">
              <i class="fas fa-download"></i> Xuất Excel
            </a>
            <a class="dropdown-item" href="[module]/trash">
              <i class="fas fa-trash-alt"></i> Thùng rác
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 2. FILTER & SEARCH BAR -->
<div class="content-filter card card-outline card-primary mb-3">
  <div class="card-body py-2">
    <form method="GET" class="row align-items-center g-2">
      <!-- Ô tìm kiếm chính -->
      <div class="col-md-4">
        <div class="input-group input-group-sm">
          <input type="text" name="q" class="form-control" placeholder="Tìm kiếm..." value="{{ request('q') }}">
          <div class="input-group-append">
            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
          </div>
        </div>
      </div>
      <!-- Filter theo trạng thái -->
      <div class="col-md-2">
        <select name="status" class="form-control form-control-sm">
          <option value="">Tất cả trạng thái</option>
          <option value="active">Đang hoạt động</option>
          <option value="inactive">Ẩn</option>
          <option value="pending">Chờ duyệt</option>
        </select>
      </div>
      <!-- Filter bổ sung (tùy module) -->
      <div class="col-md-2">
        <select name="[filter_field]" class="form-control form-control-sm">
          <option value="">-- [Bộ lọc] --</option>
        </select>
      </div>
      <!-- Date range -->
      <div class="col-md-2">
        <input type="date" name="date_from" class="form-control form-control-sm" placeholder="Từ ngày">
      </div>
      <!-- Nút reset -->
      <div class="col-md-2">
        <a href="[module]" class="btn btn-sm btn-outline-secondary w-100">
          <i class="fas fa-times"></i> Xóa lọc
        </a>
      </div>
    </form>
  </div>
</div>

<!-- 3. STATS BAR (tóm tắt nhanh) -->
<div class="row mb-3">
  <div class="col-3">
    <div class="info-box info-box-sm">
      <span class="info-box-icon bg-primary"><i class="fas fa-list"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Tổng</span>
        <span class="info-box-number">{{ $total }}</span>
      </div>
    </div>
  </div>
  <!-- Thêm các stat khác tùy module -->
</div>

<!-- 4. MAIN TABLE CARD -->
<div class="card">
  <div class="card-header">
    <!-- Bulk action toolbar (ẩn khi chưa chọn) -->
    <div class="bulk-toolbar d-none" id="bulk-toolbar">
      <span class="selected-count">0 đã chọn</span>
      <div class="btn-group ml-2">
        <button class="btn btn-sm btn-warning" id="bulk-hide">Ẩn</button>
        <button class="btn btn-sm btn-success" id="bulk-show">Hiện</button>
        <button class="btn btn-sm btn-danger" id="bulk-delete">Xóa</button>
      </div>
    </div>
    <!-- Sort info -->
    <div class="card-tools ml-auto">
      <span class="text-muted small">Hiển thị {{ $items->firstItem() }}–{{ $items->lastItem() }} / {{ $items->total() }} kết quả</span>
      <select class="form-control form-control-sm d-inline-block w-auto ml-2" name="per_page" onchange="this.form.submit()">
        <option value="15">15 / trang</option>
        <option value="25">25 / trang</option>
        <option value="50">50 / trang</option>
      </select>
    </div>
  </div>

  <div class="card-body p-0">
    <!-- EMPTY STATE -->
    <div class="empty-state text-center py-5 d-none" id="empty-state">
      <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
      <h5 class="text-muted">Chưa có dữ liệu</h5>
      <p class="text-muted small">Hãy thêm [tên module] đầu tiên</p>
      <a href="[module]/create" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> Thêm ngay
      </a>
    </div>

    <!-- TABLE -->
    <table class="table table-hover table-bordered mb-0 clickable-row">
      <thead>
        <tr>
          <th width="40">
            <input type="checkbox" id="check-all" title="Chọn tất cả">
          </th>
          <th width="60">#</th>
          <!-- Cột sortable -->
          <th>
            <a href="?sort=name&dir={{ $dir === 'asc' ? 'desc' : 'asc' }}" class="sort-link">
              Tên <i class="fas fa-sort{{ $sort === 'name' ? ($dir === 'asc' ? '-up' : '-down') : '' }}"></i>
            </a>
          </th>
          <th>Trạng thái</th>
          <th width="100">Ngày tạo</th>
          <th width="120" class="text-center">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $item)
        <tr data-href="[module]/{{ $item->id }}">
          <td onclick="event.stopPropagation()">
            <input type="checkbox" class="row-check" value="{{ $item->id }}">
          </td>
          <td>{{ $loop->iteration }}</td>
          <td><!-- Nội dung chính --></td>
          <td>
            <!-- Status toggle nhanh -->
            <span class="badge badge-pill badge-{{ $item->status === 'active' ? 'success' : 'secondary' }} status-toggle"
                  data-id="{{ $item->id }}" data-status="{{ $item->status }}" style="cursor:pointer"
                  title="Click để đổi trạng thái">
              {{ $item->status === 'active' ? 'Hiện' : 'Ẩn' }}
            </span>
          </td>
          <td class="text-muted small">{{ $item->created_at->format('d/m/Y') }}</td>
          <td onclick="event.stopPropagation()">
            <div class="btn-group btn-group-sm">
              <a href="[module]/{{ $item->id }}/edit" class="btn btn-outline-primary" title="Sửa">
                <i class="fas fa-edit"></i>
              </a>
              @can('admin')
              <button class="btn btn-outline-danger btn-delete" data-id="{{ $item->id }}" title="Xóa">
                <i class="fas fa-trash"></i>
              </button>
              @endcan
            </div>
          </td>
        </tr>
        @empty
        <!-- empty state hiện qua JS -->
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- 5. PAGINATION -->
  <div class="card-footer d-flex align-items-center justify-content-between">
    <span class="text-muted small">Trang {{ $items->currentPage() }} / {{ $items->lastPage() }}</span>
    {{ $items->withQueryString()->links('pagination::bootstrap-4') }}
  </div>
</div>
```

---

## PHẦN 3 — LAYOUT CHUẨN CHO TRANG FORM `[FORM]`

```html
<!-- Layout 2 cột: form chính (8) + sidebar meta (4) -->
<div class="row">

  <!-- CỘT TRÁI: Nội dung chính -->
  <div class="col-md-8">

    <!-- Card thông tin cơ bản -->
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Thông tin cơ bản</h3>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
            <i class="fas fa-minus"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <!-- Các field chính của module -->
        <div class="form-group">
          <label for="name">Tên <span class="text-danger">*</span></label>
          <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                 value="{{ old('name', $item->name ?? '') }}" required autofocus>
          @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group">
          <label for="slug">Slug (URL)</label>
          <div class="input-group">
            <input type="text" id="slug" name="slug" class="form-control font-monospace"
                   value="{{ old('slug', $item->slug ?? '') }}">
            <div class="input-group-append">
              <button type="button" class="btn btn-outline-secondary" id="btn-gen-slug" title="Tự tạo từ tên">
                <i class="fas fa-sync-alt"></i>
              </button>
            </div>
          </div>
          <small class="form-text text-muted">Để trống sẽ tự tạo từ Tên</small>
        </div>
      </div>
    </div>

    <!-- Card nội dung dài (Rich Text / Upload) -->
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-align-left mr-2"></i>Nội dung</h3>
        <!-- Tab switcher: Editor / Upload file -->
        <ul class="nav nav-pills ml-auto card-tools" id="content-tab">
          <li class="nav-item">
            <a class="nav-link active" href="#tab-editor" data-toggle="tab">
              <i class="fas fa-keyboard"></i> Soạn thảo
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#tab-upload" data-toggle="tab">
              <i class="fas fa-file-upload"></i> Upload file
            </a>
          </li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content">
          <!-- Tab 1: Rich text editor -->
          <div class="tab-pane active" id="tab-editor">
            <textarea id="editor" name="content" class="form-control" rows="20">{{ old('content', $item->content ?? '') }}</textarea>
            <!-- TinyMCE / CKEditor / Quill init qua JS -->
          </div>
          <!-- Tab 2: Upload .docx / .txt -->
          <div class="tab-pane" id="tab-upload">
            <div class="upload-drop-zone" id="file-drop-zone">
              <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
              <p class="mt-2">Kéo thả file vào đây hoặc <label for="file-input" class="text-primary" style="cursor:pointer">chọn file</label></p>
              <p class="text-muted small">Hỗ trợ: .docx, .txt — Tối đa 10MB</p>
              <input type="file" id="file-input" name="upload_file" class="d-none" accept=".docx,.txt">
            </div>
            <div id="upload-preview" class="d-none mt-3">
              <div class="alert alert-success">
                <i class="fas fa-check-circle mr-2"></i>
                <span id="upload-filename"></span> — <span id="upload-wordcount"></span> từ
                <button type="button" class="close" id="btn-clear-upload"><span>&times;</span></button>
              </div>
              <div id="upload-content-preview" class="border rounded p-3 bg-light" style="max-height:300px;overflow-y:auto;font-size:14px;line-height:1.8"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer text-muted small d-flex justify-content-between">
        <span id="word-count">0 từ</span>
        <span id="char-count">0 ký tự</span>
        <span id="reading-time">~0 phút đọc</span>
        <span id="last-saved"></span>
      </div>
    </div>

    <!-- Card SEO (thu gọn mặc định) -->
    <div class="card card-secondary card-outline collapsed-card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-search mr-2"></i>SEO & Meta</h3>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
        </div>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label>Meta Title</label>
          <input type="text" name="meta_title" class="form-control" maxlength="60"
                 value="{{ old('meta_title', $item->meta_title ?? '') }}">
          <small class="text-muted"><span id="meta-title-count">0</span>/60 ký tự</small>
        </div>
        <div class="form-group">
          <label>Meta Description</label>
          <textarea name="meta_description" class="form-control" rows="2" maxlength="160">{{ old('meta_description', $item->meta_description ?? '') }}</textarea>
          <small class="text-muted"><span id="meta-desc-count">0</span>/160 ký tự</small>
        </div>
      </div>
    </div>

  </div><!-- /col-md-8 -->

  <!-- CỘT PHẢI: Sidebar meta -->
  <div class="col-md-4">

    <!-- Card Publish Actions -->
    <div class="card card-primary card-outline sticky-top" style="top: 70px">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-paper-plane mr-2"></i>Xuất bản</h3>
      </div>
      <div class="card-body">
        <!-- Trạng thái -->
        <div class="form-group">
          <label>Trạng thái</label>
          <select name="status" class="form-control">
            <option value="draft" {{ old('status', $item->status ?? '') === 'draft' ? 'selected' : '' }}>Bản nháp</option>
            <option value="active" {{ old('status', $item->status ?? '') === 'active' ? 'selected' : '' }}>Xuất bản</option>
            <option value="pending" {{ old('status', $item->status ?? '') === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
          </select>
        </div>
        <!-- Lên lịch -->
        <div class="form-group">
          <label>Lên lịch xuất bản</label>
          <input type="datetime-local" name="published_at" class="form-control"
                 value="{{ old('published_at', $item->published_at?->format('Y-m-d\TH:i') ?? '') }}">
        </div>
        <!-- Nổi bật -->
        <div class="form-group">
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" value="1"
                   {{ old('is_featured', $item->is_featured ?? 0) ? 'checked' : '' }}>
            <label class="custom-control-label" for="is_featured">Đánh dấu nổi bật</label>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <div class="d-flex justify-content-between">
          <a href="[module]" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Hủy
          </a>
          <div class="btn-group">
            <!-- Lưu nháp -->
            <button type="submit" name="action" value="draft" class="btn btn-outline-primary">
              <i class="fas fa-save"></i> Lưu nháp
            </button>
            <!-- Xuất bản -->
            <button type="submit" name="action" value="publish" class="btn btn-primary">
              <i class="fas fa-check"></i> Xuất bản
            </button>
          </div>
        </div>
        <!-- Auto-save indicator -->
        <div class="text-center mt-2">
          <small class="text-muted" id="autosave-status">
            <i class="fas fa-circle text-success" style="font-size:8px"></i> Tự lưu mỗi 60 giây
          </small>
        </div>
      </div>
    </div>

    <!-- Card Ảnh bìa / Thumbnail -->
    <div class="card card-secondary card-outline">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-image mr-2"></i>Ảnh bìa</h3>
      </div>
      <div class="card-body">
        <!-- Preview hiện tại -->
        <div id="cover-current" class="{{ isset($item->cover) ? '' : 'd-none' }} text-center mb-3">
          <img id="cover-preview" src="{{ $item->cover ?? '' }}"
               style="width:120px;height:160px;object-fit:cover;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.2)">
          <div class="mt-2">
            <button type="button" class="btn btn-sm btn-outline-danger" id="btn-remove-cover">
              <i class="fas fa-times"></i> Xóa ảnh
            </button>
          </div>
        </div>
        <!-- Upload zone -->
        <div class="cover-upload-zone text-center" id="cover-drop-zone">
          <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
          <p class="mt-2 mb-1 small">Kéo thả hoặc <label for="cover-input" class="text-primary" style="cursor:pointer">chọn ảnh</label></p>
          <p class="text-muted" style="font-size:11px">JPG, PNG, WebP — Tỷ lệ 3:4 — Max 2MB</p>
          <input type="file" id="cover-input" name="cover" class="d-none" accept="image/*">
        </div>
      </div>
    </div>

    <!-- Card phân loại (Thể loại / Tag / Tác giả) -->
    <div class="card card-secondary card-outline">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-tags mr-2"></i>Phân loại</h3>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label>Thể loại</label>
          <select name="categories[]" class="form-control select2" multiple data-placeholder="Chọn thể loại...">
            @foreach($categories as $cat)
              <option value="{{ $cat->id }}" {{ in_array($cat->id, old('categories', $item->categories->pluck('id')->toArray() ?? [])) ? 'selected' : '' }}>
                {{ $cat->name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label>Tag</label>
          <input type="text" name="tags" class="form-control tags-input"
                 value="{{ old('tags', $item->tags_string ?? '') }}" placeholder="Nhập tag, phân cách bằng dấu phẩy">
          <small class="text-muted">VD: hành động, phiêu lưu, hài hước</small>
        </div>
        <div class="form-group mb-0">
          <label>Tác giả</label>
          <select name="author_id" class="form-control select2" data-placeholder="Chọn tác giả...">
            <option value=""></option>
            @foreach($authors as $author)
              <option value="{{ $author->id }}" {{ old('author_id', $item->author_id ?? '') == $author->id ? 'selected' : '' }}>
                {{ $author->name }}
              </option>
            @endforeach
          </select>
        </div>
      </div>
    </div>

  </div><!-- /col-md-4 -->
</div>
```

---

## PHẦN 4 — TỪNG MODULE CỤ THỂ

### MODULE 1: TRUYỆN

```
Trang LIST — các cột table:
  [checkbox] | [ảnh bìa 48x64] | [tên truyện + slug] | [tác giả] | [thể loại badge] | [số chương] | [lượt xem] | [trạng thái pill] | [ngày cập nhật] | [thao tác]

Filter thêm: theo thể loại, theo tác giả, theo trạng thái (đang ra / hoàn thành / tạm dừng), theo lượt xem (nhiều/ít)

Stats bar: Tổng truyện | Đang ra | Hoàn thành | Chương hôm nay

Trang FORM — field đặc thù:
  - Tên truyện (required)
  - Tên khác / tên gốc (alias)
  - Tác giả (select2 + nút "Thêm tác giả mới" inline modal)
  - Thể loại (select2 multiple)
  - Tag (tags input)
  - Trạng thái xuất bản: Đang ra | Hoàn thành | Tạm dừng | Dropped
  - Nguồn gốc: Dịch | Sáng tác | Convert
  - Năm ra mắt
  - Giới thiệu (rich text, giới hạn 2000 từ)
  - Ảnh bìa (upload + preview 3:4)
  - SEO (collapsed)

Tab bổ sung trên trang EDIT:
  [Thông tin] | [Chương (số chương, add nhanh)] | [Thống kê (views, reads)] | [Lịch sử sửa]
```

### MODULE 2: CHƯƠNG

```
Trang LIST — các cột table:
  [checkbox] | [STT chương] | [Tên chương] | [Truyện thuộc về] | [Số từ] | [Lượt đọc] | [Trạng thái] | [Ngày đăng] | [Thao tác]

Filter thêm: theo truyện (select2), theo trạng thái, theo ngày đăng

Action đặc biệt:
  - Nút "Thêm hàng loạt chương" → modal upload nhiều file .txt/.docx
  - Nút "Sắp xếp lại" → drag-drop reorder (Sortable.js)
  - Nút "Xuất bản tất cả draft"

Trang FORM — field đặc thù:
  - Truyện (select2, disabled nếu vào từ trang truyện)
  - Số chương (auto-increment, có thể sửa)
  - Tên chương (optional — "Chương 1: Khởi đầu")
  - Nội dung: Tab [Soạn thảo rich text] | Tab [Upload .docx/.txt]
  - Footer stats: số từ | số ký tự | ~X phút đọc
  - Giá (nếu có cơ chế chapter trả phí): miễn phí / coin
  - Lên lịch đăng

Auto-save:
  - Draft tự lưu localStorage mỗi 30 giây
  - Hiển thị "Đã lưu lúc 14:23" ở footer card
  - Khôi phục draft khi mở lại form
```

### MODULE 3: ẢNH / MEDIA

```
Trang LIST — dạng GRID (không dùng table):
  - Grid 5 cột (responsive: 2 mobile, 3 tablet, 5 desktop)
  - Mỗi item: thumbnail + tên file + dung lượng + checkbox overlay khi hover
  - Hover: hiện nút [Xem] [Copy URL] [Xóa]
  - Filter: theo loại (ảnh bìa / ảnh nội dung / avatar), theo tháng upload
  - View toggle: [Grid] ↔ [List]

Upload:
  - Drag & drop zone lớn ở đầu trang
  - Multi-file upload (tối đa 20 file/lần)
  - Progress bar từng file
  - Preview ngay sau upload
  - Tự động đặt tên theo slug (sanitize filename)
  - Resize tự động: bìa → 300x400, thumbnail → 150x200

Thao tác hàng loạt:
  - Chọn nhiều → Xóa hàng loạt
  - Chọn nhiều → Copy URL danh sách
  - Tìm kiếm theo tên file

Media picker modal (dùng trong form Truyện/Chương):
  - Popup chọn ảnh từ thư viện đã upload
  - Tab: [Thư viện] | [Upload mới]
  - Search trong modal
```

### MODULE 4: TÁC GIẢ

```
Trang LIST — table:
  [checkbox] | [avatar 40x40 tròn] | [tên tác giả] | [quốc gia/nguồn gốc] | [số truyện] | [trạng thái] | [thao tác]

Trang FORM — field:
  - Tên tác giả (required)
  - Tên gốc / tên khác
  - Quốc gia (select)
  - Avatar (upload ảnh tròn — preview circular)
  - Tiểu sử ngắn (textarea, max 500 ký tự)
  - Website / mạng xã hội (repeater field: loại + URL)
  - Trạng thái: active / inactive

Sidebar EDIT:
  - Danh sách truyện đang quản lý (link nhanh)
  - Tổng số truyện, tổng chương, tổng lượt đọc
```

### MODULE 5: THỂ LOẠI / TAG

```
Layout đặc biệt — chia 2 panel:
  Panel trái (col-5): Danh sách thể loại (table nhỏ, có inline edit)
  Panel phải (col-7): Form thêm/sửa thể loại (hiện khi click)

Table field:
  [drag handle] | [tên] | [slug] | [màu badge] | [số truyện] | [thao tác inline]

Form field:
  - Tên thể loại
  - Slug (auto)
  - Mô tả ngắn
  - Màu hiển thị (color picker) — dùng cho badge
  - Icon (chọn từ FontAwesome picker)
  - Thể loại cha (cho phép phân cấp 2 cấp)

Tab riêng: QUẢN LÝ TAG
  - Input thêm tag nhanh (enter để thêm)
  - Danh sách tag dạng cloud (kích thước theo tần suất dùng)
  - Gộp tag (merge): chọn nhiều tag → gộp thành 1
```

### MODULE 6: NGƯỜI DÙNG

```
Trang LIST — table:
  [checkbox] | [avatar tròn] | [tên + email] | [role badge] | [số bình luận] | [lần đăng nhập cuối] | [trạng thái] | [thao tác]

Filter: theo role (admin/editor/member), theo trạng thái (active/banned), theo ngày đăng ký

Trang FORM — chia tab:
  Tab [Thông tin]: tên, email, số điện thoại, avatar, bio
  Tab [Phân quyền]: role, permissions cụ thể (checkbox matrix)
  Tab [Bảo mật]: đặt lại mật khẩu, lịch sử đăng nhập, 2FA status

Permission matrix (tab Phân quyền):
  Dạng bảng: Module (hàng) × Quyền (cột: Xem / Thêm / Sửa / Xóa / Duyệt)
  Tick từng ô để gán quyền chi tiết

Thao tác nhanh từ list:
  - Ban/Unban user (không cần vào trang edit)
  - Reset password → gửi email
  - Impersonate (đăng nhập thay, chỉ super admin)
```

### MODULE 7: BÌNH LUẬN

```
Trang LIST — table focus vào moderation:
  [checkbox] | [nội dung bình luận (truncate 100 ký tự)] | [người dùng] | [thuộc chương/truyện] | [trạng thái] | [ngày] | [thao tác]

Trạng thái bình luận: Chờ duyệt (pending) | Đã duyệt | Spam | Đã xóa

Filter quan trọng:
  - Tab nhanh: [Tất cả] [Chờ duyệt (badge đếm)] [Spam] [Đã xóa]
  - Filter theo truyện/chương
  - Filter theo người dùng

Moderation tools:
  - Duyệt / từ chối ngay từ list (không cần vào trang detail)
  - Bulk approve / bulk spam / bulk delete
  - Xem context: click expand → hiện bình luận cha + trả lời
  - Reply từ admin ngay trong list (inline form)

Không cần trang CREATE (bình luận chỉ do user tạo)
```

### MODULE 8: BÁO CÁO & THỐNG KÊ

```
Dashboard layout — không dùng table, dùng card + chart:

Row 1 — KPI cards (4 cột):
  Tổng lượt xem hôm nay | Truyện được đọc nhiều nhất | Chương mới trong tuần | Người dùng mới

Row 2 — Charts (2 cột):
  Col-8: Line chart "Lượt xem 30 ngày" (Chart.js)
  Col-4: Doughnut chart "Tỷ lệ thể loại"

Row 3 — Tables (2 cột):
  Col-6: Top 10 truyện lượt xem cao nhất (mini table)
  Col-6: Top 10 chương được đọc nhiều nhất

Row 4 — Activity feed:
  Timeline hoạt động gần đây (chương mới, truyện mới, bình luận mới)

Filter toàn trang:
  Date range picker: Hôm nay | 7 ngày | 30 ngày | Tùy chọn
  So sánh kỳ trước: toggle bật/tắt

Export:
  - Xuất báo cáo PDF
  - Xuất dữ liệu Excel theo khoảng thời gian
```

---

## PHẦN 5 — THÀNH PHẦN UI DÙNG LẠI (REUSABLE)

### Toast Notification (feedback)
```javascript
// Dùng Toastr.js hoặc tự build với Bootstrap toast
function showToast(type, message) {
  // type: 'success' | 'error' | 'warning' | 'info'
  toastr[type](message, '', {
    positionClass: 'toast-top-right',
    timeOut: 3000,
    progressBar: true,
    closeButton: true
  });
}
// Gọi sau mỗi action:
showToast('success', 'Lưu thành công!');
showToast('error', 'Có lỗi xảy ra, vui lòng thử lại.');
```

### Confirm Delete Modal
```html
<!-- Modal confirm dùng chung cho mọi module -->
<div class="modal fade" id="modal-delete" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i>Xác nhận xóa</h5>
      </div>
      <div class="modal-body text-center">
        <p>Bạn có chắc muốn xóa <strong id="delete-item-name"></strong>?</p>
        <p class="text-muted small">Dữ liệu sẽ vào thùng rác, có thể khôi phục sau.</p>
      </div>
      <div class="modal-footer justify-content-between">
        <button class="btn btn-outline-secondary" data-dismiss="modal">Hủy</button>
        <form id="delete-form" method="POST">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-danger">Xóa</button>
        </form>
      </div>
    </div>
  </div>
</div>
```

### Status Toggle (đổi trạng thái nhanh)
```javascript
// Click vào badge trạng thái → đổi active/inactive không cần reload trang
$(document).on('click', '.status-toggle', function() {
  const id = $(this).data('id');
  const current = $(this).data('status');
  const el = $(this);

  $.post(`/admin/[module]/${id}/toggle-status`, { _token: CSRF_TOKEN }, function(res) {
    if (res.success) {
      el.data('status', res.new_status);
      el.text(res.new_status === 'active' ? 'Hiện' : 'Ẩn');
      el.removeClass('badge-success badge-secondary')
        .addClass(res.new_status === 'active' ? 'badge-success' : 'badge-secondary');
      showToast('success', 'Đã cập nhật trạng thái');
    }
  });
});
```

### Bulk Action Handler
```javascript
// Chọn tất cả / chọn từng dòng → hiện bulk toolbar
$('#check-all').on('change', function() {
  $('.row-check').prop('checked', this.checked);
  updateBulkToolbar();
});
$('.row-check').on('change', updateBulkToolbar);

function updateBulkToolbar() {
  const count = $('.row-check:checked').length;
  if (count > 0) {
    $('#bulk-toolbar').removeClass('d-none');
    $('.selected-count').text(count + ' đã chọn');
  } else {
    $('#bulk-toolbar').addClass('d-none');
  }
}
```

### Auto-save Draft (Form)
```javascript
// Tự lưu form vào localStorage mỗi 30 giây
const AUTOSAVE_KEY = 'draft_[module]_' + (itemId || 'new');

function autoSave() {
  const data = {};
  $('form').serializeArray().forEach(f => data[f.name] = f.value);
  localStorage.setItem(AUTOSAVE_KEY, JSON.stringify(data));
  const time = new Date().toLocaleTimeString('vi-VN');
  $('#autosave-status').html(`<i class="fas fa-check text-success"></i> Đã lưu nháp lúc ${time}`);
}

// Khôi phục draft khi mở form
const saved = localStorage.getItem(AUTOSAVE_KEY);
if (saved && !itemId) { // Chỉ khôi phục khi tạo mới
  // Hiện banner thông báo có draft
  $('#draft-restore-banner').removeClass('d-none');
}

setInterval(autoSave, 30000);
```

### Word Count & Reading Time
```javascript
function updateWordCount(text) {
  const words = text.trim().split(/\s+/).filter(w => w).length;
  const chars = text.length;
  const minutes = Math.ceil(words / 200); // ~200 từ/phút
  $('#word-count').text(words.toLocaleString() + ' từ');
  $('#char-count').text(chars.toLocaleString() + ' ký tự');
  $('#reading-time').text('~' + minutes + ' phút đọc');
}
```

---

## PHẦN 6 — PHÂN QUYỀN ROLE (ADMIN vs EDITOR)

```
ADMIN: toàn quyền mọi module
EDITOR: được phép

  ✅ Thêm/sửa Truyện, Chương, Tác giả, Thể loại
  ✅ Upload Ảnh/Media
  ✅ Duyệt Bình luận
  ✅ Xem Báo cáo

  ❌ Xóa vĩnh viễn bất kỳ thứ gì
  ❌ Quản lý Người dùng
  ❌ Thay đổi Role/Permission
  ❌ Cấu hình hệ thống

Implement trong Blade:
  @can('admin') ... @endcan          → chỉ Admin thấy
  @can('editor-or-above') ... @endcan → cả hai thấy

Implement trong UI:
  - Ẩn hẳn nút "Xóa" với Editor (không chỉ disable)
  - Ẩn link menu "Người dùng" trong sidebar với Editor
  - Hiện badge role trong navbar (góc phải) để nhắc nhở
```

---

## PHẦN 7 — CHECKLIST HOÀN CHỈNH MODULE

```
Trước khi đánh dấu 1 module là DONE, kiểm tra:

UI/UX:
  [ ] Trang LIST đầy đủ: filter, sort, bulk action, pagination, empty state
  [ ] Trang FORM layout 2 cột (8+4), sticky sidebar actions
  [ ] Toast notification sau mọi action (success + error)
  [ ] Confirm modal trước khi xóa
  [ ] Status toggle không cần reload trang
  [ ] Loading state khi submit form (disable button + spinner)
  [ ] Responsive hoạt động trên tablet/mobile

Tính năng:
  [ ] Soft delete + trang Thùng rác + khôi phục
  [ ] Auto-save draft (form thêm/sửa nội dung dài)
  [ ] Export danh sách Excel
  [ ] Phân quyền admin/editor đúng chỗ
  [ ] Slug tự tạo từ tên + nút tạo lại

Performance:
  [ ] Lazy load ảnh trong table/grid
  [ ] Pagination (không dùng load all)
  [ ] Select2 cho dropdown nhiều lựa chọn
  [ ] Debounce ô tìm kiếm (300ms)
```
