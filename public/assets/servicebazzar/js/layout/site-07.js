
document.addEventListener("DOMContentLoaded", function () {
    const detectedCity = window.normalizeEnglishLocationName(localStorage.getItem("jf_detected_city") || "");
    const cityText = document.getElementById("jdMobileCityText");
    const jdInput = document.getElementById("jdMobileLocationInput");
    const homeInput = document.getElementById("homeLocationInput");

    if (window.location.pathname === "/" && detectedCity) {
        localStorage.removeItem("jf_user_city");
        localStorage.removeItem("jf_selected_location_name");

        if (cityText) cityText.innerText = detectedCity;
        if (jdInput) jdInput.value = detectedCity;
        if (homeInput) {
            homeInput.value = detectedCity;
            homeInput.dataset.selectedLocation = "1";
        }

        // Point category links at the detected city right away (no refresh).
        if (typeof window.sbRefreshCategoryLinks === "function") {
            window.sbRefreshCategoryLinks();
        }
    }

    const cityBtn = document.getElementById("jdMobileCityBtn");
    if (cityBtn) cityBtn.classList.remove("detecting");
});


