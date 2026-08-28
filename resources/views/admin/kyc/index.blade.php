@extends('admin.layouts.master')
@section('content')
@section('title','KYC Verifications')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">@lang('KYC Verifications')
                    @if($pendingCount)
                        <span class="badge badge-warning">{{ $pendingCount }} @lang('pending')</span>
                    @endif
                </h5>
                <div class="btn-group btn-group-sm" role="group">
                    @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label)
                        <a href="{{ route('admin.kyc.index', ['status' => $key]) }}"
                           class="btn {{ $filter === $key ? 'btn-primary' : 'btn-outline-primary' }}">@lang($label)</a>
                    @endforeach
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>@lang('User')</th>
                                <th>@lang('Document')</th>
                                <th>@lang('Number')</th>
                                <th>@lang('Images')</th>
                                <th>@lang('Submitted')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submissions as $kyc)
                                <tr>
                                    <td>{{ $kyc->id }}</td>
                                    <td>
                                        {{ optional($kyc->user)->fullname() ?: optional($kyc->user)->username }}<br>
                                        <small class="text-muted">{{ optional($kyc->user)->mobile }}</small>
                                    </td>
                                    <td>{{ strtoupper(str_replace('_', ' ', $kyc->document_type)) }}</td>
                                    <td>{{ $kyc->document_number ?: '-' }}</td>
                                    <td>
                                        @if($kyc->front_image)
                                            <a href="{{ asset($kyc->front_image) }}" target="_blank">@lang('Front')</a>
                                        @endif
                                        @if($kyc->back_image)
                                            &nbsp;|&nbsp;<a href="{{ asset($kyc->back_image) }}" target="_blank">@lang('Back')</a>
                                        @endif
                                    </td>
                                    <td>{{ $kyc->created_at->format('d M Y, h:i A') }}</td>
                                    <td>
                                        @if($kyc->status == 0)
                                            <span class="badge badge-warning">@lang('Pending')</span>
                                        @elseif($kyc->status == 1)
                                            <span class="badge badge-success">@lang('Approved')</span>
                                        @else
                                            <span class="badge badge-danger">@lang('Rejected')</span>
                                            @if($kyc->admin_note)
                                                <br><small class="text-muted">{{ $kyc->admin_note }}</small>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($kyc->status == 0)
                                            <form action="{{ route('admin.kyc.approve', $kyc->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">@lang('Approve')</button>
                                            </form>
                                            <details class="d-inline-block mt-1">
                                                <summary class="btn btn-sm btn-danger">@lang('Reject')</summary>
                                                <form action="{{ route('admin.kyc.reject', $kyc->id) }}" method="POST" class="mt-2">
                                                    @csrf
                                                    <textarea name="admin_note" class="form-control form-control-sm mb-1"
                                                              rows="2" placeholder="@lang('Reason for rejection')" required></textarea>
                                                    <button type="submit" class="btn btn-sm btn-danger">@lang('Confirm Reject')</button>
                                                </form>
                                            </details>
                                        @else
                                            <span class="text-muted">@lang('Reviewed') {{ optional($kyc->reviewed_at)->format('d M Y') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">@lang('No KYC submissions found.')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $submissions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
