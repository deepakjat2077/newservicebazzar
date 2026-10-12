<!DOCTYPE html>
<html lang="en-IN">
<head>
	{{-- The LCP image's preload has to be the first thing the parser sees.
	     Pushed onto the css stack it landed after the analytics tag and the
	     three blocking stylesheets, and the measured cost was 1.36s of "load
	     delay" — the browser simply had not been told about the image yet
	     while it fetched everything else. --}}
	@stack('lcp')

	@include('includes.seo')
	{{-- ============================================================
	     CSS LOADING STRATEGY
	     Critical (render-blocking): bootstrap, index, style — needed for LCP layout.
	     Non-critical (async via preload/onload): everything else — icons, sliders,
	     toasts, galleries, colors — deferred so they never block first paint.
	     FontAwesome 7 alone is ~400KB; deferring it was the single biggest LCP win.
	     ============================================================ --}}

	{{-- Those three stylesheets are 254 KB and they blocked the first paint —
	     measured at 590-1090 ms, and half of the LCP's "render delay" on top.
	     What the first screen actually needs out of them is 31 KB, extracted
	     with penthouse against both the phone and the desktop viewport, so
	     neither paints unstyled. That goes inline (no request at all), and the
	     full files then load the same asynchronous way every other stylesheet
	     here already does — they arrive in time for anything below the fold. --}}
	<style>{!! file_get_contents(public_path($themeAsset.'css/critical.css')) !!}</style>

	<link rel="preload" href="{{ asset($themeAsset.'css/bootstrap.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet';this.removeAttribute('as')">
	<link rel="preload" href="{{ asset($themeAsset.'css/index.css') }}?v={{ filemtime(public_path($themeAsset.'css/index.css')) }}" as="style" onload="this.onload=null;this.rel='stylesheet';this.removeAttribute('as')">
	<link rel="preload" href="{{ asset($themeAsset.'css/style.css') }}?v={{ filemtime(public_path($themeAsset.'css/style.css')) }}" as="style" onload="this.onload=null;this.rel='stylesheet';this.removeAttribute('as')">
	<noscript>
		<link rel="stylesheet" href="{{ asset($themeAsset.'css/bootstrap.min.css') }}">
		<link rel="stylesheet" href="{{ asset($themeAsset.'css/index.css') }}">
		<link rel="stylesheet" href="{{ asset($themeAsset.'css/style.css') }}">
	</noscript>

	{{-- LCP preload is set dynamically per-page (see index.blade.php @push('css')) --}}

	{{-- NON-CRITICAL CSS — async load, never blocks paint --}}
	{{-- FontAwesome 7 (~400KB) --}}
	<link rel="preload" href="{{ asset('assets/fontawesome-free-7.2.0-web/css/all.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet';this.removeAttribute('as')">
	<noscript><link rel="stylesheet" href="{{ asset('assets/fontawesome-free-7.2.0-web/css/all.min.css') }}"></noscript>

	{{-- Line Awesome icons --}}
	<link rel="preload" href="{{ asset($themeAsset.'css/line-awesome.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet';this.removeAttribute('as')">
	<noscript><link rel="stylesheet" href="{{ asset($themeAsset.'css/line-awesome.min.css') }}"></noscript>

	{{-- Select2 dropdown --}}

	{{-- Slick slider --}}
	<link rel="preload" href="{{ asset($themeAsset.'css/slick.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet';this.removeAttribute('as')">
	<noscript><link rel="stylesheet" href="{{ asset($themeAsset.'css/slick.css') }}"></noscript>

	{{-- Toastr notifications --}}
	<link rel="preload" href="{{ asset($themeAsset.'css/toastr.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet';this.removeAttribute('as')">
	<noscript><link rel="stylesheet" href="{{ asset($themeAsset.'css/toastr.min.css') }}"></noscript>

	{{-- Lightcase gallery --}}

	{{-- Star ratings --}}

	{{-- Dynamic color CSS --}}
	<link rel="preload" href="{{ asset($themeAsset.'css/colorCss.php') }}?color={{ str_replace('#', '', $setting->first_color) }}" as="style" onload="this.onload=null;this.rel='stylesheet';this.removeAttribute('as')">
	<noscript><link rel="stylesheet" href="{{ asset($themeAsset.'css/colorCss.php') }}?color={{ str_replace('#', '', $setting->first_color) }}"></noscript>

	@stack('css')

	{{-- Google Fonts — non-blocking with font-display:swap --}}
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="preload" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet';this.removeAttribute('as')">
	<noscript><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"></noscript>

<style>
    /* JustDial-style two-row header on a listing page, phones only. */
    .jdh-top{display:none;}
    @media (max-width: 991px){
        body.jd-head #stickyScrollHeader .navbar{flex-wrap:wrap !important;row-gap:0 !important;}
        body.jd-head .jdh-top{display:flex;align-items:center;gap:4px;width:100%;order:-1;padding:4px 4px 2px;}
        body.jd-head .jdh-logo{display:flex;align-items:center;margin-right:auto;margin-left:2px;}
        body.jd-head .jdh-logo img{height:28px;width:auto;}
        body.jd-head .jdh-ico{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;border:0;background:transparent;
            color:#111;font-size:24px;padding:0;position:relative;border-radius:50%;}
        body.jd-head .jdh-ico i.fa-bell{font-size:19px;}
        /* same share arrow as the listing's Share button */
        body.jd-head .jdh-share-img{width:22px;height:22px;object-fit:contain;display:block;}
        body.jd-head .jdh-ico:active{background:#f1f3f7;}
        body.jd-head .jdh-ico .sbn-badge{top:2px;right:2px;}
        /* the second row is the search bar on its own, full width */
        body.jd-head #youtubeMobileSearch{width:100% !important;flex:1 1 100% !important;}
        body.jd-head .yt-search-back,
        body.jd-head .sb-bell-mobile,
        body.jd-head .mobile-back-btn{display:none !important;}
    }

    @media (max-width: 991px){
    body.mobile-yt-search-open .mobile-back-btn{
        display:none !important;
    }
}

    body.header-fixed .hide-on-scroll-menu,
#stickyScrollHeader.scrolled .hide-on-scroll-menu{
    display:none !important;
    visibility:hidden !important;
}
	body,
	button,
	input,
	select,
	textarea {
		font-family: "Poppins", sans-serif;
		-webkit-tap-highlight-color: transparent;
	}

	body{
    padding:0;
    -moz-osx-font-smoothing: grayscale;
    -webkit-font-smoothing: antialiased;
    letter-spacing:-0.2px;
}
body.header-fixed {
    padding-top: 70px;
}

				.ico {
			color: #da1010;
		}
		.ll {
			padding: 15px 0px 0px 0px;
		}
		#stickyScrollHeader,
		#stickyScrollHeader * {
			transition: none !important;
			animation: none !important;
		}
		#stickyScrollHeader .navbar-toggler {
			transition: none !important;
		}
		.header-area .bottom-header .navbar ul li a{
			color:black;
		}
		
		.header-area .bottom-header .navbar .navbar-toggler {
			color: #121212;
			line-height: 1px;
			transition: all 0.3s ease;
		}
		
		
	#stickyScrollHeader{
    position: relative;
    width: 100%;
    z-index: 999;
    background:#fff;
}

#stickyScrollHeader.scrolled{
    position: fixed;
    top:0;
    left:0;
    width:100%;
    z-index:999999;
    background:#fff;
}

.btn__base {
    background-color: #5964ff00;
    border: 1px solid #0e77cd;
}

.btn__base:hover {
    background: #0e77cd;
}

	
		@keyframes smoothDown{
			from{ transform: translateY(-100%); }
			to{ transform: translateY(0); }
		}
		.header-search-form{
			display: none;
		}
		
	
		.header-search-form .search-box{
			position: relative;
			flex: 1;
		}
		.header-search-form .search-box input{
			width:250px;
			height: 42px;
			border: 1px solid #ddd;
			border-radius: 8px;
			padding: 0 14px;
			outline: none;
			background: #fff;
		}
		.header-search-form .btn{
			height: 42px;
			white-space: nowrap;
		}

		@media (max-width: 991px){
			.header-search-form .search-box input{
				border: 1px solid #ddd;
				border-radius: 8px;
				outline: none;
				background: #fff;
			}
			.header-search-form{
				display: none;
				flex: 1;
				margin: 0 20px;
			}
			
			@media (max-width: 991px){
				body.header-fixed{
    padding-top:50px;
}
				.bottom-header .navbar{
					flex-wrap: wrap;
				}
				.header-search-form{
					display: none;
					width: 100%;
					flex: 0 0 100%;
					order: 3;
					margin: 10px 0 0;
				}
			
				
		
			
			}
		}

		#locationInput,
		#homeLocationInput,
		#jdMobileLocationInput {
			text-transform: capitalize !important;
		}

		@media (max-width: 991px){
			#locationInput {
				display: none !important;
			}
		}

        @media (max-width: 991px){
			.search-area {
				display: none;
			}
		}

		.logo-hight{
			height: 40px;
		}

		.profile-icon{
			width: 32px;
			height: 32px;
			border-radius: 50%;
			object-fit: cover;
		}

		.banner-section {
			position: relative;
			z-index: 20;
			overflow: visible !important;
			transition: padding-bottom 0.2s ease;
		}
		.banner-section.dropdown-open {
			padding-bottom: 220px;
		}
		.search-area,
		.search-area form,
		.search-area .row,
		.search-area .col-md-4,
		.search-area .col-md-6,
		.search-area .dropdown {
			position: relative;
			overflow: visible !important;
		}

		#locationSuggestions,
		#categorySuggestions {
			position: absolute;
			top: calc(100% + 2px);
			left: 0;
			width: 100%;
			display: none;
			z-index: 9999;
			background: #fff;
			border: 1px solid #ddd;
			border-radius: 0 0 8px 8px;
			max-height: 220px;
			overflow-y: auto;
			box-shadow: 0 10px 25px rgba(0,0,0,0.15);
			padding: 0;
			margin: 0;
			scrollbar-width: none;
			-ms-overflow-style: none;
		}

		#locationSuggestions::-webkit-scrollbar,
		#categorySuggestions::-webkit-scrollbar {
			display: none;
			width: 0;
			height: 0;
		}

		#locationSuggestions.show,
		#categorySuggestions.show {
			display: block;
		}

		#locationSuggestions li,
		#categorySuggestions li {
			list-style: none;
		}

		#locationSuggestions .dropdown-item,
		#categorySuggestions .dropdown-item {
			display: block;
			padding: 10px 14px;
		}

		/* MOBILE BOTTOM ACTION */
		@media (max-width: 991px){
			.mobile-bottom-action{
				position: fixed;
				left: 0;
				bottom: 0;
				width: 100%;
				background: #fff;
				box-shadow: 0 -2px 12px rgba(0,0,0,0.08);
				display: flex;
				align-items: center;
				justify-content: space-between;
				gap: 10px;
				padding: 10px 12px;
				z-index: 9999;
				transition: transform 0.3s ease, opacity 0.3s ease;
			}
			.mobile-bottom-action.hide-on-scroll{
				transform: translateY(100%);
				opacity: 0;
				pointer-events: none;
			}
			.mobile-action-item{
				flex: 1;
				text-align: center;
				text-decoration: none;
				color: #333;
				font-size: 12px;
				display: flex;
				flex-direction: column;
				align-items: center;
				justify-content: center;
				line-height: 1.2;
			}
			.mobile-action-item i{
				font-size: 18px;
				margin-bottom: 4px;
			}
			.mobile-action-item.active{
				color: #0072ed;
			}
			.mobile-action-btn{
				text-decoration: none;
				/* #007bff put white text at 3.98:1 against it, just under the 4.5:1
				   an accessibility check wants. Seven percent darker clears it and
				   is not a difference the eye picks up. */
				background: #0072ed;
				color: #fff;
				padding: 10px 14px;
				border-radius: 8px;
				font-size: 13px;
				font-weight: 600;
				white-space: nowrap;
			}
		}
		@media (min-width: 992px){
			.mobile-bottom-action{
				display: none !important;
			}
		}

		/* JUSTDIAL STYLE MOBILE LOCATION STRIP */
		.jd-mobile-location-strip{
			display:none;
		}
        @media (max-width: 991px){
	/* parent ko column banao */
	#stickyScrollHeader .container-fuldie.custom-right{
		display:flex;
        background-color: #fff;
		flex-direction:column;
	}

	/* Tonk strip ko upar lao */
	#stickyScrollHeader .jd-mobile-location-strip{
		order:1;
	}

	/* search form wala nav neeche rahe */
	#stickyScrollHeader .navbar{
		order:2;
	}

	/* scroll hone par strip dikhni chahiye */

	/* scroll hone par search form bhi Tonk ke niche aaye */
	
	
}

@media (max-width: 991px){

 body.mobile-yt-search-open .jd-mobile-location-strip{
    display:none !important;
}

body.mobile-yt-search-open.show-yt-location .jd-mobile-location-strip{
    display:block !important;
    visibility:visible !important;
    order:0 !important;
    padding:6px 10px 4px !important;
    background:#fff !important;
}

/* The strip is moved (script below) into the header row itself, right after
   the logo row, so it reads logo → city → search instead of sitting above the
   logo. Same order value as the logo row; DOM order puts it just after. */
#stickyScrollHeader .navbar > .jd-mobile-location-strip{
    order:-1 !important;
    flex:0 0 100% !important;
    width:100% !important;
    padding:0 10px 4px !important;
}





}#ytMobileCategorySuggestions:not(.show){
    display:none !important;
}


		@media (max-width: 991px){
			.jd-mobile-location-strip{
				display:none;
				width:100%;
				padding:8px 10px 0;
				background:#fff;
			}

			.jd-mobile-location-strip.show{
    display:block !important;
}

			.jd-mobile-city-btn{
				width:100%;
				height:40px;
				border:none;
				background:#fff;
				display:flex;
				align-items:center;
				justify-content: center;
				gap:6px;
				padding:0 2px;
				font-size:15px;
				font-weight:500;
				color:#111;
				box-shadow:none;
			}

			.jd-mobile-city-btn i{
				font-size:18px;
				color:#111;
			}

			.jd-mobile-location-box{
				display:none;
				position:relative;
				width:100%;
			}

			.jd-mobile-location-box.show{
				display:block;
			}

			.jd-mobile-location-box input{
    width:100%;
    height:35px;
    border:1px solid #ddd;
    border-radius:8px;
    padding:0 14px;
    outline:none;
    background:#fff;
    font-size:16px !important;
}
#jdMobileLocationInput{
    font-size:16px !important;
}

			.jd-mobile-location-suggestions{
				  position:fixed !important;
       top:95px !important;
        left:0 !important;
        right:0 !important;
        width:100% !important;
      height:calc(100vh - 50px) !important;
        max-height:none !important;
        background:#fff !important;
        z-index:99999999 !important;
        overflow-y:auto !important;
        box-shadow:none !important;
        border-radius:0 !important;
        margin:0 !important;
        padding-bottom:90px !important;
			}

			.jd-mobile-location-suggestions.show{
				display:block;
			}

			.jd-mobile-location-suggestions .dropdown-item{
				display:block;
				padding:12px 14px;
				font-size:12px;
				color:#222;
                font-weight: 500;
				border-bottom:1px solid #f1f1f1;
				text-decoration:none;
				
			}
		}

        .skip-link{
    position: fixed;
    left: -9999px;
    top: auto;
    width: 1px;
    height: 1px;
    overflow: hidden;
    z-index: -999;
}

.skip-link:focus{
    left: 20px;
    top: 20px;
    width: auto;
    height: auto;
    overflow: visible;
    background: #000;
    color: #fff !important;
    padding: 12px 18px;
    border-radius: 6px;
    text-decoration: none;
    z-index: 999999999;
}
.jd-mobile-city-btn.detecting{
    display: none !important;
}
.mobile-back-btn{
    display:none;
}
#ytMobileSearchInput{
    padding-right:45px !important;
    white-space:nowrap !important;
    overflow:hidden !important;
    text-overflow:ellipsis !important;
}
@media (max-width: 991px){

    .navbar{
        display:flex;
        align-items:center;
        justify-content:flex-start !important;
        gap:0 !important;
    }

    .mobile-back-btn{
    display:none;
    align-items:center;
    justify-content:center;
    width:28px;
    height:28px;
    border:0;
    background:transparent;
    padding:0;
    margin:0 !important;
    font-size:26px;
    color:#111;
    flex-shrink:0;
}
body:not(.header-fixed) .mobile-back-btn{
    display:flex;
}

    .navbar-brand{
        margin-left:10px;
        margin-right:auto !important;
        padding:0 !important;
        display:flex;
        align-items:center;
    }

    .logo-hight{
        height:30px;
        margin-right: 10px;
    }
}
.header-category-clear-btn{
    position:absolute;
    right:14px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    color:#5c79b5;
    z-index:999;
    display:none;
    font-size:15px;
}
@media (min-width: 1200px) {

    .head-right{
        margin-left: 40px;
        margin-right: 55px;
    }

    .category-area .custom-right{
        margin-left: 40px;
        margin-right: 55px;
    }

}

@media (min-width: 1400px) {

    .head-right{
        margin-left: 40px;
        margin-right: 55px;
    }

    .category-area .custom-right{
        margin-left: 40px;
        margin-right: 55px;
    }

}
#scrollSearchHolder{
    display:none;
    flex:1;
    margin:0 20px;
}

body:not(.home-page) .hide-on-scroll-menu{
    display:none !important;
}


#stickyScrollHeader.scrolled .hide-on-scroll-menu,
body.header-fixed .hide-on-scroll-menu{
    display: none !important;
}

.youtube-mobile-search{
    display:none;
}

@media (max-width: 991px){

    .mobile-header-search-btn{
        display:flex !important;
        align-items:center;
        justify-content:center;
        width:34px;
        height:34px;
        border:0;
        background:transparent;
        font-size:20px;
        color:#111;
        margin-left:auto;
    }

    body.mobile-yt-search-open .navbar-brand,
    body.mobile-yt-search-open .navbar-toggler,
    body.mobile-yt-search-open #mobileHeaderSearchBtn,
    body.mobile-yt-search-open #scrollSearchHolder{
        display:none !important;
    }

     

    .yt-search-back{
        width:30px;
        height:34px;
        border:0;
        background:transparent;
        font-size:24px;
        color:#111;
        display:flex;
        align-items:center;
        justify-content:center;
        padding:0;
    }

   .yt-search-input-box{
  
 
    background:#f1f1f1;
    border-radius:18px;
    display:flex;
     justify-content:center;
    align-items:center;
    border:1px solid #e0e0e0;
    overflow:hidden;
}

.left-search{
    width:38px;
    border:0;
    background:transparent;
    color:#111;
    font-size:15px;
    flex-shrink:0;
}

 .yt-search-input-box input{
    flex:1 1 0% !important;
    min-width:0 !important;
    width:auto !important;
    max-width:100% !important;
    border:0;
    outline:0;
    background:transparent;
    height:100%;
   font-size:16px !important;
    padding:0 4px;
    text-align:left;
}
body.mobile-yt-search-open #ytMobileSearchInput{
    font-size:16px !important;
}
.inside-mic{
    width:38px;
    height:100%;
    border:0;
    background:transparent;
    color:#111;
    font-size:15px;
    border-radius:0;
    flex-shrink:0;
}

    .yt-search-submit{
        width:34px;
        height:100%;
        border:0;
        background:transparent;
        color:#111;
        font-size:15px;
    }

    .yt-mic-btn{
        width:32px;
        height:32px;
        border:0;
        border-radius:50%;
        background:#f1f1f1;
        color:#111;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:15px;
        padding:0;
    }
}

.voice-modal{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.45);
    z-index:99999999;
    align-items:center;
    justify-content:center;
}

.voice-modal.show{
    display:flex !important;
}

.voice-box{
    background:#fff;
    width:280px;
    padding:25px 18px;
    border-radius:14px;
    text-align:center;
    position:relative;
}

.voice-close{
    position:absolute;
    right:10px;
    top:8px;
    border:0;
    background:transparent;
    font-size:24px;
}

