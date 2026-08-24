<!-- Sidebar Start -->
@php
    $admin = auth()->guard('admin')->user();
@endphp

<div class="res-sidebar-sign">
    <span class="sidebar-arrow">
    </span>
</div>
<div class="sidebar">
    <div class="sidebar-wrapper">
        <div class="sidebar-logo-area">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-logo"><img
                    src="{{ asset('assets/images/logo/logo_dark.png') }}?v={{ time() }}" alt="logo"></a>
            <a href="{{ route('admin.dashboard') }}" class="sidebar-favicon"><img
                    src="{{ asset('assets/images/logo/favicon_new.png') }}" alt="logo"></a>
        </div>
        <div class="sidebar-menu-area">
            <ul class="sidebar-menu">
                @adminCan('dashboard.view')
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.dashboard') }}" class="{{ menuActive('admin.dashboard')[0] }}">
                        <i class="fas fa-home"></i>
                        <span class="sidebar-menu-title">@lang('Dashboard')</span>
                    </a>
                </li>
                @endadminCan

                @adminCan('analytics.view')
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.analytics.index') }}" class="{{ menuActive('admin.analytics*')[0] }}">
                        <i class="fas fa-chart-line"></i>
                        <span class="sidebar-menu-title">@lang('Analytics')</span>
                    </a>
                </li>
                @endadminCan

                @adminCan('user.view')
                <li class="sidebar-menu-item has-child-menu">
                    <a href="#0" class="{{ menuActive('admin.users*')[0] }}">
                        <i class="fas fa-users"></i>
                        <span class="sidebar-menu-title">@lang('Users')</span>
                        <span class="fa-arrow"></span>
                    </a>
                    <div class="sidebar-child-menu {{ menuActive('admin.users*')[1] }}">
                        <ul>
                            <li class="sidebar-menu-item">
                                <a href="{{ route('admin.users.all') }}" class="{{ menuActive('admin.users.all')[0] }}">
                                    <span class="sidebar-menu-title"><i class="fas fa-caret-right mr-0"></i>
                                        @lang('All Users')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item">
                                <a href="{{ route('admin.users.unverified') }}"
                                    class="{{ menuActive('admin.users.unverified')[0] }}">
                                    <span class="sidebar-menu-title"><i class="fas fa-caret-right mr-0"></i>
                                        @lang('Un-verified Users')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item">
                                <a href="{{ route('admin.users.banned') }}"
                                    class="{{ menuActive('admin.users.banned')[0] }}">
                                    <span class="sidebar-menu-title"><i class="fas fa-caret-right mr-0"></i>
                                        @lang('Banned Users')</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endadminCan

@adminCan('listing.view')
 <li class="sidebar-menu-item has-child-menu">
                    <a href="#0" class="{{ menuActive('admin.listing*')[0] }}">
                        <i class="fas fa-list"></i>
                        <span class="sidebar-menu-title">@lang('Listing')</span>
                        <span class="fa-arrow"></span>
                        @if ($pendingListings)
                            <span class="badge badge-danger"><i class="fas fa-spinner fa-spin m-0"></i></span>
                        @endif
                    </a>
                    <div class="sidebar-child-menu {{ menuActive('admin.listing*')[1] }}">
                        <ul>
                            <li class="sidebar-menu-item">
                                <a href="{{ route('admin.listing.index') }}"
                                    class="{{ menuActive('admin.listing.index')[0] }}">
                                    <span class="sidebar-menu-title"><i class="fas fa-chevron-right mr-0"></i>
                                        @lang('All Listings')</span>
                                </a>
                            </li>
                             <li class="sidebar-menu-item">
                                <a href="{{ route('admin.listing.add') }}"
                                    class="{{ menuActive('admin.listing.add')[0] }}">
                                    <span class="sidebar-menu-title"><i class="fas fa-chevron-right mr-0"></i>
                                        @lang('Add Listing')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item">
                                <a href="{{ route('admin.listing.pending') }}"
                                    class="{{ menuActive('admin.listing.pending')[0] }}">
                                    <span class="sidebar-menu-title"><i class="fas fa-chevron-right mr-0"></i>
                                        @lang('Pending Listings')</span>
                                    @if ($pendingListings)
                                        <span class="badge badge-danger">{{ $pendingListings }}</span>
                                    @endif
                                </a>
                            </li>
                             <li class="sidebar-menu-item">
                <a href="{{ route('admin.listing.update.pending') }}"
                    class="{{ menuActive('admin.listing.update.pending')[0] }}">
                    <span class="sidebar-menu-title">
                        <i class="fas fa-chevron-right mr-0"></i> Update Requests
                    </span>

                    @if ($pendingUpdateListings ?? 0)
                        <span class="badge badge-warning">{{ $pendingUpdateListings }}</span>
                    @endif
                </a>
            </li>

            
                            <li class="sidebar-menu-item">
                                <a href="{{ route('admin.listing.approved') }}"
                                    class="{{ menuActive('admin.listing.approved')[0] }}">
                                    <span class="sidebar-menu-title"><i class="fas fa-chevron-right mr-0"></i>
                                        @lang('Approved Listings')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item">
                                <a href="{{ route('admin.listing.rejected') }}"
                                    class="{{ menuActive('admin.listing.rejected')[0] }}">
                                    <span class="sidebar-menu-title"><i class="fas fa-chevron-right mr-0"></i>
                                        @lang('Rejected Listings')</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
