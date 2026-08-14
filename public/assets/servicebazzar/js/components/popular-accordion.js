/**
 * Mobile-only accordion behavior for the footer's "Popular Categories" and
 * "Popular Cities" sections (resources/views/themes/servicebazzar/partials/
 * popular_categories_ajax.blade.php and popular_cities.blade.php).
 *
 * Purely additive: on desktop these toggle buttons are hidden by CSS
 * (display:none), so this script never runs any visible behavior there —
 * it only ever manipulates classes on elements that are invisible/inert on
 * desktop. Nothing here touches desktop markup, CSS, or layout.
 *
 * Re-runs on every DOMContentLoaded AND whenever the "Popular Categories"
 * panel's HTML is replaced by the existing location-based AJAX refresh
 * (loadPopularCategoriesByLocation() in index.blade.php posts to
 * /ajax/popular-categories and replaces #popularCategoriesWrapper's
 * innerHTML) — that swap doesn't reload the page, so the buttons it just
 * injected need their listeners (re)bound too.
 */
(function () {
    "use strict";

    function setToggleState(button, expanded) {
        button.setAttribute("aria-expanded", expanded ? "true" : "false");
        var icon = button.querySelector(".popular-toggle-icon i, .category-toggle-icon i");
        if (icon) {
            icon.classList.toggle("fa-plus", !expanded);
            icon.classList.toggle("fa-minus", expanded);
        }
    }

    // "Popular Categories" main heading + "Popular Cities" main heading:
    // a single on/off toggle revealing the panel named in data-target.
    // (Not nextElementSibling: both buttons are immediately followed by a
    // desktop-only heading that stays in the DOM — just hidden by CSS on
    // mobile — so "next sibling" would grab the wrong element.)
    function bindMainToggle(button) {
        if (!button || button.dataset.accordionBound === "1") return;
        button.dataset.accordionBound = "1";

        var targetSelector = button.getAttribute("data-target");
        var panel = targetSelector ? document.querySelector(targetSelector) : null;
        if (!panel) return;

        button.addEventListener("click", function () {
            var isOpen = panel.classList.toggle("mobile-open");
            setToggleState(button, isOpen);
        });
    }

    // Individual category rows inside "Popular Categories": only one
    // category's subcategory list may be open at a time — opening one
    // closes whichever other one was open.
    function bindCategoryToggle(button) {
        if (!button || button.dataset.accordionBound === "1") return;
        button.dataset.accordionBound = "1";

        var row = button.closest(".popular-mobile-category");
        var box = row ? row.querySelector(".popular-subcat-box") : null;
        if (!box) return;

        button.addEventListener("click", function () {
            var willOpen = !box.classList.contains("mobile-active");

            var container = button.closest(".popular-subcategory-area") || document;
            container.querySelectorAll(".popular-subcat-box.mobile-active").forEach(function (openBox) {
                if (openBox === box) return;
                openBox.classList.remove("mobile-active");
                var openRow = openBox.closest(".popular-mobile-category");
                var openButton = openRow ? openRow.querySelector(".popular-mobile-category-toggle") : null;
                if (openButton) setToggleState(openButton, false);
            });

            box.classList.toggle("mobile-active", willOpen);
            setToggleState(button, willOpen);
        });
    }

    // Desktop-only: "Popular Categories" tab strip. Previously duplicated
    // verbatim in index.blade.php and listings/details.blade.php — now a
    // single copy here since the footer partial renders on every page.
    // Untouched behavior/markup contract (.popular-cat-tab / data-tab /
    // .popular-subcat-box id="popular-cat-{id}" / .active class).
    function bindDesktopCategoryTabs(scope) {
        var tabs = scope.querySelectorAll(".popular-cat-tab");
        var boxes = scope.querySelectorAll(".popular-subcat-box");

        tabs.forEach(function (tab) {
            if (tab.dataset.accordionBound === "1") return;
            tab.dataset.accordionBound = "1";

            tab.addEventListener("click", function () {
                tabs.forEach(function (t) { t.classList.remove("active"); });
                boxes.forEach(function (b) { b.classList.remove("active"); });

                this.classList.add("active");

                var target = document.getElementById(this.dataset.tab);
                if (target) {
                    target.classList.add("active");
                }
            });
        });
    }

    function init(root) {
        var scope = root || document;
        scope.querySelectorAll(".popular-mobile-main-toggle").forEach(bindMainToggle);
        scope.querySelectorAll(".popular-mobile-category-toggle").forEach(bindCategoryToggle);
        bindDesktopCategoryTabs(scope);
    }

    document.addEventListener("DOMContentLoaded", function () {
        init(document);
    });

    // Re-bind after the home page's AJAX location-change refresh swaps
    // #popularCategoriesWrapper's innerHTML with freshly rendered buttons.
    document.addEventListener("popularCategoriesRefreshed", function (e) {
        init((e.detail && e.detail.target) || document);
    });
})();
