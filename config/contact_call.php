<?php

return [

    /*
     * The listing cards print a "Contact" button instead of the business's
     * number. The visitor proves a mobile number with an OTP, we store the
     * lead, and only then is the call put through.
     *
     * Turn this off and every card goes back to printing the number as a
     * tel: link, exactly as before — the switch is read on each render, so
     * nothing has to be deployed to fall back.
     */
    'enabled' => env('CONTACT_CALL', true),

    /*
     * The same visitor ringing the same business again inside this window is
     * the same lead, not a new one — a dropped call redialled a minute later
     * must not arrive as a second unread enquiry. 0 = every call is a lead.
     */
    'repeat_minutes' => (int) env('CONTACT_CALL_REPEAT_MINUTES', 1440),

    /*
     * Whether to actually place the call through Exotel once the number is
     * verified. Off (or on a listing outside the Exotel pilot in
     * config/exotel.php) the popup shows the number instead, with a dial
     * button — the lead is stored either way.
     *
     * Each bridged call is billed by Exotel, which is why this is a switch of
     * its own and not just config('exotel.enabled').
     */
    'bridge' => env('CONTACT_CALL_BRIDGE', true),

];
