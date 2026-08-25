@extends($themeView.'layouts.site')
@section('title','Get Free Quotes')
@section('content')

<section class="py-5">
    <div class="container" style="max-width:560px;">
        <div class="card shadow border-0">
            <div class="card-body p-4 p-md-5">
                <h3 class="mb-2">Get Free Quotes</h3>
                <p class="text-muted mb-4">Tell us what you need — we'll send your request to up to 5 matching businesses at once.</p>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('request-quote.store') }}" method="post">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Category *</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', optional($category)->id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">City</label>
                        <select name="location_id" class="form-control">
                            <option value="">Any city</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ old('location_id', optional($location)->id) == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Your Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mobile Number *</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">What do you need? (helps businesses quote accurately)</label>
                        <textarea name="message" class="form-control" rows="3">{{ old('message') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn__base w-100">Send Quote Request</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
