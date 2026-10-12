@extends('admin.layouts.master')
@section('title', 'Generate Enquiry')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header bg__dark text-white">
                <h5 class="mb-0">Generate Enquiry</h5>
            </div>
            <div class="card-body">

                <form action="{{ route('admin.enquiry.generate.store') }}" method="POST">
                    @csrf

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Category <span class="text-danger">*</span></label>
                        <select name="category_id" id="categorySelect" class="form-control select2-search" required>
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Listing <small class="text-muted">(optional)</small></label>
                        <select name="listing_id" id="listingSelect" class="form-control select2-search">
                            <option value="">-- Select Category First --</option>
                        </select>
                        @error('listing_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                               placeholder="Enter enquirer name" required>
                        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Mobile Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}"
                               placeholder="Enter mobile number" required>
                        @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold">Location <span class="text-danger">*</span></label>
                        <select name="location_id" id="locationSelect" class="form-control select2-search" required>
                            <option value="">-- Select Location --</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>
                                    {{ $loc->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('location_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="las la-paper-plane mr-1"></i> Generate Enquiry
                    </button>
                    <a href="{{ route('admin.enquiry.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css-link')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/select2.min.css') }}">
@endpush
@push('js-link')
    <script src="{{ asset('assets/admin/js/select2.min.js') }}"></script>
@endpush
@push('js')
<script>
$(document).ready(function () {
    $('#categorySelect, #listingSelect, #locationSelect').each(function () {
        $(this).select2({ placeholder: $(this).find('option:first').text(), allowClear: true, width: '100%' });
    });

    $('#categorySelect').on('change', function () {
        var catId = $(this).val();
        var $listing = $('#listingSelect');
        $listing.html('<option value="">Loading...</option>').trigger('change');

        if (!catId) {
            $listing.html('<option value="">-- Select Category First --</option>').trigger('change');
            return;
        }

        $.get('{{ url("admin/enquiry/listings-by-category") }}', { category_id: catId }, function (data) {
            var opts = '<option value="">-- Select Listing --</option>';
            $.each(data, function (i, l) {
                opts += '<option value="' + l.id + '">' + l.name + '</option>';
            });
            $listing.html(opts).trigger('change');
        }).fail(function () {
            $listing.html('<option value="">Error loading listings</option>').trigger('change');
        });
    });

    @if(old('category_id'))
    $('#categorySelect').val('{{ old("category_id") }}').trigger('change');
    @endif
});
</script>
@endpush
