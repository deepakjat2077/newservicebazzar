<script>
window.addEventListener("load", function () {
    const parts = window.location.pathname.split("/").filter(Boolean);

    // URL me city ho to detected city se overwrite mat karo
    if (parts.length >= 2) return;

    const sessionCity = @json(session('detected_city') ?? session('user_location') ?? session('selected_location') ?? '');
    const detectedCity = window.normalizeEnglishLocationName(localStorage.getItem("jf_detected_city") || sessionCity || "");

    if (!detectedCity) return;

    if (window.location.pathname === "/") {
        localStorage.removeItem("jf_user_city");

        const cityText = document.getElementById("jdMobileCityText");
        const jdInput = document.getElementById("jdMobileLocationInput");
        const homeInput = document.getElementById("homeLocationInput");

        if (cityText) cityText.innerText = detectedCity;
        if (jdInput) jdInput.value = detectedCity;
        if (homeInput) {
            homeInput.value = detectedCity;
            homeInput.dataset.selectedLocation = "1";
        }

        // This auto-detect fills the location input on load (after the first
        // category-link pass has already run with no known city), so re-point
        // the category links to the detected city now — otherwise they'd stay
        // on the generic /listings/... URL until the visitor refreshed.
        if (typeof window.sbRefreshCategoryLinks === "function") {
            window.sbRefreshCategoryLinks();
        }
    }
});
</script>