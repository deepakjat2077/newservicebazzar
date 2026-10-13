
document.addEventListener('click', function (e) {
    const link = e.target.closest('a[href]');
    if (!link) return;

    const href = link.getAttribute('href');
    if (!href
        || href.startsWith('#')
        || href.startsWith('javascript:')
        || href.startsWith('tel:')
        || href.startsWith('mailto:')
    ) return;

    if (link.target === '_blank' || link.hasAttribute('download')) return;
    if (e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;

    let url;
    try {
        url = new URL(href, window.location.href);
    } catch (err) {
        return;
    }

    // Only same-site navigations that actually leave this page — an
    // in-page "#section" jump or a link back to the same URL shouldn't
    // spin up a full-page overlay.
    if (url.origin !== window.location.origin) return;
    if (url.pathname === window.location.pathname && url.search === window.location.search) return;

    const loader = document.getElementById('pageLoader');
    if (loader) loader.classList.add('show');
});

// Hides it if the browser restores this exact page from bfcache (the back
// button) rather than loading a fresh one — a fresh page load never has
// the class in the first place, so this only ever needs to remove it.
window.addEventListener('pageshow', function () {
    const loader = document.getElementById('pageLoader');
    if (loader) loader.classList.remove('show');
});
