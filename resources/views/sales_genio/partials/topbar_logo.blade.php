{{-- The Service Bazzar logo beside the back arrow in an edit page's header, as on the business profile.
     Not a link: a stray tap must not leave a half-filled form. --}}
<img class="tb-logo" src="{{ asset('assets/images/logo/logo.png') }}" alt="Service Bazzar" width="128" height="28">
<style>
    .topbar .back { margin-right: 12px; }
    .topbar .tb-logo { height: 28px; width: auto; flex-shrink: 0; display: block; margin-right: 10px; }
    /* The title now sits in what is left of the row, so it needs no balancing margin. */
    /* The title takes up the rest of the row, so the arrow and logo stay together even on pages
       whose header spreads its items (Photo Gallery) — otherwise the logo drifts to the middle. */
    .topbar .tb-logo ~ .title { flex: 1; text-align: center; margin-right: 0; line-height: 1.25; }
    @media (max-width: 480px) {
        .topbar .tb-logo { height: 22px; margin-right: 8px; }
        .topbar .tb-logo ~ .title { font-size: 16px; }
    }
</style>
