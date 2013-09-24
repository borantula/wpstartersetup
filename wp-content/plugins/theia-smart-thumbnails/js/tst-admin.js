/*
 * Copyright 2012, Theia Smart Thumbnails, Liviu Cristian Mirea Ghiban.
 */
var tst = tst || {};

tst.createPickerDelayed = function(options) {
	if (jQuery(options.input).size() > 0) {
		tst.createPickerDelayedHelper(options);
	}
	else {
		setTimeout(function(options) {
			return function() {
				tst.createPickerDelayed(options);
			};
		}(options), 50);
	}
};

tst.createPickerDelayedHelper = function(options) {
	jQuery(".compat-field-theiaSmartThumbnails_position").hide();
	tst.createPicker(options);
};

tst.createPicker = function(options) {
    var me = this,
        $ = jQuery,
        defaults = {
	        image: '',
            input: '',
	        preview: '',
	        sizes: [],
	        position: {
		        x: 0.5,
		        y: 0.5
	        }
        };
    me.options = $.extend(defaults, options);

	// Initialization function
	me.init = function() {
		me.mouseIsDown = false;
		me.position = me.options.position;
		me.input = $(me.options.input);
		me.imageContainer = $(me.options.image);
		me.image = me.imageContainer.children('img');
		me.imageOverlay = $('<div class="_overlay">');
		me.crosshair = $('<div class="_crosshair">');
		me.preview = $(me.options.preview);
		me.imageContainer.append(me.crosshair, me.imageOverlay);

		// Load image
		me.imageObject = new Image();
		me.imageObject.onload = (function(me) {
			return function() {
				// Create preview elements for each size
				for (var i in me.options.sizes) {
					var size = me.options.sizes[i];
					size.image = $('<img src="' + me.image.attr('src') + '">');
					size.imageContainer = $('<div class="_imageContainer">')
							.css({
							width: size.width,
							height: size.height
						})
						.append(size.image);
					size.caption = $('<div class="_caption">' + i + '</div>');
					size.div = $('<div>')
						.append(size.imageContainer, size.caption);
					me.preview.append(size.div);
				}

				// Refresh previews.
				me.refreshPreview();

				// Set container scrollTop
				if (tst.scrollTop) {
					me.input.closest('.media-sidebar').scrollTop(tst.scrollTop);
					tst.scrollTop = null;
				}
			};
		})(me);
		me.imageObject.src = me.image.attr('src');

		// Mouse events
		me.imageOverlay
			.mousedown(function(e) {
				me.mouseIsDown = true;
				me.mousedown(e);
			})
			.mousemove(function(e) {
				if (me.mouseIsDown) {
					me.mousedown(e);
				}
			})
			.mouseup(function() {
				me.mouseIsDown = false;

				// Inside a modal window in WordPress 3.5?
				if (
					jQuery(".version-3-4").size() === 0 &&
					me.input.closest(".media-modal").size() === 1
				) {
					// Remember scroll position
					tst.scrollTop = me.input.closest('.media-sidebar').scrollTop();

					// Display loader.
					me.imageOverlay.addClass('_loader');

					// Stop accepting further input.
					me.imageOverlay.unbind();

					// Fire change event, which will fire an AJAX request.
					me.input.change();
				}
			})
			.mouseleave(function() {
				me.mouseIsDown = false;
			});
	}

	me.mousedown = function(e) {
		e.preventDefault();
		var offset = me.image.offset(),
			x = e.pageX - offset.left,
			y = e.pageY - offset.top;
		me.setPosition(x, y);
	}

	// Set x and y position.
	me.setPosition = function(x, y) {
		me.position.x = Math.max(0, Math.min(1, x / me.image.width()));
		me.position.y = Math.max(0, Math.min(1, y / me.image.height()));
		me.input.val('[' + me.position.x + ',' + me.position.y + ']');
		me.refreshPreview();
	}

	// Refresh all the previews.
	me.refreshPreview = function() {
		me.crosshair.css({
			left: me.position.x * me.image.width(),
			top: me.position.y * me.image.height()
		});

		for (var i in me.options.sizes) {
			var size = me.options.sizes[i];
			var resize = tst.imageResizeDimensions(me.imageObject.width, me.imageObject.height, size.width, size.height, me.position.x, me.position.y);
			var w, h, cssW, cssH;
			if (me.imageObject.width - resize[6]) {
				w = me.imageObject.width / me.imageObject.height * resize[5];
				h = resize[5];
				cssW = 'auto';
				cssH = resize[5];
			}
			else {
				w = resize[4];
				h = me.imageObject.height / me.imageObject.width * resize[4];
				cssW = resize[4];
				cssH = 'auto';
			}
			size.image.css({
				left: -resize[2] / me.imageObject.width * w,
				top: -resize[3] / me.imageObject.height * h,
				width: cssW,
				height: cssH
			});
		}
	}

	// Initialize.
	me.init();
};

// Calculates the resize box. Similar to WordPress's image_resize_dimensions.
tst.imageResizeDimensions = function(orig_w, orig_h, dest_w, dest_h, interestX, interestY) {
	var aspect_ratio = orig_w / orig_h;
	var new_w = Math.min(dest_w, orig_w);
	var new_h = Math.min(dest_h, orig_h);

	if ( !new_w ) {
		new_w = Math.floor(new_h * aspect_ratio);
	}

	if ( !new_h ) {
		new_h = Math.floor(new_w / aspect_ratio);
	}

	var size_ratio = Math.max(new_w / orig_w, new_h / orig_h);

	var crop_w = Math.round(new_w / size_ratio);
	var crop_h = Math.round(new_h / size_ratio);

	var s_x = Math.floor( (orig_w - crop_w) * interestX );
	var s_y = Math.floor( (orig_h - crop_h) * interestY );

	return [0, 0, Math.round(s_x), Math.round(s_y), Math.round(new_w), Math.round(new_h), Math.round(crop_w), Math.round(crop_h)];
}