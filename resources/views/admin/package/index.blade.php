@extends('admin.layouts.master')
@section('content')
@section('title','Package')
<div class="row">
	<div class="col-md-12">
		<div class="card">
			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table">
						<thead>
							<tr>
								<th scope="col">@lang('SL')</th>
								<th scope="col">@lang('Name')</th>
								<th scope="col">@lang('Price')</th>
								<th scope="col">@lang('Validity')</th>
								<th scope="col">@lang('Status')</th>
								<th scope="col">@lang('Action')</th>
							</tr>
						</thead>
						<tbody class="cur-body">
							@forelse($packages as $package)
							<tr>
								<td>{{ $loop->iteration }}</td>
								<td>{{ __($package->name) }}</td>
								<td>{{ numberFormat($package->price) }} {{ $setting->site_currency }}</td>
								<td>{{ __($package->validity) }}</td>
								<td>
									@if($package->status == 1)
									<span class="badge badge-success">@lang('Enable')</span>
									@else
									<span class="badge badge-danger">@lang('Disable')</span>
									@endif
								</td>
								<td>
									<button class="btn btn-primary btn-sm ediBtn"

									data-name="{{ $package->name }}"
									data-price="{{ numberFormat($package->price) }}"
									data-old_price="{{ $package->old_price }}"
									data-listing_allow="{{ $package->listing_allow }}"
									data-features_allow="{{ $package->features_allow }}"
									data-featured_allow="{{ $package->featured_allow }}"
									data-image_allow="{{ $package->image_allow }}"
									data-contact_allow="{{ $package->contact_allow }}"
									data-validity="{{ $package->validity }}"
									data-features="{{ $package->features }}"
									data-tag="{{ $package->tag }}"
									data-is_popular="{{ $package->is_popular }}"
									data-action="{{ route('admin.package.update',$package->id) }}"

									 data-toggle="modal" data-target="#packageEditModal"><i class="fas fa-pen"></i></button>
									@if($package->status == 1)
										<a href="{{ route('admin.package.status',$package->id) }}" class="btn btn-danger btn-sm"><i class="fas fa-eye-slash"></i></a>
									@else
										<a href="{{ route('admin.package.status',$package->id) }}" class="btn btn-success btn-sm"><i class="fas fa-eye"></i></a>
									@endif

 <!-- Delete -->
    <button
        class="btn btn-sm btn-danger deleteBtn"
        data-id="{{ $package->id }}"
        data-url="{{ route('admin.package.delete', $package->id) }}">
        <i class="fas fa-trash"></i>
    </button>

    <!-- Hidden Form -->
    <form id="delete-form-{{ $package->id }}"
          action="{{ route('admin.package.delete', $package->id) }}"
          method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

								</td>
							</tr>
							@empty
							<tr>
								<td class="text-center" colspan="100%">@lang('Data not found')</td>
							</tr>
							@endforelse
						</tbody>
					</table>
		        </div>
			</div>
		</div>
	</div>
</div>