.jd-location-title{
    padding:10px 14px 6px;
    font-size:11px;
    color:#6b7280;
    font-weight:600;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.jd-clear-recent{
    color:#0076d7;
    font-weight:500;
    cursor:pointer;
}

.jd-recent-item{
    position:relative;
    padding-right:35px !important;
}

.jd-remove-recent{
    position:absolute;
    right:14px;
    color:#999;
    font-size:16px;
}


@media (max-width: 991px){

    body.mobile-yt-search-open .navbar-brand,
    body.mobile-yt-search-open .navbar-toggler,
    body.mobile-yt-search-open #mobileHeaderSearchBtn,
    body.mobile-yt-search-open #scrollSearchHolder{
        display:none ;
    }

    body.mobile-yt-search-open #youtubeMobileSearch{
        display:flex ;
        width:100% ;
    }
}

@media (max-width: 991px){

    body.mobile-yt-search-open #scrollSearchHolder,
    body.mobile-yt-search-open .header-search-form{
        display:none;
        visibility:hidden;
        width:0;
        max-width:0;
        overflow:hidden;
    }

    body.mobile-yt-search-open #youtubeMobileSearch{
        display:flex;
        flex:1;
    }
}
/* Desktop par hide */
.mobile-header-search-btn{
    display:none;
}

/* Sirf mobile par show */
@media (max-width: 991px){
    .mobile-header-search-btn{
        display:flex !important;
        align-items:center;
        justify-content:center;
    }
}


@media (max-width: 991px){

    body.mobile-yt-search-open .youtube-mobile-search{
        width:100% !important;
        max-width:100% !important;
        padding:6px 8px 8px !important;
        box-sizing:border-box !important;
        overflow:hidden !important;
    }

    body.mobile-yt-search-open .yt-search-form{
        flex:1 1 auto !important;
        min-width:0 !important;
        width:100% !important;
        max-width:100% !important;
        overflow:hidden !important;
    }

    body.mobile-yt-search-open .yt-search-input-box{
        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;
        box-sizing:border-box !important;
        overflow:hidden !important;
        display:flex !important;
        align-items:center !important;
    }

  body.mobile-yt-search-open #ytMobileSearchInput{
    flex:1 1 auto !important;
    min-width:0 !important;
    max-width:100% !important;
    white-space:nowrap !important;
    overflow:hidden !important;
    text-overflow:ellipsis !important;
    padding-right:45px !important;
    text-align:left !important;
}

    body.mobile-yt-search-open .yt-search-back{
        flex:0 0 28px !important;
        width:28px !important;
        min-width:28px !important;
    }

    body.mobile-yt-search-open .yt-search-submit.left-search{
        flex:0 0 34px !important;
        width:34px !important;
        min-width:34px !important;
    }

    body.mobile-yt-search-open .yt-mic-btn.inside-mic{
        flex:0 0 34px !important;
        width:34px !important;
        min-width:34px !important;
    }
}

#voiceModal.show{
    display:block !important;
}

#voiceModal{
    position:fixed !important;
    inset:0 !important;
    width:100vw !important;
    height:100vh !important;
    background:rgba(0,0,0,.45) !important;
    z-index:2147483647 !important;
}

#voiceModal .voice-box{
    position:fixed !important;
    top:50% !important;
    left:50% !important;
    transform:translate(-50%, -50%) !important;
    width:550px !important;
    max-width:90vw !important;
    min-height:300px !important;
    height:auto !important;
    background:#fff !important;
    border-radius:14px !important;
    padding:28px !important;
    text-align:center !important;
    overflow:visible !important;
    box-sizing:border-box !important;
}
#voiceModal .voice-close{
    position:absolute !important;
    right:18px !important;
    top:10px !important;
}

.yt-category-item{
    display:flex;
    align-items:center;
    text-decoration:none;
}

.yt-category-icon{
    width:18px;
    height:18px;
    flex-shrink:0;
    object-fit:contain;
}
#ytMobileCategorySuggestions .yt-category-item{
    display:flex !important;
    align-items:center !important;
    width:100%;
    padding:12px 14px;
    box-sizing:border-box;
}

#ytMobileCategorySuggestions .yt-category-icon{
    width:22px;
    height:22px;
    flex:0 0 22px;
    object-fit:contain;
}

#ytMobileCategorySuggestions .yt-category-item span{
    flex:1;
    line-height:20px;
}
/* ===== YT MOBILE SUGGESTIONS - LISTING DESIGN ===== */
#ytMobileCategorySuggestions {
    display: none;
    position: fixed;
    left: 0;
    right: 0;
    top: 95px;
    background: #fff;
    z-index: 9999999;
    list-style: none;
    margin: 0;
    padding: 0;
    max-height: 100vh;
    overflow-y: auto;
    box-shadow: 0 8px 18px rgba(0,0,0,.12);
}

#ytMobileCategorySuggestions.show {
    display: block !important;
}

/* ===== LISTING ITEM ===== */
#ytMobileCategorySuggestions .yt-listing-item {
    display: block !important;
    padding: 12px 16px !important;
    border-bottom: 1px solid #f0f0f0 !important;
    text-decoration: none !important;
    background: #fff !important;
    transition: background 0.15s ease;
}

#ytMobileCategorySuggestions .yt-listing-item:active {
    background: #f5f5f5 !important;
}

/* ===== TITLE ===== */
#ytMobileCategorySuggestions .yt-listing-title {
    display: block !important;
    padding: 0 !important;
    margin: 0 0 4px 0 !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    color: #111 !important;
    line-height: 1.3 !important;
    letter-spacing: -0.2px;
}

/* ===== META ROW ===== */
#ytMobileCategorySuggestions .yt-listing-meta {
    display: flex !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 6px !important;
    padding: 0 !important;
    margin: 0 !important;
    font-size: 12px !important;
    line-height: 1.4 !important;
}

#ytMobileCategorySuggestions .yt-listing-meta span {
    display: inline-flex !important;
    padding: 0 !important;
    margin: 0 !important;
    border: 0 !important;
    width: auto !important;
}

/* ===== RATING BADGE ===== */
#ytMobileCategorySuggestions .yt-rating {
    background: #0a9f34 !important;
    color: #fff !important;
    padding: 1px 6px !important;
    border-radius: 3px !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    line-height: 16px !important;
}

/* ===== REVIEWS, DOT, AREA ===== */
#ytMobileCategorySuggestions .yt-review,
#ytMobileCategorySuggestions .yt-dot,
#ytMobileCategorySuggestions .yt-area {
    color: #777 !important;
    font-size: 12px !important;
}

/* ===== CATEGORY TAG ===== */
#ytMobileCategorySuggestions .yt-category-tag {
    background: #f0f0f0 !important;
    color: #555 !important;
    padding: 1px 8px !important;
    border-radius: 12px !important;
    font-size: 10px !important;
    font-weight: 500 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.3px !important;
}

/* ===== CATEGORY ITEM (non-listing) ===== */
#ytMobileCategorySuggestions .yt-category-item {
    display: flex !important;
    align-items: center !important;
    padding: 12px 16px !important;
    border-bottom: 1px solid #f0f0f0 !important;
    text-decoration: none !important;
    background: #fff !important;
}

#ytMobileCategorySuggestions .yt-category-item:active {
    background: #f5f5f5 !important;
}

#ytMobileCategorySuggestions .yt-category-icon {
    width: 22px !important;
    height: 22px !important;
    flex: 0 0 22px !important;
    object-fit: contain !important;
}

#ytMobileCategorySuggestions .yt-category-item span {
    flex: 1 !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    color: #111 !important;
    line-height: 1.3 !important;
}



/* ===== RECENT / TRENDING HEADER ===== */
#ytMobileCategorySuggestions .yt-section-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    padding: 10px 16px 6px !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    color: #6b7280 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    background: #fff !important;
    border-bottom: 1px solid #eee !important;
}

#ytMobileCategorySuggestions .yt-section-header .yt-clear-btn {
    color: #0076d7 !important;
    font-weight: 500 !important;
    cursor: pointer !important;
    text-transform: none !important;
    font-size: 11px !important;
}

/* ===== RECENT ITEM ===== */
#ytMobileCategorySuggestions .yt-recent-item {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    padding: 10px 16px !important;
    border-bottom: 1px solid #f5f5f5 !important;
    text-decoration: none !important;
    color: #222 !important;
    font-size: 13px !important;
    background: #fff !important;
}

#ytMobileCategorySuggestions .yt-recent-item i {
    color: #999 !important;
    font-size: 14px !important;
}

/* ===== SCROLLBAR ===== */
#ytMobileCategorySuggestions::-webkit-scrollbar {
    width: 4px;
}

#ytMobileCategorySuggestions::-webkit-scrollbar-thumb {
    background: #d0d0d0;
    border-radius: 10px;
}

#ytMobileCategorySuggestions::-webkit-scrollbar-track {
    background: transparent;
}

.jd-location-modal-head{
    position:relative;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:12px 15px;
    background:#fff;
}

.jd-location-back{
    position:absolute;
    left:8px;
    top:50%;
    transform:translateY(-50%);
    border:0;
    background:none;
    padding:0;
    color:#111;
    line-height:1;
    font-size:22px;
}

.jd-location-title-main{
    width:100%;
    text-align:center;
    font-size:15px;
    font-weight:600;
    color:#111;
}

	</style>

	<script>
		var currentRoute = @json(request()->route() ? request()->route()->getName() : '');
	</script>
<style>
/* location not in our list — shown under the box, before any search runs */
.sb-loc-invalid { position: relative; border-color: #dc3545 !important; box-shadow: 0 0 0 3px rgba(220,53,69,.12) !important; }
.sb-loc-tip { position: absolute; left: 0; top: calc(100% + 8px); z-index: 100050; width: max-content; max-width: min(360px, 90vw);
    display: flex; gap: 8px; align-items: flex-start; background: #fff; color: #b42318; border: 1px solid #f3c0c0; border-radius: 10px;
    padding: 9px 12px; font-size: 13px; line-height: 1.45; font-weight: 500; box-shadow: 0 8px 24px rgba(16,24,40,.12); white-space: normal; text-align: left; }
.sb-loc-tip::before { content: ""; position: absolute; top: -6px; left: 22px; width: 10px; height: 10px; background: #fff;
    border-left: 1px solid #f3c0c0; border-top: 1px solid #f3c0c0; transform: rotate(45deg); }
.sb-loc-tip i { font-size: 16px; margin-top: 1px; color: #dc3545; }
.sb-loc-tip--up { top: auto; bottom: calc(100% + 8px); }
.sb-loc-tip--up::before { top: auto; bottom: -6px; transform: rotate(225deg); }
</style>
</head>
@php
    $sbIsHomeLike = request()->routeIs('home') || request()->routeIs('home.city') || request()->routeIs('single.slug');

    /*
     * The mobile header has two shapes, and which one a page uses was decided
     * in JavaScript at the very end of a long script. So the phone painted the
     * plain header first and swapped in the search bar a moment later — a
     * visible jump on every page. The class is written here instead, so the
     * right header is there in the first paint. Every rule that reads it sits
     * inside @media (max-width: 991px), so this changes nothing on desktop.
     */
    $sbBodyClass = trim(($sbIsHomeLike ? 'home-page' : '')
        . (request()->routeIs('home') ? '' : ' mobile-yt-search-open')
        // Every page but the home page gets the two-row phone header (back +
        // logo + actions, then the search bar), like JustDial's.
        . (request()->routeIs('home') ? '' : ' jd-head'));
@endphp
<body class="{{ $sbBodyClass }}"
    data-page-name="{{ $pageName ?? request()->path() }}"
    @if(!empty($trackListingId)) data-listing-id="{{ $trackListingId }}" @endif
    @if(!empty($trackCategoryId)) data-category-id="{{ $trackCategoryId }}" @endif
    @if(!empty($trackLocationId)) data-location-id="{{ $trackLocationId }}" @endif
    @if(isset($searchResultsCount)) data-search-results-count="{{ $searchResultsCount }}" @endif
    @if(!empty($searchKeyword)) data-search-keyword="{{ $searchKeyword }}" @endif
    @if(!empty($searchCategoryId)) data-search-category-id="{{ $searchCategoryId }}" @endif
    @if(!empty($searchLocationId)) data-search-location-id="{{ $searchLocationId }}" @endif
>
@auth
    @php
        // The business named in the profile menu must be a live (approved)
        // one; a pending or rejected listing isn't the user's business yet.
        $sbHeaderBusiness = auth()->user()->listings()->where('status', 1)->latest()->first();
        // The header "My Business" link opens the user's listings page, which
        // shows pending ones with their status, so any listing counts there.
        $sbHasBusinessListing = $sbHeaderBusiness || auth()->user()->listings()->exists();
    @endphp
@endauth
    <a href="#main-content" class="skip-link">
    Skip to main content
</a>

{{-- Page-transition loader — category/subcategory pages are the biggest
     listing queries on the site, and a plain click gave no feedback while
     the next page rendered. Shown on any same-site link click, hidden again
     if the browser restores this page from bfcache (back button) instead of
     actually navigating. See the script right after this markup. --}}
<div id="pageLoader" class="page-loader" aria-hidden="true">
    <div class="page-loader-spinner"></div>
</div>

<style>
    .page-loader{
        position:fixed;
        inset:0;
        background:rgba(255,255,255,.85);
        z-index:2147483647;
        display:none;
        align-items:center;
        justify-content:center;
    }
    .page-loader.show{
        display:flex;
    }
    .page-loader-spinner{
        width:44px;
        height:44px;
        border:4px solid #e2e2e2;
        border-top-color:#0d6efd;
        border-radius:50%;
        animation:pageLoaderSpin .7s linear infinite;
    }
    @keyframes pageLoaderSpin{
        to{ transform:rotate(360deg); }
    }
</style>
<style>
/* Phone header up to 991px (iPads too): search is the 🔍 button / the search
   row, never the desktop location + search boxes. On a phone those boxes were
   only "hidden" by being pushed off-screen; on an iPad they came back into
   view, overflowing the header once the page scrolled. */
@media (max-width:991px){
    #stickyScrollHeader #scrollSearchHolder{ display:none !important; }
}
</style>
<style>
/* 992–1199px: the header's two search boxes didn't match — location
   grey with a heavy black border, search white with a shadow — and the bell sat
   on a grey square. One style for both boxes, a clean round bell. */
@media (min-width:992px) and (max-width:1199px){
    #stickyScrollHeader .header-input_location_box,
    #stickyScrollHeader .header-input_search_box{
        height:42px !important; background:#fff !important; border:1px solid #cfd6e0 !important;
        border-radius:10px !important; box-shadow:none !important; overflow:hidden;
        transition:border-color .15s ease, box-shadow .15s ease;
    }
    #stickyScrollHeader .header-input_location_box:focus-within,
    #stickyScrollHeader .header-input_search_box:focus-within{ border-color:#0d6efd !important; box-shadow:0 0 0 3px rgba(13,110,253,.12) !important; }
    #stickyScrollHeader .header-input_location,
    #stickyScrollHeader .header-input_search{ height:40px !important; background:transparent !important; font-size:15px !important; }
    #stickyScrollHeader .sb-bell{
        width:40px !important; height:40px !important; border-radius:50% !important; background:transparent !important;
        border:0 !important; display:inline-flex !important; align-items:center; justify-content:center; margin:0 4px 0 10px !important; font-size:19px; color:#1f2937;
    }
    #stickyScrollHeader .sb-bell:active, #stickyScrollHeader .sb-bell:hover{ background:#f1f3f7 !important; }
    #stickyScrollHeader .navbar-toggler{ padding:0 !important; border:0 !important; }
    #stickyScrollHeader .navbar-toggler .profile-icon{ width:38px !important; height:38px !important; border-radius:50%; object-fit:cover; }
    #stickyScrollHeader .navbar{ align-items:center !important; }
    /* the white fade at the box's right edge was cutting through the new border */
    #stickyScrollHeader .header-input_location_box::after{ display:none !important; }
}
</style>


<script>
document.addEventListener('click', function (e) {
    const link = e.target.closest('a[href]');
    if (!link) return;

    const href = link.getAttribute('href');
    if (!href
        || href.startsWith('#')
        || href.startsWith('javascript:')
        || href.startsWith('tel:')
        || href.startsWith('mailto:')
    ) return;

    if (link.target === '_blank' || link.hasAttribute('download')) return;
    if (e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;

    let url;
    try {
        url = new URL(href, window.location.href);
    } catch (err) {
        return;
    }

    // Only same-site navigations that actually leave this page — an
    // in-page "#section" jump or a link back to the same URL shouldn't
    // spin up a full-page overlay.
    if (url.origin !== window.location.origin) return;
    if (url.pathname === window.location.pathname && url.search === window.location.search) return;

    const loader = document.getElementById('pageLoader');
    if (loader) loader.classList.add('show');
});

// Hides it if the browser restores this exact page from bfcache (the back
// button) rather than loading a fresh one — a fresh page load never has
// the class in the first place, so this only ever needs to remove it.
window.addEventListener('pageshow', function () {
    const loader = document.getElementById('pageLoader');
    if (loader) loader.classList.remove('show');
});
</script>

	<header class="header-area" id="stickyScrollHeader">
		<div class="bottom-header">
			<div class="container-fuldie custom-right .head-right ">
				<nav class="navbar navbar-expand-lg navbar-light">
                @if(!request()->routeIs('home'))
                {{-- Phone, every page but home: first header row — back, logo, share, bell/profile. --}}
                <div class="jdh-top">
                    <button type="button" class="jdh-ico" aria-label="Go back" onclick="goBackPage()"><i class="las la-angle-left"></i></button>
                    <a href="{{ route('home') }}" class="jdh-logo"><img src="{{ asset('assets/images/logo/logo.png') }}" alt="Service Bazzar"></a>
                    <button type="button" class="jdh-ico jdh-share" aria-label="Share this page" onclick="sbJdhShare()"><img src="{{ asset('assets/images/download.svg') }}" alt="" class="jdh-share-img"></button>
                    @auth
                        <button type="button" class="sb-bell jdh-ico" aria-label="Notifications"><i class="fa-regular fa-bell"></i><span class="sbn-badge" hidden></span></button>
                    @else
                        <button type="button" class="jdh-ico" aria-label="Log in" onclick="openSbAuthPopup()"><i class="las la-user-circle"></i></button>
                    @endauth
                </div>
                @endif
                @if(!request()->routeIs('home') && !request()->routeIs('home.city') && !request()->routeIs('single.slug'))
<button type="button" class="mobile-back-btn" aria-label="Go back" onclick="goBackPage()">
    <i class="las la-angle-left"></i>
</button>
@endif
					<a class="navbar-brand" href="{{ route('home') }}">
						<img class="logo-hight" src="{{ asset('assets/images/logo/logo.png') }}" alt="Service Bazzar">
					</a>
                  <div id="scrollSearchHolder">
    @include($themeView.'includes.search-form')
</div>

<button type="button" id="mobileHeaderSearchBtn" class="mobile-header-search-btn" aria-label="Search">
    <i class="fas fa-search"></i>
</button>

@php
    $mobileSearchValue = session('selected_category', '');
    $mobileLocationValue = english_location_name(session('selected_location', ''));
@endphp

<div class="youtube-mobile-search" id="youtubeMobileSearch">
    <button type="button" class="yt-search-back" id="ytSearchBackBtn" aria-label="Back">
        <i class="las la-angle-left"></i>
    </button>

    <form action="{{ route('listings.search') }}" method="GET" class="yt-search-form">
      <div class="yt-search-input-box">

    <button type="submit" class="yt-search-submit left-search" aria-label="Search">
        <i class="fas fa-search"></i>
    </button>

    <input type="text"
           name="categorysearch"
           id="ytMobileSearchInput"
           value="{{ $mobileSearchValue }}"
           placeholder="Search Service Bazzar"
           autocomplete="off">

    <button type="button" class="yt-mic-btn inside-mic" id="ytMicBtn" aria-label="Voice search">
        <i class="fas fa-microphone"></i>
    </button>

    <ul id="ytMobileCategorySuggestions" class="yt-mobile-suggestions"></ul>

</div>

        
    </form>
</div>

				@guest
<button class="navbar-toggler"
        type="button"
        onclick="openSbAuthPopup()">
    <img src="{{ asset('assets/images/149071.png') }}" alt="User profile" class="profile-icon">
</button>
@else
<button type="button" class="sb-bell sb-bell-mobile" aria-label="Notifications"><i class="fa-regular fa-bell"></i><span class="sbn-badge" hidden></span></button>
<a href="{{ route('user.dashboard') }}" class="navbar-toggler profile-dashboard-link">
    <img src="{{ userAvatarUrl(auth()->user()->image ?? null) }}" alt="{{ auth()->user()->name ?? 'User profile' }}" class="profile-icon">
</a>
@endguest

				

					<div class="collapse navbar-collapse" id="navbarSupportedContent">
						@php
							$menuServices = activeServices();
						@endphp

						<ul class="navbar-nav ml-auto">
							<li class="nav-item dropdown hide-on-scroll-menu d-flex align-items-center">

    <a class="nav-link"
       href="{{ route('home.services.index') }}">
        Services
    </a>

<style>
    /* The caret beside "Services" was a 14px-wide tap target. Widening the
       padding gives it the 24px minimum; the arrow itself is unchanged. */
    #servicesDropdown.dropdown-toggle-split {
        padding-left: 10px;
        padding-right: 10px;
        min-width: 26px;
    }
</style>
    <a class="nav-link dropdown-toggle dropdown-toggle-split"
       href="#"
       id="servicesDropdown"
       role="button"
       data-bs-toggle="dropdown"
       aria-expanded="false"
       aria-label="Open the services menu">
    </a>

    <ul class="dropdown-menu" aria-labelledby="servicesDropdown">
        @forelse($menuServices as $item)
            <li>
                <a class="dropdown-item"
                   href="{{ route('services.show', $item->slug) }}">
                    {{ $item->menu_title ?: $item->title }}
                </a>
            </li>
        @empty
            <li>
                <span class="dropdown-item">No Services Found</span>
            </li>
        @endforelse
    </ul>

</li>

						<li class="nav-item hide-on-scroll-menu">
								<a class="nav-link" href="{{ route('blogs') }}">@lang('Blog')</a>
							</li>

							<li class="nav-item hide-on-scroll-menu">
    <a class="nav-link" href="{{ route('contact') }}">Contact</a>
</li>

							<li class="nav-item">
    @guest
        <a href="{{ route('free.listing') }}"
           onclick="event.preventDefault(); openSbAuthPopup();"
           class="nav-link">
            <i class="fa-solid fa-arrow-trend-up me-1"></i>
            @lang('Free Listing')
        </a>
    @else
        @if($sbHasBusinessListing)
            <a href="{{ route('user.listing.index') }}" class="nav-link">
                <i class="fa-solid fa-briefcase me-1"></i>
                @lang('My Business')
            </a>
        @else
            <a href="{{ route('free.listing') }}" class="nav-link">
                <i class="fa-solid fa-arrow-trend-up me-1"></i>
                @lang('Free Listing')
            </a>
        @endif
    @endguest
</li>
							@guest
								<li class="nav-item">
						{{-- Real destination so the link is crawlable and can be
						     opened in a new tab; the popup still handles a click. --}}
						<a href="{{ route('user.login') }}"
						   onclick="event.preventDefault(); openSbAuthPopup();" class="nav-link nav-btn">
    Login / Sign Up
</a>
								</li>
							@else
								<li class="nav-item d-none d-lg-flex align-items-center">
									<button type="button" class="sb-bell" aria-label="Notifications"><i class="fa-regular fa-bell"></i><span class="sbn-badge" hidden></span></button>
								</li>
								<li class="nav-item profile-dashboard-item">
									<a href="{{ route('user.dashboard') }}" class="nav-link nav-btn profile-dashboard-link d-flex align-items-center gap-2" title="{{ auth()->user()->fullname() }}">
										<img src="{{ userAvatarUrl(auth()->user()->image ?? null) }}" alt="{{ auth()->user()->fullname() }}" style="width:26px;height:26px;border-radius:50%;object-fit:cover;">
									</a>
								</li>
							@endguest
						</ul>
					</div>
				</nav>

			
				<div class="jd-mobile-location-strip" id="jdMobileLocationStrip">
				<button type="button" class="jd-mobile-city-btn detecting" id="jdMobileCityBtn">
						<i class="las la-map-marker"></i>
						<span id="jdMobileCityText">{{ $mobileLocationValue }}</span>
						<i class="las la-angle-down"></i>
					</button>

                    
				<div class="jd-mobile-location-box" id="jdMobileLocationBox">

    <div class="jd-location-modal-head">
        <button type="button"
                class="jd-location-back"
                id="jdLocationBackBtn">
            <i class="las la-angle-left"></i>
        </button>

        <div class="jd-location-title-main">
            Your Location
        </div>
    </div>

    <input type="text"
           id="jdMobileLocationInput"
           placeholder="Enter location..."
           value="{{ $mobileLocationValue }}">

    <div id="jdMobileLocationSuggestions"
         class="jd-mobile-location-suggestions"></div>

</div>
				</div>
			</div>
		</div>
	</header>

    {{-- The skip link at the top points here. Without an element carrying this
	     id it had nothing to jump to, which is why it never took focus. --}}
    <main id="main-content">
    @yield('content')
    </main>


	@include($themeView.'includes.footer')

	<div class="mobile-bottom-action" id="mobileBottomAction">
		<a href="{{ route('home') }}" class="mobile-action-item {{ request()->routeIs('home') ? 'active' : '' }}">
			<i class="las la-home"></i>
			<span>Home</span>
		</a>

		<a href="{{ route('blogs') }}" class="mobile-action-item">
			<i class="las la-newspaper"></i>
			<span>Blogs</span>
		</a>

		<a href="{{ route('contact') }}" class="mobile-action-item">
			<i class="las la-phone"></i>
			<span>Contact</span>
		</a>

		@guest
			<a href="{{ route('free.listing') }}"
			   onclick="event.preventDefault(); openSbAuthPopup();" class="mobile-action-btn">
				Free Listing
			</a>
		@else
            @if($sbHasBusinessListing)
                <a href="{{ route('user.listing.index') }}" class="mobile-action-btn">
                    My Business
                </a>
            @else
                <a href="{{ route('free.listing') }}" class="mobile-action-btn">
                    Free Listing
                </a>
            @endif
		@endguest
	</div>


<style>

    #stickyScrollHeader.scrolled #scrollSearchHolder,
.header-area.fixed-header #scrollSearchHolder{
    display:block;
}
    .yt-search-input-box{
    position:relative;
}

