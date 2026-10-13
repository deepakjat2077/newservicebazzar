<script>
document.addEventListener('DOMContentLoaded', function () {
    const popup = document.getElementById('sbAuthPopup');
    const closeBtn = document.getElementById('sbAuthClose');
    const laterBtn = document.getElementById('sbMaybeLater');
    const stepOne = document.getElementById('sbStepOne');
    const stepOtp = document.getElementById('sbStepOtp');
    const authForm = document.getElementById('sbAuthForm');
    const otpForm = document.getElementById('sbOtpForm');
    const msgBox = document.getElementById('sbMsg');
    const sentText = document.getElementById('sbOtpSentText');
    const sendBtn = document.getElementById('sbSendBtn');
    const verifyBtn = document.getElementById('sbVerifyBtn');
    const resendBtn = document.getElementById('sbResendOtp');
    const otpFull = document.getElementById('sbFullOtp');
    const otpInputs = document.querySelectorAll('.sb-otp-boxes input');

    function showPopup(){ popup.classList.add('show'); document.body.style.overflow = 'hidden'; }
    function closePopup(){ popup.classList.remove('show'); document.body.style.overflow = ''; }
    function showMsg(type, text){ msgBox.innerHTML = '<div class="sb-' + type + '">' + text + '</div>'; }
    function clearMsg(){ msgBox.innerHTML = ''; }

    closeBtn.addEventListener('click', closePopup);
    laterBtn.addEventListener('click', closePopup);
    window.openSbAuthPopup = showPopup;

  authForm.addEventListener('submit', async function (e) {
    e.preventDefault();

    clearMsg();

    sendBtn.disabled = true;
    sendBtn.innerText = 'Please wait...';

    try {
        const response = await fetch("{{ route('user.quick.auth.send') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json'
            },
            body: new FormData(authForm)
        });

        const data = await response.json();

        console.log('OTP response:', data);

        if (!response.ok || data.success === false) {
            throw data;
        }

        const mobile = data.mobile || data.phone || '';

        sentText.textContent = mobile
            ? `WhatsApp number ${mobile}`
            : 'your WhatsApp number';

        stepOne.style.display = 'none';
        stepOtp.style.display = 'block';

        showMsg(
            'success',
            data.message || 'OTP sent successfully on WhatsApp.'
        );

        setTimeout(() => {
            clearMsg();
        }, 1500);

        if (otpInputs.length > 0) {
            otpInputs[0].focus();
        }
    } catch (error) {
        console.error('OTP send error:', error);

        let message = 'Something went wrong. Please try again.';

        if (error?.message) {
            message = error.message;
        }

        if (error?.errors) {
            const firstError = Object.values(error.errors)[0];

            if (Array.isArray(firstError)) {
                message = firstError[0];
            } else if (typeof firstError === 'string') {
                message = firstError;
            }
        }

        showMsg('error', message);
    } finally {
        sendBtn.disabled = false;
        sendBtn.innerText = 'Continue';
    }
});

    function updateOtp(){
        let otp = '';
        otpInputs.forEach(input => { otp += input.value; });
        otpFull.value = otp;
        verifyBtn.disabled = otp.length !== 6;
    }

    otpInputs.forEach((input, index) => {
        input.addEventListener('input', function(){
            this.value = this.value.replace(/\D/g, '');
            if(this.value && otpInputs[index + 1]) otpInputs[index + 1].focus();
            updateOtp();
            if (otpFull.value.length === 6) otpForm.requestSubmit();
        });
        input.addEventListener('keydown', function(e){
            if(e.key === 'Backspace' && !this.value && otpInputs[index - 1]) otpInputs[index - 1].focus();
        });
    });

    otpForm.addEventListener('submit', function(e){
        e.preventDefault();
        clearMsg();
        updateOtp();
        if(otpFull.value.length !== 6){
            showMsg('error', 'Please enter 6 digit OTP');
            return;
        }
        verifyBtn.disabled = true;
        verifyBtn.innerText = 'Please wait...';
        const formData = new FormData();
        formData.append('otp', otpFull.value);
        fetch("{{ route('user.quick.auth.verify') }}", {
            method: "POST",
            headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}", "Accept": "application/json" },
            body: formData
        })
        .then(async response => {
            const data = await response.json();
            if(!response.ok) throw data;
            showMsg('success', data.message);
            location.reload();
        })
        .catch(error => {
            let message = error.message || 'Invalid OTP';
            if(error.errors) message = Object.values(error.errors)[0][0];
            showMsg('error', message);
            verifyBtn.disabled = false;
            verifyBtn.innerText = 'Continue';
        });
    });

    resendBtn.addEventListener('click', function(){
        clearMsg();
        resendBtn.disabled = true;
        resendBtn.innerText = 'Sending...';
        fetch("{{ route('user.quick.auth.resend') }}", {
            method: "POST",
            headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}", "Accept": "application/json" }
        })
        .then(async response => {
            const data = await response.json();
            if(!response.ok) throw data;
            showMsg('success', data.message);
        })
        .catch(error => { showMsg('error', error.message || 'OTP resend failed'); })
        .finally(() => {
            resendBtn.disabled = false;
            resendBtn.innerText = 'Resend OTP';
        });
    });
});

const currentUrlInput = document.getElementById('sbCurrentUrl');
if(currentUrlInput) currentUrlInput.value = window.location.href;

function openSbAuthPopup() {
    @auth return; @endauth
    document.getElementById('sbAuthPopup').classList.add('show');
    document.body.style.overflow = 'hidden';
}

// /user/login redirects here with ?login=1 — this is the sign-in screen now.
@if(request()->boolean('login') || session('showAuthPopup'))
document.addEventListener('DOMContentLoaded', function () { openSbAuthPopup(); });
@endif
</script>