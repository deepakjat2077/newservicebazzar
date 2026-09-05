@extends($themeView.'layouts.site')
    @section('content')
    @section('title','Listing Details')
    @include($themeView.'includes.rich-content')

    @push('css')

    @if(isset($listing) && !empty($listing->name))
        @php
            $mobileSearchValue = $listing->name;
        @endphp
    @elseif(isset($subcategory) && !empty($subcategory->name))
        @php
            $mobileSearchValue = $subcategory->name;
        @endphp
    @elseif(isset($category) && !empty($category->name))
        @php
            $mobileSearchValue = $category->name;
        @endphp
    @endif

    <style>
        .related-search-more {
            border: none;
            cursor: pointer;
            font-family: inherit;
        }

        .also-listed-popup-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 999999;
            background: rgba(0, 0, 0, 0.48);
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .also-listed-popup-overlay.show {
            display: flex;
        }

        .also-listed-popup {
            width: 100%;
            max-width: 480px;
            max-height: 530px;
            background: #fff;
            border-radius: 14px;
            padding: 28px 30px;
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.25);
        }

        .also-listed-popup-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
        }

        .also-listed-popup-header h4 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #111;
        }

        .also-listed-popup-close {
            border: none;
            background: transparent;
            font-size: 36px;
            line-height: 1;
            color: #111;
            cursor: pointer;
            padding: 0;
        }

        .also-listed-popup-body {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
            max-height: 420px;
            overflow-y: auto;
            padding-right: 8px;
        }

        .also-listed-popup-body .related-search-tag {
            display: inline-block;
            width: auto;
        }

        body.also-listed-popup-open {
            overflow: hidden;
        }

        @media (max-width: 576px) {
            .also-listed-popup {
                max-width: 92%;
                padding: 22px 18px;
            }

            .also-listed-popup-header h4 {
                font-size: 21px;
            }
        }
    </style>

        <style>
            /*
             * Fix: on this page (and only this page), the browser computes
             * #scrollSearchHolder's auto height as 56px instead of the 40px
             * its own content (.header-search-area) actually is — no CSS
             * rule sets that height anywhere (verified: zero stylesheet
             * rules with a `height` declaration match this element or any
             * of its ancestors/descendants on this page), so it isn't a
             * conflicting margin/padding/position/transform to remove. The
             * extra 16px pushes the search bar 8px above the header's
             * vertical center, since the visible content sits flush at the
             * top of that oversized box instead of being centered in it.
             * 40px is not an arbitrary number — it's the same height the
             * shared header CSS already assigns to the search boxes
             * themselves (#scrollSearchHolder .header-input_location_box,
             * .header-input_search_box { height: 40px }), and it's what
             * this holder naturally renders as on every other page. Pinning
             * it here makes this page match that reference exactly.
             * (Previous attempt at this fix, now removed: targeted
             * ".search-row-custom", a class the header never uses — it
             * uses "header-search-row-custom" — so it never matched
             * anything and had no effect.)
             */
            #scrollSearchHolder {
                height: 40px;
            }

            .quick-star{
        font-size:26px;
        color:#d6d6d6;
        cursor:pointer;
        transition:.2s;
    }

    .quick-star.active{
        color:#ff5a00;
    }
            .faq .accordion .card {
                margin-bottom: 10px!important;
            }
            .faq .accordion .card .card-header h5 button {
                padding: 4px 30px 0 46px !important;
                width: 100%;
                min-height: 60px;
                height: auto !important;
                overflow-wrap: break-word !important;
                word-break: break-word;
                white-space: normal !important;
                display: block;
                text-align: left;
                line-height: 1.4;
            }
            .faq .accordion .card .card-header h5{
                width: 100%;
            }
            .faq .accordion .card .card-header h5 button:after {
                left: 14px !important;
            }
            .faq .accordion .card .card-body {
                padding-left: 65px!important;
            }
            .start--time, .end--time {
                padding: 1px 10px;
            }
            .btn__schedule{
                cursor: pointer;
                background-color: #ff5252;
                color: #fff;
                display: inline-block;
                font-weight: 400;
                text-align: center;
                white-space: nowrap;
                vertical-align: middle;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
                border: 1px solid transparent;
                padding: .375rem .75rem;
                font-size: 1rem;
                line-height: 1.5;
                border-radius: .25rem;
                transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
            }
            h1 {
        font-size: 1.5rem;
    }
    .listing-star-icon {
        width: 25px;
        height: 25px;
        object-fit: contain;
        margin-left: 6px;
        vertical-align: middle;
    }

    .listing-photo-click {
        cursor: pointer;
    }


    /* --- relocated from inline <style> block (originally after line 272) to prevent FOUC --- */
    

    .listing-breadcrumb

    {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        font-size: 13px;
        font-weight: 400;
        color: #111;
    margin: 10px 10px 10px 10px;
    }

    .listing-breadcrumb .sep{
        color:#111;
        font-weight:600;
    }

    .breadcrumb-location-link,
    .breadcrumb-category-link{
        color:#111;
        text-decoration:none;
    }

    .breadcrumb-location-link:hover,
    .breadcrumb-category-link:hover{
        color:#0d6efd;
    }

    .listing-main-title{
        font-size:20px;
        font-weight:700;
        color:#111;
        margin:0 0 12px;
        line-height:1.3;
    }


    /* --- relocated from inline <style> block (originally after line 505) to prevent FOUC --- */
                                    .btn-call-main{
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .call-icon{
        width:18px;
        height:18px;
        object-fit:contain;
    }

    .btn-best-price{
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .btn-best-price .btn-icon{
        width:22px;
        height:22px;
        object-fit:contain;
    }
    .btn-whatsapp-main{
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .whatsapp-icon{
        width:22px;
        height:22px;
        object-fit:contain;
    }


    /* --- relocated from inline <style> block (originally after line 643) to prevent FOUC --- */
    .listing-location-tooltip{
        position:relative;
        display:inline-flex;
        align-items:center;
        cursor:pointer;
    }

    .address-tooltip{
        position:absolute;
        top:-12px;
        left:120%;
        width:300px;
        background:#fff;
        color:#111;
        padding:10px 14px;
        border-radius:8px;
        box-shadow:0 3px 15px rgba(0,0,0,.15);
        font-size:13px;
        font-weight:500;
        line-height:1.6;
        z-index:99999;

        opacity:0;
        visibility:hidden;
        transition:.2s;
    }

    .listing-location-tooltip:hover .address-tooltip{
        opacity:1;
        visibility:visible;
    }

    .address-tooltip:before{
        content:"";
        position:absolute;
        top:18px;
        left:-8px;

        border-top:8px solid transparent;
        border-bottom:8px solid transparent;
        border-right:8px solid #fff;
    }


    @media (max-width:767px){

        .address-tooltip{
            top:100% !important;
            left:0 !important;
            margin-top:8px;
            width:280px;
            max-width:90vw;
        }

        .address-tooltip:before{
            top:-8px;
            left:18px;
            border-left:8px solid transparent;
            border-right:8px solid transparent;
            border-bottom:8px solid #fff;
            border-top:none;
        }

    }
        .share-hover-wrap{
        position:relative;
    }

    .share-hover-popup{
        position:absolute;
        top:100%;
        right:0;
        margin-top:8px;
        opacity:0;
        visibility:hidden;
        transition:.25s;
        z-index:99999;
    }

    .share-hover-wrap:hover .share-hover-popup{
        opacity:1;
        visibility:visible;
    }
    .share-hover-wrap{
        position:relative;
        display:inline-flex;
    }

    .share-btn-icon{
        width:38px !important;
        height:38px !important;
        min-width:38px !important;
        padding:0 !important;
        display:flex !important;
        align-items:center;
        justify-content:center;
    }

    .share-hover-popup{
        position:absolute;
        top:48px;
        left:-10px;
        width:235px;
        background:#fff;
        border-radius:9px;
        padding:12px 14px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
        box-shadow:0 4px 14px rgba(0,0,0,.18);
        opacity:0;
        visibility:hidden;
        z-index:99999;
    }

    .share-hover-wrap:hover .share-hover-popup{
        opacity:1;
        visibility:visible;
    }

    .share-hover-popup .share-option{
        width:62px;
        padding:0 !important;
        margin:0 !important;
        text-align:center;
        text-decoration:none;
        border:0 !important;
        background:transparent !important;
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
    }

    .share-hover-popup .share-icon{
        width:42px;
        height:42px;
        border-radius:7px;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:26px;
    }


    .share-hover-popup small{
        margin-top:6px;
        font-size:11px;
        line-height:1;
        color:#111;
        white-space:nowrap;
    }

    .share-btn-icon{
        width:42px;
        height:42px;
        min-width:42px;
        padding:0;
    }
    .share-hover-popup:before{
        content:"";
        position:absolute;
        top:-8px;
        left:18px;
        border-left:8px solid transparent;
        border-right:8px solid transparent;
        border-bottom:8px solid #fff;
    }

    /* Edit Button Styles
       Scoped to .listing-action-buttons and flagged !important to override
       the .listing-action-buttons .listing-btn base rule (min-width:140px,
       border:none) — same technique the share button uses — so the edit
       button stays a compact 42px icon-square that sits inline next to the
       share button instead of stretching to 140px and wrapping to a new row. */
    .listing-action-buttons .edit-listing-btn{
        width:42px !important;
        height:42px !important;
        min-width:42px !important;
        padding:0 !important;
        background:#fff !important;
        border:1px solid #e5e5e5 !important;
        border-radius:6px !important;
        display:inline-flex !important;
        align-items:center !important;
        justify-content:center !important;
        color:#0d6efd;
        cursor:pointer;
        transition:all 0.2s ease;
        text-decoration:none;
        flex:0 0 auto !important;
    }

    .listing-action-buttons .edit-listing-btn:hover{
        background:#0d6efd !important;
        color:#fff;
        border-color:#0d6efd !important;
    }

    .listing-action-buttons .edit-listing-btn i{
        font-size:18px;
    }

    .edit-icon-img{
        width:20px;
        height:20px;
        object-fit:contain;
    }

    .mqa-edit-icon-img{
        width:24px;
        height:24px;
        object-fit:contain;
    }

    body.share-modal-open{
        overflow:hidden !important;
        height:100vh !important;
    }

    /* --- relocated from inline <style> block (originally after line 1106) to prevent FOUC --- */

        @media (max-width:767px){

        div.listing-action-buttons{
            display:grid !important;
            grid-template-columns:repeat(2, 1fr) !important;
            gap:8px !important;
            width:100% !important;
            max-width:100% !important;
        }

        div.listing-action-buttons > .listing-btn{
            width:100% !important;
            max-width:100% !important;
            min-width:0 !important;
            height:40px !important;
            padding:7px 5px !important;
            font-size:11px !important;
            display:flex !important;
            align-items:center !important;
            justify-content:center !important;
            white-space:nowrap !important;
            overflow:hidden !important;
            text-overflow:ellipsis !important;
            margin:0 !important;
        }
    }

        @media (max-width:767px){

    
        

    .listing-action-buttons .listing-btn{
            width:100%;
            min-width:0;
            height:40px;
            padding:8px 6px;
            font-size:12px;
            line-height:1.1;
            display:flex;
            align-items:center;
            justify-content:center;
            text-align:center;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
            border-radius:6px;
        }
            .listing-action-buttons .btn-best-price{
            grid-column:auto !important;
        }
        
        .listing-action-buttons .btn-landline-main i{
            margin-right:3px;
            font-size:14px;
            flex-shrink:0;
        }

    }

        .btn-landline-main{
        background:#ff9800;
        color:#fff;
        border:1px solid #ff9800;
    }

    .btn-landline-main:hover{
        background:#f57c00;
        color:#fff;
        border-color:#f57c00;
    }

    .btn-landline-main i{
        margin-right:6px;
        font-size:16px;
    }

        .review-menu-wrap{
        position:relative;
    }

    .review-menu-btn{
        border:none;
        background:none;
        cursor:pointer;
        font-size:22px;
    }

    .review-menu-dropdown{
        display:none;
        position:absolute;
        right:0;
        top:28px;
        width:170px;
        background:#fff;
        border:1px solid #e5e5e5;
        box-shadow:0 4px 14px rgba(0,0,0,.12);
        border-radius:6px;
        z-index:9999;
        overflow:hidden;
    }

    .review-menu-dropdown.show{
        display:block;
    }

    .review-menu-dropdown button{
        width:100%;
        border:none;
        background:#fff;
        padding:10px 12px;
        text-align:left;
        font-size:14px;
        cursor:pointer;
    }

    .review-menu-dropdown button:hover{
        background:#f5f5f5;
    }

    .jd-rate-box{
        text-align:right;
    }

    .jd-rate-title{
        font-size:14px;
        font-weight:600;
        margin-bottom:5px;
    }

    .jd-rate-stars{
        display:flex;
        justify-content:flex-end;
        gap:4px;
    }

    .jd-star{
        width:30px;
        height:30px;
        border:1px solid #ddd;
        border-radius:4px;
        text-align:center;
        line-height:28px;
        font-size:18px;
        color:#bbb;
        cursor:pointer;
        transition:0.2s;
    }

    .jd-star.active,
    .jd-star:hover{
        background:#ff5a00;
        color:#fff;
        border-color:#ff5a00;
    }

    .btn-success {
        color: #fff;
        background-color: #ff5a00;
        border-color: #ff5a00;
    }

    .btn-success:hover {
        color: #fff;
        background-color: #ff5a00;
        border-color: #ff5a00;
    }
    .jd-rate-text{
        font-size:12px;
        color:#777;
        display:block;
        margin-top:4px;
    }
        @media (max-width: 767px) {

            .listing-breadcrumb {
                display: none;
            }
    .jd-rate-stars {
        display: flex;
        justify-content: space-around;
        gap: 4px;
    }


    
            .jd-rate-title{
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 5px;
        display: flex;
        justify-content: start;
    }
            .jd-rating-badge {
            font-size: 12px;
            padding: 2px 6px;
        }

        .jd-rating-count {
            font-size: 12px;
        }

        .jd-badge-img {
            height: 18px;
        }

        .subcat-chip {
            font-size: 12px;
            padding: 4px 8px;
        }

        .listing-area {
            padding: 12px !important;
        }

        .listing-card .listing-area .row {
            flex-direction: column;
            align-items: flex-start !important;
        }

        .listing-card .listing-area .row > .col-4,
        .listing-card .listing-area .row > .col-8,
        .listing-card .listing-area .row > .col-md-4,
        .listing-card .listing-area .row > .col-md-8 {
            width: 100%;
            max-width: 100%;
            flex: 0 0 100%;
        }

        .listing-card .listing-area .row > .col-4,
        .listing-card .listing-area .row > .col-md-4 {
            margin-bottom: 12px;
        }

        .listing-img {
            width: 100%;
            height: 190px;
            object-fit: cover;
            display: block;
            border-radius: 8px;
        }

        .listing-title {
            font-size: 16px;
            margin-bottom: 6px;
        }

        .listing-location {
            margin-bottom: 8px;
        }

        .desktop-buttons {
            display: none !important;
        }

        .mobile-buttons {
            display: flex !important;
            gap: 10px;
            flex-wrap: nowrap;
        }

        .mobile-buttons .btn-whatsapp {
            display: none !important;
        }

        .mobile-buttons .btn-call,
        .mobile-buttons .btn-best-price {
            flex: 1;
            min-width: 0;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 14px !important;
            padding: 10px 12px;
            border-radius: 8px;
            white-space: nowrap;
        }

        .mobile-buttons .btn-call i,
        .mobile-buttons .btn-best-price i {
            font-size: 16px;
            margin-right: 6px;
        }

        .best-price-modal {
            padding: 14px;
        }

        .best-price-modal-dialog {
            max-width: 340px;
        }

        .best-price-left {
            padding: 20px 16px 16px;
        }

        .best-price-title {
            font-size: 17px;
        }
    }

    /* Popup */
    /*
     * Two fields and a button — so the box is sized to that and nothing else.
     * It used to open a 1000px, half-screen-tall panel with a promo image
     * taking up almost half of it, which reads as an advertisement rather than
     * a quick question and is what people were leaving over. Small enough now
     * to take in at a glance, with the page still visible around it.
     */
    .best-price-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 999999;
        background: rgba(0,0,0,0.55);
        padding: 20px;
        overflow-y: auto;
    }

    .best-price-modal.show {
        display: flex;
        justify-content: center;
        /* Centred, not pinned to the top edge — the box is short now. */
        align-items: center;
    }

    .best-price-modal-dialog {
        width: 100%;
        max-width: 380px;
        background: #fff;
        border-radius: 14px;
        overflow: hidden;
        position: relative;
        box-shadow: 0 12px 40px rgba(0,0,0,0.22);
    }

    .best-price-close {
        position: absolute;
        top: 6px;
        right: 12px;
        border: none;
        background: transparent;
        font-size: 26px;
        line-height: 1;
        cursor: pointer;
        z-index: 99;
        color: #888;
    }

    .best-price-modal-content {
        display: block;
    }

    .best-price-left {
        width: 100%;
        padding: 24px 22px 20px;
    }

    .best-price-title {
        font-size: 18px;
        font-weight: 700;
        color: #111;
        margin-bottom: 16px;
        padding-right: 22px;
        line-height: 1.35;
    }

    .best-price-title span {
        color: #0d6efd;
    }

    .best-price-field {
        margin-bottom: 12px;
    }

    .best-price-field label {
        display: block;
        font-size: 13px;
        margin-bottom: 5px;
        color: #666;
        font-weight: 500;
    }

    .best-price-field input {
        width: 100%;
        height: 42px;
        border: 1px solid #cfcfcf;
        border-radius: 6px;
        padding: 0 12px;
        font-size: 15px;
        outline: none;
    }

    .best-price-field input:focus {
        border-color: #0d6efd;
    }

    .best-price-check {
        display: flex;
        gap: 7px;
        align-items: flex-start;
        margin: 4px 0 14px;
        font-size: 12px;
        line-height: 1.4;
        color: #777;
    }

    .best-price-check a {
        color: #777;
        text-decoration: underline;
    }

    .best-price-submit {
        background: #0d6efd;
        color: #fff;
        border: none;
        border-radius: 6px;
        width: 100%;
        height: 44px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
    }

    .best-price-points {
        margin: 12px 0 0;
        font-size: 12px;
        line-height: 1.5;
        color: #888;
        text-align: center;
    }


        .btn-best-price {
        background: #0d6efd;
        color: #fff;
        padding: 8px 18px;
        border-radius: 6px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: 0.2s ease;
        border: none;
        cursor: pointer;
    }

    .btn-best-price:hover {
        background: #0b5ed7;
        color: #fff;
    }
        .dot-separator {
        margin: 0 6px;
        font-weight: bold;
        color: #555;
    }

    .business-years {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 14px;
        color: #000000;
        font-weight: 400;
    }
    .jd-rating-wrap {
        gap: 10px;
    }

    .jd-rating-badge {
        background: #1bbf73;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
    }

    .jd-rating-count {
        font-size: 13px;
        color: #555;
    }

    .jd-badge-img {
        height: 20px;
        object-fit: contain;
    }
    .sticky-listing-bar{
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        background: #fff;
        border-top: 1px solid #dcdcdc;
        box-shadow: 0 -2px 12px rgba(0,0,0,0.08);
        z-index: 9999;
        transform: translateY(120%);
        transition: all 0.3s ease;
    }

    .sticky-listing-bar.show{
        transform: translateY(0);
    }

    .sticky-listing-inner{
        max-width: 1400px;
        margin: 0 auto;
        padding: 10px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .sticky-left{
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .sticky-logo{
        width: 54px;
        height: 54px;
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
        flex-shrink: 0;
        display:flex;
        align-items:center;
        justify-content:center;
    }

    .sticky-logo img{
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .sticky-info h6{
        margin: 0 0 6px;
        font-size: 16px;
        font-weight: 700;
        color: #111;
        line-height: 1.2;
    }

    .sticky-meta{
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .sticky-rating{
        background: #159a3f;
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 6px;
    }

    .sticky-badge{
        font-size: 13px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 6px;
    }

    .sticky-badge.trust{
        background: #ffd84d;
        color: #6a5100;
    }

    .sticky-badge.verified{
        color: #2b7de9;
        background: transparent;
        padding-left: 0;
    }

    .sticky-actions{
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    .sticky-btn{
        height: 42px;
        padding: 0 14px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        font-weight: 700;
        font-size: 14px;
        border: none;
        white-space: nowrap;
    }

    .sticky-call-btn{
        background: #128c2f;
        color: #fff;
    }

    .sticky-call-btn:hover{
        color: #fff;
        background: #0e7427;
    }

    .sticky-enquire-btn{
        background: #0d6efd;
        color: #fff;
    }

    .sticky-enquire-btn:hover{
        color: #fff;
        background: #0b5ed7;
    }

    .sticky-whatsapp-btn{
        background: #fff;
        color: #111;
        border: 2px solid #20b15a;
    }

    .sticky-whatsapp-btn:hover{
        background: #20b15a;
        color: #fff;
    }

    @media (max-width: 991px){
        .sticky-listing-inner{
            flex-direction: column;
            align-items: stretch;
        }

        .sticky-actions{
            width: 100%;
            justify-content: space-between;
        }

        .sticky-btn{
            flex: 1;
            justify-content: center;
            padding: 0 10px;
        }
    }

    @media (max-width: 767px){
        .sticky-info h6{
            font-size: 14px;
        }

        .sticky-logo{
            width: 50px;
            height: 50px;
        }

        .sticky-btn span{
            display: inline;
        }

        .sticky-btn{
            min-width: 46px;
            padding: 0 12px;
        }
    }

   
    @media (max-width: 768px) {
        #stickyListingBar .sticky-left {
            display: none;
        }

        #stickyListingBar .sticky-listing-inner {
            padding: 10px 12px;
        }

        #stickyListingBar .listing-action-buttons .btn-landline-main,
        #stickyListingBar .listing-action-buttons .share-btn-icon {
            display: none !important;
        }

        #stickyListingBar .listing-action-buttons {
            display: flex !important;
            flex-wrap: nowrap !important;
            width: 100% !important;
            max-width: 100% !important;
            gap: 8px !important;
            margin-top: 0 !important;
        }

        #stickyListingBar .listing-action-buttons .listing-btn {
            flex: 1 1 0 !important;
            min-width: 0 !important;
            width: auto !important;
            max-width: none !important;
            height: 40px !important;
            padding: 0 6px !important;
            border-radius: 8px !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            gap: 6px !important;
        }

        /* Order: Call Now, Enquire Now, WhatsApp */
        #stickyListingBar .listing-action-buttons .btn-call-main {
            order: 1;
            background: #0d6efd !important;
            color: #fff !important;
            border: none !important;
            font-size: 0 !important;
        }

        #stickyListingBar .listing-action-buttons .btn-call-main::after {
            content: "Call Now";
            font-size: 12px;
            font-weight: 700;
        }

        #stickyListingBar .listing-action-buttons .btn-best-price {
            order: 2;
            font-size: 0 !important;
        }

        #stickyListingBar .listing-action-buttons .btn-best-price::after {
            content: "Enquire Now";
            font-size: 12px;
            font-weight: 700;
        }

        #stickyListingBar .listing-action-buttons .btn-whatsapp-main {
            order: 3;
            background: #0d6efd !important;
            color: #fff !important;
            border: none !important;
        }
    }

    @media (max-width: 767px) {

        .best-price-modal {
            padding: 14px;
        }

        .best-price-modal-dialog {
            max-width: 340px;
        }

        .best-price-left {
            padding: 20px 16px 16px;
        }

        .best-price-title {
            font-size: 17px;
        }
    }

    @media (min-width:768px){
    
        .listing-card-section{
            margin-top:0;
        }
    }



    @media (max-width:767px){

        .review-actions{
            display:grid;
            grid-template-columns:repeat(3, 1fr);
            gap:0;
            margin-top:12px;
            width:100%;
        }

        .review-action-btn{
            width:100%;
            font-size:12px;
            padding:6px 0;
            text-align:center;
            white-space:normal;
            line-height:1.2;
        }

        .review-action-btn i{
            display:block;
            margin:0 auto 3px;
            font-size:15px;
        }

        .review-action-btn .like-count,
        .review-action-btn .comment-count{
            display:inline;
        }
    }

    /* --- relocated from inline <style> block (originally after line 2079) to prevent FOUC --- */

                        
                        .orrrr{font-size: 14px;
        font-weight: 400;
        color: #111;
    }

    /* --- relocated from inline <style> block (originally after line 2132) to prevent FOUC --- */
                .utlink{
                font-size: 14px;
        font-weight: 400;   
                }

    /* --- relocated from inline <style> block (originally after line 2444) to prevent FOUC --- */
        .user-reviews-header{
        margin:25px 0 20px;
    }



    .review-filters{
        display:flex;
        flex-wrap:wrap;
        gap:12px;
    }

    .review-filter-btn{
        border:1px solid #e2e2e2;
        background:#f8f8f8;
        color:#222;
        font-size:16px;
        font-weight:500;
        padding:10px 18px;
        border-radius:4px;
        cursor:pointer;
        transition:.2s;
    }

    .review-filter-btn:hover{
        background:#eef5fc;
        border-color:#c8ddf2;
    }

    .review-filter-btn.active{
        background:#e7f1fb;
        border-color:#c8ddf2;
        color:#0d6efd;
    }

    @media(max-width:767px){

        .user-reviews-title{
            font-size:18px;
            margin-bottom:15px;
        }

        .review-filters{
            gap:8px;
        }

        .review-filter-btn{
            font-size:8px;
            padding:8px 14px;
        }
    }
        .rating-summary-box{
        display:flex;
        align-items:center;
        gap:18px;
        margin:15px 0 20px;
    }

    .rating-summary-score{
        width:72px;
        height:72px;
        background:rgb(0, 122, 12);
        color:#fff;
        border-radius:18px;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:36px;
        font-weight:500;
        flex-shrink:0;
    }

    .rating-summary-content h3{
        margin:0 0 4px;
        font-size:22px;
        font-weight:600;
        color:#111;
    }

    .rating-summary-content p{
        margin:0;
        font-size:15px;
        color:#666;
        line-height:1.4;
    }

    @media(max-width:767px){

        .rating-summary-score{
            width:60px;
            height:60px;
            font-size:22px;
            border-radius:14px;
        }

        .rating-summary-content h3{
            font-size:18px;
        }

        .rating-summary-content p{
            font-size:13px;
        }

        
    }
        .review-status-text{
        font-size:16px;
        font-weight:600;
        margin-bottom:8px;
        color:#222;
    }
        .edit-star{
        font-size:24px;
        color:#d4d4d4;
        cursor:pointer;
        margin-right:3px;
        transition:.2s;
    }

    .edit-star.active{
        color:#ff5a00;
    }

        .review-edit-box{
        margin-top:15px;
        padding:15px;
        background:#f8f8f8;
        border:1px solid #e5e5e5;
        border-radius:8px;
    }
    .review-item.highlight-review{
        background:#fff8d6;
        border:2px solid #ffb300;
        border-radius:8px;
        padding:15px;
    }
        .review-actions{
        display:flex;
        gap:35px;
        margin-top:15px;
    }

    .review-action-btn{
        border:none;
        background:none;
        font-size:11px;
        color:#111;
        font-weight: 500;
        cursor:pointer;
    }

    .review-action-btn i{
        margin-right:5px;
    }

    .comment-box-wrap{
        margin-top:15px;
    }

    .comment-input-row{
        display:flex;
        gap:12px;
        align-items:center;
    }

    .comment-user-img{
        width:45px;
        height:45px;
        border-radius:4px;
        object-fit:cover;
    }

    .comment-input-flex{
        flex:1;
        display:flex;
    }

    .comment-post-btn{
        border:none;
        background:#fff;
        font-weight:600;
        padding:0 15px;
    }

        .review-item{
        padding:20px 0;
        border-bottom:1px solid #ececec;
    }

    .review-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
    }

    .review-left{
        display:flex;
        align-items:center;
        gap:12px;
    }

    .review-avatar{
        width:48px;
        height:48px;
        border-radius:4px;
        object-fit:cover;
    }

    .review-name{
        font-size:16px;
        font-weight:600;
        color:#222;
    }

    .review-right{
        display:flex;
        align-items:center;
        gap:15px;
    }

    .review-date{
        font-size:14px;
        color:#666;
    }

    .review-menu{
        font-size:20px;
        color:#333;
        cursor:pointer;
    }

    .review-stars{
        margin-top:12px;
    }

    .review-stars .fas{
        color:#ff5a00;
        font-size:18px;
    }

    .review-stars .far{
        color:#cfcfcf;
        font-size:18px;
    }

    .review-text{
        margin-top:10px;
        font-size:15px;
        color:#222;
    }
    .add-review-stars{
        display:flex;
        gap:4px;
        align-items:center;
    }

    .add-review-star{
        font-size:26px;
        color:#d6d6d6;
        cursor:pointer;
        transition:.2s;
    }

    .add-review-star.active{
        color:#ff5a00;
    }

    @media (max-width:767px){

        .review-stars .fas,
        .review-stars .far{
            font-size:12px;
        }

    }


    /* --- relocated from inline <style> block (originally after line 3489) to prevent FOUC --- */
    .follow-links-box{
        display:none;
        background:#fff;
        border:1px solid #e5e5e5;
        border-top:none;
        padding:10px 20px;
    }

    .follow-links-box.show{
        display:block;
    }

    .follow-links-box a{
        display:block;
        padding:8px 0;
        color:#111;
        text-decoration:none;
        font-size:15px;
    }

    .follow-links-box a:hover{
        color:#0d6efd;
    }

    .follow-arrow{
        margin-left:auto;
    }
        .www{
            font-size: 20px;
        font-weight: 500;
        color: #111;
        }
        .listing-side-actions{
        margin-top:12px;
        border-top:1px solid #e5e5e5;
    }

    .listing-side-action{
        width:100%;
        border:none;
        border-bottom:1px solid #e5e5e5;
        background:#fff;
        padding:13px 12px;
        display:flex;
        align-items:center;
        gap:12px;
        font-size:16px;
        color:#111;
        cursor:pointer;
        text-align:left;
    }

    .listing-side-action:hover{
        background:#f1f1f1;
    }

    .listing-side-action i{
        font-size:22px;
        color:#0d6efd;
    }

    /* --- relocated from inline <style> block (originally after line 3709) to prevent FOUC --- */
        .related-searches-box{
            font-size: 13px;
        line-height: 2;
        color: #666;
        padding: 0px 0px 10px 0px;
    }



    .related-searches-list{
        line-height:2;
    }

    .related-searches-list a{
    color: #666;
        text-decoration: none;
        margin: 0 4px;
        display: inline;
        white-space: normal;
        overflow-wrap: break-word;
        word-break: break-word;
    }

    .related-searches-list a:hover{
        color:#0d6efd;
    }

    .related-searches-list span{
        color:#777;
        margin:0 6px;
    }

    /* Mobile-only "Related Searches" dropdown toggle — mirrors the
       footer's "Popular Categories"/"Popular Cities" accordion pattern.
       Hidden by default (desktop/tablet); only the max-width:767px block
       below turns it on, so wider viewports are unaffected. */
    .related-searches-mobile-toggle {
        display: none;
    }

    @media (max-width: 767px) {
        .related-searches-title {
            display: none;
        }

        .related-searches-mobile-toggle {
            display: flex;
            width: 100%;
            min-height: 48px;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            margin: 0;
            border: 0;
            border-bottom: 1px solid #eee;
            background: transparent;
            color: #222;
            font-size: 14px;
            font-weight: 600;
            text-align: left;
            cursor: pointer;
        }

        .related-searches-toggle-icon {
            display: inline-flex;
            width: 20px;
            height: 20px;
            flex: 0 0 20px;
            align-items: center;
            justify-content: center;
            margin-left: 10px;
            color: #111;
            font-size: 12px;
            line-height: 1;
        }

        .related-searches-list {
            display: none;
            padding-top: 10px;
        }

        .related-searches-list.mobile-open {
            display: block;
        }
    }

    .image-preview-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 999999;
        background: rgba(0,0,0,0.95);
        justify-content: center;
        align-items: center;
    }

    .image-preview-modal.show {
        display: flex;
    }

    /* Image */
    .image-preview-content {
        max-width: 90%;
        max-height: 85%;
        object-fit: contain;
    }

    /* Close */
    .image-close {
        position: absolute;
        top: 20px;
        right: 30px;
        font-size: 40px;
        color: #fff;
        cursor: pointer;
    }

    /* Counter */
    .image-counter {
        position: absolute;
        top: 20px;
        left: 30px;
        color: #fff;
        font-size: 16px;
        background: rgba(0,0,0,0.5);
        padding: 5px 10px;
        border-radius: 6px;
    }

    /* Arrows */
    .nav-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        font-size: 40px;
        color: #fff;
        cursor: pointer;
        padding: 10px;
        user-select: none;
    }

    .nav-arrow.left {
        left: 20px;
    }

    .nav-arrow.right {
        right: 20px;
    }

        .related-search-card{
        background:#fff;
        border:1px solid #e9e9e9;
        border-radius:16px;
        padding:20px;
    }

    .related-search-title{
        font-size:34px;
        font-weight:700;
        color:#111;
        margin-bottom:18px;
        line-height:1.2;
    }

    .related-search-tags{
        display:flex;
        flex-wrap:wrap;
        gap:12px;
    }

    .related-search-tag{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:10px 18px;
        border:1px solid #e5e5e5;
        border-radius:999px;
        background:#fff;
        color:#333;
        text-decoration:none;
        font-size:15px;
        font-weight:500;
        transition:all 0.25s ease;
        line-height:1.4;
    }

    .related-search-tag:hover{
        color:#0d6efd;
        border-color:#0d6efd;
        text-decoration:none;
    }

    @media (max-width: 991px){
        .related-search-title{
            font-size:26px;
        }
    }

    @media (max-width: 767px){
        .related-search-card{
            padding:15px;
            border-radius:12px;
        }

        .related-search-title{
            font-size:22px;
            margin-bottom:14px;
        }

        .related-search-tags{
            gap:10px;
        }

        .related-search-tag{
            font-size:14px;
            padding:8px 14px;
        }

        /* "People Also Search For": desktop/tablet show all 10 tags
           (unchanged); mobile shows only the first 5. Only targets the
           <a> tags (a.related-search-tag) so the "More" button
           (button.related-search-tag.related-search-more) is never
           affected regardless of its position among them. */
        .related-search-tags a.related-search-tag:nth-of-type(n+6){
            display:none;
        }
    }

    /* --- relocated from inline <style> block (originally after line 3925) to prevent FOUC --- */
    .sidebar-sticky-wrapper{
        position: relative;
    }

    #sidebarColumn{
        position: relative;
    }

    .contact-info-box{
        background:#fff;
        border:1px solid #e8e8e8;
        border-radius:12px;
        padding:18px 18px 16px;
        overflow:visible !important;
        height:auto !important;
    }
    .sticky-floating-form{
        position: fixed;
        top: 50px;
        z-index: 9999;
        display: none;
    }



    .sticky-floating-form.show{
        display: block;
    }

    .listing-action-buttons{
        display:flex;
        align-items:center;
        gap:8px;
        flex-wrap:wrap;
        margin-top:12px;
    }

    .btn-icon {
        background-color: #1e8fff00;
    }
    .listing-action-buttons .listing-btn{
        min-width:140px;
        height:40px;
        padding:0 18px;
        border-radius:6px;
        font-weight:600;
        font-size:16px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        text-decoration:none;
        border:none;
        cursor:pointer;
        white-space:nowrap;
    }

    .btn-call-main{
        background:#18a63b;
        color:#fff;
    }

    .btn-call-main:hover{
        background:#12852f;
        color:#fff;
    }

    .btn-whatsapp-main{
        background:#fff;
        color:#1b1b1b;
        border:1px solid #28b446 !important;
    }

    .btn-whatsapp-main:hover{
        color:#1b1b1b;
        background:#f7fff9;
    }

    .btn-best-price{
        background:#0d6efd;
        color:#fff;
    }

    .btn-best-price:hover{
        background:#0b5ed7;
        color:#fff;
    }

    .enquiry-card{
        background:#f7f7f7;
        border:1px solid #cfcfcf;
        border-radius:10px;
        padding:14px;
    }

    .enquiry-title{
        font-size:18px;
        font-weight:700;
        color:#111;
        margin-bottom:6px;
        line-height:1.4;
    }

    .enquiry-title span{
        color:#0d6efd;
    }

    .enquiry-subtitle{
        font-size:13px;
        color:#555;
        margin-bottom:16px;
    }

    .enquiry-label{
        display:block;
        font-size:15px;
        font-weight:700;
        color:#111;
        margin-bottom:10px;
    }

    .input-icon-group{
        position:relative;
    }

    .input-icon{
        position:absolute;
        left:12px;
        top:50%;
        transform:translateY(-50%);
        color:#111;
        font-size:18px;
        z-index:2;
    }

    .enquiry-input{
        height:44px;
        border:1px solid #9e9e9e;
        border-radius:3px;
        padding-left:38px;
        background:#fff;
        box-shadow:none !important;
    }

    .enquiry-check{
        font-size:12px;
        color:#666;
    }

    .enquiry-check a{
        color:#666;
        text-decoration:underline;
    }

    .enquiry-btn{
        background:#0d6efd;
        color:#fff;
        font-weight:700;
        border:none;
        border-radius:4px;
        height:44px;
        font-size:18px;
    }

    .enquiry-btn:hover{
        background:#0b5ed7;
        color:#fff;
    }

    .related-search-card{
        background:#f7f7f7;
        border:1px solid #cfcfcf;
        border-radius:10px;
        padding:14px;
    }

    .related-search-title{
        font-size:20px;
        font-weight:700;
        color:#111;
        margin-bottom:18px;
    }

    .related-search-item{
        display:flex;
        gap:12px;
        align-items:flex-start;
        padding-bottom:14px;
        margin-bottom:14px;
        border-bottom:1px solid #e5e5e5;
    }

    .related-search-item:last-child{
        border-bottom:none;
        margin-bottom:0;
        padding-bottom:0;
    }

    .related-search-img{
        width:68px;
        min-width:68px;
        height:68px;
        border-radius:10px;
        overflow:hidden;
        background:#fff;
    }

    .related-search-img img{
        width:100%;
        height:100%;
        object-fit:cover;
        display:block;
    }

    .related-search-content{
        flex:1;
    }

    .related-search-content h6{
        font-size:17px;
        font-weight:600;
        color:#111;
        margin-bottom:4px;
        line-height:1.4;
    }

    .related-search-content p{
        font-size:13px;
        color:#666;
        margin-bottom:10px;
    }

    .related-search-btn{
        display:inline-block;
        padding:6px 14px;
        border:1px solid #0d6efd;
        color:#0d6efd;
        border-radius:6px;
        font-size:13px;
        font-weight:600;
        text-decoration:none;
        background:#fff;
        transition:0.2s ease;
    }

    .related-search-btn:hover{
        background:#0d6efd;
        color:#fff;
    }

    @media (max-width: 991px){
        .sticky-floating-form{
            display: none !important;
        }
    }   

    @media (max-width: 767px){
        .enquiry-title{
            font-size:16px;
        }

        .enquiry-btn{
            font-size:16px;
        }

        .related-search-title{
            font-size:18px;
        }

        .related-search-content h6{
            font-size:15px;
        }

        .related-search-img{
            width:60px;
            min-width:60px;
            height:60px;
        }

        .listing-action-buttons{
            display:grid;
            grid-template-columns:1fr 1fr 1fr;
            gap:8px;
            width:100%;
        }

        .listing-action-buttons .listing-btn{
            min-width:0;
            width:100%;
            height:40px;
            font-size:10px;
            padding:0 6px;
        }
    }

    /* --- relocated from inline <style> block (originally after line 4571) to prevent FOUC --- */
    .info-modal{
        display:none;
        position:fixed;
        inset:0;
        background:rgba(0,0,0,.55);
        z-index:999999;
        align-items:center;
        justify-content:center;
    }

    .info-modal.show{
        display:flex;
    }

    .info-modal-box{
        width:500px;
        max-width:92%;
        background:#fff;
        border-radius:14px;
        padding:32px 30px 30px;
        position:relative;
    }

    .info-modal-close{
        position:absolute;
        right:25px;
        top:20px;
        border:0;
        background:none;
        font-size:34px;
        cursor:pointer;
    }

    .info-modal-box h3{
        font-size:24px;
        font-weight:600;
        margin-bottom:30px;
    }

    .info-modal-box input{
        width:100%;
        height:56px;
        border:1px solid #bbb;
        border-radius:8px;
        padding:0 15px;
        margin-bottom:20px;
        font-size:17px;
    }

    .info-modal-box small,
    .info-modal-box p{
        font-size:12px;
        color:#555;
    }

    .info-modal-box button[type="submit"]{
        width:100%;
        height:48px;
        border:0;
        border-radius:7px;
        background:#147bdc;
        color:#fff;
        font-size:18px;
        font-weight:600;
    }
    .email-enquiry-modal{
        display:none;
        position:fixed;
        inset:0;
        background:rgba(0,0,0,.6);
        z-index:999999;
        justify-content:center;
        align-items:center;
    }

    .email-enquiry-modal.show{
        display:flex;
    }

    .email-enquiry-box{
        width:95%;
        max-width:500px;
        background:#fff;
        border-radius:12px;
        padding:25px;
        position:relative;
    }

    .email-enquiry-close{
        position:absolute;
        right:15px;
        top:10px;
        border:none;
        background:none;
        font-size:30px;
        cursor:pointer;
    }

        .share-overlay{
        display:none;
        position:fixed;
        inset:0;
        background:rgba(0,0,0,0.55);
        z-index:999999;
        align-items:center;
        justify-content:center;
    }

    .share-overlay.show{
        display:flex;
    }

    .share-popup{
        width:350px;
        max-width:90%;
        background:#fff;
        border-radius:16px;
        padding:24px;
        position:relative;
        box-shadow:0 10px 30px rgba(0,0,0,.15);
    }

    .share-popup h4{
        font-size:22px;
        font-weight:700;
        margin:0;
        color:#111;
    }

    .share-popup p{
        margin:3px 0 25px;
        color:#666;
        font-size:14px;
    }

    .share-close{
        position:absolute;
        right:15px;
        top:12px;
        border:none;
        background:none;
        cursor:pointer;
        padding:0;
        line-height:1;
    }

    .share-close i{
        font-size:32px;
        color:#222;
    }

    .share-options{
        display:flex;
        justify-content:center;
        gap:28px;
    }

    .share-option{
        border:none;
        background:none;
        text-decoration:none;
        color:#333;
        text-align:center;
        cursor:pointer;
        padding:0;
    }

    .share-option:hover{
        text-decoration:none;
    }

    .share-icon{
        width:54px;
        height:54px;
        border-radius:10px;
        display:flex;
        align-items:center;
        justify-content:center;
        margin:0 auto 10px;
    }

    .share-icon i{
        font-size:30px;
        color:#fff;
    }

    .share-icon.fb{
        background:#1877f2;
    }

    .share-icon.wa{
        background:#25d366;
    }

    .share-icon.cp{
        background:#a855f7;
    }

    .share-option small {
        display: block;
        font-size: 11px;
        font-weight: 500;
        color: #111;
        
    }

    {{-- .popular-city-section / .popular-category-section styling now lives
         once in popular-sections.css, loaded from the shared footer --}}

    @media (max-width:767px){

        /*
         * Scoped to .listing-action-buttons: this block was written for
         * the old desktop-style action row's Share button (the widths/
         * heights here — 100%, 40px, left:10% — match that row's grid-cell
         * dimensions). That row is now hidden on mobile (superseded by
         * .mobile-quick-actions / .mqa-share above), but these selectors
         * were unscoped, so they were also matching — and, with their
         * !important flags, silently overriding — the new .mqa-share
         * button's own popup positioning. Scoping them back to their
         * original container is the root-cause fix, not another
         * !important layered on top.
         */
        .listing-action-buttons .share-hover-wrap{
            position:relative !important;
            width:100% !important;
        }

        .listing-action-buttons .share-btn-icon{
            width:100% !important;
            height:40px !important;
        }

        .listing-action-buttons .share-hover-popup{
            top:48px !important;
            left:10% !important;
            right:auto !important;
            transform:translateX(-50%) !important;
            width:235px !important;
            min-width:235px !important;
            padding:12px 14px !important;
            border-radius:10px !important;
            display:flex !important;
            justify-content:space-between !important;
            gap:10px !important;
            overflow:visible !important;
        }

        .listing-action-buttons .share-hover-popup:before{
            left:50% !important;
            right:auto !important;
            transform:translateX(-50%) !important;
        }

        .listing-action-buttons .share-hover-popup .share-option{
            width:62px !important;
        }

        .listing-action-buttons .share-hover-popup .share-icon{
            width:42px !important;
            height:42px !important;
            font-size:25px !important;
        }

        .listing-action-buttons .share-hover-popup small{
            font-size:11px !important;
        }
    }

    /* --- relocated from inline <style> block (originally after line 5001) to prevent FOUC --- */

        .col-lg-3{
        overflow: visible !important;
    }

    .contact-info-box{
        overflow: visible !important;
        height: auto !important;
    }

    #mainEnquiryForm{
        width: 100%;
    }

    @media (max-width: 991px){
        #mainEnquiryForm{
            position: static;
            top: auto;
        }
    }

        .sticky-floating-form{
        position: fixed;
        top: 50px;
        z-index: 9997;
        display: none;
    }


        .contact-info-box{
        position: relative;
    }
    .media-main-box{
        background:#fff;
        border:1px solid #e8e8e8;
        border-radius:12px;
        overflow:hidden;
    }

    .contact-info-box{
        background:#fff;
        border:1px solid #e8e8e8;
        border-radius:12px;
        overflow:visible !important;
    }
        .media-tabs{
            border-bottom:1px solid #e9ecef;
            padding:0 14px;
            margin-bottom:0;
            gap:8px;
            flex-wrap:nowrap;
            overflow-x:auto;
            white-space:nowrap;
        }

        .media-tabs .nav-link{
            color:#222;
            font-size:15px;
            font-weight:500;
            padding:14px 6px;
            border:none;
            border-bottom:3px solid transparent;
            border-radius:0;
            background:transparent;
        }

        .media-tabs .nav-link.active{
            color:#0d6efd;
            border-bottom-color:#0d6efd;
        }

        .media-content-box{
            padding:20px 16px;
        }

        .media-title{
            font-size:24px;
            font-weight:600;
            margin-bottom:18px;
            color:#222;
        }

        .photo-category-slider{
            display:flex;
            gap:14px;
            overflow-x:auto;
            padding-bottom:6px;
        }

        .photo-card{
            min-width:165px;
            max-width:165px;
            cursor:pointer;
        }

        .photo-thumb{
            width:100%;
            height:115px;
            border-radius:14px;
            overflow:hidden;
            margin-bottom:10px;
            background:#f3f3f3;
        }

        .photo-thumb img{
            width:100%;
            height:100%;
            object-fit:cover;
            display:block;
        }

        .photo-card h6{
            font-size:16px;
            font-weight:600;
            margin-bottom:4px;
            color:#222;
        }

        .photo-card p{
            margin:0;
            font-size:13px;
            color:#777;
            line-height:1.4;
        }

        .contact-info-box{
            padding:18px 18px 16px;
            height:100%;
        }

        .contact-block h5{
            font-size:20px;
            font-weight:500;
            color:#111;
        }

        .contact-link{
            color:#0d6efd;
            font-size:15px;
            font-weight:500;
            text-decoration:none;
            display:inline-flex;
            align-items:center;
            gap:8px;
        }

        .address-text{
            font-size:15px;
            color:#222;
            line-height:1.7;
            margin-bottom:18px;
        }

        .address-actions{
            display:flex;
            gap:20px;
            flex-wrap:wrap;
        }

        .address-btn{
            font-size:14px;
            color:#0d6efd;
            text-decoration:none;
            display:inline-flex;
            align-items:center;
            gap:6px;
            font-weight:500;
        }

        .contact-info-box hr{
            margin:18px 0;
            border-color:#ececec;
        }

        .business-hours-box{
            background:#f3f3f3;
            border-radius:6px;
            margin-top:10px;
        }

        .hours-header{
            padding:12px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            cursor:pointer;
            font-weight:600;
        }

        .open-label{
            color:#2aa84a;
            display:flex;
            align-items:center;
            gap:6px;
        }

        .hours-list{
            display:none;
            padding:10px 12px;
        }

        .hours-row{
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            gap:12px;
            padding:6px 0;
            font-size:14px;
        }

        .day{
            font-weight:500;
            flex-shrink:0;
        }

        .time{
            color:#333;
            text-align:right;
        }

        @media(max-width:767px){
            .media-title{
                font-size:15px;
            }

            .contact-block h5{
                font-size:18px;
            }
        }

    /* --- relocated from inline <style> block (originally after line 5252) to prevent FOUC --- */
    .business-hours-box{
        background:#f3f3f3;
        border-radius:6px;
        margin-top:10px;
    }

    .hours-header{
        padding:12px;
        display:flex;
        justify-content:space-between;
        align-items:center;
        cursor:pointer;
        font-weight:600;
    }

    .open-label{
        color:#2aa84a;
        display:flex;
        align-items:center;
        gap:6px;
    }

    .hours-list{
        display:none;
        padding:10px 12px;
    }

    .hours-row{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:12px;
        padding:6px 0;
        font-size:14px;
    }

    .day{
        font-weight:500;
        flex-shrink:0;
    }

    .time{
        color:#333;
        text-align:right;
    }
        .listing-media-section {
            background: #fff;
        }

        .media-main-box,
        .contact-info-box {
            background: #fff;
            border: 1px solid #e8e8e8;
            border-radius: 12px;
            overflow: hidden;
        }

        .media-tabs {
            border-bottom: 1px solid #e9ecef;
            padding: 0 14px;
            margin-bottom: 0;
            gap: 8px;
            flex-wrap: nowrap;
            overflow-x: auto;
            white-space: nowrap;
        }

        .media-tabs .nav-link {
            color: #222;
            font-size: 15px;
            font-weight: 500;
            padding: 14px 6px;
            border: none;
            border-bottom: 3px solid transparent;
            border-radius: 0;
            background: transparent;
        }

        .media-tabs .nav-link.active {
            color: #0d6efd;
            border-bottom-color: #0d6efd;
        }

        .media-content-box {
            padding: 0px 10px;
        }

        .media-title {
            font-size: 20px;
            font-weight: 500;
            margin-bottom: 10px;
            color: #222;
        }

        .photo-category-slider {
            display: flex;
            gap: 14px;
            overflow-x: auto;
            padding-bottom: 6px;
            scrollbar-width: thin;
        }

        .photo-card {
            min-width: 165px;
            max-width: 165px;
            cursor: pointer;
        }

        .photo-thumb {
            width: 100%;
            height: 115px;
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 10px;
            background: #f3f3f3;
        }

        .photo-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .photo-card h6 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 4px;
            color: #222;
        }

        .photo-card p {
            margin: 0;
            font-size: 20px;
            color: #777;
            line-height: 1.4;
        }

        .contact-info-box {
            padding: 18px 18px 16px;
            height: 100%;
        }

        .contact-block h5 {
            font-size: 20px;
            font-weight: 500;
            color: #111;
        }

        .contact-link {
            color: #0d6efd;
            font-size: 15px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .contact-link:hover {
            color: #0b5ed7;
        }

        .address-text {
            font-size: 15px;
            color: #222;
            line-height: 1.7;
            margin-bottom: 10px;
        }

        .address-actions {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .address-btn {
            font-size: 18px;
            color: #0d6efd;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
        }

        .address-btn:hover {
            color: #0b5ed7;
        }

        .contact-info-box hr {
            margin: 10px 0;
            border-color: #ececec;
        }

        @media (max-width: 991px) {
            .contact-info-box {
                margin-top: 10px;
            }
        }

        @media (max-width: 767px) {
            .media-title {
                font-size: 15px;
            }

            .photo-card h6 {
                font-size: 17px;
            }

            .photo-card p,
            .address-text,
            .address-btn,
            .contact-link {
                font-size: 14px;
            }

            .contact-block h5 {
                font-size: 20px;
            }

            .photo-card {
                min-width: 145px;
                max-width: 145px;
            }

            .photo-thumb {
                height: 100px;
            }
        }

    /* --- relocated from inline <style> block (originally after line 5481) to prevent FOUC --- */

    h5{
        margin-bottom: 0rem;
    }
        .listing-card-section{
            background:#f8f9fa;
        }

        .listing-card{
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            border: 1px solid #e8e8e8 !important;
        }

        .listing-logo{
            width:290px;
            height:180px;
            border:1px solid #ddd;
            border-radius:10px;
            overflow:hidden;
            background:#fff;
            display:flex;
            align-items:center;
            justify-content:center;
        }

        .listing-logo img{
            width:100%;
            height:100%;
            object-fit:cover;
        }

        .listing-title{
            font-size:32px;
            font-weight:700;
            color:#0d1b2a;
        }

        .rating-badge{
            background:#1fae4b;
            color:#fff;
            font-weight:700;
            padding:3px 10px;
            border-radius:6px;
            font-size:14px;
            line-height:1;
        }

        .trust-badge{
            background:#ffd84d;
            color:#5a4300;
            font-weight:600;
            padding:3px 10px;
            border-radius:6px;
            font-size:13px;
        }

        .verified-text{
            color:#2b7de9;
            font-weight:700;
            font-size:15px;
        }

        

        .open-text{
            color:#149c43;
            font-weight:600;
        }

        .dot{
            width:5px;
            height:5px;
            border-radius:50%;
            background:#888;
            display:inline-block;
        }

        .phone-btn{
            background:#0a9b28;
            color:#fff;
            font-weight:700;
            border-radius:10px;
            padding:10px 18px;
            border:none;
        }

        .phone-btn:hover{
            background:#088420;
            color:#fff;
        }

        .enquire-btn{
            background:#0d6efd;
            color:#fff;
            font-weight:700;
            border-radius:10px;
            padding:8px 18px;
            border:none;
            line-height:1.1;
        }

        .enquire-btn small{
            font-size:12px;
            font-weight:500;
            opacity:.95;
        }

        .enquire-btn:hover{
            background:#0b5ed7;
            color:#fff;
        }

        .whatsapp-btn{
            background:#fff;
            color:#111;
            font-weight:700;
            border:2px solid #20b15a;
            border-radius:10px;
            padding:10px 18px;
        }

        .whatsapp-btn:hover{
            background:#20b15a;
            color:#fff;
        }

        .icon-btn{
            width:46px;
            height:46px;
            border-radius:10px;
            border:1px solid #d9d9d9;
            background:#fff;
            font-size:20px;
            display:flex;
            align-items:center;
            justify-content:center;
        }

        .category-pill{
            background:#f3f3f3;
            color:#666;
            font-size:13px;
            padding:5px 12px;
            border-radius:20px;
            white-space:nowrap;
        }

        .rate-box{
            min-width:220px;
        }

    .star-btn {
        width: 42px;
        justify-content: center;
        height: 42px;
        border: 1px solid #cfcfcf;
        background: #fff;
        border-radius: 10px;
        font-size: 22px;
        line-height: 1;
        display: flex;
        align-items: center;
    }

        @media (max-width: 991px){
            .listing-title{
                font-size:24px;
            }

            .rate-box{
                min-width:100%;
            }
        }

        @media (max-width: 767px){
            .listing-title{
                font-size:20px;
            }

            .listing-logo{
            width:100%;
            height:180px;   /* banner height */
            border-radius:12px;
        }

        .listing-logo img{
            width:100%;
            height:100%;
            object-fit:cover;   /* 🔥 important for cover image */
        }

            .star-btn{
                width:38px;
                height:38px;
                font-size:20px;
            }

            .phone-btn,
            .enquire-btn,
            .whatsapp-btn{
                width:100%;
                text-align:center;
            }
        }
        

    /* --- relocated from inline <style> block (originally after line 7084) to prevent FOUC --- */
        body{
        overflow-x:hidden;
    }
        </style>
    @endpush

    @php

        $locationText = optional($listing->location)->name ?? '';

        $categoryText = $listing->category->name ?? 'Listings';
        $categorySlug = $listing->category->slug ?? '';

        $listingName = $listing->name ?? '';

    @endphp

    <div class="listing-top-info custom-right {{ isset($banners) && $banners->count() > 0 ? 'has-banner' : 'no-banner' }}">

        <div class="listing-breadcrumb">

            @if(!empty($locationText))

                <a href="{{ route('home') }}" class="breadcrumb-location-link">
                    {{ $locationText }}
                </a>

                <span class="sep">&gt;</span>

            @endif

            <a href="{{ url(\Illuminate\Support\Str::slug($locationText) . '/' . $categorySlug) }}"
            class="breadcrumb-category-link">
                {{ $categoryText }}
            </a>

            <span class="sep">&gt;</span>

            <span>{{ $listingName }}</span>

        </div>

    </div>

    <section class="listing-card-section py-1 custom-right" id="listingTopCard">
        <div class="container-fuled">
            <div class="listing-card bg-white border rounded p-3 p-md-4">

                <div class="row">
                    
                    <!-- Left Image -->
                <div class="col-12 col-md-auto">
        <div class="listing-logo">
            <img src="{{ loadImage('assets/images/listing/thumb/'.$listing->thumb) }}"
                class="img-fluid listing-photo-click"
                data-src="{{ loadImage('assets/images/listing/thumb/'.$listing->thumb) }}"
            alt="{{ $listing->name }} Logo" fetchpriority="high">

        </div>
    </div>


                    {{-- Middle Content --}}
                    <div class="col-md">
                        <div class="d-md-flex justify-content-between">

                            <div class="mb-3 mb-md-0">
                                <h1 class="mb-2">
                                    
                                    @if($listing->is_star == 1)
        <i class="fas fa-star ico"></i>
        @endif

                                    {{ __($listing->name) }}</h1>

                            @php
        $rating = 0;
        $ratingCount = 0;

        if (!empty($listing->review) && is_numeric($listing->review)) {
            $rating = (float) $listing->review;
        } elseif (!empty($listing->avg_rating) && is_numeric($listing->avg_rating)) {
            $rating = (float) $listing->avg_rating;
        } elseif (!empty($listing->rating) && is_numeric($listing->rating)) {
            $rating = (float) $listing->rating;
        }

        if (!empty($listing->total_review) && is_numeric($listing->total_review)) {
            $ratingCount = (int) $listing->total_review;
        } elseif (!empty($listing->review_count) && is_numeric($listing->review_count)) {
            $ratingCount = (int) $listing->review_count;
        } elseif (!empty($listing->ratings_count) && is_numeric($listing->ratings_count)) {
            $ratingCount = (int) $listing->ratings_count;
        }
    @endphp

    {{-- jd-rating-line1/2 are display:contents by default (see CSS), so on
         tablet/desktop these wrappers are invisible to layout and all four
         items render in .jd-rating-wrap's single flex row exactly as
         before. Only the mobile media query turns them into two real
         stacked rows: rating+count, then trust+verified. --}}
    <div class="jd-rating-wrap mb-2 d-flex align-items-center flex-wrap">
        <div class="jd-rating-line1">
            @if($rating > 0)
                <a href="#reviewsSection" class="jd-rating-badge rating-scroll-link">
                    {{ number_format($rating, 1) }} ★
                </a>
            @endif
            @if($ratingCount > 0)
                <span class="jd-rating-count">
                    {{ $ratingCount }} Rating
                </span>
            @endif
        </div>

        <div class="jd-rating-line2">
            @if($listing->trust == 1)
                <img src="{{ asset('assets/images/trust_2x.gif') }}" class="jd-badge-img" alt="Trusted Business">
            @endif

            @if($listing->verified == 1)
                <img src="{{ asset('assets/images/verified_2x.gif') }}" class="jd-badge-img" alt="Verified Business">
            @endif
        </div>

        @if(!empty($listing->registration_number))
            {{-- Public credential for professional/YMYL listings (doctors, lawyers, CAs) --}}
            <div class="jd-credential" style="margin-top:8px;font-size:13px;color:#1a7f4b;display:flex;align-items:center;gap:6px;">
                <i class="las la-certificate" aria-hidden="true"></i>
                <span><strong>{{ $listing->registration_label ?: 'Registration No.' }}:</strong> {{ $listing->registration_number }}</span>
            </div>
        @endif
    </div>

                                <div class="listing-location text-muted d-flex align-items-center flex-wrap gap-2">

    <span class="listing-location-tooltip">
        <i class="las la-map-marker"></i>
    {{ $listing->area }}, {{ optional($listing->location)->name }}
    @php
        $fullAddress = $listing->address;

        if (empty($fullAddress)) {
            $fullAddress = collect([
                $listing->plot_no,
                $listing->building_name,
                $listing->street_name,
                $listing->landmark,
                $listing->area,
                $listing->city,
                $listing->state,
                $listing->pincode,
            ])->filter()->implode(', ');
        }

        // "Get Directions" / Direction buttons: prefer the exact pin the owner
        // saved on the map (latitude/longitude) so it points at their real
        // spot; fall back to an address text search only when no pin exists.
        $sbDirectionsUrl = ($listing->latitude && $listing->longitude)
            ? 'https://www.google.com/maps/dir/?api=1&destination='.$listing->latitude.','.$listing->longitude
            : 'https://www.google.com/maps/search/?api=1&query='.urlencode($fullAddress);
    @endphp
        <div class="address-tooltip">
        {{ $fullAddress ?: 'Address not available.' }}
        </div>
    </span>

    @if(!empty($listing->established_year))
                                @php
                                    $yearsInBusiness = date('Y') - (int) $listing->established_year;
                                @endphp

                                @if($yearsInBusiness > 0)
                                    <span class="dot-separator">•</span>
                                    <span class="business-years">
                                        <i class="las la-briefcase"></i>
                                        {{ $yearsInBusiness }} Years in Business
                                    </span>
                                @endif
                            @endif

    </div>

    {{-- Mobile-only category + business-age row: a plain-text duplicate of
         (a) the desktop category badge further down this card
         (span.badge.badge-light in .text-md-right) and (b) the
         "Years in Business" text already inside .listing-location above —
         combined on their own row here instead. Hidden by default / shown
         only at max-width:768px below; the desktop badge and the
         .listing-location copy of the business-age text are both hidden
         at that same breakpoint so nothing is ever shown twice. No
         interactivity in either piece, so duplicating this plain text is
         safe — nothing to keep in sync functionally. --}}
    <div class="jd-meta-row">
        <span class="jd-category-chip">
            {{ $listing->category->name ?? 'No Category' }}
        </span>

        @if(!empty($listing->established_year))
            @php
                $jdYearsInBusiness = date('Y') - (int) $listing->established_year;
            @endphp

            @if($jdYearsInBusiness > 0)
                <span class="jd-meta-dot">•</span>
                <span class="jd-business-age">
                    <i class="las la-briefcase"></i>
                    {{ $jdYearsInBusiness }} Years in Business
                </span>
            @endif
        @endif
    </div>

                                @php
        use Carbon\Carbon;

        $businessDays = is_array($listing->business_days)
            ? $listing->business_days
            : (json_decode($listing->business_days, true) ?? []);

        $openTimes = is_array($listing->open_time)
            ? $listing->open_time
            : (json_decode($listing->open_time, true) ?? []);

        $closeTimes = is_array($listing->close_time)
            ? $listing->close_time
            : (json_decode($listing->close_time, true) ?? []);

        $today = Carbon::now('Asia/Kolkata')->format('D');
        $jdNow = Carbon::now('Asia/Kolkata');

        $jdFormatTime = function ($time) {
            if (empty($time)) {
                return null;
            }

            try {
                return Carbon::parse($time)->format('g:i A');
            } catch (\Exception $e) {
                return $time;
            }
        };

        /*
         * $businessDays / $openTimes / $closeTimes are parallel arrays and a
         * day can repeat — the timing form allows a second shift for the
         * same day (lunch-break hours), and each repeat is one more shift
         * at the same array index in the other two arrays. Grouped by day
         * here so "Open Now", "Opens at ...", and the weekly list all treat
         * every shift a day has, not just the first one found.
         */
        $jdShiftsByDay = [];

        foreach ($businessDays as $jdIndex => $jdDay) {
            $jdDay = is_string($jdDay) ? trim($jdDay) : $jdDay;

            if (empty($jdDay)) {
                continue;
            }

            $jdOpenRaw = count($openTimes) === 1 ? ($openTimes[0] ?? null) : ($openTimes[$jdIndex] ?? null);
            $jdCloseRaw = count($closeTimes) === 1 ? ($closeTimes[0] ?? null) : ($closeTimes[$jdIndex] ?? null);

            if (empty($jdOpenRaw) || empty($jdCloseRaw)) {
                continue;
            }

            $jdShiftsByDay[$jdDay][] = ['open' => $jdOpenRaw, 'close' => $jdCloseRaw];
        }

        $isOpenToday = !empty($jdShiftsByDay[$today]);

        $jdDayTimeText = function ($day) use ($jdShiftsByDay, $jdFormatTime) {
            if (empty($jdShiftsByDay[$day])) {
                return 'Closed';
            }

            $ranges = [];

            foreach ($jdShiftsByDay[$day] as $shift) {
                $o = $jdFormatTime($shift['open']);
                $c = $jdFormatTime($shift['close']);

                if (empty($o) || empty($c)) {
                    continue;
                }

                $ranges[] = (strtoupper($o) === '12:00 AM' && strtoupper($c) === '11:59 PM')
                    ? 'Open 24 Hours'
                    : ($o . ' - ' . $c);
            }

            return $ranges ? implode(', ', $ranges) : 'Closed';
        };

        // Today's full text for the always-visible header, e.g.
        // "Open 9:00 AM - 1:00 PM, 5:00 PM - 9:00 PM".
        $formattedOpenTime = null;
        $formattedCloseTime = null;

        if ($isOpenToday) {
            $firstShiftToday = $jdShiftsByDay[$today][0];
            $formattedOpenTime = $jdFormatTime($firstShiftToday['open']);
            $formattedCloseTime = $jdFormatTime($firstShiftToday['close']);
        }

        $todayHoursText = $isOpenToday ? $jdDayTimeText($today) : null;

        /*
         * Mobile-only Justdial-style "Open Now / Closed Now" status —
         * unlike $isOpenToday above (which only checks whether today is a
         * business day at all), this checks the current clock time against
         * EVERY one of today's shifts, so a lunch-break gap between two
         * shifts correctly reads as closed. Handles overnight windows
         * (close time earlier than open time) by rolling the close
         * timestamp to the next day before comparing. $jdActiveShiftClose
         * remembers which shift matched, for the "Until ..." detail text.
         */
        $isOpenNow = false;
        $jdActiveShiftClose = null;

        if ($isOpenToday) {
            foreach ($jdShiftsByDay[$today] as $shift) {
                try {
                    $jdToday = $jdNow->format('Y-m-d');
                    $jdOpenAt = Carbon::parse($jdToday . ' ' . $shift['open'], 'Asia/Kolkata');
                    $jdCloseAt = Carbon::parse($jdToday . ' ' . $shift['close'], 'Asia/Kolkata');

                    if ($jdCloseAt->lessThanOrEqualTo($jdOpenAt)) {
                        $jdCloseAt->addDay();
                    }

                    if ($jdNow->betweenIncluded($jdOpenAt, $jdCloseAt)) {
                        $isOpenNow = true;
                        $jdActiveShiftClose = $jdFormatTime($shift['close']);
                        break;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        if ($isOpenNow) {
            $formattedCloseTime = $jdActiveShiftClose;
        }

        /*
         * "Opens at ..." (closed state only): the next upcoming opening
         * time — today's own later shift (past a lunch break) counts
         * before scanning into future days. Today only counts a shift if
         * its opening time hasn't already passed.
         */
        $jdNextOpenTime = null;

        if (!$isOpenNow) {
            for ($jdOffset = 0; $jdOffset <= 7; $jdOffset++) {
                $jdCheckDate = $jdNow->copy()->addDays($jdOffset);
                $jdCheckDay = $jdCheckDate->format('D');

                if (empty($jdShiftsByDay[$jdCheckDay])) {
                    continue;
                }

                foreach ($jdShiftsByDay[$jdCheckDay] as $shift) {
                    if (empty($shift['open'])) {
                        continue;
                    }

                    if ($jdOffset === 0) {
                        try {
                            $jdCheckOpenAt = Carbon::parse($jdCheckDate->format('Y-m-d') . ' ' . $shift['open'], 'Asia/Kolkata');
                        } catch (\Exception $e) {
                            continue;
                        }

                        if ($jdCheckOpenAt->lessThanOrEqualTo($jdNow)) {
                            continue;
                        }
                    }

                    $jdNextOpenTime = $jdFormatTime($shift['open']);
                    break 2;
                }
            }
        }

        // Fixed Mon-Sun order for the expandable weekly list, independent
        // of the order days happen to be stored in $businessDays.
        $jdWeekOrder = [
            'Mon' => 'Monday',
            'Tue' => 'Tuesday',
            'Wed' => 'Wednesday',
            'Thu' => 'Thursday',
            'Fri' => 'Friday',
            'Sat' => 'Saturday',
            'Sun' => 'Sunday',
        ];
    @endphp

    <span class="jd-hours-old">
    <i class="las la-clock mqa-hours-icon" aria-hidden="true"></i>
    <span class="ml-2">
        @if(!$isOpenToday)
            Closed Today
        @elseif($todayHoursText === 'Open 24 Hours')
            Open 24 Hours
        @elseif($todayHoursText)
            Open {{ $todayHoursText }}
        @else
            Hours not available
        @endif
    </span>
    </span>

    {{-- Mobile-only Justdial-style expandable status row — replaces the
         plain "Open 9:00 AM - 6:00 PM" text (.jd-hours-old, above) on
         mobile only; .jd-hours-old stays visible as before on tablet/
         desktop. Uses the same $businessDays / $openTimes / $closeTimes
         data as the rest of this card — no hardcoded timings. --}}
    <div class="jd-status-row">
        <button type="button" class="jd-status-toggle" id="jdStatusToggle" aria-expanded="false" aria-controls="jdHoursPanel">
            <span class="jd-status-dot {{ $isOpenNow ? 'is-open' : 'is-closed' }}"></span>
            <span class="jd-status-text {{ $isOpenNow ? 'is-open' : 'is-closed' }}">
                {{ $isOpenNow ? 'Open Now' : 'Closed Now' }}
            </span>
            <span class="jd-status-detail">
                @if($isOpenNow && $formattedCloseTime)
                    : Until {{ $formattedCloseTime }}
                @elseif(!$isOpenNow && $jdNextOpenTime)
                    : Opens at {{ $jdNextOpenTime }}
                @endif
            </span>
            <i class="las la-angle-down jd-status-arrow" aria-hidden="true"></i>
        </button>

        <div class="jd-hours-panel" id="jdHoursPanel">
            <div class="jd-hours-panel-inner">
                @foreach($jdWeekOrder as $jdDayKey => $jdDayLabel)
                    <div class="jd-hours-row {{ $jdDayKey === $today ? 'is-today' : '' }}">
                        <span class="jd-hours-day">{{ $jdDayLabel }}</span>
                        <span class="jd-hours-time">
                            {{ $jdDayTimeText($jdDayKey) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

                                    @php
                                    
                                        $created = Carbon::parse($listing->created_at);
                                        $diff = $created->diff(Carbon::now());
                                    @endphp
                                

                            <div class="listing-action-buttons">


        <!-- CALL BUTTON -->
        @if(!empty($listing->mobile_numbers[0]))
    <a href="tel:{{ $listing->mobile_numbers[0] }}"
    class="listing-btn btn-call-main track-click"
    data-track="call_click"
    data-type="call"
    data-listing-id="{{ $listing->id }}"
    data-category-id="{{ $listing->category_id ?? optional($listing->category)->id ?? '' }}"
    data-location-id="{{ $listing->location_id ?? optional($listing->location)->id ?? '' }}"
    data-number="{{ $listing->mobile_numbers[0] }}">

        <img src="{{ asset('assets/download.svg') }}" class="call-icon" alt="Call">

        {{ $listing->mobile_country_code[0] ?? '+91' }}{{ $listing->mobile_numbers[0] }}
    </a>
    @endif


    @if(!empty($listing->landline_numbers[0]))
    <a href="tel:{{ $listing->landline_numbers[0] }}"
    class="listing-btn btn-landline-main track-click"
    data-type="landline"
    data-listing-id="{{ $listing->id }}"
    data-number="{{ $listing->landline_numbers[0] }}">

    <i class="las la-headset"></i>

        {{ $listing->landline_country_code[0] ?? '+91' }}
        {{ $listing->landline_numbers[0] }}
    </a>
    @endif

    @if(!empty($listing->whatsapp_numbers[0]))
    <a href="https://wa.me/91{{ $listing->whatsapp_numbers[0] ?? '' }}"
    target="_blank"
    class="listing-btn btn-whatsapp-main track-click"
    data-track="whatsapp_click"
    data-type="whatsapp"
    data-listing-id="{{ $listing->id }}"
    data-number="{{ $listing->whatsapp_numbers[0] ?? '' }}">

        <img src="{{ asset('assets/wtseappp.svg') }}" class="whatsapp-icon" alt="WhatsApp">

        WhatsApp
    </a>
    @endif

        <!-- ENQUIRY BUTTON -->
    <button type="button"
            class="listing-btn btn-best-price open-enquiry-modal track-click"
            data-type="enquiry"
            data-listing-id="{{ $listing->id }}"
            data-category-id="{{ $listing->category_id ?? optional($listing->category)->id ?? '' }}"
            data-category="{{ optional($listing->category)->name ?? '' }}"
            data-location-id="{{ $listing->location_id ?? optional($listing->location)->id ?? '' }}"
            data-location="{{ optional($listing->location)->name ?? $listing->city ?? '' }}">

        <img src="{{ asset('assets/2.svg') }}" class="btn-icon" alt="Best Price">

        Get Best Price
    </button>
    <div class="share-hover-wrap">

        <button type="button" style="cursor: pointer;" class="listing-btn share-btn-icon">
    <img src="{{ asset('assets/images/download.svg') }}" alt="">
        </button>

        <div class="share-hover-popup">

            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
            target="_blank"
            class="share-option"
            data-track="share_click"
            data-listing-id="{{ $listing->id }}"
            data-track-meta='{"medium":"facebook"}'>
                <span class="share-icon fb">
                    <i class="lab la-facebook-f"></i>
                </span>
                <small>Facebook</small>
            </a>

            <a href="https://wa.me/?text={{ urlencode(url()->current()) }}"
            target="_blank"
            class="share-option"
            data-track="share_click"
            data-listing-id="{{ $listing->id }}"
            data-track-meta='{"medium":"whatsapp"}'>
                <span class="share-icon wa">
                    <i class="lab la-whatsapp"></i>
                </span>
                <small>Whatsapp</small>
            </a>

            <button type="button"
                    class="share-option copy-listing-link"
                    data-track="share_click"
                    data-listing-id="{{ $listing->id }}"
                    data-track-meta='{"medium":"copy_link"}'
                    data-link="{{ url()->current() }}">
                <span class="share-icon cp">
                    <i class="las la-link"></i>
                </span>
                <small>Copy Link</small>
            </button>

        </div>

    </div>

    {{-- Edit Button - Only visible to listing owner --}}
    @if(auth()->check() && auth()->id() === $listing->user_id)
        <a href="{{ route('user.listing.manage', $listing->id) }}"
           class="listing-btn edit-listing-btn"
           title="Edit Listing">
            @php
                $editIconPath = 'assets/images/editicon';
                $editIconExt = '';
                // Check for uploaded edit icon with any extension
                foreach(['png', 'svg', 'jpg', 'jpeg', 'gif'] as $ext) {
                    if (file_exists(public_path($editIconPath . '.' . $ext))) {
                        $editIconExt = $ext;
                        break;
                    }
                }
            @endphp
            @if($editIconExt)
                <img src="{{ asset($editIconPath . '.' . $editIconExt) }}" alt="Edit" class="edit-icon-img">
            @else
                <i class="las la-edit"></i>
            @endif
        </a>
    @endif
    </div>
                            </div>

                            {{--
                                Mobile-only redesigned action row (reference-screenshot match).
                                Reuses the exact same hrefs/classes/data-attributes as the
                                buttons above (tel:, wa.me, open-enquiry-modal, share popup,
                                track-click analytics) so nothing functional changes — this
                                is purely an additional, mobile-only presentation of the same
                                actions. The original ".listing-action-buttons" block above
                                stays in the DOM untouched and is only hidden on mobile via
                                CSS; both blocks are shown/hidden with a media query, never
                                removed. Direction reuses the same Google Maps search URL as
                                the "Get Directions" link further down this page.
                            --}}
                            <div class="mobile-quick-actions">

                                @if(!empty($listing->mobile_numbers[0]))
                                    <a href="tel:{{ $listing->mobile_numbers[0] }}"
                                       class="mqa-btn mqa-call track-click"
                                       data-track="call_click"
                                       data-type="call"
                                       data-listing-id="{{ $listing->id }}"
                                       data-category-id="{{ $listing->category_id ?? optional($listing->category)->id ?? '' }}"
                                       data-location-id="{{ $listing->location_id ?? optional($listing->location)->id ?? '' }}"
                                       data-number="{{ $listing->mobile_numbers[0] }}">
                                        <span class="mqa-icon"><i class="las la-phone"></i></span>
                                        <span class="mqa-label">Call Now</span>
                                    </a>
                                @endif

                                @if(!empty($listing->whatsapp_numbers[0]))
                                    <a href="https://wa.me/91{{ $listing->whatsapp_numbers[0] ?? '' }}"
                                       target="_blank"
                                       class="mqa-btn mqa-whatsapp track-click"
                                       data-track="whatsapp_click"
                                       data-type="whatsapp"
                                       data-listing-id="{{ $listing->id }}"
                                       data-number="{{ $listing->whatsapp_numbers[0] ?? '' }}">
                                        <span class="mqa-icon"><i class="lab la-whatsapp"></i></span>
                                        <span class="mqa-label">WhatsApp</span>
                                    </a>
                                @endif

                                <a href="{{ $sbDirectionsUrl }}"
                                   target="_blank"
                                   class="mqa-btn mqa-direction track-click"
                                   data-track="direction_click"
                                   data-listing-id="{{ $listing->id }}">
                                    <span class="mqa-icon"><i class="las la-map-marker"></i></span>
                                    <span class="mqa-label">Direction</span>
                                </a>

                                <div class="mqa-btn mqa-share share-hover-wrap">
                                    <button type="button" class="mqa-share-trigger" style="cursor:pointer;">
                                        <span class="mqa-icon"><img src="{{ asset('assets/images/download.svg') }}" alt="Share" class="mqa-share-svg"></span>
                                        <span class="mqa-label">Share</span>
                                    </button>

                                    <div class="share-hover-popup">

                                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                                        target="_blank"
                                        class="share-option"
                                        data-track="share_click"
                                        data-listing-id="{{ $listing->id }}"
                                        data-track-meta='{"medium":"facebook"}'>
                                            <span class="share-icon fb">
                                                <i class="lab la-facebook-f"></i>
                                            </span>
                                            <small>Facebook</small>
                                        </a>

                                        <a href="https://wa.me/?text={{ urlencode(url()->current()) }}"
                                        target="_blank"
                                        class="share-option"
                                        data-track="share_click"
                                        data-listing-id="{{ $listing->id }}"
                                        data-track-meta='{"medium":"whatsapp"}'>
                                            <span class="share-icon wa">
                                                <i class="lab la-whatsapp"></i>
                                            </span>
                                            <small>Whatsapp</small>
                                        </a>

                                        <button type="button"
                                                class="share-option copy-listing-link"
                                                data-track="share_click"
                                                data-listing-id="{{ $listing->id }}"
                                                data-track-meta='{"medium":"copy_link"}'
                                                data-link="{{ url()->current() }}">
                                            <span class="share-icon cp">
                                                <i class="las la-link"></i>
                                            </span>
                                            <small>Copy Link</small>
                                        </button>

                                    </div>
                                </div>

                                {{-- Edit Button - Only visible to listing owner (mobile) --}}
                                @if(auth()->check() && auth()->id() === $listing->user_id)
                                    @php
                                        $editIconPath = 'assets/images/editicon';
                                        $editIconExt = '';
                                        // Check for uploaded edit icon with any extension
                                        foreach(['png', 'svg', 'jpg', 'jpeg', 'gif'] as $ext) {
                                            if (file_exists(public_path($editIconPath . '.' . $ext))) {
                                                $editIconExt = $ext;
                                                break;
                                            }
                                        }
                                    @endphp
                                    <a href="{{ route('user.listing.manage', $listing->id) }}"
                                       class="mqa-btn mqa-edit"
                                       title="Edit Listing">
                                        <span class="mqa-icon">
                                            @if($editIconExt)
                                                <img src="{{ asset($editIconPath . '.' . $editIconExt) }}" alt="Edit" class="mqa-edit-icon-img">
                                            @else
                                                <i class="las la-edit"></i>
                                            @endif
                                        </span>
                                        <span class="mqa-label">Edit</span>
                                    </a>
                                @endif

                            </div>

                            <button type="button"
                                    class="mqa-enquire-btn open-enquiry-modal track-click"
                                    data-type="enquiry"
                                    data-listing-id="{{ $listing->id }}"
                                    data-category-id="{{ $listing->category_id ?? optional($listing->category)->id ?? '' }}"
                                    data-category="{{ optional($listing->category)->name ?? '' }}"
                                    data-location-id="{{ $listing->location_id ?? optional($listing->location)->id ?? '' }}"
                                    data-location="{{ optional($listing->location)->name ?? $listing->city ?? '' }}">
                                Enquire Now
                                <small>Responds in 5 minutes</small>
                            </button>

                            {{-- Right Content --}}
                <div class="text-md-right d-flex flex-column justify-content-center align-items-center">
                                <div class="mb-4">
                                    <span class="badge badge-light p-2">
                                        {{ $listing->category->name ?? 'No Category' }}
                                    </span>

                                
                                </div>

                            <div class="rate-box jd-rate-box">

        @php
            $userRatingData = auth()->check()
                ? $listing->ratings->firstWhere('user_id', auth()->id())
                : null;

            $userRating = $userRatingData ? (int)$userRatingData->ratings : 0;
        @endphp

        @if(!auth()->check() || $listing->user_id != auth()->id())

            <form id="quickRatingForm">
                @csrf

                <input type="hidden" name="listing_id" value="{{ $listing->id }}">
                <input type="hidden" name="ratings" id="quick_rating_value" value="{{ $userRating }}">
                <input type="hidden" name="review" id="quick_review_text">

                <div class="jd-rate-title">
                    {{ $userRating > 0 ? 'You rated' : 'Click to Rate' }}
                </div>

                <div class="jd-rate-stars" id="quickRatingStars">

                    <span class="jd-star star-btn {{ $userRating >= 1 ? 'active' : '' }}" data-value="1">★</span>

                    <span class="jd-star star-btn {{ $userRating >= 2 ? 'active' : '' }}" data-value="2">★</span>

                    <span class="jd-star star-btn {{ $userRating >= 3 ? 'active' : '' }}" data-value="3">★</span>

                    <span class="jd-star star-btn {{ $userRating >= 4 ? 'active' : '' }}" data-value="4">★</span>

                    <span class="jd-star star-btn {{ $userRating >= 5 ? 'active' : '' }}" data-value="5">★</span>

                </div>

                <small class="jd-rate-text" id="quickRatingText">
                    {{ $userRating > 0 ? 'You already rated this listing' : 'Tap stars to rate' }}
                </small>

            </form>

        @endif

    </div>
                            </div>

                        </div>
                    </div>

                </div>



                
            </div>


        </div>
    </section>

    @push('css')
    <style>
        /* ============================================================
           Mobile-only layout for the listing top card + tabs
           (max-width: 768px). CSS-only, reuses existing classes/markup.
           Every rule below is scoped inside this media query, so
           desktop/tablet (>768px) rendering is untouched.
        ============================================================ */
        @media (max-width: 768px) {

            /*
             * Fix: on this page (and only this page — verified against the
             * category listing page, where the same shared mobile search
             * bar renders correctly), the mobile search overlay's
             * `<form class="yt-search-form">` (see .youtube-mobile-search /
             * .yt-search-form, layouts/site.blade.php) picks up a stray
             * default margin-bottom of 16px — the shared layout never
             * resets margins on the bare `form` element. That extra 16px
             * inflates the flex row's cross-axis size from 48px to 64px
             * (verified in devtools) and, since the row never centers its
             * children, dumps all of it as blank space below the search
             * bar — which is what pushed it up and away from center.
             * Zeroing that margin (scoped to this page and to
             * max-width:768px only) removes the stray space at its
             * source: the row goes back to its natural 48px — identical
             * to every other mobile page — with the search bar already
             * flush and centered, no height/position overrides needed.
             * Tablet/desktop don't use this overlay at all, so they're
             * untouched.
             */
            body.mobile-yt-search-open .yt-search-form {
                margin: 0;
            }

            /* Header: full-width business photo on top, business info
               (name, badges, location) stacked full-width below it. */
            #listingTopCard .listing-card .row {
                display: block;
            }

            #listingTopCard .listing-card .col-12.col-md-auto {
                float: none;
                width: 100% !important;
                flex: none;
                margin: 0 0 14px 0;
            }

            #listingTopCard .listing-card .col-md {
                width: 100%;
                max-width: 100%;
                flex: none;
            }

            #listingTopCard h1 {
                font-size: 17px;
                line-height: 1.35;
                margin-bottom: 6px;
            }

            #listingTopCard .jd-rating-wrap {
                margin-bottom: 6px;
            }

            #listingTopCard .jd-rating-badge,
            #listingTopCard .jd-rating-count {
                font-size: 12px;
            }

            #listingTopCard .jd-badge-img {
                height: 16px;
            }

            #listingTopCard .listing-location {
                font-size: 12px;
                margin-bottom: 4px;
            }

            #listingTopCard .mqa-hours-icon {
                display: inline-block;
                font-size: 15px;
                color: #666;
                vertical-align: middle;
                line-height: 1;
                margin-right: 4px;
            }

            #listingTopCard .business-years {
                font-size: 12px;
            }

            /* The original desktop action-buttons row is hidden (not
               removed) on mobile — replaced visually by the dedicated
               .mobile-quick-actions block below, which is shown only
               inside this same media query. */
            #listingTopCard .listing-action-buttons {
                display: none !important;
            }

            /* ---- Reference-match action row: 4 evenly-spaced circular
               icon buttons + a full-width blue "Enquire Now" CTA ---- */
            #listingTopCard .mobile-quick-actions {
                display: flex;
                justify-content: space-between;
                gap: 6px;
                width: 100%;
                margin-top: 16px;
                border-radius: 10px;
                /* Positioning context for .share-hover-popup — see note
                   below on why it's centered against this row rather than
                   the narrow Share button itself. */
                position: relative;
            }

            #listingTopCard .mqa-btn {
                flex: 1 1 0;
                min-width: 0;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: flex-start;
                gap: 6px;
                text-decoration: none;
                border: 0;
                border-radius: 10px;
                background: transparent;
                padding: 0;
                cursor: pointer;
            }

            #listingTopCard .mqa-icon {
                width: 44px;
                height: 44px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 19px;
                line-height: 1;
                color: #fff;
            }

            #listingTopCard .mqa-share-svg {
                width: 20px;
                height: 20px;
            }

            #listingTopCard .mqa-label {
                font-size: 11px;
                font-weight: 500;
                color: #333;
                white-space: nowrap;
            }

            #listingTopCard .mqa-call .mqa-icon {
                background: #18a63b;
            }

            #listingTopCard .mqa-whatsapp .mqa-icon {
                background: #25d366;
            }

            #listingTopCard .mqa-direction .mqa-icon {
                background: #2f6fed;
            }

            #listingTopCard .mqa-share .mqa-icon {
                background: transparent !important;
                background-color: transparent !important;
                background-image: none !important;
                color: #555;
                border: 1.5px solid #d9d9d9;
                box-sizing: border-box;
            }

            #listingTopCard .mqa-edit .mqa-icon {
                background: #0d6efd;
            }

            /* .mqa-share-trigger is a real <button>, which browsers give a
               default background/border/padding — unlike the Call/WhatsApp/
               Direction <a> tags, it never picked up .mqa-btn's reset since
               it's nested one level inside the .mqa-btn wrapper. Reset it
               explicitly so no box/background shows behind the icon. */
            #listingTopCard .mqa-share-trigger {
                width: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 6px;
                background: transparent !important;
                background-color: transparent !important;
                border: 0;
                padding: 0;
                margin: 0;
                appearance: none;
                -webkit-appearance: none;
            }

            /* The popup is anchored to the whole action row's positioning
               context (not the 81px-wide Share button itself — see the
               .mqa-share position:static note below), then right-aligned
               within it. Share is the rightmost item in the row, flush
               with the row's right edge, so right:0 here lines the
               popup's right edge up with Share's right edge and lets it
               expand leftward from there — landing on WhatsApp/Direction,
               never reaching Call Now (row is 341px, popup is 235px, so
               94px of clearance remains before Call Now's right edge).
               Centering it against the row (left:50%) previously put the
               popup's LEFT edge past Call Now's position instead, which is
               the overlap this replaces.
               .mqa-share also carries the sitewide .share-hover-wrap class,
               which sets position:relative at every viewport width — that
               was silently keeping it as the popup's positioned ancestor
               even after .mobile-quick-actions became position:relative
               above, so it has to be reset back to static here for the
               row's positioning context to actually take effect. */
            #listingTopCard .mqa-share {
                position: static;
            }

            #listingTopCard .mqa-share .share-hover-popup {
                left: auto;
                right: 0;
                transform: none;
            }

            /* Base .share-hover-popup:before pointer defaults to left:18px,
               which pointed at the old left-anchored popup's corner — with
               the popup now right-anchored, move the pointer to sit under
               the Share icon's center instead so it still visually points
               at the button that opened it. */
            #listingTopCard .mqa-share .share-hover-popup:before {
                left: auto;
                right: 40px;
                transform: none;
            }

            #listingTopCard .mqa-enquire-btn {
                display: block;
                width: 100%;
                margin-top: 12px;
                /* Gap before the "Click to Rate" section (.text-md-right /
                   .rate-box) that immediately follows this button in the
                   DOM — it had no spacing of its own, so the two were
                   touching. */
                margin-bottom: 16px;
                background: #0d6efd;
                color: #fff;
                border: 0;
                border-radius: 10px;
                /* Slightly shorter button: less top/bottom padding than
                   before (was 12px/10px). Font sizes below (15px main,
                   11px .small) are unchanged. */
                padding: 9px 10px 7px;
                text-align: center;
                font-size: 15px;
                font-weight: 700;
                line-height: 1.3;
            }

            #listingTopCard .mqa-enquire-btn small {
                display: block;
                font-size: 11px;
                font-weight: 400;
                opacity: .9;
                margin-top: 2px;
            }

            #listingTopCard .mqa-enquire-btn:active {
                background: #0b5ed7;
            }

            /* Category badge / quick-rate widget: keep it (not part of the
               reference crop) but let it flow full width below the buttons
               instead of the desktop right-column treatment. */
            #listingTopCard .text-md-right {
                width: 100%;
                align-items: flex-start !important;
            }

            #listingTopCard .rate-box {
                min-width: 100%;
            }

            /* Reference match: rating split into two rows, address alone
               on its own row (original hover tooltip untouched), a
               combined category + business-age row, and a Justdial-style
               expandable "Open Now : Until ..." status — see
               .jd-rating-line1/2, .jd-meta-row, .jd-status-row markup in
               the card above, and the default-hidden rules for the
               mobile-only pieces (and for .jd-hours-old) further below. */

            /* 1) Rating: two stacked rows — (rating + count), then
               (trust + verified). .jd-rating-line1/2 are display:contents
               by default (see the non-mobile rule further below), so this
               is the only place they become real flex rows; .jd-rating-wrap
               itself switches to a column so the two rows stack. */
            #listingTopCard .jd-rating-wrap {
                flex-direction: column;
                align-items: flex-start !important;
                flex-wrap: nowrap !important;
                gap: 4px;
            }

            #listingTopCard .jd-rating-line1,
            #listingTopCard .jd-rating-line2 {
                display: flex;
                align-items: center;
                flex-wrap: nowrap;
                gap: 6px;
            }

            /* 2) Address: .listing-location now shows only the address
               tooltip trigger on mobile — the "years in business" piece
               that used to share this row moves to .jd-meta-row below
               instead, so it's hidden here (not removed) to avoid showing
               it twice. Deliberately no overflow/white-space/ellipsis
               overrides on .listing-location-tooltip — those broke the
               hover/tap address popover, so this restores its original,
               unmodified CSS behavior on mobile too. */
            #listingTopCard .listing-location .dot-separator,
            #listingTopCard .listing-location .business-years {
                display: none;
            }

            /* 3) Category + business age: one row, its own line below the
               address. Hide the original desktop badge and the
               .listing-location copy of the business-age text so neither
               is shown twice. */
            #listingTopCard .jd-meta-row {
                display: flex;
                align-items: center;
                flex-wrap: nowrap;
                gap: 6px;
                margin: 2px 0 6px;
            }

            #listingTopCard .jd-category-chip {
                display: inline-block;
                flex-shrink: 0;
                background: #eef3ff;
                color: #2f6fed;
                border: 1px solid #d7e3ff;
                border-radius: 6px;
                padding: 3px 10px;
                font-size: 12px;
                font-weight: 600;
            }

            #listingTopCard .jd-meta-dot {
                color: #888;
                flex-shrink: 0;
            }

            #listingTopCard .jd-business-age {
                font-size: 12px;
                color: #555;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            #listingTopCard .text-md-right > .mb-4 {
                display: none;
            }

            /* 4) Business status: hide the old plain-text line, show the
               Justdial-style expandable status row instead. */
            #listingTopCard .jd-hours-old {
                display: none;
            }

            #listingTopCard .jd-status-row {
                display: block;
                margin: 2px 0 4px;
            }

            #listingTopCard .jd-status-toggle {
                display: flex;
                align-items: center;
                gap: 6px;
                border: 0;
                background: transparent;
                padding: 0;
                cursor: pointer;
                font-size: 13px;
                font-weight: 700;
            }

            #listingTopCard .jd-status-dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                flex-shrink: 0;
            }

            #listingTopCard .jd-status-dot.is-open {
                background: #1aa350;
            }

            #listingTopCard .jd-status-dot.is-closed {
                background: #e02424;
            }

            #listingTopCard .jd-status-text.is-open {
                color: #1aa350;
            }

            #listingTopCard .jd-status-text.is-closed {
                color: #e02424;
            }

            #listingTopCard .jd-status-detail {
                color: #666;
                font-weight: 500;
                white-space: nowrap;
            }

            #listingTopCard .jd-status-arrow {
                font-size: 14px;
                color: #666;
                transition: transform 0.3s ease;
            }

            #listingTopCard .jd-status-toggle[aria-expanded="true"] .jd-status-arrow {
                transform: rotate(180deg);
            }

            /* Smooth Justdial-style slide-down/up via max-height transition. */
            #listingTopCard .jd-hours-panel {
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.35s ease;
                background: #f7f8fa;
                border-radius: 8px;
            }

            #listingTopCard .jd-hours-panel.open {
                max-height: 320px;
            }

            #listingTopCard .jd-hours-panel-inner {
                padding: 8px 12px;
            }

            #listingTopCard .jd-hours-row {
                display: flex;
                justify-content: space-between;
                padding: 5px 0;
                font-size: 12.5px;
                border-bottom: 1px solid #eceff3;
            }

            #listingTopCard .jd-hours-row:last-child {
                border-bottom: 0;
            }

            #listingTopCard .jd-hours-row.is-today .jd-hours-day,
            #listingTopCard .jd-hours-row.is-today .jd-hours-time {
                color: #2f6fed;
                font-weight: 700;
            }

            #listingTopCard .jd-hours-day {
                font-weight: 500;
                color: #333;
            }

            #listingTopCard .jd-hours-time {
                color: #555;
            }
        }

        /* .mobile-quick-actions / .mqa-enquire-btn / .mqa-hours-icon /
           .jd-meta-row / .jd-category-chip / .jd-status-row only ever
           render inside the mobile media query above — hidden by default
           so desktop/tablet is never affected. */
        .mobile-quick-actions,
        .mqa-enquire-btn,
        .mqa-hours-icon,
        .jd-meta-row,
        .jd-category-chip,
        .jd-status-row {
            display: none;
        }

        /* .jd-rating-line1/2 (inside .jd-rating-wrap) only exist to let
           the mobile query above split rating+count from trust+verified
           into two rows. display:contents makes them invisible to layout
           by default, so on tablet/desktop all four items still render as
           direct flex items of .jd-rating-wrap in one row, exactly as
           before these wrappers were introduced. */
        .jd-rating-line1,
        .jd-rating-line2 {
            display: contents;
        }

        @media (max-width: 768px) {
            #listingMediaRow .media-tabs {
                padding: 0 10px;
                gap: 4px;
            }

            #listingMediaRow .media-tabs .nav-link {
                font-size: 13px;
                padding: 10px 4px;
            }
        }

        @media (max-width: 767px) {
            /*
             * ROOT CAUSE (why position:sticky never worked here):
             *
             * position:sticky is computed relative to the element's
             * nearest ANCESTOR that has a non-'visible' `overflow`. Walking
             * #listingTabs' ancestor chain on this page (verified via
             * getComputedStyle on each ancestor, live, at 375px):
             *
             *   #listingTabs (.media-tabs) — overflow-x:auto (deliberate,
             *     for horizontal tab scrolling) — per spec this also
             *     forces its OWN overflow-y to compute as 'auto', not
             *     'visible'. Irrelevant to its own sticky behavior (that's
             *     about ancestors, not itself), but the same mechanism is
             *     what causes the next two entries.
             *   .media-main-box — overflow:hidden (to clip its rounded
             *     corners). This alone would already break sticky.
             *   .col-lg-9, .row, .container-fluid, #listingMediaRow — all
             *     overflow:visible, position:static, no transform/filter.
             *   the body element — overflow-x:hidden is set elsewhere on this page
             *     (unrelated FOUC fix), which per the same CSS spec rule
             *     forces its overflow-y to compute as 'auto' too.
             *   the html element — independently also computes overflow:hidden auto.
             *
             * So there isn't one single blocker — .media-main-box AND
             * body AND html elements all have non-'visible' overflow. Even after
             * neutralizing .media-main-box, body/html still remain:
             * both size themselves exactly to their content (height:auto,
             * so neither ever actually shows its own internal scrollbar —
             * the real page scrolling happens through the visual
             * viewport), yet because their computed `overflow` isn't
             * 'visible', CSS still treats one of them as #listingTabs'
             * sticky containing block instead of the viewport. That
             * mismatch — sticky's reference frame not being the thing that
             * actually scrolls — is exactly why it never visibly stuck,
             * and neither is something safely fixable from this
             * page-scoped file: body's overflow-x:hidden is needed for its
             * original purpose, and html's overflow is shared, layout-level
             * behavior outside this file's scope. No transform, filter,
             * perspective, or stacking-context property was found on any
             * ancestor — that's not a factor here.
             *
             * FIX: skip position:sticky entirely and reproduce the same
             * effect with position:fixed, toggled by the script below,
             * plus a placeholder element that reserves #listingTabs'
             * normal space so nothing jumps when it switches. fixed is
             * anchored to the viewport unconditionally (no ancestor here
             * has transform/filter/perspective/will-change:transform, so
             * nothing overrides that) — the actual, provably reliable way
             * to reproduce Justdial's sticky-tabs effect on this page.
             */
            #listingTabs.is-fixed {
                position: fixed;
                /* top/left/width are set inline by the script below —
                   they depend on the live layout (card padding, viewport
                   width), which isn't knowable in static CSS. */
                z-index: 900;
                background: #fff;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }

            /* Reserves #listingTabs' normal spot in the document while it's
               position:fixed, so the content below doesn't jump upward.
               Height is set inline by the script, matching the tab bar's
               own height exactly; 0 (no layout impact) the rest of the
               time. */
            #listingTabsPlaceholder {
                height: 0;
            }
        }
    </style>
    @endpush

    <script>
    /*
     * REAL BUG FOUND: this <script> tag sits ~428 lines BEFORE
     * #listingTabs / #listingTabsPlaceholder appear in the HTML (they're
     * defined further down this same file). Since this ran as a plain
     * inline script (not deferred), the browser executed it the instant
     * the parser reached this point — before those elements existed in
     * the DOM at all. document.getElementById('listingTabs') was
     * therefore always null, the `if (!tabs || ...) return;` guard below
     * always fired, and NOTHING in this script ever actually ran on a
     * real page load — it silently did nothing, with no error, every
     * single time. (Manual re-testing in devtools after the page had
     * already finished loading masked this, since by then the elements
     * existed.) Wrapping everything in DOMContentLoaded fixes it: the
     * whole HTML document (including #listingTabs) is guaranteed to be
     * parsed before this code runs.
     */
    document.addEventListener('DOMContentLoaded', function () {
        // Mobile-only fixed-nav (see the CSS comment above for why
        // position:sticky doesn't work on this page). Everything here is
        // gated behind mobileQuery.matches, and unfixes itself immediately
        // if the viewport is ever resized past 767px, so tablet/desktop
        // are unaffected either way.
        var tabs = document.getElementById('listingTabs');
        var placeholder = document.getElementById('listingTabsPlaceholder');
        var mediaBox = document.querySelector('#listingMediaRow .media-main-box');

        if (!tabs || !placeholder || !mediaBox) {
            return;
        }

        var mobileQuery = window.matchMedia('(max-width: 767px)');
        var topOffset = 50; // shared site header's own fixed height (#stickyScrollHeader.scrolled)
        var isFixed = false;
        var ticking = false;

        function applyFixed(rect) {
            placeholder.style.height = rect.height + 'px';
            tabs.style.top = topOffset + 'px';
            tabs.style.left = rect.left + 'px';
            tabs.style.width = rect.width + 'px';
            tabs.classList.add('is-fixed');
            isFixed = true;
        }

        function releaseFixed() {
            tabs.classList.remove('is-fixed');
            tabs.style.top = '';
            tabs.style.left = '';
            tabs.style.width = '';
            placeholder.style.height = '0px';
            isFixed = false;
        }

        function update() {
            ticking = false;

            if (!mobileQuery.matches) {
                if (isFixed) {
                    releaseFixed();
                }
                return;
            }

            var tabsHeight = isFixed
                ? tabs.getBoundingClientRect().height
                : tabs.getBoundingClientRect().height;

            // Stop before the tab-content card ends so the bar never
            // overlaps whatever follows it (sidebar/footer etc).
            var boxBottom = mediaBox.getBoundingClientRect().bottom;
            var reachedEnd = boxBottom <= topOffset + tabsHeight;

            if (!isFixed) {
                var rect = tabs.getBoundingClientRect();

                if (rect.top <= topOffset && !reachedEnd) {
                    applyFixed(rect);
                }
            } else if (reachedEnd) {
                releaseFixed();
            } else {
                var placeholderRect = placeholder.getBoundingClientRect();

                if (placeholderRect.top > topOffset) {
                    // Scrolled back above the original position.
                    releaseFixed();
                } else {
                    // Keep left/width in sync with the live layout (e.g.
                    // after an orientation change) while still fixed.
                    tabs.style.left = placeholderRect.left + 'px';
                    tabs.style.width = placeholderRect.width + 'px';
                }
            }
        }

        function onScrollOrResize() {
            if (!ticking) {
                ticking = true;
                window.requestAnimationFrame(update);
            }
        }

        window.addEventListener('scroll', onScrollOrResize, { passive: true });
        window.addEventListener('resize', onScrollOrResize);
        update();

        /*
         * Automatic active-tab highlighting while scrolling (mobile only).
         * IntersectionObserver watches each tab's target section; whichever
         * one is currently sitting in the "active band" just below the
         * fixed header + tabs gets its tab marked .active as the user
         * scrolls — no click required. This only ever adds/removes the
         * .active class (exactly what the existing click handlers further
         * down this file already do), so it doesn't touch or duplicate
         * their scroll-to logic at all.
         */
        var tabButtons = Array.prototype.slice.call(document.querySelectorAll('.tab-scroll-btn'));
        var sections = tabButtons
            .map(function (btn) {
                var target = document.querySelector(btn.getAttribute('data-target'));
                return target ? { btn: btn, el: target } : null;
            })
            .filter(Boolean);

        if (sections.length && 'IntersectionObserver' in window) {
            var intersecting = new Map();
            var suppressUntil = 0;
            var tabsHeight = tabs.getBoundingClientRect().height || 46;

            function setActiveButton(activeBtn) {
                tabButtons.forEach(function (btn) {
                    btn.classList.toggle('active', btn === activeBtn);
                });
            }

            function syncActiveFromScroll() {
                if (!mobileQuery.matches || Date.now() < suppressUntil) {
                    return;
                }

                // The active section is the last one (in document/tab
                // order) currently intersecting the band — i.e. the one
                // whose heading most recently scrolled under the tabs.
                var current = null;

                for (var i = 0; i < sections.length; i++) {
                    if (intersecting.get(sections[i].el)) {
                        current = sections[i].btn;
                    }
                }

                if (current) {
                    setActiveButton(current);
                }
            }

            var sectionObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    intersecting.set(entry.target, entry.isIntersecting);
                });
                syncActiveFromScroll();
            }, {
                // Active band: a thin strip starting just below the fixed
                // header + tabs, ending 60% down the viewport — a section
                // counts as "current" once its heading crosses into it.
                rootMargin: '-' + (topOffset + tabsHeight) + 'px 0px -60% 0px',
                threshold: 0
            });

            sections.forEach(function (s) {
                sectionObserver.observe(s.el);
            });

            // Briefly suppress automatic updates right after a tab is
            // clicked, so the tab the user just chose doesn't flicker to a
            // different one while its smooth scroll is still in flight —
            // the existing click handlers already set the correct .active
            // state synchronously on click, this just avoids overriding it
            // mid-scroll. Requirement 6 (auto-updating again afterwards)
            // still holds: syncActiveFromScroll keeps running once the
            // short window ends.
            tabButtons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    suppressUntil = Date.now() + 700;
                });
            });
        }
    });
    </script>

    @php
        $popupRatingData = auth()->check()
            ? $listing->ratings->firstWhere('user_id', auth()->id())
            : null;

        $popupRatingValue = $popupRatingData ? (int) $popupRatingData->ratings : 0;
    @endphp
    <div class="modal fade" id="ratingPopup" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-3">

            <h5>{{ $popupRatingValue > 0 ? 'Update Review' : 'Add Review' }}</h5>
    <input type="hidden" id="popup_rating_value" value="{{ $popupRatingValue }}">
                <textarea id="popup_review"
                        class="form-control mb-2"
                        placeholder="Write your review">{{ $popupRatingData->review ?? '' }}</textarea>

                @guest
                    <input type="text" id="guest_name" class="form-control mb-2" placeholder="Your Name">
                    <input type="email" id="guest_email" class="form-control mb-2" placeholder="Your Email">

                    <button class="btn btn-primary w-100 mb-2" id="sendOtpBtn">Send OTP</button>

                    <div id="otpBox" class="d-none">
                        <input type="text" id="otp_code" class="form-control mb-2" placeholder="Enter OTP">
                        <button class="btn btn-success w-100" id="verifyOtpBtn">
                            Submit Rating
                        </button>
                    </div>
                @else
                    <button class="btn btn-success w-100" id="submitRatingBtn">
                        Submit Rating
                    </button>
                @endguest

            </div>
        </div>
    </div>


    <div class="sticky-listing-bar" id="stickyListingBar">
        <div class="sticky-listing-inner">

            <div class="sticky-left">
                <div class="sticky-logo">
                    <img src="{{ loadImage('assets/images/listing/thumb/'.$listing->thumb) }}" alt="{{ $listing->name }} Logo" loading="lazy">
                </div>

                <div class="sticky-info">
                    <h6>
                        @if($listing->is_star == 1)
        <i class="fas fa-star ico"></i>
        @endif
        
                        {{ $listing->name }}</h6>

                <div class="jd-rating-wrap mb-2 d-flex align-items-center flex-wrap">
        @if($rating > 0)
            <span class="jd-rating-badge">
                {{ number_format($rating, 1) }} ★
            </span>
        @endif

        @if($ratingCount > 0)
            <span class="jd-rating-count">
                {{ $ratingCount }} Ratings
            </span>
        @endif

        @if($listing->trust == 1)
            <img src="{{ asset('assets/images/trust_2x.gif') }}" class="jd-badge-img" alt="Trusted Business">
        @endif

        @if($listing->verified == 1)
            <img src="{{ asset('assets/images/verified_2x.gif') }}" class="jd-badge-img" alt="Verified Business">
        @endif
    </div>
                </div>
            </div>

        <div class="listing-action-buttons">

            @if(!empty($listing->mobile_numbers[0]))
    <a href="tel:{{ $listing->mobile_numbers[0] }}"
    class="listing-btn btn-call-main track-click"
    data-track="call_click"
    data-type="call"
    data-listing-id="{{ $listing->id }}"
    data-category-id="{{ $listing->category_id ?? optional($listing->category)->id ?? '' }}"
    data-location-id="{{ $listing->location_id ?? optional($listing->location)->id ?? '' }}"
    data-number="{{ $listing->mobile_numbers[0] }}">
        <img src="{{ asset('assets/download.svg') }}" class="call-icon" alt="Call">

        {{ $listing->mobile_country_code[0] ?? '+91' }}{{ $listing->mobile_numbers[0] }}
    </a>
    @endif


    @if(!empty($listing->landline_numbers[0]))
    <a href="tel:{{ $listing->landline_numbers[0] }}"
    class="listing-btn btn-landline-main track-click"
    data-type="landline"
    data-listing-id="{{ $listing->id }}"
    data-number="{{ $listing->landline_numbers[0] }}">

    <i class="las la-headset"></i>

        {{ $listing->landline_country_code[0] ?? '+91' }}
        {{ $listing->landline_numbers[0] }}
    </a>
    @endif
    @if(!empty($listing->whatsapp_numbers[0]))
        <a href="https://wa.me/91{{ $listing->whatsapp_numbers[0] ?? '' }}"
        target="_blank"
        class="listing-btn btn-whatsapp-main track-click"
        data-track="whatsapp_click"
        data-type="whatsapp"
        data-listing-id="{{ $listing->id }}"
        data-number="{{ $listing->whatsapp_numbers[0] ?? '' }}">
        <img src="{{ asset('assets/wtseappp.svg') }}" class="whatsapp-icon" alt="WhatsApp">

            WhatsApp
        </a>
        @endif

        <button type="button"
                class="listing-btn btn-best-price open-enquiry-modal track-click"
                data-type="enquiry"
                data-listing-id="{{ $listing->id }}"
                data-number="{{ $listing->mobile_numbers[0] ?? '' }}"
                data-category="{{ $listing->category->name ?? '' }}">
              
            Get Best Price
        </button>

    

        <button type="button"
        style="cursor: pointer;"
            class="listing-btn share-btn-icon"
                id="listingShareBtn"
                data-url="{{ url()->current() }}"
                data-text="{{ $listing->name }}">

        <img src="{{ asset('assets/images/download.svg') }}" alt="">
        </button>

        {{-- Edit button intentionally omitted from the sticky/mobile bottom
             bar — it stays only in the main header card above. --}}

    </div>

        </div>
    </div>
    <!-- BEST PRICE POPUP -->
    <div id="bestPriceModal" class="best-price-modal">
        <div class="best-price-modal-dialog">
            <button type="button" class="best-price-close" id="closeBestPriceModal">&times;</button>

            <div class="best-price-modal-content">
                <div class="best-price-left">
                    <h2 class="best-price-title">
                        Get the list of best <span id="popupCategoryName">Listings</span>
                    </h2>

                    <form action="{{ route('contact.author') }}" method="POST">
                        @csrf
                        @include('themes.servicebazzar.partials.form-guard')

                <input type="hidden" name="listing_id" value="{{ $listing->id ?? '' }}">

    <input type="hidden" name="category_id" value="{{ $listing->category_id ?? optional($listing->category)->id ?? '' }}">

    <input type="hidden" name="category_name" id="popupCategoryInput" value="{{ optional($listing->category)->name ?? 'Listings' }}">

    <input type="hidden" name="location_id" value="{{ $listing->location_id ?? optional($listing->location)->id ?? '' }}">

    <input type="hidden" name="location_name" value="{{ optional($listing->location)->name ?? $listing->city ?? '' }}">

                        <div class="best-price-field">
                            <label>Name*</label>
                            <input type="text" name="name" required>
                        </div>

                        <div class="best-price-field">
                            <label>Mobile Number*</label>
                            <input type="text" name="phone" required>
                        </div>

                        <div class="best-price-check">
                            <input type="checkbox" id="agreePolicy" checked>
                            <label for="agreePolicy">
                                I Agree to Terms and Conditions
                                <a href="{{ route('single.slug',38) }}">T&amp;C's Privacy Policy</a>
                            </label>
                        </div>

                        <button type="submit" class="best-price-submit">
                            GET BEST PRICE
                        </button>
                    </form>

                    {{-- One line, not three bullets. This sits below the
                         button, where it is reassurance for someone already
                         deciding — three separate claims there only made the
                         box taller than the screen. --}}
                    <p class="best-price-points">
                        Businesses compete for your order — details come by SMS/Email.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const topCard = document.getElementById('listingTopCard');
        const stickyBar = document.getElementById('stickyListingBar');

        if (!topCard || !stickyBar) return;

        function toggleStickyBar() {
            const rect = topCard.getBoundingClientRect();

            if (rect.bottom <= 0) {
                stickyBar.classList.add('show');
            } else {
                stickyBar.classList.remove('show');
            }
        }

        window.addEventListener('scroll', toggleStickyBar);
        window.addEventListener('resize', toggleStickyBar);
        toggleStickyBar();
    });
    </script>

    <section class="listing-media-section py-1 custom-right" id="listingMediaRow">
        <div class="container-fluid">
            <div class="row g-3">

                {{-- LEFT SECTION --}}
        <div class="col-lg-9">
        <div class="media-main-box">

            {{-- TOP BUTTONS --}}
            {{-- Placeholder for the mobile fixed-nav script below: reserves
                 #listingTabs' normal space in the document once it switches
                 to position:fixed, so nothing else on the page jumps. Height
                 stays 0 (no visual/layout presence) except while the tabs
                 are fixed, and only on mobile — see the script for why
                 position:sticky isn't used here. --}}
            <div id="listingTabsPlaceholder"></div>
            <ul class="nav media-tabs mb-3" id="listingTabs">
        <li class="nav-item">
        <button class="nav-link active tab-scroll-btn" data-target="#photosSection" type="button">
            Photos
        </button>
    </li>

    @php
        // Rate Card / Catalogue images live in extra_images (type=catalogue),
        // uploaded from the owner's "Upload Rate Card / Catalogue" page.
        $sbCatalogueImages = $listing->extraImages()->where('type', 'catalogue')->latest()->get();
    @endphp
    @if($sbCatalogueImages->isNotEmpty())
    <li class="nav-item">
        <button class="nav-link tab-scroll-btn" data-target="#catalogueSection" type="button">
            Rate Card
        </button>
    </li>
    @endif

    <li class="nav-item">
        <button class="nav-link tab-scroll-btn" data-target="#overviewSection" type="button">
            Overview
        </button>
    </li>
    @php
        $faqData = is_array($listing->faq) ? $listing->faq : (json_decode($listing->faq, true) ?? []);

        $faqQuestions = collect($faqData['question'] ?? [])
            ->map(fn($q) => is_string($q) ? trim($q) : '')
            ->filter()
            ->values();
    @endphp

    @if($faqQuestions->count() > 0)
                <li class="nav-item">
                    <button class="nav-link tab-scroll-btn" data-target="#faqSection" type="button">
                        FAQ
                    </button>
                </li>

                @endif


                @php
        $services = collect(is_array($listing->features) ? $listing->features : (json_decode($listing->features, true) ?? []))
            ->map(fn($item) => is_string($item) ? trim($item) : '')
            ->filter()
            ->values();
    @endphp

    @if($services->count() > 0)
                <li class="nav-item">
                    <button class="nav-link tab-scroll-btn" data-target="#servicesSection" type="button">
                        Services
                    </button>
                </li>
                @endif

                <li class="nav-item">
                    <button class="nav-link tab-scroll-btn" data-target="#reviewsSection" type="button">
                        Reviews
                    </button>
                </li>
            </ul>

            <div class="media-content-box">

                <div class="content-section mb-4" id="photosSection">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h4 class="media-title mb-0">Photos</h4>
        </div>

        {{-- VIEW MODE --}}
        @php
            // Photos uploaded through the newer "Photo Gallery" page live in
            // extra_images (type gallery), separate from the legacy `photos`
            // array column — show both here or new uploads look missing.
            $sbGalleryExtra = $listing->extraImages()
                ->where(fn ($q) => $q->where('type', 'gallery')->orWhereNull('type')->orWhere('type', ''))
                ->latest()
                ->get();
        @endphp
        <div id="photos_view">
            <div class="photo-category-slider">
                @if(!empty($listing->photos) || $sbGalleryExtra->isNotEmpty())
                    @foreach($listing->photos ?? [] as $photo)
                        <div class="photo-card">
                        <div class="photo-thumb">
        <img src="{{ asset('assets/images/listing/photos/'.$photo) }}"
            class="listing-photo-click"
            data-src="{{ asset('assets/images/listing/photos/'.$photo) }}"
    alt="{{ $listing->name }} Photo"
                loading="lazy"
                data-track="gallery_click"
                data-listing-id="{{ $listing->id }}">
    </div>
                        </div>
                    @endforeach
                    @foreach($sbGalleryExtra as $extra)
                        <div class="photo-card">
                        <div class="photo-thumb">
        <img src="{{ asset('assets/images/listing/extra/'.$extra->image) }}"
            class="listing-photo-click"
            data-src="{{ asset('assets/images/listing/extra/'.$extra->image) }}"
    alt="{{ $listing->name }} Photo"
                loading="lazy"
                data-track="gallery_click"
                data-listing-id="{{ $listing->id }}">
    </div>
                        </div>
                    @endforeach
                @else
                    <p>No photos available.</p>
                @endif
            </div>
        </div>

        {{-- EDIT MODE --}}
        @auth
            @if(auth()->id() == $listing->user_id)
                <div id="photos_edit" style="display:none;">
                    <form action="{{ route('user.listing.updatesection', $listing->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Add Photos</label>
                            <input type="file" name="photos[]" class="form-control" multiple>
                        </div>

                        <div class="mt-2">
                            <button class="btn btn-success btn-sm">Save</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="toggleInlineSection('photos')">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        @endauth
    </div>

                {{-- RATE CARD / CATALOGUE --}}
                @if($sbCatalogueImages->isNotEmpty())
                <div class="content-section mb-4" id="catalogueSection">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 class="media-title mb-0">Rate Card / Catalogue</h4>
                    </div>
                    <div class="photo-category-slider">
                        @foreach($sbCatalogueImages as $cat)
                            <div class="photo-card">
                                <div class="photo-thumb">
                                    <img src="{{ asset('assets/images/listing/extra/'.$cat->image) }}"
                                        class="listing-photo-click"
                                        data-src="{{ asset('assets/images/listing/extra/'.$cat->image) }}"
                                        alt="{{ $listing->name }} Rate Card"
                                        loading="lazy">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- OVERVIEW --}}
                <div class="content-section mb-4" id="overviewSection">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 class="media-title mb-0">Overview</h4>
                    </div>


                    <div class="orrrr rich-content" id="overview_view">
                        {!! \App\Support\ContentHtml::cleanCached($listing->details ?? '')
                            ?: 'No overview available.' !!}
                    </div>

                    @auth
                        @if(auth()->id() == $listing->user_id)
                            <div id="overview_edit" style="display:none;">
                            
                                <form action="{{ route('user.listing.updatesection', $listing->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="section_name" value="overview">
                                    <textarea class="richEditor" rows="5" name="section_value">@php echo $listing->details @endphp</textarea>

                                
                                    <div class="mt-2">
                                        <button class="btn btn-success btn-sm">Save</button>
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="toggleInlineSection('overview')">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    @endauth
                </div>

                {{-- PHOTOS --}}
            
    @php
        $faqData = is_array($listing->faq) ? $listing->faq : (json_decode($listing->faq, true) ?? []);

        $faqQuestions = collect($faqData['question'] ?? [])
            ->map(fn($q) => is_string($q) ? trim($q) : '')
            ->filter()
            ->values();
    @endphp

    @if($faqQuestions->count() > 0)
        <div class="listing-widget faq" id="faqSection">
            <div class="widget-title d-flex justify-content-between align-items-center">
                <h4 class="media-title mb-0">
                    <i class="las la-address-card"></i>
                    @lang('Frequently Asked Question (FAQ)')
                </h4>
            </div>

            <div class="widget-body">
                <div id="faq_view" class="accordion">
                    @foreach($faqQuestions as $index => $question)
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0" style="word-break: break-word;">
                                    <button class="utlink btn btn-link collapsed"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapse{{ $index }}">
                                        {{ ucwords(\Illuminate\Support\Str::limit($question, 160)) }}
                                    </button>
                                </h5>
                            </div>

                            <div id="collapse{{ $index }}" class="collapse" data-bs-parent="#faq_view">
                                <div class="utlink card-body">
                                    {{ ucwords($faqData['answer'][$index] ?? '') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @php
        // ---- Structured data (LocalBusiness + BreadcrumbList + FAQPage) ----
        // Built from the same data already rendered visibly above (name,
        // address, phone, rating, breadcrumb trail, FAQ accordion) rather
        // than duplicating content. aggregateRating is only included when a
        // real rating count exists — Google explicitly disallows emitting a
        // fake/empty rating.
        $listingSchema = [
            '@context' => 'https://schema.org',
            '@type'    => 'LocalBusiness',
            'name'     => $listingName,
            'url'      => $seoUrl,
        ];

        if (!empty($listing->thumb)) {
            $listingSchema['image'] = $seoImage;
        }

        if ($fullAddress) {
            $listingSchema['address'] = [
                '@type'           => 'PostalAddress',
                'streetAddress'   => trim(collect([$listing->plot_no, $listing->building_name, $listing->street_name])->filter()->implode(', ')) ?: null,
                'addressLocality' => $listing->city ?: $locationText ?: null,
                'addressRegion'   => $listing->state ?: null,
                'postalCode'      => $listing->pincode ?: null,
                'addressCountry'  => 'IN',
            ];
        }

        if (!empty($listing->mobile_numbers[0])) {
            $listingSchema['telephone'] = ($listing->mobile_country_code[0] ?? '+91') . $listing->mobile_numbers[0];
        }

        if ($ratingCount > 0 && $rating > 0) {
            $listingSchema['aggregateRating'] = [
                '@type'       => 'AggregateRating',
                'ratingValue' => round($rating, 1),
                'reviewCount' => $ratingCount,
            ];
        }

        $breadcrumbItems = [];

        if (!empty($locationText)) {
            $breadcrumbItems[] = ['name' => $locationText, 'item' => route('home')];
        }

        if (!empty($categoryText) && !empty($categorySlug)) {
            $breadcrumbItems[] = [
                'name' => $categoryText,
                'item' => url(\Illuminate\Support\Str::slug($locationText) . '/' . $categorySlug),
            ];
        }

        $breadcrumbItems[] = ['name' => $listingName, 'item' => $seoUrl];

        $breadcrumbSchema = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => collect($breadcrumbItems)->values()->map(function ($crumb, $index) {
                return [
                    '@type'    => 'ListItem',
                    'position' => $index + 1,
                    'name'     => $crumb['name'],
                    'item'     => $crumb['item'],
                ];
            })->all(),
        ];

        $faqSchema = null;

        if ($faqQuestions->count() > 0) {
            $faqSchema = [
                '@context'   => 'https://schema.org',
                '@type'      => 'FAQPage',
                'mainEntity' => $faqQuestions->map(function ($question, $index) use ($faqData) {
                    return [
                        '@type'          => 'Question',
                        'name'           => $question,
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text'  => $faqData['answer'][$index] ?? '',
                        ],
                    ];
                })->values()->all(),
            ];
        }
    @endphp

    <script type="application/ld+json">
{!! str_ireplace('</script', '<\/script', json_encode($listingSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) !!}
    </script>
    <script type="application/ld+json">
{!! str_ireplace('</script', '<\/script', json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) !!}
    </script>
    @if($faqSchema)
    <script type="application/ld+json">
{!! str_ireplace('</script', '<\/script', json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) !!}
    </script>
    @endif

    @php
        $services = collect(is_array($listing->features) ? $listing->features : (json_decode($listing->features, true) ?? []))
            ->map(fn($item) => is_string($item) ? trim($item) : '')
            ->filter()
            ->values();
    @endphp

    @if($services->count() > 0)
                {{-- SERVICES --}}
                <div class="listing-widget" id="servicesSection">

        <div class="widget-title d-flex justify-content-between align-items-center">
            <h4 class="media-title mb-0">  <i class="las la-concierge-bell"></i> @lang('Services')</h4>
        </div>

        <div class="widget-body">

            {{-- VIEW MODE --}}
            <div id="services_view">

                <div class="widget-feature">

                    @if(!empty($listing->features))
                        <ul>
                            @foreach($listing->features as $feature)
                                <li>
                                    <i class="las la-check-square"></i>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p>No services available.</p>
                    @endif

                </div>

            </div>


            {{-- EDIT MODE --}}
        {{-- EDIT MODE --}}
    @auth
        @if(auth()->id() == $listing->user_id)

            <div id="services_edit" style="display:none;">
                <form action="{{ route('user.listing.updatesection', $listing->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div id="serviceItems">
                        @if(!empty($listing->features) && is_array($listing->features))
                            @foreach($listing->features as $service)
                                <div class="row mb-2 service-row">
                                    <div class="col-md-10">
                                        <input type="text"
                                            name="service[]"
                                            class="form-control"
                                            value="{{ $service }}"
                                            placeholder="Enter service">
                                    </div>

                                    <div class="col-md-2">
                                        <button type="button"
                                                class="btn btn-danger btn-sm w-100"
                                                onclick="removeServiceRow(this)">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="row mb-2 service-row">
                                <div class="col-md-10">
                                    <input type="text"
                                        name="services_value[]"
                                        class="form-control"
                                        placeholder="Enter service">
                                </div>

                                <div class="col-md-2">
                                    <button type="button"
                                            class="btn btn-danger btn-sm w-100"
                                            onclick="removeServiceRow(this)">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    <button type="button"
                            class="btn btn-outline-primary btn-sm mt-2"
                            onclick="addServiceRow()">
                        + Add More
                    </button>

                    <div class="mt-2">
                        <button class="btn btn-success btn-sm">Save</button>

                        <button type="button"
                                class="btn btn-secondary btn-sm"
                                onclick="toggleInlineSection('services')">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

        @endif
    @endauth

    <script>
        function addServiceRow() {
            let html = `
                <div class="row mb-2 service-row">
                    <div class="col-md-10">
                        <input type="text"
                            name="services_value[]"
                            class="form-control"
                            placeholder="Enter service">
                    </div>
                    <div class="col-md-2">
                        <button type="button"
                                class="btn btn-danger btn-sm w-100"
                                onclick="removeServiceRow(this)">
                            Remove
                        </button>
                    </div>
                </div>
            `;
            document.getElementById('serviceItems').insertAdjacentHTML('beforeend', html);
        }

        function removeServiceRow(button) {
            button.closest('.service-row').remove();
        }
    </script>
        </div>
    </div>


    @endif

                {{-- REVIEWS --}}
        <div class="listing-widget" id="reviewsSection">
            <h4 class="media-title  ddd mb-0">@lang('Review & Ratings')</h4>
    
    @php
    $avgRating = round($listing->ratings->avg('ratings') ?? 0, 1);
    $totalRatings = $listing->ratings->count();
    @endphp

    <div class="rating-summary-box">
        <div class="rating-summary-score">
            {{ $avgRating > 0 ? $avgRating : '0.0' }}
        </div>

        <div class="rating-summary-content">
            <h3>{{ $totalRatings }} Ratings</h3>

            <p>
                Service Bazzar rating index based on
                {{ $totalRatings }} ratings across the web
            </p>
        </div>
    </div>


        <div class="widget-body">
        

    <div class="review mt-3">


        @php
        $alreadyRated = auth()->check()
            ? $listing->ratings->firstWhere('user_id', auth()->id())
            : null;

        $userRating = $alreadyRated ? (int) $alreadyRated->ratings : 0;
    @endphp
        <form id="reviewForm">
            @csrf

            <input type="hidden" name="listing_id" value="{{ $listing->id }}">
            <input type="hidden"
                name="ratings"
                id="guest_review_ratings"
                    value="{{ $userRating }}">

            <div class="form-group mb-2">

                <div id="reviewStatusText" class="review-status-text">
                    {{ $userRating  > 0 ? 'You rated' : 'Start your Review' }}
                </div>

                <div class="add-review-stars" id="addReviewStars">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="las la-star add-review-star {{ $i <= $userRating  ? 'active' : '' }}"
                        data-value="{{ $i }}"></i>
                    @endfor
                </div>

            </div>

        <div id="reviewFormBox" class="d-none">
                <div class="form-group mb-2">
                    <label>@lang('Write your review')</label>
                    <textarea class="form-control"
                            name="review"
                            id="guest_review_text"
                            rows="3"
                                required>{{ $alreadyRated->review ?? '' }}</textarea>
                </div>

                @guest
                    <div class="form-group">
                        <button type="button" class="btn btn__base btn-block" id="openReviewPopupBtn" data-track="review_click" data-listing-id="{{ $listing->id }}">
                            Submit Review
                        </button>
                    </div>
                @else
                    <div class="form-group">
                        <button type="submit" class="btn btn__base btn-block">
                            {{ $userRating  > 0 ? 'Update Review' : 'Submit Review' }}
                        </button>
                    </div>
                @endguest
            </div>
        </form>
    </div>
        <div class="reviewer-area" id="reviewsContainer">
            

                <div class="user-reviews-header">

        <h6 class="user-reviews-title">User Reviews</h6>

        <div class="review-filters">

            <button type="button"
                    class="review-filter-btn active"
                    data-sort="relevant">
                Relevant
            </button>

            <button type="button"
                    class="review-filter-btn"
                    data-sort="latest">
                Latest
            </button>

            <button type="button"
                    class="review-filter-btn"
                    data-sort="high">
                High to Low
            </button>

            <button type="button"
                    class="review-filter-btn"
                    data-sort="low">
                Low to High
            </button>

        </div>

    </div>
        @foreach($listing->ratings->sortByDesc('id')->values() as $index => $rating)

    <div class="reviewer mb-3 review-item-load"
        style="{{ $index >= 10 ? 'display:none;' : '' }}">
                    

                        <div class="content w-100">




                            
                        <div class="review-item"
        id="review-{{ $rating->id }}"
        data-rating="{{ $rating->ratings }}"
        data-date="{{ $rating->created_at->timestamp }}">

        <div class="review-header">

            <div class="review-left">

                <img
                    src="{{ userAvatarUrl($rating->user->image ?? null) }}"
                    class="review-avatar"
                    alt="{{ $rating->user ? $rating->user->fullname : ($rating->name ?? 'Guest') }}"
                    loading="lazy">

                <span class="review-name">
                    {{ $rating->user ? $rating->user->fullname : ($rating->name ?? 'Guest') }}
                </span>

            </div>

            <div class="review-right">

                <span class="review-date">
                    {{ $rating->created_at->format('d M') }}
                </span>

            @auth
        @if(auth()->id() == $rating->user_id)
            <div class="review-menu-wrap">
                <button type="button"
                        class="review-menu-btn"
                        onclick="toggleReviewMenu({{ $rating->id }})">
                    <i class="las la-ellipsis-v"></i>
                </button>

                <div class="review-menu-dropdown" id="reviewMenu{{ $rating->id }}">
                    <button type="button"
                            onclick="toggleReviewEdit({{ $rating->id }})">
                        <i class="las la-edit"></i> Edit Review
                    </button>

                    <button type="button"
                            class="delete-review-btn"
                            data-id="{{ $rating->id }}">
                        <i class="las la-trash"></i> Delete Review
                    </button>
                </div>
            </div>
        @endif
    @endauth

            </div>

        </div>

        <div class="review-stars">
            @for($i=1;$i<=5;$i++)
                @if($i <= $rating->ratings)
                    <i class="fas fa-star"></i>
                @else
                    <i class="far fa-star"></i>
                @endif
            @endfor
        </div>

        <div class="review-text">
            "{{ $rating->review }}"
        </div>

        

    @auth
        @if(auth()->id() == $rating->user_id)
            <div id="review_edit_{{ $rating->id }}" class="review-edit-box" style="display:none;">
                <form action="{{ route('review.update', $rating->id) }}" method="POST">
                    @csrf
                    @method('PUT')
    <div class="form-group mb-2">
        <label>Ratings</label>

        <input type="hidden"
            name="ratings"
            id="edit_rating_value_{{ $rating->id }}"
            value="{{ $rating->ratings }}">

        <div class="edit-star-box" data-rating-id="{{ $rating->id }}">
            @for($i = 1; $i <= 5; $i++)
                <i class="las la-star edit-star {{ $i <= $rating->ratings ? 'active' : '' }}"
                data-value="{{ $i }}"></i>
            @endfor
        </div>
    </div>

                    <div class="form-group mb-2">
                        <label>Edit your review</label>
                        <textarea class="form-control" name="review" rows="3" required>{{ $rating->review }}</textarea>
                    </div>

                    <button type="submit" class="btn btn__base btn-sm">Update</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="toggleReviewEdit({{ $rating->id }})">
                        Cancel
                    </button>
                </form>
            </div>
        @endif
    @endauth



        <div class="review-actions" data-rating="{{ $rating->id }}">

    <button
        class="review-action-btn like-btn {{ $rating->likes()->where('user_id', auth()->id())->exists() ? 'liked' : '' }}"
        data-rating-id="{{ $rating->id }}">
            <i class="las la-thumbs-up"></i>
            Helpful (<span class="like-count">{{ $rating->likes_count ?? 0 }}</span>)
        </button>

        <button type="button"
                class="review-action-btn comment-toggle-btn"
                data-rating-id="{{ $rating->id }}">
            <i class="las la-comment"></i>
            Comment (<span class="comment-count">{{ $rating->comments_count ?? 0 }}</span>)
        </button>

    <button type="button"
            class="review-action-btn share-review-btn"
            data-url="{{ url()->current() }}#review-{{ $rating->id }}"
            data-text="{{ $rating->review }}">
        <i class="las la-share"></i>
        Share
    </button>

    </div>

    <div class="comment-box-wrap" id="commentBox{{ $rating->id }}" style="display:none;">

        <div class="comment-input-row">

            <img src="{{ userAvatarUrl(auth()->check() ? auth()->user()->image : null) }}"
                alt="{{ auth()->check() ? (auth()->user()->fullname ?? auth()->user()->name ?? 'User') : 'Guest' }}"
                class="comment-user-img">

            <div class="comment-input-flex">
                <input type="text"
                    class="form-control comment-input"
                    id="commentInput{{ $rating->id }}"
                    placeholder="Add a comment">

                <button type="button"
                        class="comment-post-btn"
                        data-rating-id="{{ $rating->id }}">
                    Post
                </button>
            </div>

        </div>

    <div class="comments-list" id="commentsList{{ $rating->id }}">

        @foreach($rating->comments->sortByDesc('id') as $comment)
            <div class="single-comment" id="comment_{{ $comment->id }}">

                <img src="{{ userAvatarUrl($comment->user->image ?? null) }}"
                    alt="{{ $comment->user->fullname ?? $comment->user->name ?? 'User' }}"
                    loading="lazy"
                    class="comment-user-img">

                <div class="comment-body">

                    <div class="comment-head">
                        <b>{{ $comment->user->fullname ?? $comment->user->name ?? 'User' }}</b>

                        <span>{{ $comment->created_at->diffForHumans() }}</span>

                        @auth
                            @if(auth()->id() == $comment->user_id)
                                <button type="button"
                                        class="delete-comment-btn"
                                        data-id="{{ $comment->id }}">
                                    <i class="las la-trash"></i> Delete Comment
                                </button>
                            @endif
                        @endauth
                    </div>

                    <p>{{ $comment->comment }}</p>

                </div>
            </div>
        @endforeach

    </div>

    </div>
    </div>


    <div id="shareOverlay" class="share-overlay">
        <div class="share-popup">

            <button type="button" class="share-close" id="shareCloseBtn">
                <i class="las la-times"></i>
            </button>

            <h4>Share With Friends</h4>
            <p>Select from the options below</p>

            <div class="share-options">

                <a href="#" target="_blank" id="shareFacebook" class="share-option">
                    <span class="share-icon fb">
                        <i class="lab la-facebook-f"></i>
                    </span>
                    <small>Facebook</small>
                </a>

                <a href="#" target="_blank" id="shareWhatsapp" class="share-option">
                    <span class="share-icon wa">
                        <i class="lab la-whatsapp"></i>
                    </span>
                    <small>Whatsapp</small>
                </a>

                <button type="button" id="copyShareLink" class="share-option">
                    <span class="share-icon cp">
                        <i class="las la-link"></i>
                    </span>
                    <small>Copy Link</small>
                </button>

            </div>

        </div>
    </div>


                    

                            {{-- Edit Mode --}}
                    
                        </div>
                    </div>
                @endforeach

                @if($listing->ratings->count() > 10)

    <div class="text-center mt-4">
        <button type="button"
                id="loadMoreReviewsBtn"
                class="btn btn-outline-primary">
            Read all reviews
        </button>
    </div>

    @endif
            </div>

        
        </div>
    </div>

   {{-- RELATED SEARCHES START --}}
@php
    $currentCity = strtolower(
        optional($listing->location)->name
        ?? $listing->city
        ?? ''
    );

    $currentCitySlug = \Illuminate\Support\Str::slug($currentCity);

    
    $subcategoryIds = [];

    if (!empty($listing->subcategory_ids)) {
        $subcategoryIds = is_array($listing->subcategory_ids)
            ? $listing->subcategory_ids
            : (json_decode($listing->subcategory_ids, true) ?? []);
    }

    $subcategoryIds = collect($subcategoryIds)
        ->map(fn ($id) => (int) $id)
        ->filter(fn ($id) => $id > 0)
        ->unique()
        ->values();

    
    $allRelatedSearches = \App\Models\Subcategory::query()
        ->where('status', 1)
    ->where('category_id', $listing->category_id)
    ->whereIn('id', $subcategoryIds)
    ->get([
        'id',
        'name',
        'slug',
        ])
        ->map(function ($subcategory) {
            return [
                'id'   => $subcategory->id,
                'name' => $subcategory->name,
                'slug' => $subcategory->slug,
                'type' => 'subcategory',
            ];
        })
        ->filter(function ($item) {
            return !empty($item['name']) && !empty($item['slug']);
        })
        ->unique('id')
        ->values();

    
    $subcategoryPosition = $subcategoryIds
        ->flip()
        ->all();

    $allRelatedSearches = $allRelatedSearches
        ->sortBy(function ($item) use ($subcategoryPosition) {
            return $subcategoryPosition[$item['id']] ?? PHP_INT_MAX;
        })
        ->values();

   
    $peopleAlsoSearches = $allRelatedSearches->take(10)->values();

 
    $peopleIds = $peopleAlsoSearches
        ->pluck('id')
        ->all();

    $remainingRelatedSearches = $allRelatedSearches
        ->reject(function ($item) use ($peopleIds) {
            return in_array($item['id'], $peopleIds, true);
        })
        ->values();

    $relatedSearches = $remainingRelatedSearches
        ->concat($peopleAlsoSearches)
        ->values()
        ->take(50);
@endphp

   @if($relatedSearches->count() > 0)
    <div class="related-searches-box">
        {{-- Desktop/tablet heading — unchanged; hidden on mobile only. --}}
        <h4 class="related-searches-title">Related Searches</h4>

        {{-- Mobile-only dropdown toggle, same pattern as the footer's
             "Popular Categories" / "Popular Cities" accordion
             (partials/popular_categories_ajax.blade.php +
             css/components/popular-sections.css + js/components/
             popular-accordion.js) — kept self-contained here instead of
             reusing those shared files since this page's own toggle
             script below is scoped to this section only. Hidden on
             desktop/tablet; .related-searches-title above is hidden on
             mobile instead, so nothing is ever shown twice. --}}
        <button type="button"
                class="related-searches-mobile-toggle"
                id="relatedSearchesToggle"
                aria-expanded="false"
                aria-controls="relatedSearchesList">
            <span>Related Searches</span>
            <span class="related-searches-toggle-icon" aria-hidden="true">
                <i class="fas fa-plus"></i>
            </span>
        </button>

        <div class="related-searches-list" id="relatedSearchesList">
            @foreach($relatedSearches as $item)
                @php
                    $itemUrl = !empty($currentCitySlug)
                        ? url($currentCitySlug . '/' . $item['slug'])
                        : route('listings.subcategory', [
                            'slug' => $item['slug']
                        ]);
                @endphp

                <a href="{{ $itemUrl }}">
                    {{ $item['name'] }}
                </a>

                @if(!$loop->last)
                    <span class="related-search-separator">|</span>
                @endif
            @endforeach
        </div>
    </div>
@endif

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Mobile-only "Related Searches" dropdown — same interaction as
        // the footer's "Popular Categories"/"Popular Cities" accordion
        // (js/components/popular-accordion.js), scoped to just this
        // section. On desktop/tablet .related-searches-mobile-toggle is
        // display:none (see CSS), so this listener existing there has no
        // visible effect either way.
        var toggle = document.getElementById('relatedSearchesToggle');
        var list = document.getElementById('relatedSearchesList');

        if (!toggle || !list) {
            return;
        }

        toggle.addEventListener('click', function () {
            var isOpen = list.classList.toggle('mobile-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

            var icon = toggle.querySelector('.related-searches-toggle-icon i');
            if (icon) {
                icon.classList.toggle('fa-plus', !isOpen);
                icon.classList.toggle('fa-minus', isOpen);
            }
        });
    });
    </script>

    <!-- OTP Modal -->
    <div class="modal fade" id="reviewOtpModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title">Verify Your Review</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div id="reviewStep1">
                        <div class="mb-3">
                            <label class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="review_guest_name" placeholder="Enter your name">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="review_guest_email" placeholder="Enter your email">
                        </div>

                        <button type="button" class="btn btn-primary w-100" id="sendReviewOtpBtn">
                            Send OTP
                        </button>
                    </div>

                    <div id="reviewStep2" class="d-none">
                        <div class="mb-3">
                            <label class="form-label">Enter OTP</label>
                            <input type="text" class="form-control text-center" id="review_otp_code" placeholder="Enter OTP">
                        </div>

                        <button type="button" class="btn btn-success w-100" id="verifyReviewOtpBtn">
                            Verify & Submit Review
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

            </div>
        </div>
    </div>

    {{-- RIGHT SECTION --}}
    <div class="col-lg-3" id="sidebarColumn">
        <div class="sidebar-sticky-wrapper">

            <div class="contact-info-box">

                {{-- CONTACT --}}
                <div class="contact-block">
                    <h5>Contact</h5>

                @if(!empty($listing->mobile_numbers) && count(array_filter($listing->mobile_numbers)))

        @foreach($listing->mobile_numbers as $index => $number)
            @if(!empty($number))
                <a href="tel:{{ $number }}" class="contact-link d-block">
                    <i class="las la-phone"></i>
                    {{ $listing->mobile_country_code[$index] ?? '+91' }} {{ $number }}
                </a>
            @endif
        @endforeach

    @endif

    @if(!empty($listing->landline_numbers) && count(array_filter($listing->landline_numbers)))

        @foreach($listing->landline_numbers as $index => $number)
            @if(!empty($number))
                <a href="tel:{{ $number }}" class="contact-link d-block">
                    <i class="las la-headset"></i>
                    {{ $listing->landline_country_code[$index] ?? '+91' }}
                    {{ $number }}
                </a>
            @endif
        @endforeach

    @endif

    @if(
        empty(array_filter($listing->mobile_numbers ?? [])) &&
        empty(array_filter($listing->landline_numbers ?? []))
    )
        <p class="mb-0">No contact number available.</p>
    @endif
                </div>

                <hr>

                {{-- ADDRESS + TIMING --}}
                <div class="contact-block">
                    <h5>Address</h5>

                    @php
        $fullAddress = $listing->address;

        if (empty($fullAddress)) {
            $fullAddress = collect([
                $listing->plot_no,
                $listing->building_name,
                $listing->street_name,
                $listing->landmark,
                $listing->area,
                $listing->city,
                $listing->state,
                $listing->pincode,
            ])->filter()->implode(', ');
        }
    @endphp

    <p class="address-text" id="listingAddress">
        {{ $fullAddress ?: 'Address not available.' }}
    </p>
                    <div class="address-actions mb-3">
                        <a href="{{ $sbDirectionsUrl }}"
                        target="_blank"
                        class="address-btn"
                        data-track="direction_click"
                        data-listing-id="{{ $listing->id }}">
                            <i class="las la-map-marker"></i> Get Directions
                        </a>

                        <a href="javascript:void(0)" class="address-btn" onclick="copyAddress()">
                            <i class="las la-copy"></i> Copy
                        </a>
                    </div>

                @php

        $businessDays = is_array($listing->business_days)
            ? $listing->business_days
            : (json_decode($listing->business_days, true) ?? []);

        $openTimes = is_array($listing->open_time)
            ? $listing->open_time
            : (json_decode($listing->open_time, true) ?? []);

        $closeTimes = is_array($listing->close_time)
            ? $listing->close_time
            : (json_decode($listing->close_time, true) ?? []);

        $today = Carbon::now('Asia/Kolkata')->format('D');

        $formatBusinessTime = function ($time) {
            if (empty($time)) {
                return null;
            }

            try {
                return Carbon::parse($time)->format('g:i A');
            } catch (\Exception $e) {
                return $time;
            }
        };

        /*
         * $businessDays / $openTimes / $closeTimes are parallel arrays and,
         * since a listing's timing form allows a second shift for the same
         * day (lunch-break hours), a day can repeat — each repeat is one
         * more shift for that day, at the same array index in the other two
         * arrays. Group them here so the day is shown once with every shift
         * it has, instead of once per array entry.
         */
        $shiftsByDay = [];

        foreach ($businessDays as $bIndex => $bDay) {
            $bDay = is_string($bDay) ? trim($bDay) : $bDay;

            if (empty($bDay)) {
                continue;
            }

            $shiftsByDay[$bDay][] = [
                'open'  => count($openTimes) === 1 ? ($openTimes[0] ?? null) : ($openTimes[$bIndex] ?? null),
                'close' => count($closeTimes) === 1 ? ($closeTimes[0] ?? null) : ($closeTimes[$bIndex] ?? null),
            ];
        }

        $formatShiftsForDay = function ($day) use ($shiftsByDay, $formatBusinessTime) {
            if (empty($shiftsByDay[$day])) {
                return 'Closed';
            }

            $ranges = [];

            foreach ($shiftsByDay[$day] as $shift) {
                $openTime = $formatBusinessTime($shift['open']);
                $closeTime = $formatBusinessTime($shift['close']);

                if (empty($openTime) || empty($closeTime)) {
                    continue;
                }

                if (strtoupper($openTime) === '12:00 AM' && strtoupper($closeTime) === '11:59 PM') {
                    $ranges[] = 'Open 24 Hours';
                    continue;
                }

                $ranges[] = $openTime . ' - ' . $closeTime;
            }

            return $ranges ? implode(', ', $ranges) : 'Closed';
        };

        $weekOrder = [
            'Mon' => 'Monday',
            'Tue' => 'Tuesday',
            'Wed' => 'Wednesday',
            'Thu' => 'Thursday',
            'Fri' => 'Friday',
            'Sat' => 'Saturday',
            'Sun' => 'Sunday',
        ];
    @endphp

    <div class="business-hours-box">

        <div class="hours-header" onclick="toggleHours()">
            <span class="open-label">
                <i class="las la-clock"></i>

                @if(!empty($shiftsByDay[$today]))
                    {{ $formatShiftsForDay($today) }}
                @else
                    Closed Today
                @endif
            </span>

            <span class="toggle-icon" id="hoursIcon">
                <i class="las la-angle-down"></i>
            </span>
        </div>

        <div class="hours-list" id="hoursList">

            @foreach($weekOrder as $dayKey => $dayLabel)
                <div class="hours-row">

                    <span class="day">
                        {{ $dayLabel }}

                        @if($dayKey === $today)
                            (Today)
                        @endif
                    </span>

                    <span class="time">
                        {{ $formatShiftsForDay($dayKey) }}
                    </span>

                </div>
            @endforeach

        </div>
    </div>


    <div class="listing-side-actions">

    <button type="button"
            class="listing-side-action open-info-modal"
            data-listing-id="{{ $listing->id }}"
            data-category="{{ $listing->category->name ?? '' }}">
        <i class="las la-envelope-open-text"></i>
        <span>Get info via SMS/Email</span>
    </button>
        <button type="button"
            class="listing-side-action open-email-enquiry-modal">
        <i class="las la-envelope"></i>
        <span>Send Enquiry by Email</span>
    </button>


        <button type="button"
                class="listing-side-action"
                id="listingShareBtn"
                data-url="{{ url()->current() }}"
                data-text="{{ $listing->name }}">
            <i class="las la-share"></i>
            <span>Share</span>
        </button>
        

        <button type="button"
                class="listing-side-action"
                onclick="document.getElementById('reviewsSection')?.scrollIntoView({behavior:'smooth'});">
            <i class="las la-star"></i>
            <span>Tap to rate</span>
        </button>
    @if(!empty($listing->website))

        <a href="{{ Str::startsWith($listing->website, ['http://','https://']) ? $listing->website : 'https://'.$listing->website }}"
        target="_blank"
        rel="nofollow noopener"
        class="listing-side-action"
        data-track="website_click"
        data-listing-id="{{ $listing->id }}">
            <i class="las la-globe"></i>
            <span>Visit our Website</span>
        </a>

    @elseif($listing->website_approval_status == 'pending')

        <div class="listing-side-action text-warning">
            <i class="las la-clock"></i>
            <span>Waiting for Approval </span>
        </div>

    @else

        @guest
            <a href="javascript:void(0);"
            onclick="openSbAuthPopup()"
            class="listing-side-action">
                <i class="las la-edit"></i>
                <span>Add Website</span>
            </a>
        @else

            <button type="button"
                    class="listing-side-action"
                    id="showWebsiteInputBtn">
                <i class="las la-edit"></i>
                <span>Add Website</span>
            </button>

            <form action="{{ route('user.listing.updatesection', $listing->id) }}"
                method="POST"
                id="websiteInlineForm"
                style="display:none; padding:10px 12px;">
                @csrf
                @method('PUT')

                <input type="url"
                    name="website"
                    class="form-control mb-2"
                    placeholder="Enter website URL"
                    required>

                <button type="submit" class="btn btn-success btn-sm">
                    Submit
                </button>
            </form>

        @endguest

    @endif
    @if(
        !empty($listing->facebook) ||
        !empty($listing->linkedin) ||
        !empty($listing->instagram) ||
        !empty($listing->twitter) ||
        !empty($listing->youtube)
    )

    <button class="listing-side-action follow-toggle-btn" type="button">
        <i class="las la-share-alt"></i>
        <span>Follow us</span>
        <i class="las la-angle-down follow-arrow"></i>
    </button>

    <div class="follow-links-box">

        @if(!empty($listing->facebook))
            <a href="{{ $listing->facebook }}" target="_blank">Facebook</a>
        @endif

        @if(!empty($listing->linkedin))
            <a href="{{ $listing->linkedin }}" target="_blank">Linkedin</a>
        @endif

        @if(!empty($listing->instagram))
            <a href="{{ $listing->instagram }}" target="_blank">Instagram</a>
        @endif

        @if(!empty($listing->twitter))
            <a href="{{ $listing->twitter }}" target="_blank">Twitter</a>
        @endif

        @if(!empty($listing->youtube))
            <a href="{{ $listing->youtube }}" target="_blank">Youtube</a>
        @endif

    </div>

    @endif

    </div>


                </div>

                {{-- ORIGINAL ENQUIRY FORM --}}
                <div class="enquiry-card mt-3" id="mainEnquiryForm">
                    <h6 class="enquiry-title">
                        Get the List of <span>{{ $listing->category->name ?? 'No Category' }}</span>
                    </h6>

                    <p class="enquiry-subtitle">
                        We'll send you contact details in seconds for free
                    </p>

                    <form action="{{ route('contact.author') }}" method="POST">
                        @csrf
                        @include('themes.servicebazzar.partials.form-guard')
                    <input type="hidden" name="listing_id" value="{{ $listing->id ?? '' }}">

    <input type="hidden" name="category_id" value="{{ $listing->category_id ?? optional($listing->category)->id ?? '' }}">

    <input type="hidden" name="category_name" id="popupCategoryInput" value="{{ optional($listing->category)->name ?? 'Listings' }}">

    <input type="hidden" name="location_id" value="{{ $listing->location_id ?? optional($listing->location)->id ?? '' }}">

    <input type="hidden" name="location_name" value="{{ optional($listing->location)->name ?? $listing->city ?? '' }}">

                        <div class="mb-3">
                            <label class="enquiry-label">Where do you want to shift?</label>
                        </div>

                        <div class="mb-2 input-icon-group">
                            <span class="input-icon"><i class="las la-user"></i></span>
                            <input type="text" name="name" class="form-control enquiry-input" placeholder="Name" required>
                        </div>

                        <div class="mb-2 input-icon-group">
                            <span class="input-icon"><i class="las la-mobile"></i></span>
                            <input type="text" name="phone" class="form-control enquiry-input" placeholder="Mobile Number" required>
                        </div>

                        <div class="form-check enquiry-check mb-3">
                            <input class="form-check-input" type="checkbox" id="policyCheck" required>
                            <label class="form-check-label" for="policyCheck">
                                I Agree to <a href="{{ route('single.slug',38) }}">T&C's Privacy Policy</a>
                            </label>
                        </div>

                        <button type="submit" class="btn enquiry-btn w-100">
                            Send Enquiry <i class="las la-angle-double-right"></i>
                        </button>
                    </form>
                </div>

  @php
    $currentLocationName = session('user_location')
        ?: request('location_name')
        ?: optional($listing->location)->name
        ?: $listing->city;

    $currentCitySlug = \Illuminate\Support\Str::slug(
        $currentLocationName ?? ''
    );
