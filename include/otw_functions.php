<?php
/** init plugin
  *
  */
function otw_wml_plugin_init(){
	
	global $wp_registered_sidebars, $otw_replaced_sidebars, $wp_int_items, $otw_wml_plugin_url;
	
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
	
		wp_register_style('otw_sbm.css', $otw_wml_plugin_url.'/css/otw_sbm.css' );
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

/** set item row attributes
 *
 *  @param string $node_tag
 *  @param string $wp_item_type
 *  @param string $sidebar
 *  @param string $widget
 *  @param array $wpItem
 *  @return string
 *
 */
if (!function_exists( "otw_sidebar_item_row_attributes" )){
	function otw_sidebar_item_row_attributes( $node_tag, $wp_item_type, $sidebar, $widget, $wpItem ){
		
		global $wp_registered_sidebars;
		
		$attributes = array();
		
		switch( $node_tag )
		{
			case 'p':
					$attributes['class'] = array();
					if( isset( $wp_registered_sidebars[ $sidebar ]['widgets_settings'][ $wp_item_type ]['_otw_wc'][ $widget ] ) && in_array( $wp_registered_sidebars[ $sidebar ]['widgets_settings'][ $wp_item_type ]['_otw_wc'][ $widget ], array( 'vis', 'invis' ) ) ){
						if( $wp_registered_sidebars[ $sidebar ]['widgets_settings'][ $wp_item_type ]['_otw_wc'][ $widget ] == 'invis' ){
							$attributes['class'][] = 'sitem_notselected';
						}else{
							$attributes['class'][] = 'sitem_selected';
						}
					}
					elseif( isset( $wp_registered_sidebars[ $sidebar ]['widgets_settings'][ $wp_item_type ][ otw_wp_item_attribute( $wp_item_type, 'ID', $wpItem ) ]['exclude_widgets'][ $widget ] ) ){
						$attributes['class'][] = 'sitem_notselected';
					}else{
						$attributes['class'][] = 'sitem_selected';
					}
				break;
			case 'a':
					$attributes['class'] = array();
					$attributes['class'][] = $sidebar.'|'.$widget.'|'.$wp_item_type.'|'.otw_wp_item_attribute( $wp_item_type, 'ID', $wpItem );
					switch( $wp_item_type ){
						case 'page':
						case 'category':
						case 'postsincategory':
								if( isset( $wpItem->_sub_level ) && $wpItem->_sub_level ){
									$attributes['style'][] = 'margin-left: '.( $wpItem->_sub_level * 20  ).'px';
								}
							break;
					}
				break;
		}
		
		$html = '';
		foreach( $attributes as $attribute => $att_values ){
			$html .= ' '.$attribute.'="'.implode( ' ', $att_values ).'"';
		}
		
		echo $html;
	}
}

/** set html ot each item row
  *  @param string 
  *  @param string 
  *  @param string
  *  @param array
  *  @return void
  */
if (!function_exists( "otw_sidebar_item_attributes" )){
	function otw_sidebar_item_attributes( $tag, $item_type, $item_id, $sidebar_data, $item_data ){
		
		$attributes = '';
		
		switch( $tag ){
			case 'p':
					$attributes_array = array();
					if( isset( $_POST['otw_action'] ) ){
						if( isset( $_POST[ 'otw_sbi_'.$item_type ][ $item_id ] ) || isset( $_POST[ 'otw_sbi_'.$item_type ][ 'all' ] ) ){
							$attributes_array['class'][] = 'sitem_selected';
						}else{
							$attributes_array['class'][] = 'sitem_notselected';
						}
					}else{
						if( isset( $sidebar_data['sbm_validfor'][ $item_type ]['all'] ) ){
							$attributes_array['class'][] = 'sitem_selected';
						}elseif( isset( $sidebar_data['sbm_validfor'][ $item_type ][ $item_id ] ) ){
							$attributes_array['class'][] = 'sitem_selected';
						}else{
							$attributes_array['class'][] = 'sitem_notselected';
						}
					}
					if( isset( $attributes_array['class'] ) ){
						$attributes .= ' class="'.implode( ' ', $attributes_array['class'] ).'"';
					}
				break;
			case 'c':
					if( isset( $_POST['otw_action'] ) ){
						if( isset( $_POST[ 'otw_sbi_'.$item_type ][ $item_id ] )  || isset( $_POST[ 'otw_sbi_'.$item_type ][ 'all' ] ) ){
							$attributes .= ' checked="checked"';
						}
					}else{
						if( isset( $sidebar_data['sbm_validfor'][ $item_type ]['all'] ) ){
							$attributes .= ' checked="checked"';
						}elseif( isset( $sidebar_data['sbm_validfor'][ $item_type ][ $item_id ] ) ){
							$attributes .= ' checked="checked"';
						}
					}
				break;
			case 'ap':
					if( isset( $_POST['otw_action'] ) ){
						if( isset( $_POST[ 'otw_sbi_'.$item_type ][ $item_id ] ) ){
							$attributes .= ' class="all sitem_selected"';
						}else{
							$attributes .= ' class="all sitem_notselected"';
						}
					}else{
						if( isset( $sidebar_data['sbm_validfor'][ $item_type ][ $item_id ] ) ){
							$attributes .= ' class="all sitem_selected"';
						}else{
							$attributes .= ' class="all sitem_notselected"';
						}
					}
				break;
			case 'ac':
					if( isset( $_POST['otw_action'] ) ){
						if( isset( $_POST[ 'otw_sbi_'.$item_type ][ $item_id ] ) ){
							$attributes .= ' checked="checked"';
						}
					}else{
						if( isset( $sidebar_data['sbm_validfor'][ $item_type ][ $item_id ] ) ){
							$attributes .= ' checked="checked"';
						}
					}
				break;
			case 'l':
					if( isset( $item_data->_sub_level ) && $item_data->_sub_level ){
						$attributes .= ' style="margin-left: '.( $item_data->_sub_level * 20 ).'px"';
					}
				break;
		}
		echo $attributes;
	}
}


require_once( plugin_dir_path( __FILE__ ).'otw_sbm_core.php' );
?>