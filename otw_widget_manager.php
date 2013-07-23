<?php
/**
Plugin Name: Widget Manager Light
Plugin URI: http://otwthemes.com/?utm_source=wp.org&utm_medium=admin&utm_content=site&utm_campaign=wml
Description:  Get control over widgets visibility. You can now customize each page with specific widgets that are relative to the content on that page. No coding required.
Author: OTWthemes.com
Version: 1.7
Author URI: http://otwthemes.com/?utm_source=wp.org&utm_medium=admin&utm_content=site&utm_campaign=wml
*/
$wp_wml_int_items = array(
	'page'              => array( array(), __( 'Pages' ), __( 'All pages' ) ),
	'post'              => array( array(), __( 'Posts', 'otw_sbm' ), __( 'All posts', 'otw_sbm' ) ),
	'postsincategory'   => array( array(), __( 'All posts from category', 'otw_sbm' ), __( 'All categories', 'otw_sbm' ) ),
	'postsintag'        => array( array(), __( 'All posts from tag', 'otw_sbm' ), __( 'All tags', 'otw_sbm' ) ),
	'category'          => array( array(), __( 'Categories', 'otw_sbm' ), __( 'All categories', 'otw_sbm' ) ),
	'posttag'           => array( array(), __( 'Tags', 'otw_sbm' ), __( 'All tags', 'otw_sbm' ) ),
	'author_archive'    => array( array(), __( 'Author archives', 'otw_sbm' ), __( 'All author archives', 'otw_sbm' ) ),
	'templatehierarchy' => array( array(), __( 'Template Hierarchy', 'otw_sbm'), __( 'All templates', 'otw_sbm' ) ),
	'pagetemplate'      => array( array(), __( 'Page Templates', 'otw_sbm' ), __( 'All page templates', 'otw_sbm' ) ),
	'archive'           => array( array(), __( 'Archives', 'otw_sbm' ), __( 'All archives', 'otw_sbm' ) ),
	'userroles'         => array( array(), __( 'User roles/Logged in as', 'otw_sbm' ), __( 'All roles', 'otw_sbm' ) )
);

global $otw_plugin_options;

$otw_plugin_options = get_option( 'otw_plugin_options' );

$otw_wml_plugin_url = plugins_url( substr( dirname( __FILE__ ), strlen( dirname( dirname( __FILE__ ) ) ) ) );

require_once( plugin_dir_path( __FILE__ ).'/include/otw_functions.php' );


/** plugin options
  *
  */
function otw_wml_options(){
	require_once( 'include/otw_sidebar_options.php' );
}

/** plugin info
  *
  */
function otw_wml_sidebars_widget_dialog(){
	require_once( 'include/otw_widget_dialog.php' );
}
function otw_wml_info(){
	require_once( 'include/otw_sidebar_info.php' );
}

function otw_wml_ajax_widget_dialog(){
	require_once( 'include/otw_widget_dialog.php' );
	die;
}
/** admin menu actions
  * add the top level menu and register the submenus.
  */ 
function otw_wml_admin_actions(){
	
	global $otw_wml_plugin_url;
	
	add_menu_page('Widget Manager', 'Widget Manager', 'manage_options', 'otw-wml', 'otw_wml_options', $otw_wml_plugin_url.'/images/otw-sbm-icon.png' );
	add_submenu_page( 'otw-wml', 'Options', 'Options', 'manage_options', 'otw-wml', 'otw_wml_options' );
	add_submenu_page( 'otw-wml', 'Info', 'Info', 'manage_options', 'otw-wml-info', 'otw_wml_info' );
	add_submenu_page( __FILE__, __('Set up widget appearance', 'otw_sbm'), __('Set up widget appearance', 'otw_sbm'), 'manage_options', 'otw-wml-widget-dialog', 'otw_wml_sidebars_widget_dialog' );
}

function otw_wml_items_by_type(){
	require_once( 'include/otw_wml_items_by_type.php' );
	die;
}

/** include needed javascript scripts based on current page
  *  @param string
  */
function enqueue_wml_scripts( $requested_page ){

	global $otw_wml_plugin_url;
	
	switch( $requested_page ){
	
		case 'widgets.php':
				global $otw_plugin_options;
				
				if( isset( $otw_plugin_options['activate_appearence'] ) && $otw_plugin_options['activate_appearence'] ){
					wp_enqueue_script("otw_widgets", $otw_wml_plugin_url.'/js/otw_widgets.js' , array( 'jquery', 'jquery-ui-dialog', 'thickbox' ), '3.0' );
					wp_enqueue_script("otw_widget_appearence", $otw_wml_plugin_url.'/js/otw_widgets_appearence.js' , array( 'jquery' ), '1.2' );
					wp_enqueue_style (  'wp-jquery-ui-dialog' );
				}
			break;
		case 'admin_page_otw-wml-widget-dialog':
				wp_enqueue_script("otw_widget_appearence", $otw_wml_plugin_url.'/js/otw_widgets_appearence.js' , array( 'jquery' ), '3.0' );
			break;
	}
}

/**
 * include needed styles
 */
function enqueue_wml_styles( $requested_page ){
	global $otw_wml_plugin_url;
	wp_enqueue_style( 'otw_wml_sidebar', $otw_wml_plugin_url .'/css/otw_sbm_admin.css', array( 'thickbox' ), '1.1' );
}

/**
 * register admin menu 
 */
add_action('admin_menu', 'otw_wml_admin_actions');
add_action('admin_notices', 'otw_wml_admin_notice');
add_filter('sidebars_widgets', 'otw_sidebars_widgets');

/**
 * include plugin js and css.
 */
add_action('admin_enqueue_scripts', 'enqueue_wml_scripts');
add_action('admin_print_styles', 'enqueue_wml_styles' );

//register some admin actions
if( is_admin() ){
	add_action( 'wp_ajax_otw_wml_widget_dialog', 'otw_wml_ajax_widget_dialog' );
	add_action( 'wp_ajax_otw_wml_items_by_type', 'otw_wml_items_by_type' );
}
/** 
 *call init plugin function
 */
add_action('init', 'otw_wml_plugin_init', 102 );
?>