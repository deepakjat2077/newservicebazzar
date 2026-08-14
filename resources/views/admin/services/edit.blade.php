@extends('admin.layouts.master')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.services.update', $service->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @php
            $menuItems = old('menu_items', $service->menu_items ?? []);
            $cards = old('cards', $service->cards ?? []);
            $features = old('features', $service->features ?? []);
            $processSteps = old('process_steps', $service->process_steps ?? []);
            $faqs = old('faqs', $service->faqs ?? []);

            if (!is_array($menuItems) || count($menuItems) == 0) $menuItems = [['title' => '']];
            if (!is_array($cards) || count($cards) == 0) $cards = [['title' => '', 'description' => '', 'image' => '']];
            if (!is_array($features) || count($features) == 0) $features = [['title' => '', 'description' => '']];
            if (!is_array($processSteps) || count($processSteps) == 0) $processSteps = [['number' => '', 'title' => '']];
            if (!is_array($faqs) || count($faqs) == 0) $faqs = [['question' => '', 'answer' => '']];
        @endphp

        <div class="row">

            {{-- TOP SECTION --}}
            <div class="col-md-8">

                {{-- BASIC INFO --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">Basic Info</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Service Title</label>
                                <input type="text" name="title" class="form-control" value="{{ old('title', $service->title) }}" placeholder="Enter service title">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Slug</label>
                                <input type="text" name="slug" class="form-control" value="{{ old('slug', $service->slug) }}" placeholder="enter-service-slug">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Menu Title</label>
                                <input type="text" name="menu_title" class="form-control" value="{{ old('menu_title', $service->menu_title) }}" placeholder="Enter menu title">
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Short Description</label>
                                <textarea name="short_description" rows="3" class="form-control" placeholder="Enter short description">{{ old('short_description', $service->short_description) }}</textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Full Description</label>
                                <textarea name="description" rows="5" class="form-control" placeholder="Enter full description">{{ old('description', $service->description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT SIDEBAR --}}
            <div class="col-md-4">
                <div class="card mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header">
                        <h4 class="mb-0">Publish Settings</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Show in Menu</label>
                            <select name="show_in_menu" class="form-control">
                                <option value="1" {{ old('show_in_menu', $service->show_in_menu) == '1' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old('show_in_menu', $service->show_in_menu) == '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="1" {{ old('status', $service->status) == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status', $service->status) == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success w-100">Update Service Page</button>
                    </div>
                </div>
            </div>

            {{-- HERO --}}
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">Hero Section</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Hero Heading</label>
                                <input type="text" name="hero_title" class="form-control" value="{{ old('hero_title', $service->hero_title) }}" placeholder="Enter hero heading">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Hero Background Image</label>
                                <input type="file" name="hero_image" class="form-control image-input" data-preview="#hero_image_preview">
                                
                                <div class="mt-2">
                                    <img id="hero_image_preview"
                                         src="{{ !empty($service->hero_image) ? asset($service->hero_image) : '' }}"
                                         style="max-width: 180px; {{ !empty($service->hero_image) ? '' : 'display:none;' }} border:1px solid #ddd; padding:4px; border-radius:6px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- HERO KE BAAD SAB --}}
            <div class="col-12">

                {{-- LEFT SIDE MENU ITEMS --}}
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Left Side Menu Items</h4>
                        <button type="button" class="btn btn-primary btn-sm" onclick="addMenuItem()">+ Add Menu Item</button>
                    </div>
                    <div class="card-body">
                        <div id="menu-items-wrapper">
                            @foreach($menuItems as $i => $item)
                                <div class="repeater-item menu-item border rounded p-3 mb-3 position-relative">
                                    <button type="button" class="btn btn-danger btn-sm remove-item" style="position:absolute; top:10px; right:10px;">Remove</button>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label class="form-label">Menu Item Title</label>
                                            <input type="text" name="menu_items[{{ $i }}][title]" class="form-control" value="{{ $item['title'] ?? '' }}" placeholder="Enter menu item title">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- INTRO SECTION --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">Intro Section</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Intro Image</label>
                                <input type="file" name="intro_image" class="form-control image-input" data-preview="#intro_image_preview">

                                <div class="mt-2">
                                    <img id="intro_image_preview"
                                         src="{{ !empty($service->intro_image) ? asset($service->intro_image) : '' }}"
                                         style="max-width: 180px; {{ !empty($service->intro_image) ? '' : 'display:none;' }} border:1px solid #ddd; padding:4px; border-radius:6px;">
                                </div>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Intro Content</label>
                                <textarea name="intro_content" rows="8" class="form-control" placeholder="Enter intro content">{{ old('intro_content', $service->intro_content) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TRUSTED --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">Trusted / Performance Section</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Section Title</label>
                                <input type="text" name="trusted_title" class="form-control" value="{{ old('trusted_title', $service->trusted_title) }}" placeholder="Enter section title">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Section Image</label>
                                <input type="file" name="trusted_image" class="form-control image-input" data-preview="#trusted_image_preview">

                                <div class="mt-2">
                                    <img id="trusted_image_preview"
                                         src="{{ !empty($service->trusted_image) ? asset($service->trusted_image) : '' }}"
                                         style="max-width: 180px; {{ !empty($service->trusted_image) ? '' : 'display:none;' }} border:1px solid #ddd; padding:4px; border-radius:6px;">
                                </div>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Section Description</label>
                                <textarea name="trusted_description" rows="5" class="form-control" placeholder="Enter section description">{{ old('trusted_description', $service->trusted_description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SERVICES CARDS --}}
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Services Cards</h4>
                        <button type="button" class="btn btn-primary btn-sm" onclick="addCard()">+ Add Card</button>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Section Heading</label>
                            <input type="text" name="services_section_title" class="form-control" value="{{ old('services_section_title', $service->services_section_title) }}" placeholder="Enter section heading">
                        </div>

                      <div id="cards-wrapper">
    @foreach($cards as $i => $card)
        <div class="repeater-item card-item border rounded p-3 mb-3 position-relative">
            <button type="button" class="btn btn-danger btn-sm remove-item" style="position:absolute; top:10px; right:10px;">Remove</button>
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="form-label">Card Image</label>
                    <input type="file"
                           name="cards[{{ $i }}][image]"
                           class="form-control image-input"
                           data-preview="#card_preview_{{ $i }}">

                    <input type="hidden" name="cards[{{ $i }}][old_image]" value="{{ $card['image'] ?? '' }}">

                    <div class="mt-2">
                        <img id="card_preview_{{ $i }}"
                             src="{{ !empty($card['image']) ? asset($card['image']) : '' }}"
                             style="max-width: 140px; {{ !empty($card['image']) ? '' : 'display:none;' }} border:1px solid #ddd; padding:4px; border-radius:6px;">
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label">Card Title</label>
                    <input type="text" name="cards[{{ $i }}][title]" class="form-control" value="{{ $card['title'] ?? '' }}" placeholder="Enter card title">
                </div>
                <div class="col-md-5 mb-2">
                    <label class="form-label">Card Description</label>
                    <textarea name="cards[{{ $i }}][description]" class="form-control" rows="3" placeholder="Enter card description">{{ $card['description'] ?? '' }}</textarea>
                </div>
            </div>
        </div>
    @endforeach
</div>
                    </div>
                </div>

                {{-- SEO --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">SEO / Visibility Section</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Section Title</label>
                                <input type="text" name="seo_title" class="form-control" value="{{ old('seo_title', $service->seo_title) }}" placeholder="Enter SEO section title">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Section Image</label>
                                <input type="file" name="seo_image" class="form-control image-input" data-preview="#seo_image_preview">

                                <div class="mt-2">
                                    <img id="seo_image_preview"
                                         src="{{ !empty($service->seo_image) ? asset($service->seo_image) : '' }}"
                                         style="max-width: 180px; {{ !empty($service->seo_image) ? '' : 'display:none;' }} border:1px solid #ddd; padding:4px; border-radius:6px;">
                                </div>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Section Description</label>
                                <textarea name="seo_description" rows="5" class="form-control" placeholder="Enter SEO section description">{{ old('seo_description', $service->seo_description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- WHY CHOOSE US --}}
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Why Choose Us</h4>
                        <button type="button" class="btn btn-primary btn-sm" onclick="addFeature()">+ Add Point</button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Section Title</label>
                                <input type="text" name="why_choose_title" class="form-control" value="{{ old('why_choose_title', $service->why_choose_title) }}" placeholder="Enter section title">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Right Image</label>
                                <input type="file" name="why_choose_image" class="form-control image-input" data-preview="#why_choose_image_preview">

                                <div class="mt-2">
                                    <img id="why_choose_image_preview"
                                         src="{{ !empty($service->why_choose_image) ? asset($service->why_choose_image) : '' }}"
                                         style="max-width: 180px; {{ !empty($service->why_choose_image) ? '' : 'display:none;' }} border:1px solid #ddd; padding:4px; border-radius:6px;">
                                </div>
                            </div>
                        </div>

                        <div id="features-wrapper">
                            @foreach($features as $i => $feature)
                                <div class="repeater-item feature-item border rounded p-3 mb-3 position-relative">
                                    <button type="button" class="btn btn-danger btn-sm remove-item" style="position:absolute; top:10px; right:10px;">Remove</button>
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <label class="form-label">Point Title</label>
                                            <input type="text" name="features[{{ $i }}][title]" class="form-control" value="{{ $feature['title'] ?? '' }}" placeholder="Enter point title">
                                        </div>
                                        <div class="col-md-8 mb-2">
                                            <label class="form-label">Point Description</label>
                                            <textarea name="features[{{ $i }}][description]" class="form-control" rows="3" placeholder="Enter point description">{{ $feature['description'] ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- PROCESS --}}
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Process Steps</h4>
                        <button type="button" class="btn btn-primary btn-sm" onclick="addProcess()">+ Add Step</button>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Section Heading</label>
                            <input type="text" name="process_title" class="form-control" value="{{ old('process_title', $service->process_title) }}" placeholder="Enter process section heading">
                        </div>

                        <div id="process-wrapper">
                            @foreach($processSteps as $i => $step)
                                <div class="repeater-item process-item border rounded p-3 mb-3 position-relative">
                                    <button type="button" class="btn btn-danger btn-sm remove-item" style="position:absolute; top:10px; right:10px;">Remove</button>
                                    <div class="row">
                                        <div class="col-md-2 mb-2">
                                            <label class="form-label">Step Number</label>
                                            <input type="text" name="process_steps[{{ $i }}][number]" class="form-control" value="{{ $step['number'] ?? '' }}" placeholder="01">
                                        </div>
                                        <div class="col-md-10 mb-2">
                                            <label class="form-label">Step Title</label>
                                            <input type="text" name="process_steps[{{ $i }}][title]" class="form-control" value="{{ $step['title'] ?? '' }}" placeholder="Enter step title">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- PARTNER --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">Partner / Why Work With Us</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Section Title</label>
                                <input type="text" name="partner_title" class="form-control" value="{{ old('partner_title', $service->partner_title) }}" placeholder="Enter section title">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Section Image</label>
                                <input type="file" name="partner_image" class="form-control image-input" data-preview="#partner_image_preview">

                                <div class="mt-2">
                                    <img id="partner_image_preview"
                                         src="{{ !empty($service->partner_image) ? asset($service->partner_image) : '' }}"
                                         style="max-width: 180px; {{ !empty($service->partner_image) ? '' : 'display:none;' }} border:1px solid #ddd; padding:4px; border-radius:6px;">
                                </div>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Bullet Points (one per line)</label>
                                <textarea name="partner_points" rows="6" class="form-control" placeholder="Enter one point per line">{{ old('partner_points', $service->partner_points) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FAQ --}}
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">FAQ</h4>
                        <button type="button" class="btn btn-primary btn-sm" onclick="addFaq()">+ Add FAQ</button>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">FAQ Section Heading</label>
                            <input type="text" name="faq_title" class="form-control" value="{{ old('faq_title', $service->faq_title) }}" placeholder="Enter FAQ section heading">
                        </div>

                        <div id="faq-wrapper">
                            @foreach($faqs as $i => $faq)
                                <div class="repeater-item faq-item border rounded p-3 mb-3 position-relative">
                                    <button type="button" class="btn btn-danger btn-sm remove-item" style="position:absolute; top:10px; right:10px;">Remove</button>
                                    <div class="row">
                                        <div class="col-md-5 mb-2">
                                            <label class="form-label">Question</label>
                                            <input type="text" name="faqs[{{ $i }}][question]" class="form-control" value="{{ $faq['question'] ?? '' }}" placeholder="Enter question">
                                        </div>
                                        <div class="col-md-7 mb-2">
                                            <label class="form-label">Answer</label>
                                            <textarea name="faqs[{{ $i }}][answer]" class="form-control" rows="3" placeholder="Enter answer">{{ $faq['answer'] ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- CTA --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">Bottom CTA</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CTA Title</label>
                                <input type="text" name="cta_title" class="form-control" value="{{ old('cta_title', $service->cta_title) }}" placeholder="Enter CTA title">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Button Text</label>
                                <input type="text" name="cta_button_text" class="form-control" value="{{ old('cta_button_text', $service->cta_button_text) }}" placeholder="Enter button text">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Button Link</label>
                                <input type="text" name="cta_button_link" class="form-control" value="{{ old('cta_button_link', $service->cta_button_link) }}" placeholder="Enter button link">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">CTA Image</label>
                                <input type="file" name="cta_image" class="form-control image-input" data-preview="#cta_image_preview">

                                <div class="mt-2">
                                    <img id="cta_image_preview"
                                         src="{{ !empty($service->cta_image) ? asset($service->cta_image) : '' }}"
                                         style="max-width: 180px; {{ !empty($service->cta_image) ? '' : 'display:none;' }} border:1px solid #ddd; padding:4px; border-radius:6px;">
                                </div>
                            </div>

                            <div class="col-md-8 mb-3">
                                <label class="form-label">CTA Description</label>
                                <textarea name="cta_description" rows="4" class="form-control" placeholder="Enter CTA description">{{ old('cta_description', $service->cta_description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SEO META --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">SEO Meta</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Meta Title</label>
                                <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $service->meta_title) }}" placeholder="Enter meta title">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Meta Keywords</label>
                                <textarea name="meta_keywords" rows="3" class="form-control" placeholder="keyword1, keyword2">{{ old('meta_keywords', $service->meta_keywords) }}</textarea>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Meta Description</label>
                                <textarea name="meta_description" rows="3" class="form-control" placeholder="Enter meta description">{{ old('meta_description', $service->meta_description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let menuIndex = document.querySelectorAll('#menu-items-wrapper .menu-item').length;
    let cardIndex = document.querySelectorAll('#cards-wrapper .card-item').length;
    let featureIndex = document.querySelectorAll('#features-wrapper .feature-item').length;
    let processIndex = document.querySelectorAll('#process-wrapper .process-item').length;
    let faqIndex = document.querySelectorAll('#faq-wrapper .faq-item').length;

    function bindImagePreview(input) {
        input.addEventListener('change', function () {
            const previewSelector = this.getAttribute('data-preview');
            if (!previewSelector) return;

            const preview = document.querySelector(previewSelector);
            if (!preview) return;

            const file = this.files[0];
            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            }
        });
    }

    document.querySelectorAll('.image-input').forEach(function (input) {
        bindImagePreview(input);
    });

    window.addMenuItem = function () {
        const html = `
            <div class="repeater-item menu-item border rounded p-3 mb-3 position-relative">
                <button type="button" class="btn btn-danger btn-sm remove-item" style="position:absolute; top:10px; right:10px;">Remove</button>
                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label">Menu Item Title</label>
                        <input type="text" name="menu_items[${menuIndex}][title]" class="form-control" placeholder="Enter menu item title">
                    </div>
                </div>
            </div>
        `;
        document.getElementById('menu-items-wrapper').insertAdjacentHTML('beforeend', html);
        menuIndex++;
    };

   window.addCard = function () {
    const previewId = `card_preview_${cardIndex}`;
    const html = `
        <div class="repeater-item card-item border rounded p-3 mb-3 position-relative">
            <button type="button" class="btn btn-danger btn-sm remove-item" style="position:absolute; top:10px; right:10px;">Remove</button>
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="form-label">Card Image</label>
                    <input type="file"
                           name="cards[${cardIndex}][image]"
                           class="form-control image-input"
                           data-preview="#${previewId}">

                    <input type="hidden" name="cards[${cardIndex}][old_image]" value="">

                    <div class="mt-2">
                        <img id="${previewId}"
                             src=""
                             style="max-width: 140px; display:none; border:1px solid #ddd; padding:4px; border-radius:6px;">
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label">Card Title</label>
                    <input type="text" name="cards[${cardIndex}][title]" class="form-control" placeholder="Enter card title">
                </div>
                <div class="col-md-5 mb-2">
                    <label class="form-label">Card Description</label>
                    <textarea name="cards[${cardIndex}][description]" class="form-control" rows="3" placeholder="Enter card description"></textarea>
                </div>
            </div>
        </div>
    `;
    document.getElementById('cards-wrapper').insertAdjacentHTML('beforeend', html);

    const newInput = document.querySelector(`#${previewId}`).closest('.card-item').querySelector('.image-input');
    bindImagePreview(newInput);

    cardIndex++;
};

    window.addFeature = function () {
        const html = `
            <div class="repeater-item feature-item border rounded p-3 mb-3 position-relative">
                <button type="button" class="btn btn-danger btn-sm remove-item" style="position:absolute; top:10px; right:10px;">Remove</button>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Point Title</label>
                        <input type="text" name="features[${featureIndex}][title]" class="form-control" placeholder="Enter point title">
                    </div>
                    <div class="col-md-8 mb-2">
                        <label class="form-label">Point Description</label>
                        <textarea name="features[${featureIndex}][description]" class="form-control" rows="3" placeholder="Enter point description"></textarea>
                    </div>
                </div>
            </div>
        `;
        document.getElementById('features-wrapper').insertAdjacentHTML('beforeend', html);
        featureIndex++;
    };

    window.addProcess = function () {
        const html = `
            <div class="repeater-item process-item border rounded p-3 mb-3 position-relative">
                <button type="button" class="btn btn-danger btn-sm remove-item" style="position:absolute; top:10px; right:10px;">Remove</button>
                <div class="row">
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Step Number</label>
                        <input type="text" name="process_steps[${processIndex}][number]" class="form-control" placeholder="02">
                    </div>
                    <div class="col-md-10 mb-2">
                        <label class="form-label">Step Title</label>
                        <input type="text" name="process_steps[${processIndex}][title]" class="form-control" placeholder="Enter step title">
                    </div>
                </div>
            </div>
        `;
        document.getElementById('process-wrapper').insertAdjacentHTML('beforeend', html);
        processIndex++;
    };

    window.addFaq = function () {
        const html = `
            <div class="repeater-item faq-item border rounded p-3 mb-3 position-relative">
                <button type="button" class="btn btn-danger btn-sm remove-item" style="position:absolute; top:10px; right:10px;">Remove</button>
                <div class="row">
                    <div class="col-md-5 mb-2">
                        <label class="form-label">Question</label>
                        <input type="text" name="faqs[${faqIndex}][question]" class="form-control" placeholder="Enter question">
                    </div>
                    <div class="col-md-7 mb-2">
                        <label class="form-label">Answer</label>
                        <textarea name="faqs[${faqIndex}][answer]" class="form-control" rows="3" placeholder="Enter answer"></textarea>
                    </div>
                </div>
            </div>
        `;
        document.getElementById('faq-wrapper').insertAdjacentHTML('beforeend', html);
        faqIndex++;
    };

    document.addEventListener('click', function (e) {
        const removeBtn = e.target.closest('.remove-item');
        if (removeBtn) {
            const item = removeBtn.closest('.repeater-item');
            if (item) {
                item.remove();
            }
        }
    });
});
</script>
@endpush