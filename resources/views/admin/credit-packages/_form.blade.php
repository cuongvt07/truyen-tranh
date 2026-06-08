@php $pkg = $pkg ?? null; @endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Package name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $pkg?->name) }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Credits <span class="text-danger">*</span></label>
        <input type="number" name="coins" class="form-control @error('coins') is-invalid @enderror"
               value="{{ old('coins', $pkg?->coins) }}" min="1" required>
        @error('coins')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Price USD (PayPal) <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" name="price_usd" class="form-control @error('price_usd') is-invalid @enderror"
                   value="{{ old('price_usd', $pkg?->price_usd ?? 0) }}" min="0" step="0.01">
        </div>
        @error('price_usd')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Price VND (SePay)</label>
        <div class="input-group">
            <input type="number" name="price_vnd" class="form-control @error('price_vnd') is-invalid @enderror"
                   value="{{ old('price_vnd', $pkg?->price_vnd) }}" min="0">
            <span class="input-group-text">đ</span>
        </div>
        @error('price_vnd')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Icon path</label>
        <input type="text" name="icon" class="form-control"
               value="{{ old('icon', $pkg?->icon) }}" placeholder="media/payments/500.jpg">
        <small class="text-muted">Relative path from public/</small>
        @if($pkg?->icon)
            <div class="mt-1">
                <img src="{{ asset($pkg->icon) }}" alt="preview" height="60" style="border-radius:6px;">
            </div>
        @endif
    </div>

    <div class="col-md-2 mb-3">
        <label class="form-label">Sort order</label>
        <input type="number" name="sort_order" class="form-control"
               value="{{ old('sort_order', $pkg?->sort_order ?? 0) }}" min="0">
    </div>

    <div class="col-md-4 mb-3 d-flex flex-column justify-content-end">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1"
                   {{ old('is_featured', $pkg?->is_featured) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_featured">Featured</label>
        </div>
        <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                   {{ old('is_active', $pkg?->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
    </div>
</div>