{{-- ===================== Service Bazzar Certificate packages ===================== --}}
<div class="row mt-4">
	<div class="col-md-12">
		<div class="card">
			<div class="card-body p-0">
				<div class="p-3 border-bottom d-flex justify-content-between align-items-center">
					<h5 class="mb-0"><i class="fas fa-box-open text-primary mr-1"></i> @lang('Sales Packages')</h5>
					<button class="btn btn-sm btn-primary certAddBtn" data-toggle="modal" data-target="#certFormModal"><i class="fas fa-plus"></i> @lang('Add Package')</button>
				</div>
				<div class="table-responsive">
					<table class="table">
						<thead>
							<tr>
								<th>@lang('SL')</th>
								<th>@lang('Image')</th>
								<th>@lang('Name')</th>
								<th>@lang('Plans')</th>
								<th>@lang('Status')</th>
								<th>@lang('Action')</th>
							</tr>
						</thead>
						<tbody>
							@forelse($certificates as $cert)
							@php
								$certJson = [
									'name'     => $cert->name,
									'image'    => $cert->imageUrl(),
									'images'   => $cert->imageUrls(),
									'benefits'   => $cert->benefitList(),
									'highlights' => $cert->highlightList(),
									'plans'      => $cert->planList(),
									'pricing'    => $cert->pricing_type,
									'needsLoc'   => $cert->needsLocation(),
									'positions'  => $cert->positions,
									'tree'       => $cert->isPositionPriced() ? $cert->positionPriceTree() : null,
								];
							@endphp
							<tr>
								<td>{{ $loop->iteration }}</td>
								<td>
									<a href="javascript:void(0)" class="certViewBtn" data-cert='@json($certJson)' title="@lang('Click to view')">
										@if($cert->imageUrl())
										<img src="{{ $cert->imageUrl() }}" alt="" style="width:90px;height:60px;object-fit:cover;border-radius:4px;border:1px solid #e3e6f0;">
										@else
										<span class="badge badge-light">@lang('No image')</span>
										@endif
									</a>
								</td>
								<td><a href="javascript:void(0)" class="certViewBtn font-weight-bold" data-cert='@json($certJson)'>{{ $cert->name }}</a></td>
								<td>
									@if($cert->needsLocation())
										<span class="badge badge-dark mb-1">{{ implode(' · ', $cert->tierLabels()) }} — @lang('price by city/area')</span>
										<br><small class="text-muted">{{ $cert->positionPrices()->distinct('city')->count('city') }} @lang('cities'), {{ $cert->positionPrices()->count() }} @lang('prices')</small>
									@else
										@foreach($cert->planList() as $pl)
											<span class="badge badge-info mb-1">{{ $pl['label'] }} — ₹{{ numberFormat($pl['price']) }}</span>
										@endforeach
									@endif
								</td>
								<td>
									@if($cert->status)
									<span class="badge badge-success">@lang('Enable')</span>
									@else
									<span class="badge badge-danger">@lang('Disable')</span>
									@endif
								</td>
								<td style="white-space:nowrap;">
									<button class="btn btn-info btn-sm certViewBtn" data-cert='@json($certJson)' title="@lang('View')"><i class="fas fa-eye"></i></button>
									@if($cert->needsLocation())
									<a href="{{ route('admin.package.certificate.positions', $cert->id) }}" class="btn btn-sm btn-success" title="@lang('Prices by city/area')"><i class="fas fa-rupee-sign"></i></a>
									@endif
									<button class="btn btn-primary btn-sm certEditBtn" title="@lang('Edit')"
										data-cert='@json($certJson)'
										data-benefits="{{ $cert->benefits }}"
										data-highlights="{{ $cert->highlights }}"
										data-pricing="{{ $cert->pricing_type }}"
										data-positions="{{ $cert->positions }}"
										data-needs_ad="{{ (int) $cert->needs_ad_details }}"
										data-action="{{ route('admin.package.certificate.update', $cert->id) }}"
										data-toggle="modal" data-target="#certFormModal"><i class="fas fa-pen"></i></button>
									<a href="{{ route('admin.package.certificate.status', $cert->id) }}" class="btn btn-sm {{ $cert->status ? 'btn-danger' : 'btn-success' }}" title="{{ $cert->status ? 'Disable' : 'Enable' }}"><i class="fas {{ $cert->status ? 'fa-eye-slash' : 'fa-toggle-on' }}"></i></a>
									<button class="btn btn-sm btn-danger certDelBtn" data-id="{{ $cert->id }}"><i class="fas fa-trash"></i></button>
									<form id="cert-delete-{{ $cert->id }}" action="{{ route('admin.package.certificate.delete', $cert->id) }}" method="POST" style="display:none;">
										@csrf @method('DELETE')
									</form>
								</td>
							</tr>
							@empty
							<tr><td class="text-center" colspan="100%">@lang('No package yet — click "Add Package".')</td></tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

