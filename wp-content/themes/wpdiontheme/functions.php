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

define('DION_COMPONENTS_URL',get_stylesheet_directory_uri().'/components');

if ( ! isset( $content_width ) ) {
	$content_width = 640; /* pixels */
}

require DION_THEME_DIR.'/inc/vendor/autoload.php';

//setting up the theme
Dion\ThemeSetup::getInstance();

add_filter('redux/options/dionOpt/sections', 'dynamic_section');

global $reduxConfig;
//$reduxConfig = new Dion\Admin\ReduxConfig();

//start ajax
Dion\Ajax::hooks();

//example usage of ajax class
Dion\Ajax::register('tester-event',function(){

	$success = 'successful request';
	$fail = 'failed request';
	


	update_option( 'dion-ajax-test', date('H:i:s') );

	if($_POST['success'] == 'yes') {

		wp_send_json_error($fail);
	} else {
		wp_send_json_success($fail);

	}
	
});




function dynamic_section($sections)
{
    //$sections = array();
    $sections[] = array(
        'title'  => __('Section via hook', 'redux-framework-demo'),
        'desc'   => __('<p class="description">This is a section created by adding a filter to the sections array. Can be used by child themes to add/remove sections from the options.</p>', 'redux-framework-demo'),
        'icon'   => 'el-icon-paper-clip',
        // Leave this as a blank section, no options just some intro text set above.
        'fields' => array()
    );

    return $sections;
}
