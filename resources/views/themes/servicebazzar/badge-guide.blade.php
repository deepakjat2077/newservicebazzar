@extends($themeView.'layouts.site')

@section('title', 'ServiceBazzar Badges & Verification - ' . ($setting->site_name ?? 'ServiceBazzar'))

@php
    $seoTitle = 'ServiceBazzar Business Badges & Verification';
    $seoDescription = 'Learn what Trusted and Verified badges mean on ServiceBazzar. How we verify businesses and what each badge indicates about a service provider.';
    $seoKeywords = 'verified business, trusted badge, business verification, service provider ratings';
    $seoImage = asset('assets/images/logo/logo.png');
@endphp

@section('content')

<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">

            <h1 class="mb-4">ServiceBazzar Badges & Verification</h1>

            <p class="lead text-muted">
                ServiceBazzar badges indicate that a business has passed our verification process
                and meets our standards for professionalism, authenticity, and customer service quality.
            </p>

            <hr class="my-5">

            {{-- Verified Badge Section --}}
            <div class="badge-section mb-5">
                <div class="badge-header d-flex align-items-center gap-3 mb-3">
                    <img src="{{ asset('assets/images/verified_2x.gif') }}"
                         alt="Verified Business badge"
                         style="width: 48px; height: 48px;">
                    <h2 class="mb-0">Verified Business Badge</h2>
                </div>

                <div class="badge-content">
                    <h3 class="h5">What It Means</h3>
                    <p>
                        A <strong>Verified</strong> badge indicates that ServiceBazzar has independently verified
                        the business's identity and key credentials. This verification includes:
                    </p>

                    <ul class="badge-checklist">
                        <li><strong>Business Registration:</strong> Confirmed with government/municipal records</li>
                        <li><strong>Ownership Verification:</strong> Owner identity matched against official documents</li>
                        <li><strong>Contact Information:</strong> Phone number and address verified as active</li>
                        <li><strong>Business Category:</strong> Service category confirmed to match actual services offered</li>
                        <li><strong>Professional Standards:</strong> Business demonstrates compliance with local regulations</li>
                    </ul>

                    <h3 class="h5 mt-4">Why It Matters</h3>
                    <p>
                        When you contact a Verified business, you can be confident that:
                    </p>
                    <ul>
                        <li>The business is real and legally registered</li>
                        <li>You're connecting with genuine business owners or authorized representatives</li>
                        <li>The service category accurately represents what they offer</li>
                        <li>Their contact information is current and monitored</li>
                    </ul>
                </div>
            </div>

            <hr class="my-5">

            {{-- Trusted Badge Section --}}
            <div class="badge-section mb-5">
                <div class="badge-header d-flex align-items-center gap-3 mb-3">
                    <img src="{{ asset('assets/images/trust_2x.gif') }}"
                         alt="Trusted Business badge"
                         style="width: 48px; height: 48px;">
                    <h2 class="mb-0">Trusted Business Badge</h2>
                </div>

                <div class="badge-content">
                    <h3 class="h5">What It Means</h3>
                    <p>
                        A <strong>Trusted</strong> badge goes beyond standard verification. It indicates that a business has
                        additional credentials, certifications, or a proven track record of customer satisfaction.
                    </p>

                    <h3 class="h5 mt-4">Pathways to Trusted Status</h3>
                    <p>Businesses earn Trusted badges through one or more of:</p>

                    <ul class="badge-checklist">
                        <li><strong>Professional Certifications:</strong> Industry-recognized licenses or certifications (e.g., tax filing, ISO certification, health permits)</li>
                        <li><strong>Customer Reviews:</strong> Consistent high ratings (4.5+ stars) with substantial review volume (20+ reviews)</li>
                        <li><strong>Years in Business:</strong> Demonstrated longevity and stability (typically 2+ years with clean track record)</li>
                        <li><strong>Regulatory Compliance:</strong> Clean compliance record with local business regulators</li>
                        <li><strong>Professional Membership:</strong> Active membership in recognized industry associations</li>
                    </ul>

                    <h3 class="h5 mt-4">Why It Matters</h3>
                    <p>
                        Trusted businesses have demonstrated not just legal compliance, but also:
                    </p>
                    <ul>
                        <li>Commitment to professional standards above minimum requirements</li>
                        <li>Proven customer satisfaction (if based on reviews)</li>
                        <li>Specialized expertise in their field (if based on certifications)</li>
                        <li>Reliability and accountability over time</li>
                    </ul>
                </div>
            </div>

            <hr class="my-5">

            {{-- Badge Hierarchy --}}
            <div class="badge-hierarchy">
                <h2 class="mb-4">Badge Hierarchy</h2>

                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="badge-card text-center">
                            <div class="badge-status-level">1</div>
                            <h4>No Badge</h4>
                            <p class="small text-muted">
                                Standard listing. Basic information provided by business owner.
                                Verification pending or not yet submitted.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="badge-card text-center border-info">
                            <img src="{{ asset('assets/images/verified_2x.gif') }}"
                                 alt="Verified" style="height: 40px; margin-bottom: 8px;">
                            <h4>Verified</h4>
                            <p class="small text-muted">
                                Identity and business details confirmed by ServiceBazzar team.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="badge-card text-center border-success">
                            <div style="display: flex; gap: 8px; justify-content: center; margin-bottom: 8px;">
                                <img src="{{ asset('assets/images/verified_2x.gif') }}"
                                     alt="Verified" style="height: 40px;">
                                <img src="{{ asset('assets/images/trust_2x.gif') }}"
                                     alt="Trusted" style="height: 40px;">
                            </div>
                            <h4>Verified + Trusted</h4>
                            <p class="small text-muted">
                                Premium status with verified credentials AND additional qualifications.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-5">

            {{-- FAQ Section --}}
            <div class="badge-faq">
                <h2 class="mb-4">Frequently Asked Questions</h2>

                <div class="faq-item mb-3">
                    <h4 class="h6 mb-2">
                        <strong>Q: Can I trust a business without a badge?</strong>
                    </h4>
                    <p class="mb-3">
                        Yes. Many legitimate, honest businesses haven't yet completed our verification process.
                        Badges are an additional assurance layer, not a requirement for trustworthiness. Always check
                        customer reviews and ratings, contact them directly, and verify their credentials independently.
                    </p>
                </div>

                <div class="faq-item mb-3">
                    <h4 class="h6 mb-2">
                        <strong>Q: How long does verification take?</strong>
                    </h4>
                    <p class="mb-3">
                        Standard verification typically takes 5-10 business days. Trusted badge qualification
                        may take longer as it depends on review accumulation or certification uploads.
                    </p>
                </div>

                <div class="faq-item mb-3">
                    <h4 class="h6 mb-2">
                        <strong>Q: Can a badge be removed?</strong>
                    </h4>
                    <p class="mb-3">
                        Yes. Badges can be suspended or removed if a business stops meeting our standards—for example,
                        if they violate customer policies, fail compliance checks, or receive consistent poor reviews.
                    </p>
                </div>

                <div class="faq-item mb-3">
                    <h4 class="h6 mb-2">
                        <strong>Q: How does ServiceBazzar verify businesses?</strong>
                    </h4>
                    <p class="mb-3">
                        Our verification process includes document review, phone verification, cross-checking with
                        government databases, and ongoing compliance monitoring. Methods vary by business type and location.
                    </p>
                </div>

                <div class="faq-item mb-3">
                    <h4 class="h6 mb-2">
                        <strong>Q: What if a business has a badge but bad reviews?</strong>
                    </h4>
                    <p class="mb-3">
                        Badges verify identity and credentials, not service quality. Customer reviews rate actual
                        experience. A Verified badge means the business is who they claim; reviews tell you how they perform.
                        Always consider both when making decisions.
                    </p>
                </div>
            </div>

            <hr class="my-5">

            <div class="cta-section text-center py-4 bg-light rounded">
                <h3>Have Questions About a Business?</h3>
                <p class="mb-4">
                    If you notice a business with a badge that doesn't meet our standards, report it to us.
                </p>
                <a href="{{ route('contact') }}" class="btn btn-primary">Report an Issue</a>
            </div>

        </div>
    </div>
</div>

<style>
.badge-section {
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
    border-left: 4px solid #0076d7;
}

.badge-header {
    padding-bottom: 12px;
    border-bottom: 1px solid #ddd;
}

.badge-checklist {
    list-style: none;
    padding: 0;
}

.badge-checklist li {
    padding: 8px 0;
    padding-left: 28px;
    position: relative;
}

.badge-checklist li:before {
    content: "✓";
    position: absolute;
    left: 0;
    color: #28a745;
    font-weight: bold;
}

.badge-card {
    padding: 20px;
    border: 2px solid #ddd;
    border-radius: 8px;
    background: #fff;
    transition: all 0.2s;
}

.badge-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.badge-status-level {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    background: #e9ecef;
    border-radius: 50%;
    font-size: 24px;
    font-weight: bold;
    color: #6c757d;
    margin-bottom: 12px;
}

.faq-item {
    padding: 16px;
    background: #fff;
    border-left: 4px solid #0076d7;
    border-radius: 4px;
}

.faq-item h4 {
    color: #1a1a1a;
    margin-bottom: 8px !important;
}

.cta-section {
    margin-top: 50px !important;
}

@media (max-width: 767px) {
    .badge-card {
        margin-bottom: 12px;
    }
}
</style>

@endsection
