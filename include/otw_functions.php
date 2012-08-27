<?php
/** list of functions used by otw sidebar
  *
  */
if( !function_exists( 'dynamic_sidebar' ) ){
	function dynamic_sidebar( $index ){
		return otw_dynamic_sidebar( $index );
	}
}else{
	otw_wml_plugin_activate();
}
if( !function_exists( 'is_active_sidebar' ) ){
	function is_active_sidebar( $index ){
		return otw_is_active_sidebar( $index );
	}
}else{
	otw_wml_plugin_activate();
}
/** init plugin
  *
  */
function otw_wml_plugin_init(){
	
	global $wp_registered_sidebars, $otw_replaced_sidebars, $wp_int_items;
	
	if( is_admin() ){
		if( function_exists( 'otwrem_dynamic_sidebar' ) ){
			update_option( 'otw_wml_plugin_error', '' );
		}
	}
	
	$otw_registered_sidebars = get_option( 'otw_sidebars' );
	$otw_widget_settings = get_option( 'otw_widget_settings' );
	
	if( !is_array( $otw_widget_settings ) ){
		$otw_widget_settings = array();
		update_option( 'otw_widget_settings', $otw_widget_settings );
	}
	
	//apply validfor settings to all sidebars
	if( is_array( $wp_registered_sidebars ) && count( $wp_registered_sidebars ) ){
		foreach( $wp_registered_sidebars as $wp_widget_key => $wo_widget_data ){
		
			if( array_key_exists( $wp_widget_key, $otw_widget_settings ) ){
				$wp_registered_sidebars[ $wp_widget_key ]['widgets_settings'] = $otw_widget_settings[ $wp_widget_key ];
			}else{
				$wp_registered_sidebars[ $wp_widget_key ]['widgets_settings'] = array();
			}
		}
	}
	
	if( is_admin() ){
		require_once( plugin_dir_path( __FILE__ ).'/otw_process_actions.php' );
	}else{
	
		wp_register_style('otw_sbm.css', plugins_url( 'otw_wml/css/otw_sbm.css' ) );
		wp_enqueue_style('otw_sbm.css');
	}
}

function otw_wml_admin_notice(){
	$plugin_error = get_option( 'otw_wml_plugin_error' );
	
	if( $plugin_error ){
		echo '<div class="error"><p>';
		echo 'Widget Manager Light Plugin Error: '.$plugin_error;
		echo '</p></div>';
	}
}


require_once( plugin_dir_path( __FILE__ ).'otw_sbm_core.php' );
?>