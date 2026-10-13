{{-- Inlines one script from public/assets/servicebazzar/js/ at this exact spot,
     so it runs at the same moment the old inline <script> did (no extra
     request, no change in order). Edit the .js file, not this partial. Optional 'vars' fills
     __PLACEHOLDER__ tokens in the file with server values (see search-form). --}}
<script>{!! strtr(file_get_contents(public_path('assets/servicebazzar/js/' . $file)), $vars ?? []) !!}</script>
