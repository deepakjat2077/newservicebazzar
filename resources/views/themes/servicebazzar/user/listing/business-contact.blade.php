@extends($themeView.'layouts.app')
@section('content')
@section('title', 'Contact Details — '.$listing->name)

<section class="pt-30 pb-80">
    <div class="container" style="max-width:560px;">
        <div class="sb-biz-header">
            <a href="{{ route('user.listing.dashboard', $listing->id) }}" class="sb-biz-back"><i class="fas fa-arrow-left"></i></a>
            <div class="sb-biz-header-title">
                <h4>Contact Details</h4>
                <span>{{ $listing->name }}</span>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="sb-biz-panel-info-banner">
            <i class="fas fa-circle-info"></i>
            Update your contact details to stay in touch with your customers.
        </div>

        <form action="{{ route('user.listing.business-contact.update', $listing->id) }}" method="post" id="sbContactForm">
            @csrf

            <div class="sb-contact-block">
                <div class="sb-contact-row">
                    <div class="sb-biz-panel-field sb-contact-title">
                        <label>Title</label>
                        <select name="contact_title" class="sb-input">
                            @foreach(['Mr','Mrs','Ms'] as $t)
                                <option value="{{ $t }}" {{ $listing->contact_title === $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sb-biz-panel-field sb-contact-grow">
                        <label>Contact Person Name</label>
                        <input type="text" name="contact_person" class="sb-input" value="{{ $listing->contact_person }}">
                    </div>
                </div>
            </div>

            <p class="sb-contact-section-label">Mobile Numbers</p>
            <div id="sbMobileList">
                @forelse(collect($listing->mobile_numbers ?? [])->values() as $i => $number)
                    <div class="sb-contact-row sb-contact-repeatable">
                        <div class="sb-biz-panel-field sb-contact-code">
                            <select name="mobile_country_code[]" class="sb-input">
                                <option value="+91" {{ (($listing->mobile_country_code[$i] ?? '+91') === '+91') ? 'selected' : '' }}>+91</option>
                            </select>
                        </div>
                        <div class="sb-biz-panel-field sb-contact-grow">
                            <label>Mobile Number</label>
                            <input type="text" name="mobile_numbers[]" class="sb-input" value="{{ $number }}">
                        </div>
                        <button type="button" class="sb-contact-remove"><i class="fas fa-trash"></i></button>
                    </div>
                @empty
                @endforelse
            </div>
            <button type="button" class="sb-contact-add-btn" id="sbAddMobile"><i class="fas fa-plus"></i> Add Another Mobile Number</button>

            <p class="sb-contact-section-label">WhatsApp Numbers</p>
            <div id="sbWhatsappList">
                @forelse(collect($listing->whatsapp_numbers ?? [])->values() as $i => $number)
                    <div class="sb-contact-row sb-contact-repeatable">
                        <div class="sb-biz-panel-field sb-contact-code">
                            <select name="whatsapp_country_code[]" class="sb-input">
                                <option value="+91" {{ (($listing->whatsapp_country_code[$i] ?? '+91') === '+91') ? 'selected' : '' }}>+91</option>
                            </select>
                        </div>
                        <div class="sb-biz-panel-field sb-contact-grow">
                            <label>WhatsApp Number</label>
                            <input type="text" name="whatsapp_numbers[]" class="sb-input" value="{{ $number }}">
                        </div>
                        <button type="button" class="sb-contact-remove"><i class="fas fa-trash"></i></button>
                    </div>
                @empty
                @endforelse
            </div>
            <button type="button" class="sb-contact-add-btn" id="sbAddWhatsapp"><i class="fas fa-plus"></i> Add Another WhatsApp Number</button>

            <p class="sb-contact-section-label">Email</p>
            <div id="sbEmailList">
                @forelse(collect($listing->extra_emails ?? [])->values() as $email)
                    <div class="sb-contact-row sb-contact-repeatable">
                        <div class="sb-biz-panel-field sb-contact-grow">
                            <label>Email</label>
                            <input type="email" name="extra_emails[]" class="sb-input" value="{{ $email }}">
                        </div>
                        <button type="button" class="sb-contact-remove"><i class="fas fa-trash"></i></button>
                    </div>
                @empty
                @endforelse
            </div>
            <button type="button" class="sb-contact-add-btn" id="sbAddEmail"><i class="fas fa-plus"></i> Add Another Email</button>

            <p class="sb-contact-section-label">Landline Number</p>
            <div id="sbLandlineList">
                @forelse(collect($listing->landline_numbers ?? [])->values() as $i => $number)
                    <div class="sb-contact-row sb-contact-repeatable">
                        <div class="sb-biz-panel-field sb-contact-code">
                            <select name="landline_country_code[]" class="sb-input">
                                <option value="+91" {{ (($listing->landline_country_code[$i] ?? '+91') === '+91') ? 'selected' : '' }}>+91</option>
                            </select>
                        </div>
                        <div class="sb-biz-panel-field sb-contact-grow">
                            <label>Landline Number</label>
                            <input type="text" name="landline_numbers[]" class="sb-input" value="{{ $number }}">
                        </div>
                        <button type="button" class="sb-contact-remove"><i class="fas fa-trash"></i></button>
                    </div>
                @empty
                @endforelse
            </div>
            <button type="button" class="sb-contact-add-btn" id="sbAddLandline"><i class="fas fa-plus"></i> Add Landline Number</button>

            <button type="submit" class="sb-wizard-primary-btn sb-biz-form-submit">Save</button>
        </form>
    </div>
</section>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('assets/servicebazzar/css/business-panel.css') }}">
<style>
    .sb-contact-section-label{ font-size:13px; font-weight:700; color:#222; margin:22px 0 10px; }
    .sb-contact-row{ display:flex; gap:10px; align-items:flex-end; margin-bottom:10px; }
    .sb-contact-code{ width:80px; flex-shrink:0; }
    .sb-contact-grow{ flex:1; min-width:0; }
    .sb-contact-title{ width:90px; flex-shrink:0; }
    .sb-contact-remove{ border:0; background:transparent; color:#d9364f; padding:9px; flex-shrink:0; }
    .sb-contact-add-btn{
        border:0;
        background:transparent;
        color:#1e90ff;
        font-size:13px;
        font-weight:600;
        padding:0;
        margin-bottom:6px;
    }
    .sb-biz-form-submit{ display:block; width:100%; margin-top:26px; }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    function addRow(listId, template){
        var list = document.getElementById(listId);
        var wrapper = document.createElement('div');
        wrapper.innerHTML = template;
        var row = wrapper.firstElementChild;
        row.querySelector('.sb-contact-remove').addEventListener('click', function(){ row.remove(); });
        list.appendChild(row);
    }

    document.querySelectorAll('.sb-contact-remove').forEach(function(btn){
        btn.addEventListener('click', function(){ btn.closest('.sb-contact-repeatable').remove(); });
    });

    document.getElementById('sbAddMobile').addEventListener('click', function(){
        addRow('sbMobileList', '<div class="sb-contact-row sb-contact-repeatable"><div class="sb-biz-panel-field sb-contact-code"><select name="mobile_country_code[]" class="sb-input"><option value="+91" selected>+91</option></select></div><div class="sb-biz-panel-field sb-contact-grow"><label>Mobile Number</label><input type="text" name="mobile_numbers[]" class="sb-input"></div><button type="button" class="sb-contact-remove"><i class="fas fa-trash"></i></button></div>');
    });

    document.getElementById('sbAddWhatsapp').addEventListener('click', function(){
        addRow('sbWhatsappList', '<div class="sb-contact-row sb-contact-repeatable"><div class="sb-biz-panel-field sb-contact-code"><select name="whatsapp_country_code[]" class="sb-input"><option value="+91" selected>+91</option></select></div><div class="sb-biz-panel-field sb-contact-grow"><label>WhatsApp Number</label><input type="text" name="whatsapp_numbers[]" class="sb-input"></div><button type="button" class="sb-contact-remove"><i class="fas fa-trash"></i></button></div>');
    });

    document.getElementById('sbAddEmail').addEventListener('click', function(){
        addRow('sbEmailList', '<div class="sb-contact-row sb-contact-repeatable"><div class="sb-biz-panel-field sb-contact-grow"><label>Email</label><input type="email" name="extra_emails[]" class="sb-input"></div><button type="button" class="sb-contact-remove"><i class="fas fa-trash"></i></button></div>');
    });

    document.getElementById('sbAddLandline').addEventListener('click', function(){
        addRow('sbLandlineList', '<div class="sb-contact-row sb-contact-repeatable"><div class="sb-biz-panel-field sb-contact-code"><select name="landline_country_code[]" class="sb-input"><option value="+91" selected>+91</option></select></div><div class="sb-biz-panel-field sb-contact-grow"><label>Landline Number</label><input type="text" name="landline_numbers[]" class="sb-input"></div><button type="button" class="sb-contact-remove"><i class="fas fa-trash"></i></button></div>');
    });
});
</script>
@endpush
