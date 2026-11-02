
document.addEventListener('DOMContentLoaded', function () {
    var strip = document.getElementById('jdMobileLocationStrip');
    var top = document.querySelector('#stickyScrollHeader .jdh-top');
    if (strip && top && top.parentElement) top.parentElement.insertBefore(strip, top.nextSibling);
});
// Whatever closes the mobile location box (back arrow, city button, a pick,
// browser back), put the header back the way it was: search row on, page
// scrollable. Some close paths forgot this and left the old header row showing.
document.addEventListener('DOMContentLoaded', function () {
    var box = document.getElementById('jdMobileLocationBox');
    if (!box || !document.body.classList.contains('jd-head') || !window.MutationObserver) return;
    var wasOpen = box.classList.contains('show');
    new MutationObserver(function () {
        var open = box.classList.contains('show');
        if (wasOpen && !open) {
            document.body.classList.add('mobile-yt-search-open');
            document.body.classList.remove('show-yt-location');
            document.documentElement.style.removeProperty('overflow');
            document.body.style.removeProperty('overflow');
            var yt = document.getElementById('youtubeMobileSearch');
            if (yt) yt.style.removeProperty('display');
        }
        wasOpen = open;
    }).observe(box, { attributes: true, attributeFilter: ['class'] });
});
