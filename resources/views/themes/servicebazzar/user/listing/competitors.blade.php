@extends($themeView.'layouts.app')
@section('content')
@section('title', 'Competitors — '.$listing->name)

<section class="pt-30 pb-80">
    <div class="container">
        <div class="sb-biz-header">
            <a href="{{ route('user.listing.dashboard', $listing->id) }}" class="sb-biz-back"><i class="fas fa-arrow-left"></i></a>
            <div class="sb-biz-header-title">
                <h4>{{ $listing->name }}</h4>
                <span>{{ optional($listing->location)->name ?? $listing->area }}</span>
            </div>
        </div>

        @if($ranking->count() < 2)
            <p class="sb-wizard-empty">
                There's no one else in {{ optional($listing->category)->name ?? 'your category' }}
                in {{ optional($listing->location)->name ?? 'your area' }} yet on Service Bazzar
                to compare against.
            </p>
        @else
            <p class="sb-an-sub sb-comp-intro">
                Compared against the {{ $ranking->count() - 1 }} other {{ Str::plural('business', $ranking->count() - 1) }}
                listed under {{ optional($listing->category)->name ?? 'your category' }} in
                {{ optional($listing->location)->name ?? 'your area' }}.
            </p>

            {{-- Performance comparison --}}
            <div class="sb-comp-cards">
                <div class="sb-comp-card">
                    <span class="sb-comp-card-label">Lead Response Rate</span>
                    <strong>{{ $self['response_percent'] ?? '—' }}{{ $self['response_percent'] !== null ? '%' : '' }}</strong>
                    <span class="sb-comp-card-vs">Competition avg: {{ $competitionAverages['response_percent'] !== null ? round($competitionAverages['response_percent']).'%' : '—' }}</span>
                </div>
                <div class="sb-comp-card">
                    <span class="sb-comp-card-label">Average Rating</span>
                    <strong>{{ $self['avg_rating'] > 0 ? $self['avg_rating'] : '—' }}</strong>
                    <span class="sb-comp-card-vs">Competition avg: {{ $competitionAverages['avg_rating'] ? round($competitionAverages['avg_rating'], 1) : '—' }}</span>
                </div>
                <div class="sb-comp-card">
                    <span class="sb-comp-card-label">Reviews</span>
                    <strong>{{ $self['review_count'] }}</strong>
                    <span class="sb-comp-card-vs">Competition avg: {{ round($competitionAverages['review_count']) }}</span>
                </div>
                <div class="sb-comp-card">
                    <span class="sb-comp-card-label">Photos</span>
                    <strong>{{ $self['photo_count'] }}</strong>
                    <span class="sb-comp-card-vs">Competition avg: {{ round($competitionAverages['photo_count']) }}</span>
                </div>
            </div>

            {{-- Business ranking --}}
            <div class="sb-an-card">
                <h5>Business Ranking</h5>
                <p class="sb-an-sub">Ranked by total enquiries received on Service Bazzar</p>

                <div class="sb-rank-head">
                    <span>Rank</span><span>Business Name</span><span>Leads</span>
                </div>

                @foreach($ranking as $row)
                    <div class="sb-rank-row {{ $row['is_self'] ? 'is-self' : '' }}">
                        <span class="sb-rank-num">{{ $row['rank'] }}</span>
                        <span class="sb-rank-name">{{ $row['name'] }}{{ $row['is_self'] ? ' (You)' : '' }}</span>
                        <span class="sb-rank-leads">{{ $row['leads'] }}</span>
                    </div>

                    @if($row['is_self'] && $row['rank'] === 1)
                        <div class="sb-rank-congrats">
                            🏆 <strong>Congratulations!</strong> You're currently ranked #1 by leads in
                            {{ optional($listing->location)->name ?? 'your area' }}.
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        <p class="sb-an-note">
            <i class="fas fa-circle-info"></i>
            This ranks businesses by leads, ratings, reviews and photos already tracked on
            Service Bazzar — there's no search-volume trend or call-answer data here, and no
            profile-scoring formula, because this site doesn't have that data.
        </p>
    </div>
</section>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('assets/servicebazzar/css/business-panel.css') }}">
@endpush
