<?php
/**
 * Template for page view
 * @package themify
 * @since 1.0.0
 */
?>
<?php get_header(); ?>

<!-- layout-container -->
<div id="layout" class="pagewidth clearfix">	

	<?php themify_content_before(); // hook ?>
	<!-- content -->
	<div id="content" class="clearfix">
    	<?php themify_content_start(); // hook ?>
	
		<?php 
		/////////////////////////////////////////////
		// 404							
		/////////////////////////////////////////////
		if(is_404()): ?>
			<h1 class="page-title"><?php _e('404','themify'); ?></h1>	
			<p><?php _e( 'Page not found.', 'themify' ); ?></p>	
		<?php endif; ?>

		<?php 
		/////////////////////////////////////////////
		// PAGE							
		/////////////////////////////////////////////
		?>
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

			<div id="page-<?php the_ID(); ?>" class="type-page">
						
				<!-- page-title -->
				<h1 class="page-title"><?php the_title(); ?></h1>
				<!-- /page-title -->

				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="post-image">
						<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
							<?php the_post_thumbnail( 'medium' ); ?>
						</a>
					</figure>
				<?php endif; // has post thumbnail ?>

				<div class="page-content">
				
					<?php the_content(); ?>
				
					<?php wp_link_pages(array('before' => '<p><strong>'.__('Pages:','themify').'</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
					
					<?php edit_post_link(__('Edit','themify'), '[', ']'); ?>
					
					<!-- comments -->
					<?php comments_template(); ?>
					<!-- /comments -->
					
				</div>
				<!-- /.post-content -->
		
			</div><!-- /.type-page -->
		<?php endwhile; endif; ?>
        
		<?php themify_content_end(); // hook ?>
	</div>
	<!-- /content -->
    <?php themify_content_after(); // hook ?>

	<?php 
	/////////////////////////////////////////////
	// Sidebar							
	/////////////////////////////////////////////
	get_sidebar(); ?>

</div>
<!-- /layout-container -->
	
<?php get_footer(); ?>