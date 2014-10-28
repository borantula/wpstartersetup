<?php
/**
 * Custom Featured Image Metabox.
 *
 * Custom the title, content and link / button text in the Featured Image metabox.
 *
 * @package   Custom_Featured_Image_Metabox
 * @author    1fixdotio <1fixdotio@gmail.com>
 * @license   GPL-2.0+
 * @link      http://1fix.io/custom-featured-image-metabox
 * @copyright 2014 1Fix.io
 *
 * @wordpress-plugin
 * Plugin Name:       Custom Featured Image Metabox
 * Plugin URI:        http://1fix.io/custom-featured-image-metabox
 * Description:       Custom the title, content and set / remove link text in the Featured Image metabox.
 * Version:           1.0.1
 * Author:            1fixdotio
 * Author URI:        http://1fix.io
 * Text Domain:       cfim
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/1fixdotio/custom-featured-image-metabox
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'public/class-custom-featured-image-metabox.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *
 */
register_activation_hook( __FILE__, array( 'Custom_Featured_Image_Metabox', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Custom_Featured_Image_Metabox', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Custom_Featured_Image_Metabox', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-custom-featured-image-metabox-admin.php' );
	add_action( 'plugins_loaded', array( 'Custom_Featured_Image_Metabox_Admin', 'get_instance' ) );

}
