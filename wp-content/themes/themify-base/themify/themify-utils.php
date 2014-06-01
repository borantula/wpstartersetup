<?php
/**
 * 
 * Created by themify
 * @since 1.0.0
 */

//////////////////////////////////////////////////////////////////////////
// Settings API
//////////////////////////////////////////////////////////////////////////

/**
 * Create admin menu links
 */
function themify_admin_nav() {
	$theme = wp_get_theme();
	$theme_name = $theme->display( 'Name' );
	$docs_name = sprintf( __( '%s Docs', 'themify' ), $theme->display( 'Name' ) );
	add_theme_page( $theme_name, $theme_name, 'manage_options', 'themify', 'themify_page' );
	add_theme_page( $docs_name, $docs_name, 'manage_options', 'themify_docs', 'themify_docs' );
}

/**
 * Themify docs admin menu entry
 */
function themify_docs() {
	exit;
}

/**
 * Redirect to Themify documentation page
 */
function themify_docs_redirect() {
	global $pagenow;
	if ( 'themes.php' == $pagenow && isset( $_GET['page'] ) && 'themify_docs' == $_GET['page'] ) {
		wp_redirect( 'http://themify.me/docs', 301 );
		exit;
	}
}
add_action( 'after_setup_theme', 'themify_docs_redirect' );

/**
 * Render settings page
 */
function themify_page() {
	if ( !current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to update this site.', 'themify' ) );
	}

	$theme = wp_get_theme();
	$theme_name = ( is_child_theme() ) ? $theme->parent()->Name : $theme->display( 'Name' );
	$theme_version = ( is_child_theme() ) ? $theme->parent()->Version : $theme->display( 'Version' );

	?>
	<h2><?php printf( __( '%s Settings', 'themify' ), $theme_name ); ?></h2>

	<form action="options.php" method="post">
		<?php settings_fields( 'themify_settings' ); ?>
		<?php do_settings_sections( 'themify' ); ?>
		<p>
			<input name="Submit" type="submit" value="<?php esc_attr_e( 'Save Changes' ); ?>" class="button-primary"/>
		</p>
	</form>

	<?php
}

/**
 * Render settings fields
 */
function themify_settings_admin_init() {

	register_setting( 'themify_settings', 'themify_settings' );

	add_settings_section( 'themify_main', __( 'Main Settings', 'themify' ), 'themify_settings_main', 'themify' );

	$fields = themify_settings_config();

	foreach( $fields as $id => $field ) {
		$args = isset( $field['args'] ) ? $field['args'] : array();
		$args['field_id'] = $id;
		add_settings_field( $id, $field['name'], 'themify_setting_field_'.$field['type'], 'themify', 'themify_main', $args );
	}
}

/**
 * Section description.
 */
function themify_settings_main() {
	echo __( 'Layout and other general options', 'themify' );
}

/**
 * Get setting.
 *
 * @param string $key Setting key to query.
 * @param string $default Default value if setting doesn't exist.
 * @return mixed|bool
 */
function themify_get( $key = '', $default = '' ) {
	global $themify_settings;
	if ( isset( $themify_settings[$key] ) ) {
		return $themify_settings[$key];
	} elseif ( '' != $default ) {
		return $default;
	}
	return false;
}

/**
 *
 */
function themify_admin_enqueue_assets() {
	// Enqueue CSS files
	wp_enqueue_style( 'themify-admin-styles', THEMIFY_URI . '/css/themify-ui.css' );

	// Enqueue Javascript files
	wp_enqueue_script( 'themify-admin-scripts', THEMIFY_URI . '/js/scripts.js', array( 'jquery' ), THEMIFY_VERSION );
}

//////////////////////////////////////////////////////////////////////////
// Utility Functions
//////////////////////////////////////////////////////////////////////////

/**
 * Add Themify Settings link to admin bar
 * @since 1.0.0
 */
function themify_admin_bar() {
	global $wp_admin_bar;
	if ( !is_super_admin() || !is_admin_bar_showing() )
		return;
	$wp_admin_bar->add_menu( array(
		'id' => 'themify-settings',
		'parent' => 'appearance',
		'title' => __( 'Themify Settings', 'themify' ),
		'href' => admin_url( 'admin.php?page=themify' )
	));
}

