<?php

class WPCG_Functions {

	// General functions 
	
	function wpcg_backend_script() {
	    if (isset($_GET['page']) && $_GET['page'] == 'wpcg_plugin_options') {
	        wp_enqueue_media();
	        wp_register_script('backend-js', WP_PLUGIN_URL.'/wp-caregiver/backend.js', array('jquery'));
	        wp_enqueue_script('backend-js');
	    }
	}

	function wpcg_backend_style() {
	    if (isset($_GET['page']) && $_GET['page'] == 'wpcg_plugin_options') {
			wp_register_style( 'backend-style', WP_PLUGIN_URL.'/wp-caregiver/backend.css' );
			wp_enqueue_style( 'backend-style' );
	    }
	}
	
	function wpcg_content_excerpt() {
		global $post;

		$post_data = get_post( $post->ID );
	    $content = strip_tags( $post_data->post_content );
		$content = strip_shortcodes( $content );
		$content = wp_trim_words( $content, 30 );
		
		return $content;
	}
	
	function wpcg_foldersize( $path ) {
	    $total_size = 0;
	    $files = scandir( $path );
	    $cleanPath = rtrim( $path, '/' ) . '/';

	    foreach( $files as $t ) {
	        if ( '.' != $t && '..' != $t ) {
	            $currentFile = $cleanPath . $t;
	            if ( is_dir( $currentFile ) ) {
	                $size = $this->wpcg_foldersize( $currentFile );
	                $total_size += $size;
	            } else {
	                $size = filesize( $currentFile );
	                $total_size += $size;
	            }
	        }   
	    }
	    return $total_size;
	}
	
	function wpcg_format_size($size) {
	    $units = explode( ' ', 'B KB MB GB TB PB' );
	    $mod = 1024;
	
	    for ( $i = 0; $size > $mod; $i++ ) $size /= $mod;
	
	    $endIndex = strpos( $size, "." ) + 3;
	
	    return substr( $size, 0, $endIndex ) . ' ' . $units[$i];
	}

	// Functions for hooks

	function wpcg_tinymce_excerpt_js () { 
		?>
		<script type="text/javascript">
		jQuery(document).ready( tinymce_excerpt );
	            function tinymce_excerpt() {
			jQuery("#excerpt").addClass("mceEditor");
			tinyMCE.execCommand("mceAddControl", false, "excerpt");
		    }
		</script>
		<?php 
	}
	
	function wpcg_tinymce_excerpt_css () { 
		?>
		<style type='text/css'>
		    #postexcerpt .inside{margin:0;padding:0;background:#fff;}
		    #postexcerpt .inside p{padding:0px 0px 5px 10px;}
		    #postexcerpt #excerpteditorcontainer { border-style: solid; padding: 0; }
		    #postexcerpt #excerpt_ifr {min-height:300px!important;}
		</style>
		<?php 
	}
	
	function wpcg_tinymce_excerpt_edit($e){
		return nl2br($e);
	}
	 

	function wpcg_posts_columns_id ($defaults){
		$defaults['wps_post_id'] = 'ID';
		return $defaults;
	}
	
	function wpcg_page_category (){
		register_taxonomy('page_category', 'page', array(
			'hierarchical' => true,
			'show_ui' => true,
			'show_admin_column'  => true,
		));
	}
	
	function wpcg_posts_custom_id_columns ($column_name, $id){
		if($column_name === 'wps_post_id'){
			print $id;
		}
	}
	
	function wpcg_remove_comment_url ($fields) {
    	unset($fields['url']);
    	return $fields;
	}

	function wpcg_remove_comment_tags($fields) {
		$fields['comment_notes_after'] = '';
		return $fields;
	}

