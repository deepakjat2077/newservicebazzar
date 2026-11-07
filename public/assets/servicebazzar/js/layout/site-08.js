
document.addEventListener('DOMContentLoaded', function () {
    var strip = document.getElementById('jdMobileLocationStrip');
    var top = document.querySelector('#stickyScrollHeader .jdh-top');
    if (strip && top && top.parentElement) top.parentElement.insertBefore(strip, top.nextSibling);
});
