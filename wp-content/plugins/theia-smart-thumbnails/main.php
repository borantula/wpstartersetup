<?php
    /*
    Plugin Name: Theia Smart Thumbnails
    Description: Gain full control over your thumbnails by customizing the cropping zone for each one of them.
    Author: Liviu Cristian Mirea Ghiban
    Version: 1.1.1
    */

/*
 * Copyright 2012, Theia Smart Thumbnails, Liviu Cristian Mirea Ghiban.
 */

/*
 * Plugin version. Used to forcefully invalidate CSS and JavaScript caches by appending the version number to the
 * filename (e.g. "style.css?ver=TPS_VERSION").
 */
define('TST_VERSION', '1.1.1');

// Include other files.
include(dirname(__FILE__) . '/TstMisc.php');

// Add hooks.
add_action('attachment_fields_to_edit', 'TstMisc::attachment_fields_to_edit', 20, 2);
add_action('attachment_fields_to_save', 'TstMisc::attachment_fields_to_save', 10, 2);
add_action('image_resize_dimensions', 'TstMisc::image_resize_dimensions', 10, 6);
add_action('wp_get_attachment_metadata', 'TstMisc::wp_get_attachment_metadata', 10, 2);
add_action('get_attached_file', 'TstMisc::get_attached_file', 10, 2);
add_action('admin_enqueue_scripts', 'TstMisc::admin_enqueue_scripts');