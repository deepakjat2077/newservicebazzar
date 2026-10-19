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


    @media (max-width: 991px){

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


        @media (max-width: 991px){

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

        @media (max-width: 991px){

    
        

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

    .jd-star.active{
        background:#ff5a00;
        color:#fff;
        border-color:#ff5a00;
    }

    @media (hover:hover){
        #quickRatingStars:hover .jd-star{ background:#fff; color:#bbb; border-color:#ddd; }
        #quickRatingStars:hover .jd-star:hover,
        #quickRatingStars .jd-star:has(~ .jd-star:hover){
            background:#ff5a00; color:#fff; border-color:#ff5a00;
        }
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
        @media (max-width: 991px) {

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
        background: #12874f;
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

    @media (max-width: 991px){
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

   
    @media (max-width: 991px) {
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

    @media (max-width: 991px) {

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

    @media (min-width: 992px){
    
        .listing-card-section{
            margin-top:0;
        }
    }



    @media (max-width: 991px){

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


                        
                        .orrrr{font-size: 14px;
        font-weight: 400;
        color: #111;
    }

                .utlink{
                font-size: 14px;
        font-weight: 400;   
                }

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

    @media(max-width: 991px){

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

    @media(max-width: 991px){

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

    @media (min-width: 992px){
        .review-actions{ gap:12px; margin-top:14px; }
        .review-action-btn{
            display:inline-flex;
            align-items:center;
            font-size:14px;
            font-weight:600;
            color:#333;
            padding:7px 14px;
            border-radius:20px;
            transition:background .15s ease, color .15s ease;
        }
        .review-action-btn i{ font-size:19px; margin-right:6px; }
        .review-action-btn:hover{ background:#f1f3f5; color:#111; }
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
 
    @media (hover:hover){
        #addReviewStars:hover .add-review-star{ color:#d6d6d6; }
        #addReviewStars:hover .add-review-star:hover,
        #addReviewStars .add-review-star:has(~ .add-review-star:hover){ color:#ff5a00; }
    }
    .review-cancel-btn{ display:block; width:100%; margin-top:8px; border:1px solid #d5dae5; background:#fff; color:#555;
        border-radius:6px; padding:9px; font-size:14px; cursor:pointer; }
    .review-cancel-btn:hover{ background:#f5f6f9; }

    @media (max-width: 991px){

        .review-stars .fas,
        .review-stars .far{
            font-size:12px;
        }

    }


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
       Hidden by default (desktop/tablet); only the max-width: 991px block
       below turns it on, so wider viewports are unaffected. */
    /* style.css sizes bare h4 as calc(1.275rem - 1.7vw), which shrinks as the
       screen widens — this heading came out at 3px at 1024. Below 992px the
       phone layout hides it. */
    @media (min-width: 992px) and (max-width: 1200px) {
        .related-searches-title { font-size: 20px !important; }
    }

    /* iPads (768–991px) get the phone layout of this page (its phone rules run
       up to 991px). Bootstrap's own md classes in the top card still switch on
       at 768px and would line name, icon buttons, Enquire Now and the rating box
       up side by side — undo just those so the card stacks exactly like a phone. */
    @media (min-width: 768px) and (max-width: 991px) {
        #listingTopCard.p-md-4, #listingTopCard .p-md-4 { padding: 1rem !important; }
        #listingTopCard .d-md-flex { display: block !important; }
        #listingTopCard .mb-md-0 { margin-bottom: 1rem !important; }
        #listingTopCard .text-md-right { text-align: left !important; align-items: stretch !important; }
        #listingTopCard .row > .col-md-auto,
        #listingTopCard .row > .col-md { flex: 0 0 100% !important; max-width: 100% !important; width: 100% !important; }

        /* The phone design at iPad width looked stretched: 12–17px text, five
           44px icons spread 150px apart, stars scattered across the card.
           Same layout, sized for a tablet. */
        #listingTopCard .listing-card { border-radius: 16px !important; margin: 12px 16px !important; padding: 18px !important; box-shadow: 0 4px 18px rgba(15,23,42,.06); }
        #listingTopCard .listing-logo,
        #listingTopCard .listing-logo img { border-radius: 14px !important; }
        #listingTopCard h1 { font-size: 26px !important; line-height: 1.3 !important; margin: 16px 0 6px !important; }
        #listingTopCard .listing-location { font-size: 15px !important; }
        #listingTopCard .jd-meta-row { font-size: 15px !important; gap: 10px; }
        #listingTopCard .jd-meta-row .badge, #listingTopCard .jd-meta-row a { font-size: 14px !important; }
        #listingTopCard .jd-hours-old, #listingTopCard [class*="open-now"], #listingTopCard .jd-open { font-size: 15px !important; }

        /* five actions as equal tiles filling the row */
        .mobile-quick-actions { display: grid !important; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-top: 18px !important; }
        .mobile-quick-actions .mqa-btn { width: auto !important; align-items: center; padding: 14px 6px 12px; background: #f6f8fb; border: 1px solid #e6ebf2; border-radius: 14px; }
        .mobile-quick-actions .mqa-icon { width: 52px !important; height: 52px !important; font-size: 24px !important; border-radius: 14px !important; }
        .mobile-quick-actions .mqa-label { font-size: 14px !important; margin-top: 8px !important; font-weight: 500; }
        .mqa-enquire-btn { height: auto !important; padding: 14px !important; font-size: 19px !important; border-radius: 14px !important; margin-top: 14px !important; }
        .mqa-enquire-btn small { font-size: 13px !important; }

        /* rating: title, five stars together, hint — one tidy row */
        #listingTopCard .jd-rate-box form { display: flex !important; align-items: center; flex-wrap: wrap; gap: 14px; margin-top: 16px; padding-top: 16px; border-top: 1px solid #eef1f5; }
        #listingTopCard .jd-rate-title { font-size: 17px !important; margin: 0 !important; }
        #listingTopCard .jd-rate-stars { display: flex !important; justify-content: flex-start !important; gap: 10px !important; width: auto !important; }
        #listingTopCard .jd-star { width: 46px !important; height: 46px !important; font-size: 22px !important; border-radius: 10px !important; }
        #listingTopCard .jd-rate-text { font-size: 13px !important; margin: 0 !important; }

        #listingTopCard .jd-meta-row *, #listingTopCard .jd-hours-old, #listingTopCard .jd-hours-old * { font-size: 15px !important; }
        #listingTopCard .jd-status-row, #listingTopCard .jd-status-row * { font-size: 15px !important; }
        #listingTopCard .jd-meta-row .badge, #listingTopCard .jd-meta-row [class*="chip"], #listingTopCard .jd-meta-row [class*="cat"] { padding: 6px 12px !important; }
        #listingTopCard .jd-meta-row, #listingTopCard .listing-location { margin-bottom: 8px !important; }
        #photosSection h2, #photosSection h3, #photosSection .section-title { font-size: 20px !important; }
        #photosSection img { width: 200px !important; height: 140px !important; object-fit: cover; }

        /* tabs + body text */
        #listingTabs a, #listingTabs button { font-size: 16px !important; padding: 14px 16px !important; }
        #overview_view p, #overview_view li { font-size: 16px !important; line-height: 1.7 !important; }
    }

    /* 992–1199px: the right column (contact, address, hours) was 25% — about
       190px of text, so the address and hours box wrapped word by word. */
    @media (min-width: 992px) and (max-width: 1199px) {
        .col-lg-9:has(+ #sidebarColumn), .row > .col-lg-9 { flex: 0 0 64% !important; max-width: 64% !important; }
        #sidebarColumn.col-lg-3 { flex: 0 0 36% !important; max-width: 36% !important; }
        #overview_view p, #overview_view li { font-size: 15px !important; line-height: 1.7 !important; }
    }

    /* a review opened from a shared #review-N link */
    .review-item.sb-review-flash { animation: sbReviewFlash 2.6s ease-out; border-radius: 10px; }
    @keyframes sbReviewFlash { 0%,35% { background: #fff4d6; box-shadow: 0 0 0 6px #fff4d6; } 100% { background: transparent; box-shadow: 0 0 0 6px transparent; } }

    .related-searches-mobile-toggle {
        display: none;
    }

    @media (max-width: 991px) {
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

    @media (max-width: 991px){
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
        /* Clears the fixed site header; the JS's own stickyTop constant
           assumes this same 95px (all.blade.php uses it too). At 50px the
           card sat jammed against the header. */
        top: 95px;
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
            left: 10px !important;
            right: 10px !important;
            width: auto !important;
            max-width: none !important;
            top: 80px;
        }
    }

    @media (max-width: 991px){
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

    @media (max-width: 991px){

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
        top: 95px;
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

        @media(max-width: 991px){
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

        .sb-photos-of, .sb-photo-chips{ display:none; }
        /* Photos slider: no grey scrollbar under it — round arrows on the sides
           instead (shown only when there is more to scroll that way). */
        #photosSection .photo-category-slider{ scrollbar-width:none; scroll-behavior:smooth; }
        #photosSection .photo-category-slider::-webkit-scrollbar{ display:none; }
        #photosSection #photos_view{ position:relative; }
        .sb-ph-arrow{
            position:absolute; top:58px; transform:translateY(-50%); z-index:3;
            width:38px; height:38px; border-radius:50%; border:0; cursor:pointer;
            background:#fff; color:#12203f; font-size:18px;
            box-shadow:0 4px 14px rgba(15,23,42,.22);
            display:flex; align-items:center; justify-content:center;
            transition:opacity .2s ease, transform .2s ease;
        }
        .sb-ph-arrow:hover{ transform:translateY(-50%) scale(1.08); }
        .sb-ph-arrow.prev{ left:-6px; }
        .sb-ph-arrow.next{ right:-6px; }
        .sb-ph-arrow[hidden]{ display:none; }
        #photosSection.is-grid .sb-ph-arrow{ display:none !important; }
        @media (max-width: 991px){
            /* Phone: JustDial-style — "Photos of X in Area, City", filter chips,
               and a two-column masonry grid — shown once the Photos tab (or the
               "+N Photos" badge) is tapped; until then the usual slider. */
            #photosSection.is-grid .sb-photos-of{ display:inline; }
            #photosSection.is-grid .media-title{ font-size:16px; font-weight:600; line-height:1.35; }
            #photosSection.is-grid .sb-photo-chips{ display:flex; gap:8px; margin:4px 0 12px; overflow-x:auto; }
            #photosSection.is-grid .sb-photo-chips button{
                flex:none; border:1px solid #d9dde6; background:#fff; color:#333; border-radius:8px;
                padding:6px 12px; font-size:13px; font-weight:500;
            }
            #photosSection.is-grid .sb-photo-chips button.on{ border-color:#1a73e8; color:#1a73e8; background:#eef5ff; }
            #photosSection.is-grid .photo-category-slider{
                display:block !important; column-count:2; column-gap:6px; overflow:visible !important; padding:0 !important;
            }
            #photosSection.is-grid .photo-card{
                display:inline-block; width:100% !important; min-width:0 !important; max-width:none !important;
                margin:0 0 6px; break-inside:avoid;
            }
            #photosSection.is-grid .photo-card.is-off{ display:none; }
            #photosSection.is-grid .photo-thumb{ height:auto !important; margin:0 !important; border-radius:6px !important; }
            #photosSection.is-grid .photo-thumb img{ height:auto !important; min-height:90px; }
            #photosSection.is-grid .photo-thumb video{ height:auto !important; aspect-ratio:3/4; }
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

        @media (max-width: 991px) {
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

        /* The photo is shown whole (contain) — cropping a square or portrait
           photo into this wide box kept only its top (sky) and cut the
           building off. A blurred copy of the same photo fills the sides. */
        .listing-logo{ position:relative; }
        .sb-hero-slide, .sb-hero-dots{ display:none; }
        .sb-hero-count{
            position:absolute; right:10px; bottom:10px; z-index:2;
            border:0; border-radius:6px; padding:5px 9px;
            background:rgba(17,24,39,.78); color:#fff;
            font-size:12px; font-weight:600; line-height:1; cursor:pointer;
            display:inline-flex; align-items:center; gap:3px;
        }
        .listing-logo::before{
            content:""; position:absolute; inset:-20px;
            background:var(--sb-logo-bg) center / cover no-repeat;
            filter:blur(18px) brightness(.9); transform:scale(1.1);
        }
        .listing-logo img{
            position:relative; z-index:1;
            width:100% !important;
            height:100% !important;
            object-fit:contain !important;
            object-position:center !important;
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

        @media (max-width: 991px){
            .listing-title{
                font-size:20px;
            }

            /* Phone: like JustDial — the photo fills the whole box (no blurred
           sides), a little taller, cropped around its middle. */
        .listing-logo{
            width:100%;
            height:auto !important;
            aspect-ratio:16 / 9;
            border-radius:12px;
        }
        .listing-logo::before{ display:none; }
        .listing-logo img{
            object-fit:cover !important;
            object-position:center !important;
        }
        /* Phone: the cover and the gallery photos slide by on their own. */
        .listing-logo .sb-hero-slide{
            display:block; position:absolute; inset:0; z-index:1;
            opacity:0; transition:opacity .6s ease;
        }
        .listing-logo > img{ transition:opacity .6s ease; }
        .listing-logo .is-hidden{ opacity:0 !important; pointer-events:none; }
        .listing-logo .is-shown{ opacity:1 !important; }
        .sb-hero-dots{
            display:flex; position:absolute; left:50%; bottom:12px; transform:translateX(-50%); z-index:2; gap:5px;
        }
        .sb-hero-dots span{ width:6px; height:6px; border-radius:50%; background:rgba(255,255,255,.55); transition:all .25s; }
        .sb-hero-dots span.on{ width:16px; border-radius:3px; background:#fff; }

        .listing-logo img{
            width:100%;
            height:100%;
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
        {{-- The whole photo is shown (no crop) over a blurred copy of itself that fills the box. --}}
        <div class="listing-logo" style="--sb-logo-bg:url('{{ loadImage('assets/images/listing/thumb/'.$listing->thumb) }}')">
            <img src="{{ loadImage('assets/images/listing/thumb/'.$listing->thumb) }}"
                class="img-fluid listing-photo-click"
                data-src="{{ loadImage('assets/images/listing/thumb/'.$listing->thumb) }}"
            alt="{{ $listing->name }} Logo" fetchpriority="high">
            @php
                // The rest of the gallery, slid through on phones after the cover (max 10 in all).
                $sbHeroSlides = collect($listing->photos ?? [])
                    ->filter(fn ($f) => $f && $f !== $listing->thumb)
                    ->map(fn ($f) => asset('assets/images/listing/photos/' . $f))
                    ->merge($listing->extraImages()->where(fn ($q) => $q->where('type', 'gallery')->orWhereNull('type')->orWhere('type', ''))->latest()->pluck('image')
                        ->map(fn ($f) => asset('assets/images/listing/extra/' . $f)))
                    ->unique()->take(9)->values();
            @endphp
            @foreach($sbHeroSlides as $sbSlide)
                <img src="{{ $sbSlide }}" data-src="{{ $sbSlide }}" class="sb-hero-slide" alt="{{ $listing->name }} photo" loading="lazy" decoding="async">
            @endforeach
            @if($sbHeroSlides->isNotEmpty())
                <div class="sb-hero-dots"><span class="on"></span>@foreach($sbHeroSlides as $sbSlide)<span></span>@endforeach</div>
            @endif
            @php
                $sbHeroPhotoCount = count($listing->photos ?? [])
                    + $listing->extraImages()->where(fn ($q) => $q->where('type', 'gallery')->orWhereNull('type')->orWhere('type', ''))->count()
                    + ((!empty($listing->thumb) && !in_array($listing->thumb, $listing->photos ?? [], true)) ? 1 : 0);
            @endphp
            @if($sbHeroPhotoCount > 1)
                {{-- JustDial-style count badge; takes you to the photos. --}}
                <button type="button" class="sb-hero-count" onclick="var t=document.getElementById('photosSection'); if(t){ t.scrollIntoView({behavior:'smooth', block:'start'}); }">
                    +{{ $sbHeroPhotoCount - 1 }} Photos <i class="las la-angle-right"></i>
                </button>
            @endif

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
    // Existing listing rating and count
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

    // Google reviews data
    $gReviews = $listing->gmb_reviews_json;

    if (is_string($gReviews)) {
        $gReviews = json_decode($gReviews, true);
    }

    $gReviews = is_array($gReviews) ? $gReviews : [];
    $gRating = (float) ($listing->gmb_rating ?? 0);
    $gCount = (int) ($listing->gmb_review_count ?? 0);

    // Combine existing and Google ratings
    $existingRatingSum = $rating * $ratingCount;
    $googleRatingSum = $gRating * $gCount;

    $combinedCount = $ratingCount + $gCount;

    if ($combinedCount > 0) {
        $rating = round(
            ($existingRatingSum + $googleRatingSum) / $combinedCount,
            1
        );
    } else {
        $rating = 0;
    }

  
    $ratingCount = $combinedCount;
@endphp

    
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
         only at max-width: 991px below; the desktop badge and the
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
        // Listing::shiftsByDay() reads every way hours were ever saved (one row
        // per day per shift, or ticked days + slots that apply to all of them).
        $jdShiftsByDay = [];
        foreach ($listing->shiftsByDay() as $jdDay => $jdShifts) {
            foreach ($jdShifts as [$jdOpenRaw, $jdCloseRaw]) {
                $jdShiftsByDay[$jdDay][] = ['open' => $jdOpenRaw, 'close' => $jdCloseRaw];
            }
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

    <div class="jd-status-row">
        <button type="button" class="jd-status-toggle" id="jdStatusToggle" aria-expanded="false" aria-controls="jdHoursPanel">
            <span class="jd-status-dot {{ $isOpenNow ? 'is-open' : 'is-closed' }}"></span>
            <span class="jd-status-text {{ $isOpenNow ? 'is-open' : 'is-closed' }}">
                {{ $isOpenNow ? 'Open Now' : 'Closed Now' }}
            </span>
            <span class="jd-status-detail">
                @php
                   
                    $jdOpen24Today = collect($jdShiftsByDay[$today] ?? [])->contains(function ($sh) use ($jdFormatTime) {
                        return strtoupper((string) $jdFormatTime($sh['open'])) === '12:00 AM'
                            && strtoupper((string) $jdFormatTime($sh['close'])) === '11:59 PM';
                    });
                @endphp
                @if($isOpenNow && $jdOpen24Today)
                    : 24 Hours
                @elseif($isOpenNow && $formattedCloseTime)
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

        @php
         
            $bridgeCategoryIds = config('exotel.restricted_category_ids', []);
            $bridgeLocationIds = config('exotel.restricted_location_ids', []);
            $bridgeCallEligible = config('exotel.enabled')
                && !empty($listing->mobile_numbers[0])
                && (empty($bridgeCategoryIds) || in_array((string) ($listing->category_id ?? optional($listing->category)->id), $bridgeCategoryIds, true))
                && (empty($bridgeLocationIds) || in_array((string) ($listing->location_id ?? optional($listing->location)->id), $bridgeLocationIds, true));
        @endphp

        <!-- CALL BUTTON -->
        @if($bridgeCallEligible)
    <a href="javascript:void(0)"
    class="listing-btn btn-call-main track-click bridge-call-btn"
    data-track="call_click"
    data-type="bridge_call"
    data-listing-id="{{ $listing->id }}"
    data-category-id="{{ $listing->category_id ?? optional($listing->category)->id ?? '' }}"
    data-location-id="{{ $listing->location_id ?? optional($listing->location)->id ?? '' }}">

        <img src="{{ asset('assets/download.svg') }}" class="call-icon" alt="Call">

        <span class="bridge-call-btn-text">Call Now</span>
    </a>
    @elseif(!empty($listing->mobile_numbers[0]))
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
    @php
       
        $categoryName = $listing->category?->name ?? 'services';
        $businessName = $listing->name ?? 'your business';
        $message = "Hi, I found you on Service Bazzar. I'm interested in your {$categoryName}. Please tell me more.";
        $whatsappUrl = 'https://wa.me/91' . ($listing->whatsapp_numbers[0] ?? '') . '?text=' . urlencode($message);
    @endphp
    <a href="{{ $whatsappUrl }}"
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
    <img src="{{ asset('assets/images/download.svg') }}" alt="Share listing">
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

    {{-- Edit button — shown to everyone; the click decides (owner edits, guest logs in, anyone else is told no). --}}
    @php
        $sbEditState = !auth()->check() ? 'guest' : (auth()->id() === $listing->user_id ? 'owner' : 'other');
    @endphp
    @if(true)
        <a href="{{ $sbEditState === 'owner' ? route('user.listing.profile', $listing->id) : '#' }}"
           class="listing-btn edit-listing-btn sb-edit-btn"
           data-edit-state="{{ $sbEditState }}"
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

                                @if($bridgeCallEligible)
                                    <a href="javascript:void(0)"
                                       class="mqa-btn mqa-call track-click bridge-call-btn"
                                       data-track="call_click"
                                       data-type="bridge_call"
                                       data-listing-id="{{ $listing->id }}"
                                       data-category-id="{{ $listing->category_id ?? optional($listing->category)->id ?? '' }}"
                                       data-location-id="{{ $listing->location_id ?? optional($listing->location)->id ?? '' }}">
                                        <span class="mqa-icon"><i class="las la-phone"></i></span>
                                        <span class="mqa-label bridge-call-btn-text">Call Now</span>
                                    </a>
                                @elseif(!empty($listing->mobile_numbers[0]))
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
                                    @php
                                        $categoryName = $listing->category?->name ?? 'services';
                                        $message = "Hi, I found you on Service Bazzar. I'm interested in your {$categoryName}. Please tell me more.";
                                        $whatsappUrl = 'https://wa.me/91' . ($listing->whatsapp_numbers[0] ?? '') . '?text=' . urlencode($message);
                                    @endphp
                                    <a href="{{ $whatsappUrl }}"
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

                                {{-- Edit button (mobile) — same rules as the desktop one. --}}
                                @php $sbEditState = !auth()->check() ? 'guest' : (auth()->id() === $listing->user_id ? 'owner' : 'other'); @endphp
                                @if(true)
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
                                    <a href="{{ $sbEditState === 'owner' ? route('user.listing.profile', $listing->id) : '#' }}"
                                       class="mqa-btn mqa-edit sb-edit-btn"
                                       data-edit-state="{{ $sbEditState }}"
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
           (max-width: 991px). CSS-only, reuses existing classes/markup.
           Every rule below is scoped inside this media query, so
           desktop/tablet (>768px) rendering is untouched.
        ============================================================ */
        @media (max-width: 991px) {

            
            body.mobile-yt-search-open .yt-search-form {
                margin: 0;
            }

          
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

          
            #listingTopCard .listing-action-buttons {
                display: none !important;
            }

          
            #listingTopCard .mobile-quick-actions {
                display: flex;
                justify-content: space-between;
                gap: 6px;
                width: 100%;
                margin-top: 16px;
                border-radius: 10px;
                
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
                background: #fff !important;
                color: #555;
                border: 1.5px solid #d9d9d9;
                box-sizing: border-box;
            }
            #listingTopCard .mqa-edit .mqa-edit-icon-img {
                width: 18px;
                height: 18px;
            }

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

            
            #listingTopCard .mqa-share {
                position: static;
            }

            #listingTopCard .mqa-share .share-hover-popup {
                left: auto;
                right: 0;
                transform: none;
            }

          
            #listingTopCard .mqa-share .share-hover-popup:before {
                left: auto;
                right: 40px;
                transform: none;
            }

            #listingTopCard .mqa-enquire-btn {
                display: block;
                width: 100%;
                margin-top: 12px;
               
                margin-bottom: 16px;
                background: #0d6efd;
                color: #fff;
                border: 0;
                border-radius: 10px;
              
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

        
            #listingTopCard .text-md-right {
                width: 100%;
                align-items: flex-start !important;
            }

            #listingTopCard .rate-box {
                min-width: 100%;
            }

         
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

           
            #listingTopCard .listing-location .dot-separator,
            #listingTopCard .listing-location .business-years {
                display: none;
            }

       
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

   
        .mobile-quick-actions,
        .mqa-enquire-btn,
        .mqa-hours-icon,
        .jd-meta-row,
        .jd-category-chip,
        .jd-status-row {
            display: none;
        }

       
        .jd-rating-line1,
        .jd-rating-line2 {
            display: contents;
        }

        @media (max-width: 991px) {
            #listingMediaRow .media-tabs {
                padding: 0 10px;
                gap: 4px;
            }

            #listingMediaRow .media-tabs .nav-link {
                font-size: 13px;
                padding: 10px 4px;
            }
        }

        @media (max-width: 991px) {
            
            #listingTabs.is-fixed {
                position: fixed;
                
                z-index: 900;
                background: #fff;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }

         
            #listingTabsPlaceholder {
                height: 0;
            }
        }
    </style>
    @endpush

    <script>
  
    document.addEventListener('DOMContentLoaded', function () {
      
        var tabs = document.getElementById('listingTabs');
        var placeholder = document.getElementById('listingTabsPlaceholder');
        var mediaBox = document.querySelector('#listingMediaRow .media-main-box');

        if (!tabs || !placeholder || !mediaBox) {
            return;
        }

        var mobileQuery = window.matchMedia('(max-width: 991px)');
     
        var topOffset = 50;
        function measureHeader() {
            var h = document.getElementById('stickyScrollHeader');
            if (h) {
                var b = h.getBoundingClientRect().bottom;
                if (b > 0) topOffset = Math.round(b);
            }
            return topOffset;
        }
        measureHeader();
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

            measureHeader();
            if (isFixed) tabs.style.top = topOffset + 'px';
            var tabsHeight = tabs.getBoundingClientRect().height;

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
                 
                    releaseFixed();
                } else {
                 
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
              
                rootMargin: '-' + (topOffset + tabsHeight) + 'px 0px -60% 0px',
                threshold: 0
            });

            sections.forEach(function (s) {
                sectionObserver.observe(s.el);
            });

         
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

            <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="position:absolute;top:10px;right:16px;border:0;background:none;font-size:26px;line-height:1;color:#666;">&times;</button>
            <div class="modal-title" style="font-size: 20px; font-weight: 600; margin-bottom: 16px;">{{ $popupRatingValue > 0 ? 'Update Review' : 'Add Review' }}</div>
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

            @if($bridgeCallEligible)
    <a href="javascript:void(0)"
    class="listing-btn btn-call-main track-click bridge-call-btn"
    data-track="call_click"
    data-type="bridge_call"
    data-listing-id="{{ $listing->id }}"
    data-category-id="{{ $listing->category_id ?? optional($listing->category)->id ?? '' }}"
    data-location-id="{{ $listing->location_id ?? optional($listing->location)->id ?? '' }}">
        <img src="{{ asset('assets/download.svg') }}" class="call-icon" alt="Call">

        <span class="bridge-call-btn-text">Call Now</span>
    </a>
    @elseif(!empty($listing->mobile_numbers[0]))
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
        @php
            $categoryName = $listing->category?->name ?? 'services';
            $message = "Hi, I found you on Service Bazzar. I'm interested in your {$categoryName}. Please tell me more.";
            $whatsappUrl = 'https://wa.me/91' . ($listing->whatsapp_numbers[0] ?? '') . '?text=' . urlencode($message);
        @endphp
        <a href="{{ $whatsappUrl }}"
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

        <img src="{{ asset('assets/images/download.svg') }}" alt="Share business card">
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
                                <a href="{{ url('/terms-and-conditions') }}">Terms & Conditions</a> & <a href="{{ url('/privacy-policy') }}">Privacy Policy</a>
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

                @php
                    // Certificates this business has bought and still holds.
                    $sbCertificates = \App\Models\ListingSalesPackage::where('listing_id', $listing->id)
                        ->whereNotNull('asset')->where('expires_at', '>', now())
                        ->with('package')->latest()->get();
                @endphp

                @if($sbCertificates->isNotEmpty())
                <div class="content-section mb-4" id="certificatesSection">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 class="media-title mb-0">Certificates</h4>
                    </div>
                    <div class="sbcert-public">
                        @foreach($sbCertificates as $cert)
                            <figure>
                                <a href="{{ $cert->assetUrl() }}" target="_blank" rel="noopener">
                                    <img src="{{ $cert->assetUrl() }}" alt="{{ $cert->package->name ?? 'Certificate' }} — {{ $listing->name }}" loading="lazy">
                                </a>
                                <figcaption>{{ $cert->package->name ?? 'Certificate' }} · valid till {{ $cert->expires_at->format('d M Y') }}</figcaption>
                            </figure>
                        @endforeach
                    </div>
                    <style>
                    .sbcert-public{display:flex;gap:14px;flex-wrap:wrap;}
                    .sbcert-public figure{margin:0;}
                    .sbcert-public img{height:115px;width:auto;max-width:100%;object-fit:contain;
                        border:1px solid #e6e9f2;border-radius:10px;background:#fff;display:block;cursor:zoom-in;}
                    .sbcert-public figcaption{margin-top:6px;font-size:11.5px;color:#6b7285;max-width:170px;line-height:1.35;}
                    </style>
                </div>
                @endif

                <div class="content-section mb-4" id="photosSection">
        <div class="d-flex justify-content-between align-items-center mb-2">
            @php $sbPhotosWhere = collect([$listing->area, $listing->city])->filter()->unique()->implode(', '); @endphp
            <h4 class="media-title mb-0">Photos<span class="sb-photos-of"> of {{ $listing->name }}{{ $sbPhotosWhere ? ' in ' . $sbPhotosWhere : '' }}</span></h4>
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

            // The cover photo belongs in the gallery too — it's usually the
            // shop front, and it was the one picture missing from here.
            $sbCoverInGallery = !empty($listing->thumb)
                && !in_array($listing->thumb, $listing->photos ?? [], true);
        @endphp
        @php
            $sbPhotoTotal = ($sbCoverInGallery ? 1 : 0) + count($listing->photos ?? []) + $sbGalleryExtra->count();
            $sbVideoTotal = count($listing->videos ?? []);
        @endphp
        @if($sbPhotoTotal + $sbVideoTotal > 0)
            {{-- JustDial-style filter chips over the photo grid (phone). --}}
            <div class="sb-photo-chips" role="tablist">
                <button type="button" class="on" data-kind="all">All ({{ $sbPhotoTotal + $sbVideoTotal }})</button>
                @if($sbVideoTotal)
                    <button type="button" data-kind="photo">Photos ({{ $sbPhotoTotal }})</button>
                    <button type="button" data-kind="video">Videos ({{ $sbVideoTotal }})</button>
                @endif
            </div>
        @endif
        <div id="photos_view">
            <div class="photo-category-slider">
                @if($sbCoverInGallery || !empty($listing->photos) || $sbGalleryExtra->isNotEmpty() || !empty($listing->videos))
                    @if($sbCoverInGallery)
                        <div class="photo-card">
                            <div class="photo-thumb">
                                <img src="{{ loadImage('assets/images/listing/thumb/'.$listing->thumb) }}"
                                    class="listing-photo-click"
                                    data-src="{{ loadImage('assets/images/listing/thumb/'.$listing->thumb) }}"
                                    alt="{{ $listing->name }} Photo"
                                    loading="lazy"
                                    data-track="gallery_click"
                                    data-listing-id="{{ $listing->id }}">
                            </div>
                        </div>
                    @endif
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
                    @foreach($listing->videos ?? [] as $video)
                        <div class="photo-card is-video">
                            <div class="photo-thumb" style="position:relative;cursor:pointer;"
                                 onclick="sbPlayListingVideo('{{ asset('assets/images/listing/videos/'.$video) }}')">
                                <video src="{{ asset('assets/images/listing/videos/'.$video) }}#t=0.1"
                                       muted preload="metadata"
                                       style="width:100%;height:100%;object-fit:cover;border-radius:8px;"></video>
                                <span style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#fff;font-size:28px;text-shadow:0 2px 8px rgba(0,0,0,.6);">
                                    <i class="las la-play-circle"></i>
                                </span>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p>No photos available.</p>
                @endif
            </div>
        </div>

        {{-- Video player overlay (Sales Genio uploaded videos) --}}
        <div id="sbVideoOverlay" style="position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:9999;display:none;align-items:center;justify-content:center;"
             onclick="if(event.target.id==='sbVideoOverlay')sbCloseListingVideo()">
            <button type="button" onclick="sbCloseListingVideo()" style="position:absolute;top:18px;right:22px;color:#fff;font-size:28px;background:none;border:none;cursor:pointer;">&times;</button>
            <video id="sbVideoPlayer" controls playsinline style="max-width:92vw;max-height:82vh;border-radius:8px;"></video>
        </div>
        <script>
            function sbPlayListingVideo(url){
                var ov=document.getElementById('sbVideoOverlay'), vp=document.getElementById('sbVideoPlayer');
                vp.src=url; ov.style.display='flex'; vp.play().catch(function(){});
            }
            function sbCloseListingVideo(){
                var ov=document.getElementById('sbVideoOverlay'), vp=document.getElementById('sbVideoPlayer');
                vp.pause(); vp.removeAttribute('src'); vp.load(); ov.style.display='none';
            }
        </script>

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
        // Specific schema.org LocalBusiness subtype per category (Dentist,
        // MovingCompany, Restaurant, ...), falling back to the generic
        // LocalBusiness — see App\Support\ListingSchema for the mapping and
        // why only true LocalBusiness subtypes are used.
        $listingSchema = [
            '@context' => 'https://schema.org',
            '@type'    => \App\Support\ListingSchema::type($categorySlug),
            'name'     => $listingName,
            'url'      => $seoUrl,
        ];

        if (!empty($listing->thumb)) {
            $listingSchema['image'] = $seoImage;
        }

        if ($fullAddress) {
            // Street from the separate parts; many listings only have the one
            // free-text address, so fall back to that, cut before the city
            // ("…, Jhotwara, Jaipur-302012, Rajasthan" -> "…, Jhotwara").
            $schemaStreet = trim(collect([$listing->plot_no, $listing->building_name, $listing->street_name, $listing->landmark])->filter()->implode(', '));
            if ($schemaStreet === '' && trim((string) $listing->address) !== '') {
                $schemaStreet = trim((string) $listing->address);
                $schemaCity = trim((string) ($listing->city ?: optional($listing->location)->name));
                $cut = $schemaCity !== '' ? stripos($schemaStreet, $schemaCity) : false;
                if ($cut !== false && $cut > 0) {
                    $schemaStreet = rtrim(substr($schemaStreet, 0, $cut), " ,-");
                }
            }
            $listingSchema['address'] = [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $schemaStreet !== '' ? $schemaStreet : null,
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

        // geo / openingHoursSpecification / sameAs — each only when the
        // listing actually carries the data (helper returns null / [] when it
        // doesn't), so we never emit a coordinate, an hour or a profile URL
        // the page can't back up.
        if ($geo = \App\Support\ListingSchema::geo($listing)) {
            $listingSchema['geo'] = $geo;
        }
        if ($openingHours = \App\Support\ListingSchema::openingHours($listing)) {
            $listingSchema['openingHoursSpecification'] = $openingHours;
        }
        if ($sameAs = \App\Support\ListingSchema::sameAs($listing)) {
            $listingSchema['sameAs'] = $sameAs;
        }

        $breadcrumbItems = [];

        if (!empty($locationText)) {
            // The city crumb opens the city's own page, not the homepage.
            $breadcrumbItems[] = ['name' => $locationText, 'item' => url(\Illuminate\Support\Str::slug($locationText))];
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

              
        <div class="listing-widget" id="reviewsSection">
            <h4 class="media-title  ddd mb-0">@lang('Review & Ratings')</h4>
    
@php
    
    $avgRating = 0;
    $totalRatings = 0;

    if (!empty($listing->review) && is_numeric($listing->review)) {
        $avgRating = (float) $listing->review;
    } elseif (!empty($listing->avg_rating) && is_numeric($listing->avg_rating)) {
        $avgRating = (float) $listing->avg_rating;
    } elseif (!empty($listing->rating) && is_numeric($listing->rating)) {
        $avgRating = (float) $listing->rating;
    }

    if (!empty($listing->total_review) && is_numeric($listing->total_review)) {
        $totalRatings = (int) $listing->total_review;
    } elseif (!empty($listing->review_count) && is_numeric($listing->review_count)) {
        $totalRatings = (int) $listing->review_count;
    } elseif (!empty($listing->ratings_count) && is_numeric($listing->ratings_count)) {
        $totalRatings = (int) $listing->ratings_count;
    } else {
        $totalRatings = $listing->ratings->count();
    }
    $gRating = (float) ($listing->gmb_rating ?? 0);
    $gCount = (int) ($listing->gmb_review_count ?? 0);

    // Combine Service Bazzar and Google ratings
    $existingRatingSum = $avgRating * $totalRatings;
    $googleRatingSum = $gRating * $gCount;

    $combinedCount = $totalRatings + $gCount;

    if ($combinedCount > 0) {
        $avgRating = round(
            ($existingRatingSum + $googleRatingSum) / $combinedCount,
            1
        );
    } else {
        $avgRating = 0;
    }

    $totalRatings = $combinedCount;
@endphp

<div class="rating-summary-box">
    <div class="rating-summary-score">
        {{ number_format($avgRating, 1) }}
    </div>

    <div class="rating-summary-content">
        <h3>{{ $totalRatings }} {{ $totalRatings === 1 ? 'Rating' : 'Ratings' }}</h3>

        <p>
            @if($totalRatings === 0)
                No ratings yet. Be the first to rate this business.
            @elseif($totalRatings === 1)
                Based on 1 rating from customers on Service Bazzar 
            @else
                Average of {{ $totalRatings }} ratings from Service Bazzar 
            @endif
        </p>
    </div>
</div>


      
@php
    $alreadyRated = auth()->check()
        ? $listing->ratings->firstWhere('user_id', auth()->id())
        : null;

    $userRating = $alreadyRated ? (int) $alreadyRated->ratings : 0;

    // Decode Google reviews safely.
    $gReviews = $listing->gmb_reviews_json ?? [];

    if (is_string($gReviews)) {
        $gReviews = json_decode($gReviews, true) ?: [];
    }

    if (is_array($gReviews) && isset($gReviews['reviews']) && is_array($gReviews['reviews'])) {
        $gReviews = $gReviews['reviews'];
    }

    $gReviews = is_array($gReviews) ? $gReviews : [];

    $gRating = (float) ($listing->gmb_rating ?? 0);
    $gCount = (int) ($listing->gmb_review_count ?? 0);

    // Combine both review sources.
    $combinedReviews = [];

    foreach ($listing->ratings as $rating) {
        $combinedReviews[] = [
            'type' => 'user',
            'data' => $rating,
            'date' => $rating->created_at ? $rating->created_at->timestamp : 0,
        ];
    }

    foreach ($gReviews as $googleReview) {
        if (!is_array($googleReview)) {
            continue;
        }

        $combinedReviews[] = [
            'type' => 'google',
            'data' => $googleReview,
            'date' => 0,
        ];
    }

    usort($combinedReviews, function ($a, $b) {
        return $b['date'] <=> $a['date'];
    });

    $totalReviewCount = count($combinedReviews);
@endphp

<div class="reviewer-area" id="reviewsContainer">

    <div class="user-reviews-header">
      

        <div class="review-filters">
            <button type="button" class="review-filter-btn active" data-sort="relevant">Relevant</button>
            <button type="button" class="review-filter-btn" data-sort="latest">Latest</button>
            <button type="button" class="review-filter-btn" data-sort="high">High to Low</button>
            <button type="button" class="review-filter-btn" data-sort="low">Low to High</button>
        </div>
    </div>

    @foreach($combinedReviews as $index => $item)
 
@php
    $isGoogleReview = $item['type'] === 'google';
    $review = $item['data'];

    if ($isGoogleReview) {
        $reviewRating = (int) ($review['rating'] ?? $review['stars'] ?? 0);

        $reviewAuthor = $review['author']
            ?? $review['author_name']
            ?? 'Google User';

        $reviewText = $review['text']
            ?? $review['text_original']
            ?? $review['review']
            ?? '';

        // Exact date ya timestamp available ho tabhi date dikhayein
        $reviewDate = $review['time']
            ?? $review['published_at']
            ?? $review['publish_date']
            ?? $review['date']
            ?? $review['created_at']
            ?? null;

        $reviewWhen = '';

        if ($reviewDate !== null && $reviewDate !== '') {
            try {
                if (is_numeric($reviewDate)) {
                    $timestamp = (int) $reviewDate;

                    // Milliseconds ko seconds mein convert karein
                    if ($timestamp > 9999999999) {
                        $timestamp = (int) ($timestamp / 1000);
                    }

                    $reviewWhen = \Carbon\Carbon::createFromTimestamp(
                        $timestamp,
                        config('app.timezone')
                    )->format('d M Y');
                } else {
                    $reviewWhen = \Carbon\Carbon::parse($reviewDate)
                        ->format('d M Y');
                }
            } catch (\Throwable $e) {
                $reviewWhen = '';
            }
        }
    } else {
        $reviewRating = (int) ($review->ratings ?? 0);

        $reviewAuthor = $review->user->fullname
            ?? $review->name
            ?? 'Guest';

        $reviewText = $review->review ?? '';

        $reviewWhen = $review->created_at
            ? $review->created_at->format('d M Y')
            : '';
    }
@endphp


        <div class="reviewer mb-3 review-item-load"
             style="{{ $index >= 5 ? 'display:none;' : '' }}">

            <div class="content w-100">

                <div class="review-item {{ $isGoogleReview ? 'google-review-item' : '' }}"
                     @if(!$isGoogleReview)
                         id="review-{{ $review->id }}"
                     @endif
                     data-rating="{{ $reviewRating }}"
                     data-date="{{ $item['date'] }}"
                     data-source="{{ $item['type'] }}">

                    <div class="review-header">
                        <div class="review-left">

                            @if($isGoogleReview)
                               <img
                                    src="{{ userAvatarUrl($review->user->image ?? null) }}"
                                    class="review-avatar"
                                    alt="{{ $reviewAuthor }}"
                                    loading="lazy">
                            @else
                                <img
                                    src="{{ userAvatarUrl($review->user->image ?? null) }}"
                                    class="review-avatar"
                                    alt="{{ $reviewAuthor }}"
                                    loading="lazy">
                            @endif

                            <span class="review-name">{{ $reviewAuthor }}</span>

                          
                        </div>

                        <div class="review-right">
                            <span class="review-date">{{ $reviewWhen }}</span>

                            @if(!$isGoogleReview)
                                @auth
                                    @if(auth()->id() == $review->user_id)
                                        <div class="review-menu-wrap">
                                            <button type="button"
                                                    class="review-menu-btn"
                                                    onclick="toggleReviewMenu({{ $review->id }})">
                                                <i class="las la-ellipsis-v"></i>
                                            </button>

                                            <div class="review-menu-dropdown" id="reviewMenu{{ $review->id }}">
                                                <button type="button" onclick="toggleReviewEdit({{ $review->id }})">
                                                    <i class="las la-edit"></i> Edit Review
                                                </button>

                                                <button type="button"
                                                        class="delete-review-btn"
                                                        data-id="{{ $review->id }}">
                                                    <i class="las la-trash"></i> Delete Review
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                @endauth
                            @endif
                        </div>
                    </div>

                    <div class="review-stars">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="{{ $i <= $reviewRating ? 'fas' : 'far' }} fa-star"></i>
                        @endfor
                    </div>

                    @if(trim((string) $reviewText) !== '')
                        <div class="review-text">"{{ $reviewText }}"</div>
                    @endif

                    @if(!$isGoogleReview)

                        @auth
                            @if(auth()->id() == $review->user_id)
                                <div id="review_edit_{{ $review->id }}"
                                     class="review-edit-box"
                                     style="display:none;">

                                    <form action="{{ route('review.update', $review->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')

                                        <div class="form-group mb-2">
                                            <label>Ratings</label>

                                            <input type="hidden"
                                                   name="ratings"
                                                   id="edit_rating_value_{{ $review->id }}"
                                                   value="{{ $review->ratings }}">

                                            <div class="edit-star-box" data-rating-id="{{ $review->id }}">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="las la-star edit-star {{ $i <= $review->ratings ? 'active' : '' }}"
                                                       data-value="{{ $i }}"></i>
                                                @endfor
                                            </div>
                                        </div>

                                        <div class="form-group mb-2">
                                            <label>Edit your review</label>
                                            <textarea class="form-control" name="review" rows="3" required>{{ $review->review }}</textarea>
                                        </div>

                                        <button type="submit" class="btn btn__base btn-sm">Update</button>

                                        <button type="button"
                                                class="btn btn-secondary btn-sm"
                                                onclick="toggleReviewEdit({{ $review->id }})">
                                            Cancel
                                        </button>
                                    </form>
                                </div>
                            @endif
                        @endauth

                        <div class="review-actions" data-rating="{{ $review->id }}">
                            <button
                                class="review-action-btn like-btn {{ $review->likes()->where('user_id', auth()->id())->exists() ? 'liked' : '' }}"
                                data-rating-id="{{ $review->id }}">
                                <i class="las la-thumbs-up"></i>
                                Helpful (<span class="like-count">{{ $review->likes()->count() }}</span>)
                            </button>

                            <button type="button"
                                    class="review-action-btn comment-toggle-btn"
                                    data-rating-id="{{ $review->id }}">
                                <i class="las la-comment"></i>
                                Comment (<span class="comment-count">{{ $review->comments()->count() }}</span>)
                            </button>

                            <button type="button"
                                    class="review-action-btn share-review-btn"
                                    data-url="{{ url()->current() }}#review-{{ $review->id }}"
                                    data-text="{{ $review->review }}">
                                <i class="las la-share"></i>
                                Share
                            </button>
                        </div>

                        <div class="comment-box-wrap"
                             id="commentBox{{ $review->id }}"
                             style="display:none;">

                            <div class="comment-input-row">
                                <img
                                    src="{{ userAvatarUrl(auth()->check() ? auth()->user()->image : null) }}"
                                    alt="User"
                                    loading="lazy"
                                    class="comment-user-img">

                                <div class="comment-input-flex">
                                    <input type="text"
                                           class="form-control comment-input"
                                           id="commentInput{{ $review->id }}"
                                           placeholder="Add a comment">

                                    <button type="button"
                                            class="comment-post-btn"
                                            data-rating-id="{{ $review->id }}">
                                        Post
                                    </button>
                                </div>
                            </div>

                            <div class="comments-list" id="commentsList{{ $review->id }}">
                                @foreach($review->comments->sortByDesc('id') as $comment)
                                    <div class="single-comment" id="comment_{{ $comment->id }}">
                                        <img
                                            src="{{ userAvatarUrl($comment->user->image ?? null) }}"
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

                    @endif

                </div>
            </div>
        </div>
    @endforeach

    @if($totalReviewCount > 5)
        <div class="text-center mt-4">
            <button type="button"
                    id="loadMoreReviewsBtn"
                    class="btn btn-outline-primary">
                View More
            </button>
        </div>
    @endif

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
      
        <h4 class="related-searches-title">Related Searches</h4>

       
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

    <div class="col-lg-3" id="sidebarColumn">
        <div class="sidebar-sticky-wrapper">

            <div class="contact-info-box">

                <div class="contact-block">
                    <h5>Contact</h5>

                @if($bridgeCallEligible)

        <a href="javascript:void(0)" class="contact-link d-block bridge-call-btn" data-listing-id="{{ $listing->id }}">
            <i class="las la-phone"></i>
            <span class="bridge-call-btn-text">Call Now</span>
        </a>

    @elseif(!empty($listing->mobile_numbers) && count(array_filter($listing->mobile_numbers)))

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

        
        $shiftsByDay = [];
        foreach ($listing->shiftsByDay() as $bDay => $bShifts) {
            foreach ($bShifts as [$bOpen, $bClose]) {
                $shiftsByDay[$bDay][] = ['open' => $bOpen, 'close' => $bClose];
            }
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


                <div class="enquiry-card mt-3" id="mainEnquiryForm">
                    <div class="enquiry-title">
                        Get the List of <span>{{ $listing->category->name ?? 'No Category' }}</span>
                    </div>

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
                                I Agree to <a href="{{ url('/terms-and-conditions') }}">Terms &amp; Conditions</a> &amp; <a href="{{ url('/privacy-policy') }}">Privacy Policy</a>
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
    <div class="related-search-card mt-3" id="relatedSearchTrigger">
        <div class="related-search-box">

            <div class="mb-2">People Also Search For</div>

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
        <img class="image-preview-content" id="previewImage" alt="{{ $listing->name }} photo">

        <!-- Right Arrow -->
        <span class="nav-arrow right" id="nextImage">&#10095;</span>

    </div>


            </div>
        </div>
    </div>

    {{-- STICKY FLOATING ENQUIRY FORM --}}
    <div class="sticky-floating-form" id="stickyFloatingForm">
        <div class="enquiry-card">
            <div class="enquiry-title">
                Get the List of <span>{{ $listing->category->name ?? 'No Category' }}</span>
            </div>

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
                        I Agree to <a href="{{ url('/terms-and-conditions') }}">Terms &amp; Conditions</a> &amp; <a href="{{ url('/privacy-policy') }}">Privacy Policy</a>
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
                return;
            }

           
            const box = sidebarColumn.querySelector(".contact-info-box") || sidebarColumn;
            const rect = box.getBoundingClientRect();

            stickyForm.style.width = Math.round(rect.width) + "px";
            stickyForm.style.left = rect.left + "px";
            stickyForm.style.right = "auto";
        }

        function toggleStickyForm() {
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

        <div class="popular-title">
            {{ $currentCategory }} in Nearby Areas
        </div>

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
    @media (min-width: 992px){

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
    // Phone: the Photos tab or the "+N Photos" badge opens the full grid view.
    document.addEventListener('click', function (e) {
        if (!window.matchMedia('(max-width: 991px)').matches) return;
        if (e.target.closest('.tab-scroll-btn[data-target="#photosSection"], .sb-hero-count')) {
            var sec = document.getElementById('photosSection');
            if (sec) sec.classList.add('is-grid');
        }
    }, true);
    </script>
    <script>
   
    document.addEventListener('DOMContentLoaded', function () {
        var view = document.getElementById('photos_view');
        var track = view && view.querySelector('.photo-category-slider');
        if (!track) return;
        function mk(cls, icon, dir) {
            var b = document.createElement('button');
            b.type = 'button'; b.className = 'sb-ph-arrow ' + cls; b.setAttribute('aria-label', cls === 'prev' ? 'Previous photos' : 'Next photos');
            b.innerHTML = '<i class="las ' + icon + '"></i>';
            b.addEventListener('click', function () { track.scrollBy({ left: dir * track.clientWidth * 0.8, behavior: 'smooth' }); });
            view.appendChild(b); return b;
        }
        var prev = mk('prev', 'la-angle-left', -1), next = mk('next', 'la-angle-right', 1);
        function sync() {
            var max = track.scrollWidth - track.clientWidth - 2;
            prev.hidden = track.scrollLeft <= 2;
            next.hidden = track.scrollLeft >= max;
        }
        track.addEventListener('scroll', sync, { passive: true });
        window.addEventListener('resize', sync);
        window.addEventListener('load', sync);
        sync();
    });
    </script>
    <script>
    
    document.addEventListener('click', function (e) {
        var chip = e.target.closest('#photosSection .sb-photo-chips button');
        if (!chip) return;
        var kind = chip.getAttribute('data-kind');
        chip.parentNode.querySelectorAll('button').forEach(function (b) { b.classList.toggle('on', b === chip); });
        document.querySelectorAll('#photosSection .photo-category-slider .photo-card').forEach(function (card) {
            var isVideo = card.classList.contains('is-video');
            card.classList.toggle('is-off', (kind === 'photo' && isVideo) || (kind === 'video' && !isVideo));
        });
    });
    </script>
    <script>
   
    document.addEventListener("DOMContentLoaded", function () {
        var box = document.querySelector('#listingTopCard .listing-logo');
        if (!box || !window.matchMedia('(max-width: 991px)').matches) return;
        var slides = [box.querySelector(':scope > img.listing-photo-click')].concat(Array.prototype.slice.call(box.querySelectorAll('.sb-hero-slide')));
        slides = slides.filter(Boolean);
        if (slides.length < 2) return;
        var dots = box.querySelectorAll('.sb-hero-dots span'), i = 0, timer;

        function show(n) {
            i = (n + slides.length) % slides.length;
            slides.forEach(function (el, k) {
                el.classList.toggle('is-shown', k === i);
                el.classList.toggle('is-hidden', k !== i);
            });
            dots.forEach(function (d, k) { d.classList.toggle('on', k === i); });
        }
        function start() { clearInterval(timer); timer = setInterval(function () { if (!document.hidden) show(i + 1); }, 3000); }

        
        slides.forEach(function (el, k) {
            if (k === 0) return;
            el.addEventListener('click', function () {
                var match = document.querySelector('#photosSection .listing-photo-click[data-src="' + el.dataset.src + '"]');
                (match || slides[0]).click();
            });
        });

        var x0 = null;
        box.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
        box.addEventListener('touchend', function (e) {
            if (x0 === null) return;
            var dx = e.changedTouches[0].clientX - x0; x0 = null;
            if (Math.abs(dx) > 40) { show(i + (dx < 0 ? 1 : -1)); start(); }
        }, { passive: true });

        show(0); start();
    });
    </script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {

        const images = document.querySelectorAll(".listing-photo-click");
        const modal = document.getElementById("imagePreviewModal");
        const preview = document.getElementById("previewImage");
        const counter = document.getElementById("imageCounter");

        let currentIndex = 0;

        images.forEach((img, index) => {
            img.addEventListener("click", () => {
                currentIndex = index;
                showImage();
                modal.classList.add("show");
            });
        });

        function showImage() {
            preview.src = images[currentIndex].dataset.src;

            counter.innerText = (currentIndex + 1) + " / " + images.length;
        }

        document.getElementById("nextImage").onclick = function () {
            currentIndex = (currentIndex + 1) % images.length;
            showImage();
        };

        document.getElementById("prevImage").onclick = function () {
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            showImage();
        };

        document.getElementById("closeImagePreview").onclick = function () {
            modal.classList.remove("show");
        };

        modal.addEventListener("click", function (e) {
            if (e.target === modal) {
                modal.classList.remove("show");
            }
        });
        var touchStartX = 0;
        modal.addEventListener("touchstart", function (e) {
            touchStartX = e.changedTouches[0].clientX;
        }, { passive: true });
        modal.addEventListener("touchend", function (e) {
            var diff = touchStartX - e.changedTouches[0].clientX;
            if (Math.abs(diff) < 40) return;
            if (diff > 0) {
                currentIndex = (currentIndex + 1) % images.length;
            } else {
                currentIndex = (currentIndex - 1 + images.length) % images.length;
            }
            showImage();
        }, { passive: true });

        document.addEventListener("keydown", function (e) {
            if (!modal.classList.contains("show")) return;
            if (e.key === "ArrowRight") {
                currentIndex = (currentIndex + 1) % images.length;
                showImage();
            } else if (e.key === "ArrowLeft") {
                currentIndex = (currentIndex - 1 + images.length) % images.length;
                showImage();
            } else if (e.key === "Home") {
                e.preventDefault();
                currentIndex = 0;
                showImage();
            } else if (e.key === "End") {
                e.preventDefault();
                currentIndex = images.length - 1;
                showImage();
            } else if (e.key === "Escape") {
                modal.classList.remove("show");
            }
        });

    });
    </script>

    <script>
    document.addEventListener("DOMContentLoaded", function () {

        var _waClickTime = null;
        var _waListingId = null;
        var _waNumber    = null;

        document.addEventListener("visibilitychange", function () {
            if (document.visibilityState === 'visible' && _waClickTime !== null) {
                var elapsed = (Date.now() - _waClickTime) / 1000;
                if (elapsed >= 8 && typeof gtag === 'function') {
                    gtag('event', 'whatsapp_lead', {
                        event_category: 'Lead',
                        event_label: 'WhatsApp Message Sent',
                        listing_id: _waListingId,
                        whatsapp_number: _waNumber
                    });
                }
                _waClickTime = null;
                _waListingId = null;
                _waNumber    = null;
            }
        });

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

                if (this.dataset.track === 'whatsapp_click') {
                    _waClickTime = Date.now();
                    _waListingId = this.dataset.listingId || '';
                    _waNumber    = this.dataset.number || '';
                }

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

        const savedRating = {{ (int) ($userRating ?? 0) }};
        const savedText = quickRatingText ? quickRatingText.innerText : '';
        let ratingSubmitted = false;
        function restoreStars() {
            if (ratingSubmitted) return;
            selectedRating = savedRating;
            if (quickRatingValue) quickRatingValue.value = savedRating || '';
            document.querySelectorAll('#quickRatingStars .star-btn').forEach(s => {
                s.classList.toggle('active', Number(s.dataset.value) <= savedRating);
            });
            if (quickRatingText) quickRatingText.innerText = savedText;
        }
        if (ratingModalEl) {
            let wasOpen = false;
            new MutationObserver(() => {
                const open = ratingModalEl.classList.contains('show');
                if (wasOpen && !open) restoreStars();
                wasOpen = open;
            }).observe(ratingModalEl, { attributes: true, attributeFilter: ['class'] });
        }
        if (!isLoggedIn) {
           
            const authPop = document.getElementById('sbAuthPopup');
            if (authPop) new MutationObserver(() => { if (!authPop.classList.contains('show')) restoreStars(); })
                .observe(authPop, { attributes: true, attributeFilter: ['class'] });
        }

        if (isLoggedIn) {
            document.getElementById('submitRatingBtn')?.addEventListener('click', function () {
                ratingSubmitted = true;

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
                    ratingSubmitted = false;
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

        document.querySelectorAll('.star-btn').forEach(function (star) {
            star.addEventListener('click', function () {
                openReviewBox(this.dataset.value);
            });
        });

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

    
        document.querySelectorAll(".bridge-call-btn").forEach(function(btn){
            btn.addEventListener("click", function(){
                if (btn.dataset.calling === "1") return; // guard double-click

                const listingId = btn.dataset.listingId;
                const label = btn.querySelector(".bridge-call-btn-text");
                const originalText = label ? label.textContent : "";

                btn.dataset.calling = "1";
                if (label) label.textContent = "Preparing...";

                fetch("{{ route('bridge.call.prepare') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({ listing_id: listingId })
                })
                .then(async function(res){
                    if (res.status === 401) {
                        openLoginPopup();
                        return null;
                    }
                    const data = await res.json();
                    if (!res.ok) throw data;
                    return data;
                })
                .then(function(data){
                    if (!data) return;
                    if (data.success && data.virtual_number) {
                    
                        window.location.href = "tel:" + data.virtual_number;
                    } else {
                        alert(data.message || "Could not start the call. Please try again.");
                    }
                })
                .catch(function(err){
                    alert((err && err.message) || "Could not start the call. Please try again.");
                })
                .finally(function(){
                    btn.dataset.calling = "0";
                    if (label) label.textContent = originalText;
                });
            });
        });

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
                                <img src="${data.image}" class="comment-user-img" alt="${data.name}" loading="lazy">
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

    // A guest who picks stars, writes a review and then has to log in: keep both
    // in this tab, and put them back in the form when the page reloads signed in.
    (function () {
        var KEY = 'sbPendingReview:{{ $listing->id }}';
        document.addEventListener('click', function (e) {
            if (!e.target.closest('#openReviewPopupBtn')) return;
            try {
                sessionStorage.setItem(KEY, JSON.stringify({
                    rating: parseInt(document.getElementById('guest_review_ratings').value || '0', 10),
                    review: (document.getElementById('guest_review_text') || {}).value || ''
                }));
            } catch (err) {}
        }, true);

        @auth
        var saved = null;
        try { saved = JSON.parse(sessionStorage.getItem(KEY) || 'null'); sessionStorage.removeItem(KEY); } catch (err) {}
        if (saved && saved.rating > 0) {
            document.addEventListener('DOMContentLoaded', function () {
                document.getElementById('guest_review_ratings').value = saved.rating;
                document.getElementById('reviewStatusText').innerText = 'You rated';
                document.querySelectorAll('.add-review-star').forEach(function (s) {
                    s.classList.toggle('active', parseInt(s.dataset.value) <= saved.rating);
                });
                var box = document.getElementById('reviewFormBox');
                var txt = document.getElementById('guest_review_text');
                if (txt && saved.review) txt.value = saved.review;
                if (box) {
                    box.classList.remove('d-none');
                    box.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        }
        @endauth
    })();

    // Cancel: close the form without saving and put the stars back as they were.
    document.addEventListener("click", function(e){
        if (!e.target.closest("#reviewCancelBtn")) return;
        const saved = {{ (int) ($userRating ?? 0) }};
        document.getElementById("guest_review_ratings").value = saved || "";
        document.getElementById("reviewStatusText").innerText = saved > 0 ? "You rated" : "Start your Review";
        document.querySelectorAll(".add-review-star").forEach(function(s){
            s.classList.toggle("active", parseInt(s.dataset.value) <= saved);
        });
        document.getElementById("reviewFormBox").classList.add("d-none");
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

        let visible = 5;

        const btn = document.getElementById("loadMoreReviewsBtn");

        if(!btn) return;

        btn.addEventListener("click", function(){

            let hiddenReviews = document.querySelectorAll(
                ".review-item-load[style*='display:none']"
            );

            for(let i = 0; i < 5 && i < hiddenReviews.length; i++){
                hiddenReviews[i].style.display = "block";
            }

            visible += 5;

            if(document.querySelectorAll(".review-item-load[style*='display:none']").length === 0){
                btn.style.display = "none";
            }

        });

    });
    /*
     * Shared review links (…/listing#review-27, from the reviewer's profile and
     * the review's own Share button) landed at the top of the page: only the
     * newest 5 reviews are shown, the rest sit display:none behind "Load more",
     * and the browser can't scroll to a hidden element. Reveal up to the linked
     * review, scroll it clear of the sticky header and tabs, and flash it.
     */
    (function () {
        function openReviewFromHash() {
            var m = /^#review-(\d+)$/.exec(location.hash || '');
            if (!m) return;
            var target = document.getElementById('review-' + m[1]);
            if (!target) return;

            var wrap = target.closest('.review-item-load');
            var all = Array.prototype.slice.call(document.querySelectorAll('.review-item-load'));
            var upTo = wrap ? all.indexOf(wrap) : -1;
            all.forEach(function (el, i) { if (i <= upTo && el.style.display === 'none') el.style.display = 'block'; });
            var btn = document.getElementById('loadMoreReviewsBtn');
            if (btn && !document.querySelector(".review-item-load[style*='display:none']")) btn.style.display = 'none';

            function offset() {
                var h = 0;
                var hdr = document.getElementById('stickyScrollHeader');
                if (hdr) h += hdr.getBoundingClientRect().height;
                var tabs = document.getElementById('listingTabs');
                if (tabs && /fixed|sticky/.test(getComputedStyle(tabs).position)) h += tabs.getBoundingClientRect().height;
                return h + 16;
            }
            function go() {
                var y = target.getBoundingClientRect().top + window.pageYOffset - offset();
                window.scrollTo({ top: Math.max(0, y), behavior: 'auto' });
            }
            go();
            // photos above it load later and push it down — settle once more
            window.addEventListener('load', function () { setTimeout(go, 50); }, { once: true });
            setTimeout(go, 600);

            target.classList.add('sb-review-flash');
            setTimeout(function () { target.classList.remove('sb-review-flash'); }, 2600);
        }
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', openReviewFromHash);
        else openReviewFromHash();
        window.addEventListener('hashchange', openReviewFromHash);
    })();
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

        /*
         * sessionStorage alone is per TAB, so every listing opened in a new tab
         * showed the popup again. A cookie with no expiry is shared by all tabs
         * and is dropped when the browser closes — once per visit, site-wide.
         */
        function popupAlreadyShown() {
            if (document.cookie.split('; ').indexOf(POPUP_KEY + '=1') !== -1) return true;
            try {
                return sessionStorage.getItem(POPUP_KEY) === '1';
            } catch (e) {
                return false;   // private mode / storage blocked
            }
        }

        function rememberPopupShown() {
            document.cookie = POPUP_KEY + '=1; path=/; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
            try {
                sessionStorage.setItem(POPUP_KEY, '1');
            } catch (e) {}
        }

        // Someone following a link to a specific review came to read it — don't
        // cover it with the enquiry popup on the way down.
        let popupShown = popupAlreadyShown() || /^#review-\d+$/.test(location.hash || '');

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

            // Lock scrolling on <html>. The old body{overflow:hidden;height:100vh}
            // collapsed the page to one screen, so opening any popup threw the
            // reader back to the very top.
            const root = document.documentElement;
            if (hasOpenPopup) {
                if (root.style.overflow !== 'hidden') root.style.overflow = 'hidden';
            } else if (root.style.overflow === 'hidden') {
                root.style.overflow = '';
                document.body.style.overflow = '';
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
    {{-- "You can't edit this listing" — for a signed-in visitor who isn't the owner. --}}
    <div id="sbEditDenied" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(15,23,42,.55);align-items:center;justify-content:center;padding:20px;">
        <div style="background:#fff;border-radius:14px;max-width:380px;width:100%;padding:26px 22px 20px;text-align:center;font-family:inherit;box-shadow:0 20px 50px rgba(15,23,42,.3);">
            <div style="width:52px;height:52px;border-radius:50%;background:#fdecec;color:#e53935;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:24px;"><i class="las la-lock"></i></div>
            <h4 style="margin:0 0 8px;font-size:18px;font-weight:600;color:#12203f;">You can't edit this listing</h4>
            <p style="margin:0 0 18px;font-size:14px;line-height:1.55;color:#5a6478;">Only the owner of this business can make changes to it. If this is your business, please contact Service Bazzar support.</p>
            <button type="button" id="sbEditDeniedOk" style="border:0;border-radius:8px;background:#1a73e8;color:#fff;padding:11px 30px;font-size:15px;font-weight:600;cursor:pointer;">OK</button>
        </div>
    </div>
    <script>
    (function () {
        var KEY = 'sbEditAfterLogin';
        var listingId = '{{ $listing->id }}';
        var box = document.getElementById('sbEditDenied');

        function denied() { box.style.display = 'flex'; }
        document.getElementById('sbEditDeniedOk').addEventListener('click', function () { box.style.display = 'none'; });
        box.addEventListener('click', function (e) { if (e.target === box) box.style.display = 'none'; });

        function act(btn, e) {
            var state = btn.getAttribute('data-edit-state');
            if (state === 'owner') return;            // a real link to the edit page
            e.preventDefault();
            if (state === 'other') { denied(); return; }
            // Guest: sign in first; the page reloads after login and picks up from here.
            try { sessionStorage.setItem(KEY, listingId); } catch (err) {}
            if (typeof openSbAuthPopup === 'function') { openSbAuthPopup(); }
            else { window.location.href = '{{ route('user.login') }}'; }
        }

        document.querySelectorAll('.sb-edit-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) { act(btn, e); });
        });

        // Just signed in after tapping Edit: carry on to the edit page, or say why not.
        var pending = null;
        try { pending = sessionStorage.getItem(KEY); } catch (err) {}
        if (pending === listingId) {
            try { sessionStorage.removeItem(KEY); } catch (err) {}
            var btn = document.querySelector('.sb-edit-btn');
            var state = btn ? btn.getAttribute('data-edit-state') : 'guest';
            if (state === 'owner') { window.location.href = btn.getAttribute('href'); }
            else if (state === 'other') { denied(); }
        }
    })();
    </script>
    @endsection