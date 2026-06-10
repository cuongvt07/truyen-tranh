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
        <label class="form-label">Package type <span class="text-danger">*</span></label>
        <select name="package_type" id="package_type" class="form-control @error('package_type') is-invalid @enderror" required>
            @php $type = old('package_type', $pkg?->package_type ?? 'credit'); @endphp
            <option value="credit" {{ $type === 'credit' ? 'selected' : '' }}>Credit only</option>
            <option value="subscription" {{ $type === 'subscription' ? 'selected' : '' }}>Subscription: hide ads + daily credits</option>
        </select>
        @error('package_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Credits <span class="text-danger">*</span></label>
        <input type="number" name="coins" class="form-control @error('coins') is-invalid @enderror"
               value="{{ old('coins', $pkg?->coins) }}" min="1" required>
        <small class="text-muted">Credit packages grant this amount once. Subscription packages use this as package cost/label.</small>
        @error('coins')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4 mb-3 subscription-fields">
        <label class="form-label">Subscription days</label>
        <input type="number" name="subscription_days" class="form-control @error('subscription_days') is-invalid @enderror"
               value="{{ old('subscription_days', $pkg?->subscription_days ?? 0) }}" min="0">
        @error('subscription_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4 mb-3 subscription-fields">
        <label class="form-label">Daily credits</label>
        <input type="number" name="daily_credits" class="form-control @error('daily_credits') is-invalid @enderror"
               value="{{ old('daily_credits', $pkg?->daily_credits ?? 0) }}" min="0">
        @error('daily_credits')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Price USD (PayPal)</label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" name="price_usd" class="form-control @error('price_usd') is-invalid @enderror"
                   value="{{ old('price_usd', $pkg?->price_usd ?? 0) }}" min="0" step="0.01">
        </div>
        @error('price_usd')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('package_type');
    const fields = document.querySelectorAll('.subscription-fields');

    function syncSubscriptionFields() {
        const isSubscription = typeSelect.value === 'subscription';
        fields.forEach(field => field.style.display = isSubscription ? '' : 'none');
    }

    typeSelect?.addEventListener('change', syncSubscriptionFields);
    syncSubscriptionFields();
});
</script>
