@extends('admin.layouts.master')
@section('title', 'Sales Discounts')
@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body p-0">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Sales Discounts</h5>
                        <small class="text-muted">The sales team picks from the ones that are on, in the cart.</small>
                    </div>
                    <a href="{{ route('admin.sales_discounts.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Discount
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Discount</th>
                                <th>Applies to</th>
                                <th>Valid</th>
                                <th>Status</th>
                                <th>Used</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($discounts as $d)
                                @php
                                    $state = !$d->is_active ? ['Off', 'secondary']
                                        : ($d->starts_at && $d->starts_at->isFuture() ? ['Scheduled', 'info']
                                        : ($d->ends_at && $d->ends_at->isPast() ? ['Expired', 'warning'] : ['Live', 'success']));
                                @endphp
                                <tr>
                                    <td><strong>{{ $d->name }}</strong></td>
                                    <td>{{ $d->label() }}</td>
                                    <td>
                                        @if($d->coversAllPackages())
                                            All packages
                                        @else
                                            {{ collect($d->package_ids)->map(fn ($id) => $packages[$id] ?? '#'.$id)->implode(', ') }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($d->starts_at || $d->ends_at)
                                            {{ $d->starts_at ? $d->starts_at->format('d M Y') : 'Any time' }} – {{ $d->ends_at ? $d->ends_at->format('d M Y') : 'No end' }}
                                        @else
                                            Always
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.sales_discounts.toggle', $d->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="badge badge-{{ $state[1] }}" style="border:none;cursor:pointer;" title="Click to switch {{ $d->is_active ? 'off' : 'on' }}">{{ $state[0] }}</button>
                                        </form>
                                    </td>
                                    <td>{{ $used[$d->id] ?? 0 }} order{{ ($used[$d->id] ?? 0) == 1 ? '' : 's' }}</td>
                                    <td>
                                        <a href="{{ route('admin.sales_discounts.edit', $d->id) }}" class="btn btn-sm btn-primary"><i class="fas fa-pen"></i></a>
                                        <form action="{{ route('admin.sales_discounts.destroy', $d->id) }}" method="POST" style="display:inline;"
                                              onsubmit="return confirm('Delete this discount? Past orders keep their own record of it.');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center py-4">No discounts yet. Add one and it appears in the sales team's cart.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