/**
 * Checks if Woocommerce plugin is active and returns the proper value
 * @return bool
 * @since 1.0.0
 */
function themify_is_woocommerce_active() {
	$plugin = 'woocommerce/woocommerce.php';
	$network_active = false;
	if ( is_multisite() ) {
		$plugins = get_site_option( 'active_sitewide_plugins' );
		if ( isset( $plugins[$plugin] ) )
			$network_active = true;
	}
	return in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || $network_active;
}

if ( ! function_exists( 'themify_site_title' ) ) {
	/**
	 * Returns markup for site name
	 * @param string $location
	 * @return mixed|void
	 */
	function themify_site_title( $location = 'site-logo' ) {
		$site_name = get_bloginfo( 'name' );
		$image = get_theme_mod( $location . '_image' );
		$html = '<h1 id="' . $location . '" class="' . $location . '">';
		$html .= '<a href="' . apply_filters( 'themify_logo_home_url', home_url() ) . '" title="' . $site_name . '">';
		if ( $image ) {
			$html .= '<img src="' . $image . '" />';
			$html .= '<span style="display:none">' . $site_name . '</span>';
		} else {
			$html .= '<span>' . $site_name . '</span>';
		}
		$html .= '</a>';
		$html .= '</h1>';
		return apply_filters( 'themify_' . $location . '_logo_html', $html, $location );
	}
}

if ( ! function_exists( 'themify_get_category_description' ) ) {
	/**
	 * Returns taxonomy term description.
	 * @return string
	 */
	function themify_get_category_description() {
		$description = term_description();
		return ! empty( $description ) ? '<div class="category-description">' . $description . '</div>' : '';
	}
}

/**
 * Echoes page navigation
 *
 * @param string $before Markup to show before pagination links.
 * @param string $after Markup to show after pagination links.
 * @param bool   $query WordPress query object to use.
 * @uses themify_get_pagenav
 * @since 1.0.0
 */
function themify_pagenav( $before = '', $after = '', $query = false ) {
	echo themify_get_pagenav( $before, $after, $query );
}

if ( ! function_exists( 'themify_get_pagenav' ) ) {
	/**
	 * Returns page navigation.
	 *
	 * @param string $before Markup to show before pagination links.
	 * @param string $after Markup to show after pagination links.
	 * @param bool   $query WordPress query object to use.
	 * @return string
	 */
	function themify_get_pagenav( $before = '', $after = '', $query = false ) {
		global $wpdb, $wp_query;

		if ( false == $query ) {
			$query = $wp_query;
		}
		$request = $query->request;
		$posts_per_page = intval( get_query_var( 'posts_per_page' ) );
		$paged = intval( get_query_var( 'paged' ) );
		$numposts = $query->found_posts;
		$max_page = $query->max_num_pages;

		if ( empty( $paged ) || $paged == 0 ) {
			$paged = 1;
		}
		$pages_to_show = apply_filters( 'themify_filter_pages_to_show', 5 );
		$pages_to_show_minus_1 = $pages_to_show - 1;
		$half_page_start = floor( $pages_to_show_minus_1 / 2 );
		$half_page_end = ceil( $pages_to_show_minus_1 / 2 );
		$start_page = $paged - $half_page_start;
		if ( $start_page <= 0 ) {
			$start_page = 1;
		}
		$end_page = $paged + $half_page_end;
		if ( ( $end_page - $start_page ) != $pages_to_show_minus_1 ) {
			$end_page = $start_page + $pages_to_show_minus_1;
		}
		if ( $end_page > $max_page ) {
			$start_page = $max_page - $pages_to_show_minus_1;
			$end_page = $max_page;
		}
		if ( $start_page <= 0 ) {
			$start_page = 1;
		}
		$out = '';
		if ( $max_page > 1 ) {
			$out .= $before . '<div class="pagenav clearfix">';
			if ( $start_page >= 2 && $pages_to_show < $max_page ) {
				$first_page_text = "&laquo;";
				$out .= '<a href="' . get_pagenum_link() . '" title="' . $first_page_text . '" class="number">' . $first_page_text . '</a>';
			}
			if ( $pages_to_show < $max_page ) {
				$out .= get_previous_posts_link( '&lt;' );
			}
			for ( $i = $start_page; $i <= $end_page; $i ++ ) {
				if ( $i == $paged ) {
					$out .= ' <span class="number current">' . $i . '</span> ';
				} else {
					$out .= ' <a href="' . get_pagenum_link( $i ) . '" class="number">' . $i . '</a> ';
				}
			}
			if ( $pages_to_show < $max_page ) {
				$out .= get_next_posts_link( '&gt;' );
			}
			if ( $end_page < $max_page ) {
				$last_page_text = "&raquo;";
				$out .= '<a href="' . get_pagenum_link( $max_page ) . '" title="' . $last_page_text . '" class="number">' . $last_page_text . '</a>';
			}
			$out .= '</div>' . $after;
		}
		return $out;
	}
}

