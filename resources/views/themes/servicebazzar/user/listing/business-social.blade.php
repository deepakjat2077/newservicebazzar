@extends($themeView.'layouts.app')
@section('content')
@section('title', 'Social Media — '.$listing->name)

@php
    $platforms = [
        'facebook'  => ['label' => 'Facebook Business Page Link', 'icon' => 'fab fa-facebook-f', 'color' => '#1877F2'],
        'x'         => ['label' => 'X Business Account Link', 'icon' => 'fab fa-x-twitter', 'color' => '#000'],
        'linkedin'  => ['label' => 'LinkedIn Company Page Link', 'icon' => 'fab fa-linkedin-in', 'color' => '#0A66C2'],
        'youtube'   => ['label' => 'YouTube Channel Link', 'icon' => 'fab fa-youtube', 'color' => '#FF0000'],
        'instagram' => ['label' => 'Instagram Profile Link', 'icon' => 'fab fa-instagram', 'color' => '#E1306C'],
    ];
@endphp

<section class="pt-30 pb-80">
    <div class="container" style="max-width:640px;">
        <div class="sb-biz-header">
            <a href="{{ request('from') === 'profile' ? route('user.listing.profile', $listing->id) : (request('from') === 'manage' ? route('user.listing.manage', $listing->id) : route('user.listing.dashboard', $listing->id)) }}" class="sb-biz-back"><i class="fas fa-arrow-left"></i></a>
            <div class="sb-biz-header-title">
                <h4>Add Social Media Links</h4>
                <span>{{ $listing->name }}</span>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('user.listing.business-social.update', $listing->id) }}" method="post">
            @csrf

            @foreach($platforms as $key => $platform)
                <div class="sb-social-block" id="sbSocialBlock_{{ $key }}">
                    @foreach($socialLinks[$key]->isEmpty() ? [''] : $socialLinks[$key]->all() as $i => $url)
                        <div class="sb-social-row">
                            <span class="sb-social-icon" style="background:{{ $platform['color'] }}"><i class="{{ $platform['icon'] }}"></i></span>
                            <input type="url" name="{{ $key }}[]" class="sb-input" placeholder="{{ $platform['label'] }}" value="{{ $url }}">
                            @if($i > 0)
                                <button type="button" class="sb-contact-remove sb-social-remove"><i class="fas fa-trash"></i></button>
                            @endif
                        </div>
                    @endforeach
                </div>
                <button type="button" class="sb-contact-add-btn sb-social-add" data-platform="{{ $key }}">
                    <i class="fas fa-plus"></i> Add Another {{ $platform['label'] }}
                </button>
            @endforeach

            <button type="submit" class="sb-wizard-primary-btn sb-biz-form-submit">Save</button>
        </form>
    </div>
</section>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('assets/servicebazzar/css/business-panel.css') }}">
<style>
    .sb-social-block{ margin-bottom:8px; }
    .sb-social-row{ display:flex; align-items:center; gap:12px; margin-bottom:8px; }
    .sb-social-icon{
        width:34px;
        height:34px;
        border-radius:50%;
        display:flex;
        align-items:center;
        justify-content:center;
        color:#fff;
        font-size:14px;
        flex-shrink:0;
    }
    .sb-social-row .sb-input{ flex:1; }
    .sb-social-add{ display:block; margin-bottom:18px; }
    .sb-biz-form-submit{ display:block; width:100%; margin-top:10px; }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    var icons = {
        facebook: 'fab fa-facebook-f', x: 'fab fa-x-twitter', linkedin: 'fab fa-linkedin-in',
        youtube: 'fab fa-youtube', instagram: 'fab fa-instagram',
    };
    var colors = {
        facebook: '#1877F2', x: '#000', linkedin: '#0A66C2', youtube: '#FF0000', instagram: '#E1306C',
    };
    var placeholders = {
        facebook: 'Facebook Business Page Link', x: 'X Business Account Link',
        linkedin: 'LinkedIn Company Page Link', youtube: 'YouTube Channel Link',
        instagram: 'Instagram Profile Link',
    };

    document.querySelectorAll('.sb-social-remove').forEach(bindRemove);
    function bindRemove(btn){
        btn.addEventListener('click', function(){ btn.closest('.sb-social-row').remove(); });
    }

    document.querySelectorAll('.sb-social-add').forEach(function(btn){
        btn.addEventListener('click', function(){
            var key = btn.getAttribute('data-platform');
            var block = document.getElementById('sbSocialBlock_' + key);
            var row = document.createElement('div');
            row.className = 'sb-social-row';
            row.innerHTML = '<span class="sb-social-icon" style="background:' + colors[key] + '"><i class="' + icons[key] + '"></i></span>' +
                '<input type="url" name="' + key + '[]" class="sb-input" placeholder="' + placeholders[key] + '">' +
                '<button type="button" class="sb-contact-remove sb-social-remove"><i class="fas fa-trash"></i></button>';
            block.appendChild(row);
            bindRemove(row.querySelector('.sb-social-remove'));
        });
    });
});
</script>
@endpush
