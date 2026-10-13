
document.addEventListener('DOMContentLoaded', function () {
    const panel = document.getElementById('sbProfilePanel');
    const overlay = document.getElementById('sbProfilePanelOverlay');
    const closeBtn = document.getElementById('sbProfilePanelClose');
    const triggers = document.querySelectorAll('.profile-dashboard-link');
    const backgroundInputs = Array.from(document.querySelectorAll(
        '#homeLocationInput, #homeCategorySearch, #locationInput, #CategorySearch, #jdMobileLocationInput, #ytMobileSearchInput'
    ));

    function setBackgroundInputsDisabled(disabled) {
        backgroundInputs.forEach(function (input) {
            input.disabled = disabled;
            input.setAttribute('aria-disabled', disabled ? 'true' : 'false');
        });
    }

    function openPanel(e) {
        e.preventDefault();
        if (document.activeElement && backgroundInputs.includes(document.activeElement)) {
            document.activeElement.blur();
        }
        setBackgroundInputsDisabled(true);
        panel.classList.add('show');
        overlay.classList.add('show');
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
    }

    function closePanel() {
        panel.classList.remove('show');
        overlay.classList.remove('show');
        setBackgroundInputsDisabled(false);
        document.documentElement.style.overflow = '';
        document.body.style.overflow = '';
    }

    triggers.forEach(function (trigger) {
        trigger.addEventListener('click', openPanel);
    });

    closeBtn.addEventListener('click', closePanel);
    overlay.addEventListener('click', closePanel);
});
