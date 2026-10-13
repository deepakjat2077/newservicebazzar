<script>
(function ($) {
    "use strict";

    var pollInterval = 8000;
    var soundUrl = @json(asset($themeAsset.'sound/message.wav'));
    var notifyUrl = @json(route('guest-chat.notifications'));
    var textOwnerReplied = @json(__('Owner replied'));
    var hasStorage = true;
    var lastId = 0;
    var guestKey = null;
    var audio = null;

    try {
        localStorage.setItem('_t', '1');
        localStorage.removeItem('_t');
    } catch (e) {
        hasStorage = false;
    }

    function shouldPoll() {
        if (!hasStorage) {
            return true;
        }

        return localStorage.getItem('guestChatSession') === '1';
    }

    function isChatActive() {
        return window.guestChatActive === true;
    }

    function getAudio() {
        if (!audio) {
            audio = new Audio(soundUrl);
            audio.preload = 'auto';
        }

        return audio;
    }

    function playSound() {
        try {
            var a = getAudio();
            a.pause();
            a.currentTime = 0;
            a.volume = 0.6;
            a.play().catch(function () {});
        } catch (e) {}
    }

    function poll() {
        if (!shouldPoll()) {
            return;
        }

        $.get(notifyUrl, { _t: Date.now() })
            .done(function (resp) {
                if (!resp || !resp.ok) {
                    return;
                }

                if (!resp.active) {
                    if (hasStorage) {
                        localStorage.setItem('guestChatSession', '0');
                    }

                    return;
                }

                if (!guestKey && resp.guest_id) {
                    guestKey = 'guestChatLastId_' + resp.guest_id;

                    if (hasStorage) {
                        lastId = parseInt(localStorage.getItem(guestKey) || '0', 10);
                    }
                }

                if (resp.latest_id && resp.latest_id > lastId) {
                    lastId = resp.latest_id;

                    if (hasStorage && guestKey) {
                        localStorage.setItem(guestKey, String(lastId));
                    }

                    if (!isChatActive()) {
                        try {
                            if (window.toastr) {
                                var listing = resp.listing_name ? ' - ' + resp.listing_name : '';
                                toastr.info(textOwnerReplied + listing);
                            }
                        } catch (e) {}

                        if (!hasStorage || localStorage.getItem('guestChatSound') !== 'off') {
                            playSound();
                        }
                    }
                }
            });
    }

    setInterval(poll, pollInterval);
    poll();
})(jQuery);
</script>