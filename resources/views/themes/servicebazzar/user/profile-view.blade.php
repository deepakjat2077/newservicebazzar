@extends($themeView.'layouts.site')

@section('content')

<section class="pt-30 pb-80">
    <div class="container">
        <div class="row">
            {{-- Left Column: Profile Card --}}
            <div class="col-md-4">
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center; position: sticky; top: 20px;">

                    {{-- Cover Photo --}}
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 120px; border-radius: 8px 8px 0 0; margin: -20px -20px 0; position: relative;">
                    </div>

                    {{-- Avatar --}}
                    <div style="margin: -50px 0 12px; position: relative;">
                        <img src="{{ $user->image ? loadImage('assets/images/user/'.$user->image) : asset('assets/images/149071.png') }}" alt="{{ $user->first_name }}" style="width: 100px; height: 100px; border-radius: 50%; border: 4px solid #fff; object-fit: cover;">
                    </div>

                    <h4 style="margin: 0 0 4px; color: #111;">{{ $user->first_name }} {{ $user->last_name }}</h4>
                    <p style="color: #666; margin: 0 0 16px; font-size: 13px;">{{ $user->created_at->diffForHumans() }}</p>

                    @if(auth()->check() && auth()->id() === $user->id)
                        <a href="{{ route('user.profile') }}" style="display: block; width: 100%; padding: 10px; border: 1px solid #ddd; background: #fff; text-decoration: none; color: #333; border-radius: 6px; font-weight: 500;">
                            Edit Profile
                        </a>
                    @endif

                </div>
            </div>

            {{-- Right Column: Reviews & Search --}}
            <div class="col-md-8">

                {{-- Recent Search --}}
                @php
                    $recentListings = App\Models\Listing::where('status', 1)
                        ->latest()
                        ->take(3)
                        ->get();
                @endphp

                @if($recentListings->count() > 0)
                <div style="background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                    <h5 style="margin: 0 0 16px; font-size: 14px; font-weight: 600;">Recent Search</h5>

                    @foreach($recentListings as $listing)
                    <div style="padding: 12px 0; border-bottom: 1px solid #eee;">
                        <a href="{{ route('listings.details', ['category' => $listing->category->slug, 'slug' => $listing->slug]) }}" style="color: #333; text-decoration: none; font-weight: 500; display: block; margin-bottom: 4px;">
                            {{ $listing->name }}
                        </a>
                        <p style="color: #999; margin: 0; font-size: 13px;">{{ $listing->city }}, {{ $listing->state }}</p>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Reviews Given --}}
                @php
                    $userReviews = App\Models\Rating::whereNotNull('user_id')
                        ->where('is_verified', 1)
                        ->latest()
                        ->take(5)
                        ->get();
                @endphp

                @if($userReviews->count() > 0)
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                    <h5 style="margin: 0 0 16px; font-size: 14px; font-weight: 600;">Reviews Given</h5>

                    @foreach($userReviews as $review)
                    <div style="padding: 16px 0; border-bottom: 1px solid #eee;">
                        <div style="display: flex; gap: 12px; margin-bottom: 8px;">
                            <div style="flex: 1;">
                                <p style="margin: 0; font-weight: 500; color: #111; font-size: 14px;">
                                    ⭐ {{ $review->ratings }}/5 Rating
                                </p>
                                <small style="color: #999;">{{ $review->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        @if($review->review)
                        <p style="margin: 8px 0 0; color: #555; font-size: 13px; line-height: 1.5;">{{ Str::limit($review->review, 150) }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

            </div>
        </div>
    </div>
</section>

@endsection
