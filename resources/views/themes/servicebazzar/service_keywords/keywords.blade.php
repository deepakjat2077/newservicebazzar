@extends($themeView.'layouts.site')

@section('title', $keywordCategory->name.' Keywords')

@section('content')




<section class="blog-details pt-80 pb-80">
    <div class="container">

        <div class="row mb-4">
            <div class="col-md-12">

                <h2>{{ $keywordCategory->name }}</h2>

                <p>
                    Browse all keywords related to {{ $keywordCategory->name }}.
                </p>

            </div>
        </div>
        <div class="row mb__30">
            @forelse($keywords as $keyword)
            <div class="col-md-4">
                <div class="blog-area">
                    <div class="content-area">
<h5>
        <a href="{{ route('service.keywords.details', [$keywordCategory->slug, $keyword->slug]) }}"
        class="d-flex align-items-center">
        <i class="fas fa-chevron-right me-2"></i>
        <span>{{ $keyword->keyword }}</span>
        </a>
</h5>
                       
                      
                    </div>

                </div>
            </div>

            @empty

            <div class="col-md-12">
                <div class="text-center">
                    <h5>No Keywords Found</h5>
                </div>
            </div>

            @endforelse

            <div class="mt-2">
                {{ $keywords->links() }}
            </div>

        </div>

    </div>
</section>

<style>
    h5{
        font-size: 18px;
        color: #000;
    }
.blog-area .thumb{
    width:100%;
    height:220px;
    overflow:hidden;
    border-radius:8px;
}

.blog-area .thumb img{
    width:100%;
    height:100%;
    object-fit:cover;
}
</style>

@endsection