
document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("#scrollSearchHolder #headerSearchForm");
    if (!form) return;
    const locationInput = form.querySelector("#headerLocationInput");
    const categoryInput = form.querySelector("#headerCategorySearch");
    function titleCase(str){
        return String(str || "").replace(/-/g, " ").replace(/\b\w/g, c => c.toUpperCase());
    }
    function slugify(text){
        return String(text || "").toLowerCase().trim().replace(/,/g, "").replace(/\s+/g, "-").replace(/[^a-z0-9-]/g, "").replace(/-+/g, "-").replace(/^-+|-+$/g, "");
    }
    form.addEventListener("submit", function(e){
        e.preventDefault();
        const locationValue = locationInput.value.trim();
        const categoryValue = categoryInput.value.trim();
        if(categoryValue === ""){
            categoryInput.focus();
            return false;
        }
        if(locationValue === ""){
            locationInput.focus();
            return false;
        }
        let city = locationValue;
        let area = "";
        if(locationValue.includes(",")){
            const parts = locationValue.split(",");
            area = parts[0].trim();
            city = parts[1].trim();
        }
        let finalUrl = `${window.location.origin}/${slugify(city)}/${slugify(categoryValue)}`;
        if(area !== "") finalUrl += `-in-${slugify(area)}`;
        window.location.href = finalUrl;
        return false;
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("ytMobileSearchInput");
    if (!input) return;
    let clearBtn = document.createElement("span");
    clearBtn.className = "yt-mobile-clear-btn";
    clearBtn.innerHTML = '<i class="fas fa-times"></i>';
    input.parentNode.appendChild(clearBtn);
    function toggleClearBtn(){ clearBtn.style.display = input.value.trim() !== "" ? "block" : "none"; }
    input.addEventListener("input", toggleClearBtn);
    clearBtn.addEventListener("click", function(){
        input.value = "";
        clearBtn.style.display = "none";
        input.focus();
        const sug = document.getElementById("ytMobileCategorySuggestions");
        if(sug){
            sug.classList.remove("show");
            sug.innerHTML = "";
        }
    });
    toggleClearBtn();
});




