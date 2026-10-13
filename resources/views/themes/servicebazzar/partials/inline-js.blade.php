{{-- Inlines one script from public/assets/servicebazzar/js/ at this exact spot,
     so it runs at the same moment the old inline <script> did (no extra
     request, no change in order). Edit the .js file, not this partial. --}}
<script>{!! file_get_contents(public_path('assets/servicebazzar/js/' . $file)) !!}</script>