.yt-mobile-suggestions{
    display:none;
    position:fixed;
    left:0;
    right:0;
    top:95px;
    background:#fff;
    z-index:9999999;
    list-style:none;
    margin:0;
    padding:0;
    max-height:300px;
    overflow-y:auto;
    box-shadow:0 8px 18px rgba(0,0,0,.12);
}

.yt-mobile-suggestions.show{
    display:block;
}

.yt-mobile-suggestions li a,
.yt-mobile-suggestions li span{
    text-decoration:none;
    border-bottom:1px solid #eee;
    display: block;
    padding: 10px 14px;
    color: #222;
    background: #fff;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    line-height: 1.4;
    letter-spacing: .1px;
    white-space: normal !important;
    word-break: break-word !important;
    overflow-wrap: break-word !important;
    text-decoration: none;
    max-width: 100%;
    box-sizing: border-box;
}
.yt-mobile-suggestions li span{
    border:none !important;
}

@media (max-width: 991px){

   body.mobile-yt-search-open #stickyScrollHeader .navbar{
    order:2 !important;
    margin-top:0 !important;
    width:100% !important;
}

    body.mobile-yt-search-open #youtubeMobileSearch{
        display:flex !important;
        width:100% !important;
        position:relative !important;
        z-index:9999999 !important;
        background:#fff !important;
    }

    body.mobile-yt-search-open #jdMobileLocationStrip{
        order:1 !important;
        width:100% !important;
    }

    body.mobile-yt-search-open:not(.show-yt-location) #jdMobileLocationStrip{
        display:none !important;
    }

  body.mobile-yt-search-open.show-yt-location #jdMobileLocationStrip{
    display:block !important;
    position:relative !important;
    top:auto !important;
    left:auto !important;
    width:100% !important;
    z-index:auto !important;
    padding:6px 10px 4px !important;
    background:#fff !important;
}
}
@media (max-width: 991px){
    #jdMobileLocationBox{
        position:relative !important;
        z-index:99999999 !important;
    }

  

    #jdMobileLocationSuggestions.show{
        display:block !important;
    }
}

@media (max-width: 991px){


    body.mobile-yt-search-open.show-yt-location #stickyScrollHeader{
        overflow:visible !important;
    }

 body.mobile-yt-search-open.show-yt-location #jdMobileLocationSuggestions{
    position:relative !important;
    top:0 !important;
    left:0 !important;
    right:auto !important;
    width:100% !important;
    max-height:260px !important;
    background:#fff !important;
    z-index:999999999 !important;
    overflow-y:auto !important;
    box-shadow:0 8px 18px rgba(0,0,0,.12) !important;
    border-radius:0 0 8px 8px !important;
    margin-top:6px !important;
}
}
#ytMobileSearchInput{
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    padding-right: 28px !important;
}.yt-search-input-box{
    min-width: 0 !important;
}

.yt-search-input-box input{
    min-width: 0 !important;
}
.navbar-nav .dropdown-menu {
    min-width: 240px !important;
    width: auto !important;
}

.navbar-nav .dropdown-item {
    white-space: nowrap !important;
    padding: 10px 20px !important;
}

.location-permission-popup {
    position: fixed;
    inset: 0;
    z-index: 999999999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 18px;
    background: rgba(0, 0, 0, .48);
}

.location-permission-popup[hidden] {
    display: none;
}

.location-permission-popup__box {
    width: min(420px, 100%);
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 18px 50px rgba(0, 0, 0, .24);
    overflow: hidden;
}

.location-permission-popup__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 16px;
    border-bottom: 1px solid #f0f0f0;
}

.location-permission-popup__head h5 {
    margin: 0;
    font-size: 17px;
    font-weight: 700;
    color: #222;
}

.location-permission-popup__close {
    width: 32px;
    height: 32px;
    border: 0;
    border-radius: 50%;
    background: transparent;
    color: #333;
    font-size: 26px;
    line-height: 1;
    cursor: pointer;
}

.location-permission-popup__close:hover,
.location-permission-popup__close:focus {
    background: #f5f5f5;
    outline: none;
}

.location-permission-popup__body {
    padding: 16px;
}

.location-permission-popup__message {
    display: flex;
    gap: 10px;
    margin: 0;
    color: #444;
    font-size: 14px;
    line-height: 1.5;
}

.location-permission-popup__message i {
    color: #e5581e;
    font-size: 18px;
    line-height: 1.4;
    flex: 0 0 auto;
}

.location-permission-popup__steps {
    margin: 12px 0 0;
    padding-left: 20px;
    color: #6d4b13;
    font-size: 13px;
    line-height: 1.6;
}

.location-permission-popup__actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 0 16px 16px;
}

.location-permission-popup__btn {
    border: 0;
    border-radius: 8px;
    padding: 9px 16px;
    background: #e5581e;
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
}

.location-permission-popup__btn--muted {
    background: #f2f2f2;
    color: #333;
}

@media (max-width: 991px){
    .location-permission-popup {
        align-items: flex-end;
        padding: 12px;
    }

    .location-permission-popup__box {
        border-radius: 12px;
    }

    .location-permission-popup__actions {
        flex-direction: column-reverse;
    }

    .location-permission-popup__btn {
        width: 100%;
    }
}
@media (max-width: 991px){

    body.mobile-yt-search-open.show-yt-location #jdMobileLocationSuggestions{
        position:fixed !important;
       top:95px !important;
        left:0 !important;
        right:0 !important;
        width:100% !important;
      height:calc(100vh - 50px) !important;
        max-height:none !important;
        background:#fff !important;
        z-index:99999999 !important;
        overflow-y:auto !important;
        box-shadow:none !important;
        border-radius:0 !important;
        margin:0 !important;
        padding-bottom:90px !important;
    }

    body.mobile-yt-search-open.show-yt-location #jdMobileLocationSuggestions .dropdown-item{
        padding:14px 22px !important;
        font-size:13px !important;
        border-bottom:1px solid #eee !important;
        background:#fff !important;
    }

    body.mobile-yt-search-open.show-yt-location .jd-location-title{
        padding:14px 22px 8px !important;
        border-bottom:1px solid #eee !important;
        background:#fff !important;
    }
}

</style>

<div class="location-permission-popup" id="locationPermissionPopup" hidden>
    <div class="location-permission-popup__box" role="dialog" aria-modal="true" aria-labelledby="locationPermissionPopupTitle">
        <div class="location-permission-popup__head">
            <h5 id="locationPermissionPopupTitle">Turn on location</h5>
            <button type="button" class="location-permission-popup__close" data-location-popup-close aria-label="Close">&times;</button>
        </div>
        <div class="location-permission-popup__body">
            <p class="location-permission-popup__message">
                <i class="fas fa-location-dot"></i>
                <span id="locationPermissionPopupMessage">Please allow location access to detect your city.</span>
            </p>
            <ol class="location-permission-popup__steps" id="locationPermissionPopupSteps" hidden>
                <li>Tap the lock or info icon near the website address.</li>
                <li>Open <strong>Site settings</strong> or <strong>Permissions</strong>.</li>
                <li>Set <strong>Location</strong> to <strong>Allow</strong>.</li>
                <li>Come back and tap <strong>Try again</strong>.</li>
            </ol>
        </div>
        <div class="location-permission-popup__actions">
            <button type="button" class="location-permission-popup__btn location-permission-popup__btn--muted" data-location-popup-close>Cancel</button>
            <button type="button" class="location-permission-popup__btn" id="locationPermissionPopupRetry">Try again</button>
        </div>
    </div>
</div>

<script>
window.showLocationPermissionPopup = function(options){
    options = options || {};

    const popup = document.getElementById('locationPermissionPopup');
    const message = document.getElementById('locationPermissionPopupMessage');
    const steps = document.getElementById('locationPermissionPopupSteps');
    const retry = document.getElementById('locationPermissionPopupRetry');

    if(!popup || !message || !steps || !retry) return;

    message.textContent = options.message || 'Please allow location access to detect your city.';
    steps.hidden = !options.blocked;
    retry.textContent = options.retryText || (options.blocked ? 'Try again' : 'Turn on location');
    popup.hidden = false;

    retry.onclick = function(){
        popup.hidden = true;
        if(typeof options.onRetry === 'function') options.onRetry();
    };
};

document.addEventListener('click', function(event){
    if(event.target.matches('[data-location-popup-close]')){
        const popup = document.getElementById('locationPermissionPopup');
        if(popup) popup.hidden = true;
    }
});
</script>

	{{-- ============================================================
	     JS LOADING STRATEGY
	     jquery + bootstrap + toastr: sync — inline scripts in this layout use
	     $ and toastr directly, so they must be ready before those <script> blocks.
	     app.js: deferred — it initialises the slick slider once the DOM is up.
	     at parse time, saving ~30KB of main-thread blocking.
	     tracking.js: defer (unchanged).
	     ============================================================ --}}
	{{-- SYNC (inline scripts below need these immediately): jQuery 88KB + Bootstrap 48KB + Toastr 5.5KB --}}
	<script src="{{ asset($themeAsset.'js/jquery-3.6.0.min.js') }}"></script>
	<script src="{{ asset($themeAsset.'js/bootstrap.min.js') }}"></script>
	<script src="{{ asset($themeAsset.'js/toastr.min.js') }}"></script>

	{{-- Every page used to carry select2 (72 KB), wow (8 KB) and
	     bootstrap-notify (8 KB). Nothing in the theme calls any of them:
	     no element carries class="select2" (app.js's `$('.select2').select2()`
	     matched nothing), `new WOW` appears nowhere, and neither does
	     `.notify(`. They were downloaded, parsed and evaluated on every visit
	     for no effect, which is main-thread time the LCP image was waiting on.

	     lightcase and starrr are real, but only on the listing detail page —
	     they are pushed from there now instead of loading site-wide. --}}
	<script src="{{ asset($themeAsset.'js/slick.min.js') }}" defer></script>
	<script src="{{ asset($themeAsset.'js/app.js') }}?v={{ filemtime(public_path($themeAsset.'js/app.js')) }}" defer></script>

	<script src="{{ asset('assets/js/tracking.js') }}" defer></script>

	@stack('js')


<script>
document.addEventListener("DOMContentLoaded", function () {

    const header = document.getElementById("stickyScrollHeader");
    const hideMenus = document.querySelectorAll(".hide-on-scroll-menu");

    const isHomePage = @json(
        request()->routeIs('home') ||
        request()->routeIs('home.city')
    );

    // Home ke alawa sab pages par hide rakho
    if (!isHomePage) {
    hideMenus.forEach(function(item){
        item.style.display = "none";
    });
}

    function toggleHeaderScroll() {

        const scrollLimit = 160;

        if (window.scrollY > scrollLimit) {

            header?.classList.add("scrolled");
            document.body.classList.add("header-fixed");

            hideMenus.forEach(function(item){
                item.style.display = "none";
            });

        } else {

            header?.classList.remove("scrolled");
            document.body.classList.remove("header-fixed");

            hideMenus.forEach(function(item){
                item.style.display = "";
            });
        }
    }

    window.addEventListener("scroll", toggleHeaderScroll);

    setTimeout(toggleHeaderScroll, 300);

});
</script>

<script>
"use strict";

toastr.options = {
    "closeButton": false,
    "debug": false,
    "newestOnTop": false,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": true,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "3000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};

@if ($errors->any())
    @foreach ($errors->all() as $emsg)
        toastr.error('{{ $emsg }}');
    @endforeach
@endif

@if(session()->has('alert'))
    @if(session('alert')[0] == 'danger')
        toastr.error('{{ session('alert')[1] }}');
    @elseif(session('alert')[0] == 'success')
        toastr.success('{{ session('alert')[1] }}');
    @else
        toastr.error('{{ session('alert')[1] }}');
    @endif
@endif

function systemAlert(type, message) {
    if (type === 'danger') {
        toastr.error(message);
    } else if (type === 'success') {
        toastr.success(message);
    } else {
        toastr.error(message);
    }
}

$('.language').change(function () {
    window.location.href = `{{ url('/language') }}/${$(this).val()}`;
});
</script>

<script>
(function ($) {
    "use strict";

    var pollInterval = 8000;
    var soundUrl = @json(asset($themeAsset.'sound/message.wav'));
    var notifyUrl = @json(route('guest-chat.notifications'));
    var textOwnerReplied = @json(__('Owner replied'));
    var hasStorage = true;
    var lastId = 0;
    var guestKey = null;
    var audio = null;

    try {
        localStorage.setItem('_t', '1');
        localStorage.removeItem('_t');
    } catch (e) {
        hasStorage = false;
    }

    function shouldPoll() {
        if (!hasStorage) {
            return true;
        }

        return localStorage.getItem('guestChatSession') === '1';
    }

    function isChatActive() {
        return window.guestChatActive === true;
    }

    function getAudio() {
        if (!audio) {
            audio = new Audio(soundUrl);
            audio.preload = 'auto';
        }

        return audio;
    }

    function playSound() {
        try {
            var a = getAudio();
            a.pause();
            a.currentTime = 0;
            a.volume = 0.6;
            a.play().catch(function () {});
        } catch (e) {}
    }

    function poll() {
        if (!shouldPoll()) {
            return;
        }

        $.get(notifyUrl, { _t: Date.now() })
            .done(function (resp) {
                if (!resp || !resp.ok) {
                    return;
                }

                if (!resp.active) {
                    if (hasStorage) {
                        localStorage.setItem('guestChatSession', '0');
                    }

                    return;
                }

                if (!guestKey && resp.guest_id) {
                    guestKey = 'guestChatLastId_' + resp.guest_id;

                    if (hasStorage) {
                        lastId = parseInt(localStorage.getItem(guestKey) || '0', 10);
                    }
                }

                if (resp.latest_id && resp.latest_id > lastId) {
                    lastId = resp.latest_id;

                    if (hasStorage && guestKey) {
                        localStorage.setItem(guestKey, String(lastId));
                    }

                    if (!isChatActive()) {
                        try {
                            if (window.toastr) {
                                var listing = resp.listing_name ? ' - ' + resp.listing_name : '';
                                toastr.info(textOwnerReplied + listing);
                            }
                        } catch (e) {}

                        if (!hasStorage || localStorage.getItem('guestChatSound') !== 'off') {
                            playSound();
                        }
                    }
                }
            });
    }

    setInterval(poll, pollInterval);
    poll();
})(jQuery);
</script>


