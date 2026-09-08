{{-- Default FAQ section for category/service pages. If a category or service
     has custom FAQs in the database, a different template renders those. This
     fallback provides E-A-T signals for thin/new pages via generic
     question/answer pairs relevant to the category/service breadth. --}}

<section class="faq-section mt-5 pt-4 border-top">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">

                <h2 class="faq-title mb-4">
                    @if(!empty($categoryName))
                        Frequently Asked Questions About {{ $categoryName }}
                    @else
                        Frequently Asked Questions
                    @endif
                </h2>

                <div class="faq-items">

                    {{-- FAQ Item 1: "What is X?" (category introduction) --}}
                    <div class="faq-item mb-3">
                        <div class="faq-question" role="button" tabindex="0"
                             aria-expanded="false" aria-controls="faq-answer-1"
                             onclick="toggleFaqItem(this)">
                            <span class="faq-icon">+</span>
                            @if(!empty($categoryName))
                                What is {{ $categoryName }}?
                            @else
                                What services are available in this category?
                            @endif
                        </div>
                        <div class="faq-answer" id="faq-answer-1" hidden>
                            @if(!empty($categoryDescription))
                                {{ $categoryDescription }}
                            @else
                                {{ $categoryName ?? 'This category' }} includes a range of professional
                                services to meet your needs. Browse our verified service providers to find
                                the right match for your requirements.
                            @endif
                        </div>
                    </div>

                    {{-- FAQ Item 2: "How do I find the best provider?" --}}
                    <div class="faq-item mb-3">
                        <div class="faq-question" role="button" tabindex="0"
                             aria-expanded="false" aria-controls="faq-answer-2"
                             onclick="toggleFaqItem(this)">
                            <span class="faq-icon">+</span>
                            How do I find the best {{ $categoryName ?? 'service provider' }}?
                        </div>
                        <div class="faq-answer" id="faq-answer-2" hidden>
                            Look for verified providers with high ratings and authentic customer reviews.
                            Check their business profile, certifications, and years of experience. Contact
                            multiple providers to compare pricing and services before making your decision.
                        </div>
                    </div>

                    {{-- FAQ Item 3: "Are these providers verified?" --}}
                    <div class="faq-item mb-3">
                        <div class="faq-question" role="button" tabindex="0"
                             aria-expanded="false" aria-controls="faq-answer-3"
                             onclick="toggleFaqItem(this)">
                            <span class="faq-icon">+</span>
                            Are these providers verified?
                        </div>
                        <div class="faq-answer" id="faq-answer-3" hidden>
                            Service Bazzar verifies business credentials and ownership through a multi-step
                            process. A blue "Verified" badge indicates a provider has passed our verification
                            checks. A "Trusted" badge shows additional credentials or certifications.
                        </div>
                    </div>

                    {{-- FAQ Item 4: "How do ratings work?" --}}
                    <div class="faq-item mb-3">
                        <div class="faq-question" role="button" tabindex="0"
                             aria-expanded="false" aria-controls="faq-answer-4"
                             onclick="toggleFaqItem(this)">
                            <span class="faq-icon">+</span>
                            How do ratings and reviews work?
                        </div>
                        <div class="faq-answer" id="faq-answer-4" hidden>
                            Ratings are based on real customer experiences. Only verified customers who have
                            received the service can leave a review. Ratings help you understand service
                            quality, reliability, and customer satisfaction before you make contact.
                        </div>
                    </div>

                    {{-- FAQ Item 5: "Is Service Bazzar free?" --}}
                    <div class="faq-item mb-3">
                        <div class="faq-question" role="button" tabindex="0"
                             aria-expanded="false" aria-controls="faq-answer-5"
                             onclick="toggleFaqItem(this)">
                            <span class="faq-icon">+</span>
                            Is Service Bazzar free to use?
                        </div>
                        <div class="faq-answer" id="faq-answer-5" hidden>
                            Yes. Searching, viewing provider profiles, and reading reviews is completely free.
                            Pricing and service fees are determined directly between you and the service provider.
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</section>

<style>
.faq-section {
    background: #f9f9f9;
    padding: 40px 0;
    margin-top: 50px;
}

.faq-title {
    font-size: 28px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 30px;
}

.faq-items {
    max-width: 800px;
    margin: 0 auto;
}

.faq-item {
    border: 1px solid #ddd;
    border-radius: 6px;
    overflow: hidden;
    background: #fff;
    margin-bottom: 12px;
}

.faq-question {
    padding: 16px 20px;
    background: #fafafa;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 600;
    color: #333;
    transition: background 0.2s;
    user-select: none;
}

.faq-question:hover {
    background: #f0f0f0;
}

.faq-question[aria-expanded="true"] {
    background: #e8f5e9;
}

.faq-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    background: #0076d7;
    color: #fff;
    border-radius: 50%;
    font-size: 16px;
    flex-shrink: 0;
    transition: transform 0.2s;
}

.faq-question[aria-expanded="true"] .faq-icon {
    transform: rotate(45deg);
}

.faq-answer {
    padding: 20px;
    color: #555;
    line-height: 1.6;
    font-size: 15px;
}

@media (max-width: 767px) {
    .faq-section {
        padding: 30px 0;
    }
    .faq-title {
        font-size: 22px;
        margin-bottom: 20px;
    }
    .faq-question {
        padding: 14px 16px;
        font-size: 15px;
    }
    .faq-answer {
        padding: 16px;
        font-size: 14px;
    }
}
</style>

<script>
function toggleFaqItem(el) {
    const isExpanded = el.getAttribute('aria-expanded') === 'true';
    el.setAttribute('aria-expanded', !isExpanded);
    const answer = document.getElementById(el.getAttribute('aria-controls'));
    if (answer) {
        answer.toggleAttribute('hidden');
    }
}

// Allow keyboard navigation
document.querySelectorAll('.faq-question').forEach(item => {
    item.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            item.click();
        }
    });
});
</script>