/**
 * Outputs footer text
 * @param string $block The block of text this is.
 * @param string $date_fmt Date format for year shown.
 * @param bool $echo Whether to echo or return the markup.
 * @return string $html The markup and text.
 */
function themify_the_footer_text( $block = 'one', $date_fmt = 'Y', $echo = true ) {

	if ( 'one' == $block ) {
		$text = '&copy; <a href="' . home_url() . '">' . get_bloginfo( 'name' ) . '</a> ' . date( $date_fmt );
	} elseif ( 'two' == $block ) {
		$text = sprintf( __( 'Powered by <a href="%s">WordPress</a> &bull; <a href="%s">Themify WordPress Themes</a>', 'themify' ), 'http://wordpress.org', 'http://themify.me' );
	}

	$html = '<div class="' . $block . '">' . apply_filters( 'themify_the_footer_text_' . $block, $text ) . '</div>';
	$html = apply_filters( 'themify_the_footer_text', $html, $block );

	if ( $echo ) {
		echo $html;
	}
	return $html;
}

/**
 * Returns a list of web safe fonts
 * @param bool $only_names Whether to return only the array keys or the values as well
 * @return mixed|void
 * @since 1.0.0
 */
function themify_get_web_safe_fonts($only_names = false) {
	$web_safe_font_names = array(
		'Arial, Helvetica, sans-serif',
		'Verdana, Geneva, sans-serif',
		'Georgia, \'Times New Roman\', Times, serif',
		'\'Times New Roman\', Times, serif',
		'Tahoma, Geneva, sans-serif',
		'\'Trebuchet MS\', Arial, Helvetica, sans-serif',
		'Palatino, \'Palatino Linotype\', \'Book Antiqua\', serif',
		'\'Lucida Sans Unicode\', \'Lucida Grande\', sans-serif'
	);

	if( ! $only_names ) {
		$web_safe_fonts = array();
		foreach( $web_safe_font_names as $font ) {
			$web_safe_fonts[str_replace( '\'', '"', $font )] = $font;
		}
	} else {
		$web_safe_fonts = $web_safe_font_names;
	}

	return apply_filters( 'themify_get_web_safe_fonts', $web_safe_fonts );
}

if ( ! function_exists( 'themify_is_touch' ) ) {
	/**
	 * Returns true if it's a phone or tablet
	 * @param string $check What to check, all, phone or tablet.
	 * @return bool
	 */
	function themify_is_touch( $check = 'all' ) {
		global $themify_mobile_detect;
		switch ( $check ) {
			case 'phone':
				return $themify_mobile_detect->isMobile() && ! $themify_mobile_detect->isTablet();
				break;
			case 'tablet':
				return $themify_mobile_detect->isTablet();
				break;
		}
		return $themify_mobile_detect->isMobile();
	}
}

/**
 * Check if the site is using an HTTPS scheme and returns the proper url
 * @param string $url The requested to set its scheme.
 * @return string
 */
function themify_https_esc( $url = '' ) {
	if ( is_ssl() ) {
		$url = str_replace( 'http://', 'https://', $url );
	}
	return $url;
}

/**
 * Registers footer sidebars.
 * @param array $columns Sets of sidebars that can be created.
 * @param array $widget_attr General markup for widgets.
 * @param string $widgets_key Theme settings key to use.
 * @param string $default_set Set of widgets to create.
 */