@endphp
@if($peopleAlsoSearches->count() > 0)
    <div class="related-search-card mt-3">
        <div class="related-search-box">

            <h6 class="mb-2">People Also Search For</h6>

            <div class="related-search-tags">

                @foreach($peopleAlsoSearches as $item)
                    @php
                        $itemUrl = !empty($currentCitySlug)
                            ? url($currentCitySlug . '/' . $item['slug'])
                            : route('listings.subcategory', [
                                'slug' => $item['slug']
                            ]);
                    @endphp

                    <a href="{{ $itemUrl }}"
                       class="related-search-tag">
                        {{ $item['name'] }}
                    </a>
                @endforeach

                @if($allRelatedSearches->count() > 10)
                    <button
                        type="button"
                        class="related-search-tag related-search-more"
                        id="openAlsoListedPopup"
                    >
                        More
                    </button>
                @endif

            </div>
        </div>
    </div>

    @if($allRelatedSearches->count() > 10)
        <div class="also-listed-popup-overlay"
             id="alsoListedPopup">

            <div class="also-listed-popup">

                <div class="also-listed-popup-header">
                    <h4>Also listed in</h4>

                    <button
                        type="button"
                        class="also-listed-popup-close"
                        id="closeAlsoListedPopup"
                    >
                        &times;
                    </button>
                </div>

                <div class="also-listed-popup-body">

                    @foreach($allRelatedSearches as $item)
                        @php
                            $itemUrl = !empty($currentCitySlug)
                                ? url($currentCitySlug . '/' . $item['slug'])
                                : route('listings.subcategory', [
                                    'slug' => $item['slug']
                                ]);
                        @endphp

                        <a href="{{ $itemUrl }}"
                           class="related-search-tag">
                            {{ $item['name'] }}
                        </a>
                    @endforeach

                </div>
            </div>
        </div>
    @endif
