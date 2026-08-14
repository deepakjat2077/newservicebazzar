{{--
    Pincode already fills City, and Location is the same city by another name
    — so picking it by hand was pure repetition, and forgetting to was an easy
    way to save a listing against the wrong city.

    Both fields stay: City is free text from India Post, Location is the row
    listings are actually grouped by. This just keeps the second in step with
    the first.

    Exposed as a global so each form's own pincode handler can call it right
    after it sets #city.
--}}
@push('js')
<script>
(function ($) {
    "use strict";

    // "New Delhi" and "new-delhi" have to compare equal.
    function words(value) {
        return (value || '')
            .toString()
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, ' ')
            .trim()
            .split(' ')
            .filter(Boolean);
    }

    function isSubset(small, big) {
        return small.length > 0 && small.every(function (w) {
            return big.indexOf(w) !== -1;
        });
    }

    /**
     * Select the Location that matches a city name. Returns true if it found
     * one. Leaves the field alone when nothing matches, so an unlisted city
     * still gets picked by hand rather than silently landing on the wrong row.
     */
    window.autoSelectListingLocation = function (city) {
        var $select = $('#location_id');

        if (!$select.length) {
            return false;
        }

        var target = words(city);

        if (!target.length) {
            return false;
        }

        var exact = null;
        var partial = null;
        var partialSize = 0;

        $select.find('option').each(function () {
            if (!this.value) {
                return;
            }

            var name = words($(this).text());

            if (!name.length) {
                return;
            }

            if (name.join(' ') === target.join(' ')) {
                exact = this.value;
                return false;
            }

            /*
             * Whole-word containment both ways, never substrings: India Post
             * says "New Delhi" where the location row is "Delhi", and
             * "Mumbai" where it is "Navi Mumbai". Matching on raw substrings
             * would also pair "Pune" with "Rajpura".
             *
             * Longest match wins, so "Navi Mumbai" beats "Mumbai".
             */
            if (isSubset(name, target) || isSubset(target, name)) {
                if (name.length > partialSize) {
                    partial = this.value;
                    partialSize = name.length;
                }
            }
        });

        var picked = exact || partial;

        if (!picked) {
            $('#locationAutoNote').addClass('d-none');

            return false;
        }

        if ($select.val() !== picked) {
            // change, so the location-filtered subcategory list reloads.
            $select.val(picked).trigger('change');
        }

        $('#locationAutoNote')
            .removeClass('d-none')
            .text('Auto-selected from pincode. Change it if this is wrong.');

        return true;
    };
})(jQuery);
</script>
@endpush
