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
            <label for="description">Mô tả</label>
            <textarea name="description" id="description"
                      class="form-control{{ $errors->has('description') ? ' is-invalid' : '' }}">{{ old('description', $article->description) }}</textarea>
            @if ($errors->has('description'))
                <div class="invalid-feedback">{{ $errors->first('description') }}</div>
            @endif
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
        <div class="form-group">
            <label for="cover_image">Ảnh bìa</label>
            <div class="m-5">
                <img id="preview_image" src="{{ old('cover_image_url', $article->cover_image) }}"
                     alt="{{ old('title', $article->title) }}" width="200px">

            </div>
            <ul class="nav nav-tabs" id="myTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab1-tab" data-toggle="tab" href="#tab1" role="tab"
                       aria-controls="tab1" aria-selected="true">Nhập URL ảnh</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab2-tab" data-toggle="tab" href="#tab2" role="tab" aria-controls="tab2"
                       aria-selected="false">Tải lên tệp</a>
                </li>
            </ul>
            <div class="tab-content" id="myTabsContent">
                <div class="tab-pane fade show active" id="tab1" role="tabpanel" aria-labelledby="tab1-tab">
                    <div class="form-group mt-4">
                        <input type="text" class="form-control"
                               placeholder="https://example.com/image.jpg"
                               id="cover_image_url_preview"
                               name="cover_image_url_preview"
                               value="{{ old('cover_image_url_preview', $article->cover_image) }}">
                        <input type="hidden" name="cover_image_url"
                               value="{{ old('cover_image_url', $article->cover_image) }}">
                    </div>
                </div>
                <div class="tab-pane fade" id="tab2" role="tabpanel" aria-labelledby="tab2-tab">
                    <div class="form-group mt-4">
                        <input type="file" class="form-control-file" name="cover_image" accept="image/*">
                    </div>
                </div>
            </div>
            @if ($errors->has('cover_image'))
                <div class="invalid-feedback">{{ $errors->first('cover_image') }}</div>
            @endif
        </div>
        <div class="form-group">
            <label for="status">Link Audio Youtube</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control{{ $errors->has('affi_link') ? ' is-invalid' : '' }}"
                       name="affi_link" id="affi_link"
                       value="{{ old('affi_link', $article->affi_link) }}"
                       placeholder="https://www.youtube.com/watch?v=...">
        </div>
        <div id="affiliate-links-wrapper">
            @if(old('affiliate_links'))
                @foreach(old('affiliate_links') as $index => $affi)
                    @include('articles._affiliate_link_form', ['index' => $index, 'affi' => $affi])
                @endforeach
            @elseif($article->affiliateLinks ?? false)
                @foreach($article->affiliateLinks as $index => $affi)
                    <div class="affiliate-link-block border rounded p-3 mb-3 position-relative">
                        <button type="button" class="btn btn-danger btn-sm position-absolute" style="top: 5px; right: 5px"
                                onclick="this.closest('.affiliate-link-block').remove()">✖</button>

                        <input type="hidden" name="affiliate_links[{{ $index }}][id]" value="{{ $affi['id'] ?? $affi->id ?? '' }}">

                        <div class="row">
                            <div class="col-md-6">
                                <label>Link Affiliate</label>
                                <input type="url"
                                    name="affiliate_links[{{ $index }}][link]"
                                    class="form-control"
                                    placeholder="https://affiliate.example.com/..."
                                    value="{{ old("affiliate_links.$index.link", $affi['link'] ?? $affi->link ?? '') }}">
                            </div>

                            <div class="col-md-6">
                                <label>Ảnh Affiliate</label>
                                <input type="file"
                                    name="affiliate_links[{{ $index }}][image_file]"
                                    class="form-control-file"
                                    accept="image/*"
                                    onchange="previewImage(this, {{ $index }})">
                                <div class="mt-2">
                                    <img id="affi-img-preview-{{ $index }}"
                                        src="{{ asset(old("affiliate_links.$index.image", $affi['image_path'] ?? $affi->image_path ?? 'images/articles/default.jpg')) }}"
                                        alt="Preview"
                                        class="img-thumbnail"
                                        width="120">
                                </div>
                            </div>
                        </div>
                    </div>

                @endforeach
            @else
                @include('articles._affiliate_link_form', ['index' => 0])
            @endif
        </div>
        <button type="button" class="btn btn-secondary my-3" onclick="addAffiliateLink()">➕ Thêm Link Affiliate</button>
        <template id="affiliate-link-template">
    <div class="affiliate-link-block border rounded p-3 mb-3 position-relative">
        <button type="button" class="btn btn-danger btn-sm position-absolute" style="top: 5px; right: 5px"
                onclick="this.closest('.affiliate-link-block').remove()">✖</button>

        <div class="row">
            <div class="col-md-6">
                <label>Link Affiliate</label>
                <input type="url" name="__LINK_NAME__" class="form-control" placeholder="https://affiliate.example.com/..." />
            </div>

            <div class="col-md-6">
                <label>Ảnh Affiliate</label>
                <input type="file" name="__IMAGE_NAME__" class="form-control-file"
                       accept="image/*" onchange="previewAffiliateImage(this, '__INDEX__')" />
                <div class="mt-2">
                    <img id="affi-img-preview-__INDEX__" src="/images/articles/default.jpg"
                         alt="Preview" class="img-thumbnail" width="120">
                </div>
            </div>
        </div>
    </div>
</template>
    </div>
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Xác nhận') }}</button>
    </div>
</div>

@section('ArticleScripts')
    <script>
        $(document).ready(function () {
            previewImage();
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
    </script>
    <script>
        function previewAffiImage() {
            let imgPreview = document.querySelector('#preview_affi_image');
            let affiImageUrl = document.querySelector('input[name="affi_image_url"]');

            $('input[name="affi_image"]').on('change', function (event) {
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

            $('#affi_image_url_preview').on('input', debounce(function () {
                if (imgPreview) {
                    let url = $(this).val();
                    $.ajax({
                        url: url,
                        method: 'GET',
                        success: function () {
                            imgPreview.src = affiImageUrl.value = url;
                        },
                        error: function (jqXHR) {
                            if (jqXHR.status === 404) {
                                let defaultImage = '/images/articles/default.jpg';
                                imgPreview.src = affiImageUrl.value = defaultImage;
                            }
                        }
                    });
                }
            }, 250));
        }

        $(document).ready(function () {
            previewImage();
            previewAffiImage();
        });
        </script>

@endsection
<script>
let affiIndex = {{ isset($article) && $article->affiliateLinks ? $article->affiliateLinks->count() : 1 }};

function addAffiliateLink() {
    const template = document.getElementById('affiliate-link-template').innerHTML;

    const html = template
        .replace(/__INDEX__/g, affiIndex)
        .replace('__LINK_NAME__', `affiliate_links[${affiIndex}][link]`)
        .replace('__IMAGE_NAME__', `affiliate_links[${affiIndex}][image_file]`);

    $('#affiliate-links-wrapper').append(html);
    affiIndex++;
}

function previewAffiliateImage(input, index) {
    const preview = document.getElementById('affi-img-preview-' + index);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>