<style>
    .smww{
     
        color: #0076d7 !important;
       
        border-bottom: 1px solid #eee;
     
    }

    
</style>
<script>
    document.addEventListener("DOMContentLoaded", function () {
    const homeInput = document.getElementById("homeLocationInput");
    const headerInput = document.getElementById("locationInput");
    const homeDropdown = document.getElementById("homeLocationSuggestions");
    const headerDropdown = document.getElementById("locationSuggestions");

    const categoryInput = document.getElementById("CategorySearch");
    const categoryDropdown = document.getElementById("categorySuggestions");

    const jdStrip = document.getElementById("jdMobileLocationStrip");
    const jdCityBtn = document.getElementById("jdMobileCityBtn");
    const jdCityText = document.getElementById("jdMobileCityText");
    const jdLocationBox = document.getElementById("jdMobileLocationBox");
    const jdInput = document.getElementById("jdMobileLocationInput");
    
    const jdDropdown = document.getElementById("jdMobileLocationSuggestions");

    document.addEventListener("click", function(e){

    const backBtn = e.target.closest("#ytSearchBackBtn");
    if (!backBtn) return;

    e.preventDefault();
    e.stopPropagation();

    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = "{{ route('home') }}";
    }

}, true);

if (jdCityBtn && jdLocationBox) {

    jdCityBtn.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        const isOpen = jdLocationBox.classList.contains("show");

        const ytCat = document.getElementById("ytMobileCategorySuggestions");
        if (ytCat) {
            ytCat.classList.remove("show");
            ytCat.innerHTML = "";
        }

      if (isOpen) {

    jdLocationBox.classList.remove("show");
    closeDropdown(jdDropdown);

    jdCityBtn.style.setProperty("display", "flex", "important");
    document.body.classList.remove("show-yt-location");

    const ytSearchBox = document.getElementById("youtubeMobileSearch");
    if (ytSearchBox) {
        ytSearchBox.style.removeProperty("display");
    }

    return;
}

document.body.classList.remove("mobile-yt-search-open");

document.body.classList.add("show-yt-location");
document.documentElement.style.overflow = "hidden";
document.body.style.overflow = "hidden";

const ytSearchBox = document.getElementById("youtubeMobileSearch");
if (ytSearchBox) {
    ytSearchBox.style.setProperty("display", "none", "important");
}

if (jdStrip) {
    jdStrip.classList.add("show");
    jdStrip.style.setProperty("display", "block", "important");
}

        jdCityBtn.style.setProperty("display", "none", "important");
        jdLocationBox.classList.add("show");

 if (jdInput) {
    jdInput.value = "";
    jdInput.dataset.selectedLocation = "0";
}

if (jdDropdown) {
    renderJdLocationList([], jdDropdown);
    jdDropdown.classList.add("show");
}

setTimeout(function () {
    fetchLocations("", jdDropdown);
}, 100);

setTimeout(function () {
    if (jdInput) jdInput.focus();
}, 150);
    });

}

document.addEventListener("mousedown", function(e){

    const backBtn = e.target.closest("#jdLocationBackBtn");
    if (!backBtn) return;

    e.preventDefault();
    e.stopPropagation();

    document.body.classList.remove("show-yt-location");

    document.documentElement.style.removeProperty("overflow");
    document.body.style.removeProperty("overflow");

    if (jdLocationBox) jdLocationBox.classList.remove("show");
    if (jdDropdown) closeDropdown(jdDropdown);

    if (jdStrip) {
        jdStrip.classList.remove("show");
        jdStrip.style.removeProperty("display");
    }

    if (jdCityBtn) {
        jdCityBtn.style.setProperty("display", "flex", "important");
    }

    const ytSearchBox = document.getElementById("youtubeMobileSearch");
    if (ytSearchBox) {
        ytSearchBox.style.removeProperty("display");
    }

    const isHomePage = @json(request()->routeIs('home'));

    if (isHomePage) {
        document.body.classList.remove("mobile-yt-search-open");
    } else {
        document.body.classList.add("mobile-yt-search-open");
    }

}, true);

    const savedCityKey = "jf_city";
    const savedLatKey = "jf_lat";
    const savedLngKey = "jf_lng";
const sessionSelectedLocation = @json(session('selected_location', ''));
const sessionSelectedCategory = @json(session('selected_category', ''));
    function isMobile() {
        return window.innerWidth <= 991;
    }

    function formatSlug(slug) {
        return String(slug || "")
            .replace(/-/g, " ")
            .replace(/\b\w/g, function (c) {
                return c.toUpperCase();
            })
            .trim();
    }

    function slugify(text) {
        return String(text || "")
            .toLowerCase()
            .trim()
            .replace(/,/g, "")
            .replace(/\s+/g, "-")
            .replace(/[^a-z0-9-]/g, "")
            .replace(/-+/g, "-")
            .replace(/^-+|-+$/g, "");
    }

    /*
     * Only real cities may become a URL. Typed text that was never picked from
     * the list ("Xn, Ksj, B Dskj") used to be slugged straight into
     * /ksj/it-consultants-in-xn and land on "Page Not Found".
     */
    @php
        $sbCityKeys = \Illuminate\Support\Facades\Cache::remember('sb:city_keys:v1', 3600, fn () =>
            \App\Models\Location::where('status', 1)->get(['name', 'slug'])
                ->flatMap(fn ($l) => [strtolower((string) $l->slug), \Illuminate\Support\Str::slug((string) $l->name)])
                ->filter()->unique()->values()->all());
    @endphp
    const SB_CITY_KEYS = new Set(@json($sbCityKeys));
    function sbCityOf(text) {                 // "Vaishali Nagar, Jaipur" -> "Jaipur"
        const parts = String(text || "").split(",");
        return (parts.length > 1 ? parts[parts.length - 1] : parts[0]).trim();
    }
    function sbKnownCity(text) {
        const c = sbCityOf(text);
        if (!c) return false;
        return SB_CITY_KEYS.has(slugify(c)) || SB_CITY_KEYS.has(c.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, ""));
    }
    window.sbKnownCity = sbKnownCity;
    function sbAskForRealCity() {
        const msg = "Please choose your city or area from the suggestions list.";
        ["homeLocationInput", "headerLocationInput", "jdMobileLocationInput"].forEach(function (id) {
            const el = document.getElementById(id);
            if (el && el.offsetParent !== null) { el.focus(); el.select && el.select(); }
        });
        if (window.Swal && Swal.fire) Swal.fire({ icon: "info", text: msg, timer: 3500, showConfirmButton: false });
        else alert(msg);
    }
    window.sbAskForRealCity = sbAskForRealCity;

    // Junk saved in this browser before the check existed: drop it.
    try {
        ["jf_city", "jf_detected_city", "jf_selected_location_name", "jf_user_city"].forEach(function (k) {
            const v = localStorage.getItem(k);
            if (v && !sbKnownCity(v)) { localStorage.removeItem(k); if (k === "jf_detected_city") { localStorage.removeItem("jf_lat"); localStorage.removeItem("jf_lng"); } }
        });
        const rec = JSON.parse(localStorage.getItem("service_bazzar_recent_locations") || "[]");
        if (Array.isArray(rec)) {
            const ok = rec.filter(function (r) { return r && sbKnownCity(r.value); });
            if (ok.length !== rec.length) localStorage.setItem("service_bazzar_recent_locations", JSON.stringify(ok));
        }
    } catch (e) {}

    /*
     * Check the location BEFORE any search runs. The old search code builds the
     * results URL in several places straight from whatever is in the box, so
     * "sddddd" still searched and only bounced back afterwards. This runs first
     * (window, capture phase) for: picking a category suggestion, pressing
     * Enter in a search box, the search button, and form submit.
     */
    function sbLocInputFor(el) {
        // the suggestion lists may be moved elsewhere in the page for positioning
        if (el.closest("#homeCategorySuggestions")) return document.getElementById("homeLocationInput");
        if (el.closest("#headerCategorySuggestions")) return document.getElementById("headerLocationInput");
        if (el.closest("#homeSearchOriginalPlace, #homeSearchForm")) return document.getElementById("homeLocationInput");
        if (el.closest("#scrollSearchHolder, #headerSearchForm")) return document.getElementById("headerLocationInput");
        return null;
    }
    function sbClearLocError(input) {
        const box = input && input.closest(".input_location_box, .header-input_location_box") || (input && input.parentElement);
        if (!box) return;
        box.classList.remove("sb-loc-invalid");
        const holder = box.parentElement || box;
        holder.querySelectorAll(".sb-loc-tip").forEach(function (t) { t.remove(); });
    }
    function sbShowLocError(input, text) {
        if (!input) return;
        const box = input.closest(".input_location_box, .header-input_location_box") || input.parentElement;
        sbClearLocError(input);
        box.classList.add("sb-loc-invalid");
        const tip = document.createElement("div");
        tip.className = "sb-loc-tip";
        tip.setAttribute("role", "alert");
        tip.innerHTML = '<i class="las la-map-marker-alt" aria-hidden="true"></i><span></span>';
        tip.querySelector("span").textContent = text;
        // above the box when there is room (hero search), so the location list
        // that opens below it can't cover the message; below in the top bar
        if (input.getBoundingClientRect().top > 100) tip.classList.add("sb-loc-tip--up");
        // the box itself clips (overflow:hidden) — hang the message on its parent
        const holder = box.parentElement || box;
        if (getComputedStyle(holder).position === "static") holder.style.position = "relative";
        holder.appendChild(tip);
        input.focus();
        try { input.select(); } catch (e) {}
        input.dispatchEvent(new Event("focus"));
    }
    window.sbShowLocError = sbShowLocError;
    function sbLocationBlocked(el) {
        const input = sbLocInputFor(el);
        if (!input) return false;
        const v = input.value.trim();
        if (v === "" || sbKnownCity(v)) return false;
        sbShowLocError(input, '"' + v + '" is not a location we cover. Please pick your city or area from the list.');
        return true;
    }
    window.addEventListener("click", function (e) {
        const t = e.target;
        if (!t || !t.closest) return;
        const isSuggestion = t.closest("#homeCategorySuggestions a, #homeCategorySuggestions .dropdown-item, #homeCategorySuggestions li, #headerCategorySuggestions .header-dropdown-item, #headerCategorySuggestions a");
        const isButton = t.closest("#homeSearchForm button, #homeSearchForm [type=submit], #homeSearchForm .search-icon, #headerSearchForm button, #headerSearchForm [type=submit], #headerSearchForm .search-icon");
        if (!isSuggestion && !(isButton && !t.closest("#homeVoiceSearchBtn, #headerVoiceSearchBtn, #headerClearBtn"))) return;
        if (sbLocationBlocked(t)) { e.preventDefault(); e.stopImmediatePropagation(); }
    }, true);
    window.addEventListener("keydown", function (e) {
        if (e.key !== "Enter" || !e.target || !e.target.closest) return;
        if (!e.target.matches("#homeCategorySearch, #headerCategorySearch, #homeLocationInput, #headerLocationInput")) return;
        if (sbLocationBlocked(e.target)) { e.preventDefault(); e.stopImmediatePropagation(); }
    }, true);
    window.addEventListener("submit", function (e) {
        if (e.target && e.target.matches && e.target.matches("#homeSearchForm, #headerSearchForm") && sbLocationBlocked(e.target)) {
            e.preventDefault(); e.stopImmediatePropagation();
        }
    }, true);
    ["homeLocationInput", "headerLocationInput"].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) el.addEventListener("input", function () { sbClearLocError(el); });
    });

    const englishLocationNames = {
        "जयपुर": "Jaipur",
        "दिल्ली": "Delhi",
        "नई दिल्ली": "New Delhi",
        "मुंबई": "Mumbai",
        "बेंगलुरु": "Bengaluru",
        "बैंगलोर": "Bengaluru",
        "हैदराबाद": "Hyderabad",
        "अहमदाबाद": "Ahmedabad",
        "चेन्नई": "Chennai",
        "कोलकाता": "Kolkata",
        "पुणे": "Pune",
        "सूरत": "Surat",
        "लखनऊ": "Lucknow",
        "कानपुर": "Kanpur",
        "नागपुर": "Nagpur",
        "इंदौर": "Indore",
        "भोपाल": "Bhopal",
        "पटना": "Patna",
        "वडोदरा": "Vadodara",
        "गाजियाबाद": "Ghaziabad",
        "लुधियाना": "Ludhiana",
        "आगरा": "Agra",
        "नासिक": "Nashik",
        "फरीदाबाद": "Faridabad",
        "मेरठ": "Meerut",
        "राजकोट": "Rajkot",
        "वाराणसी": "Varanasi",
        "श्रीनगर": "Srinagar",
        "औरंगाबाद": "Aurangabad",
        "धनबाद": "Dhanbad",
        "अमृतसर": "Amritsar",
        "नवी मुंबई": "Navi Mumbai",
        "प्रयागराज": "Prayagraj",
        "इलाहाबाद": "Prayagraj",
        "रांची": "Ranchi",
        "हावड़ा": "Howrah",
        "कोयंबटूर": "Coimbatore",
        "जबलपुर": "Jabalpur",
        "ग्वालियर": "Gwalior",
        "विजयवाड़ा": "Vijayawada",
        "जोधपुर": "Jodhpur",
        "मदुरै": "Madurai",
        "रायपुर": "Raipur",
        "कोटा": "Kota",
        "चंडीगढ़": "Chandigarh",
        "गुवाहाटी": "Guwahati",
        "मैसूर": "Mysuru",
        "उदयपुर": "Udaipur",
        "अजमेर": "Ajmer",
        "अलवर": "Alwar",
        "भीलवाड़ा": "Bhilwara",
        "बीकानेर": "Bikaner"
    };

    window.normalizeEnglishLocationName = function(value) {
        let city = String(value || "").replace(/\s+/g, " ").trim();
        if (!city) return "";
        if (englishLocationNames[city]) return englishLocationNames[city];
        if (city.includes(",")) {
            const parts = city.split(",").map(function(part){ return part.trim(); }).filter(Boolean);
            for (let i = parts.length - 1; i >= 0; i--) {
                if (englishLocationNames[parts[i]]) return englishLocationNames[parts[i]];
            }
            // No Hindi part matched — this is an ordinary "Area, City"
            // string (the shape used everywhere in this app for a
            // suggestion/recent search), not a Hindi geocoder result that
            // needs translating. Return it exactly as given instead of
            // collapsing to just the last comma-separated part, which used
            // to silently drop "Vaishali Nagar" from "Vaishali Nagar,
            // Jaipur" down to just "Jaipur" — this function is called on
            // every location selection via cleanCity()/setCity(), so that
            // collapse showed up as the header always snapping back to the
            // bare city name no matter which area the admin actually picked.
            return city;
        }
        return englishLocationNames[city] || city;
    };

    function normalizeStoredCityValues() {
        ["jf_city", "jf_detected_city", "jf_selected_location_name", "jf_user_city"].forEach(function(key) {
            const value = localStorage.getItem(key);
            const normalized = window.normalizeEnglishLocationName(value);
            if (value && normalized && normalized !== value) {
                localStorage.setItem(key, normalized);
            }
        });
    }

    function cleanCity(value) {
        let city = window.normalizeEnglishLocationName(value);
        if (!city) return "";
        city = city.replace(/\s+/g, " ").trim();
        if (
            city.toLowerCase().includes("detect current location") ||
            city.toLowerCase().includes("no result") ||
            city.toLowerCase().includes("no location")
        ) {
            return "";
        }
        return city;
    }

    normalizeStoredCityValues();

    // ✅ SET CITY - ONLY INPUTS + LOCALSTORAGE (NO URL OVERRIDE)
  function setCity(value, save = false, lat = "", lng = "") {
    const city = cleanCity(value);
    if (!city) return;
    if (!sbKnownCity(city)) return;   // never show / store a place we don't have

    if (homeInput) {
        homeInput.value = city;
        homeInput.dataset.selectedLocation = "1";
    }

    if (headerInput) {
        headerInput.value = city;
        headerInput.dataset.selectedLocation = "1";
    }

    if (jdInput) {
        jdInput.value = city;
        jdInput.dataset.selectedLocation = "1";
    }

    if (jdCityText) {
        jdCityText.textContent = city;
    }

    // Location just changed — re-point every category link to the new city
    // immediately, so the visitor never has to refresh to see /delhi/... etc.
    if (typeof window.sbRefreshCategoryLinks === "function") {
        window.sbRefreshCategoryLinks();
    }

    if (save) {
        try {

            // ✅ Detect Location se aaya ho
            if (lat && lng) {
                localStorage.setItem("jf_detected_city", city);
                localStorage.setItem("jf_lat", lat);
                localStorage.setItem("jf_lng", lng);
                // When, so a stale city is not shown days later.
                localStorage.setItem("jf_detected_at", String(Date.now()));
            }

            // ❌ yaha jf_city set nahi karna
            // jf_city manual selected city ke liye alag set hoga

        } catch (e) {}
    }

    if (isMobile() && jdStrip) {
        jdStrip.classList.remove("show");
    }

    if (jdLocationBox) {
        jdLocationBox.classList.remove("show");
    }

    if (jdCityBtn) {
        jdCityBtn.style.display = "flex";
    }
}
    function clearCity() {
        if (homeInput) homeInput.value = "";
        if (headerInput) headerInput.value = "";
        if (jdInput) jdInput.value = "";
    }

  

function loadCityFromStorage() {
    try {
        const pathParts = window.location.pathname.split("/").filter(Boolean);
        const isRootHome = pathParts.length === 0;

        // Home page par purani location/session nahi dikhani
        if (isRootHome) {
            clearCity();
            return false;
        }

        // Search/listing page par session wali location hi dikhani
        if (sessionSelectedLocation) {
            setCity(sessionSelectedLocation, false);
            return true;
        }

    } catch (e) {}

    clearCity();
    return false;
}
const isListingDetailsPage = @json(request()->routeIs('listings.details'));

if (isListingDetailsPage) {
    const listingCity = @json($listing->city ?? $listing->location->name ?? '');

    if (listingCity) {
        setCity(listingCity, false);
    }
} else {
    loadCityFromStorage();
    sbAutoDetectCity();
}

/**
 * Fills the location box by itself when the visitor has already granted
 * location access — until now nothing detected on its own, the city only
 * appeared after someone tapped "Detect my location".
 *
 * It never asks for permission on load. A prompt nobody asked for is
 * usually dismissed, and Chrome on Android blocks repeat prompts after
 * that, which would break the button as well. So it only acts when the
 * browser already holds a granted permission.
 */
async function sbAutoDetectCity() {
    // Something was picked for this visit already — leave it alone.
    if (sessionSelectedLocation) return;
    if (!navigator.geolocation) return;

    // A city found earlier today shows at once, with no wait for GPS.
    try {
        const cached = localStorage.getItem("jf_detected_city");
        const when   = parseInt(localStorage.getItem("jf_detected_at") || "0", 10);

        // Anyone who detected before the timestamp was added has a city but no
        // date against it. Trust it once and stamp it, rather than ignoring it.
        if (cached && !when) {
            localStorage.setItem("jf_detected_at", String(Date.now()));
            setCity(cached, false);
        } else if (cached && (Date.now() - when) < 12 * 60 * 60 * 1000) {
            setCity(cached, false);
        }
    } catch (e) {}

    let granted = false;

    try {
        if (navigator.permissions && navigator.permissions.query) {
            const status = await navigator.permissions.query({ name: "geolocation" });
            granted = status.state === "granted";
        }
    } catch (e) {
        // Older browsers have no Permissions API; stay quiet rather than prompt.
    }

    if (!granted) return;

    navigator.geolocation.getCurrentPosition(async function (pos) {
        const city = await sbCityFromCoords(pos.coords.latitude, pos.coords.longitude);

        if (city) {
            setCity(city, true, pos.coords.latitude, pos.coords.longitude);
        }
    }, function () {
        // Nothing to say on a silent attempt.
    }, {
        enableHighAccuracy: false,
        // Without a timeout this can hang for ever on a phone indoors.
        timeout: 10000,
        maximumAge: 10 * 60 * 1000,
    });
}

