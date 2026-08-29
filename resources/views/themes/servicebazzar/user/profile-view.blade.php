@extends($themeView.'layouts.site')

@section('content')

<section class="pt-60 pb-80">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">

                {{-- User Profile Card --}}
                <div style="background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center;">

                    <div style="margin-bottom: 20px;">
                        <img src="{{ asset('assets/images/user-avatar.png') }}" alt="{{ $user->first_name }}" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover;">
                    </div>

                    <h2 style="margin: 0 0 8px; color: #111;">
                        {{ $user->first_name }} {{ $user->last_name }}
                    </h2>

                    <p style="color: #666; margin: 0 0 20px; font-size: 14px;">
                        Member since {{ $user->created_at->format('M Y') }}
                    </p>

                    @if(auth()->check() && auth()->id() === $user->id)
                        <a href="{{ route('user.profile') }}" style="display: inline-block; padding: 10px 24px; background: var(--primary-color, #2563eb); color: #fff; text-decoration: none; border-radius: 6px; font-weight: 500;">
                            ✏️ Edit Profile
                        </a>
                    @endif

                </div>

            </div>
        </div>
    </div>
</section>

@endsection