@endif
    <div id="imagePreviewModal" class="image-preview-modal">
        
        <!-- Close -->
        <span class="image-close" id="closeImagePreview">&times;</span>

        <!-- Counter -->
        <div class="image-counter" id="imageCounter">1 / 1</div>

        <!-- Left Arrow -->
        <span class="nav-arrow left" id="prevImage">&#10094;</span>

        <!-- Image -->
        <img class="image-preview-content" id="previewImage">

        <!-- Right Arrow -->
        <span class="nav-arrow right" id="nextImage">&#10095;</span>

    </div>


            </div>
        </div>
    </div>

    {{-- STICKY FLOATING ENQUIRY FORM --}}
    <div class="sticky-floating-form" id="stickyFloatingForm">
        <div class="enquiry-card">
            <h6 class="enquiry-title">
                Get the List of <span>{{ $listing->category->name ?? 'No Category' }}</span>
            </h6>

            <p class="enquiry-subtitle">
                We'll send you contact details in seconds for free
            </p>

            <form action="{{ route('contact.author') }}" method="POST">
                @csrf
                @include('themes.servicebazzar.partials.form-guard')
                <input type="hidden" name="listing_id" value="{{ $listing->id ?? '' }}">
                <input type="hidden" name="category_id" value="{{ $listing->category->id ?? '' }}">
                <input type="hidden" name="author_id" value="{{ $listing->user->id ?? '' }}">
    <input type="hidden" name="location_id"
        value="{{ request('location_id') ?? ($location->id ?? '') }}">
                <div class="mb-3">
                    <label class="enquiry-label">Where do you want to shift?</label>
                </div>

                <div class="mb-2 input-icon-group">
                    <span class="input-icon"><i class="las la-user"></i></span>
                    <input type="text" name="name" class="form-control enquiry-input" placeholder="Name" required>
                </div>

                <div class="mb-2 input-icon-group">
                    <span class="input-icon"><i class="las la-mobile"></i></span>
                    <input type="text" name="phone" class="form-control enquiry-input" placeholder="Mobile Number" required>
                </div>

                <div class="form-check enquiry-check mb-3">
                    <input class="form-check-input" type="checkbox" id="policyCheckSticky" required>
                    <label class="form-check-label" for="policyCheckSticky">
                        I Agree to <a href="{{ route('single.slug',38) }}">T&C's Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" class="btn enquiry-btn w-100">
                    Send Enquiry <i class="las la-angle-double-right"></i>
                </button>
            </form>
        </div>
    </div>


    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const stickyForm = document.getElementById("stickyFloatingForm");
        const triggerSection = document.getElementById("relatedSearchTrigger");
        const sidebarColumn = document.getElementById("sidebarColumn");
        const listingSection = document.getElementById("listingMediaRow");

        if (!stickyForm || !triggerSection || !sidebarColumn || !listingSection) return;

        function setStickyPosition() {
            if (window.innerWidth <= 991) {
                stickyForm.classList.remove("show");
                return;
            }

            const rect = sidebarColumn.getBoundingClientRect();
        
            stickyForm.style.left = rect.left + "px";
            stickyForm.style.right = "auto";
        }

        function toggleStickyForm() {
            if (window.innerWidth <= 991) {
                stickyForm.classList.remove("show");
                return;
            }

            const triggerRect = triggerSection.getBoundingClientRect();
            const listingRect = listingSection.getBoundingClientRect();
            const stickyTop = 95;
            const stickyHeight = stickyForm.offsetHeight || 320;

            const startSticky = triggerRect.bottom < stickyTop;
            const stopSticky = listingRect.bottom <= (stickyTop + stickyHeight + 30);

            if (startSticky && !stopSticky) {
                setStickyPosition();
                stickyForm.classList.add("show");
            } else {
                stickyForm.classList.remove("show");
            }
        }

        window.addEventListener("scroll", toggleStickyForm);
        window.addEventListener("resize", function () {
            setStickyPosition();
            toggleStickyForm();
        });

        setStickyPosition();
        toggleStickyForm();
    });
    </script>

            </div>
        </div>
    </section>

    @php
    use Illuminate\Support\Str;

    $currentCategory = $listing->category->name ?? '';
    $currentCategorySlug = $listing->category->slug ?? '';
    $currentCitySlug = Str::slug(optional($listing->location)->name ?? '');
