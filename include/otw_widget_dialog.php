<?php
/** OTW widget appearence dialog
  *
  */
$sidebar = '';
$widget = '';

if( isset( $_GET['sidebar'] ) ){
	$sidebar = $_GET['sidebar'];
}
if( isset( $_GET['widget'] ) ){
	$widget = $_GET['widget'];
}

//validat input data
if( !$sidebar || !$widget ){
	wp_die( __( 'Invalid sidebar or widget' ) );
}

global $wp_registered_sidebars, $wp_int_items, $otw_wml_plugin_url;


//validate that this sidebar exists
if( !isset( $wp_registered_sidebars[ $sidebar ] ) ){
	wp_die( __( 'Requested not registered sidebar' ) );
}

$otw_sidebars = get_option( 'otw_sidebars' );

if( !is_array( $otw_sidebars ) ){
	$otw_sidebars = array();
}

$sidebar_widgets = get_option('sidebars_widgets');

//check if widget is part of this sidebar
if( !isset( $sidebar_widgets[ $sidebar ] ) || !count( $sidebar_widgets[ $sidebar ] ) || !in_array( $widget, $sidebar_widgets[ $sidebar ]  ) ){
	wp_die( __( 'Requested widget is not assinged to this sidebar' ) );
}

if( isset( $_POST['otw_action'] ) && in_array( $_POST['otw_action'], array( 'vis', 'invis' ) ) ){

	if( isset( $_POST['item_type'] ) ){
		
		$response = '';
		$otw_widget_settings = get_option( 'otw_widget_settings' );
		
		if( !isset( $otw_widget_settings[ $sidebar ] ) ){
			$otw_widget_settings[ $sidebar ] = array();
		}
		
		if( !isset( $otw_widget_settings[ $sidebar ][ $_POST['item_type'] ] ) ){
			$otw_widget_settings[ $sidebar ][ $_POST['item_type'] ] = array();
		}
		
		$current_wc = '';
		if( !isset( $otw_widget_settings[ $sidebar ][ $_POST['item_type'] ]['_otw_wc'] ) || !is_array( $otw_widget_settings[ $sidebar ][ $_POST['item_type'] ]['_otw_wc'] ) ){
			$otw_widget_settings[ $sidebar ][ $_POST['item_type'] ]['_otw_wc'] = array();
		}
		
		if( !isset( $otw_widget_settings[ $sidebar ][ $_POST['item_type'] ]['_otw_wc'][ $widget ] ) ){
			$otw_widget_settings[ $sidebar ][ $_POST['item_type'] ]['_otw_wc'][ $widget ] = '';
		}else{
			$current_wc = $otw_widget_settings[ $sidebar ][ $_POST['item_type'] ]['_otw_wc'][ $widget ];
		}
		
		if( $current_wc == $_POST['otw_action'] ){
			$otw_widget_settings[ $sidebar ][ $_POST['item_type'] ]['_otw_wc'][ $widget ] = '';
			$response = 'none';
		}else{
			$otw_widget_settings[ $sidebar ][ $_POST['item_type'] ]['_otw_wc'][ $widget ] = $_POST['otw_action'];
			$response  = $_POST['otw_action'];
		}
		
		update_option( 'otw_widget_settings', $otw_widget_settings );
		
		echo $response;
		
		return;
	}
	
}
if( isset( $_POST['otw_action'] ) && ( $_POST['otw_action'] == 'update' ) ){
	
	if( isset( $_POST['item_type'] ) && isset( $_POST['item_id'] ) ){
	
		$otw_widget_settings = get_option( 'otw_widget_settings' );
		
		if( !isset( $otw_widget_settings[ $sidebar ] ) ){
			$otw_widget_settings[ $sidebar ] = array();
		}
		
		//create item selection if not create but all used
		if( !isset( $otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $_POST['item_id'] ] ) ){
			
			$otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $_POST['item_id'] ] = array();
			$otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $_POST['item_id'] ]['id'] = $_POST['item_id'];
			$otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $_POST['item_id'] ]['exclude_widgets'] = array();
			
		}
		
		//process action to excluded widgets
		if( isset( $otw_widget_settings[ $sidebar ][ $_POST['item_type'] ]['_otw_wc'][$widget] ) && in_array( $otw_widget_settings[ $sidebar ][ $_POST['item_type'] ]['_otw_wc'][ $widget ],array( 'vis', 'invis' ) ) ){
		
			if( $otw_widget_settings[ $sidebar ][ $_POST['item_type'] ]['_otw_wc'][ $widget ] == 'invis' ){
				
				
				if( is_array( $otw_sidebars ) && array_key_exists( $sidebar, $otw_sidebars ) ){
					
					if( isset( $wp_registered_sidebars[ $sidebar ]['validfor'][ $_POST['item_type'] ] ) ){
						
						foreach( $wp_registered_sidebars[ $sidebar ]['validfor'][ $_POST['item_type'] ] as $wp_sb_item_id => $wp_sb_item_data ){
							
							if( !isset( $otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $wp_sb_item_id ] ) ){
								
								$otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $wp_sb_item_id ] = array();
								$otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $wp_sb_item_id ]['id'] = $wp_sb_item_id;
								$otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $wp_sb_item_id ]['exclude_widgets'] = array();
							}
							$otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $wp_sb_item_id ]['exclude_widgets'][ $widget ] = $widget;
						}
					}
				}else{
					$wp_all_items = otw_get_wp_items( $_POST['item_type'] );
					
					if( is_array( $wp_all_items ) && count( $wp_all_items ) ){
						
						foreach( $wp_all_items as $wp_all_item ){
							
							$wp_sb_item_id = otw_wp_item_attribute( $_POST['item_type'], 'ID', $wp_all_item );
							if( !isset( $otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $wp_sb_item_id ] ) ){
								
								$otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $wp_sb_item_id ] = array();
								$otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $wp_sb_item_id ]['id'] = $wp_sb_item_id;
								$otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $wp_sb_item_id ]['exclude_widgets'] = array();
							}
							$otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $wp_sb_item_id ]['exclude_widgets'][ $widget ] = $widget;
						}
					}
				}
				
				if( isset( $otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $_POST['item_id'] ]['exclude_widgets'][ $widget ] ) ){
					unset( $otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $_POST['item_id'] ]['exclude_widgets'][ $widget ] );
					echo 'sitem_selected_from_invis';
				}else{
					echo 'sitem_selected_from_invis';
				}
				unset( $otw_widget_settings[ $sidebar ][ $_POST['item_type'] ]['_otw_wc'][ $widget ] );
				
			}elseif( $otw_widget_settings[ $sidebar ][ $_POST['item_type'] ]['_otw_wc'][ $widget ] == 'vis' ){
				
				
				if( is_array( $otw_sidebars ) && array_key_exists( $sidebar, $otw_sidebars ) ){
					
					if( isset( $wp_registered_sidebars[ $sidebar ]['validfor'][ $_POST['item_type'] ] ) ){
						
						foreach( $wp_registered_sidebars[ $sidebar ]['validfor'][ $_POST['item_type'] ] as $wp_sb_item_id => $wp_sb_item_data ){
							
							if( isset( $otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $wp_sb_item_id ]['exclude_widgets'][ $widget ] ) ){
								unset( $otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $wp_sb_item_id ]['exclude_widgets'][ $widget ] );
							}
						}
					}
				}else{
					$wp_all_items = otw_get_wp_items( $_POST['item_type'] );
					
					if( is_array( $wp_all_items ) && count( $wp_all_items ) ){
						
						foreach( $wp_all_items as $wp_all_item ){
							
							$wp_sb_item_id = otw_wp_item_attribute( $_POST['item_type'], 'ID', $wp_all_item );
							if( isset( $otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $wp_sb_item_id ]['exclude_widgets'][ $widget ] ) ){
								unset( $otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $wp_sb_item_id ]['exclude_widgets'][ $widget ] );
							}
						}
					}
				}
				
				if( !isset( $otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $_POST['item_id'] ]['exclude_widgets'][ $widget ] ) ){
					$otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $_POST['item_id'] ]['exclude_widgets'][ $widget ] = $widget;
					echo 'sitem_selected_from_vis';
				}else{
					echo 'sitem_selected_from_vis';
				}
				
				unset( $otw_widget_settings[ $sidebar ][ $_POST['item_type'] ]['_otw_wc'][ $widget ] );
				
			}
			
		}elseif( isset( $otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $_POST['item_id'] ] ) ){
			if( isset( $otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $_POST['item_id'] ]['exclude_widgets'][ $widget ] ) ){
				unset( $otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $_POST['item_id'] ]['exclude_widgets'][ $widget ] );
				echo 'sitem_selected';
			}else{
				$otw_widget_settings[ $sidebar ][ $_POST['item_type'] ][ $_POST['item_id'] ]['exclude_widgets'][ $widget ]  = $widget;
				echo 'sitem_notselected';
			}
			
		}
		
		update_option( 'otw_widget_settings', $otw_widget_settings );
	}
	return;
}
/** set class name for all selection links
 *
 *  @param string $type vis|invis
 *  @param string $sidebar
 *  @param string $widget
 *  @param string $wp_item_type
 *  @return string
 */
