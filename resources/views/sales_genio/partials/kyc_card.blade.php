@php
    $items = $docs[$key] ?? collect();
    // A row may hold only a document number, with no scan behind it yet.
    $files = $items->whereNotNull('path');
    // Address proof and Aadhaar have two sides; office photos are many.
    $multi = in_array($key, ['office_photos', 'aadhaar', 'address']);
    $badge = null;
    if ($files->count()) {
        $badge = $files->first()->status; // uploaded | in_review | approved | rejected
    } elseif ($t[1]) {
        $badge = 'req';
    }
    $badgeLabel = ['req'=>'Required','uploaded'=>'Uploaded','in_review'=>'In Review','approved'=>'Approved','rejected'=>'Rejected'][$badge] ?? '';
@endphp
<div class="kyc-card">
    @if($badge)<span class="badge {{ $badge }}">{{ $badgeLabel }}</span>@endif
    <div class="ic"><i class="fas {{ $icon }}"></i></div>
    <div class="nm">{{ $t[0] }}</div>
    @if($t[1])<div class="mand">Mandatory</div>@endif
    @if($key === 'aadhaar')<div class="mand" style="color:#888;">Front &amp; Back</div>@endif

    @if($files->count())
        <div class="files">
            @foreach($files as $it)
                <span class="file-chip">
                    <a href="{{ asset($it->path) }}" target="_blank"><i class="fas fa-file"></i> View</a>
                    <span class="rm" onclick="delKyc({{ $it->id }})"><i class="fas fa-xmark"></i></span>
                </span>
            @endforeach
        </div>
    @endif

    @php
        // Documents that carry a number worth keeping — the PAN and GST
        // numbers are printed on the customer's invoice.
        $numbered = ['pan' => 'PAN Number', 'gst' => 'GST Number', 'aadhaar' => 'Aadhaar Number',
                     'msme' => 'Udyam Number', 'driving_licence' => 'Licence Number', 'voter_id' => 'Voter ID Number'];
        $savedNumber = $items->pluck('doc_number')->filter()->first();
    @endphp

    @if(isset($numbered[$key]))
        <div class="doc-no">
            <span class="lbl">{{ $numbered[$key] }}</span>
            @if($savedNumber)
                <b>{{ $savedNumber }}</b>
                <span class="edit" onclick="editNumber('{{ $key }}', '{{ $numbered[$key] }}', @js($savedNumber))">
                    <i class="fas fa-pen"></i></span>
            @else
                <span class="add" onclick="editNumber('{{ $key }}', '{{ $numbered[$key] }}', '')">
                    <i class="fas fa-plus"></i> Add number</span>
            @endif
        </div>
    @endif

    <div class="upl" onclick="pickFile('{{ $key }}', {{ $multi ? 'true' : 'false' }})">
        <i class="fas fa-upload"></i>
        @if($multi && $files->count())
            {{ in_array($key, ['address', 'aadhaar']) ? ($files->count() < 2 ? 'Add back side' : 'Add more') : 'Add more' }}
        @else
            {{ $files->count() && !$multi ? 'Replace' : 'Upload' }}
        @endif
    </div>
</div>
