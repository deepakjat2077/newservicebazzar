<script>
document.addEventListener("DOMContentLoaded", function () {
    const mobileForm = document.querySelector(".yt-search-form");
    const input = document.getElementById("ytMobileSearchInput");

    function makeSlug(text){
        return String(text || "")
            .toLowerCase()
            .trim()
            .replace(/,/g, "")
            .replace(/\s+/g, "-")
            .replace(/[^a-z0-9-]/g, "")
            .replace(/-+/g, "-")
            .replace(/^-+|-+$/g, "");
    }

    function goMobileSearch(){
        const value = input?.value.trim() || "";
        if (!value) return;

        fetch(`{{ route('category.suggestions') }}?q=${encodeURIComponent(value)}&_=${Date.now()}`, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            }
        })
        .then(res => res.json())
        .then(data => {
            let categorySlug = "";

            if (data && data.length > 0 && data[0].url) {
                categorySlug = data[0].url.split("/").filter(Boolean).pop();
            }

            if (!categorySlug) {
                categorySlug = makeSlug(value);
            }

            let selectedLocation =
                document.getElementById("jdMobileLocationInput")?.value.trim() ||
                document.getElementById("jdMobileCityText")?.innerText.trim() ||
                localStorage.getItem("jf_selected_location_name") ||
                localStorage.getItem("jf_city") ||
                "";

            selectedLocation = selectedLocation.trim();

            let city = "";
            let area = "";

            if (selectedLocation.includes(",")) {
                const parts = selectedLocation.split(",");
                area = parts[0].trim();
                city = parts[1].trim();
            } else {
                city = selectedLocation;
            }

            const citySlug = makeSlug(city);

            if (!citySlug || !categorySlug) return;

            let finalUrl = `${window.location.origin}/${citySlug}/${categorySlug}`;

            if (area !== "") {
                finalUrl += `-in-${makeSlug(area)}`;
            }

            window.location.href = finalUrl;
        });
    }

    if (mobileForm) {
        mobileForm.addEventListener("submit", function(e){
            e.preventDefault();
            goMobileSearch();
        });
    }

    document.querySelector(".yt-search-submit")?.addEventListener("click", function(e){
        e.preventDefault();
        goMobileSearch();
    });
});
</script>