<script src="https://www.google.com/recaptcha/api.js"></script>
<div class="g-recaptcha" data-sitekey="{{@$setting->recaptcha->site_key}}" data-callback="verifyCaptcha"></div>
<div id="g-recaptcha-error"></div>

@push('js')
<script>
    (function($){
        "use strict"
        $('.captchaVerify').on('submit',function(){
            var response = grecaptcha.getResponse();
            if (response.length == 0) {
                document.getElementById('g-recaptcha-error').innerHTML = '<span class="text-danger">@lang("Captcha field is required.")</span>';
                return false;
            }
            return true;
        });
    })(jQuery);
</script>
@endpush
