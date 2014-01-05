jQuery(document).ready(function($){

	// Image uploader

    var wpcg_uploader;

    $('#upload_image_button').click(function(e) {
        e.preventDefault();
 
        if (wpcg_uploader) {
            wpcg_uploader.open();
            return;
        }
 
        wpcg_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        });
 
        wpcg_uploader.on('select', function() {
            attachment = wpcg_uploader.state().get('selection').first().toJSON();
            $('#upload_image').val(attachment.url);
        });
 
		wpcg_uploader.open();
	});
    
    
    // Settings table toggler

	$('.toggler').each(function() {
		var settings_table = $(this).parent().find('.wpcg_settings_table');
    	     	
		(this.checked) ? settings_table.show() : settings_table.hide();
    	
		$(this).change(function() {
			settings_table.toggle();
		});
	});


});