<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package _s
 */
?>
	
<aside>
	<?php do_action( 'before_sidebar' ); ?>
	<?php if ( ! dynamic_sidebar( 'sidebar-1' ) ) : ?>

	<?php endif; // end sidebar widget area ?>
</aside><!-- #secondary -->
