{{--
    Shared send controls for the listings tab and the enquiry detail page.
    Expects $templates (id => label), $defaultTemplate and, optionally,
    $enquiryId. The table on the page supplies the checkboxes: any input with
    class `wa-target` whose value is a listing id gets picked up.
--}}
<div class="card m-b-30">
    <div class="card-header bg-color-primary">
        <h6 class="color-white">@lang('Send WhatsApp')</h6>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-5">
                <label>@lang('Template ID')</label>
                <select class="form-control" id="waTemplateSelect">
                    @forelse($templates as $id => $label)
                        <option value="{{ $id }}" @if($id === $defaultTemplate) selected @endif>
                            {{ $label }} — {{ $id }}
                        </option>
                    @empty
                        <option value="" disabled selected>@lang('No template saved yet')</option>
                    @endforelse
                    <option value="__custom__">@lang('+ Add / use another Template ID')</option>
                </select>
                <small class="text-muted">
                    @lang('Saved templates come from')
                    <a href="{{ route('admin.setting.gupshup') }}">@lang('WhatsApp Setting')</a>.
                </small>
            </div>

            <div class="form-group col-md-4 d-none" id="waCustomTemplateWrap">
                <label>@lang('Custom Template ID')</label>
                <input type="text" class="form-control" id="waCustomTemplate"
                       placeholder="@lang('Paste an approved template id')">
                <small class="text-muted">@lang('Used for this send only.')</small>
            </div>

            <div class="form-group col-md-3">
                <label>&nbsp;</label>
                <button type="button" class="btn btn-success btn-block" id="waSendBtn" disabled>
                    <i class="fab fa-whatsapp"></i> @lang('Send to selected')
                    (<span id="waSelectedCount">0</span>)
                </button>
            </div>
        </div>

        <div class="form-group mb-0">
            <a href="#0" id="waToggleParams" class="small">@lang('Advanced: custom template variables')</a>
            <div class="mt-2 d-none" id="waParamsWrap">
                <textarea class="form-control" id="waParams" rows="3"
                          placeholder="@lang('One variable per line. Leave blank to fill them in automatically.')"></textarea>
                <small class="text-muted">
                    @lang('Left blank, an enquiry send fills the 7 enquiry variables and a plain listing send uses the listing name and URL. Only fill this in if your template takes something else — the number of lines must match the template.')
                </small>
            </div>
        </div>
    </div>
</div>

<div id="waResult"></div>