@endphp


    {{-- Nearby Areas --}}

@if(isset($nearbyAreas) && $nearbyAreas->count() > 0)

    <div class="popular-city-section">

        <h5 class="popular-title">
            {{ $currentCategory }} in Nearby Areas
        </h5>

        <div class="popular-city-list">

            @foreach($nearbyAreas as $area)

                <a class="spn"
                   href="{{ url(
                       $currentCitySlug . '/' .
                       $currentCategorySlug . '-in-' .
                       Str::slug($area)
                   ) }}">
                    {{ $currentCategory }} in {{ $area }}
                </a>

                @if(!$loop->last)
                    <span>|</span>
                @endif

            @endforeach

        </div>

    </div>

@endif



{{-- Popular Cities --}}

@if(isset($popularCities) && $popularCities->count() > 0)

    <div class="popular-city-section">

        <h5 class="popular-title">
            {{ $currentCategory }} in Popular Cities
        </h5>

        <div class="popular-city-list">

            @foreach($popularCities as $city)

                <a class="spn"
                   href="{{ url(Str::slug($city->name) . '/' . $currentCategorySlug) }}">
                    {{ $currentCategory }} in {{ $city->name }}
                </a>

                @if(!$loop->last)
                    <span>|</span>
                @endif

            @endforeach

        </div>

    </div>

