/**
 * Mobile-only accordion behavior for the site-wide footer's "Site Link",
 * "Useful Links", "Services", and "Contact Info" columns
 * (resources/views/themes/servicebazzar/includes/footer.blade.php).
 *
 * Purely additive: on desktop/tablet the toggle icon this binds to is
 * display:none and .widget-body is never given display:none by CSS
 * there (see css/components/footer-accordion.css), so this script never
 * changes anything visible outside max-width:767px.
 */
(function () {
    "use strict";

    function setToggleState(title, expanded) {
        title.setAttribute("aria-expanded", expanded ? "true" : "false");
        var icon = title.querySelector(".footer-widget-toggle-icon i");
        if (icon) {
            icon.classList.toggle("fa-plus", !expanded);
            icon.classList.toggle("fa-minus", expanded);
        }
    }

    function bindToggle(title) {
        if (!title || title.dataset.footerAccordionBound === "1") return;
        title.dataset.footerAccordionBound = "1";

        var widget = title.closest(".footer-widget");
        var body = widget ? widget.querySelector(".widget-body") : null;
        if (!body) return;

        title.addEventListener("click", function () {
            var isOpen = body.classList.toggle("mobile-open");
            setToggleState(title, isOpen);
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        document
            .querySelectorAll(".footer-area .footer-widget .widget-title")
            .forEach(bindToggle);
    });
})();
