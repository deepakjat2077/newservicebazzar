@extends('admin.layouts.master')
@section('content')
@section('title','Callback Requests')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">@lang('Callback Requests')
                    @if($pendingCount)
                        <span class="badge badge-warning">{{ $pendingCount }} @lang('pending')</span>
                    @endif
                </h5>
                <div class="btn-group btn-group-sm" role="group">
                    @foreach(['pending' => 'Pending', 'handled' => 'Handled', 'all' => 'All'] as $key => $label)
                        <a href="{{ route('admin.callback.index', ['status' => $key]) }}"
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
                                <th>@lang('Name')</th>
                                <th>@lang('Phone')</th>
                                <th>@lang('Topic')</th>
                                <th>@lang('Message')</th>
                                <th>@lang('Requested')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $req)
                                <tr>
                                    <td>{{ $req->id }}</td>
                                    <td>{{ $req->name ?: (optional($req->user)->username ?: '-') }}</td>
                                    <td><a href="tel:{{ $req->phone }}">{{ $req->phone }}</a></td>
                                    <td>{{ $req->topic ?: '-' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($req->message, 60) ?: '-' }}</td>
                                    <td>{{ $req->created_at->format('d M Y, h:i A') }}</td>
                                    <td>
                                        @if($req->status == 0)
                                            <span class="badge badge-warning">@lang('Pending')</span>
                                        @else
                                            <span class="badge badge-success">@lang('Handled')</span>
                                            @if($req->admin_note)
                                                <br><small class="text-muted">{{ $req->admin_note }}</small>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($req->status == 0)
                                            <details>
                                                <summary class="btn btn-sm btn-success">@lang('Mark Handled')</summary>
                                                <form action="{{ route('admin.callback.handled', $req->id) }}" method="POST" class="mt-2">
                                                    @csrf
                                                    <textarea name="admin_note" class="form-control form-control-sm mb-1"
                                                              rows="2" placeholder="@lang('Note (optional)')"></textarea>
                                                    <button type="submit" class="btn btn-sm btn-success">@lang('Confirm')</button>
                                                </form>
                                            </details>
                                        @else
                                            <span class="text-muted">{{ optional($req->handled_at)->format('d M Y') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">@lang('No callback requests found.')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $requests->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