@endadminCan

                @adminCanAny('category.view', 'subcategory.view')
                <li class="sidebar-menu-item has-child-menu">
                    <a href="#0" class="{{ menuActive('admin.category*')[0] }}">
                           <i class="fas fa-bars"></i>
                        <span class="sidebar-menu-title">@lang('Category')</span>
                        <span class="fa-arrow"></span>
                    </a>
                    <div class="sidebar-child-menu {{ menuActive('admin.category*')[1] }}">
                        <ul>
                            @adminCan('category.view')
                               <li class="sidebar-menu-item">
                                <a href="{{ route('admin.category.index') }}" class="{{ menuActive('admin.category.index')[0] }}">
                                    <span class="sidebar-menu-title"><i class="fas fa-caret-right mr-0"></i>
                                        @lang('Category')</span>
                                </a>
                            </li>
                            @endadminCan

                            @adminCan('subcategory.view')
                              <li class="sidebar-menu-item">
                                <a href="{{ route('admin.subcategories.index') }}" class="{{ menuActive('admin.category.index')[0] }}">
                                    <span class="sidebar-menu-title"><i class="fas fa-caret-right mr-0"></i>
                                        @lang('subcategories')</span>
                                </a>
                            </li>
                            @endadminCan
                        </ul>
                    </div>
                </li>
                @endadminCanAny

                @adminCan('location.view')
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.location.index') }}" class="{{ menuActive('admin.location*')[0] }}">
                        <i class="fas fa-map-marker-alt"></i>
                        <span class="sidebar-menu-title">@lang('Location')</span>
                    </a>
                </li>
                @endadminCan


@adminCan('agent.manage')
                   <li class="sidebar-menu-item">
                    <a href="{{ route('admin.agents.index') }}" class="{{ menuActive('admin.agents*')[0] }}">
                      <i class="nav-icon fas fa-users-cog"></i>
                        <span class="sidebar-menu-title">@lang('Manage Agents')</span>
                    </a>
                </li>
@endadminCan

@adminCan('role.manage')
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.roles.index') }}" class="{{ menuActive('admin.roles*')[0] }}">
                        <i class="nav-icon fas fa-user-shield"></i>
                        <span class="sidebar-menu-title">@lang('Roles & Permissions')</span>
                    </a>
                </li>
@endadminCan
@adminCan('enquiry.view')
              <li class="sidebar-menu-item has-child-menu">
    <a href="#0" class="{{ menuActive('admin.enquiry*')[0] }}">
        <i class="fas fa-envelope"></i>
        <span class="sidebar-menu-title">@lang('Enquiry')</span>
        <span class="fa-arrow"></span>
    </a>

    <div class="sidebar-child-menu {{ menuActive('admin.enquiry*')[1] }}">
        <ul>
            <li class="sidebar-menu-item">
                <a href="{{ route('admin.enquiry.index') }}" class="{{ menuActive('admin.enquiry.index')[0] }}">
                    <span class="sidebar-menu-title">
                        <i class="fas fa-caret-right mr-0"></i> All Enquiries
                    </span>
                </a>
            </li>
<li class="sidebar-menu-item">
    <a href="{{ route('admin.enquiry.clicks') }}" class="{{ menuActive('admin.enquiry.clicks')[0] }}">
        <span class="sidebar-menu-title">
            <i class="fas fa-caret-right mr-0"></i> Listing Clicks
        </span>
    </a>
</li>

        </ul>
    </div>
