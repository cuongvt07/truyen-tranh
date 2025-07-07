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
                     src="{{ old("affiliate_links.$index.image", $affi['image'] ?? $affi->image ?? '/images/articles/default.jpg') }}"
                     alt="Preview"
                     class="img-thumbnail"
                     width="120">
            </div>
        </div>
    </div>
</div>