function themify_register_grouped_widgets( $columns = array(), $widget_attr = array(), $widgets_key = 'setting-footer_widgets', $default_set = 'footerwidget-3col' ) {

	if ( empty( $columns ) ) {
		$columns = array(
			'footerwidget-4col' => 4,
			'footerwidget-3col' => 3,
			'footerwidget-2col' => 2,
			'footerwidget-1col' => 1,
			'none'              => 0
		);
	}
	$option = themify_get( $widgets_key, $default_set );

	if ( empty( $widget_attr ) ) {
		$widget_attr = array(
			'sidebar_name'  => __( 'Footer Widget', 'themify' ),
			'sidebar_id'    => 'footer-widget',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="widgettitle">',
			'after_title'   => '</h4>',
		);
	}
	extract( $widget_attr );

	for ( $x = 1; $x <= $columns[$option]; $x ++ ) {
		register_sidebar( array(
			'name'          => $sidebar_name . ' ' . $x,
			'id'            => $sidebar_id . '-' . $x,
			'before_widget' => $before_widget,
			'after_widget'  => $after_widget,
			'before_title'  => $before_title,
			'after_title'   => $after_title,
		));
	}
}

if ( ! function_exists( 'themify_lightbox_vars_init' ) ) {
	/**
	 * Post Gallery lightbox/fullscreen and single lightbox definition
	 *
	 * @return array Lightbox/Fullscreen galleries initialization parameters
	 */
	function themify_lightbox_vars_init() {
		$lightbox_content_images = themify_get( 'setting-lightbox_content_images' );
		$gallery_lightbox = themify_get( 'setting-gallery_lightbox' );
		$lightboxSelector = '.lightbox';
		$overlay_args = array();
		$file_extensions = array( 'jpg', 'gif', 'png', 'JPG', 'GIF', 'PNG', 'jpeg', 'JPEG' );
		$content_images = '';
		$gallery_selector = '';
		foreach ( $file_extensions as $ext ) {
			$content_images .= '.post-content a[href$=' . $ext . '],.page-content a[href$=' . $ext . '],';
			$gallery_selector .= '.gallery-icon > a[href$=' . $ext . '],';
		}
		$content_images = substr( $content_images, 0, - 1 );
		$gallery_selector = substr( $gallery_selector, 0, - 1 );

		// Include Magnific style and script
		wp_enqueue_style( 'magnific', THEMIFY_URI . '/css/lightbox.css' );
		wp_enqueue_script( 'magnific', THEMIFY_URI . '/js/lightbox.js', array( 'jquery' ), false, true );

		// Lightbox default settings
		$overlay_args = array(
			'lightboxSelector'              => $lightboxSelector,
			'lightboxOn'                    => true,
			'lightboxContentImages'         => '' == $lightbox_content_images ? false : true,
			'lightboxContentImagesSelector' => $content_images,
			'theme'                         => apply_filters( 'themify_overlay_gallery_theme', 'pp_default' ),
			'social_tools'                  => false,
			'allow_resize'                  => true,
			'show_title'                    => false,
			'overlay_gallery'               => false,
			'screenWidthNoLightbox'         => 600,
			'deeplinking'                   => false,
			'contentImagesAreas'            => '.post, .type-page, .type-highlight, .type-slider'
		);

		// If user selected lightbox or is a new install/reset
		if ( 'lightbox' == $gallery_lightbox || 'prettyphoto' == $gallery_lightbox || null == $gallery_lightbox ) {
			$overlay_args['gallerySelector'] = $gallery_selector;
			$overlay_args['lightboxGalleryOn'] = true;

			// else if user selected fullscreen gallery
		} elseif ( 'photoswipe' == $gallery_lightbox ) {
			// Include fullscreen gallery style and script
			wp_enqueue_style( 'photoswipe', THEMIFY_URI . '/css/photoswipe.css' );
			wp_enqueue_script( 'photoswipe', THEMIFY_URI . '/js/photoswipe.js', array( 'jquery' ), false, true );

			// Parameter to handle fullscreen gallery
			$overlay_args = array_merge( $overlay_args, array(
				'fullscreenSelector' => $gallery_selector,
				'fullscreenOn'       => true
			));
		}

		return apply_filters( 'themify_gallery_plugins_args', $overlay_args );
	}
}

