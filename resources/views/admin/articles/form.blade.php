<div class="box box-info padding-1">
    <div class="box-body">
        <div class="form-group required">
            <label for="title">Tên truyện</label>
            <input type="text" name="title" id="title" value="{{ old('title', $article->title) }}"
                   class="form-control{{ $errors->has('title') ? ' is-invalid' : '' }}">
            @if ($errors->has('title'))
                <div class="invalid-feedback">{{ $errors->first('title') }}</div>
            @endif
        </div>
        <div class="form-group">
            <label for="slug">Slug</label>
            <input type="text" name="slug" id="slug"
                   value="{{ old('slug', optional($article->slug)->slug) }}"
                   class="form-control{{ $errors->has('slug') ? ' is-invalid' : '' }}"
                   placeholder="tu-dong-tao-theo-ten-truyen">
            <small class="form-text text-muted">URL: /articles/<span id="slug-preview">{{ old('slug', optional($article->slug)->slug) }}</span></small>
            @if ($errors->has('slug'))
                <div class="invalid-feedback">{{ $errors->first('slug') }}</div>
            @endif
        </div>
        <div class="form-group">
            <label for="description">Mô tả</label>
            <textarea name="description" id="description"
                      class="form-control{{ $errors->has('description') ? ' is-invalid' : '' }}">{{ old('description', $article->description) }}</textarea>
            @if ($errors->has('description'))
                <div class="invalid-feedback">{{ $errors->first('description') }}</div>
            @endif
        </div>

        {{-- Thông tin mở rộng (đồng bộ với trang client) --}}
        <div class="row">
            <div class="form-group col-md-6">
                <label for="alt_title">Tên khác (alternative)</label>
                <input type="text" name="alt_title" id="alt_title" class="form-control" value="{{ old('alt_title', $article->alt_title) }}">
            </div>
            <div class="form-group col-md-6">
                <label for="illustrator">Hoạ sĩ minh hoạ</label>
                <input type="text" name="illustrator" id="illustrator" class="form-control" value="{{ old('illustrator', $article->illustrator) }}">
            </div>
            <div class="form-group col-md-4">
                <label for="novel_type">Loại</label>
                <select name="novel_type" id="novel_type" class="form-control">
                    <option value="0" {{ old('novel_type', $article->novel_type)==0?'selected':'' }}>Web Novel</option>
                    <option value="1" {{ old('novel_type', $article->novel_type)==1?'selected':'' }}>Light Novel</option>
                    <option value="2" {{ old('novel_type', $article->novel_type)==2?'selected':'' }}>Truyện xuất bản</option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="country">Quốc gia</label>
                <select name="country" id="country" class="form-control">
                    <option value="">—</option>
                    @foreach($countries ?? [] as $c)
                        <option value="{{ $c->id }}" {{ old('country', $article->country)==$c->id?'selected':'' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="year_of_release">Năm phát hành</label>
                <input type="number" name="year_of_release" id="year_of_release" min="1900" max="2100" class="form-control" value="{{ old('year_of_release', $article->year_of_release) }}">
            </div>
            <div class="form-group col-md-4">
                <label for="view">Lượt xem</label>
                <input type="number" name="view" id="view" min="0" class="form-control" value="{{ old('view', $article->view ?? 0) }}">
                <small class="form-text text-muted">Số set thủ công; lượt xem thực tế sẽ tự cộng thêm vào.</small>
            </div>
            <div class="form-group col-md-12">
                <label for="tags">Tags <small class="text-muted">(phân cách bằng dấu phẩy)</small></label>
                @php $articleTags = $article->exists ? $article->tags->pluck('name')->implode(', ') : ''; @endphp
                <input type="text" name="tags" id="tags" class="form-control" value="{{ old('tags', $articleTags) }}" placeholder="Xuyên không, Trùng sinh...">
            </div>
            <div class="form-group col-md-12 d-flex" style="gap:24px">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_completed" name="is_completed" value="1" {{ old('is_completed', $article->is_completed) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="is_completed">Đã hoàn thành</label>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_adult" name="is_adult" value="1" {{ old('is_adult', $article->is_adult ?? false) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="is_adult">Nội dung 18+</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="genres">Thể loại</label>
            <div class="row">
                @foreach($genres as $genre)
                    <div class="col-md-2">
                        <div class="form-check">
                            <input type="checkbox" name="genres[]" value="{{ $genre->id }}"
                                   {{ in_array($genre->id, old('genres', $selectedGenres)) ? 'checked' : '' }} class="form-check-input">
                            <label class="form-check-label">{{ $genre->name }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="form-group">
            <label for="genres">Tác giả</label>
            <div class="row">
                @foreach($authors as $author)
                    <div class="col-md-2">
                        <div class="form-check">
                            <input type="checkbox" name="authors[]" value="{{ $author->id }}"
                                   {{ in_array($author->id, old('authors', $selectedAuthors)) ? 'checked' : '' }} class="form-check-input">
                            <label class="form-check-label">{{ $author->name }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @php
            $selectedSimilarArticleIds = collect(old('similar_article_ids', $article->similar_article_ids ?? []))->map(function ($id) { return (int) $id; })->all();
            $selectedTranslationArticleIds = collect(old('translation_request_article_ids', $article->translation_request_article_ids ?? []))->map(function ($id) { return (int) $id; })->all();
            $selectedRelatedGenreIds = collect(old('related_genre_ids', $article->related_genre_ids ?? []))->map(function ($id) { return (int) $id; })->all();
        @endphp
        <div class="card card-info card-outline detail-blocks-card">
            <div class="card-header">
                <h3 class="card-title">Cấu hình 3 khối trang chi tiết</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-4">
                        <label for="similar_article_ids">Similar</label>
                        <select name="similar_article_ids[]" id="similar_article_ids" class="form-control detail-block-select" multiple>
                            @foreach(($articleOptions ?? collect()) as $option)
                                <option value="{{ $option->id }}" {{ in_array($option->id, $selectedSimilarArticleIds) ? 'selected' : '' }}>
                                    {{ $option->title }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Để trống sẽ tự lấy truyện cùng tác giả và cùng thể loại.</small>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="translation_request_article_ids">Translation requests</label>
                        <select name="translation_request_article_ids[]" id="translation_request_article_ids" class="form-control detail-block-select" multiple>
                            @foreach(($articleOptions ?? collect()) as $option)
                                <option value="{{ $option->id }}" {{ in_array($option->id, $selectedTranslationArticleIds) ? 'selected' : '' }}>
                                    {{ $option->title }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Để trống sẽ tự lấy top truyện mới nhất.</small>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="related_genre_ids">Related Collections</label>
                        <select name="related_genre_ids[]" id="related_genre_ids" class="form-control detail-block-select" multiple>
                            @foreach($genres as $genre)
                                <option value="{{ $genre->id }}" {{ in_array($genre->id, $selectedRelatedGenreIds) ? 'selected' : '' }}>
                                    {{ $genre->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Để trống sẽ lấy các thể loại của truyện hiện tại.</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            @php $coverCurrent = ($article->cover_image && $article->cover_image !== '/images/articles/default.jpg') ? $article->cover_image : null; @endphp
            <x-admin.image-upload name="cover_image" label="Ảnh bìa" :height="200"
                :current="$coverCurrent"
                urlName="cover_image_url" :urlValue="old('cover_image_url', $coverCurrent)" />
            @error('cover_image')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label for="status">Link Audio Youtube</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control{{ $errors->has('affi_link') ? ' is-invalid' : '' }}"
                       name="affi_link" id="affi_link"
                       value="{{ old('affi_link', $article->affi_link) }}"
                       placeholder="https://www.youtube.com/watch?v=...">
        </div>
        </div>
        {{-- Credit / paywall settings --}}
        <hr>
        <h5>Nhóm dịch</h5>
        <div class="row">
            <div class="form-group col-md-6">
                <label for="team_id">Gán cho nhóm dịch</label>
                <select name="team_id" id="team_id" class="form-control">
                    <option value="">— Không có —</option>
                    @foreach(\App\Models\Team::where('status', \App\Models\Team::STATUS_APPROVED)->orderBy('name')->get(['id','name']) as $team)
                        <option value="{{ $team->id }}" {{ old('team_id', $article->team_id) == $team->id ? 'selected' : '' }}>
                            {{ $team->name }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Nhóm dịch phụ trách truyện này.</small>
            </div>
        </div>

        <hr>
        <h5>Cài đặt mở khoá bằng Credit</h5>
        <div class="row">
            <div class="form-group col-md-6">
                <label for="credit_start_chapter">Thu credit từ chương số</label>
                <input type="number" name="credit_start_chapter" id="credit_start_chapter"
                       min="1" class="form-control{{ $errors->has('credit_start_chapter') ? ' is-invalid' : '' }}"
                       value="{{ old('credit_start_chapter', $article->credit_start_chapter) }}"
                       placeholder="Để trống = miễn phí toàn bộ">
                <small class="form-text text-muted">Ví dụ: 16 → chương 1–15 miễn phí, từ chương 16 trở đi phải dùng credit.</small>
                @if ($errors->has('credit_start_chapter'))
                    <div class="invalid-feedback">{{ $errors->first('credit_start_chapter') }}</div>
                @endif
            </div>
            <div class="form-group col-md-6">
                <label for="credit_per_chapter">Credit / chương (mặc định)</label>
                <input type="number" name="credit_per_chapter" id="credit_per_chapter"
                       min="0" class="form-control{{ $errors->has('credit_per_chapter') ? ' is-invalid' : '' }}"
                       value="{{ old('credit_per_chapter', $article->credit_per_chapter ?? 0) }}"
                       placeholder="0">
                <small class="form-text text-muted">Từng chương có thể ghi đè riêng trong trang sửa chương.</small>
                @if ($errors->has('credit_per_chapter'))
                    <div class="invalid-feedback">{{ $errors->first('credit_per_chapter') }}</div>
                @endif
            </div>
        </div>

    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Xác nhận') }}</button>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
    <style>
        .detail-block-select + .select2-container{width:100%!important}
        .select2-container--bootstrap4 .select2-selection--multiple{
            min-height:38px;
            border-radius:.25rem;
        }
        /* tag đơn giản: x đứng trước tên, không viền ô × */
        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice{
            background:#f1f3f5;
            border:1px solid #dee2e6;
            color:#343a40;
            border-radius:.25rem;
            padding:1px 8px;
            display:inline-flex;
            align-items:center;
        }
        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove{
            border:none!important;
            background:transparent!important;
            padding:0!important;
            margin-right:6px;
            color:#868e96;
            font-size:1rem;
            line-height:1;
        }
        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove:hover{color:#dc3545;background:transparent!important}
    </style>
@endpush

@section('ArticleScripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            previewImage();
            bindSlugField();
            $('.detail-block-select').select2({
                theme: 'bootstrap4',
                width: '100%',
                allowClear: true,
                closeOnSelect: false,
                placeholder: 'Tìm và chọn...',
                language: {
                    noResults: function () {
                        return 'Không tìm thấy kết quả';
                    }
                }
            });
        });

        function previewImage() {
            let imgPreview = document.querySelector('#preview_image');
            let coverImageUrl = document.querySelector('input[name="cover_image_url"]');

            $('input[name="cover_image"]').on('change', function (event) {
                if (event.target.files && event.target.files[0]) {
                    let reader = new FileReader();
                    reader.onload = function (e) {
                        if (imgPreview) {
                            imgPreview.src = e.target.result;
                        }
                    };
                    reader.readAsDataURL(event.target.files[0]);
                }
            });

            $('#cover_image_url_preview').on('input', debounce(function () {
                if (imgPreview) {
                    let url = $(this).val();
                    $.ajax({
                        url: url,
                        method: 'GET',
                        success: function (data, textStatus, jqXHR) {
                            imgPreview.src = coverImageUrl.value = url;
                            console.log(imgPreview.src);
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            if (jqXHR.status === 404) {
                                let defaultImage = '/images/articles/default.jpg';
                                imgPreview.src = coverImageUrl.value = defaultImage;
                            } else {
                                console.log('Lỗi:', errorThrown);
                            }
                        }
                    });
                }
            }, 250));
        }

        function slugifyText(value) {
            return String(value || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/đ/g, 'd')
                .replace(/Đ/g, 'd')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .replace(/-{2,}/g, '-');
        }

        function bindSlugField() {
            let titleInput = document.querySelector('#title');
            let slugInput = document.querySelector('#slug');
            let preview = document.querySelector('#slug-preview');

            if (!titleInput || !slugInput) {
                return;
            }

            let manualSlug = slugInput.value.length > 0;

            function updatePreview() {
                if (preview) {
                    preview.textContent = slugInput.value || slugifyText(titleInput.value);
                }
            }

            titleInput.addEventListener('input', function () {
                if (!manualSlug) {
                    slugInput.value = slugifyText(titleInput.value);
                }
                updatePreview();
            });

            slugInput.addEventListener('input', function () {
                manualSlug = slugInput.value.length > 0;
                slugInput.value = slugifyText(slugInput.value);
                updatePreview();
            });

            if (!slugInput.value) {
                slugInput.value = slugifyText(titleInput.value);
            }
            updatePreview();
        }
    </script>
@endsection
