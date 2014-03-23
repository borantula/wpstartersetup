<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    // not defined, abort
    exit ();
} 
// it was defined, now delete
delete_option('_disable_updates');