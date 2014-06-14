<?php
/**
* @package wpdion
* Template Name: Contact
*/

get_header(); ?>

<?php
/**
 * MODEL AREA
 * Put any query, variable, calcution etc. here.
 * Unless you really have no other option, you can do within content part
 */


?>	

<?php while ( have_posts() ) : the_post(); ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		
	</article>
<?php endwhile; // end of the loop. ?>


<?php get_sidebar(); ?>
<?php get_footer(); ?>
