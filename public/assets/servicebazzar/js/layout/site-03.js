
document.addEventListener("DOMContentLoaded", function () {
    const mobileBottomAction = document.getElementById("mobileBottomAction");
    if (!mobileBottomAction) return;
    window.addEventListener("scroll", function () {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        if (window.innerWidth <= 991) {
            if (scrollTop > 50) {
                mobileBottomAction.classList.add("hide-on-scroll");
            } else {
                mobileBottomAction.classList.remove("hide-on-scroll");
            }
        }
    });
});
