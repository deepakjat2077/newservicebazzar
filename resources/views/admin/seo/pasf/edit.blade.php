@extends('admin.layouts.master')
@section('title','People Also Search: ' . $row->location->name)
@section('content')

<style>
.pasf-item { border:1px solid #e2e8f0; border-radius:8px; padding:14px; margin-bottom:12px; background:#fff; }
.pasf-item.removed { opacity:.45; }
.pasf-kw { font-size:12px; color:#64748b; }
</style>

@php $pageUrl = url('/' . $row->location->slug . '/' . $row->category->slug); @endphp

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
    <div>
        <a href="{{ route('admin.seo.pasf.index', ['category' => $row->category_id]) }}">&larr; All cities</a>
        <h4 class="mb-0 mt-1">{{ $row->category->name }} in {{ $row->location->name }}</h4>
        <a href="{{ $pageUrl }}" target="_blank" rel="noopener" class="small">{{ $pageUrl }}</a>
    </div>
    <div class="mt-2">
        <a href="{{ $row->status === 'approved' ? $pageUrl . '#pasf-1' : $row->previewUrl() }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary mr-2">
            <i class="fas fa-eye"></i> {{ $row->status === 'approved' ? 'See on page' : 'Preview on page' }}
        </a>
        @if($row->status === 'approved')
            <span class="badge badge-success p-2">Live on the page</span>
        @else
            <span class="badge badge-warning p-2">Draft: not on the page yet</span>
        @endif
    </div>
</div>

<div class="alert alert-info small">
    Check each answer against the businesses' own pages. Fix anything wrong, or tick <strong>Remove</strong>.
    Business names in the answers are linked to their pages automatically.
    <span class="d-block mt-1">
        Searches found on Google ({{ ['serpapi' => 'People also ask / Related searches', 'serpapi+autocomplete' => 'People also ask / Related searches + search suggestions'][$row->keyword_source] ?? 'Google search suggestions' }}):
        <em>{{ implode(', ', $row->keywords ?? []) }}</em>
    </span>
</div>

<form method="POST" action="{{ route('admin.seo.pasf.update', $row->id) }}">
    @csrf
    @foreach($row->faqs ?? [] as $i => $faq)
        <div class="pasf-item">
            <div class="d-flex justify-content-between">
                <span class="pasf-kw">Searches: {{ implode(', ', $faq['keywords'] ?? [$faq['keyword']]) ?: '-' }}</span>
                <label class="mb-0 small text-danger">
                    <input type="checkbox" name="faqs[{{ $i }}][remove]" value="1" class="pasf-remove"> Remove
                </label>
            </div>
            @foreach($faq['keywords'] ?? [$faq['keyword']] as $kw)
                <input type="hidden" name="faqs[{{ $i }}][keywords][]" value="{{ $kw }}">
            @endforeach
            <label class="small mb-1 mt-2">Question</label>
            <input type="text" name="faqs[{{ $i }}][question]" value="{{ $faq['question'] }}" class="form-control" maxlength="200">
            <label class="small mb-1 mt-2">Answer</label>
            <textarea name="faqs[{{ $i }}][answer]" class="form-control" rows="3" maxlength="600">{{ $faq['answer'] }}</textarea>
        </div>
    @endforeach

    {{-- The action travels in a hidden field, not on the button: a button's
         own name/value is dropped whenever anything submits the form in
         script, and the approval was silently saving as a draft. --}}
    <input type="hidden" name="action" id="pasfAction" value="save">

    <div class="d-flex flex-wrap" style="gap:8px">
        <button type="submit" class="btn btn-success" data-pasf-action="approve">
            {{ $row->status === 'approved' ? 'Save (stays live)' : 'Save & Approve (show on page)' }}
        </button>
        @if($row->status !== 'approved')
            <button type="submit" class="btn btn-outline-secondary" data-pasf-action="save">Save as draft</button>
        @endif
    </div>
</form>

<div class="d-flex flex-wrap mt-4 pt-3 border-top" style="gap:8px">
    <form method="POST" action="{{ route('admin.seo.pasf.generate') }}" onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').textContent='Writing... (up to 30 sec)';">
        @csrf
        <input type="hidden" name="category_id" value="{{ $row->category_id }}">
        <input type="hidden" name="location_id" value="{{ $row->location_id }}">
        <button class="btn btn-sm btn-outline-primary">Write again from scratch{{ $row->status === 'approved' ? ' (comes off the page until approved)' : '' }}</button>
    </form>
    @if($row->status === 'approved')
        <form method="POST" action="{{ route('admin.seo.pasf.unpublish', $row->id) }}">
            @csrf
            <button class="btn btn-sm btn-outline-danger">Remove from page</button>
        </form>
    @endif
</div>

<script>
    document.querySelectorAll('[data-pasf-action]').forEach(function (b) {
        b.addEventListener('click', function () {
            document.getElementById('pasfAction').value = b.getAttribute('data-pasf-action');
        });
    });

    document.querySelectorAll('.pasf-remove').forEach(function (c) {
        c.addEventListener('change', function () { c.closest('.pasf-item').classList.toggle('removed', c.checked); });
    });
</script>
@endsection
