<?php

namespace Dion;
/**
 * Includes standart theme functions, theme styles, scripts for both 
 * frontend and backend
 *
 * @package wpdion
 */
class ThemeSetup {


	public static $instance;

	/**
	 * Constructor. add_action and add_filters for class are here
	 *
	 * @access public
	 */
	private function __construct()
	{
		//check version first
		$this->checkPhpVersion();

		add_action('after_setup_theme',array($this,'themeSetup'));

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'adminStyles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'adminScripts' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );


		add_action( 'widgets_init', array( $this, 'widgets' ) );


	}


	public static function getInstance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	/**
	* Standart wordpress theme setup functions
	*
	*/
	public function themeSetup()
	{

		// This theme styles the visual editor with editor-style.css to match the theme style.
		add_editor_style();

		// This theme uses wp_nav_menu() in one location.
		register_nav_menu( 'primary', __( 'Primary Menu', DION_THEME_SLUG ) );

		// This theme uses a custom image size for featured images, displayed on "standard" posts.
		add_theme_support( 'post-thumbnails' );
		

		/**
		 * Make theme available for translation
		 * Translations can be filed in the /languages/ directory
		 * If you're building a theme based on _s, use a find and replace
		 * to change '_s' to the name of your theme in all the template files
		 */
		load_theme_textdomain( DION_THEME_SLUG, get_template_directory() . '/languages' );

		/**
		 * Add default posts and comments RSS feed links to head
		 */
		add_theme_support( 'automatic-feed-links' );


		/**
		 * More menus can be added if necessary
		 */
		register_nav_menus( array(
			'primary' => __( 'Primary Menu', DION_THEME_SLUG ),
		) );

		/**
		 * Enable support for Post Formats
		 */
		add_theme_support( 'post-formats', array( 'aside', 'image', 'video', 'quote', 'link' ) );


	}
	/**
	* Theme CSS Files
	*/
	public function styles()
	{
		wp_enqueue_style( '_s-style', get_stylesheet_uri() );
		wp_enqueue_style('main-css',DION_THEME_URL.'/css/main.css');
	}

	/**
	* Admin CSS Files
	*/
	public function adminStyles()
	{

	}


	/*
	* Theme JS Files
	*
	* uses: wp_register_script & wp_enqueue_script
	* If any javascript (jquery, backbone, plupload etc.) that is shipped with
	* WordPress is going to be used, should be used from the wordpress core
	* see: http://codex.wordpress.org/Function_Reference/wp_enqueue_script#Default_Scripts_Included_and_Registered_by_WordPress
	*/
	public function scripts()
	{

		wp_enqueue_script('jquery');

		// If using the regular comments of wordpress
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}

	/**
	* Admin JS files
	*/
	public function adminScripts()
	{
		
	}

	/**
	 * Widgets
	 * 
	 */
	function widgets() {
		register_sidebar( array(
			'name'          => __( 'Sidebar', '_s' ),
			'id'            => 'sidebar-1',
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h1 class="widget-title">',
			'after_title'   => '</h1>',
		) );
	}

	//it should be at least 5.3.7
	function checkPhpVersion()
	{
		$version = explode('.', PHP_VERSION);

		$message =  'Php version must be at least 5.3.7 for wpdiontheme to work. Please upgrade.';

		if (strnatcmp(phpversion(),'5.3.7') >= 0) 
	    { 
	    	// do nothing
	    } 
	    else 
	    { 
			die($message); 
	    }  
	}

	


}

