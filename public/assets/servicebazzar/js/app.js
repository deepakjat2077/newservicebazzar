(function ($) {
	"use strict";
	$(document).ready(function() {
		// select2 is no longer loaded site-wide (nothing in the theme carries
		// class="select2"). Calling it unguarded threw a TypeError that killed
		// the rest of this file — including the slider set-up below.
		if ($.fn.select2) {
			$('.select2').select2();
		}
	});

	// featured-listing-slider
  $('.featured-listing-slider').slick({
    autoplay: true,
    autoplaySpeed: 2000,
    dots: false,
    infinite: true,
    speed: 300,
    slidesToShow: 3,
    arrows: true,
    prevArrow: '<div class="prev"><i class="las la-angle-left"></i></div>',
    nextArrow: '<div class="next"><i class="las la-angle-right"></i></div>',
    slidesToScroll: 1,
    responsive: [
      {
        breakpoint: 1500,
        settings: {
          slidesToShow: 3,
        }
      },
      {
        breakpoint: 1400,
        settings: {
          slidesToShow: 3,
        }
      },
      {
        breakpoint: 1200,
        settings: {
          slidesToShow: 3,
        }
      },
      {
        breakpoint: 992,
        settings: {
          slidesToShow: 2,
        }
      },
      {
        breakpoint: 800,
        settings: {
          slidesToShow: 2,
        }
      },
      {
        breakpoint: 768,
        settings: {
          slidesToShow: 1,
        }
      }
    ]
  });


  // menu fixed to top
 var fixed_top = $(".header-area");

function toggleFixedHeader() {

    if (
        $(window).scrollTop() > 50 ||
        currentRoute === "listings.search" ||
        currentRoute === "listings.category" ||
         currentRoute === "listings.subcategory"||
          currentRoute === "listings.category.location"||
           currentRoute === "listings.category"||
           currentRoute === "listings.details"||
           currentRoute === "listings.city.category.area"




    ) {
        fixed_top.addClass("animated fadeInDown fixed-header");
    } else {
        fixed_top.removeClass("animated fadeInDown fixed-header");
    }

}

// page load
$(document).ready(function() {
    toggleFixedHeader();
});

// scroll
$(window).on("scroll", function() {
    toggleFixedHeader();
});

  //image upload preview
  function readURL(input,thisElement) {
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      var parentElement = thisElement.closest('.image-upload-area');
      var previewElement = parentElement.find('#preview');
      reader.onload = function(e) {
        previewElement.attr('src', e.target.result);
      }
      
      reader.readAsDataURL(input.files[0]); // convert to base64 string
    }
  }

  $(".upload-image").change(function() {
    var thisElement = $(this);
    readURL(this,thisElement);
  });

 // $( document ).ready(function() {
//     var loader = $('.preloader');
//     setTimeout(function() {
//         loader.addClass('loaded');
//     }, 500);
// });

})(jQuery);