/**
 * Add different CSS classes to body tag.
 * Outputs:
 * 		skin name
 * 		layout
 * @param Array
 * @return Array
 * @since 1.2.2
 */
function themify_body_classes( $classes ) {

	// Add skin name
	if ( $skin = themify_get( 'skin' ) ) {
		$classes[] = 'skin-' . $skin;
	} else {
		$classes[] = 'skin-default';
	}

	// Browser classes
	global $is_gecko, $is_opera, $is_iphone, $is_IE, $is_winIE, $is_macIE;
	$is_android = stripos( $_SERVER['HTTP_USER_AGENT'], 'android' ) ? true : false;
	$is_webkit = stripos( $_SERVER['HTTP_USER_AGENT'], 'webkit' ) ? true : false;
	$is_ie10 = stripos( $_SERVER['HTTP_USER_AGENT'], 'MSIE 10' ) ? true : false;
	$is_ie9 = stripos( $_SERVER['HTTP_USER_AGENT'], 'MSIE 9' ) ? true : false;
	$is_ie8 = stripos( $_SERVER['HTTP_USER_AGENT'], 'MSIE 8' ) ? true : false;
	$is_ie7 = stripos( $_SERVER['HTTP_USER_AGENT'], 'MSIE 7' ) ? true : false;

	$is_not_ie = true;

	$browsers = array(
		'gecko'   => $is_gecko,
		'opera'   => $is_opera,
		'iphone'  => $is_iphone,
		'android' => $is_android,
		'webkit'  => $is_webkit,
		'ie'      => $is_IE,
		'iewin'   => $is_winIE,
		'iemac'   => $is_macIE,
		'ie10'    => $is_ie10,
		'ie9'     => $is_ie9,
		'ie8'     => $is_ie8,
		'ie7'     => $is_ie7
	);

	foreach ( $browsers as $browser => $state ) {
		if ( $state ) {
			$classes[] = $browser;
			if ( stripos( $browser, 'ie' ) !== false ) {
				$is_not_ie = false;
			}
		}
	}
	if ( $is_not_ie ) {
		$classes[] = 'not-ie';
	}

	$layout = themify_get_sidebar_layout();

	// If still empty, set default
	if ( apply_filters( 'themify_default_layout_condition', '' == $layout ) ) {
		$layout = apply_filters( 'themify_default_layout', 'sidebar1' );
	}
	$classes[] = $layout;

	return apply_filters( 'themify_body_classes', $classes );
}

/**
 * Return sidebar layout.
 * @return bool|mixed
 */
function themify_get_sidebar_layout() {
	$layout = 'sidebar1';
	if ( is_page() ) {
		// It's a page
		$layout = themify_get( 'setting-default_page_layout', 'sidebar1' );
	} elseif ( is_single() ) {
		// It's a post
		$layout = themify_get( 'setting-default_page_post_layout', 'sidebar1' );
	} else {
		// Add default layout and post layout
		$layout = themify_get( 'setting-default_layout' );
	}
	return $layout;
}

/**
 * Add JavaScript files if IE version is lower than 9
 */
function themify_ie_enhancements() {
	echo '
	<!-- media-queries.js -->
	<!--[if lt IE 9]>
		<script src="' . THEME_URI . '/js/respond.js"></script>
	<![endif]-->

	<!-- html5.js -->
	<!--[if lt IE 9]>
		<script src="' . themify_https_esc( 'http://html5shim.googlecode.com/svn/trunk/html5.js' ) . '"></script>
	<![endif]-->
	';
}

/**
 * Add viewport tag for responsive layouts
 */
function themify_viewport_tag() {
	echo "\n" . '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">' . "\n";
}

/**
 * Make IE behave like a standards-compliant browser
 */
function themify_ie_standards_compliant() {
	echo '
	<!--[if lt IE 9]>
	<script src="' . themify_https_esc( 'http://s3.amazonaws.com/nwapi/nwmatcher/nwmatcher-1.2.5-min.js' ) . '"></script>
	<script type="text/javascript" src="' . themify_https_esc( 'http://cdnjs.cloudflare.com/ajax/libs/selectivizr/1.0.2/selectivizr-min.js' ) . '"></script>
	<![endif]-->
	';
}