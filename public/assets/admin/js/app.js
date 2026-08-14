$(function() {
	"use strict";
	
	$('.has-child-menu > a').on('click', function () {
		if (!$('.sidebar').hasClass('closed')) {
			if ($(this).parent().find('.sidebar-child-menu').length) {
				if ($(this).parent().find('.sidebar-child-menu').first().is(':visible')) {
					$(this).removeClass('sidebar-menu-active');
					$(this).parent().find('.sidebar-child-menu').first().slideUp({
						done: function done() {
							$(this).removeClass('child-menu-active');
						}
					});
				} else {
					$(this).addClass('sidebar-menu-active');
					$(this).parent().find('.sidebar-child-menu').first().slideDown({
						done: function done() {
							$(this).addClass('child-menu-active');
						}
					});
				}
			}
		}
	});



	//Sidebar toggle
	$('.bar-toggle').on('click',function(){
		$('.sidebar').toggleClass('closed');
		$('.topbar').toggleClass('topbar-full');
		$('.content-panel').toggleClass('content-panel-full');
		$(this).toggleClass('bar-closed');
	});

	$('.res-sidebar-sign').on('click',function(){
		$('.sidebar').toggleClass('sidebar-open');
		$(this).toggleClass('sidebar-activated');
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

	$(".upload-image").on('change',function() {
		var thisElement = $(this);
	  readURL(this,thisElement);
	});


	
    /*
     * Summernote replaces the old jQuery richText editor. Textareas marked
     * data-editor="manual" are skipped: the listing add/edit forms build their
     * own instance with paste sanitising and SEO-score hooks, and initialising
     * twice would leave the second call operating on a dead textarea.
     */
    if ($.fn.summernote) {
        $('.richEditor').not('[data-editor="manual"]').summernote({
            height: 250,
            toolbar: [
                ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture', 'table', 'hr']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    }

    $('.copyBtn').on('click',function(){
		/* Get the text field */
	  	var copyText = document.getElementById("myInput");

	  	/* Select the text field */
	  	copyText.select();
	  	copyText.setSelectionRange(0, 99999); /* For mobile devices */

	  	/* Copy the text inside the text field */
	  	document.execCommand("copy");
	  	$(this).text('Copied');
	});


	/*
	 * The sidebar menu scrolls with plain CSS overflow (see .sidebar-menu-area
	 * in app.css). slimScroll used to wrap it, but it only listens for the
	 * legacy mousewheel/DOMMouseScroll events, so the wheel did nothing in
	 * current browsers and the lower menu items were unreachable.
	 */

});