{{-- Add / Edit certificate modal --}}
<div class="modal fade" id="certFormModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="certFormTitle">@lang('Add Package')</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<form id="certForm" action="{{ route('admin.package.certificate.store') }}" data-store="{{ route('admin.package.certificate.store') }}" method="post" enctype="multipart/form-data">
				@csrf
				<div class="modal-body">
					<div class="form-group row">
						<label class="col-sm-3">@lang('Name')</label>
						<div class="col-sm-9">
							<input type="text" name="name" class="form-control" placeholder="Service Bazzar Certificate" required>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Main Image')</label>
						<div class="col-sm-9">
							<input type="file" name="image" accept="image/*" class="form-control-file" id="certImageInput">
							<small class="text-muted d-block" id="certImageHelp">@lang('Shown first in the package preview (JPG/PNG/WebP, max 10MB). Saved as WebP.')</small>
							<img id="certImagePreview" src="" alt="" style="display:none;max-width:220px;margin-top:8px;border:1px solid #e3e6f0;border-radius:4px;">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Extra Images')</label>
						<div class="col-sm-9">
							<input type="file" name="gallery[]" accept="image/*" multiple class="form-control-file">
							<small class="text-muted d-block">@lang('Optional. More slides after the main image, e.g. web + mobile views (up to 6).')</small>
							<div id="certGalleryCurrent" class="mt-2" style="display:none;">
								<div id="certGalleryThumbs" class="d-flex flex-wrap" style="gap:6px;"></div>
								<div class="custom-control custom-checkbox mt-1">
									<input type="checkbox" class="custom-control-input" id="certClearGallery" name="clear_gallery" value="1">
									<label class="custom-control-label" for="certClearGallery">@lang('Remove current extra images')</label>
								</div>
							</div>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Benefits You get')</label>
						<div class="col-sm-9">
							<textarea name="benefits" rows="4" class="form-control" placeholder="One benefit per line, e.g.&#10;Get Service Bazzar Reviews and Ratings Certificate for your business every year&#10;Better conversion from walk-in customers&#10;Laminated certificate with fibre frame to display customer ratings"></textarea>
							<small class="text-muted">@lang('One benefit per line — shown with a green tick.')</small>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Highlights')</label>
						<div class="col-sm-9">
							<textarea name="highlights" rows="2" class="form-control" placeholder="Optional, one per line, e.g.&#10;Official Business Verified Certificate"></textarea>
							<small class="text-muted">@lang('Shown as chips under the benefits. Blue by default; a line with "Purchased"/"Customers" shows green with a cart, a line with "Trust" shows gold.')</small>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Pricing Type')</label>
						<div class="col-sm-9">
							<select name="pricing_type" id="certPricingType" class="form-control">
								<option value="plan">@lang('Same price everywhere (plans below)')</option>
								<option value="position">@lang('Top position — price by city / area')</option>
								<option value="location">@lang('Same plans, but a different price in each city / area')</option>
							</select>
							<small class="text-muted">@lang('For the last two, set the prices from the green ₹ button after saving.')</small>
						</div>
					</div>
					<div class="form-group row" id="certPositionsRow" style="display:none;">
						<label class="col-sm-3">@lang('How many positions')</label>
						<div class="col-sm-9">
							<input type="number" name="positions" min="1" max="10" value="3" class="form-control" style="max-width:120px;">
							<small class="text-muted">@lang('e.g. 3 for Top 1, Top 2, Top 3. Set the city/area prices from the green ₹ button after saving.')</small>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Ad Details')</label>
						<div class="col-sm-9">
							<div class="custom-control custom-switch">
								<input type="checkbox" class="custom-control-input" id="certNeedsAd" name="needs_ad_details" value="1">
								<label class="custom-control-label" for="certNeedsAd">@lang('After payment, ask the buyer for their banner details and build the banner automatically')</label>
							</div>
						</div>
					</div>
					<div class="form-group row" id="certPlansRow">
						<label class="col-sm-3">@lang('Plans & Price')</label>
						<div class="col-sm-9">
							<div id="certPlanRows"></div>
							<button type="button" class="btn btn-sm btn-outline-primary" id="certAddPlan"><i class="fas fa-plus"></i> @lang('Add Plan')</button>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
					<button type="submit" class="btn btn-primary">@lang('Submit')</button>
				</div>
			</form>
		</div>
	</div>
</div>

{{-- JDRR-style preview modal --}}
<div class="modal fade" id="certViewModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document" style="max-width:960px;">
		<div class="modal-content" style="border-radius:12px;">
			<div class="modal-body p-4">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="cert-view">
					<div class="cert-view__img">
						<div class="cert-slides"><img id="cvImg" src="" alt=""></div>
						<div id="cvDots" class="cert-dots"></div>
					</div>
					<div class="cert-view__body">
						<h3 id="cvName"></h3>
						<h6>@lang('Benefits You get')</h6>
						<ul id="cvBenefits"></ul>
						<div id="cvHighlights" class="cert-chips"></div>
						<div id="cvLocation" class="cert-loc" style="display:none;">
							<div>
								<label>@lang('City')</label>
								<select id="cvCity" class="form-control form-control-sm"></select>
							</div>
							<div>
								<label>@lang('Area')</label>
								<select id="cvArea" class="form-control form-control-sm"></select>
							</div>
						</div>
						<h6 id="cvPlanHead">@lang('Select Plan')</h6>
						<div id="cvPlans" class="cert-plans"></div>
						<p id="cvNoPrice" class="text-muted" style="display:none;font-size:13px;">@lang('No price set for this city/area yet — add it from the ₹ button on the package list.')</p>
						<div class="cert-view__actions">
							<button type="button" class="btn cert-btn-outline" data-dismiss="modal">@lang('Close')</button>
							<div class="cert-total">@lang('Total'): <strong id="cvTotal"></strong></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>



