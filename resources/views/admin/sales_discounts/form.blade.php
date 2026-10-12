@extends('admin.layouts.master')
@section('title', $discount->exists ? 'Edit Discount' : 'Add Discount')
@section('content')
@php
    $editing   = $discount->exists;
    $type      = old('type', $discount->type);
    $selected  = array_map('intval', (array) old('package_ids', $discount->package_ids ?? []));
    $scope     = old('scope', $editing && !$discount->coversAllPackages() ? 'selected' : 'all');
@endphp

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header bg__dark text-white">
                <h5 class="mb-0">{{ $editing ? 'Edit' : 'Add' }} Discount</h5>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif

                <form method="POST" action="{{ $editing ? route('admin.sales_discounts.update', $discount->id) : route('admin.sales_discounts.store') }}">
                    @csrf

                    <div class="form-group">
                        <label class="font-weight-bold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" maxlength="120" required
                               value="{{ old('name', $discount->name) }}" placeholder="e.g. Festive offer">
                        <small class="text-muted">The sales team sees this name, and it prints on the invoice.</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-control">
                                <option value="percent" @selected($type === 'percent')>Percent off (%)</option>
                                <option value="flat" @selected($type === 'flat')>Flat amount off (₹)</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Value <span class="text-danger">*</span></label>
                            <input type="number" name="value" class="form-control" step="0.01" min="0.01" required
                                   value="{{ old('value', $discount->value ? (float) $discount->value : '') }}" placeholder="10">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold d-block">Applies to</label>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="scopeAll" name="scope" value="all" class="custom-control-input" @checked($scope === 'all')>
                            <label class="custom-control-label" for="scopeAll">All packages</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="scopeSel" name="scope" value="selected" class="custom-control-input" @checked($scope === 'selected')>
                            <label class="custom-control-label" for="scopeSel">Selected packages</label>
                        </div>

                        <div id="pkgBox" class="border rounded p-3 mt-2" style="{{ $scope === 'selected' ? '' : 'display:none;' }}">
                            <div class="row">
                                @foreach($packages as $p)
                                    <div class="col-md-6">
                                        <div class="custom-control custom-checkbox mb-1">
                                            <input type="checkbox" class="custom-control-input" id="pkg{{ $p->id }}" name="package_ids[]" value="{{ $p->id }}" @checked(in_array($p->id, $selected))>
                                            <label class="custom-control-label" for="pkg{{ $p->id }}">{{ $p->name }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <small class="text-muted">A discount only reduces the packages it covers; the other items in the cart keep their full price.</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Starts on</label>
                            <input type="date" name="starts_on" class="form-control" value="{{ old('starts_on', optional($discount->starts_at)->format('Y-m-d')) }}">
                            <small class="text-muted">Leave empty to start straight away.</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Ends on</label>
                            <input type="date" name="ends_on" class="form-control" value="{{ old('ends_on', optional($discount->ends_at)->format('Y-m-d')) }}">
                            <small class="text-muted">Leave empty for no end. The last day is included.</small>
                        </div>
                    </div>

                    <div class="custom-control custom-switch mb-4">
                        <input type="checkbox" class="custom-control-input" id="isActive" name="is_active" value="1" @checked(old('is_active', $discount->is_active))>
                        <label class="custom-control-label" for="isActive">On (the sales team can use it)</label>
                    </div>

                    <button type="submit" class="btn btn-primary">{{ $editing ? 'Save changes' : 'Create discount' }}</button>
                    <a href="{{ route('admin.sales_discounts.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.querySelectorAll('input[name="scope"]').forEach(function (r) {
        r.addEventListener('change', function () {
            document.getElementById('pkgBox').style.display = document.getElementById('scopeSel').checked ? '' : 'none';
        });
    });
</script>
@endpush