</li>
@endadminCan
@adminCan('whatsapp.view')
              <li class="sidebar-menu-item has-child-menu">
    <a href="#0" class="{{ menuActive('admin.whatsapp*')[0] }}">
        <i class="fab fa-whatsapp"></i>
        <span class="sidebar-menu-title">@lang('WhatsApp')</span>
        @if(!empty($unreadWhatsappReplies))
            <span class="badge badge-danger">{{ $unreadWhatsappReplies }}</span>
        @endif
        <span class="fa-arrow"></span>
    </a>

    <div class="sidebar-child-menu {{ menuActive('admin.whatsapp*')[1] }}">
        <ul>
            <li class="sidebar-menu-item">
                <a href="{{ route('admin.whatsapp.index') }}" class="{{ menuActive('admin.whatsapp.index')[0] }}">
                    <span class="sidebar-menu-title">
                        <i class="fas fa-caret-right mr-0"></i> WhatsApp Panel
                    </span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('admin.whatsapp.listings') }}" class="{{ menuActive('admin.whatsapp.listings')[0] }}">
                    <span class="sidebar-menu-title">
                        <i class="fas fa-caret-right mr-0"></i> Send to Listings
                    </span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('admin.whatsapp.enquiries') }}" class="{{ menuActive('admin.whatsapp.enquiries')[0] }}">
                    <span class="sidebar-menu-title">
                        <i class="fas fa-caret-right mr-0"></i> Enquiries
                    </span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('admin.whatsapp.chats') }}" class="{{ menuActive('admin.whatsapp.chats')[0] }}">
                    <span class="sidebar-menu-title">
                        <i class="fas fa-caret-right mr-0"></i> Chats
                        @if(!empty($unreadWhatsappReplies))
                            <span class="badge badge-danger">{{ $unreadWhatsappReplies }}</span>
                        @endif
                    </span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('admin.whatsapp.replies') }}" class="{{ menuActive('admin.whatsapp.replies')[0] }}">
                    <span class="sidebar-menu-title">
                        <i class="fas fa-caret-right mr-0"></i> Replies
                        @if(!empty($unreadWhatsappReplies))
                            <span class="badge badge-danger">{{ $unreadWhatsappReplies }}</span>
                        @endif
                    </span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="{{ route('admin.setting.gupshup') }}" class="{{ menuActive('admin.setting.gupshup')[0] }}">
                    <span class="sidebar-menu-title">
                        <i class="fas fa-caret-right mr-0"></i> Setup WhatsApp
                    </span>
                </a>
            </li>
        </ul>
    </div>
</li>
@endadminCan
@adminCan('package.manage')
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.package.index') }}" class="{{ menuActive('admin.package*')[0] }}">
                        <i class="fas fa-list-alt"></i>
                        <span class="sidebar-menu-title">@lang('Package')</span>
                    </a>
                </li>
                @endif


                @adminCan('banner.manage')
                <li class="sidebar-menu-item">
    <a href="{{ route('admin.banners.index') }}" class="{{ menuActive('admin.banners*')[0] }}">
        <i class="las la-image"></i>
        <span class="sidebar-menu-title">@lang('Banners')</span>
    </a>
</li>
@endif

 @adminCan('category.manage')
                <li class="sidebar-menu-item">
    <a href="{{ route('admin.categoryHeading.index') }}" class="{{ menuActive('admin.categoryHeading*')[0] }}">
       <i class="nav-icon fas fa-heading"></i>
        <span class="sidebar-menu-title">@lang('Category Heading')</span>
    </a>
</li>
@endif

@adminCan('seasonal.manage')
    <li class="sidebar-menu-item">
        <a href="{{ route('admin.seasonal.sections.index') }}"
           class="{{ menuActive('admin.seasonal.sections*')[0] }}">

            <i class="nav-icon fas fa-sun"></i>

            <span class="sidebar-menu-title">
                @lang('Seasonal Section')
            </span>
        </a>
    </li>
@endif

  @adminCan('hbb.manage')
 <li class="sidebar-menu-item">
    <a href="{{ route('admin.hbb.index') }}" class="{{ menuActive('admin.hbb*')[0] }}">
        <i class="las la-image"></i>
        <span class="sidebar-menu-title">@lang('Home Bottom Banners')</span>
    </a>
</li>
@endif


@adminCan('guestchat.view')
<li class="sidebar-menu-item">
    <a href="{{ route('admin.guest.chats.index') }}"
       class="{{ menuActive('admin.guest.chats*')[0] }}">

        <i class="las la-comments"></i>
        <span class="sidebar-menu-title">Guest Chats</span>

        @if(($unreadGuestChats ?? 0) > 0)
            <span class="badge badge-danger">
                {{ $unreadGuestChats }}
            </span>
        @endif
    </a>
</li>
@endadminCan