function otw_sidebar_item_all_class( $type, $sidebar, $widget, $wp_item_type ){

	global $wp_registered_sidebars;
	$class = '';
	
	if( isset( $wp_registered_sidebars[ $sidebar ]['widgets_settings'][ $wp_item_type ]['_otw_wc'][ $widget ] ) ){
	
		if( $wp_registered_sidebars[ $sidebar ]['widgets_settings'][ $wp_item_type ]['_otw_wc'][ $widget ] == $type ){
			$class .= ' all_selected';
		}
	}
	
	echo $class;
}


foreach( $wp_int_items as $wp_item_type => $wp_item_data ){
	
	if( is_array( $otw_sidebars ) && array_key_exists( $sidebar, $otw_sidebars ) ){
	
		if( isset( $wp_registered_sidebars[ $sidebar ]['validfor'][ $wp_item_type ] )  && count( $wp_registered_sidebars[ $sidebar ]['validfor'][ $wp_item_type ] )){
			$wp_int_items[ $wp_item_type ][0] = array( 1 );
		}else{
			$wp_int_items[ $wp_item_type ][0] = array();
		}
	}else{
		$wp_int_items[ $wp_item_type ][0] = array( 1 );
	}
}

?>
<div class="otw_dialog_content" id="otw_dialog_content">

