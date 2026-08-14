@extends('admin.layouts.master')
@section('title', 'Listing Details')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row">

                    {{-- Listing Image --}}
                    <div class="col-md-4 mb-3">
                        <img src="{{ loadImage('assets/images/listing/thumb/' . $listing->thumb) }}"
                             alt="Image"
                             class="img-fluid rounded w-100"
                             style="max-height: 300px; object-fit: cover;">
                    </div>

                    {{-- Listing Details --}}
                    <div class="col-md-8">
                        {{-- The Listing Details prose used to print here. It
                             runs to several paragraphs and pushed everything
                             actually being reviewed — status, contact,
                             address — below the fold. It is still on the
                             public page and in the edit form. --}}
                        <h3 class="mb-3">{{ __($listing->name) }}</h3>

                        <table class="table table-bordered listing-data">
                            <tbody>
                                <tr>
                                    <th>@lang('Category')</th>
                                    <td>{{ __($listing->category->name ?? '') }}</td>
                                </tr>

                                {{-- Sub Category was here. A listing carries
                                     dozens of them, so the row ran on for
                                     lines and pushed the rest of the table
                                     down. The edit form is where they are
                                     read and changed. --}}

                                {{-- full_address, not address: the plain
                                     `address` column is empty on most
                                     listings, so this row always read N/A
                                     even though the listing had a complete
                                     address stored across its parts. --}}
                                <tr>
                                    <th>@lang('Address')</th>
                                    <td>{{ $listing->full_address ?: 'N/A' }}</td>
                                </tr>

                                <tr>
                                    <th>@lang('City / Area')</th>
                                    <td>{{ optional($listing->location)->name ?: ($listing->city ?: 'N/A') }}</td>
                                </tr>

                                {{-- Contact.

                                     Both shapes are read: the numbers live in
                                     the `mobile_numbers` / `email` pair the
                                     forms write today, and older listings
                                     still only have the single `mobile` and
                                     `email` columns. Taking whichever is
                                     present means neither generation of row
                                     shows up blank. --}}
                                @php
                                    $mobiles = collect((array) $listing->mobile_numbers)
                                        ->push($listing->mobile)
                                        ->filter()
                                        ->unique();

                                    $emails = collect((array) $listing->extra_emails)
                                        ->push($listing->email)
                                        ->filter()
                                        ->unique();
                                @endphp

                                <tr>
                                    <th>@lang('Mobile')</th>
                                    <td>
                                        @forelse($mobiles as $mobile)
                                            <a href="tel:{{ $mobile }}">{{ $mobile }}</a>@if(!$loop->last), @endif
                                        @empty
                                            <span class="text-muted">N/A</span>
                                        @endforelse
                                    </td>
                                </tr>

                                @if(!empty($listing->landline_numbers))
                                    <tr>
                                        <th>@lang('Landline')</th>
                                        <td>{{ implode(', ', (array) $listing->landline_numbers) }}</td>
                                    </tr>
                                @endif

                                @if(!empty($listing->whatsapp_numbers))
                                    <tr>
                                        <th>@lang('WhatsApp')</th>
                                        <td>{{ implode(', ', (array) $listing->whatsapp_numbers) }}</td>
                                    </tr>
                                @endif

                                <tr>
                                    <th>@lang('Email')</th>
                                    <td>
                                        @forelse($emails as $email)
                                            <a href="mailto:{{ $email }}">{{ $email }}</a>@if(!$loop->last), @endif
                                        @empty
                                            <span class="text-muted">N/A</span>
                                        @endforelse
                                    </td>
                                </tr>

                                @if(!empty($listing->contact_person))
                                    <tr>
                                        <th>@lang('Contact Person')</th>
                                        <td>{{ $listing->contact_person }}</td>
                                    </tr>
                                @endif

                                @if(!empty($listing->website))
                                    <tr>
                                        <th>@lang('Website')</th>
                                        <td>
                                            <a href="{{ $listing->website }}"
                                               target="_blank"
                                               rel="noopener noreferrer">{{ $listing->website }}</a>
                                        </td>
                                    </tr>
                                @endif

                                @if(!empty($listing->pending_address))
                                    {{-- The owner has asked to change the
                                         address and it is waiting on approval.
                                         Only shown when one is pending, so the
                                         table stays short the rest of the
                                         time. --}}
                                    <tr>
                                        <th>@lang('Pending Address')</th>
                                        <td class="text-warning">{{ $listing->pending_address }}</td>
                                    </tr>
                                @endif

                                <tr>
                                    <th>@lang('Status')</th>
                                    <td>
                                        @if($listing->status == 0)
                                            <span class="badge badge-warning">@lang('Pending')</span>
                                        @elseif($listing->status == 1)
                                            <span class="badge badge-success">@lang('Approved')</span>
                                        @elseif($listing->status == 2)
                                            <span class="badge badge-danger">@lang('Rejected')</span>
                                        @endif
                                    </td>
                                </tr>

                                <tr>
                                    <th>@lang('Verified')</th>
                                    <td>
                                        @if($listing->verified == 1)
                                            <span class="badge badge-success">@lang('Verified')</span>
                                        @else
                                            <span class="badge badge-secondary">@lang('Not Verified')</span>
                                        @endif
                                    </td>
                                </tr>

                                <tr>
                                    <th>@lang('Trust')</th>
                                    <td>
                                        @if($listing->trust == 1)
                                            <span class="badge badge-info">@lang('Trusted')</span>
                                        @else
                                            <span class="badge badge-secondary">@lang('Not Trusted')</span>
                                        @endif
                                    </td>
                                </tr>

                                <tr>
                                    <th>@lang('Top Position')</th>
                                    <td>
                                        @if($listing->top_rank == 1)
                                            <span class="badge badge-primary">P - 1</span>
                                        @elseif($listing->top_rank == 2)
                                            <span class="badge badge-info">D - 2</span>
                                        @elseif($listing->top_rank == 3)
                                            <span class="badge badge-warning">G - 3</span>
                                        @elseif($listing->top_rank == 4)
                                            <span class="badge badge-success">S - 4</span>
                                        @else
                                            <span class="badge badge-secondary">Normal Listing</span>
                                        @endif
                                    </td>
                                </tr>

                                @if(!empty($listing->note))
                                <tr>
                                    <th>@lang('Note')</th>
                                    <td>{{ $listing->note }}</td>
                                </tr>
                                @endif

                                {{-- Who added it, and when.

                                     created_by_admin_id is written on create
                                     only, so this stays the admin who put the
                                     listing in rather than whoever touched it
                                     last. It is empty on listings from before
                                     the column existed and on anything a user
                                     submitted themselves — hence the two
                                     fallbacks rather than a blank cell. --}}
                                <tr>
                                    <th>@lang('Added By')</th>
                                    <td>
                                        @if($listing->createdByAdmin)
                                            {{ trim(
                                                $listing->createdByAdmin->first_name.' '.
                                                $listing->createdByAdmin->last_name
                                            ) ?: $listing->createdByAdmin->username }}
                                            <span class="text-muted">({{ $listing->createdByAdmin->username }})</span>
                                        @elseif($listing->user_id)
                                            <span class="text-muted">@lang('Submitted by user')</span>
                                        @else
                                            <span class="text-muted">@lang('Not recorded')</span>
                                        @endif
                                    </td>
                                </tr>

                                <tr>
                                    <th>@lang('Created')</th>
                                    <td>
                                        @if($listing->created_at)
                                            {{ $listing->created_at->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}
                                            <span class="text-muted">({{ $listing->created_at->diffForHumans() }})</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>

                                <tr>
                                    <th>@lang('Last Updated')</th>
                                    <td>
                                        @if($listing->updated_at)
                                            {{ $listing->updated_at->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- The Extra Images gallery and the Features list used to
                         follow here. Both are edited on the listing form and
                         shown on the public page; repeating them on the review
                         screen only made it long, and the extra images meant a
                         page of full-size photo downloads every time an admin
                         opened a listing. --}}

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Modal -->
<div class="modal fade" id="actionModal" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="actionModalLabel">@lang('Action')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="{{ route('admin.listing.action', $listing->id) }}" method="POST">
                @csrf

                <div class="modal-body">

                    <div class="form-group">
                        <label>@lang('Status')</label>
                        <select class="form-control" name="status" id="listingStatus">
                            <option value="0" {{ $listing->status == 0 ? 'selected' : '' }}>@lang('Pending')</option>
                            <option value="1" {{ $listing->status == 1 ? 'selected' : '' }}>@lang('Approve')</option>
                            <option value="2" {{ $listing->status == 2 ? 'selected' : '' }}>@lang('Reject')</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>@lang('Verified')</label>
                        <select class="form-control" name="verified">
                            <option value="0" {{ $listing->verified == 0 ? 'selected' : '' }}>@lang('No')</option>
                            <option value="1" {{ $listing->verified == 1 ? 'selected' : '' }}>@lang('Yes')</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>@lang('Trust')</label>
                        <select class="form-control" name="trust">
                            <option value="0" {{ $listing->trust == 0 ? 'selected' : '' }}>@lang('No')</option>
                            <option value="1" {{ $listing->trust == 1 ? 'selected' : '' }}>@lang('Yes')</option>
                        </select>
                    </div>
                    <div class="form-group">
    <label>@lang('Star Listing (Paid)')</label>
    <select class="form-control" name="is_star">
        <option value="0" {{ $listing->is_star == 0 ? 'selected' : '' }}>@lang('No')</option>
        <option value="1" {{ $listing->is_star == 1 ? 'selected' : '' }}>@lang('Yes')</option>
    </select>
</div>

                    <div class="form-group" id="topRankWrapper">
                        <label>@lang('Top Position')</label>
                        <select class="form-control" name="top_rank">
                            <option value="">Normal Listing</option>
                            <option value="1" {{ $listing->top_rank == 1 ? 'selected' : '' }}>P - 1</option>
                            <option value="2" {{ $listing->top_rank == 2 ? 'selected' : '' }}>D - 2</option>
                            <option value="3" {{ $listing->top_rank == 3 ? 'selected' : '' }}>G - 3</option>
                            <option value="4" {{ $listing->top_rank == 4 ? 'selected' : '' }}>S - 4</option>
                        </select>
                        <small class="text-muted">Only approved listing should get top position.</small>
                    </div>

                    <div class="form-group">
                        <label>@lang('Note to publisher')</label>
                        <textarea class="form-control" name="note" rows="5" required>{{ $listing->note }}</textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
                    <button type="submit" class="btn btn-primary">@lang('Submit')</button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection

@push('top-area')
<button class="btn btn-primary" data-toggle="modal" data-target="#actionModal">
    @lang('Action')
</button>
@endpush

@push('css')
<style>
    .listing-data th {
        width: 180px;
        background: #f8f9fa;
    }
    .listing-data td,
    .listing-data th {
        vertical-align: middle;
    }
</style>
@endpush

@push('script')
<script>
    $(document).ready(function () {
        function toggleTopRank() {
            let status = $('#listingStatus').val();

            if (status == '1') {
                $('#topRankWrapper').show();
            } else {
                $('#topRankWrapper').hide();
                $('#topRankWrapper select').val('');
            }
        }

        toggleTopRank();
        $('#listingStatus').on('change', function () {
            toggleTopRank();
        });
    });
</script>
@endpush