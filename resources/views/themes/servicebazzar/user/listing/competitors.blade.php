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
<style>
    .sb-biz-header{ display:flex; align-items:center; gap:14px; padding-bottom:18px; margin-bottom:16px; border-bottom:1px solid #eceef0; }
    .sb-biz-back{ color:#444; font-size:16px; }
    .sb-biz-header-title{ flex:1; }
    .sb-biz-header-title h4{ margin:0; font-size:18px; font-weight:700; color:#222; }
    .sb-biz-header-title span{ font-size:12px; color:#888; }
    .sb-wizard-empty{ font-size:13px; color:#888; }
    .sb-comp-intro{ margin-bottom:20px; }
    .sb-an-sub{ font-size:12px; color:#888; }

    .sb-comp-cards{
        display:grid;
        grid-template-columns:repeat(4,1fr);
        gap:14px;
        margin-bottom:24px;
    }
    @media (max-width:700px){ .sb-comp-cards{ grid-template-columns:repeat(2,1fr); } }
    .sb-comp-card{
        border:1px solid #eceef0;
        border-radius:10px;
        padding:16px;
    }
    .sb-comp-card-label{ display:block; font-size:11.5px; color:#888; margin-bottom:6px; }
    .sb-comp-card strong{ display:block; font-size:22px; font-weight:700; color:#222; }
    .sb-comp-card-vs{ display:block; font-size:11px; color:#999; margin-top:4px; }

    .sb-an-card{ border:1px solid #eceef0; border-radius:10px; padding:20px; margin-bottom:18px; }
    .sb-an-card h5{ font-size:15px; font-weight:700; color:#222; margin:0 0 3px; }

    .sb-rank-head{
        display:grid;
        grid-template-columns:60px 1fr 80px;
        font-size:11px;
        color:#999;
        font-weight:700;
        text-transform:uppercase;
        padding:10px 0;
        border-bottom:1px solid #eceef0;
    }
    .sb-rank-row{
        display:grid;
        grid-template-columns:60px 1fr 80px;
        align-items:center;
        padding:12px 0;
        border-bottom:1px solid #f3f3f3;
        font-size:13.5px;
        color:#333;
    }
    .sb-rank-row.is-self{ background:#eaf4fe; font-weight:700; border-radius:6px; padding-left:6px; }
    .sb-rank-num{ font-weight:700; color:#888; }
    .sb-rank-row.is-self .sb-rank-num{ color:#1e90ff; }
    .sb-rank-leads{ font-weight:700; }
    .sb-rank-congrats{
        background:#e6f7ec;
        color:#22a45d;
        border-radius:8px;
        padding:12px 16px;
        font-size:13px;
        text-align:center;
        margin:10px 0;
    }

    .sb-an-note{ font-size:12px; color:#888; background:#f7f8f9; border-radius:8px; padding:12px 14px; line-height:1.6; }
    .sb-an-note i{ color:#1e90ff; margin-right:4px; }
</style>
@endpush