<div class="d_info">
	<p><img src="<?php echo $otw_wml_plugin_url.'/images/selected.gif' ?>" alt=""/><?php _e('Means the widget will be displayed on that page, post, category, etc.');?></p>
	<p><img src="<?php echo $otw_wml_plugin_url.'/images/not_selected.gif' ?>" alt=""/><?php _e('Means  the widget will be hidden form that page, post, category, etc');?></p>
	<div class="updated visupdated">
		<p><?php _e( 'A selected page template includes all pages using that template.', 'otw_sbm' )?><br />
		<?php _e( 'Template hierarchy Page includes all pages.', 'otw_sbm' )?></p>
	</div>
</div>
<?php if( is_array( $wp_int_items ) && count( $wp_int_items ) ){?>
	
	<?php foreach( $wp_int_items as $wp_item_type => $wp_item_data ){?>
		
		<?php if( is_array( $wp_item_data[0] ) && count( $wp_item_data[0] ) ){?>
			<div class="meta-box-sortables metabox-holder">
				<div class="postbox">
					<div title="<?php _e('Click to toggle')?>" class="handlediv sitem_toggle"><br></div>
					<h3 class="hndle sitem_header"><span><?php echo $wp_item_data[1]?></span></h3>
					
					<div class="inside sitems<?php if( count( $wp_item_data[0] ) > 15 ){ echo ' mto';}?>" id="otw_sbm_app_type_<?php echo $wp_item_type?>" rel="<?php echo $sidebar?>|<?php echo $widget?>|<?php echo $wp_item_type?>" >
						<div class="all_vis_lnks">
							<a href="javascript:;" rel="<?php echo $sidebar?>|<?php echo $widget?>|<?php echo $wp_item_type?>|vis" class="all_vis<?php echo otw_sidebar_item_all_class( 'vis', $sidebar, $widget, $wp_item_type )?>"><?php _e( 'all visible' )?></a>
							<a href="javascript:;" rel="<?php echo $sidebar?>|<?php echo $widget?>|<?php echo $wp_item_type?>|invis"class="all_invis<?php echo otw_sidebar_item_all_class( 'invis', $sidebar, $widget, $wp_item_type )?>"><?php _e( 'all invisible' )?></a>
							<?php if( !in_array( $wp_item_type, array( 'templatehierarchy', 'pagetemplate', 'archive' ) ) ){?>
									<input type="text" class="q_filter" value="<?php _e('Type to search' );?>"/>
									<div class="otw_app_loading"></div>
							<?php }?>
						</div>
						<?php if( count( $wp_item_data[0] ) > 15 ) {?>
							<div class="more_items"><?php echo sprintf( __( 'Showing 15 of %d items. Please use the filter box to filter them out' ), count( $wp_item_data[0] ) );?></div>
						<?php }?>
						<div class="lf_items">
						</div>
						
					</div>
					
				</div>
			
			</div>
		<?php }?>
	<?php }?>
	<script type="text/javascript">
		otw_init_appearence_dialog();
	</script>
<?php }?>
</div>
