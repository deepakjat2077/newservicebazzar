@extends($themeView.'layouts.app')
@section('content')
@section('title', 'Leads — '.$listing->name)

<section class="pt-30 pb-80">
    <div class="container">
        <div class="sb-biz-header">
            <a href="{{ route('user.listing.dashboard', $listing->id) }}" class="sb-biz-back"><i class="fas fa-arrow-left"></i></a>
            <div class="sb-biz-header-title">
                <h4>{{ $listing->name }}</h4>
                <span>{{ optional($listing->location)->name ?? $listing->area }}</span>
            </div>
            <a href="{{ route('contact') }}" class="sb-leads-help"><i class="fas fa-headset"></i> Help</a>
        </div>

        <div class="sb-leads-newcount">Total Enquiries : {{ $totalCount }}</div>

        {{-- The "Today's Enquiries" link from the dashboard lands here with
             filter=today — that's a single fixed view (today's leads, full
             stop), not another tab among the rest, so the whole filter bar
             stays hidden for it rather than showing tabs that would just
             re-filter away from "today" the moment one was clicked.

             New / Missed Enquiries carry real unread-count badges. Missed
             Call and Not Connected are left as coming-soon: this site has
             no telephony behind it, so it genuinely cannot know whether a
             call rang and went unanswered — unlike New/Missed, which are
             derived straight from this listing's own enquiry rows. --}}
        @if($filter !== 'today')
        <div class="sb-leads-filters">
            <a href="{{ route('user.listing.leads', ['id' => $listing->id, 'filter' => 'new']) }}" class="sb-leads-filter-btn {{ $filter === 'new' ? 'is-active' : '' }}">
                @if($newCount > 0)<span class="sb-leads-filter-badge">{{ $newCount }}</span>@endif
                New
            </a>
            <a href="{{ route('user.listing.leads', ['id' => $listing->id, 'filter' => 'missed']) }}" class="sb-leads-filter-btn {{ $filter === 'missed' ? 'is-active' : '' }}">
                @if($missedCount > 0)<span class="sb-leads-filter-badge">{{ $missedCount }}</span>@endif
                Missed Enquiries
            </a>
            <button type="button" class="sb-leads-filter-btn" id="sbLeadsDateBtn">
                <i class="fas fa-calendar"></i> Date <i class="fas fa-chevron-down"></i>
            </button>
            <a href="{{ route('user.listing.leads', ['id' => $listing->id, 'filter' => 'hidden']) }}" class="sb-leads-filter-btn {{ $filter === 'hidden' ? 'is-active' : '' }}">
                <i class="fas fa-eye-slash"></i> Hidden
            </a>
            <a href="{{ route('user.listing.leads', ['id' => $listing->id, 'filter' => 'starred']) }}" class="sb-leads-filter-btn {{ $filter === 'starred' ? 'is-active' : '' }}">
                <i class="fas fa-star"></i> Starred
            </a>
            <a href="{{ route('user.listing.leads', ['id' => $listing->id, 'filter' => 'notes']) }}" class="sb-leads-filter-btn {{ $filter === 'notes' ? 'is-active' : '' }}">
                <i class="fas fa-note-sticky"></i> Notes
            </a>
            <div class="sb-leads-filter-btn is-disabled" title="No call telephony on this site yet">
                <i class="fas fa-phone-slash"></i> Missed Call
            </div>
            <a href="{{ route('user.listing.leads', ['id' => $listing->id, 'filter' => 'hot']) }}" class="sb-leads-filter-btn {{ $filter === 'hot' ? 'is-active' : '' }}">
                <i class="fas fa-fire"></i> Hot Enquiries
            </a>
            <div class="sb-leads-filter-btn is-disabled" title="No call telephony on this site yet">
                <i class="fas fa-phone"></i> Not Connected
            </div>
            @if($categoryOptions->isNotEmpty())
                <button type="button" class="sb-leads-filter-btn" id="sbLeadsCategoryBtn">
                    Category <i class="fas fa-chevron-down"></i>
                </button>
            @endif
            <button type="button" class="sb-leads-filter-btn" id="sbLeadsStatusBtn">
                Enquiry Status <i class="fas fa-chevron-down"></i>
            </button>
            <div class="sb-leads-filter-btn is-disabled" title="No review-per-enquiry data on this site yet">
                Rating <i class="fas fa-chevron-down"></i>
            </div>
        </div>

        {{-- Date dropdown --}}
        <div class="sb-leads-dropdown" id="sbLeadsDateDropdown" hidden>
            <a href="{{ request()->fullUrlWithQuery(['date_range' => 'today']) }}" class="sb-leads-dropdown-item {{ $dateRange === 'today' ? 'is-active' : '' }}">Today</a>
            <a href="{{ request()->fullUrlWithQuery(['date_range' => 'yesterday']) }}" class="sb-leads-dropdown-item {{ $dateRange === 'yesterday' ? 'is-active' : '' }}">Yesterday</a>
            <a href="{{ request()->fullUrlWithQuery(['date_range' => 'week']) }}" class="sb-leads-dropdown-item {{ $dateRange === 'week' ? 'is-active' : '' }}">This Week</a>
            <a href="{{ request()->fullUrlWithQuery(['date_range' => 'month']) }}" class="sb-leads-dropdown-item {{ $dateRange === 'month' ? 'is-active' : '' }}">This Month</a>
            <form action="{{ route('user.listing.leads', $listing->id) }}" method="get" class="sb-leads-dropdown-custom">
                @if($filter)<input type="hidden" name="filter" value="{{ $filter }}">@endif
                <input type="hidden" name="date_range" value="custom">
                <input type="date" name="date_from" class="sb-input">
                <input type="date" name="date_to" class="sb-input">
                <button type="submit" class="sb-wizard-secondary-btn">Apply</button>
            </form>
            @if($dateRange)
                <a href="{{ route('user.listing.leads', array_filter(['id' => $listing->id, 'filter' => $filter])) }}" class="sb-leads-dropdown-clear">Clear date filter</a>
            @endif
        </div>

        {{-- Enquiry Status dropdown --}}
        <div class="sb-leads-dropdown" id="sbLeadsStatusDropdown" hidden>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'unread']) }}" class="sb-leads-dropdown-item {{ $status === 'unread' ? 'is-active' : '' }}">Unread</a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'read']) }}" class="sb-leads-dropdown-item {{ $status === 'read' ? 'is-active' : '' }}">Responded</a>
            @if($status)
                <a href="{{ route('user.listing.leads', array_filter(['id' => $listing->id, 'filter' => $filter])) }}" class="sb-leads-dropdown-clear">Clear status filter</a>
            @endif
        </div>
        @endif

        @php
            $sbReviewLink = $listing->category
                ? route('listings.details', [$listing->category->slug, $listing->slug])
                : null;
            $sbFallbackCategoryName = optional($listing->category)->name;
        @endphp

        @if($leads->isEmpty())
            <p class="sb-wizard-empty">No enquiries here yet.</p>
        @else
            <div class="sb-leads-list">
                @foreach($leads as $lead)
                    <div class="sb-lead-card" data-lead-id="{{ $lead->id }}">
                        <div class="sb-lead-card-main">
                            <div class="sb-lead-badges">
                                @if(!$lead->is_read)
                                    <span class="sb-lead-badge sb-lead-badge--new">NEW</span>
                                @endif
                                @if($lead->created_at->gte(now()->subDay()))
                                    <span class="sb-lead-badge sb-lead-badge--hot"><i class="fas fa-fire"></i> HOT ENQUIRY</span>
                                @endif
                            </div>

                            <div class="sb-lead-row">
                                <div class="sb-lead-info">
                                    <button type="button" class="sb-lead-star {{ $lead->is_starred ? 'is-starred' : '' }}" data-star-url="{{ route('user.listing.leads.star', $lead->id) }}">
                                        <i class="fas fa-star"></i>
                                    </button>
                                    <strong>{{ $lead->name ?: 'Unknown' }}</strong>
                                    <div class="sb-lead-meta">{{ optional($lead->location)->name }}</div>
                                    <div class="sb-lead-meta">{{ optional($lead->category)->name ?? $sbFallbackCategoryName ?? '' }}</div>

                                    <button type="button" class="sb-lead-price-btn is-disabled" title="No pricing/offers feature yet">
                                        <i class="fas fa-gear"></i> Send Special Price
                                    </button>

                                    <button type="button" class="sb-lead-showdetails" data-lead-details="{{ $lead->id }}" data-read-url="{{ route('user.listing.leads.read', $lead->id) }}">Show details</button>

                                    <div class="sb-lead-details" id="sbLeadDetails{{ $lead->id }}" hidden>
                                        <div class="sb-lead-actions">
                                            <button type="button" class="sb-lead-action-btn sb-lead-note-btn" data-note-url="{{ route('user.listing.leads.note', $lead->id) }}" data-current-note="{{ $lead->note }}">
                                                <i class="fas fa-note-sticky"></i> {{ $lead->note ? 'Edit Note' : 'Add Note' }}
                                            </button>
                                            <button type="button" class="sb-lead-action-btn is-disabled">
                                                <i class="fas fa-gear"></i> Send Special Price
                                            </button>
                                            <button type="button" class="sb-lead-action-btn sb-lead-reminder-btn" data-reminder-url="{{ route('user.listing.leads.reminder', $lead->id) }}">
                                                <i class="fas fa-clock"></i> Add Reminder
                                            </button>
                                            @if($sbReviewLink)
                                                <a href="https://wa.me/{{ preg_replace('/\D/', '', $lead->phone) }}?text={{ urlencode('Hi '.($lead->name ?: '').', thanks for your enquiry! We would really appreciate it if you could leave us a review: '.$sbReviewLink) }}" target="_blank" class="sb-lead-action-btn">
                                                    <i class="fas fa-star-half-stroke"></i> Ask Rating
                                                </a>
                                            @endif
                                            <button type="button" class="sb-lead-action-btn sb-lead-share-btn" data-share-toggle="sbShare{{ $lead->id }}">
                                                <i class="fas fa-share-nodes"></i> Share
                                            </button>
                                        </div>

                                        @if($lead->note)
                                            <div class="sb-lead-note-preview"><i class="fas fa-note-sticky"></i> {{ $lead->note }}</div>
                                        @endif

                                        @if($lead->reminder_at)
                                            <div class="sb-lead-reminder-preview"><i class="fas fa-clock"></i> Reminder set for {{ $lead->reminder_at->format('d M Y, h:i A') }}</div>
                                        @endif

                                        <div class="sb-lead-detail-block">
                                            <span>Query</span>
                                            <strong>{{ $lead->name ?: 'This buyer' }} searched for {{ optional($lead->category)->name ?? $sbFallbackCategoryName ?? $listing->name }}.</strong>
                                        </div>
                                        <div class="sb-lead-detail-block">
                                            <span>Enquiry Source</span>
                                            <strong>{{ $lead->category_id ? 'Category Search Enquiry' : 'Listing Enquiry' }}</strong>
                                        </div>

                                        <div class="sb-lead-share-panel" id="sbShare{{ $lead->id }}" hidden>
                                            <p>Choose what you want to share on: <span class="sb-lead-share-wa">WhatsApp</span></p>
                                            <div class="sb-lead-share-options">
                                                @if($sbReviewLink)
                                                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $lead->phone) }}?text={{ urlencode('Have a look at our reviews: '.$sbReviewLink) }}" target="_blank" class="sb-lead-share-opt"><i class="fas fa-star"></i> Reviews</a>
                                                @endif
                                                @if($sbReviewLink)
                                                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $lead->phone) }}?text={{ urlencode('Check out our business: '.$sbReviewLink) }}" target="_blank" class="sb-lead-share-opt"><i class="fas fa-image"></i> Business Photos</a>
                                                @endif
                                                @if($listing->address)
                                                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $lead->phone) }}?text={{ urlencode('Our address: '.$listing->address) }}" target="_blank" class="sb-lead-share-opt"><i class="fas fa-location-dot"></i> Address</a>
                                                @endif
                                            </div>
                                        </div>

                                        <button type="button" class="sb-lead-hidedetails" data-lead-details="{{ $lead->id }}">Hide details</button>
                                    </div>
                                </div>

                                <div class="sb-lead-side">
                                    <small>{{ $lead->created_at->format('D, h:i A') }}</small>
                                    <div class="sb-lead-contact-btns">
                                        <a href="tel:{{ $lead->phone }}" class="sb-lead-contact-btn sb-lead-contact-btn--call"><i class="fas fa-phone"></i></a>
                                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $lead->phone) }}" target="_blank" class="sb-lead-contact-btn sb-lead-contact-btn--whatsapp"><i class="fab fa-whatsapp"></i></a>
                                        @if($lead->email)
                                            <a href="mailto:{{ $lead->email }}" class="sb-lead-contact-btn sb-lead-contact-btn--email"><i class="fas fa-envelope"></i></a>
                                        @endif
                                    </div>
                                    <button type="button" class="sb-lead-hide-btn" data-hide-url="{{ route('user.listing.leads.hide', $lead->id) }}">
                                        <i class="fas {{ $lead->is_hidden ? 'fa-eye' : 'fa-eye-slash' }}"></i> {{ $lead->is_hidden ? 'Unhide' : 'Hide' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="sb-leads-pagination">{{ $leads->links() }}</div>
        @endif
    </div>
</section>

{{-- Note modal --}}
<div class="sb-biz-modal-overlay" id="sbNoteModalOverlay">
    <div class="sb-biz-modal sb-leads-modal">
        <button type="button" class="sb-biz-modal-close" data-modal-close="sbNoteModalOverlay">&times;</button>
        <h5>Note</h5>
        <textarea id="sbNoteTextarea" class="sb-leads-textarea" placeholder="Enter your note here..."></textarea>
        <button type="button" class="sb-wizard-primary-btn" id="sbNoteSaveBtn">Save Note</button>
    </div>
</div>

{{-- Reminder modal --}}
<div class="sb-biz-modal-overlay" id="sbReminderModalOverlay">
    <div class="sb-biz-modal sb-leads-modal">
        <button type="button" class="sb-biz-modal-close" data-modal-close="sbReminderModalOverlay">&times;</button>
        <h5>Add Reminder</h5>

        <p class="sb-leads-modal-label">Select Date</p>
        <div class="sb-leads-radio-row">
            <label><input type="radio" name="sbReminderDate" value="today"> Today</label>
            <label><input type="radio" name="sbReminderDate" value="tomorrow"> Tomorrow</label>
            <label><input type="radio" name="sbReminderDate" value="pick"> Pick a date</label>
        </div>
        <input type="date" id="sbReminderDateInput" class="sb-input sb-leads-date-input" hidden>

        <p class="sb-leads-modal-label">Select Time</p>
        <div class="sb-leads-radio-row">
            <label><input type="radio" name="sbReminderTime" value="10:00"> 10 am</label>
            <label><input type="radio" name="sbReminderTime" value="14:00"> 2 pm</label>
            <label><input type="radio" name="sbReminderTime" value="18:00"> 6 pm</label>
            <label><input type="radio" name="sbReminderTime" value="pick"> Pick a time</label>
        </div>
        <input type="time" id="sbReminderTimeInput" class="sb-input sb-leads-date-input" hidden>

        <div class="sb-leads-modal-btns">
            <button type="button" class="sb-leads-cancel-btn" data-modal-close="sbReminderModalOverlay">Close</button>
            <button type="button" class="sb-wizard-primary-btn" id="sbReminderSaveBtn">Save Reminder</button>
        </div>
    </div>
</div>

{{-- Category filter modal --}}
@if($categoryOptions->isNotEmpty())
<div class="sb-biz-modal-overlay" id="sbCategoryModalOverlay">
    <div class="sb-biz-modal sb-leads-modal sb-category-modal">
        <button type="button" class="sb-biz-modal-close" data-modal-close="sbCategoryModalOverlay">&times;</button>
        <h5>Categories</h5>
        <input type="text" class="sb-input" id="sbCategorySearch" placeholder="Search Category">

        <form action="{{ route('user.listing.leads', $listing->id) }}" method="get" id="sbCategoryForm">
            @if($filter)<input type="hidden" name="filter" value="{{ $filter }}">@endif
            <div class="sb-category-list">
                @foreach($categoryOptions as $cat)
                    <label class="sb-category-row">
                        {{ $cat->name }}
                        <input type="checkbox" name="subcategory_id[]" value="{{ $cat->id }}" {{ in_array($cat->id, (array) request('subcategory_id', [])) ? 'checked' : '' }}>
                    </label>
                @endforeach
            </div>
            <button type="submit" class="sb-wizard-primary-btn sb-category-done-btn">Done</button>
        </form>
    </div>
</div>
@endif
@endsection

@push('css')
<style>
    /* Shared with the dashboard page (header, empty-state, modal shell,
       primary button) — duplicated here since this is a separate page with
       its own CSS push block.

       That sentence used to spell out the actual directive syntax here
       (at-push-open-quote-css-close-quote) as plain descriptive text. Blade
       compiles directives by scanning the raw file text, comments included
       — it can't tell "this is inside a CSS comment" from "this is real
       code" — so that literal text opened a second, nested copy of the
       directive at the top of this file, right inside this comment,
       before the real one below ever closed.
       The mismatched push/pop broke output buffering for the whole page:
       this block's own CSS ended up flushed to the very start of the
       response, ahead of <!DOCTYPE html> itself, which is why the page
       rendered with no styling at all. Describe the syntax in words, never
       spell it out literally in a comment. */
    .sb-biz-header{
        display:flex; align-items:center; gap:14px;
        padding-bottom:18px; margin-bottom:20px; border-bottom:1px solid #eceef0;
    }
    .sb-biz-back{ color:#444; font-size:16px; }
    .sb-biz-header-title{ flex:1; }
    .sb-biz-header-title h4{ margin:0; font-size:18px; font-weight:700; color:#222; }
    .sb-biz-header-title span{ font-size:12px; color:#888; }
    .sb-wizard-empty{ font-size:13px; color:#888; }
    .sb-wizard-primary-btn{
        background:#1e90ff; color:#fff; border:0; border-radius:6px;
        padding:11px 26px; font-size:14px; font-weight:600; cursor:pointer;
        display:inline-block; text-align:center; text-decoration:none;
    }
    .sb-wizard-secondary-btn{
        background:#fff; color:#1e90ff; border:1px solid #1e90ff; border-radius:6px;
        padding:9px 18px; font-size:13px; font-weight:600; cursor:pointer;
    }
    .sb-input{
        width:100%; border:1px solid #d5d9dd; border-radius:6px;
        padding:9px 12px; font-size:14px; color:#222; background:#fff;
    }
    .sb-biz-modal-overlay{
        display:none; position:fixed; inset:0; background:rgba(0,0,0,.4);
        z-index:3000; align-items:center; justify-content:center; padding:20px;
    }
    .sb-biz-modal-overlay.show{ display:flex; }
    .sb-biz-modal{
        background:#fff; border-radius:10px; padding:30px 24px 24px;
        width:100%; max-width:420px; position:relative;
    }
    .sb-biz-modal-close{
        position:absolute; top:-16px; right:-16px; width:34px; height:34px;
        border-radius:50%; border:0; background:#fff;
        box-shadow:0 2px 10px rgba(0,0,0,.18); font-size:20px; line-height:1;
        color:#333; cursor:pointer; display:flex; align-items:center; justify-content:center;
    }

    .sb-leads-help{
        background:#1e90ff;
        color:#fff;
        border-radius:20px;
        padding:8px 16px;
        font-size:13px;
        font-weight:600;
    }
    .sb-leads-newcount{ font-size:14px; font-weight:700; color:#222; margin-bottom:14px; }
    .sb-leads-filters{
        display:flex;
        flex-wrap:wrap;
        gap:10px;
        margin-bottom:20px;
        padding-bottom:18px;
        border-bottom:1px solid #eceef0;
    }
    .sb-leads-filter-btn{
        position:relative;
        display:inline-flex;
        align-items:center;
        gap:6px;
        border:1px solid #d5d9dd;
        border-radius:6px;
        background:#fff;
        padding:8px 14px;
        font-size:12.5px;
        font-weight:600;
        color:#444;
    }
    .sb-leads-filter-btn.is-active{ border-color:#1e90ff; color:#1e90ff; background:#eaf4fe; }
    .sb-leads-filter-btn.is-disabled{ color:#bbb; cursor:default; }
    .sb-leads-filter-badge{
        background:#d9364f;
        color:#fff;
        font-size:10px;
        font-weight:700;
        border-radius:10px;
        padding:1px 6px;
        position:absolute;
        top:-8px;
        right:-6px;
    }

    .sb-leads-dropdown{
        position:relative;
        background:#fff;
        border:1px solid #e6e8eb;
        border-radius:8px;
        padding:12px;
        margin:-10px 0 20px;
        max-width:320px;
        display:flex;
        flex-direction:column;
        gap:6px;
    }
    .sb-leads-dropdown-item{
        padding:8px 10px;
        border-radius:6px;
        font-size:13px;
        color:#333;
    }
    .sb-leads-dropdown-item.is-active{ background:#eaf4fe; color:#1e90ff; font-weight:600; }
    .sb-leads-dropdown-custom{ display:flex; gap:6px; margin-top:6px; flex-wrap:wrap; }
    .sb-leads-dropdown-custom .sb-input{ flex:1; min-width:110px; }
    .sb-leads-dropdown-clear{ font-size:12px; color:#d9364f; margin-top:4px; }

    .sb-lead-card{ border:1px solid #eceef0; border-radius:8px; margin-bottom:14px; padding:16px 18px; }
    .sb-lead-badges{ display:flex; gap:8px; margin-bottom:6px; }
    .sb-lead-badge{ font-size:10px; font-weight:700; border-radius:4px; padding:2px 8px; }
    .sb-lead-badge--new{ background:#e6f7ec; color:#22a45d; }
    .sb-lead-badge--hot{ background:#fdecec; color:#d9364f; }

    .sb-lead-row{ display:flex; justify-content:space-between; gap:14px; }
    .sb-lead-info{ flex:1; min-width:0; }
    .sb-lead-star{ border:0; background:transparent; color:#ccc; font-size:13px; margin-right:4px; cursor:pointer; }
    .sb-lead-star.is-starred{ color:#f2b400; }
    .sb-lead-info strong{ font-size:15px; color:#222; }
    .sb-lead-meta{ font-size:12.5px; color:#777; margin:2px 0 0 20px; }
    .sb-lead-price-btn{
        margin:10px 0 8px 0;
        border:1px solid #d5d9dd;
        border-radius:6px;
        background:#fff;
        padding:7px 12px;
        font-size:12px;
        font-weight:600;
        color:#444;
    }
    .sb-lead-price-btn.is-disabled{ color:#bbb; cursor:default; }
    .sb-lead-showdetails, .sb-lead-hidedetails{
        display:block;
        border:0;
        background:transparent;
        color:#1e90ff;
        font-size:12.5px;
        font-weight:600;
        cursor:pointer;
        padding:0;
        margin-top:4px;
    }

    .sb-lead-side{ text-align:right; flex-shrink:0; }
    .sb-lead-side small{ display:block; font-size:11.5px; color:#999; margin-bottom:8px; }
    .sb-lead-contact-btns{ display:flex; gap:8px; justify-content:flex-end; margin-bottom:10px; }
    .sb-lead-contact-btn{
        width:34px;
        height:34px;
        border-radius:50%;
        display:flex;
        align-items:center;
        justify-content:center;
        color:#fff;
        font-size:14px;
    }
    .sb-lead-contact-btn--call{ background:#1e90ff; }
    .sb-lead-contact-btn--whatsapp{ background:#25d366; }
    .sb-lead-contact-btn--email{ background:#666; }
    .sb-lead-hide-btn{
        border:0;
        background:transparent;
        color:#888;
        font-size:11.5px;
        cursor:pointer;
    }

    .sb-lead-details{ margin-top:12px; border-top:1px dashed #eceef0; padding-top:12px; }
    .sb-lead-actions{ display:flex; flex-wrap:wrap; gap:8px; margin-bottom:10px; }
    .sb-lead-action-btn{
        border:1px solid #d5d9dd;
        border-radius:6px;
        background:#fff;
        padding:7px 12px;
        font-size:12px;
        font-weight:600;
        color:#333;
    }
    .sb-lead-action-btn.is-disabled{ color:#bbb; cursor:default; }
    .sb-lead-note-preview, .sb-lead-reminder-preview{
        font-size:12.5px;
        color:#555;
        background:#f7f8f9;
        border-radius:6px;
        padding:8px 12px;
        margin-bottom:8px;
    }
    .sb-lead-detail-block{ margin-bottom:8px; }
    .sb-lead-detail-block span{ display:block; font-size:11px; color:#999; }
    .sb-lead-detail-block strong{ font-size:13px; color:#222; font-weight:600; }

    .sb-lead-share-panel{ background:#f7f8f9; border-radius:8px; padding:12px; margin:10px 0; }
    .sb-lead-share-panel p{ font-size:12.5px; margin-bottom:8px; }
    .sb-lead-share-wa{ color:#25d366; font-weight:700; }
    .sb-lead-share-options{ display:flex; flex-wrap:wrap; gap:8px; }
    .sb-lead-share-opt{
        border:1px solid #d5d9dd;
        border-radius:6px;
        background:#fff;
        padding:7px 12px;
        font-size:12px;
        font-weight:600;
        color:#333;
    }

    .sb-leads-modal h5{ margin-bottom:16px; font-size:16px; font-weight:700; }
    .sb-leads-textarea{
        width:100%;
        min-height:110px;
        border:1px solid #1e90ff;
        border-radius:6px;
        padding:10px;
        font-size:13px;
        margin-bottom:16px;
        resize:vertical;
    }
    .sb-leads-modal-label{ font-size:12.5px; color:#666; margin:14px 0 8px; font-weight:600; }
    .sb-leads-radio-row{ display:flex; flex-wrap:wrap; gap:16px; font-size:13px; color:#333; }
    .sb-leads-date-input{ margin-top:10px; }
    .sb-leads-modal-btns{ display:flex; gap:10px; margin-top:20px; }
    .sb-leads-cancel-btn{
        flex:1;
        border:0;
        background:#9aa1a8;
        color:#fff;
        border-radius:6px;
        padding:11px;
        font-weight:600;
    }
    .sb-leads-modal-btns .sb-wizard-primary-btn{ flex:1; }

    .sb-category-modal{ max-width:480px; text-align:left; }
    .sb-category-list{ max-height:340px; overflow-y:auto; margin:14px 0; border:1px solid #eceef0; border-radius:8px; }
    .sb-category-row{
        display:flex;
        justify-content:space-between;
        align-items:center;
        padding:12px 14px;
        border-bottom:1px solid #f3f3f3;
        font-size:13.5px;
        color:#333;
        cursor:pointer;
    }
    .sb-category-row:last-child{ border-bottom:0; }
    .sb-category-done-btn{ width:100%; }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var csrf = '{{ csrf_token() }}';

    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: body ? JSON.stringify(body) : null,
        }).then(function (r) { return r.json(); });
    }

    // Show/hide details.
    document.querySelectorAll('[data-lead-details]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-lead-details');
            var panel = document.getElementById('sbLeadDetails' + id);
            var willShow = panel.hidden;
            panel.hidden = !willShow;
            if (willShow) {
                var readUrl = btn.getAttribute('data-read-url');
                if (readUrl) post(readUrl);
            }
        });
    });

    // Star toggle.
    document.querySelectorAll('.sb-lead-star').forEach(function (btn) {
        btn.addEventListener('click', function () {
            post(btn.getAttribute('data-star-url')).then(function (data) {
                btn.classList.toggle('is-starred', !!data.starred);
            });
        });
    });

    // Hide/unhide.
    document.querySelectorAll('.sb-lead-hide-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            post(btn.getAttribute('data-hide-url')).then(function () {
                btn.closest('.sb-lead-card').remove();
            });
        });
    });

    // Share panel toggle.
    document.querySelectorAll('.sb-lead-share-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var panel = document.getElementById(btn.getAttribute('data-share-toggle'));
            if (panel) panel.hidden = !panel.hidden;
        });
    });

    // Modal open/close helper.
    function openModal(id) { document.getElementById(id).classList.add('show'); }
    function closeModal(id) { document.getElementById(id).classList.remove('show'); }
    document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
        btn.addEventListener('click', function () { closeModal(btn.getAttribute('data-modal-close')); });
    });

    // Note modal.
    var noteUrl = null;
    document.querySelectorAll('.sb-lead-note-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            noteUrl = btn.getAttribute('data-note-url');
            document.getElementById('sbNoteTextarea').value = btn.getAttribute('data-current-note') || '';
            openModal('sbNoteModalOverlay');
        });
    });
    document.getElementById('sbNoteSaveBtn').addEventListener('click', function () {
        if (!noteUrl) return;
        post(noteUrl, { note: document.getElementById('sbNoteTextarea').value }).then(function () {
            closeModal('sbNoteModalOverlay');
            location.reload();
        });
    });

    // Reminder modal.
    var reminderUrl = null;
    document.querySelectorAll('.sb-lead-reminder-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            reminderUrl = btn.getAttribute('data-reminder-url');
            openModal('sbReminderModalOverlay');
        });
    });
    document.querySelectorAll('input[name="sbReminderDate"]').forEach(function (r) {
        r.addEventListener('change', function () {
            document.getElementById('sbReminderDateInput').hidden = r.value !== 'pick';
        });
    });
    document.querySelectorAll('input[name="sbReminderTime"]').forEach(function (r) {
        r.addEventListener('change', function () {
            document.getElementById('sbReminderTimeInput').hidden = r.value !== 'pick';
        });
    });
    document.getElementById('sbReminderSaveBtn').addEventListener('click', function () {
        if (!reminderUrl) return;

        var dateChoice = document.querySelector('input[name="sbReminderDate"]:checked');
        var timeChoice = document.querySelector('input[name="sbReminderTime"]:checked');
        if (!dateChoice || !timeChoice) return;

        var date;
        if (dateChoice.value === 'today') date = new Date();
        else if (dateChoice.value === 'tomorrow') { date = new Date(); date.setDate(date.getDate() + 1); }
        else date = new Date(document.getElementById('sbReminderDateInput').value);

        var time = timeChoice.value === 'pick' ? document.getElementById('sbReminderTimeInput').value : timeChoice.value;
        var parts = (time || '10:00').split(':');

        var y = date.getFullYear(), m = String(date.getMonth() + 1).padStart(2, '0'), d = String(date.getDate()).padStart(2, '0');
        var reminderAt = y + '-' + m + '-' + d + ' ' + parts[0] + ':' + parts[1] + ':00';

        post(reminderUrl, { reminder_at: reminderAt }).then(function () {
            closeModal('sbReminderModalOverlay');
            location.reload();
        });
    });

    // Category filter modal.
    var catBtn = document.getElementById('sbLeadsCategoryBtn');
    if (catBtn) {
        catBtn.addEventListener('click', function () { openModal('sbCategoryModalOverlay'); });
    }
    var catSearch = document.getElementById('sbCategorySearch');
    if (catSearch) {
        catSearch.addEventListener('input', function () {
            var q = catSearch.value.toLowerCase();
            document.querySelectorAll('.sb-category-row').forEach(function (row) {
                row.style.display = row.textContent.toLowerCase().indexOf(q) > -1 ? '' : 'none';
            });
        });
    }

    // Date / Enquiry Status dropdowns.
    function toggleDropdown(btnId, dropdownId) {
        var btn = document.getElementById(btnId);
        var dropdown = document.getElementById(dropdownId);
        if (!btn || !dropdown) return;
        btn.addEventListener('click', function () {
            var willShow = dropdown.hidden;
            document.querySelectorAll('.sb-leads-dropdown').forEach(function (d) { d.hidden = true; });
            dropdown.hidden = !willShow;
        });
    }
    toggleDropdown('sbLeadsDateBtn', 'sbLeadsDateDropdown');
    toggleDropdown('sbLeadsStatusBtn', 'sbLeadsStatusDropdown');
});
</script>
@endpush
