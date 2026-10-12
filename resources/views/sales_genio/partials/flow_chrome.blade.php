{{-- The campaign and payment steps stand on their own, like an app flow: no portal top bar and
     no left menu on any screen size. (Phones already hid them.) The left menu also pushed every
     page element across by its own width, so that space is handed back too. --}}
<style>
    .sg-nav, .sg-rail, .sg-bottom { display: none !important; }
    /* Hand back the space the menu reserved, to everything except the page wrappers... */
    body > *:not(.sg-nav):not(.sg-rail):not(.sg-bottom):not(.cmp-wrap):not(.ct-wrap):not(.pay-wrap):not(.gst-wrap):not(.ol-wrap):not(.wt-wrap):not(.fl-top) { margin-left: 0 !important; }
    /* ...which centre themselves again (a flat zero here is what left them hugging the left edge). */
    body > .cmp-wrap, body > .ct-wrap, body > .pay-wrap, body > .gst-wrap, body > .ol-wrap, body > .wt-wrap, body > .fl-top { margin-left: auto !important; margin-right: auto !important; }
</style>
