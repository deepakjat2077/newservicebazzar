<script>
document.addEventListener("DOMContentLoaded", function () {
    function initHeaderClear(inputId){
        const input = document.getElementById(inputId);
        if(!input) return;
        const wrapper = input.closest(".search-box, .input_search_box");
        if(!wrapper) return;
        let clearBtn = wrapper.querySelector(".header-category-clear-btn");
        if(!clearBtn){
            clearBtn = document.createElement("span");
            clearBtn.className = "header-category-clear-btn";
            clearBtn.innerHTML = '<i class="fas fa-times"></i>';
            wrapper.appendChild(clearBtn);
        }
        function toggleBtn(){
            if(input.value.trim() !== ""){
                clearBtn.style.display = "block";
            }else{
                clearBtn.style.display = "none";
            }
        }
        input.addEventListener("input", toggleBtn);
        clearBtn.addEventListener("click", function () {
            input.value = "";
            clearBtn.style.display = "none";
            input.focus();
            const dropdown = document.getElementById("categorySuggestions");
            if(dropdown){
                dropdown.classList.remove("show");
                dropdown.innerHTML = "";
            }
        });
        toggleBtn();
    }
    initHeaderClear("CategorySearch");
});

document.addEventListener("DOMContentLoaded", function () {
    const categoryInput = document.getElementById("CategorySearch");
    if (!categoryInput) return;
    categoryInput.addEventListener("keydown", function (e) {
        if (e.key !== "Enter") return;
        e.preventDefault();
        const value = this.value.trim();
        if (!value) return;
        const selectedLocation = document.getElementById("locationInput")?.value.trim() || localStorage.getItem("jf_detected_city") || localStorage.getItem("jf_city") || "";
       fetch(`{{ route('category.suggestions') }}?q=${encodeURIComponent(value)}&_=${Date.now()}`, {
            method: "GET",
            headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" }
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.length > 0 && data[0].url) {
                const categorySlug = data[0].url.split("/").filter(Boolean).pop();
                if (selectedLocation) {
                    let city = "";
                    let area = "";
                    if (selectedLocation.includes(",")) {
                        const parts = selectedLocation.split(",");
                        area = parts[0].trim();
                        city = parts[1].trim();
                    } else {
                        city = selectedLocation.trim();
                    }
                    let finalUrl = `${window.location.origin}/${slugify(city)}/${categorySlug}`;
                    if (area !== "") finalUrl += `-in-${slugify(area)}`;
                    window.location.href = finalUrl;
                    return;
                }
                window.location.href = data[0].url;
                return;
            }
            window.location.href = `{{ route('listings.search') }}?categorysearch=${encodeURIComponent(value)}&location_name=${encodeURIComponent(selectedLocation)}`;
        });
    });
});
</script>