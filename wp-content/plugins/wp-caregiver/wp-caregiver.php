<?php
/*
 * Plugin Name: WP Caregiver
 * Plugin URI: 
 * Description: With this plugin you can turn on/off some services of Wordpress or even you can customize it too.
 * Version: 0.3.0
 * Author: Blintux
 * Author URI: http://blintdesign.hu
 * License: GPL2
 * Text Domain: wpcg
 * Domain Path: /languages
 */

require_once( dirname( __FILE__ ) . '/class-functions.php' );

class WPCG extends WPCG_Functions {	

	private $wpcg_version			= '0.3.0';
	
	public $info_page_key			= 'wpcg_info_page';
	public $frontend_settings_key	= 'wpcg_frontend_settings';
	public $backend_settings_key	= 'wpcg_backend_settings';
	public $plugin_options_key		= 'wpcg_plugin_options';
	public $plugin_settings_tabs	= array();
	
	/*
	 * The constructor
	 */
	function __construct() {

		load_plugin_textdomain( 'wpcg', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		
		add_action( 'init', array( &$this, 'load_settings' ) );
		add_action( 'init', array( &$this, 'do_settings_frontend' ) );
		
		if ( is_admin() ) {
			add_action( 'init', array( &$this, 'do_settings_backend' ) );
			
			add_action( 'admin_action_wpcg_revsdel', array( &$this, 'wpcg_delete_revisions' ) );
			add_action( 'admin_action_wpcg_disc_usage', array( &$this, 'wpcg_info_disc_usage' ) );
		}
		
		if ( current_user_can('manage_options') ) {
			add_action( 'admin_enqueue_scripts', array( &$this, 'wpcg_backend_script' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'wpcg_backend_style' ) );
			
			add_action( 'admin_init', array( &$this, 'init_info_page' ) );
			add_action( 'admin_init', array( &$this, 'register_frontend_settings' ) );
			add_action( 'admin_init', array( &$this, 'register_backend_settings' ) );
			add_action( 'admin_menu', array( &$this, 'add_admin_menus' ) );
		}
	}
	
	/*
	 * Loads settings
	 */
	function load_settings() {
		$this->frontend_settings = (array) get_option( $this->frontend_settings_key );
		$this->backend_settings = (array) get_option( $this->backend_settings_key );
		
		// Frontend options (merge with defaults)
		$this->frontend_settings = array_merge( array(
			'wp_generator'     => '',
			'rsd_link'         => '',
			'wlw_link'         => '',
			'post_rel_link'    => '',
			'canonical'        => '',
			'rss_links'        => '',
			'adminbar'         => '',
			'comment_url'      => '',
			'comment_tags'     => '',
			'authenticator'    => '',
			'maintenance'      => '',
			'maintenance_text' => __('This site is undergoing maintenance. Please check back later.', 'wpcg'),
			'og_status'			=> '',
			'og_thumb'			=> 'http://',
		), $this->frontend_settings );
	
		// Backend options (merge with defaults)
		$this->backend_settings = array_merge( array(
			'core_update'     => '',
			'plugin_update'   => '',
			'theme_update'    => '',
			'login_error'     => '',
			'advsettings'     => '',
			'thumbnails'      => '',
			'linkmanager'     => '',
			'excerpt_page'    => '',
			'excerpt_tinymce' => '',
			'show_post_id'    => '',
			'page_category'   => '',
			'custom_wpmail'   => '',
			'sender_name'     => get_bloginfo('name'),
			'sender_email'    => get_bloginfo('admin_email'),			
			'footer_ver'      => '',
			'footer_text'     => '',
			'adminbar'        => '',
			'custom_error_text' => 'Wrong username or password!'
		), $this->backend_settings );
	}

	/*
	 * Apply the frontend settings
	 */
	function do_settings_frontend() {
		
		// Theme Head (WP Head)
				
		if ( $this->frontend_settings['wp_generator'] ) {
			remove_action('wp_head', 'wp_generator');
		}
		
		if ( $this->frontend_settings['rsd_link'] ) {
			remove_action('wp_head', 'rsd_link');
		}
		
		if ( $this->frontend_settings['wlw_link'] ) {
			remove_action('wp_head', 'wlwmanifest_link');
		}
		
		if ( $this->frontend_settings['post_rel_link'] ) {
			remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head');
		}
		
		if ( $this->frontend_settings['canonical'] ) {
			remove_action('wp_head', 'rel_canonical');
		}
		
		if ( $this->frontend_settings['rss_links'] ) {
			remove_action( 'wp_head', 'feed_links_extra', 3 );
			remove_action( 'wp_head', 'feed_links', 2 );
		}

		// Theme layout
		
		if ( $this->frontend_settings['adminbar'] ) {
			show_admin_bar(false);
		}

		if ( $this->frontend_settings['comment_url'] ) {
			add_filter('comment_form_default_fields', array(&$this, 'wpcg_remove_comment_url'));
		}
		
		if ( $this->frontend_settings['comment_tags'] ) {
			add_filter('comment_form_defaults', array(&$this, 'wpcg_remove_comment_tags'));
		}

		if ( $this->backend_settings['login_error'] ) {
			$text = $this->backend_settings['custom_error_text'];
			add_filter('login_errors', create_function ('$text', "return \"$text\" ;" ) );
		}

		if ( $this->frontend_settings['maintenance'] ) {
			$text = $this->frontend_settings['maintenance_text'];
			$this->wpcg_quick_maintenance ($text);
		}

		if ( $this->frontend_settings['authenticator'] ) {
			$this->wpcg_authenticator();
		}
		
		// Open Graph
		
		if ( $this->frontend_settings['og_status'] ) {
			add_filter('language_attributes', array(&$this, 'wpcg_og_doctype') );
			add_action('wp_head', array(&$this, 'wpcg_og_head'), 5 );
		}

	}
	
	/*
	 * Apply the backend settings
	 */
	function do_settings_backend() {
		
		// Notifications
		
		if ( $this->backend_settings['core_update'] ) {
			add_filter ('pre_site_transient_update_core', create_function ('$a', "return null;"));
		}
		
		if ( $this->backend_settings['plugin_update'] ) {
			remove_action ('load-update-core.php', 'wp_update_plugins');
			add_filter ('pre_site_transient_update_plugins', create_function ('$a', "return null;"));
		}

		if ( $this->backend_settings['theme_update'] ) {
			remove_action ('load-update-core.php', 'wp_update_themes');
			add_filter ('pre_site_transient_update_themes', create_function ('$a', "return null;"));
		}
		
		// Features
		
		if ( $this->backend_settings['advsettings'] ) {
			add_action('admin_menu', create_function ('$a', 
			"add_options_page(__('All Settings'), __('All Settings'), 'administrator', 'options.php');" ));
   		}

		if ( $this->backend_settings['thumbnails'] ) {
   			add_theme_support( 'post-thumbnails' );
		}
		
		if ( $this->backend_settings['linkmanager'] ) {
   			add_filter( 'pre_option_link_manager_enabled', '__return_true' );
		}
   		
		if ( $this->backend_settings['excerpt_page'] ) {
			add_post_type_support( 'page', 'excerpt' );
		}
		
		if ( $this->backend_settings['excerpt_tinymce'] ) {
			add_action( 'admin_head-post.php', array(&$this, 'wpcg_tinymce_excerpt_js'));
			add_action( 'admin_head-post-new.php', array(&$this, 'wpcg_tinymce_excerpt_js'));
			add_action( 'admin_head-post.php', array(&$this, 'wpcg_tinymce_excerpt_css'));
			add_action( 'admin_head-post-new.php', array(&$this, 'wpcg_tinymce_excerpt_css'));	
		    add_action( 'excerpt_edit_pre', array(&$this, 'wpcg_tinymce_excerpt_edit'));
		}
		
		if ( $this->backend_settings['show_post_id'] ) {
			add_filter('manage_posts_columns', array(&$this, 'wpcg_posts_columns_id'), 5);
			add_action('manage_posts_custom_column', array(&$this, 'wpcg_posts_custom_id_columns'), 5, 2);
			add_filter('manage_pages_columns', array(&$this, 'wpcg_posts_columns_id'), 5);
			add_action('manage_pages_custom_column', array(&$this, 'wpcg_posts_custom_id_columns'), 5, 2);
		}
		
		if ( $this->backend_settings['page_category'] ) {
			$this->wpcg_page_category();
		}
		
		if ( $this->backend_settings['custom_wpmail'] ) {
			$name  = $this->backend_settings['sender_name'];
			$email = $this->backend_settings['sender_email'];
			add_filter('wp_mail_from', create_function ('$email', "return \"$email\" ;" ) );
			add_filter('wp_mail_from_name', create_function ('$name', "return \"$name\" ;" ) );
		}
		
		// Admin customize
		
		if ( $this->backend_settings['footer_ver'] ) {
			add_filter( 'update_footer', create_function ('$a', "return null;" ), 9999);
		}
		
		if ( $this->backend_settings['footer_text'] ) {
			$text = $this->backend_settings['footer_text'];
			add_filter('admin_footer_text', create_function ('$text', "return \"$text\" ;" ) );
		}
	}

	/*
	 * Register the information page tab
	 */
	function init_info_page() {
		$this->plugin_settings_tabs[$this->info_page_key] = __('Information', 'wpcg');
	}
	
	/*
	 * Registers the frontend settings
	 */
	function register_frontend_settings() {
		$this->plugin_settings_tabs[$this->frontend_settings_key] = __('Frontend', 'wpcg');
		
		register_setting( $this->frontend_settings_key, $this->frontend_settings_key );
		
		add_settings_section( 'section_frontend', __('Theme tweaks', 'wpcg'), 
			array( &$this, 'section_frontend_desc' ), $this->frontend_settings_key );
			
		add_settings_field( 'wp_head', __('Theme Head', 'wpcg'), 
			array( &$this, 'fields_frontend_head' ), $this->frontend_settings_key, 'section_frontend' );
			
		add_settings_field( 'theme_layout', __('Theme layout', 'wpcg'),
			array( &$this, 'fields_frontend_layout' ), $this->frontend_settings_key, 'section_frontend' );
			
		add_settings_field( 'open_graph', __('Open Graph Meta', 'wpcg'),
			array( &$this, 'fields_frontend_og' ), $this->frontend_settings_key, 'section_frontend' );
	}
	
	/*
	 * Registers the backend settings
	 */
	function register_backend_settings() {
		$this->plugin_settings_tabs[$this->backend_settings_key] = __('Backend', 'wpcg');
		
		register_setting( $this->backend_settings_key, $this->backend_settings_key );
		
		add_settings_section( 'section_backend', __('Admin tweaks', 'wpcg'), 
			array( &$this, 'section_backend_desc' ), $this->backend_settings_key );
		
		add_settings_field( 'notification', __('Update notifications', 'wpcg'),
			array( &$this, 'fields_backend_notifications' ), $this->backend_settings_key, 'section_backend' );
			
		add_settings_field( 'features', __('Admin features', 'wpcg'), 
			array( &$this, 'fields_backend_features' ), $this->backend_settings_key, 'section_backend' );
			
		add_settings_field( 'wpmail', __('Customize WP Mail', 'wpcg'),
			array( &$this, 'fields_backend_wpmail' ), $this->backend_settings_key, 'section_backend' );
			
		add_settings_field( 'customize', __('Customize Admin interface', 'wpcg'), 
			array( &$this, 'fields_backend_customize' ), $this->backend_settings_key, 'section_backend' );
	}

	/*
	 * Form descriptions
	 */
	function section_frontend_desc() {
		print __('These settings overwrite the default output of the template.', 'wpcg');
	}
	function section_backend_desc() {
		print __('These settings change the core functions of Wordpress.', 'wpcg'); 
	}
	
	/*
	 * Frontend fields callbacks
	 */
	 
	// Theme head
	function fields_frontend_head() {
		$key = $this->frontend_settings_key;
		?>
		<input type="checkbox" id="gen" name="<?php print $key; ?>[wp_generator]" 
			<?php print ( $this->frontend_settings['wp_generator']) ? 'checked':'' ?> />
		<label for="gen"><?php print __('Remove generator meta tag', 'wpcg'); ?></label><br />
		
		<input type="checkbox" id="rsd" name="<?php print $key; ?>[rsd_link]" 
			<?php print ( $this->frontend_settings['rsd_link']) ? 'checked':'' ?> />
		<label for="rsd"><?php print __('Remove RSD (Really Simple Discovery ) link', 'wpcg'); ?></label><br />

		<input type="checkbox" id="wlw" name="<?php print $key; ?>[wlw_link]" 
			<?php print ( $this->frontend_settings['wlw_link']) ? 'checked':'' ?> />
		<label for="wlw"><?php print __('Remove Windows Live Writer link', 'wpcg'); ?></label><br />

		<input type="checkbox" id="rel" name="<?php print $key; ?>[post_rel_link]" 
			<?php print ( $this->frontend_settings['post_rel_link']) ? 'checked':'' ?> />
		<label for="rel"><?php print __('Remove post relational links (prev, next)', 'wpcg'); ?></label><br />
		
		<input type="checkbox" id="can" name="<?php print $key; ?>[canonical]" 
			<?php print ( $this->frontend_settings['canonical']) ? 'checked':'' ?> />
		<label for="can"><?php print __('Remove canonical link', 'wpcg'); ?></label><br />
		
		<input type="checkbox" id="rss" name="<?php print $key; ?>[rss_links]" 
			<?php print ( $this->frontend_settings['rss_links']) ? 'checked':'' ?> />
		<label for="rss"><?php print __('Remove RSS feed links', 'wpcg'); ?></label><br />
		<?php
	}

	// Theme layout
	function fields_frontend_layout() {
		$key = $this->frontend_settings_key;
		?>
		<input type="checkbox" id="abar" name="<?php print $key; ?>[adminbar]" 
			<?php print ( $this->frontend_settings['adminbar']) ? 'checked':'' ?> />
		<label for="abar"><?php print __('Remove admin bar', 'wpcg'); ?></label><br />
		
		<input type="checkbox" id="urlc" name="<?php print $key; ?>[comment_url]" 
			<?php print ( $this->frontend_settings['comment_url']) ? 'checked':'' ?> />
		<label for="urlc"><?php print __('Remove the Website URL field from the comment form', 'wpcg'); ?></label><br />
		
		<input type="checkbox" id="urlt" name="<?php print $key; ?>[comment_tags]" 
			<?php print ( $this->frontend_settings['comment_tags']) ? 'checked':'' ?> />
		<label for="urlt"><?php print __('Remove allowed HTML tags text from the comment form', 'wpcg'); ?></label><br />
		
		<br />
		<input type="checkbox" id="qma" class="toggler" name="<?php print $key; ?>[maintenance]" 
			<?php print ( $this->frontend_settings['maintenance']) ? 'checked':'' ?> />
		<label for="qma"><?php print __('Quick maintenance mode', 'wpcg'); ?></label><br />
		
		<table class="wpcg_settings_table">
			<tr>
				<td><label for="qtxt"><?php print __('Maintenance text:', 'wpcg'); ?></label><br />
					<textarea cols="60" rows="2" id="qtxt" name="<?php 
						print $this->frontend_settings_key; ?>[maintenance_text]"><?php 
						print esc_attr( $this->frontend_settings['maintenance_text'] ); ?></textarea>
				</td>
			</tr>
		</table>

		<input type="checkbox" id="auth" name="<?php print $key; ?>[authenticator]" 
			<?php print ( $this->frontend_settings['authenticator']) ? 'checked':'' ?> />
		<label for="auth"><?php print __('Force login (Redirect users to the login page)', 'wpcg'); ?></label><br />
		<?php
	}


	// Open Graph
	function fields_frontend_og() {
		$key = $this->frontend_settings_key;
		?>
		<input type="checkbox" id="og_status" class="toggler" name="<?php print $key; ?>[og_status]" 
			<?php print ( $this->frontend_settings['og_status']) ? 'checked':'' ?> />
		<label for="og_status"><?php print __('Enable Open Graph tags generator', 'wpcg'); ?></label><br />

		<table class="wpcg_settings_table">
			<tr>
				<td><label for="og_appid"><?php print __('Facebook App ID:', 'wpcg'); ?></label></td>
				<td><input type="text" id="og_appid" name="<?php print $this->frontend_settings_key; ?>[og_appid]" 
					value="<?php print esc_attr( $this->frontend_settings['og_appid'] ); ?>" size="35" /></td>
				<td><span class="description"><?php print __('Optional', 'wpcg'); ?></span></td>
			</tr>
			<tr>
				<td><label for="og_admins"><?php print __('Facebook Admins:', 'wpcg'); ?></label></td>
				<td><input type="text" id="og_admins" name="<?php print $this->frontend_settings_key; ?>[og_admins]" 
					value="<?php print esc_attr( $this->frontend_settings['og_admins'] ); ?>" size="35" /></td>
				<td><span class="description"><?php print __('Recommended', 'wpcg'); ?></span></td>
			</tr>
			<tr>
				<td><label for="upload_image"><?php print __('Default image URL:', 'wpcg'); ?></label></td>
				<td><input id="upload_image" type="text" name="<?php print $this->frontend_settings_key; ?>[og_thumb]" 
					value="<?php print esc_attr( $this->frontend_settings['og_thumb'] ); ?>" size="35" /></td>
				<td><input id="upload_image_button" class="button" type="button" 
					value="<?php print __('Upload image', 'wpcg'); ?>" /></td>
			</tr>
			<tr>
				<td colspan="3"><a href="https://developers.facebook.com/tools/debug?q=<?php bloginfo('url'); ?>" 
					target="_blank"><?php print __('Test in Facebook debuger', 'wpcg'); ?></a></td>
				<td>
			</tr>
		</table>
		<?php
	}


	
	/*
	 * Backend fields callbacks
	 */
	 
	// Notifications
	function fields_backend_notifications() {
		$key = $this->backend_settings_key;
		?>
		<input type="checkbox" id="core" name="<?php print $key; ?>[core_update]" 
			<?php print ( $this->backend_settings['core_update']) ? 'checked':'' ?> />
		<label for="core"><?php print __('Disable Wordpress update notification', 'wpcg'); ?></label><br />
		
		<input type="checkbox" id="plug" name="<?php print $key; ?>[plugin_update]" 
			<?php print ( $this->backend_settings['plugin_update']) ? 'checked':'' ?> />
		<label for="plug"><?php print __('Disable plugins update notification', 'wpcg'); ?></label><br />
		
		<input type="checkbox" id="theme" name="<?php print $key; ?>[theme_update]" 
			<?php print ( $this->backend_settings['theme_update']) ? 'checked':'' ?> />
		<label for="theme"><?php print __('Disable themes update notification', 'wpcg'); ?></label><br /><br />

		<input type="checkbox" id="loge" class="toggler" name="<?php print $key; ?>[login_error]" 
			<?php print ( $this->backend_settings['login_error']) ? 'checked':'' ?> />
		<label for="loge"><?php print __('Custom login error message', 'wpcg'); ?></label><br />
		
		<table class="wpcg_settings_table">
			<tr>
				<td><label for="cler"><?php print __('Custom message:', 'wpcg'); ?></label></td>
				<td><input type="text" id="cler" name="<?php print $this->backend_settings_key; ?>[custom_error_text]" 
					value="<?php print esc_attr( $this->backend_settings['custom_error_text'] ); ?>" size="35" />
				</td>
			</tr>
		</table>
		<?php
	}

	// Features
	function fields_backend_features() {
		$key = $this->backend_settings_key;
		?>
		<input type="checkbox" id="adst" name="<?php print $key; ?>[advsettings]" 
			<?php print ( $this->backend_settings['advsettings']) ? 'checked':'' ?> />
		<label for="adst"><?php print __('Enable "All Settings" item in Settings menu', 'wpcg'); ?></label><br />
		
		<input type="checkbox" id="link" name="<?php print $key; ?>[linkmanager]" 
			<?php print ( $this->backend_settings['linkmanager']) ? 'checked':'' ?> />
		<label for="link"><?php print __('Enable Link Manager feature', 'wpcg'); ?></label><br />
		
		<input type="checkbox" id="thb" name="<?php print $key; ?>[thumbnails]" 
			<?php print ( $this->backend_settings['thumbnails']) ? 'checked':'' ?> />
		<label for="thb"><?php print __('Enable Featured image support', 'wpcg'); ?></label><br />
		
		<input type="checkbox" id="pagx" name="<?php print $key; ?>[excerpt_page]" 
			<?php print ( $this->backend_settings['excerpt_page']) ? 'checked':'' ?> />
		<label for="pagx"><?php print __('Enable excerpt field for pages', 'wpcg'); ?></label><br />
		
		<input type="checkbox" id="exty" name="<?php print $key; ?>[excerpt_tinymce]" 
			<?php print ( $this->backend_settings['excerpt_tinymce']) ? 'checked':'' ?> />
		<label for="exty"><?php print __('Enable TinyMCE Editor for excerpt', 'wpcg'); ?></label><br />
		
		<input type="checkbox" id="pid" name="<?php print $key; ?>[show_post_id]" 
			<?php print ( $this->backend_settings['show_post_id']) ? 'checked':'' ?> />
		<label for="pid"><?php print __('Display IDs in post/page list', 'wpcg'); ?></label><br />
		
		<input type="checkbox" id="pcat" name="<?php print $key; ?>[page_category]" 
			<?php print ( $this->backend_settings['page_category']) ? 'checked':'' ?> />
		<label for="pcat"><?php print __('Enable categories for pages', 'wpcg'); ?></label><br />
		<?php
	}

	// Wordpress E-Mail
	function fields_backend_wpmail() {
		$key = $this->backend_settings_key;
		?>
		<input type="checkbox" id="wpma" class="toggler" name="<?php print $key; ?>[custom_wpmail]" 
			<?php print ( $this->backend_settings['custom_wpmail']) ? 'checked':'' ?> />
		<label for="wpma"><?php print __('Customize WP Mail', 'wpcg'); ?></label><br />
		
		<table class="wpcg_settings_table">
			<tr>
				<td><label for="snam"><?php print __('Sender name:', 'wpcg'); ?></label></td>
				<td><input type="text" id="snam" name="<?php print $this->backend_settings_key; ?>[sender_name]" 
					value="<?php print esc_attr( $this->backend_settings['sender_name'] ); ?>" size="35" />
				</td>
			</tr>
			<tr>
				<td><label for="sema"><?php print __('Sender E-mail:', 'wpcg'); ?></label></td>
				<td><input type="text" id="sema" name="<?php print $this->backend_settings_key; ?>[sender_email]" 
					value="<?php print esc_attr( $this->backend_settings['sender_email'] ); ?>" size="35" />
				</td>
			</tr>
		</table>
		<?php
	}

	// Customizations
	function fields_backend_customize() {
		$key = $this->backend_settings_key;
		?>				
		<input type="checkbox" id="fver" name="<?php print $key; ?>[footer_ver]" 
			<?php print ( $this->backend_settings['footer_ver']) ? 'checked':'' ?> />
		<label for="fver"><?php print __('Remove Wordpress version from footer', 'wpcg'); ?></label><br /><br />

		<label for="ftxt"><?php print __('Custom footer text:', 'wpcg'); ?></label><br />
		<input type="text" id="ftxt" name="<?php print $this->backend_settings_key; ?>[footer_text]" 
			value="<?php print esc_attr( $this->backend_settings['footer_text'] ); ?>" size="60" /><br />

		<?php
	}

	/*
	 * Define the admin_menu
	 */
	function add_admin_menus() {
		add_options_page( __('WP Caregiver Settings', 'wpcg'), 'WP Caregiver', 'manage_options', $this->plugin_options_key, 
			array( &$this, 'plugin_options_page' ) );
	}
	
	/*
	 * Options page rendering 
	 */
	function plugin_options_page() {
		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->info_page_key;
		?>
		<div class="wrap">
			<?php $this->plugin_options_tabs(); ?>
			<?php if ( $tab == 'wpcg_info_page' ): ?>
				<?php $this->wpcg_info_template(); ?>			
			<?php else: ?>
				<form method="post" action="options.php">
					<?php wp_nonce_field( 'update-options' ); ?>
					<?php settings_fields( $tab ); ?>
					<?php do_settings_sections( $tab ); ?>
					<?php submit_button(); ?>
				</form>
			<?php endif; ?>

			<div class="wpcg_footer">
				Wordpress Caregiver - Version <?php print $this->wpcg_version; ?>
				<a href="http://flattr.com/thing/410712/Blintdesign" target="_blank"><img 
					src="http://api.flattr.com/button/flattr-badge-large.png" alt="Flattr this" 
					title="Flattr this" border="0" /></a>
			</div>
		
		</div>
		<?php
	}
	
	/*
	 * Tabs
	 */
	function plugin_options_tabs() {
		$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->info_page_key;

		screen_icon();
		print '<h2 class="nav-tab-wrapper">';
		foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
			$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
			print '<a class="nav-tab ' . $active . '" href="?page=' . $this->plugin_options_key . '&tab=' . $tab_key . '">' . 
				$tab_caption . '</a>';	
		}
		print '</h2>';
	}

};

// Initialize the plugin
add_action( 'plugins_loaded', create_function( '', '$wpcg = new WPCG;' ) );
