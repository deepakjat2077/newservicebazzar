@extends($themeView.'layouts.app')
@section('content')
@section('title', 'Business Website — '.$listing->name)

<section class="pt-30 pb-80">
    <div class="container" style="max-width:560px;">
        <div class="sb-biz-header">
            <a href="{{ request('from') === 'manage' ? route('user.listing.manage', $listing->id) : route('user.listing.dashboard', $listing->id) }}" class="sb-biz-back"><i class="fas fa-arrow-left"></i></a>
            <div class="sb-biz-header-title">
                <h4>Business Website</h4>
                <span>{{ $listing->name }}</span>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="sb-biz-panel-info-banner">
            <i class="fas fa-circle-info"></i>
            Please provide the URL of your business website so customers can reach you.
        </div>

        <form action="{{ route('user.listing.business-website.update', $listing->id) }}" method="post">
            @csrf

            <div class="sb-biz-panel-field">
                <label>Enter Business Website</label>
                <input type="url" name="website" class="sb-input" value="{{ $listing->website }}" placeholder="https://www.example.com">
            </div>

            <div id="sbExtraWebsites">
                @foreach((array) ($listing->extra_websites ?? []) as $url)
                    <div class="sb-contact-row sb-contact-repeatable">
                        <div class="sb-biz-panel-field sb-contact-grow">
                            <label>Website</label>
                            <input type="url" name="extra_websites[]" class="sb-input" value="{{ $url }}">
                        </div>
                        <button type="button" class="sb-contact-remove"><i class="fas fa-trash"></i></button>
                    </div>
                @endforeach
            </div>
            <button type="button" class="sb-contact-add-btn" id="sbAddWebsite"><i class="fas fa-plus"></i> Add Another Website</button>

            <button type="submit" class="sb-wizard-primary-btn sb-biz-form-submit">Save</button>
        </form>
    </div>
</section>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('assets/servicebazzar/css/business-panel.css') }}">
<style>
    .sb-biz-form-submit{ display:block; width:100%; margin-top:24px; }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.sb-contact-remove').forEach(function(btn){
        btn.addEventListener('click', function(){ btn.closest('.sb-contact-repeatable').remove(); });
    });

    document.getElementById('sbAddWebsite').addEventListener('click', function(){
        var list = document.getElementById('sbExtraWebsites');
        var row = document.createElement('div');
        row.className = 'sb-contact-row sb-contact-repeatable';
        row.innerHTML = '<div class="sb-biz-panel-field sb-contact-grow"><label>Website</label><input type="url" name="extra_websites[]" class="sb-input"></div>' +
            '<button type="button" class="sb-contact-remove"><i class="fas fa-trash"></i></button>';
        list.appendChild(row);
        row.querySelector('.sb-contact-remove').addEventListener('click', function(){ row.remove(); });
    });
});
</script>
@endpush
