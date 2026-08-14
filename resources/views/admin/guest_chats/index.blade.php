@extends('admin.layouts.master')

@section('content')

<div class="body-wrapper">
    <div class="bodywrapper__inner">

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Guest Chats</h5>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table--light style--two mb-0">
                        <thead>
                            <tr>
                                <th>Listing</th>
                                <th>Guest</th>
                                <th>Status</th>
                                <th>Unread</th>
                                <th>Last Message</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                        @forelse($conversations as $conversation)
                            <tr>
                                <td>
                                    @if($conversation->listing)
                                        <a href="{{ route('listings.details', [
                                            'category' => $conversation->listing->category->slug ?? 'listing',
                                            'slug' => $conversation->listing->slug
                                        ]) }}" target="_blank">
                                            {{ $conversation->listing->name }}
                                        </a>
                                    @else
                                        <span class="badge badge-info">Admin Chat</span>
                                    @endif
                                </td>

                                <td>
                                    <strong>{{ $conversation->guestUser->name ?? '-' }}</strong><br>
                                    <small>{{ $conversation->guestUser->email ?? '-' }}</small>
                                </td>

                                <td>
                                    @if($conversation->status == 'open')
                                        <span class="badge badge-success">Open</span>
                                    @else
                                        <span class="badge badge-danger">Closed</span>
                                    @endif
                                </td>

                                <td>
                                    @if($conversation->unread_count > 0)
                                        <span class="badge badge-danger">{{ $conversation->unread_count }}</span>
                                    @else
                                        <span class="badge badge-secondary">0</span>
                                    @endif
                                </td>

                                <td>
                                    {{ optional($conversation->last_message_at)->diffForHumans() ?? $conversation->created_at->diffForHumans() }}
                                </td>

                                <td>
                                    <a href="{{ route('admin.guest.chats.show', $conversation->id) }}" class="btn btn-sm btn-primary">
                                        <i class="las la-comments"></i> Open
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%" class="text-center">No guest chats found</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer">
                {{ $conversations->links() }}
            </div>
        </div>

    </div>
</div>

@endsection