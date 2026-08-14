@php
    $languages = App\Models\Language::get();
    $site_contact = themeRows($setting->theme)->where('name','site_contact')->where('is_multiple',0)->first()
@endphp
<div class="top-header">
    <div class="container">
        <div class="contact-area">
            <ul>
                <li><i class="las la-phone"></i> {{ __($site_contact->content->phone) }}</li>
                <li><i class="las la-envelope"></i> {{ __($site_contact->content->email) }}</li>
            </ul>
        </div>
        @if($setting->multi_lang)
        <div class="lang-area">
            <select class="language">
                @foreach($languages as $language)
                <option value="{{ $language->lang_code }}" @if(session('lang') == $language->lang_code) selected @endif>{{ __($language->name) }}</option>
                @endforeach
            </select>
        </div>
        @endif
    </div>
</div>
@push('js')
<script>
    (function($){
        "use strict";

        $('.language').on('change',function(){
            window.location.href = `{{ url('/language') }}/${$(this).val()}`;
        });

    })(jQuery)
</script>
@endpush