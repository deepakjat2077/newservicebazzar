@extends('admin.layouts.master')
@section('title', $package->name . ' — Prices')
@section('content')

@php($medal = ['#d4a017', '#9aa3ab', '#b3734a'])

<div class="pos-head">
	<div class="pos-head__left">
		@if($package->imageUrl())<img src="{{ $package->imageUrl() }}" alt="">@endif
		<div>
			<h5>{{ $package->name }}</h5>
			<span>@lang('Price by City / Area') — {{ implode(' · ', $tiers) }}</span>
		</div>
	</div>
	<a href="{{ route('admin.package.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i> @lang('Back to Packages')</a>
</div>

<div class="row">
	<div class="col-lg-4 mb-4">
		<div class="card pos-card h-100">
			<div class="card-body">
				<h6 class="pos-title"><i class="fas fa-plus-circle"></i> @lang('Set Prices')</h6>

				<form action="{{ route('admin.package.certificate.positions.store', $package->id) }}" method="post">
					@csrf
					<div class="form-group">
						<label>@lang('City')</label>
						<select name="city" id="posCity" class="form-control" required>
							<option value="">@lang('Select city')</option>
							@foreach($cities as $city)
								<option value="{{ $city }}">{{ $city }}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label>@lang('Area')</label>
						<select name="area" id="posArea" class="form-control" disabled>
							<option value="">@lang('Whole city')</option>
						</select>
						<small class="text-muted" id="posAreaHelp">@lang('Select a city first.')</small>
					</div>

					<label class="mt-2 d-block">@lang('Price per plan')</label>
					@foreach($tiers as $i => $tier)
					<div class="pos-price-row">
						<span class="pos-badge" style="background:{{ $medal[$i] ?? '#6c757d' }}">{{ $i + 1 }}</span>
						<span class="pos-price-row__label">{{ $tier }}</span>
						<div class="input-group input-group-sm">
							<div class="input-group-prepend"><span class="input-group-text">&#8377;</span></div>
							<input type="number" min="0" step="0.01" name="price[{{ $i }}]" class="form-control" placeholder="@lang('Price')">
						</div>
						<div class="input-group input-group-sm" style="max-width:110px;">
							<input type="number" min="1" max="3650" name="days[{{ $i }}]" class="form-control" placeholder="@lang('Days')">
							<div class="input-group-append"><span class="input-group-text">d</span></div>
						</div>
					</div>
					@endforeach
					<small class="text-muted d-block">@lang('Days is optional — left blank it is read from the plan name ("2 Days" = 2, "1 Year" = 365).')</small>
					@if(!$package->isPositionPriced())
						<small class="text-muted d-block mt-1"><i class="fas fa-info-circle"></i> @lang('Only add a city/area here when its price is different. Everywhere else the package\'s own plan price is used:')
							{{ collect($package->planList())->map(fn ($p) => $p['label'].' ₹'.numberFormat($p['price']))->implode(' · ') }}
						</small>
					@endif

					<button type="submit" class="btn btn-primary btn-block mt-3"><i class="fas fa-save"></i> @lang('Save Prices')</button>
					<small class="text-muted d-block mt-2">@lang('Same city/area saved again updates its prices. An empty box removes that position.')</small>
				</form>
			</div>
		</div>
	</div>

	<div class="col-lg-8">
		<div class="card pos-card">
			<div class="card-body p-0">
				<div class="pos-table-head">
					<h6 class="pos-title mb-0"><i class="fas fa-list-ul"></i> @lang('Saved Prices')</h6>
					<form method="get" class="form-inline">
						<select name="city" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
							<option value="">@lang('All cities')</option>
							@foreach($cities as $city)
								<option value="{{ $city }}" @selected(request('city') == $city)>{{ $city }}</option>
							@endforeach
						</select>
						@if(request('city'))
							<a href="{{ route('admin.package.certificate.positions', $package->id) }}" class="btn btn-sm btn-link">@lang('Clear')</a>
						@endif
					</form>
				</div>

				<div class="table-responsive">
					<table class="table pos-table mb-0">
						<thead>
							<tr>
								<th>@lang('City')</th>
								<th>@lang('Area')</th>
								@foreach($tiers as $i => $tier)
									<th><span class="pos-badge pos-badge--sm" style="background:{{ $medal[$i] ?? '#6c757d' }}">{{ $i + 1 }}</span> {{ $tier }}</th>
								@endforeach
								<th class="text-right">@lang('Action')</th>
							</tr>
						</thead>
						<tbody>
							@forelse($rows as $key => $group)
							@php([$city, $area] = explode('|', $key, 2))
							<tr>
								<td class="font-weight-bold">{{ $city }}</td>
								<td>{!! $area ? e($area) : '<span class="pos-chip">Whole city</span>' !!}</td>
								@foreach($tiers as $tier)
									@php($row = $group->firstWhere('tier', $tier))
									<td class="{{ $row ? 'pos-amount' : 'text-muted' }}">
										{{ $row ? '₹' . numberFormat($row->price) : '—' }}
										@if($row && $row->days)<br><small class="text-muted">{{ $row->days }} days</small>@endif
									</td>
								@endforeach
								<td class="text-right" style="white-space:nowrap;">
									<button type="button" class="btn btn-sm btn-light posEditBtn" title="@lang('Edit')"
										data-city="{{ $city }}" data-area="{{ $area }}"
										data-prices='@json($group->pluck("price", "tier"))'
										data-days='@json($group->pluck("days", "tier"))'><i class="fas fa-pen"></i></button>
									<button type="button" class="btn btn-sm btn-light text-danger posDelBtn" title="@lang('Delete')" data-form="posDel{{ $loop->index }}"><i class="fas fa-trash"></i></button>
									<form id="posDel{{ $loop->index }}" action="{{ route('admin.package.certificate.positions.delete', $package->id) }}" method="post" style="display:none;">
										@csrf @method('DELETE')
										<input type="hidden" name="city" value="{{ $city }}">
										<input type="hidden" name="area" value="{{ $area }}">
									</form>
								</td>
							</tr>
							@empty
							<tr><td colspan="100%" class="text-center text-muted py-4">@lang('No prices yet — add the first city on the left.')</td></tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