@endif




    
    {{-- Popular Categories / Popular Cities now live once in the shared
         footer (resources/views/themes/servicebazzar/includes/footer.blade.php)
         — the "Nearby Areas" and "{category} in Popular Cities" SEO blocks
         above are a different feature and are kept as-is. --}}


    <div id="shareOverlay" class="share-overlay">
        <div class="share-popup">

            <button type="button" class="share-close" id="shareCloseBtn">
                <i class="las la-times"></i>
            </button>

            <h4>Share With Friends</h4>
            <p>Select from the options below</p>

            <div class="share-options">

                <a href="#" target="_blank" id="shareFacebook" class="share-option">
                    <span class="share-icon fb">
                        <i class="lab la-facebook-f"></i>
                    </span>
                    <small>Facebook</small>
                </a>

                <a href="#" target="_blank" id="shareWhatsapp" class="share-option">
                    <span class="share-icon wa">
                        <i class="lab la-whatsapp"></i>
                    </span>
                    <small>Whatsapp</small>
                </a>

                <button type="button" id="copyShareLink" class="share-option">
                    <span class="share-icon cp">
                        <i class="las la-link"></i>
                    </span>
                    <small>Copy Link</small>
                </button>

            </div>

        </div>
    </div>


    <div id="emailEnquiryModal" class="email-enquiry-modal">

        <div class="email-enquiry-box">

            <button type="button"
                    class="email-enquiry-close"
                    id="closeEmailEnquiryModal">
                ×
            </button>

            <h3>Send Enquiry By Email</h3>

            <h5>
                To - {{ $listing->name }}
            </h5>

            <form action="{{ route('contact.author.email') }}" method="POST">
                @csrf
                @include('themes.servicebazzar.partials.form-guard')

                <input type="hidden" name="listing_id" value="{{ $listing->id }}">

                <div class="form-group">
                    <input type="text"
                        name="name"
                        class="form-control"
                        placeholder="Name*"
                        required>
                </div>

                <div class="form-group">
                    <input type="text"
                        name="phone"
                        class="form-control"
                        placeholder="Mobile No.*"
                        required>
                </div>

                <div class="form-group">
                    <input type="email"
                        name="email"
                        class="form-control"
                        placeholder="Email ID*"
                        required>
                </div>

                <div class="form-group">
                    <textarea name="message"
                            rows="5"
                            class="form-control"
                            placeholder="Body*"
                            required></textarea>
                </div>

                <button type="submit"
                        class="btn btn-primary btn-block">
                    Send
                </button>

            </form>

        </div>

    </div>

    <div id="infoSmsEmailModal" class="info-modal">
        <div class="info-modal-box">

            <button type="button" class="info-modal-close" id="closeInfoModal">
                &times;
            </button>

            <h3>Get information by SMS/Email</h3>

            <form action="{{ route('contact.author') }}" method="POST">
                @csrf
                @include('themes.servicebazzar.partials.form-guard')

                <input type="hidden" name="listing_id" value="{{ $listing->id }}">
                <input type="hidden" name="category_id" value="{{ $listing->category_id }}">
                <input type="hidden" name="category_name" value="{{ $listing->category->name ?? '' }}">
                <input type="hidden" name="location_id" value="{{ $listing->location_id }}">
                <input type="hidden" name="location_name" value="{{ $listing->location->name ?? '' }}">

                <input type="text" name="name" placeholder="Name*" required>
                <input type="number" name="phone" placeholder="Mobile No.*" required>

                <small>Only India Numbers SMS to mobile is FREE</small>

                <input type="email" name="email" placeholder="Email ID">

                <p>* Indicates mandatory fields</p>

                <button type="submit">Send</button>
            </form>

        </div>
    </div>


    {{-- Desktop "Popular Categories" tab-click behavior now lives once in
         popular-accordion.js (loaded from the shared footer). The old
         .popular-city-link click handler that was here matched zero elements
         on this page (no such class existed here) and has been removed
         rather than left to misfire against the shared footer's city links,
         which use different data attributes (data-city, not data-location-name). --}}


    <script>
        function copyAddress() {
            let text = document.getElementById("listingAddress").innerText;

            navigator.clipboard.writeText(text).then(function () {
                alert("Address copied!");
            });
        }

        function toggleHours() {
            let list = document.getElementById("hoursList");
            let icon = document.getElementById("hoursIcon");

            if (list.style.display === "block") {
                list.style.display = "none";
                icon.innerHTML = '<i class="las la-angle-down"></i>';
            } else {
                list.style.display = "block";
                icon.innerHTML = '<i class="las la-angle-up"></i>';
            }
        }

        // Mobile-only Justdial-style "Open Now" status row (top card).
        // Independent of toggleHours()/#hoursList above (a different,
        // pre-existing expandable hours block elsewhere on this page) —
        // separate ids/classes throughout so neither affects the other.
        (function () {
            var jdToggle = document.getElementById('jdStatusToggle');
            var jdPanel = document.getElementById('jdHoursPanel');

            if (!jdToggle || !jdPanel) {
                return;
            }

            jdToggle.addEventListener('click', function () {
                var expanded = jdToggle.getAttribute('aria-expanded') === 'true';
                jdToggle.setAttribute('aria-expanded', String(!expanded));
                jdPanel.classList.toggle('open', !expanded);
            });
        })();
    </script>



    <!-- FIXED REVIEW OTP JAVASCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const reviewForm = document.getElementById('reviewForm');
        const openPopupBtn = document.getElementById('openReviewPopupBtn');

        function openLoginPopup(){
            if (typeof openSbAuthPopup === "function") {
                openSbAuthPopup();
            } else {
                window.location.href = "{{ route('user.login') }}";
            }
        }

        @auth
        if (reviewForm) {
            reviewForm.addEventListener('submit', function (e) {
                e.preventDefault();

                let formData = new FormData(reviewForm);

                fetch("{{ route('review') }}", {
                    method: "POST",
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(async response => {
                    let data = await response.json();
                    if (!response.ok) throw data;
                    return data;
                })
                .then(data => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: data.message || 'Review submitted successfully'
                    }).then(() => {
                        location.reload();
                    });
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Something went wrong'
                    });
                });
            });
        }
        @endauth

        @guest
        if (openPopupBtn) {
            openPopupBtn.addEventListener('click', function () {
                let review = document.getElementById('guest_review_text').value;
                let ratings = document.getElementById('guest_review_ratings').value;

                if (!ratings) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Rating required',
                        text: 'Please select rating'
                    });
                    return;
                }

                if (!review.trim()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Review required',
                        text: 'Please write your review'
                    });
                    return;
                }

                openLoginPopup();
            });
        }
        @endguest
    });
    </script>


    <script>
    document.addEventListener("DOMContentLoaded", function () {

        const quickStars = document.querySelectorAll("#quickRatingStars .star-btn");
        const quickRatingValue = document.getElementById("quick_rating_value");
        const quickRatingText = document.getElementById("quickRatingText");

        function openLoginPopup() {
            if (typeof openSbAuthPopup === "function") {
                openSbAuthPopup();
            } else {
                window.location.href = "{{ route('user.login') }}";
            }
        }

        quickStars.forEach(function (star) {
            star.addEventListener("click", function (e) {
                e.preventDefault();

                let ratingValue = this.dataset.value;

                @guest
                    openLoginPopup();
                    return;
                @endguest

                if (quickRatingValue) {
                    quickRatingValue.value = ratingValue;
                }

                quickStars.forEach(function (s) {
                    if (parseInt(s.dataset.value) <= parseInt(ratingValue)) {
                        s.classList.add("active");
                    } else {
                        s.classList.remove("active");
                    }
                });

                if (quickRatingText) {
                    quickRatingText.innerText = "You selected " + ratingValue + " star";
                }

                const ratingPopupEl = document.getElementById("ratingPopup");

                if (ratingPopupEl) {
                    const ratingPopup = new bootstrap.Modal(ratingPopupEl);
                    ratingPopup.show();
                }
            });
        });

    });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('.tab-scroll-btn');

        buttons.forEach(button => {
            button.addEventListener('click', function () {
                buttons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                const targetSelector = this.getAttribute('data-target');
                const target = document.querySelector(targetSelector);

                if (target) {
                    const offset = 100;
                    const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;

                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    });
    </script>

    {{--
        NOTE: @endsection used to be right here, closing the 'content'
        section ~1,370 lines before the actual end of this file. Everything
        after this point (through the rest of the file) was therefore
        rendering as raw top-level output OUTSIDE the 'content' section —
        which, with @extends, still gets echoed to the response, but BEFORE
        the deferred parent-layout render (the part that outputs
        <!DOCTYPE html>, <html>, <head>, etc.). The practical effect: the
        page's actual first response bytes were a stray <script> tag from
        deep in this file, with <!DOCTYPE html> arriving ~36KB into the
        response instead of as the very first thing — which is exactly
        what puts a browser into Quirks Mode (document.compatMode ===
        "BackCompat", document.scrollingElement === null). That, not
        anything CSS-related, was the real root cause behind sticky/fixed
        positioning and window scroll-event handling being unreliable
        throughout this page. Moved @endsection to the true end of the
        file (nothing here was deleted) so the whole page is captured in
        the 'content' section and the parent layout — and its <!DOCTYPE
        html> — renders first, as it should.
    --}}
    @push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.css"/>
    <style>
        iframe{
            width: 100%;
        }
        .guest-chat-messages{
            max-height: 280px;
            overflow-y: auto;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 12px;
            background: #fafafa;
        }
        .guest-chat-message{
            margin-bottom: 10px;
            display: flex;
            flex-direction: column;
        }
        .guest-chat-message.is-guest{
            align-items: flex-end;
        }
        .guest-chat-message .bubble{
            max-width: 75%;
            padding: 8px 12px;
            border-radius: 12px;
            background: #ffffff;
            border: 1px solid #e0e0e0;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .guest-chat-message.is-guest .bubble{
            background: #e8f7ff;
            border-color: #cfe9f6;
        }
        .guest-chat-message .meta{
            font-size: 12px;
            color: #6c757d;
            margin-top: 4px;
        }
        .guest-chat-modal .modal-dialog{
            max-width: 520px;
        }
        .guest-chat-modal .modal-body{
            padding: 20px;
        }
        .guest-chat-modal .form-group label{
            font-size: 0.9rem;
            margin-bottom: 0.35rem;
        }
        .guest-chat-loading{
            min-height: 220px;
            font-weight: 500;
            color: #495057;
        }
        .guest-chat-loading.is-hidden{
            display: none !important;
        }
        .guest-chat-head{
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 8px;
        }
        .guest-chat-owner{
            font-weight: 600;
        }
        .guest-chat-status{
            font-size: 12px;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .guest-chat-status .dot{
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            background: #adb5bd;
        }
        .guest-chat-status .dot.online{
            background: #2ecc71;
        }
        .guest-chat-status .dot.offline{
            background: #ff6b6b;
        }
        .guest-chat-note{
            font-size: 12px;
        }
        .guest-chat-sound{
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .guest-chat-otp{
            letter-spacing: 6px;
            text-align: center;
            font-weight: 600;
        }
        .guest-chat-modal .input-group .form-control{
            border-right: 0;
        }
        .guest-chat-modal .input-group-append .btn{
            border-radius: 0 .25rem .25rem 0;
        }
        .guest-chat-modal .input-group{
            border: 1px solid #e3e7ed;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }
        .guest-chat-modal .input-group .form-control{
            border: 0;
            box-shadow: none;
        }
        .guest-chat-modal .input-group-append .btn{
            border-radius: 0;
        }

        .single-comment{
        display:flex;
        gap:12px;
        margin-top:18px;
        padding:15px;
        border:1px solid #eee;
        border-radius:8px;
    }

    .comment-body{
        flex:1;
    }

    .comment-head{
        display:flex;
        justify-content:space-between;
        align-items:center;
    }

    .delete-comment-btn{
        border:none;
        background:#eee;
        padding:7px 12px;
        cursor:pointer;
    }

    .like-btn.liked{
        color:#0d6efd;
        font-weight:600;
    }

    .like-btn.liked i{
        color:#0d6efd;
    }
    @media (min-width:768px){

        .jd-rate-box{
            text-align:center !important;
            width:100%;
        }

        .jd-rate-stars{
            justify-content:center !important;
            align-items:center !important;
        }

        .jd-rate-title,
        .jd-rate-text{
            text-align:center !important;
            display:block !important;
        }

        .text-md-right{
            text-align:center !important;
        }
    }
    </style>
    @endpush
    @push('js')
    {{-- These two used to load on every page in the site layout, where nothing
         else called them. This is the only page that does, so they load here. --}}
    <link rel="stylesheet" href="{{ asset($themeAsset.'css/lightcase.css') }}">
    <link rel="stylesheet" href="{{ asset($themeAsset.'css/starrr.css') }}">
    <script src="{{ asset($themeAsset.'js/lightcase.min.js') }}"></script>
    <script src="{{ asset($themeAsset.'js/starrr.js') }}"></script>
    <script>
        (function($){
            "use strict"
            $('a[data-rel^=lightcase]').lightcase();
            $('.starrr').starrr({
                max: 5,
                rating: $('input[name=ratings]').val(),
                change: function(e, value){
                $('input[name=ratings]').val(value).trigger('input');
                }
            })
        })(jQuery)
    </script>
    @endpush

    <script>
    function copyAddress() {
        let text = document.getElementById('listingAddress').innerText;

        navigator.clipboard.writeText(text).then(function () {
            alert('Address copied!');
        });
    }
    </script>


    <script>
    document.addEventListener('DOMContentLoaded', function () {

        document.querySelectorAll('.tab-scroll-btn').forEach(button => {
            button.addEventListener('click', function () {
                document.querySelectorAll('.tab-scroll-btn').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                let target = document.querySelector(this.getAttribute('data-target'));
                if (target) {
                    /*
                     * BUG FIX: this used to be `target.offsetTop - 100`.
                     * offsetTop is measured from the target's nearest
                     * positioned ancestor, not the page — and Bootstrap's
                     * grid columns (.col-lg-9, the ancestor every tab
                     * section sits inside) have `position: relative` by
                     * default. So offsetTop was measuring each section's
                     * distance from the TOP of that column box, not from
                     * the top of the document — landing on a scroll
                     * position hundreds of pixels short of the real
                     * target (e.g. clicking Services landed on Overview,
                     * clicking Overview landed near the top card). Using
                     * getBoundingClientRect().top + window.pageYOffset
                     * (document-relative, same formula the other
                     * tab-scroll-btn click handler already uses) fixes it.
                     */
                    const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - 100;

                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });

    });

    function toggleInlineSection(section) {
        let viewBox = document.getElementById(section + '_view');
        let editBox = document.getElementById(section + '_edit');

        if (!viewBox || !editBox) return;

        if (editBox.style.display === 'none' || editBox.style.display === '') {
            viewBox.style.display = 'none';
            editBox.style.display = 'block';

            
        } else {
            viewBox.style.display = 'block';
            editBox.style.display = 'none';
        }
    }

    function addFaqRow() {
        document.getElementById('faqItems').insertAdjacentHTML('beforeend', `
            <div class="row mb-2 faq-row">
                <div class="col-md-5">
                    <input type="text" name="question[]" class="form-control" placeholder="Question">
                </div>
                <div class="col-md-5">
                    <input type="text" name="answer[]" class="form-control" placeholder="Answer">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeFaqRow(this)">Remove</button>
                </div>
            </div>
        `);
    }

    function removeFaqRow(button) {
        let row = button.closest('.faq-row');
        if (row) {
            row.remove();
        }
    }
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {

        const modal = document.getElementById('bestPriceModal');
        const closeBtn = document.getElementById('closeBestPriceModal');
        const categoryText = document.getElementById('popupCategoryName');
        const categoryInput = document.getElementById('popupCategoryInput');

        function closeEnquiryModal() {

            modal.classList.remove('show');

            document.body.style.overflow = '';
            document.body.style.paddingRight = '';

            document.body.classList.remove('modal-open');

            document.querySelectorAll('.modal-backdrop').forEach(function(el){
                el.remove();
            });
        }

        document.querySelectorAll('.open-enquiry-modal').forEach(function(button) {

            button.addEventListener('click', function () {

                const category = this.getAttribute('data-category') || 'Listings';

                categoryText.textContent = category;
                categoryInput.value = category;

                modal.classList.add('show');

                document.body.style.overflow = 'hidden';
                document.body.style.paddingRight = '0px';
            });

        });

        if (closeBtn) {

            closeBtn.addEventListener('click', function () {
                closeEnquiryModal();
            });

        }

        if (modal) {

            modal.addEventListener('click', function (e) {

                if (e.target === modal) {
                    closeEnquiryModal();
                }

            });

        }

    });
    </script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const city = localStorage.getItem('jf_city') || '';

        if (!city) return;

        document.querySelectorAll('.related-search-tag').forEach(link => {
            try {
                const url = new URL(link.href, window.location.origin);
                const slugCity = city.toLowerCase().trim().replace(/\s+/g, '-');

                const parts = url.pathname.split('/').filter(Boolean);
                const lastPart = parts[parts.length - 1];

                link.href = window.location.origin + '/' + slugCity + '/' + lastPart;
            } catch (e) {}
        });
    });
    </script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {

        const images = document.querySelectorAll(".listing-photo-click");
        const modal = document.getElementById("imagePreviewModal");
        const preview = document.getElementById("previewImage");
        const counter = document.getElementById("imageCounter");

        let currentIndex = 0;

        // OPEN IMAGE
        images.forEach((img, index) => {
            img.addEventListener("click", () => {
                currentIndex = index;
                showImage();
                modal.classList.add("show");
            });
        });

        function showImage() {
            preview.src = images[currentIndex].dataset.src;

            // Counter update
            counter.innerText = (currentIndex + 1) + " / " + images.length;
        }

        // NEXT
        document.getElementById("nextImage").onclick = function () {
            currentIndex = (currentIndex + 1) % images.length;
            showImage();
        };

        // PREV
        document.getElementById("prevImage").onclick = function () {
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            showImage();
        };

        // CLOSE
        document.getElementById("closeImagePreview").onclick = function () {
            modal.classList.remove("show");
        };

        // CLICK OUTSIDE CLOSE
        modal.addEventListener("click", function (e) {
            if (e.target === modal) {
                modal.classList.remove("show");
            }
        });

    });
    </script>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".track-click").forEach(function (btn) {
            btn.addEventListener("click", function () {

                const payload = {
                    listing_id: this.dataset.listingId || '',
                    category_id: this.dataset.categoryId || '',
                    location_id: this.dataset.locationId || '',
                    type: this.dataset.type || '',
                    clicked_number: this.dataset.number || ''
                };

                fetch("{{ route('listing.click.track') }}", {
                    method: "POST",
                    keepalive: true,
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify(payload)
                }).catch(function(error) {
                    console.log('Click track error:', error);
                });

            });
        });
    });
    </script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {

        let selectedRating = 0;
        const ratingModalEl = document.getElementById('ratingPopup');
        const quickRatingValue = document.getElementById('quick_rating_value');
        const quickRatingText = document.getElementById('quickRatingText');

        const isLoggedIn = @json(auth()->check());

        let modal = null;

        if (ratingModalEl && isLoggedIn) {
            modal = new bootstrap.Modal(ratingModalEl);
        }

        function openLoginPopup() {
            if (typeof openSbAuthPopup === "function") {
                openSbAuthPopup();
            } else {
                window.location.href = "{{ route('user.login') }}";
            }
        }

        function ajaxPost(url, data) {
            return fetch(url, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify(data)
            })
            .then(async res => {
                let text = await res.text();

                try {
                    text = JSON.parse(text);
                } catch (e) {
                    console.error("HTML/Error Response:", text);
                    throw new Error("Server returned HTML, not JSON.");
                }

                if (res.status === 401) {
                    openLoginPopup();
                    return null;
                }

                if (!res.ok) {
                    throw text;
                }

                return text;
            });
        }

        document.querySelectorAll('#quickRatingStars .star-btn').forEach(star => {
            star.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                selectedRating = this.dataset.value;

                if (quickRatingValue) {
                    quickRatingValue.value = selectedRating;
                }

                document.querySelectorAll('#quickRatingStars .star-btn').forEach(s => {
                    s.classList.toggle('active', Number(s.dataset.value) <= Number(selectedRating));
                });

                if (quickRatingText) {
                    quickRatingText.innerText = selectedRating + ' Star Rating';
                }

                if (!isLoggedIn) {
                    openLoginPopup();
                    return false;
                }

                if (modal) {
                    modal.show();
                }

                return false;
            }, true);
        });

        if (isLoggedIn) {
            document.getElementById('submitRatingBtn')?.addEventListener('click', function () {

                ajaxPost("{{ route('review') }}", {
                    listing_id: "{{ $listing->id }}",
                    ratings: selectedRating,
                    review: document.getElementById('popup_review')?.value || ''
                })
                .then((res) => {
                    if (res !== null) {
                        location.reload();
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert(err.message || "Rating submit failed.");
                });

            });
        }

    });
    </script>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const mobileInput = document.getElementById("ytMobileSearchInput");

        if (mobileInput) {
            mobileInput.value = @json($listing->name ?? '');
        }
    });
    </script>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const backBtn = document.getElementById("ytSearchBackBtn");

        if(backBtn){
            backBtn.onclick = function(e){
                e.preventDefault();
                e.stopImmediatePropagation();

                window.location.href = "{{ url(\Illuminate\Support\Str::slug(optional($listing->location)->name ?? '') . '/' . ($listing->category->slug ?? '')) }}";
            };
        }
    });


    document.querySelectorAll('.star-btn').forEach(function(star){

        star.addEventListener('click', function(){

            let rating = this.dataset.value;

            document.getElementById('quick_rating_value').value = rating;

            document.getElementById('guest_review_ratings').value = rating;

            document.getElementById('reviewFormBox').style.display = 'block';

        });

    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {

        function openReviewBox(rating) {
            let box = document.getElementById('reviewFormBox');
            let input = document.getElementById('guest_review_ratings');

            if (input) input.value = rating;
            if (box) box.classList.remove('d-none');
        }

        // Top wale custom stars
        document.querySelectorAll('.star-btn').forEach(function (star) {
            star.addEventListener('click', function () {
                openReviewBox(this.dataset.value);
            });
        });

        // Niche wale starrr plugin stars
        if (typeof $ !== 'undefined' && $('#star2').length) {
            $('#star2').on('starrr:change', function (e, value) {
                openReviewBox(value);
            });
        }

    });
    </script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {

        const csrfToken = "{{ csrf_token() }}";

        function openLoginPopup(){
            if (typeof openSbAuthPopup === "function") {
                openSbAuthPopup();
            } else {
                window.location.href = "{{ route('user.login') }}";
            }
        }

        document.querySelectorAll(".comment-toggle-btn").forEach(function(btn){
            btn.addEventListener("click", function(){
                let ratingId = this.dataset.ratingId;
                let box = document.getElementById("commentBox" + ratingId);

                if (box) {
                    box.style.display = box.style.display === "none" ? "block" : "none";
                }
            });
        });

        document.querySelectorAll(".like-btn").forEach(function(btn){
            btn.addEventListener("click", function(){
                let ratingId = this.dataset.ratingId;
                let countBox = this.querySelector(".like-count");

                fetch("{{ route('rating.like') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({
                        rating_id: ratingId
                    })
                })
                .then(async function(res){
                    if (res.status === 401) {
                        openLoginPopup();
                        return null;
                    }

                    const data = await res.json();

                    if (!res.ok) {
                        throw data;
                    }

                    return data;
                })
                .then(function(data){
                    if (!data) return;

                    if (!data.status) {
                        openLoginPopup();
                        return;
                    }

                    if (countBox) {
                        countBox.innerText = data.count;
                    }

                    if (data.liked) {
                        btn.classList.add("liked");
                    } else {
                        btn.classList.remove("liked");
                    }
                })
                .catch(function(error){
                    if (error && error.message === "Please login first") {
                        openLoginPopup();
                        return;
                    }

                    console.log(error);
                });
            });
        });

        document.querySelectorAll(".comment-post-btn").forEach(function(btn){
            btn.addEventListener("click", function(){
                let ratingId = this.dataset.ratingId;
                let input = document.getElementById("commentInput" + ratingId);
                let comment = input ? input.value.trim() : "";

                if (comment === "") {
                    alert("Please write comment");
                    return;
                }

                fetch("{{ route('rating.comment') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({
                        rating_id: ratingId,
                        comment: comment
                    })
                })
                .then(async function(res){
                    if (res.status === 401) {
                        openLoginPopup();
                        return null;
                    }

                    const data = await res.json();

                    if (!res.ok) {
                        throw data;
                    }

                    return data;
                })
                .then(function(data){
                    if (!data) return;

                    if (!data.status) {
                        openLoginPopup();
                        return;
                    }

                    input.value = "";

                    let list = document.getElementById("commentsList" + ratingId);

                    if (list) {
                        list.insertAdjacentHTML("afterbegin", `
                            <div class="single-comment" id="comment_${data.id}">
                                <img src="${data.image}" class="comment-user-img">
                                <div class="comment-body">
                                    <div class="comment-head">
                                        <b>${data.name}</b>
                                        <span>Now</span>
                                        <button type="button" class="delete-comment-btn" data-id="${data.id}">
                                            <i class="las la-trash"></i> Delete Comment
                                        </button>
                                    </div>
                                    <p>${data.comment}</p>
                                </div>
                            </div>
                        `);
                    }

                    let countBox = document.querySelector(
                        `.comment-toggle-btn[data-rating-id="${ratingId}"] .comment-count`
                    );

                    if (countBox) {
                        countBox.innerText = data.count;
                    }
                })
                .catch(function(error){
                    if (error && error.message === "Please login first") {
                        openLoginPopup();
                        return;
                    }

                    console.log(error);
                });
            });
        });

        document.addEventListener("click", function(e){
            let btn = e.target.closest(".delete-comment-btn");
            if (!btn) return;

            let id = btn.dataset.id;

            fetch("/rating/comment/" + id, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    "Accept": "application/json"
                }
            })
            .then(async function(res){
                if (res.status === 401) {
                    openLoginPopup();
                    return null;
                }

                return await res.json();
            })
            .then(function(data){
                if (!data) return;

                if (data.status) {
                    document.getElementById("comment_" + id)?.remove();
                }
            });
        });

    });
    </script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {

        const overlay = document.getElementById("shareOverlay");
        const closeBtn = document.getElementById("shareCloseBtn");
        const fb = document.getElementById("shareFacebook");
        const wa = document.getElementById("shareWhatsapp");
        const copyBtn = document.getElementById("copyShareLink");

        let currentShareUrl = window.location.href;

        document.querySelectorAll(".share-review-btn").forEach(function(btn){
            btn.addEventListener("click", function(){
                currentShareUrl = this.dataset.url || window.location.href;
                let text = this.dataset.text || "Service Bazzar";

                fb.href = "https://www.facebook.com/sharer/sharer.php?u=" + encodeURIComponent(currentShareUrl);
                wa.href = "https://api.whatsapp.com/send?text=" + encodeURIComponent(text + " " + currentShareUrl);

                overlay.classList.add("show");
            });
        });

        closeBtn.addEventListener("click", function(){
            overlay.classList.remove("show");
        });

        overlay.addEventListener("click", function(e){
            if(e.target === overlay){
                overlay.classList.remove("show");
            }
        });

        copyBtn.addEventListener("click", function(){
            navigator.clipboard.writeText(currentShareUrl).then(function(){
                copyBtn.querySelector("small").innerText = "Copied!";
                setTimeout(function(){
                    copyBtn.querySelector("small").innerText = "Copy Link";
                }, 1500);
            });
        });

    });
    </script>
    <script>
    function toggleReviewMenu(id){
        document.querySelectorAll('.review-menu-dropdown').forEach(function(menu){
            if(menu.id !== 'reviewMenu' + id){
                menu.classList.remove('show');
            }
        });

        let menu = document.getElementById('reviewMenu' + id);
        if(menu){
            menu.classList.toggle('show');
        }
    }

    function toggleReviewEdit(id){
        let editBox = document.getElementById('review_edit_' + id);
        let menu = document.getElementById('reviewMenu' + id);

        if(editBox){
            editBox.style.display =
                (editBox.style.display === 'none' || editBox.style.display === '')
                ? 'block'
                : 'none';
        }

        if(menu){
            menu.classList.remove('show');
        }
    }

    document.addEventListener('click', function(e){

        if(!e.target.closest('.review-menu-wrap')){
            document.querySelectorAll('.review-menu-dropdown').forEach(function(menu){
                menu.classList.remove('show');
            });
        }

        let deleteBtn = e.target.closest('.delete-review-btn');

        if(deleteBtn){
            let id = deleteBtn.dataset.id;

            if(!confirm('Delete this review?')) return;

            fetch('/review-delete/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.status){
                    document.getElementById('review-' + id)?.remove();
                    document.getElementById('review_edit_' + id)?.remove();
                }
            });
        }
    });
    </script>
    <script>
    document.addEventListener('click', function(e){

        let star = e.target.closest('.edit-star');
        if(!star) return;

        let box = star.closest('.edit-star-box');
        let ratingId = box.dataset.ratingId;
        let value = parseInt(star.dataset.value);

        let input = document.getElementById('edit_rating_value_' + ratingId);
        if(input){
            input.value = value;
        }

        box.querySelectorAll('.edit-star').forEach(function(s){
            s.classList.toggle('active', parseInt(s.dataset.value) <= value);
        });

    });
    </script>
    <script>
    document.addEventListener("click", function(e){

        const star = e.target.closest(".add-review-star");
        if(!star) return;

        const rating = parseInt(star.dataset.value);

        document.getElementById("guest_review_ratings").value = rating;
        document.getElementById("reviewStatusText").innerText = "You rated";

        document.querySelectorAll(".add-review-star").forEach(function(s){
            s.classList.toggle("active", parseInt(s.dataset.value) <= rating);
        });

        document.getElementById("reviewFormBox").classList.remove("d-none");
    });

    </script>
    <script>
    document.addEventListener("DOMContentLoaded", function(){

        document.querySelectorAll('.review-filter-btn').forEach(btn => {

            btn.addEventListener('click', function(){

                document.querySelectorAll('.review-filter-btn')
                    .forEach(b => b.classList.remove('active'));

                this.classList.add('active');

                let type = this.dataset.sort;

                let container = document.getElementById('reviewsContainer');

                let reviews = Array.from(
                    container.querySelectorAll('.reviewer')
                );

                reviews.sort(function(a,b){

                    let ra = parseInt(
                        a.querySelector('.review-item').dataset.rating
                    );

                    let rb = parseInt(
                        b.querySelector('.review-item').dataset.rating
                    );

                    let da = parseInt(
                        a.querySelector('.review-item').dataset.date
                    );

                    let db = parseInt(
                        b.querySelector('.review-item').dataset.date
                    );

                    if(type === 'latest'){
                        return db - da;
                    }

                    if(type === 'high'){
                        return rb - ra;
                    }

                    if(type === 'low'){
                        return ra - rb;
                    }

                    return db - da;
                });

                reviews.forEach(function(review){
                    container.appendChild(review);
                });

            });

        });

    });
    </script>
    <script>
    document.addEventListener("DOMContentLoaded", function(){

        let visible = 10;

        const btn = document.getElementById("loadMoreReviewsBtn");

        if(!btn) return;

        btn.addEventListener("click", function(){

            let hiddenReviews = document.querySelectorAll(
                ".review-item-load[style*='display:none']"
            );

            for(let i = 0; i < 10 && i < hiddenReviews.length; i++){
                hiddenReviews[i].style.display = "block";
            }

            visible += 10;

            if(document.querySelectorAll(".review-item-load[style*='display:none']").length === 0){
                btn.style.display = "none";
            }

        });

    });
    document.querySelectorAll(".quick-star").forEach(star => {

        star.addEventListener("click", function () {

            let value = this.dataset.value;

            document.getElementById("quick_rating_value").value = value;

            document.querySelectorAll(".quick-star").forEach(s => {
                s.classList.remove("active");
            });

            document.querySelectorAll(".quick-star").forEach(s => {
                if (parseInt(s.dataset.value) <= value) {
                    s.classList.add("active");
                }
            });

        });

    });
    </script>
    <script>
    document.addEventListener("click", function(e){

        const star = e.target.closest(".quick-star");
        if(!star) return;

        const rating = parseInt(star.dataset.value);

        document.getElementById("quick_rating_value").value = rating;
        document.getElementById("quickRatingText").innerText = "You rated";

        document.querySelectorAll(".quick-star").forEach(function(s){
            s.classList.toggle("active", parseInt(s.dataset.value) <= rating);
        });

    });
    </script>
    <script>
    document.addEventListener("DOMContentLoaded", function(){

        const listingShareBtn = document.getElementById("listingShareBtn");
        const overlay = document.getElementById("shareOverlay");
        const fb = document.getElementById("shareFacebook");
        const wa = document.getElementById("shareWhatsapp");

    if(listingShareBtn){
        listingShareBtn.addEventListener("click", function(e){
            e.preventDefault();
            e.stopPropagation();

            let url = this.dataset.url || window.location.href;
            let text = this.dataset.text || "Service Bazzar";

            if(fb){
                fb.href = "https://www.facebook.com/sharer/sharer.php?u=" + encodeURIComponent(url);
            }

            if(wa){
                wa.href = "https://api.whatsapp.com/send?text=" + encodeURIComponent(text + " " + url);
            }

            if(overlay){
                overlay.classList.add("show");
            }
        });
    }

    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        const modal = document.getElementById('bestPriceModal');
        const closeBtn = document.getElementById('closeBestPriceModal');

        /*
         * This used to be a plain page-scoped flag, so the popup came back on
         * every refresh and on every listing the visitor opened. sessionStorage
         * keeps it across page loads within one visit, and clears itself when
         * the browser session ends — so a returning visitor sees it once again.
         *
         * Only the scroll-triggered popup is suppressed; the buttons that open
         * the same modal on purpose keep working.
         */
        const POPUP_KEY = 'sb_best_price_popup_shown';

        function popupAlreadyShown() {
            try {
                return sessionStorage.getItem(POPUP_KEY) === '1';
            } catch (e) {
                return false;   // private mode / storage blocked
            }
        }

        function rememberPopupShown() {
            try {
                sessionStorage.setItem(POPUP_KEY, '1');
            } catch (e) {}
        }

        let popupShown = popupAlreadyShown();

        function openBestPriceOnScroll() {
            if (popupShown) return;

            if (window.scrollY > 250) {
                modal.classList.add('show');
                popupShown = true;
                rememberPopupShown();
                window.removeEventListener('scroll', openBestPriceOnScroll);
            }
        }

        if (!popupShown) {
            window.addEventListener('scroll', openBestPriceOnScroll);
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                modal.classList.remove('show');
            });
        }

        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.remove('show');
            }
        });

    });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function(){

        const modal = document.getElementById('emailEnquiryModal');

        document.querySelectorAll('.open-email-enquiry-modal').forEach(btn=>{
            btn.addEventListener('click', function(){
                modal.classList.add('show');
            });
        });

        document.getElementById('closeEmailEnquiryModal')
            .addEventListener('click', function(){
                modal.classList.remove('show');
            });

        modal.addEventListener('click', function(e){
            if(e.target === modal){
                modal.classList.remove('show');
            }
        });

    });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function(){

        const modal = document.getElementById('infoSmsEmailModal');

        document.querySelectorAll('.open-info-modal').forEach(function(btn){
            btn.addEventListener('click', function(){
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            });
        });

        document.getElementById('closeInfoModal').addEventListener('click', function(){
            modal.classList.remove('show');
            document.body.style.overflow = '';
        });

        modal.addEventListener('click', function(e){
            if(e.target === modal){
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        });

    });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        function checkPopupState() {

            const hasOpenPopup =
                document.querySelector('.best-price-modal.show') ||
                document.querySelector('.share-overlay.show') ||
                document.querySelector('.info-modal.show') ||
                document.querySelector('.modal.show') ||
                document.querySelector('.modal.fade.show');

            if (hasOpenPopup) {
                document.body.style.overflow = 'hidden';
                document.body.style.height = '100vh';
            } else {
                document.body.style.overflow = '';
                document.body.style.height = '';
            }
        }

        setInterval(checkPopupState, 200);

    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function(){

        const btn = document.querySelector('.follow-toggle-btn');
        const box = document.querySelector('.follow-links-box');

        if(btn && box){

            btn.addEventListener('click', function(){

                box.classList.toggle('show');

                const arrow = btn.querySelector('.follow-arrow');

                if(box.classList.contains('show')){
                    arrow.classList.remove('la-angle-down');
                    arrow.classList.add('la-angle-up');
                }else{
                    arrow.classList.remove('la-angle-up');
                    arrow.classList.add('la-angle-down');
                }

            });

        }

    });
    </script>
    <script>
    document.addEventListener('click', function(e){

        const btn = e.target.closest('#listingShareBtn');
        if(!btn) return;

        e.preventDefault();

        const url = btn.dataset.url;
        const text = btn.dataset.text;

        if(navigator.share){
            navigator.share({
                title: text,
                text: text,
                url: url
            });
        }else{
            navigator.clipboard.writeText(url);
            alert('Link copied');
        }

    });


    </script>

    <script>
    document.addEventListener('click', function(e){

        const btn = e.target.closest('.copy-listing-link');
        if(!btn) return;

        e.preventDefault();

        const link = btn.dataset.link || window.location.href;
        const textEl = btn.querySelector('small');

        function copiedDone(){
            if(textEl){
                textEl.innerText = 'Copied!';
                setTimeout(() => {
                    textEl.innerText = 'Copy Link';
                }, 1500);
            }else{
                alert('Link copied');
            }
        }

        if(navigator.clipboard && window.isSecureContext){
            navigator.clipboard.writeText(link).then(copiedDone);
        }else{
            const input = document.createElement('input');
            input.value = link;
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
            copiedDone();
        }
    });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const showBtn = document.getElementById('showWebsiteInputBtn');
        const websiteForm = document.getElementById('websiteInlineForm');

        if (showBtn && websiteForm) {
            showBtn.addEventListener('click', function () {
                showBtn.style.display = 'none';
                websiteForm.style.display = 'block';
                websiteForm.querySelector('input[name="section_value"]').focus();
            });
        }
    });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const openButton = document.getElementById('openAlsoListedPopup');
        const closeButton = document.getElementById('closeAlsoListedPopup');
        const popup = document.getElementById('alsoListedPopup');

        if (!openButton || !closeButton || !popup) {
            return;
        }

        function openPopup() {
            popup.classList.add('show');
            document.body.classList.add('also-listed-popup-open');
        }

        function closePopup() {
            popup.classList.remove('show');
            document.body.classList.remove('also-listed-popup-open');
        }

        openButton.addEventListener('click', openPopup);
        closeButton.addEventListener('click', closePopup);

        popup.addEventListener('click', function (event) {
            if (event.target === popup) {
                closePopup();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closePopup();
            }
        });
    });
    </script>
    @endsection