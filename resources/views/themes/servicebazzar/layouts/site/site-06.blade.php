<script>
document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("CategorySearch");
    if (!input) return;
    const placeholders = [
        "Search for Packers and Movers",
        "Search for Electricians",
        "Search for Plumbers",
        "Search for Restaurants",
        "Search for Hotels",
        "Search for AC Repair",
        "Search for Bike Repair",
        "Search for Taxi Services"
    ];
    let index = 0;
    function changePlaceholder() {
        input.setAttribute("placeholder", placeholders[index]);
        index++;
        if (index >= placeholders.length) index = 0;
    }
    changePlaceholder();
    setInterval(changePlaceholder, 2000);
});
function sbJdhShare() {
    var data = { title: document.title, url: window.location.href.split('#')[0] };
    if (navigator.share) { navigator.share(data).catch(function () {}); return; }
    if (navigator.clipboard) {
        navigator.clipboard.writeText(data.url).then(function () { alert('Link copied'); });
    }
}

function goBackPage() {
    if (document.referrer && document.referrer !== "") {
        history.back();
    } else {
        window.location.href = "{{ route('home') }}";
    }
}
</script>