<!-- Modal -->
	<div class="modal fade" id="packageModal" tabindex="-1" role="dialog" aria-labelledby="packageModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="packageModalLabel">@lang('Add New Package')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form action="{{ route('admin.package.store') }}" method="post" enctype="multipart/form-data">
					@csrf
					<div class="modal-body">
						<div class="form-group row">
							<label class="col-sm-3">@lang('Name')</label>
							<div class="col-sm-9">
								<input type="text" name="name" class="form-control" required>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Price')</label>
							<div class="col-sm-9">
								<input type="text" name="price" class="form-control" required>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Old Price')</label>
							<div class="col-sm-9">
								<input type="text" name="old_price" placeholder="@lang('Optional — shown struck-through')" class="form-control">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Validity')</label>
							<div class="col-sm-9">
								<input type="text" name="validity" class="form-control" required>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Features Included')</label>
							<div class="col-sm-9">
								<textarea name="features" rows="6" class="form-control" placeholder="One feature per line, e.g.&#10;Guaranteed Leads&#10;Online Catalogue&#10;Verified Seal"></textarea>
								<small class="text-muted">@lang('One feature per line — shown as the green-tick checklist on the plan card.')</small>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Ribbon Tag')</label>
							<div class="col-sm-9">
								<input type="text" name="tag" placeholder="@lang('Optional, e.g. SAVE 25% / Most Popular')" class="form-control">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Highlight as Popular')</label>
							<div class="col-sm-9">
								<div class="custom-control custom-switch">
									<input type="checkbox" class="custom-control-input" id="addPopular" name="is_popular" value="1">
									<label class="custom-control-label" for="addPopular">@lang('Show this plan as the highlighted / recommended one')</label>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
						<button type="submit" class="btn btn-primary">@lang('Submit')</button>
					</div>
				</form>
			</div>
		</div>
	</div>


<!--Edit Modal -->
	<div class="modal fade" id="packageEditModal" tabindex="-1" role="dialog" aria-labelledby="packageModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="packageModalLabel">@lang('Edit Package')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form action="" method="post" enctype="multipart/form-data">
					@csrf
					<div class="modal-body">
						<div class="form-group row">
							<label class="col-sm-3">@lang('Name')</label>
							<div class="col-sm-9">
								<input type="text" name="name" class="form-control" required>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Price')</label>
							<div class="col-sm-9">
								<input type="text" name="price" class="form-control" required>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Old Price')</label>
							<div class="col-sm-9">
								<input type="text" name="old_price" placeholder="@lang('Optional — shown struck-through')" class="form-control">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Validity')</label>
							<div class="col-sm-9">
								<input type="text" name="validity" class="form-control" required>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Features Included')</label>
							<div class="col-sm-9">
								<textarea name="features" rows="6" class="form-control" placeholder="One feature per line"></textarea>
								<small class="text-muted">@lang('One feature per line — shown as the green-tick checklist on the plan card.')</small>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Ribbon Tag')</label>
							<div class="col-sm-9">
								<input type="text" name="tag" placeholder="@lang('Optional, e.g. SAVE 25% / Most Popular')" class="form-control">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Highlight as Popular')</label>
							<div class="col-sm-9">
								<div class="custom-control custom-switch">
									<input type="checkbox" class="custom-control-input" id="editPopular" name="is_popular" value="1">
									<label class="custom-control-label" for="editPopular">@lang('Show this plan as the highlighted / recommended one')</label>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
						<button type="submit" class="btn btn-primary">@lang('Submit')</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
@push('top-area')
<button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#packageModal">@lang('Add New Package')</button>
@endpush

