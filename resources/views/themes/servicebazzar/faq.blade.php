@extends($themeView.'layouts.site')
@section('content')
@section('title',$title)

<!-- Faq Section -->
    <section class="faq pt-80 pb-80">
        <div class="container">
            <h1 class="sr-only">{{ $faq_section?->content?->title ?? 'Frequently Asked Questions' }}</h1>
            <div class="row justify-content-center">
                <div class="col-xl-8 col-lg-8 text-center">
                    <div class="section-title">
                        <h2 class="h4">{{ __($faq_section?->content?->title) }}</h2>
                        <p>{{ __($faq_section?->content?->sub_title) }}</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12 col-lg-12">
                    <div class="accordion" id="accordionExample">
                        <div class="row">
                            <div class="col-xl-6 col-lg-6">
                                @php
                                    $faqsArray = $faqs->toArray();
                                    $faqsCount = count($faqsArray);
                                    $halfCount = ceil($faqsCount / 2);
                                    $firstHalf = array_slice($faqsArray, 0, $halfCount);
                                @endphp

                                @foreach($firstHalf as $index => $faq)
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">
                                                <button class="btn btn-link collapsed" type="button" data-toggle="collapse"
                                                        data-target="#collapse{{ $index }}" aria-expanded="false" aria-controls="collapse{{ $index }}">
                                                    {{ $faq['content']->question }}
                                                </button>
                                            </h5>
                                        </div>

                                        <div id="collapse{{ $index }}" class="collapse" data-parent="#accordionExample">
                                            <div class="card-body">
                                                {{ $faq['content']->answer }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="col-xl-6 col-lg-6">
                                @php
                                    $faqsArray = $faqs->toArray();
                                    $faqsCount = count($faqsArray);
                                    $halfCount = ceil($faqsCount / 2);
                                    $secondHalf = array_slice($faqsArray, $halfCount);
                                @endphp

                                @foreach($secondHalf as $index => $faq)
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">
                                                <button class="btn btn-link collapsed" type="button" data-toggle="collapse"
                                                        data-target="#collapse{{ $index + $halfCount }}" aria-expanded="false" aria-controls="collapse{{ $index + $halfCount }}">
                                                    {{ $faq['content']->question }}
                                                </button>
                                            </h5>
                                        </div>

                                        <div id="collapse{{ $index + $halfCount }}" class="collapse" data-parent="#accordionExample">
                                            <div class="card-body">
                                                {{ $faq['content']->answer }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </section>
    <!-- Faq Section End -->

@endsection