@push('css')
<style>
.pos-head{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:18px;flex-wrap:wrap}
.pos-head__left{display:flex;align-items:center;gap:14px}
.pos-head__left img{width:60px;height:60px;object-fit:cover;border-radius:8px;border:1px solid #e3e6f0;background:#fff}
.pos-head h5{margin:0;font-weight:700}
.pos-head span{font-size:13px;color:#7b7b7b}
.pos-card{border:1px solid #ebedf3;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.04)}
.pos-title{font-weight:700;font-size:15px;color:#2b2b2b}
.pos-title i{color:#0076d7;margin-right:6px}
.pos-price-row{display:flex;align-items:center;gap:10px;margin-bottom:10px}
.pos-price-row__label{font-size:13px;color:#555;width:52px}
.pos-price-row .input-group{flex:1}
.pos-badge{display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;color:#fff;font-weight:700;font-size:13px}
.pos-badge--sm{width:20px;height:20px;font-size:11px}
.pos-table-head{display:flex;justify-content:space-between;align-items:center;padding:14px 18px;border-bottom:1px solid #eef0f4;flex-wrap:wrap;gap:10px}
.pos-table thead th{font-size:12px;text-transform:uppercase;letter-spacing:.3px;color:#6b7280;border-top:0;background:#fafbfc}
.pos-table td{vertical-align:middle;font-size:14px}
.pos-amount{font-weight:700;color:#1a7f3c}
.pos-chip{display:inline-block;padding:3px 10px;border-radius:20px;background:#eef4fb;color:#1c6fb8;font-size:12px;font-weight:600}
.pos-table tbody tr:hover{background:#fbfcfe}
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function($){
	"use strict";
	var areaUrl = '{{ route('admin.package.certificate.areas') }}';

	function loadAreas(city, selected){
		var sel = $('#posArea');
		if (!city) {
			sel.prop('disabled', true).html('<option value="">Whole city</option>');
			$('#posAreaHelp').text('Select a city first.');
			return;
		}
		sel.prop('disabled', true).html('<option value="">Loading…</option>');
		$('#posAreaHelp').text('Loading areas…');
		$.get(areaUrl, {city: city}, function(list){
			sel.empty().append($('<option>').val('').text('Whole city — one price for all of ' + city));
			(list || []).forEach(function(a){ sel.append($('<option>').val(a).text(a)); });
			sel.prop('disabled', false).val(selected || '');
			$('#posAreaHelp').text((list || []).length + ' areas found in ' + city + '.');
		}).fail(function(){
			sel.empty().append($('<option>').val('').text('Whole city')).prop('disabled', false);
			$('#posAreaHelp').text('Could not load areas.');
		});
	}

	$('#posCity').on('change', function(){ loadAreas($(this).val(), ''); });

	var tiers = @json($tiers);
	$('.posEditBtn').on('click', function(){
		var prices = $(this).data('prices') || {}, days = $(this).data('days') || {};
		var city = $(this).data('city'), area = String($(this).data('area') || '');
		$('#posCity').val(city);
		loadAreas(city, area);
		$('[name^="price["], [name^="days["]').val('');
		tiers.forEach(function(tier, i){
			if (prices[tier] != null) $('[name="price[' + i + ']"]').val(parseFloat(prices[tier]));
			if (days[tier] != null) $('[name="days[' + i + ']"]').val(days[tier]);
		});
		$('html, body').animate({scrollTop: 0}, 200);
	});

	$('.posDelBtn').on('click', function(){
		var form = $('#' + $(this).data('form'));
		Swal.fire({
			title: 'Remove these prices?',
			text: 'Is city/area ke saare position prices delete ho jayenge.',
			icon: 'warning', showCancelButton: true,
			confirmButtonColor: '#d33', cancelButtonColor: '#3085d6',
			confirmButtonText: 'Yes, remove'
		}).then(function(r){ if (r.isConfirmed) form.submit(); });
	});
})(jQuery);
</script>
@endpush

@endsection