@push('css')
<style>
.cert-view{display:flex;gap:28px;align-items:flex-start;flex-wrap:wrap}
.cert-view__img{flex:0 0 300px;max-width:100%}
.cert-view__img img{width:100%;border-radius:6px;cursor:zoom-in}
.cert-view__body{flex:1;min-width:260px}
.cert-view__body h3{font-weight:700;font-size:22px;margin-bottom:14px;color:#111}
.cert-view__body h6{font-weight:700;font-size:15px;margin:14px 0 10px;color:#222}
#cvBenefits{list-style:none;padding:0;margin:0}
#cvBenefits li{position:relative;padding-left:26px;margin-bottom:10px;font-size:14px;color:#333}
#cvBenefits li:before{content:"\2713";position:absolute;left:4px;top:0;color:#1a9e5a;font-weight:700}
.cert-dots{display:flex;justify-content:center;gap:8px;margin-top:12px}
.cert-dots span{width:10px;height:10px;border-radius:50%;background:#d6d6d6;cursor:pointer}
.cert-dots span.active{background:#0076d7}
.cert-loc{display:flex;gap:14px;margin:14px 0 4px;flex-wrap:wrap}
.cert-loc>div{min-width:190px}
.cert-loc label{font-size:12px;color:#666;margin-bottom:4px;display:block}
.cert-chips{display:flex;gap:10px;flex-wrap:wrap;margin:6px 0 4px}
.cert-chip{display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border-radius:8px;background:linear-gradient(90deg,#e8f3fd,#f4f9fe);color:#1c6fb8;font-weight:600;font-size:14px}
.cert-chip i{color:inherit}
.cert-chip--green{background:linear-gradient(90deg,#e3f6e8,#f1fbf3);color:#2e9d4a}
.cert-chip--gold{background:linear-gradient(90deg,#fff5d6,#fffaeb);color:#b07d00}
.cert-plans{display:flex;gap:12px;flex-wrap:wrap}
.cert-plan{position:relative;width:130px;padding:14px 12px;border:1px solid #e6e6e6;background:#f7f7f7;border-radius:8px;cursor:pointer;transition:.15s}
.cert-plan .cp-label{font-size:13px;color:#666;margin-bottom:8px}
.cert-plan .cp-price{font-size:20px;font-weight:700;color:#111}
.cert-plan .cp-price small{font-size:14px;font-weight:600;margin-right:2px}
.cert-plan.active{border-color:#0076d7;background:#eef6fd}
.cert-plan.active .cp-label{color:#0076d7}
.cert-plan.active:after{content:"\2713";position:absolute;top:-10px;right:-8px;width:22px;height:22px;border-radius:50%;background:#fff;border:1px solid #0076d7;color:#0076d7;font-size:12px;line-height:20px;text-align:center}
.cert-view__actions{display:flex;align-items:center;gap:20px;margin-top:22px;flex-wrap:wrap}
.cert-btn-outline{border:1px solid #0076d7;color:#0076d7;background:#fff;min-width:180px}
.cert-total{font-size:16px;color:#333}
.cert-total strong{color:#0076d7;font-size:20px}
.cert-plan-row{display:flex;gap:8px;margin-bottom:8px;align-items:center}
.cert-plan-row .input-group{flex:1}
@media (max-width:576px){.cert-view__img{flex-basis:100%}.cert-plan{width:calc(33% - 8px);padding:10px 8px}}
</style>
@endpush

@push('js')
<script>
	(function($){
		"use strict";

		$('.ediBtn').on('click',function(){
			var modal = $('#packageEditModal');
			modal.find('input[name=name]').val($(this).data('name'));
			modal.find('input[name=price]').val($(this).data('price'));
			modal.find('input[name=old_price]').val($(this).data('old_price'));
			modal.find('input[name=validity]').val($(this).data('validity'));
			modal.find('textarea[name=features]').val($(this).data('features'));
			modal.find('input[name=tag]').val($(this).data('tag'));
			modal.find('input[name=is_popular]').prop('checked', $(this).data('is_popular') == 1);
			modal.find('form').attr('action',$(this).data('action'));
		});


	})(jQuery);



$(document).on('click', '.deleteBtn', function(e){
    e.preventDefault();

    var id = $(this).data('id');
    var form = $('#delete-form-' + id);

    Swal.fire({
        title: 'Are you sure?',
        text: 'Ye package permanently delete ho jayega!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then(function(result){
        if (result.isConfirmed) {
            form.submit();
        }
    });
});

</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function($){
	"use strict";
	function planRow(years, price){
		var r = $('<div class="cert-plan-row">'+
			'<div class="input-group"><input type="text" maxlength="30" name="plan_label[]" class="form-control" placeholder="Plan label, e.g. EMI / 1 Year"></div>'+
			'<div class="input-group"><div class="input-group-prepend"><span class="input-group-text">&#8377;</span></div><input type="number" min="0" step="0.01" name="plan_price[]" class="form-control" placeholder="Price"></div>'+
			'<button type="button" class="btn btn-sm btn-outline-danger certDelPlan"><i class="fas fa-times"></i></button></div>');
		r.find('[name="plan_label[]"]').val(years);
		r.find('[name="plan_price[]"]').val(price);
		$('#certPlanRows').append(r);
	}
	$('#certAddPlan').on('click', function(){
		planRow('', '');
	});
	$(document).on('click', '.certDelPlan', function(){
		if ($('#certPlanRows .cert-plan-row').length > 1) $(this).closest('.cert-plan-row').remove();
	});

	$('.certAddBtn').on('click', function(){
		var f = $('#certForm');
		f[0].reset();
		f.attr('action', f.data('store'));
		$('#certFormTitle').text('Add Package');
		f.find('input[name=name]').val('Service Bazzar Certificate');
		$('#certImagePreview').hide().attr('src','');
		$('#certGalleryCurrent').hide();
		$('#certPlanRows').empty();
		planRow('1 Year', ''); planRow('2 Years', ''); planRow('3 Years', '');
		$('#certPricingType').val('plan'); f.find('input[name=positions]').val(3);
		$('#certNeedsAd').prop('checked', false); syncPricingType();
	});
	$('.certEditBtn').on('click', function(){
		var d = $(this).data('cert'), f = $('#certForm');
		f[0].reset();
		f.attr('action', $(this).data('action'));
		$('#certFormTitle').text('Edit Package');
		f.find('input[name=name]').val(d.name);
		f.find('textarea[name=benefits]').val($(this).data('benefits'));
		f.find('textarea[name=highlights]').val($(this).data('highlights'));
		if (d.image) $('#certImagePreview').attr('src', d.image).show(); else $('#certImagePreview').hide();
		var extra = (d.images || []).slice(1), th = $('#certGalleryThumbs').empty();
		extra.forEach(function(u){ th.append($('<img>').attr('src', u).css({width:90,height:60,objectFit:'cover',border:'1px solid #e3e6f0',borderRadius:4})); });
		$('#certGalleryCurrent').toggle(extra.length > 0);
		$('#certPlanRows').empty();
		(d.plans && d.plans.length ? d.plans : [{label:'',price:''}]).forEach(function(p){ planRow(p.label, p.price); });
		$('#certPricingType').val($(this).data('pricing') || 'plan');
		f.find('input[name=positions]').val($(this).data('positions') || 3);
		$('#certNeedsAd').prop('checked', $(this).data('needs_ad') == 1);
		syncPricingType();
	});
	function syncPricingType(){
		var mode = $('#certPricingType').val();
		$('#certPositionsRow').toggle(mode === 'position');
		// 'location' still needs the plan list — the plans are the tiers priced per city.
		$('#certPlansRow').toggle(mode !== 'position');
	}
	$('#certPricingType').on('change', syncPricingType);

	$('#certImageInput').on('change', function(){
		if (this.files && this.files[0]) $('#certImagePreview').attr('src', URL.createObjectURL(this.files[0])).show();
	});

	function inr(v){ return '\u20B9' + Number(v).toLocaleString('en-IN'); }
	function selectPlan(el){
		$('#cvPlans .cert-plan').removeClass('active');
		$(el).addClass('active');
		$('#cvTotal').text(inr($(el).data('price')));
	}
	$(document).on('click', '.certViewBtn', function(e){
		e.preventDefault();
		var d = $(this).data('cert');
		$('#cvName').text(d.name);
		var imgs = d.images && d.images.length ? d.images : (d.image ? [d.image] : []);
		$('#cvImg').attr('src', imgs[0] || '').toggle(imgs.length > 0);
		var dots = $('#cvDots').empty().toggle(imgs.length > 1);
		imgs.forEach(function(u, i){
			dots.append($('<span>').toggleClass('active', i === 0).on('click', function(){
				$('#cvImg').attr('src', u);
				dots.children().removeClass('active'); $(this).addClass('active');
			}));
		});
		var ul = $('#cvBenefits').empty();
		(d.benefits || []).forEach(function(b){ ul.append($('<li>').text(b)); });
		var hl = $('#cvHighlights').empty().toggle(!!(d.highlights && d.highlights.length));
		(d.highlights || []).forEach(function(h){
			var k = h.toLowerCase(), cls = '', ic = 'fa-check';
			if (/purchased|customers/.test(k)) { cls = ' cert-chip--green'; ic = 'fa-shopping-cart'; }
			else if (/trust/.test(k)) { cls = ' cert-chip--gold'; ic = 'fa-shield-alt'; }
			hl.append($('<span class="cert-chip' + cls + '">').html('<i class="fas ' + ic + '"></i> ').append(document.createTextNode(h)));
		});
		var wrap = $('#cvPlans').empty();

		if (d.needsLoc) {
			renderPositions(d);
		} else {
			$('#cvLocation').hide(); $('#cvNoPrice').hide(); $('#cvPlanHead').text('Select Plan');
			(d.plans || []).forEach(function(p){
				var c = $('<div class="cert-plan">').attr('data-price', p.price);
				c.append($('<div class="cp-label">').text(p.label));
				c.append($('<div class="cp-price">').html('<small>&#8377;</small>' + Number(p.price).toLocaleString('en-IN')));
				wrap.append(c);
			});
			var first = wrap.find('.cert-plan').first();
			if (first.length) selectPlan(first); else $('#cvTotal').text('');
		}
		$('#certViewModal').modal('show');
	});
	$(document).on('click', '#cvPlans .cert-plan', function(){ selectPlan(this); });

	function renderPositions(d){
		var tree = d.tree || {}, cities = Object.keys(tree).sort();
		$('#cvPlanHead').text('Select Position');
		$('#cvLocation').toggle(cities.length > 0);
		var citySel = $('#cvCity').empty();
		cities.forEach(function(ct){ citySel.append($('<option>').val(ct).text(ct)); });
		if (!cities.length) { $('#cvPlans').empty(); $('#cvArea').empty(); $('#cvTotal').text(''); $('#cvNoPrice').show(); return; }
		$('#cvNoPrice').hide();
		fillAreas();

		function fillAreas(){
			var areas = Object.keys(tree[citySel.val()] || {}).sort(function(a, b){ return a === '' ? -1 : b === '' ? 1 : a.localeCompare(b); });
			var areaSel = $('#cvArea').empty();
			areas.forEach(function(a){ areaSel.append($('<option>').val(a).text(a === '' ? 'Whole city' : a)); });
			drawCards();
		}
		function drawCards(){
			var prices = ((tree[$('#cvCity').val()] || {})[$('#cvArea').val()]) || {};
			var w = $('#cvPlans').empty();
			Object.keys(prices).sort(function(a, b){ return a - b; }).forEach(function(pos){
				var c = $('<div class="cert-plan">').attr('data-price', prices[pos]);
				c.append($('<div class="cp-label">').text('Top ' + pos));
				c.append($('<div class="cp-price">').html('<small>&#8377;</small>' + Number(prices[pos]).toLocaleString('en-IN')));
				w.append(c);
			});
			var f = w.find('.cert-plan').first();
			$('#cvNoPrice').toggle(f.length === 0);
			if (f.length) selectPlan(f); else $('#cvTotal').text('');
		}
		$('#cvCity').off('change.pos').on('change.pos', fillAreas);
		$('#cvArea').off('change.pos').on('change.pos', drawCards);
	}
	$(document).on('click', '#cvImg', function(){ if (this.src) window.open(this.src, '_blank'); });

	$(document).on('click', '.certDelBtn', function(){
		var form = $('#cert-delete-' + $(this).data('id'));
		Swal.fire({
			title: 'Delete this package?',
			text: 'Ye package permanently delete ho jayega!',
			icon: 'warning', showCancelButton: true,
			confirmButtonColor: '#d33', cancelButtonColor: '#3085d6',
			confirmButtonText: 'Yes, delete it!'
		}).then(function(r){ if (r.isConfirmed) form.submit(); });
	});
})(jQuery);
</script>
@endpush
