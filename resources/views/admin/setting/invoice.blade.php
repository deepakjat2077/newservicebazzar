@extends('admin.layouts.master')
@section('content')
@section('title','Invoice Setting')
@php
    $inv = $setting->invoice ?? null;
    $terms = is_array(@$inv->terms) ? implode("\n", $inv->terms) : (string) @$inv->terms;
@endphp
<div class="row justify-content-center">
	<div class="col-md-10">
		<div class="card m-b-30">
			<div class="card-header bg-color-primary d-flex justify-content-between align-items-center">
	            <h6 class="color-white mb-0">@lang('Invoice Setting')</h6>
	        </div>

			<form action="{{ route('admin.setting.invoice.update') }}" method="post">
				@csrf
				<div class="card-body">

					<div class="alert alert-info">
						@lang('These details are printed on every invoice emailed to a customer after payment. A wrong GSTIN makes the invoice legally invalid, so check it against the registration certificate.')
					</div>

					<h6 class="mb-3 text-primary">@lang('Company')</h6>

					<div class="form-group row">
						<label class="col-sm-3">@lang('Legal Name') <span class="text-danger">*</span></label>
						<div class="col-sm-9">
							<input type="text" name="legal_name" class="form-control" required
								placeholder="@lang('Exactly as on the GST / incorporation certificate')"
								value="{{ old('legal_name', @$inv->legal_name) }}">
						</div>
					</div>

					<div class="form-group row">
						<label class="col-sm-3">@lang('Trade Name')</label>
						<div class="col-sm-9">
							<input type="text" name="trade_name" class="form-control"
								placeholder="@lang('The name customers know you by')"
								value="{{ old('trade_name', @$inv->trade_name) }}">
						</div>
					</div>

					<div class="form-group row">
						<label class="col-sm-3">@lang('Registered Address')</label>
						<div class="col-sm-9">
							<textarea name="address" rows="2" class="form-control"
								placeholder="@lang('Building, street, area')">{{ old('address', @$inv->address) }}</textarea>
						</div>
					</div>

					<div class="form-group row">
						<label class="col-sm-3">@lang('City / State / Pincode')</label>
						<div class="col-sm-3">
							<input type="text" name="city" class="form-control" placeholder="@lang('City')"
								value="{{ old('city', @$inv->city ?: 'Jaipur') }}">
						</div>
						<div class="col-sm-3">
							<input type="text" name="state" class="form-control" placeholder="@lang('State')"
								value="{{ old('state', @$inv->state ?: 'Rajasthan') }}">
						</div>
						<div class="col-sm-3">
							<input type="text" name="pincode" class="form-control" placeholder="@lang('Pincode')"
								value="{{ old('pincode', @$inv->pincode) }}">
						</div>
						<div class="col-sm-9 offset-sm-3">
							<small class="text-muted">
								@lang('The state decides the tax split: same state as the buyer means CGST + SGST, a different state means IGST.')
							</small>
						</div>
					</div>

					<hr>
					<h6 class="mb-3 text-primary">@lang('Tax & Registration')</h6>

					<div class="form-group row">
						<label class="col-sm-3">@lang('GSTIN')</label>
						<div class="col-sm-9">
							<input type="text" name="gstin" class="form-control text-uppercase"
								placeholder="@lang('e.g. 08AAAAA0000A1Z5')"
								value="{{ old('gstin', @$inv->gstin) }}">
							<small class="text-muted">
								@lang('Leave blank if not registered — the document will then say INVOICE instead of TAX INVOICE and the GST lines will be hidden.')
							</small>
						</div>
					</div>

					<div class="form-group row">
						<label class="col-sm-3">@lang('CIN')</label>
						<div class="col-sm-9">
							<input type="text" name="cin" class="form-control text-uppercase"
								placeholder="@lang('e.g. U74999RJ2024PTC000000')"
								value="{{ old('cin', @$inv->cin) }}">
							<small class="text-muted">@lang('Companies only. A proprietorship or partnership has no CIN — leave it blank.')</small>
						</div>
					</div>

					<div class="form-group row">
						<label class="col-sm-3">@lang('PAN')</label>
						<div class="col-sm-9">
							<input type="text" name="pan" class="form-control text-uppercase"
								placeholder="@lang('e.g. AAAAA0000A')"
								value="{{ old('pan', @$inv->pan) }}">
						</div>
					</div>

					<div class="form-group row">
						<label class="col-sm-3">@lang('SAC Code')</label>
						<div class="col-sm-4">
							<input type="text" name="sac" class="form-control"
								value="{{ old('sac', @$inv->sac ?: '998365') }}">
						</div>
						<label class="col-sm-2">@lang('GST Rate %')</label>
						<div class="col-sm-3">
							<input type="number" step="0.01" name="gst_rate" class="form-control"
								value="{{ old('gst_rate', @$inv->gst_rate ?: 18) }}">
						</div>
						<div class="col-sm-9 offset-sm-3">
							<small class="text-muted">@lang('998365 is "sale of advertising space". Confirm with your accountant if unsure.')</small>
						</div>
					</div>

					<hr>
					<h6 class="mb-3 text-primary">@lang('Contact shown on the invoice')</h6>

					<div class="form-group row">
						<label class="col-sm-3">@lang('Mobile / Phone')</label>
						<div class="col-sm-9">
							<input type="text" name="phone" class="form-control"
								placeholder="@lang('e.g. +91 92510 67682')"
								value="{{ old('phone', @$inv->phone) }}">
						</div>
					</div>

					<div class="form-group row">
						<label class="col-sm-3">@lang('Email')</label>
						<div class="col-sm-9">
							<input type="email" name="email" class="form-control"
								value="{{ old('email', @$inv->email ?: 'info@servicebazzar.com') }}">
							<small class="text-muted">@lang('Invoices are also sent from this address, and customer replies come back here.')</small>
						</div>
					</div>

					<div class="form-group row">
						<label class="col-sm-3">@lang('Website')</label>
						<div class="col-sm-9">
							<input type="text" name="website" class="form-control"
								value="{{ old('website', @$inv->website ?: 'www.servicebazzar.com') }}">
						</div>
					</div>

					<hr>
					<h6 class="mb-3 text-primary">@lang('Bank Details') <small class="text-muted">(@lang('optional'))</small></h6>

					<div class="form-group row">
						<label class="col-sm-3">@lang('Bank / Account No')</label>
						<div class="col-sm-5">
							<input type="text" name="bank_name" class="form-control" placeholder="@lang('Bank name')"
								value="{{ old('bank_name', @$inv->bank_name) }}">
						</div>
						<div class="col-sm-4">
							<input type="text" name="bank_account" class="form-control" placeholder="@lang('Account number')"
								value="{{ old('bank_account', @$inv->bank_account) }}">
						</div>
					</div>

					<div class="form-group row">
						<label class="col-sm-3">@lang('IFSC / Branch')</label>
						<div class="col-sm-4">
							<input type="text" name="bank_ifsc" class="form-control text-uppercase" placeholder="@lang('IFSC')"
								value="{{ old('bank_ifsc', @$inv->bank_ifsc) }}">
						</div>
						<div class="col-sm-5">
							<input type="text" name="bank_branch" class="form-control" placeholder="@lang('Branch')"
								value="{{ old('bank_branch', @$inv->bank_branch) }}">
						</div>
						<div class="col-sm-9 offset-sm-3">
							<small class="text-muted">@lang('Leave all four blank and the bank block is left off the invoice.')</small>
						</div>
					</div>

					<hr>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Terms')</label>
						<div class="col-sm-9">
							<textarea name="terms" rows="4" class="form-control"
								placeholder="@lang('One term per line')">{{ old('terms', $terms) }}</textarea>
						</div>
					</div>
				</div>

				<div class="card-footer text-right">
					<button type="submit" class="btn btn-primary">@lang('Save')</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection
