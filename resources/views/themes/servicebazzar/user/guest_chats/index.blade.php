@extends($themeView.'layouts.app')
@section('content')
@section('title','Guest Chats')
<section class="dashboard pt-80 pb-80">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('Listing')</th>
                                        <th scope="col">@lang('Guest')</th>
                                        <th scope="col">@lang('Status')</th>
                                        <th scope="col">@lang('Last Message')</th>
                                        <th scope="col">@lang('Action')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($conversations as $conversation)
                                    <tr>
                                        <td>
                                            <a href="{{ route('listings.details', $conversation->listing->slug) }}" target="_blank">
                                                {{ __($conversation->listing->name) }}
                                            </a>
                                        </td>
                                        <td>
                                            <div>{{ __($conversation->guestUser->name) }}</div>
                                            <small class="text-muted">{{ __($conversation->guestUser->email) }}</small>
                                        </td>
                                        <td>
                                            @if($conversation->status === 'open')
                                                <span class="badge badge-success">@lang('Open')</span>
                                            @else
                                                <span class="badge badge-secondary">@lang('Closed')</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ __($conversation->last_message_at?->diffForHumans() ?? $conversation->created_at->diffForHumans()) }}
                                        </td>
                                        <td>
                                            <a href="{{ route('user.guest-chats.show', $conversation->id) }}" class="btn btn-primary btn-sm">
                                                @lang('Open')
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td class="text-center" colspan="100%">@lang('No guest chats found')</td>
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
    </div>
</section>
@endsection
