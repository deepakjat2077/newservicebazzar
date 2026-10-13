{{-- Inlines one stylesheet from public/assets/servicebazzar/css/ into the page.
     The homepage's CSS lives in css/home/*.css (one file per former <style>
     block, same order) but is still sent inline: an external stylesheet cost
     ~7 mobile PageSpeed points when tried on 2026-09-03 (extra round trip
     before first paint). Edit the .css files, not this partial. --}}
<style>{!! file_get_contents(public_path('assets/servicebazzar/css/' . $file)) !!}</style>
