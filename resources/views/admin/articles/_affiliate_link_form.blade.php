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
            @php $affiImg = $affi['image'] ?? (is_object($affi) ? ($affi->image ?? null) : null); @endphp
            <x-admin.image-upload name="affiliate_links[{{ $index }}][image_file]" label="Ảnh Affiliate"
                removeName="affiliate_links[{{ $index }}][image_remove]" :height="120"
                :current="($affiImg && $affiImg !== '/images/articles/default.jpg') ? $affiImg : null" />
        </div>
    </div>
</div>
