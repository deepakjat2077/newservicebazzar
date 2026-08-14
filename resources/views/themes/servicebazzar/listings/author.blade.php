@extends($themeView.'layouts.site')
@section('content')
    @push('css')
        <style>
            .start--time, .end--time {
                padding: 1px 10px;
            }
            .btn__schedule{
                cursor: pointer;
                background-color: #ff5252;
                color: #fff;
                display: inline-block;
                font-weight: 400;
                text-align: center;
                white-space: nowrap;
                vertical-align: middle;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
                border: 1px solid transparent;
                padding: .375rem .75rem;
                font-size: 1rem;
                line-height: 1.5;
                border-radius: .25rem;
                transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
            }

            @media (min-width: 992px) {

    #listingColumn {
        height: 100vh;
        overflow-y: auto;
        position: sticky;
        top: 0;

        /* Hide scrollbar */
        scrollbar-width: none;          /* Firefox */
        -ms-overflow-style: none;       /* IE & Edge */
    }

    #listingColumn::-webkit-scrollbar {
        display: none;                  /* Chrome, Safari */
    }
}
        </style>
    @endpush
@section('title',$title)
<section class="author pt-80 pb-80">
	<div class="container">
		<div class="row mb__30 align-items-start">
		  <div class="col-md-8" id="listingColumn">
                <div class="listing-items">

                    <div class="row" id="listingWrapper">
                    
                        @include($themeView.'partials.ajax-listings')
                    </div>

                </div>
            </div>


			<div class="col-md-4">
				<div class="author-area">
					<div class="author-profile">
						<div class="author-thumb">
							<img src="{{ loadImage('assets/images/user/'.$user->image) }}" alt="author">
						</div>
						<div class="author-info">
							<h5 class="mb-0">
                                <a href="javascript:void(0)">{{ __($user->fullname()) }}</a>
                                @if($user->verified_tag)
                                    <img src="{{ asset('assets/images/tag.png') }}" width="15" height="15" alt="@lang('tag')">
                                @endif
                            </h5>
							<p class="mb-0">@lang('Joined at') {{ $user->created_at->format('d F, Y') }}</p>
						</div>
					</div>
					<div class="author-contact">
                        <ul>
                            @if($user->address)
                            <li><i class="las la-map-marker-alt"></i> {{ $user->address }}</li>
                            @endif
                            <li><i class="las la-phone-volume"></i> {{ __($user->mobile) }}</li>
                            <li><i class="las la-envelope"></i> {{ __($user->email) }}</li>
                            @if($user->website)
                            <li><a href="{{ $user->website }}" target="_blank"><i class="fas fa-globe"></i> {{ $user->website }}</a></li>
                            @endif
                        </ul>
					</div>
					<div class="author-social">
						<ul>
							@if(@$user->social->facebook)
							<li><a href="{{ @$user->social->facebook }}" target="_blank"><i class="lab la-facebook"></i></a></li>
							@endif
							@if(@$user->social->linkedin)
							<li><a href="{{ @$user->social->linkedin }}" target="_blank"><i class="lab la-linkedin"></i></a></li>
							@endif
							@if(@$user->social->youtube)
							<li><a href="{{ @$user->social->youtube }}" target="_blank"><i class="lab la-youtube"></i></a></li>
							@endif
							@if(@$user->social->instagram)
							<li><a href="{{ @$user->social->instagram }}" target="_blank"><i class="lab la-instagram"></i></a></li>
							@endif
							@if(@$user->social->twitter)
							<li><a href="{{ @$user->social->twitter }}" target="_blank"><i class="lab la-twitter"></i></a></li>
							@endif
						</ul>
					</div>
				</div>
                <div class="contact-with-author mb-4 d-flex justify-content-center justify-content-between align-items-center">
                    <span class="mb-0">@lang('Daily Time Schedule')</span>
                    <button class="btn__schedule" data-toggle="modal" data-target="#exampleModal">
                        <i class="fas fa-hand-pointer"></i> @lang('Schedule')
                    </button>
                </div>
				<div class="contact-with-author">
					<form action="{{ route('contact.author') }}" method="post">
						@csrf
						<input type="hidden" name="author_id" value="{{ $user->id }}">
						<div class="form-group">
							<input type="text" name="name" placeholder="@lang('Your name')" class="form-control">
						</div>
                        
						<div class="form-group">
							<input type="email" name="email" placeholder="@lang('Your email')" class="form-control">
						</div>
                        <div class="form-group">
							<input type="text" name="subject" placeholder="@lang('subject')" class="form-control">
						</div>
						<div class="form-group">
							<textarea rows="4" name="message" class="form-control" placeholder="@lang('Your message')"></textarea>
						</div>
						<div class="form-group">
							<button class="btn btn__base w-100">@lang('Submit')</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</section>

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ucwords(__($user->fullname())) }} @lang('Daily Time Schedule')</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @php
                        $days = [
                            1 => 'monday',
                            2 => 'tuesday',
                            3 => 'wednesday',
                            4 => 'thursday',
                            5 => 'friday',
                            6 => 'saturday',
                            0 => 'sunday',
                        ];

                        $data = [];
                        foreach ($user?->schedules as $schedule) {
                            $data[$schedule->day][] = [
                                'id' => $schedule->id,
                                'start_time' => $schedule->opening_time,
                                'end_time' => $schedule->closing_time
                            ];
                        }
                    @endphp

                    @foreach($days as $dayId => $dayName)
                        <div class="schedule-item">
                            <span class="btn">{{ ucwords(__('' . $dayName)) }} :</span>
                            <div class="schedult-date-content">
                                @if(!empty($data[$dayId]))
                                    <span class="d-inline-flex align-items-center">
                                        @foreach ($data[$dayId] as $day)
                                            <span class="start--time">
                                                <span class="clock--icon">
                                                    <i class="las la-clock"></i>
                                                </span>
                                                <span class="info">
                                                    <span>@lang('Opening Time')</span>
                                                    {{ date('h:i A', strtotime($day['start_time'])) }}
                                                </span>
                                            </span>
                                            <span class="end--time">
                                                <span class="clock--icon">
                                                    <i class="las la-clock"></i>
                                                </span>
                                                <span class="info">
                                                    <span>@lang('Closing Time')</span>
                                                    {{ date('h:i A', strtotime($day['end_time'])) }}
                                                </span>
                                            </span>
                                        @endforeach
                                    </span>
                                @else
                                    <span class="btn btn-sm btn-outline-danger m-1 disabled">
                                        @lang('Offday')
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-dark" data-dismiss="modal">
                        <i class="far fa-check-circle"></i> @lang('Close')</button>
                </div>
            </div>
        </div>
    </div>

    

@endsection
