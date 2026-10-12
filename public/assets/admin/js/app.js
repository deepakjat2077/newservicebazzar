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
    /*
     * Content is almost always pasted out of Word or Google Docs, which brings
     * <o:p> tags, mso-* styles, <font size>/<font color> and a deep stack of
     * <div>s along with the text. Kept as-is, every page ends up with its own
     * font sizes, its own colours and its own gaps.
     *
     * So the paste is reduced to structure before it ever reaches the editor,
     * and the font/colour buttons are gone from the toolbar: the site's
     * stylesheet decides how this content looks, not whoever wrote it. The
     * server applies the same rules again in App\Support\ContentHtml, this is
     * only so the admin sees on screen what will actually be saved.
     */
    function cleanPastedHtml(html) {
        return String(html)
            // Word conditional comments and namespaced tags (<o:p>, <w:sdt>).
            .replace(/<!--\[if[\s\S]*?<!\[endif\]-->/gi, '')
            .replace(/<!--[\s\S]*?-->/g, '')
            .replace(/<\s*(style|script|xml)\b[^>]*>[\s\S]*?<\/\s*\1\s*>/gi, '')
            .replace(/<\/?\s*[a-z]+:[a-z0-9]+[^>]*>/gi, '')
            // Purely presentational wrappers.
            .replace(/<\/?\s*(font|span|small|big|center)\b[^>]*>/gi, '')
            .replace(/<\/?\s*div\b[^>]*>/gi, '')
            // Everything else keeps its tag but loses its looks.
            .replace(/\s(style|class|lang|align|width|height|face|color|size)\s*=\s*("[^"]*"|'[^']*'|[^\s>]+)/gi, '')
            .replace(/&nbsp;/gi, ' ')
            .replace(/ /g, ' ')
            .replace(/[ \t]*\n[ \t]*/g, ' ')
            .replace(/[ \t]{2,}/g, ' ');
    }

    function normaliseSavedEditorSpacers(html) {
        return String(html || '')
            .replace(/<div\b[^>]*class=(["'])[^"']*\brich-content-spacer\b[^"']*\1[^>]*>\s*<\/div>/gi, '<p><br></p>')
            .replace(/<p\b[^>]*>\s*<br\s*\/?>\s*<\/p>/gi, '<p><br></p>');
    }

    if ($.fn.summernote) {
        $('.richEditor').not('[data-editor="manual"]').summernote({
            height: 250,
            // Word's own list/heading markup is what breaks the layout, so the
            // editor is told to write plain semantic tags instead.
            styleTags: ['p', 'h2', 'h3', 'h4'],
            toolbar: [
                ['style', ['style', 'bold', 'italic', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture', 'table', 'hr']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            callbacks: {
                // Upload pictures as files and insert their link; Summernote's
                // default embeds the whole image as base64 text in the post.
                onImageUpload: function (files) {
                    var $editor = $(this);
                    Array.prototype.forEach.call(files, function (file) {
                        var data = new FormData();
                        data.append('image', file);
                        var token = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').first().val();
                        $.ajax({
                            url: '/admin/editor-image',
                            method: 'POST',
                            data: data,
                            processData: false,
                            contentType: false,
                            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
                        }).done(function (res) {
                            if (res && res.url) {
                                $editor.summernote('insertImage', res.url, function ($img) {
                                    $img.attr('alt', (file.name || '').replace(/\.[^.]+$/, '').replace(/[-_]+/g, ' '));
                                    $img.css('max-width', '100%');
                                });
                            }
                        }).fail(function (xhr) {
                            var msg = (xhr.responseJSON && (xhr.responseJSON.message || (xhr.responseJSON.errors && Object.values(xhr.responseJSON.errors)[0][0]))) || 'Image upload failed.';
                            alert(msg);
                        });
                    });
                },
                onInit: function () {
                    var $editor = $(this);
                    var code = $editor.summernote('code');
                    var normalised = normaliseSavedEditorSpacers(code);

                    if (code !== normalised) {
                        $editor.summernote('code', normalised);
                    }
                },
                onPaste: function (e) {
                    var clipboard = (e.originalEvent || e).clipboardData;

                    if (!clipboard) {
                        return;
                    }

                    var html = clipboard.getData('text/html');
                    var text = clipboard.getData('text/plain');

                    e.preventDefault();

                    if (html) {
                        $(this).summernote('pasteHTML', cleanPastedHtml(html));
                        return;
                    }

                    // Plain text still needs its blank lines turned into real
                    // paragraphs, or the whole thing arrives as one block.
                    var paragraphs = String(text || '')
                        .replace(/\r\n?/g, '\n')
                        .split(/\n{2,}/)
                        .map(function (block) {
                            return block.trim();
                        })
                        .map(function (block) {
                            if (!block) {
                                return '<p><br></p>';
                            }

                            return '<p>' + $('<div>').text(block).html().replace(/\n/g, ' ') + '</p>';
                        })
                        .filter(Boolean)
                        .join('<p><br></p>');

                    $(this).summernote('pasteHTML', paragraphs);
                }
            }
        });
    }

    $('form').on('submit', function () {
        if (!$.fn.summernote) {
            return;
        }

        $(this).find('.richEditor').not('[data-editor="manual"]').each(function () {
            var $editor = $(this);

            if ($editor.next('.note-editor').length) {
                $editor.val(normaliseSavedEditorSpacers($editor.summernote('code')));
            }
        });
    });

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
