@extends($themeView.'layouts.app')
@section('content')
@section('title', 'Import Leads — '.$listing->name)

<section class="pt-30 pb-80">
    <div class="container">
        <div class="sb-biz-header">
            <a href="{{ route('user.listing.dashboard', $listing->id) }}" class="sb-biz-back"><i class="fa-solid fa-arrow-left"></i></a>
            <div class="sb-biz-header-title">
                <h4>{{ $listing->name }}</h4>
                <span>{{ optional($listing->location)->name ?? $listing->area }}</span>
            </div>
        </div>

        <form action="{{ route('user.listing.import-leads.upload', $listing->id) }}" method="post" enctype="multipart/form-data" class="sb-import-form">
            @csrf
            <div class="sb-import-dropzone">
                <p><strong>Upload enquiries data from CSV/Excel File</strong></p>
                <label class="sb-import-choose-btn">
                    Choose Data File
                    <input type="file" name="data_file" accept=".csv,.xlsx,.xls" required>
                </label>
                <span class="sb-import-filename" id="sbImportFilename"></span>
            </div>

            <div class="sb-import-note">
                <div class="sb-import-note-head">
                    <strong>Important Note:</strong>
                    <a href="{{ route('user.listing.import-leads.sample') }}"><i class="fa-solid fa-download"></i> example.csv</a>
                </div>
                <ol>
                    <li>Supports CSV or excel file formats only</li>
                    <li>Enquiry date and mobile number is mandatory in Enquiries data</li>
                    <li>For upto 1000 records, data will be processed instantly. Larger files will take some time to process.</li>
                    <li>Max file size allowed - 2 MB</li>
                    <li>Only 1 year data from current day can be viewed</li>
                </ol>
            </div>

            <button type="submit" class="sb-wizard-primary-btn">Upload &amp; Import</button>
        </form>
    </div>
</section>
@endsection

@push('css')
<style>
    .sb-import-dropzone{
        background:#eaf4fe;
        border-radius:10px;
        padding:26px;
        text-align:center;
        margin-bottom:22px;
    }
    .sb-import-dropzone p{ margin-bottom:14px; font-size:15px; color:#222; }
    .sb-import-choose-btn{
        display:inline-block;
        background:#1e90ff;
        color:#fff;
        border-radius:6px;
        padding:11px 26px;
        font-size:14px;
        font-weight:600;
        cursor:pointer;
        position:relative;
    }
    .sb-import-choose-btn input[type="file"]{
        position:absolute;
        inset:0;
        opacity:0;
        cursor:pointer;
    }
    .sb-import-filename{
        display:block;
        margin-top:10px;
        font-size:12px;
        color:#555;
    }
    .sb-import-note{ margin-bottom:24px; }
    .sb-import-note-head{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:10px;
    }
    .sb-import-note-head a{ font-size:13px; color:#1e90ff; }
    .sb-import-note ol{ padding-left:20px; font-size:13px; color:#555; line-height:1.8; }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    var input = document.querySelector('.sb-import-choose-btn input[type="file"]');
    var out = document.getElementById('sbImportFilename');
    if (input && out) {
        input.addEventListener('change', function(){
            out.textContent = input.files && input.files[0] ? input.files[0].name : '';
        });
    }
});
</script>
@endpush