/** Coordinates to a city name. Returns "" when it cannot be worked out. */
async function sbCityFromCoords(lat, lng) {
    try {
        const res = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=jsonv2&accept-language=en&lat=${lat}&lon=${lng}`
        );

        if (!res.ok) return "";

        const data = await res.json();
        const a = data.address || {};

        return window.normalizeEnglishLocationName(
            a.city || a.town || a.village || a.state_district || a.county || a.district || a.state || ""
        );
    } catch (e) {
        return "";
    }
}

   
   
    // ✅ CLOSE DROPDOWN
    function closeDropdown(dropdown) {
        if (!dropdown) return;
        dropdown.classList.remove("show");
        dropdown.innerHTML = "";
    }

    function openDropdown(dropdown) {
        if (!dropdown) return;
        dropdown.classList.add("show");
    }

    function setDropdownPosition(input, dropdown) {
        if (!input || !dropdown) return;
        const rect = input.getBoundingClientRect();
        dropdown.style.position = "fixed";
        dropdown.style.top = rect.bottom + "px";
        dropdown.style.left = rect.left + "px";
        dropdown.style.width = rect.width + "px";
        dropdown.style.zIndex = "999999";
    }

    // ✅ RECENT LOCATIONS
    function getRecentLocations(){
        return JSON.parse(localStorage.getItem("service_bazzar_recent_locations") || "[]");
    }

    function saveRecentLocation(location){
        let recent = getRecentLocations();
        recent = recent.filter(item => item.value !== location.value);
        recent.unshift(location);
        recent = recent.slice(0, 5);
        localStorage.setItem("service_bazzar_recent_locations", JSON.stringify(recent));
    }

    // ✅ RENDER JD LOCATION LIST
    function renderJdLocationList(list, dropdown) {
        if (!dropdown) return;

        const searchValue = jdInput ? jdInput.value.trim() : "";
        dropdown.innerHTML = "";

        if (searchValue === "") {
            dropdown.innerHTML = `
                <a href="javascript:void(0)"
                   class="dropdown-item smww jd-detect-location-item"
                   data-detect="1">
                    <i class="las la-crosshairs"></i>
                    Detect Location
                    <span class="jd-detect-spinner" style="display:none; margin-left:8px;">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                </a>
            `;
        }

        const recentLocations = getRecentLocations();
        if (searchValue === "" && recentLocations.length > 0) {
            dropdown.innerHTML += `
                <div class="jd-location-title">
                    <span>Recent Searches</span>
                    <span class="jd-clear-recent">Clear All</span>
                </div>
            `;
            recentLocations.forEach(function(item){
                dropdown.innerHTML += `
                    <a href="javascript:void(0)"
                       class="dropdown-item jd-recent-item"
                       data-value="${item.value}">
                        <i class="las la-map-marker"></i>
                        ${item.value}
                    </a>
                `;
            });
        }

        if (Array.isArray(list) && list.length > 0) {
            const showHeading = searchValue === "";
            if (showHeading) {
                dropdown.innerHTML += `<div class="jd-location-title">Trending Areas</div>`;
            }
            let usedLocations = [];
            list.forEach(function (item) {
                let name = "";
                if (typeof item === "string") {
                    name = item;
                } else if (item && typeof item === "object") {
                    name = item.name || item.location_name || item.city || item.area || item.address || item.display_name || "";
                }
                name = String(name).trim();
                if (!name || name === "[object Object]") return;
                let parentName = item.parent || "";
                let finalName = parentName ? name + ", " + parentName : name;
                let uniqueKey = finalName.toLowerCase();
                if (usedLocations.includes(uniqueKey)) return;
                usedLocations.push(uniqueKey);
                dropdown.innerHTML += `
                    <a href="javascript:void(0)"
                       class="dropdown-item"
                       data-value="${finalName}"
                       data-location-id="${item.location_id || item.id || item.locationId || ''}"
                       data-lat="${item.latitude || item.lat || ''}"
                       data-lng="${item.longitude || item.lng || ''}">
                        <i class="las la-map-marker"></i>
                        ${finalName}
                    </a>
                `;
            });
        }
        openDropdown(dropdown);
    }

    document.addEventListener("mousedown", function(e){
        if(e.target.classList.contains("jd-clear-recent")){
            e.preventDefault();
            e.stopPropagation();
            localStorage.removeItem("service_bazzar_recent_locations");
            const dropdown = document.getElementById("jdMobileLocationSuggestions");
            if(dropdown){
                dropdown.querySelectorAll(".jd-recent-item").forEach(el => el.remove());
                const recentTitle = e.target.closest(".jd-location-title");
                if(recentTitle) recentTitle.remove();
                dropdown.classList.add("show");
            }
            return false;
        }
    }, true);

    function getCurrentBaseCity(){
        let city = document.getElementById("jdMobileCityText")?.innerText.trim() ||
            localStorage.getItem("jf_user_city") ||
            localStorage.getItem("jf_city") ||
            localStorage.getItem("jf_detected_city") ||
            "";
        city = window.normalizeEnglishLocationName(city);
        if(city.includes(",")){
            city = city.split(",").pop().trim();
        }
        return city;
    }

    function fetchLocations(query, dropdown) {
        const baseCity = getCurrentBaseCity();
        const finalQuery = query && query.trim() !== "" ? query.trim() : baseCity;

        // A phone on mobile data drops a request now and then. This used to
        // close the whole list on any failure, which left the panel open with
        // nothing in it — not even "Detect Location" — and no way forward.
        const giveUp = setTimeout(function () { controller.abort(); }, 8000);
        const controller = new AbortController();

        fetch(`{{ route('nearby.location.suggestions') }}?q=${encodeURIComponent(finalQuery)}&base_city=${encodeURIComponent(baseCity)}&_=${Date.now()}`, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            },
            signal: controller.signal
        })
        .then(function (res) {
            if (!res.ok) throw new Error("HTTP " + res.status);
            return res.json();
        })
        .then(function (data) {
            clearTimeout(giveUp);
            renderJdLocationList(Array.isArray(data) ? data : [], dropdown);
        })
        .catch(function (err) {
            clearTimeout(giveUp);
            console.log(err);
            // Fall back to what we can build without the server: Detect
            // Location, and whatever the visitor searched for before.
            renderJdLocationList([], dropdown);
            dropdown.classList.add("show");
        });
    }

  function attachLocationInput(input, dropdown) {
    if (!input || !dropdown) return;

    let timer;

    function showDefaultList() {
        renderJdLocationList([], dropdown);
        dropdown.classList.add("show");

        setTimeout(function () {
            fetchLocations("", dropdown);
        }, 100);
    }

    input.addEventListener("input", function () {
        clearTimeout(timer);

        const value = this.value.trim();
        this.dataset.selectedLocation = "0";

        if (value === "") {
            showDefaultList();
            return;
        }

        timer = setTimeout(function () {
            fetchLocations(value, dropdown);
        }, 300);
    });

    input.addEventListener("focus", function () {
        const value = input.value.trim();

        if (value === "") {
            showDefaultList();
            return;
        }

        fetchLocations(value, dropdown);
    });
}
    attachLocationInput(jdInput, jdDropdown);

    // ✅ DETECT CURRENT LOCATION
    async function detectCurrentLocation(detectItem = null) {
        const spinner = detectItem ? detectItem.querySelector(".jd-detect-spinner") : null;
        const showDetectPopup = function(message, blocked = false) {
            if (typeof window.showLocationPermissionPopup === "function") {
                window.showLocationPermissionPopup({
                    message: message,
                    blocked: blocked,
                    onRetry: function () {
                        detectCurrentLocation(detectItem);
                    }
                });
            }
        };
        if (!navigator.geolocation) {
            if (spinner) spinner.style.display = "none";
            showDetectPopup("This browser does not support location detection.");
            return;
        }
        const activeInput = document.activeElement === jdInput ? jdInput : document.activeElement === homeInput ? homeInput : headerInput;
        navigator.geolocation.getCurrentPosition(async function (pos) {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            try {
                const city = await sbCityFromCoords(lat, lng);
                if (spinner) spinner.style.display = "none";
                if (!city) {
                    showDetectPopup("Could not work out your city. Please pick it from the list.");
                }
                if (city) {
    setCity(city, true, lat, lng);

    const isHomePage = @json(request()->routeIs('home'));

if (isHomePage && isMobile()) {
    document.body.classList.remove("show-yt-location");
    document.body.classList.add("mobile-yt-search-open");

    document.documentElement.style.removeProperty("overflow");
    document.body.style.removeProperty("overflow");

    closeDropdown(jdDropdown);

    if (jdLocationBox) jdLocationBox.classList.remove("show");
    if (jdStrip) jdStrip.classList.remove("show");
    if (jdCityBtn) jdCityBtn.style.display = "flex";

    const ytSearchBox = document.getElementById("youtubeMobileSearch");
    if (ytSearchBox) ytSearchBox.style.removeProperty("display");

    const ytInput = document.getElementById("ytMobileSearchInput");
    setTimeout(function () {
        if (ytInput) {
            ytInput.value = "";
            ytInput.focus();
            ytInput.click();
        }
    }, 150);

    return;
}

    const mobileCatInput = document.getElementById("ytMobileSearchInput");

    if (mobileCatInput && mobileCatInput.value.trim() !== "") {
        const citySlug = slugify(city);
        let catSlug  = slugify(mobileCatInput.value.trim());

        // Still the category this page is on: drop its own place ("...-in-south-delhi").
        if (catSlug === @json($sbCategoryRawSlug ?? '') && @json($sbCategorySlug ?? '')) catSlug = @json($sbCategorySlug ?? '');

        if (citySlug && catSlug) {
            if (!sbKnownCity(city)) { sbAskForRealCity(); return; }
            window.location.href = "/" + citySlug + "/" + catSlug;
            return;
        }
    }
} else if (activeInput) {
                    activeInput.value = "";
                }
            } catch (e) {
                console.error(e);
                if (activeInput) activeInput.value = "";
            }
            closeDropdown(homeDropdown);
            closeDropdown(headerDropdown);
            closeDropdown(jdDropdown);
        }, function (error) {
            if (spinner) spinner.style.display = "none";
            showDetectPopup(
                error && error.code === error.TIMEOUT
                    ? "Could not get a location fix. Please try again."
                    : "Please allow location access to detect your city.",
                error && error.code === error.PERMISSION_DENIED
            );
            if (activeInput) activeInput.value = "";
        }, {
            enableHighAccuracy: false,
            // The default is no timeout at all, which leaves the spinner
            // turning for ever when a phone cannot get a fix indoors.
            timeout: 15000,
            maximumAge: 5 * 60 * 1000,
        });
    }


    const ytInput = document.getElementById("ytMobileSearchInput");

if (ytInput) {

    ytInput.addEventListener("focus", function () {
        setTimeout(() => {
            this.scrollLeft = 0;
        }, 50);
    });

    ytInput.addEventListener("input", function () {
        this.scrollLeft = 0;
    });

}

    // ✅ MOUSE DOWN EVENTS
    document.addEventListener("mousedown", function (e) {
        const item = e.target.closest("#locationSuggestions .dropdown-item, #jdMobileLocationSuggestions .dropdown-item");
        if (!item) return;
        e.preventDefault();
        if (item.dataset.detect) {
            const spinner = item.querySelector(".jd-detect-spinner");
            if (spinner) spinner.style.display = "inline-block";
            detectCurrentLocation(item);
            return;
        }
       const value = cleanCity(item.dataset.value || item.textContent.trim());
if (!value) return;

setCity(value, true);

const isListingDetailsPage = @json(request()->routeIs('listings.details'));

if (isListingDetailsPage) {

    document.body.classList.remove("show-yt-location");
    document.body.classList.add("mobile-yt-search-open");

    document.documentElement.style.removeProperty("overflow");
    document.body.style.removeProperty("overflow");

    closeDropdown(jdDropdown);

    if (jdLocationBox) jdLocationBox.classList.remove("show");
    if (jdStrip) jdStrip.classList.remove("show");
    if (jdCityBtn) jdCityBtn.style.display = "flex";

    const ytSearchBox = document.getElementById("youtubeMobileSearch");
    if (ytSearchBox) ytSearchBox.style.removeProperty("display");

    const ytInput = document.getElementById("ytMobileSearchInput");
    setTimeout(function () {
        if (ytInput) {
            ytInput.focus();
            ytInput.click();
        }
    }, 150);

    return;
}
        const locationId = item.dataset.locationId || item.getAttribute("data-location-id") || "";
        if (jdInput) { jdInput.dataset.locationId = locationId; }
        const headerLocationIdInput = document.getElementById("headerSearchLocationId");
        if (headerLocationIdInput) { headerLocationIdInput.value = locationId; }
        // localStorage.setItem("jf_location_id", locationId);
        // localStorage.setItem("jf_selected_location_name", value);
        saveRecentLocation({ value: value, lat: item.dataset.lat || "", lng: item.dataset.lng || "" });
    if (!isListingDetailsPage) {
    const mobileCatInput = document.getElementById("ytMobileSearchInput");

    if (mobileCatInput && mobileCatInput.value.trim() !== "") {

    let locationText = value.trim();
    let city = "";
    let area = "";

    if (locationText.includes(",")) {
        const parts = locationText.split(",");
        area = parts[0].trim();
        city = parts[1].trim();
    } else {
        city = locationText;
    }

    const citySlug = slugify(city);
    let catSlug  = slugify(mobileCatInput.value.trim());

    // Still the category this page is on: drop its own place ("...-in-south-delhi").
    if (catSlug === @json($sbCategoryRawSlug ?? '') && @json($sbCategorySlug ?? '')) catSlug = @json($sbCategorySlug ?? '');

    if (citySlug && catSlug && !sbKnownCity(locationText)) { sbAskForRealCity(); return; }
    if (citySlug && catSlug) {
        let finalUrl = "/" + citySlug + "/" + catSlug;

        if (area !== "") {
            finalUrl += "-in-" + slugify(area);
        }

        window.location.href = finalUrl;
    }
}
}
document.body.classList.remove("show-yt-location");
document.documentElement.style.removeProperty("overflow");
document.body.style.removeProperty("overflow");

const isHomePage = @json(request()->routeIs('home'));

// Already on a category's results — reopen the same category in the place
// just picked. Without this the label changed but the listings underneath
// stayed on the old city, and nothing told the visitor why.
const sbCategorySlug = @json($sbCategorySlug ?? '');

if (!isHomePage && sbCategorySlug) {
    // "Vaishali Nagar, Jaipur" -> area + city; a bare name is the city.
    const picked = value.trim();
    const bits = picked.includes(",") ? picked.split(",") : [];
    const pickedArea = bits.length ? bits[0].trim() : "";
    const pickedCity = bits.length ? bits[1].trim() : picked;

    const target = sbCategoryUrl(sbCategorySlug, { city: pickedCity, area: pickedArea });

    if (target && target !== window.location.origin + window.location.pathname) {
        window.location.href = target;
        return;
    }
}

const ytSearchBox = document.getElementById("youtubeMobileSearch");
if (ytSearchBox) {
    ytSearchBox.style.removeProperty("display");
}

if (isHomePage) {
    // The home page opens its search itself; keep that behaviour on mobile.
    if (isMobile()) {
        document.body.classList.add("mobile-yt-search-open");

        const ytInput = document.getElementById("ytMobileSearchInput");

        setTimeout(function () {
            if (ytInput) {
                ytInput.focus();
                ytInput.click();
            }
        }, 150);
    }
} else {
    // Every other page shows the search box by default (see the page-load
    // rule above). Removing the class here was what made the category search
    // vanish as soon as the visitor changed location.
    document.body.classList.add("mobile-yt-search-open");
}

const jdStripNow = document.getElementById("jdMobileLocationStrip");
if(jdStripNow) jdStripNow.classList.remove("show");

closeDropdown(homeDropdown);
closeDropdown(headerDropdown);
closeDropdown(jdDropdown);

if (jdLocationBox) jdLocationBox.classList.remove("show");
if (jdCityBtn) jdCityBtn.style.display = "flex";
    });

    document.addEventListener("click", function (e) {
        if (!e.target.closest("#homeLocationInput") && !e.target.closest("#homeLocationSuggestions")) {
            closeDropdown(homeDropdown);
        }
        if (!e.target.closest("#locationInput") && !e.target.closest("#locationSuggestions")) {
            closeDropdown(headerDropdown);
        }
       if (
    !e.target.closest("#jdMobileCityBtn") &&
    !e.target.closest("#jdMobileLocationBox") &&
    !e.target.closest("#jdMobileLocationInput") &&
    !e.target.closest("#jdMobileLocationSuggestions")
) {
    closeDropdown(jdDropdown);
}
    });

    window.addEventListener("resize", function () {
        closeDropdown(homeDropdown);
        closeDropdown(headerDropdown);
        closeDropdown(jdDropdown);
    });

    document.querySelectorAll(".select-home-location").forEach(function (card) {
        card.addEventListener("click", function (e) {
            e.preventDefault();
            const locationName = cleanCity(this.dataset.locationName || "");
            if (!locationName) return;
            setCity(locationName, true);
            try {
                localStorage.removeItem("jf_selected_location_name");
                localStorage.removeItem("jf_selected_lat");
                localStorage.removeItem("jf_selected_lng");
            } catch (err) {}
            const bannerSection = document.querySelector(".banner-section");
            if (bannerSection) {
                bannerSection.scrollIntoView({ behavior: "smooth", block: "start" });
            }
        });
    });

    // ✅ HOME CATEGORY SEARCH
    const homeCategoryInput = document.getElementById("homeCategorySearch");
    const homeCategoryDropdown = document.getElementById("homeCategorySuggestions");

    function closeCategoryDropdown(dropdown) {
        if (!dropdown) return;
        dropdown.classList.remove("show");
        dropdown.innerHTML = "";
    }

    function openCategoryDropdown(dropdown) {
        if (!dropdown) return;
        dropdown.classList.add("show");
    }

    function renderCategories(list, input, dropdown) {
        if (!input || !dropdown) return;
        dropdown.innerHTML = "";
        if (!list || list.length === 0) {
            dropdown.innerHTML = `<li role="option"><span class="dropdown-item" style="color:#888; cursor:default;">Not found</span></li>`;
            setDropdownPosition(input, dropdown);
            openCategoryDropdown(dropdown);
            return;
        }
        list.forEach(function (item) {
            const li = document.createElement("li");
            const a = document.createElement("a");
            a.href = "javascript:void(0)";
            a.className = "dropdown-item";
            a.dataset.value = item.name;
            a.dataset.url = item.url || "";
            a.innerHTML = item.name;
            li.appendChild(a);
            dropdown.appendChild(li);
        });
        setDropdownPosition(input, dropdown);
        openCategoryDropdown(dropdown);
    }

    function fetchCategorySuggestions(search, input, dropdown) {
        const currentValue = search;
        const hasTrailingSpace = /\s$/.test(search || "");
        let locationId = "";
        const homeLocationId = document.getElementById("homeSearchLocationId");
        const headerLocationId = document.getElementById("headerSearchLocationId");
        if (homeLocationId && homeLocationId.value) locationId = homeLocationId.value;
        else if (headerLocationId && headerLocationId.value) locationId = headerLocationId.value;
        fetch(`{{ route('category.suggestions') }}?q=${encodeURIComponent(search || "")}&location_id=${encodeURIComponent(locationId)}&exact=${hasTrailingSpace ? 1 : 0}&_=${Date.now()}`, {
            method: "GET",
            headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (input.value !== currentValue) return;
            renderCategories(data, input, dropdown);
        })
        .catch(function (err) {
            console.error("Category suggestion error:", err);
            closeCategoryDropdown(dropdown);
        });
    }

    function attachCategoryInput(input, dropdown) {
        if (!input || !dropdown) return;
        let timer;
        input.addEventListener("input", function () {
            const value = this.value;
            clearTimeout(timer);
            if (value.trim().length < 1) {
                closeCategoryDropdown(dropdown);
                return;
            }
            timer = setTimeout(function () {
                fetchCategorySuggestions(value, input, dropdown);
            }, 250);
        });
        input.addEventListener("focus", function () {
            setDropdownPosition(input, dropdown);
        });
        dropdown.addEventListener("mousedown", function (e) { e.preventDefault(); });
        dropdown.addEventListener("click", function (e) {
            const item = e.target.closest(".dropdown-item");
            if (!item || !item.dataset.url) return;
            e.preventDefault();
            input.value = item.dataset.value || "";
            closeCategoryDropdown(dropdown);
            const baseUrl = item.dataset.url;
            const categorySlug = baseUrl.split("/").filter(Boolean).pop();
            let selectedLocation = "";
            const headerInputLoc = document.getElementById("locationInput");
            const homeInputLoc = document.getElementById("homeLocationInput");
            if (headerInputLoc && headerInputLoc.value.trim() !== "") {
                selectedLocation = headerInputLoc.value.trim();
            } else if (homeInputLoc && homeInputLoc.value.trim() !== "") {
                selectedLocation = homeInputLoc.value.trim();
            } else {
                selectedLocation = localStorage.getItem("jf_detected_city") || "";
            }
            if (selectedLocation) {
                let locationText = selectedLocation.trim();
                let city = "";
                let area = "";
                if (locationText.includes(",")) {
                    const parts = locationText.split(",");
                    area = parts[0].trim();
                    city = parts[1].trim();
                } else {
                    city = locationText.trim();
                    area = "";
                }
              window.location.href =
    `{{ route('listings.search') }}?categorysearch=${encodeURIComponent(input.value.trim())}&location_id=${encodeURIComponent(localStorage.getItem("jf_location_id") || "")}&location_name=${encodeURIComponent(selectedLocation)}`;
            } else {
                window.location.href = baseUrl;
            }
        });
    }

    document.addEventListener("click", function (e) {
        if (!e.target.closest("#CategorySearch") && !e.target.closest("#categorySuggestions")) {
            closeCategoryDropdown(categoryDropdown);
        }
        if (!e.target.closest("#homeCategorySearch") && !e.target.closest("#homeCategorySuggestions")) {
            closeCategoryDropdown(homeCategoryDropdown);
        }
    });

    // The homepage HTML is served from a shared full-page cache, so the
    // city-prefixed category URLs cannot be baked server-side — one visitor's
    // city would be cached and handed to everyone (that's how a Delhi visitor
    // was seeing /jaipur/... links). The location the visitor actually picked
    // lives client-side (the input value / localStorage), so the links are
    // resolved here, per visitor, instead.

    // Resolve the visitor's chosen location into {city, area}, or null when we
    // genuinely don't know it yet. Same precedence the location input uses.
    function sbResolveCityArea() {
        let selectedLocation = "";
        if (homeInput && homeInput.dataset.selectedLocation === "1" && homeInput.value.trim() !== "") {
            selectedLocation = homeInput.value.trim();
        } else if (headerInput && headerInput.dataset.selectedLocation === "1" && headerInput.value.trim() !== "") {
            selectedLocation = headerInput.value.trim();
        } else {
            selectedLocation = localStorage.getItem("jf_detected_city") || "";
        }
        selectedLocation = (selectedLocation || "").trim();
        if (!selectedLocation || !sbKnownCity(selectedLocation)) return null;

        let city = selectedLocation, area = "";
        if (selectedLocation.includes(",")) {
            const parts = selectedLocation.split(",");
            area = parts[0].trim();
            city = parts[1].trim();
        }
        return { city: city, area: area };
    }

    function sbCategoryUrl(categorySlug, loc) {
        const citySlug = slugify(loc.city);
        if (!citySlug || !categorySlug) return null;
        let url = `${window.location.origin}/${citySlug}/${categorySlug}`;
        if (loc.area !== "") url += `-in-${slugify(loc.area)}`;
        return url;
    }

    // Rewrite every category link's href to the visitor's city, so the
    // hover/status-bar URL, middle-click and normal click all match the
    // location shown in the input — not just a JS-intercepted left click.
    // Exposed on window so setCity() can re-run it the moment the visitor
    // changes location, with no page refresh needed.
    window.sbRefreshCategoryLinks = function () {
        const loc = sbResolveCityArea();
        if (!loc) return; // unknown city: leave the server's generic href as-is
        document.querySelectorAll(".dynamic-category-link").forEach(function (link) {
            const categorySlug = link.dataset.slug || "";
            const url = sbCategoryUrl(categorySlug, loc);
            if (url) link.setAttribute("href", url);
        });
    };

    window.sbRefreshCategoryLinks();

    // Update a single link's href from the CURRENT location, right now.
    function sbUpdateOneCategoryLink(link) {
        if (!link) return;
        const categorySlug = link.dataset.slug || "";
        if (!categorySlug) return;
        const loc = sbResolveCityArea();
        if (!loc) return; // unknown city: leave the server's generic href
        const url = sbCategoryUrl(categorySlug, loc);
        if (url) link.setAttribute("href", url);
    }

    // The real fix for "hover shows the old city until I refresh":
    // rewrite the link's href the instant the cursor (or keyboard focus, or a
    // touch) reaches it, reading the live location at that moment. This does
    // not depend on catching every place the location can change — whatever
    // the input says when you point at the link is what the status-bar URL
    // will show. Delegated on document so it also covers the banner carousel
    // and any links added after load.
    ["mouseover", "focusin", "touchstart"].forEach(function (evt) {
        document.addEventListener(evt, function (e) {
            const link = e.target.closest ? e.target.closest(".dynamic-category-link") : null;
            if (link) sbUpdateOneCategoryLink(link);
        }, true);
    });

    // Keep a click handler too, as a final safety net (e.g. a link clicked
    // without ever firing a hover, as on some touch flows).
    document.querySelectorAll(".dynamic-category-link").forEach(function (link) {
        link.addEventListener("click", function (e) {
            const categorySlug = this.dataset.slug || "";
            if (!categorySlug) return;
            const loc = sbResolveCityArea();
            if (!loc) return; // fall through to the anchor's own href
            const finalUrl = sbCategoryUrl(categorySlug, loc);
            if (!finalUrl) return;
            e.preventDefault();
            window.location.href = finalUrl;
        });
    });

    if (categoryInput && jdStrip) {
        categoryInput.addEventListener("focus", function () {
            if (!isMobile()) return;
            jdStrip.classList.add("show");
            if (jdLocationBox) jdLocationBox.classList.remove("show");
            if (jdCityBtn) jdCityBtn.style.display = "flex";
        });
        homeCategoryInput.addEventListener("focus", function () {
            if (!isMobile()) return;
            jdStrip.classList.add("show");
            jdLocationBox?.classList.remove("show");
            if (jdCityBtn) jdCityBtn.style.display = "flex";
        });
        homeCategoryInput.addEventListener("click", function () {
            if (!isMobile()) return;
            jdStrip.classList.add("show");
            jdLocationBox?.classList.remove("show");
            if (jdCityBtn) jdCityBtn.style.display = "flex";
        });
        categoryInput.addEventListener("click", function () {
            if (!isMobile()) return;
            jdStrip.classList.add("show");
            if (jdLocationBox) jdLocationBox.classList.remove("show");
            if (jdCityBtn) jdCityBtn.style.display = "flex";
        });
    }
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const mobileBottomAction = document.getElementById("mobileBottomAction");
    if (!mobileBottomAction) return;
    window.addEventListener("scroll", function () {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        if (window.innerWidth <= 991) {
            if (scrollTop > 50) {
                mobileBottomAction.classList.add("hide-on-scroll");
            } else {
                mobileBottomAction.classList.remove("hide-on-scroll");
            }
        }
    });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const cityBtn = document.getElementById("jdMobileCityBtn");
    if (cityBtn) cityBtn.classList.remove("detecting");
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("CategorySearch");
    if (!input) return;
    const placeholders = [
        "Search for Packers and Movers",
        "Search for Electricians",
        "Search for Plumbers",
        "Search for Restaurants",
        "Search for Hotels",
        "Search for AC Repair",
        "Search for Bike Repair",
        "Search for Taxi Services"
    ];
    let index = 0;
    function changePlaceholder() {
        input.setAttribute("placeholder", placeholders[index]);
        index++;
        if (index >= placeholders.length) index = 0;
    }
    changePlaceholder();
    setInterval(changePlaceholder, 2000);
});
function sbJdhShare() {
    var data = { title: document.title, url: window.location.href.split('#')[0] };
    if (navigator.share) { navigator.share(data).catch(function () {}); return; }
    if (navigator.clipboard) {
        navigator.clipboard.writeText(data.url).then(function () { alert('Link copied'); });
    }
}

function goBackPage() {
    if (document.referrer && document.referrer !== "") {
        history.back();
    } else {
        window.location.href = "{{ route('home') }}";
    }
}
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    function initHeaderClear(inputId){
        const input = document.getElementById(inputId);
        if(!input) return;
        const wrapper = input.closest(".search-box, .input_search_box");
        if(!wrapper) return;
        let clearBtn = wrapper.querySelector(".header-category-clear-btn");
        if(!clearBtn){
            clearBtn = document.createElement("span");
            clearBtn.className = "header-category-clear-btn";
            clearBtn.innerHTML = '<i class="fas fa-times"></i>';
            wrapper.appendChild(clearBtn);
        }
        function toggleBtn(){
            if(input.value.trim() !== ""){
                clearBtn.style.display = "block";
            }else{
                clearBtn.style.display = "none";
            }
        }
        input.addEventListener("input", toggleBtn);
        clearBtn.addEventListener("click", function () {
            input.value = "";
            clearBtn.style.display = "none";
            input.focus();
            const dropdown = document.getElementById("categorySuggestions");
            if(dropdown){
                dropdown.classList.remove("show");
                dropdown.innerHTML = "";
            }
        });
        toggleBtn();
    }
    initHeaderClear("CategorySearch");
});

document.addEventListener("DOMContentLoaded", function () {
    const categoryInput = document.getElementById("CategorySearch");
    if (!categoryInput) return;
    categoryInput.addEventListener("keydown", function (e) {
        if (e.key !== "Enter") return;
        e.preventDefault();
        const value = this.value.trim();
        if (!value) return;
        const selectedLocation = document.getElementById("locationInput")?.value.trim() || localStorage.getItem("jf_detected_city") || localStorage.getItem("jf_city") || "";
       fetch(`{{ route('category.suggestions') }}?q=${encodeURIComponent(value)}&_=${Date.now()}`, {
            method: "GET",
            headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" }
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.length > 0 && data[0].url) {
                const categorySlug = data[0].url.split("/").filter(Boolean).pop();
                if (selectedLocation) {
                    let city = "";
                    let area = "";
                    if (selectedLocation.includes(",")) {
                        const parts = selectedLocation.split(",");
                        area = parts[0].trim();
                        city = parts[1].trim();
                    } else {
                        city = selectedLocation.trim();
                    }
                    let finalUrl = `${window.location.origin}/${slugify(city)}/${categorySlug}`;
                    if (area !== "") finalUrl += `-in-${slugify(area)}`;
                    window.location.href = finalUrl;
                    return;
                }
                window.location.href = data[0].url;
                return;
            }
            window.location.href = `{{ route('listings.search') }}?categorysearch=${encodeURIComponent(value)}&location_name=${encodeURIComponent(selectedLocation)}`;
        });
    });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchBtn = document.getElementById("mobileHeaderSearchBtn");
    const backBtn = document.getElementById("ytSearchBackBtn");

    if(backBtn){
    backBtn.addEventListener("click", function(e){
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        document.body.classList.remove("show-yt-location");

        document.documentElement.style.removeProperty("overflow");
        document.body.style.removeProperty("overflow");

        jdStrip?.classList.remove("show");
        closeJdLocationBox();
        closeLocationSuggestions();
        closeCategorySuggestions();

        if (isHomePage) {
            document.body.classList.remove("mobile-yt-search-open");
        } else {
            document.body.classList.add("mobile-yt-search-open");
        }

    }, true);
}
    const input = document.getElementById("ytMobileSearchInput");
    const isHomePage = @json(request()->routeIs('home'));
    if (window.innerWidth <= 991) {
        if (isHomePage) {
            document.body.classList.remove("mobile-yt-search-open");
        } else {
            document.body.classList.add("mobile-yt-search-open");
        }
    }
    const suggestions = document.getElementById("ytMobileCategorySuggestions");
    let ytAllowBlankSuggestion = false;
    let ytLastSearchValue = "";

function showRecentTrendingInYtSearch(){
    if (!input) return;

    ytLastSearchValue = input.value;

    ytAllowBlankSuggestion = true;
    fetchMobileCategorySuggestions("");
}

    let ytSelectedCategoryUrl = "";
    
    const micBtn = document.getElementById("ytMicBtn");
    const jdStrip = document.getElementById("jdMobileLocationStrip");
    const jdCityBtn = document.getElementById("jdMobileCityBtn");
    const jdLocationBox = document.getElementById("jdMobileLocationBox");
    const jdInput = document.getElementById("jdMobileLocationInput");
    function slugify(text){
        return String(text || "").toLowerCase().trim().replace(/,/g, "").replace(/\s+/g, "-").replace(/[^a-z0-9-]/g, "").replace(/-+/g, "-").replace(/^-+|-+$/g, "");
    }
    function closeLocationSuggestions(){
        const locSug = document.getElementById("jdMobileLocationSuggestions");
        if(locSug){
            locSug.classList.remove("show");
            locSug.innerHTML = "";
        }
    }
    function closeCategorySuggestions(){
        if(suggestions){
            suggestions.classList.remove("show");
            suggestions.innerHTML = "";
        }
    }
    function closeJdLocationBox(){
        if(jdLocationBox) jdLocationBox.classList.remove("show");
        if(jdCityBtn) jdCityBtn.style.display = "flex";
        closeLocationSuggestions();
    }
function getRecentMobileCategories(){
    const raw = JSON.parse(localStorage.getItem("service_bazzar_recent_categories") || "[]");
    
    return raw
        .filter(function(item){
            let type = "category";
            
            if (typeof item === "string") {
                return false; // Purane string format ko skip karo
            }
            
            if (item && typeof item === "object") {
                type = item.type || "category";
            }
            
            type = String(type).toLowerCase(); // ✅ YEH IMPORTANT HAI
            
            // ✅ SIRF CATEGORY TYPE WALE ITEMS LO
            return type === "category" || type === "subcategory";
        })
        .map(function(item){
            return item.name || item.value || "";
        })
        .filter(Boolean);
}
function saveRecentMobileCategory(item){
    let name = "";
    let type = "category";

    if (typeof item === "string") {
        name = item;
        type = "category";
    } else if (item && typeof item === "object") {
        name = item.name || item.value || "";
        type = item.type || "category";
    }

    name = String(name || "").trim();
    type = String(type || "category").toLowerCase(); // ✅ YEH IMPORTANT HAI

    if (!name) return;

    // ✅ LISTING ITEMS KO SAVE MAT KARO
    if (type === "listing") {
        console.log("Skipping listing:", name);
        return;
    }

    // ✅ SIRF CATEGORY TYPE SAVE KARO
    if (type !== "category" && type !== "subcategory") {
        console.log("Skipping unknown type:", type, name);
        return;
    }

    let recent = JSON.parse(localStorage.getItem("service_bazzar_recent_categories") || "[]");

    recent = recent.filter(function(oldItem){
        const oldName = typeof oldItem === "string" ? oldItem : (oldItem.name || oldItem.value || "");
        return oldName.toLowerCase() !== name.toLowerCase();
    });

    recent.unshift({
        name: name,
        type: type
    });

    recent = recent.slice(0, 5);

    localStorage.setItem(
        "service_bazzar_recent_categories",
        JSON.stringify(recent)
    );
}
function renderSuggestions(data) {
    if (!suggestions) return;

    suggestions.innerHTML = "";

 const searchValue = (input?.value || "").trim();
const showRecentTrending = ytAllowBlankSuggestion === true;

if (searchValue.length === 0 && ytAllowBlankSuggestion === false) {
    suggestions.innerHTML = "";
    suggestions.classList.remove("show");
    return;
}
    // ===== RECENT SEARCHES =====
    if (showRecentTrending) {
        const recent = getRecentMobileCategories();

        if (recent.length > 0) {
            suggestions.innerHTML += `
                <li>
                    <div class="yt-section-header">
                        Recent Searches
                        <span class="yt-clear-btn" id="ytClearRecentCat">Clear All</span>
                    </div>
                </li>
            `;

            recent.forEach(function (name) {
                if (
    name.toLowerCase().includes("dealer") ||
    name.toLowerCase().includes("showroom") ||
    name.toLowerCase().includes("consultant") ||
    name.toLowerCase().includes("service in") ||
    name.toLowerCase().includes("near me")
) {
    return;
}

                suggestions.innerHTML += `
                    <li>
                        <a href="javascript:void(0)"
                           class="yt-recent-item"
                           data-name="${name}"
                           data-url="">
                            <i class="las la-history"></i>
                            ${name}
                        </a>
                    </li>
                `;
            });
        }
    }

    // ===== TRENDING SEARCHES =====
    if (showRecentTrending && data && data.length > 0) {
        suggestions.innerHTML += `
            <li>
                <div class="yt-section-header">Trending Searches</div>
            </li>
        `;

        data.forEach(function (item) {
            const isListing = item.type === "listing" || item.rating || item.reviews || item.area;

            if (isListing) {
                // ===== LISTING ITEM (2nd image style) =====
                const rating = item.rating || "";
                const reviews = item.reviews || item.review || "";
                const area = item.area || "";
                const category = item.category || "";

                let metaHtml = "";

                if (rating) {
                    metaHtml += `<span class="yt-rating">${rating} ★</span>`;
                }

                if (reviews) {
                    metaHtml += ` <span class="yt-review">(${reviews})</span>`;
                }

                if (area) {
                    metaHtml += ` <span class="yt-dot">·</span> <span class="yt-area">${area}</span>`;
                }

                if (category) {
                    metaHtml += ` <span class="yt-dot">·</span> <span class="yt-category-tag">${category}</span>`;
                }

                suggestions.innerHTML += `
                    <li>
                        <a href="${item.url || 'javascript:void(0)'}"
                           class="yt-listing-item"
                           data-name="${item.name}"
                            data-type="listing"
                           data-url="${item.url || ''}">

                            <div class="yt-listing-title">
                                ${item.name}
                            </div>

                            <div class="yt-listing-meta">
                                ${metaHtml}
                            </div>

                        </a>
                    </li>
                `;

            } else {
                // ===== CATEGORY ITEM =====
                suggestions.innerHTML += `
                    <li>
                      <a href="javascript:void(0)"
   class="yt-category-item"
   data-name="${item.name}"
   data-type="category"
   data-url="${item.url || ''}">

                            <img src="/assets/images/ffffjndjs.svg"
                                 alt="Trending indicator"
                                 class="yt-category-icon">

                            <span>${item.name}</span>

                        </a>
                    </li>
                `;
            }
        });
    }

    // ===== TYPING SEARCH (only exact matches) =====
// ===== TYPING SEARCH (only exact matches) =====
if (!showRecentTrending && data && data.length > 0) {
    suggestions.innerHTML = "";

    const rawSearch = input ? input.value : "";
    const searchValue = rawSearch.toLowerCase().replace(/\s+/g, " ").trim();
    const hasTrailingSpace = /\s$/.test(rawSearch);

    data.forEach(function (item) {

        const itemName = String(item.name || "")
            .toLowerCase()
            .replace(/\s+/g, " ")
            .trim();

        if (hasTrailingSpace) {
            if (itemName !== searchValue && !itemName.startsWith(searchValue + " ")) {
                return;
            }
        } else {
            if (searchValue === "car" && itemName.startsWith("career")) {
                return;
            }

            if (searchValue && !itemName.includes(searchValue)) {
                return;
            }
        }

        const isListing = item.type === "listing" || item.rating || item.reviews || item.area;

            if (isListing) {
                const rating = item.rating || "";
                const reviews = item.reviews || item.review || "";
                const area = item.area || "";
                const category = item.category || "";

                let metaHtml = "";

                if (rating) {
                    metaHtml += `<span class="yt-rating">${rating} ★</span>`;
                }

                if (reviews) {
                    metaHtml += ` <span class="yt-review">(${reviews})</span>`;
                }

                if (area) {
                    metaHtml += ` <span class="yt-dot">·</span> <span class="yt-area">${area}</span>`;
                }

                if (category) {
                    metaHtml += ` <span class="yt-dot">·</span> <span class="yt-category-tag">${category}</span>`;
                }

                suggestions.innerHTML += `
                    <li>
                       <a href="${item.url || 'javascript:void(0)'}"
   class="yt-listing-item"
   data-name="${item.name}"
   data-type="listing"
   data-url="${item.url || ''}">

                            <div class="yt-listing-title">
                                ${item.name}
                            </div>

                            <div class="yt-listing-meta">
                                ${metaHtml}
                            </div>

                        </a>
                    </li>
                `;

            } else {
                suggestions.innerHTML += `
                    <li>
                       <a href="javascript:void(0)"
   class="yt-category-item"
   data-name="${item.name}"
   data-type="category"
   data-url="${item.url || ''}">

                            <img src="/assets/images/ffffjndjs.svg"
                                 alt="Trending indicator"
                                 class="yt-category-icon">

                            <span>${item.name}</span>

                        </a>
                    </li>
                `;
            }
        });
    }

    // ===== NO RESULT =====
    if (suggestions.innerHTML.trim() === "") {
        suggestions.innerHTML = `
            <li>
                <span style="display:block;padding:20px 16px;color:#999;font-size:14px;text-align:center;">
                    Not found
                </span>
            </li>
        `;
    }

    // #ytMobileCategorySuggestions' CSS `top` was a hardcoded 95px,
    // assuming the search bar above it is always that tall. On pages
    // where it's actually shorter (~50px), that left a gap between the
    // search bar and the dropdown with the underlying page's own content
    // (e.g. its "Popular X in Y" heading) visible through it. Position it
    // from the search bar's real, current bottom edge instead — same
    // pattern already used for the location dropdown via
    // setDropdownPosition() above.
    const ytSearchBar = document.getElementById("youtubeMobileSearch");
    if (ytSearchBar) {
        suggestions.style.top = ytSearchBar.getBoundingClientRect().bottom + "px";
    }

    suggestions.classList.add("show");
    suggestions.style.display = "";
}
function getMobileSelectedLocationData(){
    const locationText =
        document.getElementById("jdMobileLocationInput")?.value.trim() ||
        document.getElementById("jdMobileCityText")?.innerText.trim() ||
        localStorage.getItem("jf_selected_location_name") ||
        localStorage.getItem("jf_city") ||
        "";

    const locationId =
        document.getElementById("jdMobileLocationInput")?.dataset.locationId ||
        document.getElementById("headerSearchLocationId")?.value ||
        localStorage.getItem("jf_location_id") ||
        "";

    return {
        locationText: locationText,
        locationId: locationId
    };
}

function fetchMobileCategorySuggestions(value){
    const loc = getMobileSelectedLocationData();

    fetch(`{{ route('category.suggestions') }}?q=${encodeURIComponent(value || "")}&location_id=${encodeURIComponent(loc.locationId)}&location_name=${encodeURIComponent(loc.locationText)}&_=${Date.now()}`, {
        method: "GET",
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            "Accept": "application/json"
        }
    })
    .then(res => res.json())
    .then(function(data) {
        console.log("Backend data:", data); // ✅ YE CHECK KARO
        renderSuggestions(data);
    })
    .catch(() => closeCategorySuggestions());
}

   let timer;

if(searchBtn){
    searchBtn.addEventListener("click", function(e){
        e.preventDefault();

        document.body.classList.add("mobile-yt-search-open");
        document.body.classList.remove("show-yt-location");

        if (jdLocationBox) {
            jdLocationBox.classList.remove("show");
        }

        if (jdCityBtn) {
            jdCityBtn.style.display = "flex";
        }

        closeLocationSuggestions();
        closeJdLocationBox();

        setTimeout(function(){

            if(input){
                input.focus();
                input.click();

                setTimeout(function(){
                    input.blur();
                    input.scrollLeft = 0;
                }, 50);
            }

        }, 100);
    });
}


 if(input){

   input.addEventListener("click", function(){
    closeLocationSuggestions();
    closeJdLocationBox();

    document.body.classList.add("show-yt-location");
    document.documentElement.style.overflow = "";
    document.body.style.overflow = "";

    if(jdStrip) jdStrip.classList.add("show");
    if(jdCityBtn) jdCityBtn.style.display = "flex";

    showRecentTrendingInYtSearch();
});

 input.addEventListener("focus", function(){
    showRecentTrendingInYtSearch();
});

 input.addEventListener("input", function(e){
    closeLocationSuggestions();

    this.dataset.selectedSuggestion = "0";
    ytSelectedCategoryUrl = "";

    const value = this.value.trim();
    clearTimeout(timer);

    ytAllowBlankSuggestion = false;

    if(value === ""){
        closeCategorySuggestions();
        return;
    }

    timer = setTimeout(function(){
        fetchMobileCategorySuggestions(value);
    }, 250);
});
}


 
    if(jdInput){
        jdInput.addEventListener("click", function(e){ e.stopPropagation(); closeCategorySuggestions(); });
        jdInput.addEventListener("input", function(){ closeCategorySuggestions(); });
        jdInput.addEventListener("focus", function(){ closeCategorySuggestions(); });
    }

document.addEventListener("pointerdown", function(e){
    const clickedCityBtn = e.target.closest("#jdMobileCityBtn");
    const clickedLocationInput = e.target.closest("#jdMobileLocationInput");
    const clickedLocationSuggestion = e.target.closest("#jdMobileLocationSuggestions");
    const clickedMobileSearch = e.target.closest("#youtubeMobileSearch");
    const clickedLocationBox = e.target.closest("#jdMobileLocationBox"); 
    if (clickedCityBtn || clickedLocationInput || clickedLocationSuggestion || clickedMobileSearch || clickedLocationBox) {
        return;
    }

    document.body.classList.remove("show-yt-location");
    if (jdStrip) jdStrip.classList.remove("show");
    closeJdLocationBox();
    closeCategorySuggestions();
}, true);


    document.addEventListener("click", function(e){

    // Category Clear All
    if(e.target.closest("#ytClearRecentCat")){
        e.preventDefault();
        e.stopPropagation();

        localStorage.removeItem("service_bazzar_recent_categories");

        const input = document.getElementById("ytMobileSearchInput");
        const value = input ? input.value.trim() : "";

       fetch(`{{ route('category.suggestions') }}?q=${encodeURIComponent(value)}&_=${Date.now()}`, {
            method: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            }
        })
        .then(res => res.json())
        .then(data => renderSuggestions(data));

        return;
    }

    // Location Clear All
    if(e.target.closest(".jd-clear-recent")){
        e.preventDefault();
        e.stopPropagation();

        localStorage.removeItem("service_bazzar_recent_locations");

        fetchLocations(getCurrentBaseCity(), jdDropdown);
        return;
    }

    // Single Location Remove
    const removeLoc = e.target.closest(".jd-remove-recent");
    if(removeLoc){
        e.preventDefault();
        e.stopPropagation();

        const value = removeLoc.dataset.value || "";
        let recent = getRecentLocations();
        recent = recent.filter(item => item.value !== value);

        localStorage.setItem("service_bazzar_recent_locations", JSON.stringify(recent));

        fetchLocations(getCurrentBaseCity(), jdDropdown);
        return;
    }

});
   if(suggestions){
    suggestions.addEventListener("mousedown", function(e){
        const item = e.target.closest("a");
        if(!item) return;

        e.preventDefault();

        const url = item.dataset.url || "";
        const name = item.dataset.name || "";
        const type = item.dataset.type || "category";

        if(!name) return;

        if(input) {
            input.value = name;
            input.dataset.selectedSuggestion = "1";
        }

        ytSelectedCategoryUrl = url;

        saveRecentMobileCategory({
            name: name,
            type: type
        });

const categorySlug = url
    ? url.split("/").filter(Boolean).pop()
    : slugify(name);

let currentCity =
    document.getElementById("jdMobileCityText")?.innerText.trim() ||
    document.getElementById("jdMobileLocationInput")?.value.trim() ||
    localStorage.getItem("jf_city") ||
    localStorage.getItem("jf_selected_location_name") ||
    localStorage.getItem("jf_detected_city") ||
    "jaipur";

if (currentCity.includes(",")) {
    currentCity = currentCity.split(",").pop().trim();
}

const citySlug = slugify(currentCity);

window.location.href = `${window.location.origin}/${citySlug}/${categorySlug}`;
    });
}
    if(micBtn){
        micBtn.addEventListener("click", function(){
            const voiceModal = document.getElementById("voiceModal");
            const voiceCloseBtn = document.getElementById("voiceCloseBtn");
            const voiceStatusText = document.getElementById("voiceStatusText");
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if(!SpeechRecognition){
                alert("Voice search is not supported in this browser");
                return;
            }
            const recognition = new SpeechRecognition();
            recognition.lang = "en-IN";
            recognition.interimResults = false;
            recognition.maxAlternatives = 1;
            if(voiceModal) voiceModal.classList.add("show");
            if(voiceStatusText) voiceStatusText.innerText = "Listening.....";
            recognition.start();
            recognition.onresult = function(event){
                const text = event.results[0][0].transcript;
                if(input){
                    input.value = text;
                    input.dispatchEvent(new Event("input"));
                }
                if(voiceStatusText) voiceStatusText.innerText = text;
                setTimeout(function(){ if(voiceModal) voiceModal.classList.remove("show"); },700);
            };
            recognition.onerror = function(){
                if(voiceStatusText) voiceStatusText.innerText = "Please try again";
                setTimeout(function(){ if(voiceModal) voiceModal.classList.remove("show"); },900);
            };
            recognition.onend = function(){
                setTimeout(function(){ if(voiceModal) voiceModal.classList.remove("show"); },800);
            };
            if(voiceCloseBtn){
                voiceCloseBtn.onclick = function(){
                    recognition.stop();
                    if(voiceModal) voiceModal.classList.remove("show");
                };
            }
        });
    }
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const mobileForm = document.querySelector(".yt-search-form");
    const input = document.getElementById("ytMobileSearchInput");

    function makeSlug(text){
        return String(text || "")
            .toLowerCase()
            .trim()
            .replace(/,/g, "")
            .replace(/\s+/g, "-")
            .replace(/[^a-z0-9-]/g, "")
            .replace(/-+/g, "-")
            .replace(/^-+|-+$/g, "");
    }

    function goMobileSearch(){
        const value = input?.value.trim() || "";
        if (!value) return;

        fetch(`{{ route('category.suggestions') }}?q=${encodeURIComponent(value)}&_=${Date.now()}`, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            }
        })
        .then(res => res.json())
        .then(data => {
            let categorySlug = "";

            if (data && data.length > 0 && data[0].url) {
                categorySlug = data[0].url.split("/").filter(Boolean).pop();
            }

            if (!categorySlug) {
                categorySlug = makeSlug(value);
            }

            let selectedLocation =
                document.getElementById("jdMobileLocationInput")?.value.trim() ||
                document.getElementById("jdMobileCityText")?.innerText.trim() ||
                localStorage.getItem("jf_selected_location_name") ||
                localStorage.getItem("jf_city") ||
                "";

            selectedLocation = selectedLocation.trim();

            let city = "";
            let area = "";

            if (selectedLocation.includes(",")) {
                const parts = selectedLocation.split(",");
                area = parts[0].trim();
                city = parts[1].trim();
            } else {
                city = selectedLocation;
            }

            const citySlug = makeSlug(city);

            if (!citySlug || !categorySlug) return;

            let finalUrl = `${window.location.origin}/${citySlug}/${categorySlug}`;

            if (area !== "") {
                finalUrl += `-in-${makeSlug(area)}`;
            }

            window.location.href = finalUrl;
        });
    }

    if (mobileForm) {
        mobileForm.addEventListener("submit", function(e){
            e.preventDefault();
            goMobileSearch();
        });
    }

    document.querySelector(".yt-search-submit")?.addEventListener("click", function(e){
        e.preventDefault();
        goMobileSearch();
    });
});
</script>

<div id="voiceModal" class="voice-modal">
    <div class="voice-box">
        <button type="button" class="voice-close" id="voiceCloseBtn" aria-label="Close">×</button>
        <h3 id="voiceStatusText">Listening.....</h3>
        <div class="voice-loader">
            <span></span><span></span><span></span><span></span><span></span>
        </div>
    </div>
</div>

<style>
.sb-auth-popup{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.45);
    backdrop-filter:blur(3px);
    z-index:999999;
    display:none;
    align-items:center;
    justify-content:center;
    padding:20px;
}
.sb-auth-popup.show{display:flex;}
.sb-auth-box{
    width:380px;
    max-width:100%;
    background:#fff;
    border-radius:12px;
    padding:22px 26px 20px;
    position:relative;
}
.sb-auth-close{
    position:absolute;
    right:16px;
    top:12px;
    border:0;
    background:transparent;
    font-size:26px;
    color:#111;
    line-height:1;
}
.sb-auth-head{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:16px;
}
.sb-auth-head img{
    width:80px;
    max-height:32px;
    object-fit:contain;
    border-right:1px solid #eee;
    padding-right:12px;
}
.sb-auth-head h4{
    margin:0;
    font-size:16px;
    font-weight:700;
    color:#000;
}
.sb-auth-head p{
    margin:2px 0 0;
    font-size:13px;
    color:#000;
    line-height:1.3;
}
.sb-field{
    position:relative;
    margin-bottom:14px;
}
.sb-field label{
    position:absolute;
    top:-8px;
    left:14px;
    background:#fff;
    padding:0 8px;
    font-size:12px;
    color:#000;
}
.sb-field input{
    width:100%;
    height:44px;
    border:1px solid #111;
    border-radius:8px;
    padding:0 14px;
    font-size:14px;
    outline:none;
}
.sb-social-login{
    display:flex;
    flex-direction:row;
    justify-content:center;
    gap:16px;
    margin:14px 0 2px;
}
.sb-social-btn{
    display:flex;
    align-items:center;
    justify-content:center;
    flex:none;
    width:48px;
    height:48px;
    border-radius:50%;
    border:1px solid #dde1ea;
    background:#fff;
    text-decoration:none;
    transition:background .15s, box-shadow .15s;
}
.sb-social-btn svg{
    width:22px;
    height:22px;
}
.sb-social-btn:hover{
    background:#f6f8fc;
    box-shadow:0 2px 8px rgba(0,0,0,.08);
}
.sb-social-divider{
    display:flex;
    align-items:center;
    text-align:center;
    color:#999;
    font-size:12px;
    margin:12px 0;
}
.sb-social-divider::before,
.sb-social-divider::after{
    content:"";
    flex:1;
    height:1px;
    background:#e3e5e8;
}
.sb-social-divider span{
    padding:0 10px;
}
.sb-terms{
    text-align:center;
    font-size:12px;
    color:#555;
    margin:4px 0 14px;
}
.sb-terms input{
    width:18px;
    height:18px;
    vertical-align:middle;
    accent-color:#087dde;
}
.sb-terms a{
    color:#444;
    text-decoration:underline;
}
.sb-btn{
    width:100%;
    height:42px;
    border:0;
    border-radius:7px;
    background:#087dde;
    color:#fff;
    font-size:16px;
    font-weight:700;
}
.sb-btn:disabled{
    background:#777;
}
.sb-later{
    display:block;
    text-align:center;
    margin-top:16px;
    color:#000;
    font-size:13px;
    /* It is a <button> now, so undo the chrome a button brings with it. */
    width:100%;
    background:none;
    border:0;
    padding:0;
    cursor:pointer;
    font-family:inherit;
}
.sb-error{
    color:red;
    text-align:center;
    font-size:14px;
    margin-bottom:12px;
}
.sb-success{
    color:green;
    text-align:center;
    font-size:14px;
    margin-bottom:12px;
}
.sb-otp-text{
    font-size:18px;
    color:#000;
    margin-bottom:26px;
}
.sb-otp-text strong{
    font-weight:700;
}
.sb-otp-boxes{
    display:flex;
    gap:8px;
    margin-bottom:34px;
}
.sb-otp-boxes input{
    /* 6 of these at the old 58px + 12px gap needed 408px, but the popup
       itself (.sb-auth-box) is only 380px wide minus its own padding —
       about 328px of actual room — so the last box or two always poked
       out past the popup's edge. This width/gap is the largest that still
       fits all 6 with room to spare, at every width .sb-auth-box can be. */
    width:44px;
    height:56px;
    border:1px solid #aaa;
    border-radius:8px;
    text-align:center;
    font-size:22px;
    outline:none;
}
.sb-otp-bottom{
    display:flex;
    justify-content:space-between;
    align-items:center;
    font-size:13px;
    color:#555;
    margin-bottom:30px;
}
.sb-otp-bottom button{
    border:0;
    background:transparent;
    color:#555;
}
@media(max-width:575px){
    .sb-auth-box{padding:18px 16px;}
    .sb-auth-head img{width:70px;}
    .sb-otp-boxes{gap:8px;}
    .sb-otp-boxes input{
        width:44px;
        height:56px;
    }
}
</style>

<div id="sbAuthPopup" class="sb-auth-popup">
    <div class="sb-auth-box">
        <button type="button" class="sb-auth-close" id="sbAuthClose">&times;</button>
        <div id="sbMsg"></div>
        <div id="sbStepOne">
            <div class="sb-auth-head">
                <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Service Bazzar">
                <div>
                    <h4>Welcome</h4>
                    <p>Login for a seamless experience</p>
                </div>
            </div>
            @if(config('services.google.client_id') || config('services.facebook.client_id'))
                <div class="sb-social-login">
                    @if(config('services.google.client_id'))
                        <a href="{{ route('user.social.login.redirect', ['provider' => 'google', 'redirect' => url()->current()]) }}" class="sb-social-btn sb-social-google" title="Continue with Google" aria-label="Continue with Google">
                            <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.72v2.26h2.9c1.7-1.57 2.7-3.88 2.7-6.62z"/><path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.9-2.26c-.8.54-1.84.86-3.06.86-2.35 0-4.34-1.59-5.05-3.72H.98v2.33A9 9 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.95 10.7A5.4 5.4 0 0 1 3.67 9c0-.59.1-1.17.28-1.7V4.97H.98A9 9 0 0 0 0 9c0 1.45.35 2.83.98 4.03l2.97-2.33z"/><path fill="#EA4335" d="M9 3.58c1.32 0 2.51.46 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0A9 9 0 0 0 .98 4.97l2.97 2.33C4.66 5.17 6.65 3.58 9 3.58z"/></svg>
                        </a>
                    @endif
                    @if(config('services.facebook.client_id'))
                        <a href="{{ route('user.social.login.redirect', ['provider' => 'facebook', 'redirect' => url()->current()]) }}" class="sb-social-btn sb-social-facebook" title="Continue with Facebook" aria-label="Continue with Facebook">
                            <svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="#1877F2" d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.7 4.53-4.7 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.95.93-1.95 1.89v2.26h3.32l-.53 3.49h-2.79V24C19.61 23.1 24 18.1 24 12.07z"/></svg>
                        </a>
                    @endif
                </div>
                <div class="sb-social-divider"><span>or</span></div>
            @endif
            <form id="sbAuthForm">
                @csrf
                <input type="hidden" name="current_url" id="sbCurrentUrl">
                <div class="sb-field">
                    <label>Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="sb-field">
                    <input type="text" name="mobile" placeholder="Enter Mobile Number" required>
                </div>
                <div class="sb-terms">
                    <label>
                        <input type="checkbox" required checked>
                        I Agree to Terms and Conditions
                    </label>
                    <br>
                    <a href="{{ url('/terms-and-conditions') }}">Terms & Conditions</a> & <a href="{{ url('/privacy-policy') }}">Privacy Policy</a>
                </div>
                <button type="submit" class="sb-btn" id="sbSendBtn">Continue</button>
            </form>
            <button type="button" id="sbMaybeLater" class="sb-later">Maybe Later</button>
        </div>
        <div id="sbStepOtp" style="display:none;">
            <div class="sb-auth-head">
                <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Service Bazzar">
                <div>
                    <h4>Welcome</h4>
                    <p>Login for a seamless<br>experience</p>
                </div>
            </div>
            <div class="sb-otp-text">
                Enter the code sent to <span id="sbOtpSentText" style="font-weight:600"></span>
            </div>
            <form id="sbOtpForm">
                @csrf
                <input type="hidden" name="otp" id="sbFullOtp">
                <div class="sb-otp-boxes">
                    <input type="text" maxlength="1" inputmode="numeric">
                    <input type="text" maxlength="1" inputmode="numeric">
                    <input type="text" maxlength="1" inputmode="numeric">
                    <input type="text" maxlength="1" inputmode="numeric">
                    <input type="text" maxlength="1" inputmode="numeric">
                    <input type="text" maxlength="1" inputmode="numeric">
                </div>
                <div class="sb-otp-bottom">
                    <span>Didn’t Receive the OTP?</span>
                    <button type="button" id="sbResendOtp">Resend OTP</button>
                </div>
                <button type="submit" class="sb-btn" id="sbVerifyBtn" disabled>Continue</button>
            </form>
        </div>
    </div>
</div>

{{-- OTP step every enquiry form goes through before it submits. Sits here so
     one copy covers every page, including forms injected after load. --}}
@include('themes.servicebazzar.partials.enquiry-otp')

@auth
{{--
    Logged-in profile panel — a JustDial-style slide-in from the right,
    opened by the avatar icon in the header instead of that icon linking
    straight to /dashboard. Every link below is a route that already
    exists elsewhere on the site (dashboard, profile, listings, wallet,
    leads inbox, password, contact) — nothing here is a placeholder.
--}}
<div id="sbProfilePanelOverlay" class="sb-profile-overlay"></div>
<div id="sbProfilePanel" class="sb-profile-panel">
    <div class="sb-profile-head">
        <a href="{{ route('user.profile.view', ['username' => Str::slug(auth()->user()->first_name . '-' . auth()->user()->last_name)]) }}" class="sb-profile-identity">
            <img src="{{ userAvatarUrl(auth()->user()->image ?? null) }}" alt="{{ auth()->user()->fullname() }}">
            <span>
                <strong>{{ auth()->user()->fullname() }}</strong>
                <small>Click to view profile</small>
            </span>
        </a>
        <button type="button" class="sb-profile-close" id="sbProfilePanelClose">&times;</button>
    </div>

    @php
        // Shown as "My Business" the way JustDial surfaces the listing you
        // already run — pulled straight from this user's own listings
        // rather than anything typed in on this page, so it's only ever as
        // current as the listing itself.
        $sbMyBusiness = $sbHeaderBusiness;
    @endphp

    <div class="sb-profile-menu">
        @if($sbMyBusiness)
            <a href="{{ route('user.listing.dashboard', $sbMyBusiness->id) }}" class="sb-profile-item sb-profile-item--business">
                <i class="fa-solid fa-store"></i>
                <span>
                    {{ $sbMyBusiness->name }}
                    <small>My Business</small>
                </span>
            </a>
            <a href="{{ route('free.listing') }}" class="sb-profile-item">
                <i class="fa-solid fa-plus"></i>
                <span>Add New Business</span>
            </a>
        @else
            <a href="{{ route('free.listing') }}" class="sb-profile-item sb-profile-item--highlight">
                <i class="fa-solid fa-arrow-trend-up"></i>
                <span>List Your Business</span>
            </a>
        @endif

        <a href="{{ route('user.dashboard') }}" class="sb-profile-item">
            <i class="fa-solid fa-gauge"></i>
            <span>Dashboard</span>
        </a>

        {{-- Every enquiry this business has had (the Leads page, no filter) — not the guest chats. --}}
        <a href="{{ $sbMyBusiness ? route('user.listing.leads', $sbMyBusiness->id) : route('user.listing.index') }}" class="sb-profile-item">
            <i class="fa-solid fa-inbox"></i>
            <span>Leads &amp; Enquiries</span>
        </a>

        <a href="{{ route('user.deposit') }}" class="sb-profile-item">
            <i class="fa-solid fa-wallet"></i>
            <span>Wallet</span>
        </a>

        <a href="{{ route('user.profile') }}" class="sb-profile-item">
            <i class="fa-solid fa-user-pen"></i>
            <span>Edit Profile</span>
        </a>

        <a href="{{ route('contact') }}" class="sb-profile-item">
            <i class="fa-solid fa-headset"></i>
            <span>Customer Service</span>
        </a>

        <a href="{{ url('/privacy-policy') }}" class="sb-profile-item">
            <i class="fa-solid fa-shield-halved"></i>
            <span>Policy</span>
        </a>

        <a href="{{ route('user.logout') }}" class="sb-profile-item sb-profile-item--danger">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<style>
.sb-profile-overlay{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.45);
    z-index:19999999999;
}
.sb-profile-overlay.show{ display:block; }
.sb-profile-panel{
    position:fixed;
    top:0;
    right:-360px;
    width:340px;
    max-width:88vw;
    height:100%;
    background:#fff;
    z-index:20000000000;
    box-shadow:-4px 0 24px rgba(0,0,0,.15);
    transition:right .25s ease;
    display:flex;
    flex-direction:column;
    overflow-y:auto;
    /* The panel still scrolls if the menu ever grows taller than the
       screen, but the always-visible track/thumb was showing up as a
       stray grey bar down the panel even when nothing needed scrolling. */
    scrollbar-width:none;
    -ms-overflow-style:none;
}
.sb-profile-panel::-webkit-scrollbar{
    display:none;
}
.sb-profile-panel.show{ right:0; }
.sb-profile-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    padding:20px 18px;
    border-bottom:1px solid #eee;
}
.sb-profile-identity{
    display:flex;
    align-items:center;
    gap:12px;
    text-decoration:none;
    color:#111;
    min-width:0;
}
.sb-profile-identity img{
    width:48px;
    height:48px;
    border-radius:50%;
    object-fit:cover;
    flex-shrink:0;
}
.sb-profile-identity strong{
    display:block;
    font-size:18px;
    font-weight:800;
    color:#111;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}
.sb-profile-identity small{
    display:block;
    font-size:12px;
    color:#777;
}
.sb-profile-close{
    border:0;
    background:transparent;
    font-size:26px;
    line-height:1;
    color:#111;
    flex-shrink:0;
}
.sb-profile-menu{
    padding:8px 0;
}
.sb-profile-item{
    display:flex;
    align-items:center;
    gap:14px;
    padding:14px 18px;
    color:#222;
    text-decoration:none;
    font-size:15px;
    border-bottom:1px solid #f3f3f3;
}
.sb-profile-item:hover{
    background:#f7f8f9;
    color:#222;
}
.sb-profile-item i{
    width:18px;
    text-align:center;
    color:#087dde;
}
.sb-profile-item--highlight{
    background:#eaf4fe;
    font-weight:600;
}
.sb-profile-item--business span{
    display:flex;
    flex-direction:column;
    gap:2px;
}
.sb-profile-item--business span strong,
.sb-profile-item--business span{
    font-weight:600;
    color:#222;
}
.sb-profile-item--business small{
    font-size:11px;
    font-weight:600;
    color:#22a45d;
}
.sb-profile-item--danger i,
.sb-profile-item--danger span{
    color:#d9364f;
}

/*
 * The header profile trigger reuses .nav-btn (the same class as the
 * "Login / Sign Up" button) for its layout, but it should just be the
 * round avatar — not a bordered/background button around it.
 */
.header-area .bottom-header .navbar ul li a.nav-btn.profile-dashboard-link{
    border:0;
    padding:0;
    background:transparent;
    line-height:normal;
}
.header-area .bottom-header .navbar ul li a.nav-btn.profile-dashboard-link:hover{
    background:transparent;
}
/* Every other nav item keeps its vertical spot via nav-btn's own padding;
   this one lost that when the padding was zeroed above, so it needs to be
   centered against its siblings explicitly instead. */
.header-area .bottom-header .navbar ul li.profile-dashboard-item{
    display:flex;
    align-items:center;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const panel = document.getElementById('sbProfilePanel');
    const overlay = document.getElementById('sbProfilePanelOverlay');
    const closeBtn = document.getElementById('sbProfilePanelClose');
    const triggers = document.querySelectorAll('.profile-dashboard-link');
    const backgroundInputs = Array.from(document.querySelectorAll(
        '#homeLocationInput, #homeCategorySearch, #locationInput, #CategorySearch, #jdMobileLocationInput, #ytMobileSearchInput'
    ));

    function setBackgroundInputsDisabled(disabled) {
        backgroundInputs.forEach(function (input) {
            input.disabled = disabled;
            input.setAttribute('aria-disabled', disabled ? 'true' : 'false');
        });
    }

    function openPanel(e) {
        e.preventDefault();
        if (document.activeElement && backgroundInputs.includes(document.activeElement)) {
            document.activeElement.blur();
        }
        setBackgroundInputsDisabled(true);
        panel.classList.add('show');
        overlay.classList.add('show');
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
    }

    function closePanel() {
        panel.classList.remove('show');
        overlay.classList.remove('show');
        setBackgroundInputsDisabled(false);
        document.documentElement.style.overflow = '';
        document.body.style.overflow = '';
    }

    triggers.forEach(function (trigger) {
        trigger.addEventListener('click', openPanel);
    });

    closeBtn.addEventListener('click', closePanel);
    overlay.addEventListener('click', closePanel);
});
</script>
@endauth

<script>
document.addEventListener('DOMContentLoaded', function () {
    const popup = document.getElementById('sbAuthPopup');
    const closeBtn = document.getElementById('sbAuthClose');
    const laterBtn = document.getElementById('sbMaybeLater');
    const stepOne = document.getElementById('sbStepOne');
    const stepOtp = document.getElementById('sbStepOtp');
    const authForm = document.getElementById('sbAuthForm');
    const otpForm = document.getElementById('sbOtpForm');
    const msgBox = document.getElementById('sbMsg');
    const sentText = document.getElementById('sbOtpSentText');
    const sendBtn = document.getElementById('sbSendBtn');
    const verifyBtn = document.getElementById('sbVerifyBtn');
    const resendBtn = document.getElementById('sbResendOtp');
    const otpFull = document.getElementById('sbFullOtp');
    const otpInputs = document.querySelectorAll('.sb-otp-boxes input');

    function showPopup(){ popup.classList.add('show'); document.body.style.overflow = 'hidden'; }
    function closePopup(){ popup.classList.remove('show'); document.body.style.overflow = ''; }
    function showMsg(type, text){ msgBox.innerHTML = '<div class="sb-' + type + '">' + text + '</div>'; }
    function clearMsg(){ msgBox.innerHTML = ''; }

    closeBtn.addEventListener('click', closePopup);
    laterBtn.addEventListener('click', closePopup);
    window.openSbAuthPopup = showPopup;

  authForm.addEventListener('submit', async function (e) {
    e.preventDefault();

    clearMsg();

    sendBtn.disabled = true;
    sendBtn.innerText = 'Please wait...';

    try {
        const response = await fetch("{{ route('user.quick.auth.send') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json'
            },
            body: new FormData(authForm)
        });

        const data = await response.json();

        console.log('OTP response:', data);

        if (!response.ok || data.success === false) {
            throw data;
        }

        const mobile = data.mobile || data.phone || '';

        sentText.textContent = mobile
            ? `WhatsApp number ${mobile}`
            : 'your WhatsApp number';

        stepOne.style.display = 'none';
        stepOtp.style.display = 'block';

        showMsg(
            'success',
            data.message || 'OTP sent successfully on WhatsApp.'
        );

        setTimeout(() => {
            clearMsg();
        }, 1500);

        if (otpInputs.length > 0) {
            otpInputs[0].focus();
        }
    } catch (error) {
        console.error('OTP send error:', error);

        let message = 'Something went wrong. Please try again.';

        if (error?.message) {
            message = error.message;
        }

        if (error?.errors) {
            const firstError = Object.values(error.errors)[0];

            if (Array.isArray(firstError)) {
                message = firstError[0];
            } else if (typeof firstError === 'string') {
                message = firstError;
            }
        }

        showMsg('error', message);
    } finally {
        sendBtn.disabled = false;
        sendBtn.innerText = 'Continue';
    }
});

    function updateOtp(){
        let otp = '';
        otpInputs.forEach(input => { otp += input.value; });
        otpFull.value = otp;
        verifyBtn.disabled = otp.length !== 6;
    }

    otpInputs.forEach((input, index) => {
        input.addEventListener('input', function(){
            this.value = this.value.replace(/\D/g, '');
            if(this.value && otpInputs[index + 1]) otpInputs[index + 1].focus();
            updateOtp();
            if (otpFull.value.length === 6) otpForm.requestSubmit();
        });
        input.addEventListener('keydown', function(e){
            if(e.key === 'Backspace' && !this.value && otpInputs[index - 1]) otpInputs[index - 1].focus();
        });
    });

    otpForm.addEventListener('submit', function(e){
        e.preventDefault();
        clearMsg();
        updateOtp();
        if(otpFull.value.length !== 6){
            showMsg('error', 'Please enter 6 digit OTP');
            return;
        }
        verifyBtn.disabled = true;
        verifyBtn.innerText = 'Please wait...';
        const formData = new FormData();
        formData.append('otp', otpFull.value);
        fetch("{{ route('user.quick.auth.verify') }}", {
            method: "POST",
            headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}", "Accept": "application/json" },
            body: formData
        })
        .then(async response => {
            const data = await response.json();
            if(!response.ok) throw data;
            showMsg('success', data.message);
            location.reload();
        })
        .catch(error => {
            let message = error.message || 'Invalid OTP';
            if(error.errors) message = Object.values(error.errors)[0][0];
            showMsg('error', message);
            verifyBtn.disabled = false;
            verifyBtn.innerText = 'Continue';
        });
    });

    resendBtn.addEventListener('click', function(){
        clearMsg();
        resendBtn.disabled = true;
        resendBtn.innerText = 'Sending...';
        fetch("{{ route('user.quick.auth.resend') }}", {
            method: "POST",
            headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}", "Accept": "application/json" }
        })
        .then(async response => {
            const data = await response.json();
            if(!response.ok) throw data;
            showMsg('success', data.message);
        })
        .catch(error => { showMsg('error', error.message || 'OTP resend failed'); })
        .finally(() => {
            resendBtn.disabled = false;
            resendBtn.innerText = 'Resend OTP';
        });
    });
});

const currentUrlInput = document.getElementById('sbCurrentUrl');
if(currentUrlInput) currentUrlInput.value = window.location.href;

function openSbAuthPopup() {
    @auth return; @endauth
    document.getElementById('sbAuthPopup').classList.add('show');
    document.body.style.overflow = 'hidden';
}

// /user/login redirects here with ?login=1 — this is the sign-in screen now.
@if(request()->boolean('login') || session('showAuthPopup'))
document.addEventListener('DOMContentLoaded', function () { openSbAuthPopup(); });
@endif
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector("#scrollSearchHolder #headerSearchForm");
    if (!form) return;
    const locationInput = form.querySelector("#headerLocationInput");
    const categoryInput = form.querySelector("#headerCategorySearch");
    function titleCase(str){
        return String(str || "").replace(/-/g, " ").replace(/\b\w/g, c => c.toUpperCase());
    }
    function slugify(text){
        return String(text || "").toLowerCase().trim().replace(/,/g, "").replace(/\s+/g, "-").replace(/[^a-z0-9-]/g, "").replace(/-+/g, "-").replace(/^-+|-+$/g, "");
    }
    form.addEventListener("submit", function(e){
        e.preventDefault();
        const locationValue = locationInput.value.trim();
        const categoryValue = categoryInput.value.trim();
        if(categoryValue === ""){
            categoryInput.focus();
            return false;
        }
        if(locationValue === ""){
            locationInput.focus();
            return false;
        }
        let city = locationValue;
        let area = "";
        if(locationValue.includes(",")){
            const parts = locationValue.split(",");
            area = parts[0].trim();
            city = parts[1].trim();
        }
        let finalUrl = `${window.location.origin}/${slugify(city)}/${slugify(categoryValue)}`;
        if(area !== "") finalUrl += `-in-${slugify(area)}`;
        window.location.href = finalUrl;
        return false;
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("ytMobileSearchInput");
    if (!input) return;
    let clearBtn = document.createElement("span");
    clearBtn.className = "yt-mobile-clear-btn";
    clearBtn.innerHTML = '<i class="fas fa-times"></i>';
    input.parentNode.appendChild(clearBtn);
    function toggleClearBtn(){ clearBtn.style.display = input.value.trim() !== "" ? "block" : "none"; }
    input.addEventListener("input", toggleClearBtn);
    clearBtn.addEventListener("click", function(){
        input.value = "";
        clearBtn.style.display = "none";
        input.focus();
        const sug = document.getElementById("ytMobileCategorySuggestions");
        if(sug){
            sug.classList.remove("show");
            sug.innerHTML = "";
        }
    });
    toggleClearBtn();
});




</script>

<style>
    
    .yt-search-input-box{ position:relative; }
    .yt-mobile-clear-btn{
        position:absolute;
        right:38px;
        top:50%;
        transform:translateY(-50%);
        cursor:pointer;
        display:none;
        font-size:14px;
        color:#666;
        z-index:999;
    }

    
</style>


<style>
      .voice-modal {
        display: none;
        position: fixed !important;
        inset: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(0, 0, 0, .45);
        z-index: 2147483647 !important;
    }

    .voice-box {
        width: 550px;
        max-width: 90%;
        height: 300px;
        background: #fff;
        border-radius: 14px;
        padding: 28px;
        text-align: center;
        position: fixed !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
    }

    .voice-close {
        position: absolute;
        right: 18px;
        top: 10px;
        border: 0;
        background: transparent;
        font-size: 38px;
        line-height: 1;
        cursor: pointer;
    }

    .voice-box h3 {
        font-size: 24px;
        font-weight: 600;
        margin-top: 0;
    }

    .voice-loader {
        margin-top: 90px;
    }

    .voice-loader span {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin: 0 4px;
        animation: headerVoiceBounce .9s infinite;
    }

    .voice-loader span:nth-child(1),
    .voice-loader span:nth-child(5) { background: #ff6b00; }
    .voice-loader span:nth-child(2),
    .voice-loader span:nth-child(4) { background: #1e88ff; }
    .voice-loader span:nth-child(3) { background: #0d6efd; }
    .voice-loader span:nth-child(2) { animation-delay: .1s; }
    .voice-loader span:nth-child(3) { animation-delay: .2s; }
    .voice-loader span:nth-child(4) { animation-delay: .3s; }
    .voice-loader span:nth-child(5) { animation-delay: .4s; }

    @keyframes headerVoiceBounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-12px); }
    }

    
</style>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const detectedCity = window.normalizeEnglishLocationName(localStorage.getItem("jf_detected_city") || "");
    const cityText = document.getElementById("jdMobileCityText");
    const jdInput = document.getElementById("jdMobileLocationInput");
    const homeInput = document.getElementById("homeLocationInput");

    if (window.location.pathname === "/" && detectedCity) {
        localStorage.removeItem("jf_user_city");
        localStorage.removeItem("jf_selected_location_name");

        if (cityText) cityText.innerText = detectedCity;
        if (jdInput) jdInput.value = detectedCity;
        if (homeInput) {
            homeInput.value = detectedCity;
            homeInput.dataset.selectedLocation = "1";
        }

        // Point category links at the detected city right away (no refresh).
        if (typeof window.sbRefreshCategoryLinks === "function") {
            window.sbRefreshCategoryLinks();
        }
    }

    const cityBtn = document.getElementById("jdMobileCityBtn");
    if (cityBtn) cityBtn.classList.remove("detecting");
});


</script>
<script>
window.addEventListener("load", function () {
    const parts = window.location.pathname.split("/").filter(Boolean);

    // URL me city ho to detected city se overwrite mat karo
    if (parts.length >= 2) return;

    const sessionCity = @json(session('detected_city') ?? session('user_location') ?? session('selected_location') ?? '');
    const detectedCity = window.normalizeEnglishLocationName(localStorage.getItem("jf_detected_city") || sessionCity || "");

    if (!detectedCity) return;

    if (window.location.pathname === "/") {
        localStorage.removeItem("jf_user_city");

        const cityText = document.getElementById("jdMobileCityText");
        const jdInput = document.getElementById("jdMobileLocationInput");
        const homeInput = document.getElementById("homeLocationInput");

        if (cityText) cityText.innerText = detectedCity;
        if (jdInput) jdInput.value = detectedCity;
        if (homeInput) {
            homeInput.value = detectedCity;
            homeInput.dataset.selectedLocation = "1";
        }

        // This auto-detect fills the location input on load (after the first
        // category-link pass has already run with no known city), so re-point
        // the category links to the detected city now — otherwise they'd stay
        // on the generic /listings/... URL until the visitor refreshed.
        if (typeof window.sbRefreshCategoryLinks === "function") {
            window.sbRefreshCategoryLinks();
        }
    }
});
</script>

@include('themes.servicebazzar.partials.notification_bell')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var strip = document.getElementById('jdMobileLocationStrip');
    var top = document.querySelector('#stickyScrollHeader .jdh-top');
    if (strip && top && top.parentElement) top.parentElement.insertBefore(strip, top.nextSibling);
});
</script>
</body>
</html>
