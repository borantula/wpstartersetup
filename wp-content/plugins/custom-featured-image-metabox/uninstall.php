<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   Custom Featured Image Metabox
 * @author    1fixdotio <1fixdotio@gmail.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 1Fix.io
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once( plugin_dir_path( __FILE__ ) . 'public/class-custom-featured-image-metabox.php' );

$plugin = Custom_Featured_Image_Metabox::get_instance();
$post_types = $plugin->supported_post_types();
foreach ( $post_types as $pt ) {
	delete_option( $plugin->get_plugin_slug() . '_' . $pt );
}

delete_option( 'cfim-display-activation-message' );
/**
 * @todo Delete options in whole network
 */