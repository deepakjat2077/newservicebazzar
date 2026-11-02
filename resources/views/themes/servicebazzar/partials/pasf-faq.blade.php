{{-- "People also search for" Q&As (LanderSchema::pasfFaq): what people
     search around this page on Google, answered from the listings above.
     Answers are plain text escaped in LanderSchema, with the businesses they
     name linked; the FAQPage schema carries the same text. --}}
@if(!empty($pasfFaq['faqs']))
    <section class="pasf-faq">
        <h2>People in {{ $pasfFaq['city'] }} also search for</h2>
        <ul class="pasf-faq-chips">
            {{-- One chip per answer: its most-searched search. The near-identical
                 ones it also answers ("pet store in jaipur", "pet shop jaipur")
                 would only repeat it. --}}
            @foreach($pasfFaq['faqs'] as $faq)
                @if($faq['keyword'] !== '')
                    <li><a href="#{{ $faq['id'] }}" data-pasf-open="{{ $faq['id'] }}">{{ $faq['keyword'] }}</a></li>
                @endif
            @endforeach
        </ul>
        <div class="pasf-faq-list">
            @foreach($pasfFaq['faqs'] as $faq)
                <details id="{{ $faq['id'] }}" @if($loop->first) open @endif>
                    <summary><h3>{{ $faq['question'] }}</h3></summary>
                    <p>{!! $faq['answer_html'] !!}</p>
                </details>
            @endforeach
        </div>
    </section>
    <script>
        // Clicking a search opens its answer. The others close and the page
        // scrolls to it, so something visibly happens even when the answer
        // was already the open one.
        document.addEventListener('click', function (e) {
            var link = e.target.closest && e.target.closest('[data-pasf-open]');
            if (!link) return;

            var section = link.closest('.pasf-faq');
            var wanted = document.getElementById(link.getAttribute('data-pasf-open'));
            if (!section || !wanted) return;

            e.preventDefault();

            section.querySelectorAll('.pasf-faq-list details').forEach(function (d) {
                d.open = (d === wanted);
            });
            section.querySelectorAll('[data-pasf-open]').forEach(function (a) {
                a.classList.toggle('is-open', a === link);
            });

            wanted.scrollIntoView({ behavior: 'smooth', block: 'center' });
            wanted.classList.remove('just-opened');
            void wanted.offsetWidth;
            wanted.classList.add('just-opened');
        });
    </script>
    <style>
        .pasf-faq { margin: 4px 0 18px; }
        .pasf-faq h2 { font-size: 18px; margin: 0 0 10px; }
        .pasf-faq-chips { list-style: none; padding: 0; margin: 0 0 10px; display: flex; flex-wrap: wrap; gap: 8px; }
        .pasf-faq-chips a { display: inline-block; padding: 6px 12px; background: #f1f5f9; border-radius: 20px; font-size: 13px; color: #334155; text-decoration: none; }
        .pasf-faq-chips a:hover { background: #e2e8f0; }
        .pasf-faq-chips a.is-open { background: var(--primary-color, #2563eb); color: #fff; }
        .pasf-faq-list details.just-opened { animation: pasf-flash 1.1s ease-out; }
        @keyframes pasf-flash {
            0%   { background: #fff7cc; }
            100% { background: transparent; }
        }
        .pasf-faq-list details { border-bottom: 1px solid #eef0f3; padding: 8px 0; scroll-margin-top: 90px; }
        .pasf-faq-list summary { cursor: pointer; list-style: revert; }
        .pasf-faq-list summary h3 { display: inline; font-size: 15px; font-weight: 600; margin: 0; }
        .pasf-faq-list p { margin: 6px 0 0; font-size: 14px; line-height: 1.5; color: #334155; }
    </style>
@endif
