@extends($themeView.'layouts.app')
@section('content')
@section('title', 'Business Categories — '.$listing->name)

<section class="pt-30 pb-80">
    <div class="container" style="max-width:640px;">
        <div class="sb-biz-header">
            <a href="{{ request('from') === 'manage' ? route('user.listing.manage', $listing->id) : route('user.listing.dashboard', $listing->id) }}" class="sb-biz-back"><i class="fas fa-arrow-left"></i></a>
            <div class="sb-biz-header-title">
                <h4>Add Business Categories</h4>
                <span>{{ $listing->name }}</span>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('user.listing.business-categories.update', $listing->id) }}" method="post" id="sbCategoriesForm">
            @csrf

            <p class="sb-contact-section-label">Add Categories</p>
            <div id="sbCatSearchWrap">
                <div class="sb-cat-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="sbCatSearchInput" class="sb-input sb-cat-search-input" placeholder="Search category" autocomplete="off">
                </div>
                <div class="sb-cat-suggestions" id="sbCatSuggestions" hidden></div>
            </div>

            <p class="sb-contact-section-label">Selected Categories</p>
            <div class="sb-cat-chips" id="sbCatChips">
                @foreach($selected as $sub)
                    <span class="sb-cat-chip" data-id="{{ $sub->id }}">
                        {{ $sub->name }}
                        <button type="button" class="sb-cat-remove"><i class="fas fa-times"></i></button>
                        <input type="hidden" name="subcategory_ids[]" value="{{ $sub->id }}">
                    </span>
                @endforeach
            </div>

            <button type="submit" class="sb-wizard-primary-btn sb-biz-form-submit">
                Save and Proceed <i class="fas fa-arrow-right"></i>
            </button>
        </form>
    </div>
</section>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('assets/servicebazzar/css/business-panel.css') }}">
<style>
    .sb-cat-chips{ display:flex; flex-wrap:wrap; gap:8px; margin-bottom:16px; }
    .sb-cat-chip{
        display:inline-flex;
        align-items:center;
        gap:8px;
        border:1px solid #1e90ff;
        border-radius:20px;
        padding:6px 8px 6px 14px;
        font-size:12.5px;
        color:#1e90ff;
        font-weight:600;
    }
    .sb-cat-remove{ border:0; background:transparent; color:#1e90ff; font-size:11px; cursor:pointer; }
    .sb-cat-search-box{
        display:flex;
        align-items:center;
        gap:10px;
        border:1px solid #d5d9dd;
        border-radius:6px;
        padding:9px 12px;
        margin-bottom:4px;
    }
    .sb-cat-search-box i{ color:#888; font-size:13px; }
    .sb-cat-search-input{ border:0; padding:0; }
    .sb-cat-search-input:focus{ outline:none; box-shadow:none; }
    .sb-cat-suggestions{
        border:1px solid #eceef0;
        border-radius:8px;
        max-height:280px;
        overflow-y:auto;
        margin-bottom:16px;
    }
    .sb-cat-suggestion{
        padding:12px 14px;
        font-size:13.5px;
        color:#333;
        border-bottom:1px solid #f3f3f3;
        cursor:pointer;
    }
    .sb-cat-suggestion:hover{ background:#f7f8f9; }
    .sb-cat-suggestion mark{ background:transparent; color:#111; font-weight:700; padding:0; }
    .sb-cat-note{ color:#888; cursor:default; }
    .sb-cat-note:hover{ background:transparent; }
    .sb-biz-form-submit{ display:block; width:100%; margin-top:10px; }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    var chipsWrap = document.getElementById('sbCatChips');
    var searchInput = document.getElementById('sbCatSearchInput');
    var suggestBox = document.getElementById('sbCatSuggestions');
    // Root-relative on purpose: an absolute route() URL can come out as http://
    // behind a proxy while the page is https:// (or on a different host), and
    // the browser then blocks the fetch as mixed/cross-origin content. Stripping
    // the scheme+host makes the request go to the current origin over the same
    // protocol, which is what we always want here.
    var lookupUrl = @json(route('user.listing.business-categories.lookup', $listing->id)).replace(/^https?:\/\/[^/]+/i, '');
    var csrfToken = document.querySelector('#sbCategoriesForm input[name=_token]').value;
    var timer = null;
    var seq = 0; // guards against slow older responses clobbering newer ones

    function selectedIds(){
        return Array.from(chipsWrap.querySelectorAll('.sb-cat-chip')).map(function(c){ return c.getAttribute('data-id'); });
    }

    function bindRemove(chip){
        chip.querySelector('.sb-cat-remove').addEventListener('click', function(){ chip.remove(); });
    }
    chipsWrap.querySelectorAll('.sb-cat-chip').forEach(bindRemove);

    function addChip(sub){
        if (selectedIds().indexOf(String(sub.id)) > -1) return;

        var chip = document.createElement('span');
        chip.className = 'sb-cat-chip';
        chip.setAttribute('data-id', sub.id);
        chip.innerHTML = sub.name + ' <button type="button" class="sb-cat-remove"><i class="fas fa-times"></i></button>' +
            '<input type="hidden" name="subcategory_ids[]" value="' + sub.id + '">';
        chipsWrap.appendChild(chip);
        bindRemove(chip);
    }

    function showNote(text){
        suggestBox.innerHTML = '<div class="sb-cat-suggestion sb-cat-note">' + text + '</div>';
        suggestBox.hidden = false;
    }

    searchInput.addEventListener('input', function(){
        var q = searchInput.value.trim();
        clearTimeout(timer);

        // Suggest from the very first character typed.
        if (q.length < 1) { suggestBox.hidden = true; suggestBox.innerHTML = ''; return; }

        var mySeq = ++seq;
        timer = setTimeout(function(){
            fetch(lookupUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: new URLSearchParams({ q: q }).toString()
            })
                .then(function(r){
                    if (!r.ok) { var st = r.status; return r.text().then(function(){ throw new Error('HTTP ' + st); }); }
                    return r.json();
                })
                .then(function(rows){
                    if (mySeq !== seq) return; // a newer keystroke already fired
                    suggestBox.innerHTML = '';
                    if (!Array.isArray(rows) || !rows.length) { showNote('No matching category found.'); return; }

                    rows.forEach(function(sub){
                        var row = document.createElement('div');
                        row.className = 'sb-cat-suggestion';
                        row.textContent = sub.name;
                        row.addEventListener('click', function(){
                            addChip(sub);
                            searchInput.value = '';
                            suggestBox.hidden = true;
                        });
                        suggestBox.appendChild(row);
                    });
                    suggestBox.hidden = false;
                })
                .catch(function(err){ if (mySeq === seq) showNote('Could not load suggestions (' + (err && err.message ? err.message : 'network error') + ').'); });
        }, 250);
    });
});
</script>
@endpush
