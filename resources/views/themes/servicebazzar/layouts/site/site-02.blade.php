<script>
document.addEventListener("DOMContentLoaded", function () {

    const header = document.getElementById("stickyScrollHeader");
    const hideMenus = document.querySelectorAll(".hide-on-scroll-menu");

    const isHomePage = @json(
        request()->routeIs('home') ||
        request()->routeIs('home.city')
    );

    // Home ke alawa sab pages par hide rakho
    if (!isHomePage) {
    hideMenus.forEach(function(item){
        item.style.display = "none";
    });
}

    function toggleHeaderScroll() {

        const scrollLimit = 160;

        if (window.scrollY > scrollLimit) {

            header?.classList.add("scrolled");
            document.body.classList.add("header-fixed");

            hideMenus.forEach(function(item){
                item.style.display = "none";
            });

        } else {

            header?.classList.remove("scrolled");
            document.body.classList.remove("header-fixed");

            hideMenus.forEach(function(item){
                item.style.display = "";
            });
        }
    }

    window.addEventListener("scroll", toggleHeaderScroll);

    setTimeout(toggleHeaderScroll, 300);

});
</script>