@adminCan('seo.audit')
<li class="sidebar-menu-item">
    <a href="{{ route('admin.seo.audit') }}"
       class="{{ menuActive('admin.seo.audit*')[0] }}">

        <i class="las la-search-plus"></i>

        <span class="sidebar-menu-title">
            @lang('SEO Audit')
        </span>
    </a>
</li>
@endadminCan


@adminCan('schema.manage')
<li class="sidebar-menu-item">
    <a href="{{ route('admin.schema.index') }}"
       class="{{ menuActive('admin.schema*')[0] }}">

        <i class="las la-code"></i>

        <span class="sidebar-menu-title">
            @lang('Schema Settings')
        </span>
    </a>
</li>
@endif


@adminCan('keyword.manage')
<li class="sidebar-menu-item">
    <a href="{{ route('admin.service-keywords.index') }}"
        class="{{ menuActive('admin.service-keywords*')[0] }}">
        <i class="fas fa-tags mr-2"></i>
        <span class="sidebar-menu-title">Service Keywords</span>
    </a>
</li>
@endadminCan
               

    @adminCan('gateway.manage')
                <li class="text-white nav-section">@lang('Finance')</li>
                <li class="sidebar-menu-item has-child-menu">
                    <a href="#0" class="{{ menuActive('admin.gateway.method*')[0] }}">
                        <i class="fas fa-money-check-alt"></i>
                        <span class="sidebar-menu-title">@lang('Payment Gateway')</span>
                        <span class="fa-arrow"></span>
                    </a>
                    <div class="sidebar-child-menu {{ menuActive('admin.gateway.method*')[1] }}">
                        <ul>
                            <li class="sidebar-menu-item">
                                <a href="{{ route('admin.gateway.method.index') }}"
                                    class="{{ menuActive('admin.gateway.method.index')[0] }}">
                                    <span class="sidebar-menu-title"><i class="fas fa-caret-right mr-0"></i>
                                        @lang('Gateway Method')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item">
                                <a href="{{ route('admin.gateway.method.currencies') }}"
                                    class="{{ menuActive('admin.gateway.method.currencies')[0] }}">
                                    <span class="sidebar-menu-title"><i class="fas fa-caret-right mr-0"></i>
                                        @lang('Method Currencies')</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                  @endif

                      @adminCan('deposit.view')
                <li class="sidebar-menu-item has-child-menu">
                    <a href="#0" class="{{ menuActive('admin.deposit*')[0] }}">
                        <i class="fas fa-download"></i>
                        <span class="sidebar-menu-title">@lang('Payments')</span>
                        @if ($pendingDeposits)
                            <span class="badge badge-danger"><i class="fas fa-spinner fa-spin m-0"></i></span>
                        @endif
                        <span class="fa-arrow"></span>
                    </a>
                    <div class="sidebar-child-menu {{ menuActive('admin.deposit*')[1] }}">
                        <ul>
                            <li class="sidebar-menu-item">
                                <a href="{{ route('admin.deposit.all') }}"
                                    class="{{ menuActive('admin.deposit.all')[0] }}">
                                    <span class="sidebar-menu-title"><i class="fas fa-caret-right mr-0"></i>
                                        @lang('All Payments')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item">
                                <a href="{{ route('admin.deposit.approved') }}"
                                    class="{{ menuActive('admin.deposit.approved')[0] }}">
                                    <span class="sidebar-menu-title"><i class="fas fa-caret-right mr-0"></i>
                                        @lang('Approved Payments')</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item">
                                <a href="{{ route('admin.deposit.pending') }}"
                                    class="{{ menuActive('admin.deposit.pending')[0] }}">
                                    <span class="sidebar-menu-title"><i class="fas fa-caret-right mr-0"></i>
                                        @lang('Pending Payments')</span>
                                    @if ($pendingDeposits)
                                        <span class="badge badge-danger">{{ $pendingDeposits }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="sidebar-menu-item">
                                <a href="{{ route('admin.deposit.rejected') }}"
                                    class="{{ menuActive('admin.deposit.rejected')[0] }}">
                                    <span class="sidebar-menu-title"><i class="fas fa-caret-right mr-0"></i>
                                        @lang('Rejected Payments')</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                  @endif


                    
                @adminCan('history.view')
                <li class="text-white nav-section">@lang('History')</li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.history.login') }}" class="{{ menuActive('admin.history.login')[0] }}">
                        <i class="fas fa-sign-in-alt"></i>
                        <span class="sidebar-menu-title">@lang('Login History')</span>
                    </a>
                </li>
                @endadminCan
                  @adminCan('subscriber.view')
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.subscriber.index') }}"
                        class="{{ menuActive('admin.subscriber*')[0] }}">
                        <i class="fas fa-mail-bulk"></i>
                        <span class="sidebar-menu-title">@lang('Subscriber')</span>
                    </a>
                </li>

                  @endif
                      @adminCan('setting.manage')
                <li class="text-white nav-section">@lang('Settings')</li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.setting.index') }}" class="{{ menuActive('admin.setting.index')[0] }}">
                        <i class="fas fa-cogs"></i>
                        <span class="sidebar-menu-title">@lang('Setting')</span>
                    </a>
                </li>

                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.setting.recaptcha') }}" class="{{ menuActive('admin.setting.recaptcha')[0] }}">
                        <i class="fas fa-puzzle-piece"></i>
                        <span class="sidebar-menu-title">@lang('Recaptcha')</span>
                    </a>
                </li>

                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.language.index') }}" class="{{ menuActive('admin.language*')[0] }}">
                      <i class="fas fa-language"></i>
                      <span class="sidebar-menu-title">@lang('Language')</span>
                    </a>
                </li>
                  @endif

    @adminCan('setting.manage')
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.setting.logofav') }}"
                        class="{{ menuActive('admin.setting.logofav')[0] }}">
                        <i class="fas fa-copyright"></i>
                        <span class="sidebar-menu-title">@lang('Logo & Favicon')</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.setting.seo') }}" class="{{ menuActive('admin.setting.seo')[0] }}">
                        <i class="fas fa-chart-line"></i>
                        <span class="sidebar-menu-title">@lang('SEO Setting')</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.setting.email.index') }}"
                        class="{{ menuActive('admin.setting.email.index')[0] }}">
                        <i class="fas fa-envelope-open"></i>
                        <span class="sidebar-menu-title">@lang('Email Setting')</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.setting.social.login') }}"
                        class="{{ menuActive('admin.setting.social.login')[0] }}">
                        <i class="fab fa-google"></i>
                        <span class="sidebar-menu-title">@lang('Social Login')</span>
                    </a>
                </li>
{{-- WhatsApp Setting is reachable from the WhatsApp menu above
                     (Setup WhatsApp), so it is not repeated here. --}}

                <li class="sidebar-menu-item has-child-menu">
                    <a href="#0" class="{{ menuActive('admin.theme*','admin.services.index')[0] }}">
                        <i class="fas fa-sliders-h"></i>
                        <span class="sidebar-menu-title">@lang('Theme Options')</span>
                        <span class="fa-arrow"></span>
                    </a>
                    <div class="sidebar-child-menu {{ menuActive('admin.theme*','admin.services.index')[1] }}">
                        <ul>

                            @foreach ($tempBars as $key => $bar)
                                <li class="sidebar-menu-item">
                                    <a href="{{ route('admin.theme.item', $key) }}"
                                        class="@if (route('admin.theme.item', $key) == url()->current()) sidebar-menu-active @endif">
                                        <span class="sidebar-menu-title"><i class="fas fa-chevron-right mr-0"></i>
                                            {{ __($bar['name']) }}</span>
                                    </a>
                                </li>
                            @endforeach
                            
                             <li class="sidebar-menu-item">
                    <a href="{{ route('admin.services.index') }}"
                        class="{{ menuActive('admin.services.index')[0] }}">
                        <i class="fas fa-chevron-right mr-0"></i>
                        <span class="sidebar-menu-title">@lang('services')</span>
                    </a>
                </li>

                <li class="sidebar-menu-item">
    <a href="{{ route('admin.social.sites.index') }}"
        class="{{ menuActive('admin.social.sites.index')[0] }}">
        <i class="fas fa-chevron-right mr-0"></i>
        <span class="sidebar-menu-title">Social Sites</span>
    </a>
</li>

  <li class="sidebar-menu-item">
    <a href="{{ route('admin.home.content.sections.index') }}"
        class="{{ menuActive('admin.home.content.sections.index')[0] }}">
        <i class="fas fa-chevron-right mr-0"></i>
        <span class="sidebar-menu-title">Home Content Section</span>
    </a>
</li>



                        </ul>
                    </div>
                </li>
                  @endif


            </ul>
            <div class="mt-4 mb-4 copyright">
                <p class="text-white m-l-18">@lang('@Copyright') <a href="{{ route('admin.dashboard') }}"
                        class="color-primary">{{ __($setting->site_name) }}</a> {{ date('Y') }}</p>
            </div>
        </div>
    </div>
</div>
<!-- Sidebar End