	function wpcg_quick_maintenance ($text) {
		if ( ( !current_user_can( 'edit_themes' ) || !is_user_logged_in() ) 
			&& !in_array( $GLOBALS['pagenow'], array( 'wp-login.php' ) ) ) {
			$login_url = wp_login_url();
			wp_die('<h1>'.$text.'</h1><p><a href="'.$login_url.'">'. __('Admin login', 'wpcg') .'</a></p>', get_bloginfo('name' ));
		}
	}
	
	function wpcg_authenticator () {
		if ( !is_user_logged_in() && !in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) ) ) {
			auth_redirect();
		}
	}
	
	// Functions for info page

	function wpcg_info_server_time() {
    	return date('d/m/Y - H:i:s');
	}
	
	function wpcg_info_memory() {
		$mem = number_format( ( memory_get_peak_usage()/1024/1024 ), 1, ',', '' ) . ' / ' . WP_MEMORY_LIMIT;
    	return $mem;
	}
	
	function wpcg_info_sql_queries() {
    	return $GLOBALS['wpdb']->num_queries;
	}
		
	function wpcg_info_revisions() {
		global $wpdb;
		$total_revisions = $wpdb->get_var(
			$wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s", 'revision')
		);
    	return $total_revisions;
	}
	
	function wpcg_info_disc_usage() {
		$raw = $this->wpcg_foldersize( ABSPATH );	
	    wp_redirect( $_SERVER['HTTP_REFERER'] . '&wpcgdu=' . $raw );
	    exit();
	}

	function wpcg_delete_revisions() {
		global $wpdb;

    	// Run a query that deletes post revisions and metadata associated with them
    	$cleanupquery = "DELETE a,b,c FROM $wpdb->posts a LEFT JOIN $wpdb->term_relationships b ON (a.ID = b.object_id) LEFT JOIN $wpdb->postmeta c ON (a.ID = c.post_id) WHERE a.post_type = 'revision';";
    	$wpdb->query( $cleanupquery );
    
    	// Optimize the tables which we altered above
    	$optimizequery = "OPTIMIZE TABLE '$wpdb->posts', '$wpdb->term_relationships', '$wpdb->postmeta';";
		$wpdb->query( $optimizequery );

	    wp_redirect( $_SERVER['HTTP_REFERER'] );
	    exit();
	}
	
	// Functions for Open Graph

	function wpcg_og_doctype( $output ) {
		return $output . ' xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml"';
	}

	function wpcg_og_thumb() {
		global $post, $posts;
		$og_thumb = '';
		
		if( is_home() ) {
			$og_thumb = $this->frontend_settings['og_thumb'];
			return $og_thumb;
		}
		
		if ((function_exists('has_post_thumbnail')) && (has_post_thumbnail())) {
			$src = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), '', '' );
			$og_thumb = $src[0];		
		} else {		
			$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
			$og_thumb = $matches[1][0];
		}
		
		if(empty($og_thumb)) {
			$og_thumb = $this->frontend_settings['og_thumb'];
		}
	  
		return $og_thumb;
	}
	
	function wpcg_og_data() {
		
		switch (1) {
			//for posts
			case (is_single()):
				$og_url = get_permalink();
				$og_desc = $this->wpcg_content_excerpt();
				break;
		
			//for pages
			case (is_page()):
				$og_url = get_permalink();
				$og_desc = $this->wpcg_content_excerpt();
				break;
		
			//for category archives
			case is_category():
				$og_url = get_bloginfo('url').get_option('category_base')."/".get_query_var('category_name');
				$og_desc = get_bloginfo('description');
				break;
		
			//for tag archives
			case is_tag():
				$og_url = get_bloginfo('url').get_option('tag_base')."/".get_query_var('tag');
				$og_desc = get_bloginfo('description');
				break;
		
			//for date archives
			case is_date():
				$og_url = get_bloginfo('url')."/".get_query_var('year')."/".zeroise(get_query_var('monthnum'), 2);
				$og_desc = get_bloginfo('description');
				break;
		
			//for search pages
			case is_search():
				$og_url = get_bloginfo('url')."/index.php?s=".get_search_query();
				$og_desc = get_bloginfo('description');
				break;
		
			//for everything else
			default:
				$og_url = get_bloginfo('url');
				$og_desc = get_bloginfo('description');
				break;
		}
		
		if ( $og_url[strlen($og_url)-1] != '/' ) $og_url .= '/';
		
		$result = array('og_url' => $og_url, 'og_desc' => $og_desc);
		
		return $result;
	}
	

	function wpcg_og_head() {

		$og_data = $this->wpcg_og_data();

	?>
	<?php if ($this->frontend_settings['og_appid']): ?>
	<meta property="fb:app_id" content="<?php print esc_attr( $this->frontend_settings['og_appid'] ); ?>" />
	<?php endif; ?>
	<?php if ($this->frontend_settings['og_admins']): ?>
	<meta property="fb:admins" content="<?php print esc_attr( $this->frontend_settings['og_admins'] ); ?>" />  
	<?php endif; ?>
	<meta property="og:title" content="<?php if(is_home()) { bloginfo('name'); } elseif(is_category()) { print single_cat_title();} elseif(is_author()) { $curauth = (get_query_var('author_name')) ? get_user_by('slug', get_query_var('author_name')) : get_userdata(get_query_var('author')); print $curauth->display_name; } else { print the_title(); } ?>" />
	<meta property="og:description" content="<?php print $og_data['og_desc']; ?>"/>
	<meta property="og:url" content="<?php print $og_data['og_url']; ?>"/>
	<meta property="og:image" content="<?php print $this->wpcg_og_thumb(); ?>"/>
	<meta property="og:type" content="<?php if (is_single() || is_page()) { print "article"; } else { print "website";} ?>"/>
	<meta property="og:site_name" content="<?php bloginfo('name'); ?>"/>
	<?php
	}


	/*
	 * Info page template
	 */
	function wpcg_info_template() {
		?>
		<br />
		<table class="widefat fixed wpcg_sysinfo">
		<thead>
			<tr>
				<th scope="col" class="first"><?php print __('System information', 'wpcg'); ?></th><th scope="col"></th>
			</tr>
		</thead>
		<tbody>
		<tr>
			<th scope="row"><?php print __('Server time', 'wpcg'); ?></th>
			<td><?php print $this->wpcg_info_server_time(); ?></td>
		</tr>
		<tr>
			<th scope="row"><?php print __('Memory usage', 'wpcg'); ?></th>
			<td><?php print $this->wpcg_info_memory(); ?></td>
		</tr>
		<tr>
			<th scope="row"><?php print __('SQL Queries', 'wpcg'); ?></th>
			<td><?php print $this->wpcg_info_sql_queries(); ?></td>
		</tr>
		<tr>
			<th scope="row"><?php print __('Post revisions', 'wpcg'); ?></th>
			<td>
				<span class="wpcg_data_space"><?php print $this->wpcg_info_revisions(); ?></span>
				<form method="POST" action="<?php print admin_url( 'admin.php' ); ?>" style="display:inline;">
				    <input type="hidden" name="action" value="wpcg_revsdel" />
				    <input type="submit" value="<?php print __('Delete All', 'wpcg'); ?>" class="button button-secondary" />
				</form>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php print __('Disc usage', 'wpcg'); ?></th>
			<td>
				<span class="wpcg_data_space"><?php print (isset($_GET['wpcgdu'])) ? $this->wpcg_format_size( $_GET['wpcgdu'] ) : __('N/A'); ?></span>
				<form method="POST" action="<?php print admin_url( 'admin.php' ); ?>" style="display:inline;">
				    <input type="hidden" name="action" value="wpcg_disc_usage" />
				    <input type="submit" value="<?php print __('Calculate', 'wpcg'); ?>" class="button button-secondary" />
				</form>
			</td>
		</tr>
		</tbody>
		</table>
		<?php
	}
}

?>