@extends('admin.layouts.master')
@section('title', 'Plan Subscriptions')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">Plan Subscriptions</h5>

                    <div class="d-flex flex-wrap" style="gap:8px;">
                        <a href="{{ route('admin.plans.index', ['status' => 'active']) }}"
                           class="btn btn-sm {{ $status === 'active' ? 'btn-success' : 'btn-outline-success' }}">
                            Active ({{ $totalActive }})
                        </a>
                        <a href="{{ route('admin.plans.index', ['status' => 'expired']) }}"
                           class="btn btn-sm {{ $status === 'expired' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                            Expired ({{ $totalExpired }})
                        </a>
                        <a href="{{ route('admin.plans.index', ['status' => 'all']) }}"
                           class="btn btn-sm {{ $status === 'all' ? 'btn-dark' : 'btn-outline-dark' }}">
                            All ({{ $totalAll }})
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Listing</th>
                                <th>Owner</th>
                                <th>Plan</th>
                                <th>City / Area</th>
                                <th>Price</th>
                                <th>Starts</th>
                                <th>Expires</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plans as $plan)
                            @php
                                $isActive = $plan->expires_at && $plan->expires_at->isFuture();
                                $user     = optional($plan->listing)->user;
                                $daysLeft = $isActive ? now()->diffInDays($plan->expires_at, false) : null;
                            @endphp
                            <tr>
                                <td>{{ $plan->id }}</td>

                                <td>
                                    @if($plan->listing)
                                        <a href="{{ route('admin.listing.details', $plan->listing_id) }}" target="_blank">
                                            {{ $plan->listing->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">Listing #{{ $plan->listing_id }}</span>
                                    @endif
                                </td>

                                <td>
                                    @if($user)
                                        <div>{{ $user->first_name }} {{ $user->last_name }}</div>
                                        <small class="text-muted">{{ $user->mobile }}</small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge bg-primary text-white" style="white-space:normal;max-width:180px;">
                                        {{ $plan->plan }}
                                    </span>
                                </td>

                                <td>
                                    {{ $plan->city ?: '—' }}
                                    @if($plan->area)
                                        <br><small class="text-muted">{{ $plan->area }}</small>
                                    @endif
                                </td>

                                <td>
                                    @if($plan->price > 0)
                                        ₹{{ number_format($plan->price, 0) }}
                                    @else
                                        <span class="text-muted">Free</span>
                                    @endif
                                </td>

                                <td>
                                    <small>{{ $plan->starts_at ? $plan->starts_at->format('d M Y') : '—' }}</small>
                                </td>

                                <td>
                                    @if($plan->expires_at)
                                        <small>{{ $plan->expires_at->format('d M Y') }}</small>
                                        @if($isActive && $daysLeft <= 7)
                                            <br><small class="text-danger fw-bold">{{ $daysLeft }}d left</small>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td>
                                    @if($isActive)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Expired</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No plans found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($plans->hasPages())
            <div class="card-footer">
                {{ $plans->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
