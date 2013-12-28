<?php
/*
Plugin Name: Color My Posts
Plugin URL: http://remicorson.com/color-my-posts
Description: A little plugin to color post rows depending on the posts' status in the administration
Version: 0.1
Author: Remi Corson
Author URI: http://remicorson.com
Contributors: corsonr
tags: color, customization, administration, corsonr, remi corson
*/

class rc_color_my_posts {
 
	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/
 
	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct() {
	
		add_action('admin_footer', array( &$this,'rc_color_my_admin_posts') );
 
	} // end constructor
 
	function rc_color_my_admin_posts(){
		?>
		<style>
			/* Color by post Status */
			.status-draft { background: #ffffe0 !important;}
			.status-future { background: #E9F2D3 !important;}
			.status-publish {}
			.status-pending { background: #D3E4ED !important;}
			.status-private { background: #FFECE6 !important;}
			.post-password-required { background: #ff9894 !important;}
			
			/* Color by author data */
			.author-self {}
			.author-other {}
			
			/* Color by post format */
			.format-aside {}
			.format-gallery {}
			.format-link {}
			.format-image {}
			.format-quote {}
			.format-status {}
			.format-video {}
			.format-audio {}
			.format-chat {}
			.format-standard {}
			
			/* Color by post category (change blog by the category slug) */
			.category-blog {}
			
			/* Color by custom post type (change xxxxx by the custom post type slug) */
			.xxxxx {}
			.type-xxxxx {}
			
			/* Color by post ID (change xxxxx by the post ID) */
			.post-xxxxx {}
			
			/* Color by post tag (change xxxxx by the tag slug) */
			.tag-xxxxx {}
			
			/* Color hAtom compliance */
			.hentry {}
			
		</style>
		<?php
	}

 
}
 
// instantiate plugin's class
$GLOBALS['color_my_posts'] = new rc_color_my_posts();