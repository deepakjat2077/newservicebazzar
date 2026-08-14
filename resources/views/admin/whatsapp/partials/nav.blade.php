{{-- SweetAlert2 is already loaded for every admin page by includes/topbar,
     so nothing in this panel needs its own copy — and nothing in it may fall
     back to a native browser confirm()/alert(). --}}
@if(!$configured)
    <div class="alert alert-warning">
        <strong>@lang('WhatsApp is not set up yet.')</strong>
        @lang('Fill in your API key, app name and source number before sending anything.')
        <a href="{{ route('admin.setting.gupshup') }}" class="btn btn-sm btn-primary ml-2">
            <i class="fas fa-cog"></i> @lang('Setup WhatsApp')
        </a>
    </div>
@endif

<div class="row mb-3">
    <div class="col-md-2 col-6 mb-2">
        <div class="card h-100">
            <div class="card-body text-center p-3">
                <h4 class="mb-0">{{ $stats['total'] }}</h4>
                <small class="text-muted">@lang('Total sent')</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6 mb-2">
        <div class="card h-100">
            <div class="card-body text-center p-3">
                <h4 class="mb-0 text-success">{{ $stats['delivered'] }}</h4>
                <small class="text-muted">@lang('Delivered')</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6 mb-2">
        <div class="card h-100">
            <div class="card-body text-center p-3">
                <h4 class="mb-0 text-warning">{{ $stats['pending'] }}</h4>
                <small class="text-muted">@lang('In transit')</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6 mb-2">
        <div class="card h-100">
            <div class="card-body text-center p-3">
                <h4 class="mb-0 text-danger">{{ $stats['failed'] }}</h4>
                <small class="text-muted">@lang('Failed')</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6 mb-2">
        <div class="card h-100">
            <div class="card-body text-center p-3">
                <h4 class="mb-0">{{ $stats['replies'] }}</h4>
                <small class="text-muted">@lang('Replies')</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6 mb-2">
        <div class="card h-100">
            <div class="card-body text-center p-3">
                <h4 class="mb-0 text-primary">{{ $stats['unread'] }}</h4>
                <small class="text-muted">@lang('Unread replies')</small>
            </div>
        </div>
    </div>
</div>

<ul class="nav nav-pills mb-3">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.whatsapp.index') ? 'active' : '' }}"
           href="{{ route('admin.whatsapp.index') }}">
            <i class="fas fa-tachometer-alt"></i> @lang('Overview')
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.whatsapp.listings') ? 'active' : '' }}"
           href="{{ route('admin.whatsapp.listings') }}">
            <i class="fas fa-list"></i> @lang('Listings')
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.whatsapp.enquiries') || request()->routeIs('admin.whatsapp.enquiry') ? 'active' : '' }}"
           href="{{ route('admin.whatsapp.enquiries') }}">
            <i class="fas fa-inbox"></i> @lang('Enquiries')
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.whatsapp.chats') || request()->routeIs('admin.whatsapp.chat') ? 'active' : '' }}"
           href="{{ route('admin.whatsapp.chats') }}">
            <i class="fab fa-whatsapp"></i> @lang('Chats')
            {{-- Always in the markup, hidden at zero, so the chat page's
                 poll can update the count without rebuilding the nav. --}}
            <span class="badge badge-light wa-nav-unread"
                  style="{{ $stats['unread'] ? '' : 'display:none' }}">{{ $stats['unread'] }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.whatsapp.replies') ? 'active' : '' }}"
           href="{{ route('admin.whatsapp.replies') }}">
            <i class="fas fa-comments"></i> @lang('Replies')
        </a>
    </li>
    <li class="nav-item ml-auto">
        <a class="nav-link" href="{{ route('admin.setting.gupshup') }}">
            <i class="fas fa-cog"></i> @lang('Setup WhatsApp')
        </a>
    </li>
</ul>
