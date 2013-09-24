<?php
/**
* theme constants
*/
/*
* Lowercase theme slug which is also theme directory
*/
define('DION_THEME_SLUG','wpdion');
define('DION_THEME_URL',get_stylesheet_directory_uri());
define('DION_THEME_DIR',get_stylesheet_directory());

if ( ! isset( $content_width ) )
	$content_width = 640; /* pixels */

require DION_THEME_DIR.'/inc/vendor/autoload.php';


//setting up the theme
Dion\ThemeSetup::getInstance();

$dionAjax = \Dion\Ajax::hooks();

//example usage of ajax class
\Dion\Ajax::register('tester-event',function(){

	$success = 'successful request';
	$fail = 'failed request';
	


	update_option( 'dion-ajax-test', date('H:i:s') );

	if($_POST['success'] == 'yes') {

		wp_send_json_error($fail);
	} else {
		wp_send_json_success($fail);

	}
	
});





