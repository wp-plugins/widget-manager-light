<?php
/** OTW Sidebar & Widget Manager Column Interface
  *  load wp items by given type and string
  */

global $wp_registered_sidebars, $wp_wml_int_items, $otw_wml_plugin_url;

$otw_options = get_option( 'otw_plugin_options' );

$items_limit = 20;

if( isset( $otw_options['otw_sbm_items_limit'] ) && intval( $otw_options['otw_sbm_items_limit'] ) ){
	$items_limit = $otw_options['otw_sbm_items_limit'];
}

$wp_item_type = '';
$otw_sidebar_id = 0;
$widget = '';
$string_filter = '';
$format = '';
if( isset( $_POST['type'] ) && strlen( trim( $_POST['type'] ) ) )
{
	$wp_item_type = $_POST['type'];
}
if( isset( $_POST['sidebar'] ) && strlen( trim( $_POST['sidebar'] ) ) )
{
	$otw_sidebar_id = $_POST['sidebar'];
}
if( isset( $_POST['string_filter'] ) && strlen( trim( $_POST['string_filter'] ) ) )
{
	$string_filter = $_POST['string_filter'];
}
if( isset( $_POST['format'] ) && strlen( trim( $_POST['format'] ) ) )
{
	$format = $_POST['format'];
}
if( isset( $_POST['widget'] ) && strlen( trim( $_POST['widget'] ) ) )
{
	$widget = $_POST['widget'];
}
$otw_sidebar_values = array(
	'sbm_title'              =>  '',
	'sbm_description'        =>  '',
	'sbm_replace'            =>  '',
	'sbm_status'             =>  'inactive',
	'sbm_widget_alignment'   =>  'vertical'
);

if( $format == 'ids' ){
	$db_items = otw_sbm_get_filtered_items( $wp_item_type, $string_filter, $otw_sidebar_id, 0 );
	
	$items = array();
	$total_items = 0;
	
	if( isset( $db_items[1] ) )
	{
		$total_items = $db_items[0];
		$items = $db_items[1];
	}
	
	$keys = array();
	foreach( $items as $wpItem ){
		$key = otw_wml_wp_item_attribute( $wp_item_type, 'ID', $wpItem );
		$keys[ $key ] = $key;
	}
	if( count( $keys ) ){
		echo implode( ",", $keys );
	}
	die;
}elseif( $format == 'a_dialog' ){
	$otw_sidebars = get_option( 'otw_sidebars' );
	
	if( !is_array( $otw_sidebars ) ){
		$otw_sidebars = array();
	}
	$sidebar_widgets = get_option('sidebars_widgets');
	
	if( isset( $wp_registered_sidebars[ $otw_sidebar_id ]['validfor'][ $wp_item_type ] ) && !isset( $wp_registered_sidebars[ $otw_sidebar_id ]['validfor'][ $wp_item_type ]['all'] ) )
	{
		$db_items = otw_sbm_get_filtered_items( $wp_item_type, $string_filter, $otw_sidebar_id, $items_limit, array_keys( $wp_registered_sidebars[ $otw_sidebar_id ]['validfor'][ $wp_item_type ] ) );
	}
	else
	{
		$db_items = otw_sbm_get_filtered_items( $wp_item_type, $string_filter, $otw_sidebar_id, $items_limit );
	}
	
	$items = array();
	$total_items = 0;
	
	if( isset( $db_items[1] ) )
	{
		$total_items = $db_items[0];
		$items = $db_items[1];
	}
}else{
	$db_items = otw_sbm_get_filtered_items( $wp_item_type, $string_filter, $otw_sidebar_id, $items_limit );
	
	$items = array();
	$total_items = 0;
	
	if( isset( $db_items[1] ) )
	{
		$total_items = $db_items[0];
		$items = $db_items[1];
	}
}
?>
<?php if( $format == 'a_dialog'){?>

	<?php if( is_array( $items ) && count( $items ) ){?>
		<?php if( $total_items > 0 ){
			echo '<div class="items_info_app">'.__( 'Showing' ).' '.count( $items ).' '.__( 'of' ).' '.$total_items.'</div>';
		}?>
		<?php foreach( $items as $wpItem ) {?>
			<?php if( isset( $wp_registered_sidebars[ $otw_sidebar_id ]['validfor'][ $wp_item_type ][ otw_wml_wp_item_attribute( $wp_item_type, 'ID', $wpItem ) ] ) || isset( $wp_registered_sidebars[ $otw_sidebar_id ]['validfor'][ $wp_item_type ][ 'all' ] ) || !array_key_exists( $otw_sidebar_id, $otw_sidebars )  ){?>
				<p<?php otw_sidebar_item_row_attributes( 'p', $wp_item_type, $otw_sidebar_id, $widget, $wpItem )?> >
					<a href="javascript:;"<?php otw_sidebar_item_row_attributes( 'a', $wp_item_type, $otw_sidebar_id, $widget, $wpItem )?> ><?php echo otw_wml_wp_item_attribute( $wp_item_type, 'TITLE', $wpItem ) ?></a>
				</p>
			<?php }?>
		<?php }?>
	<?php }else{ echo '&nbsp;'; }?>
	
<?php }else{?>
	<?php if( is_array( $items ) && count( $items ) ){?>
		<?php if( $total_items > count( $items ) ){
			echo '<p>'.__( 'Showing' ).' '.count( $items ).' '.__( 'of' ).' '.$total_items.'</p>';
		}else{
			echo '<p>'.__( 'Total items found:' ).' '.count( $items ).'</p>';
		}?>
		<?php foreach( $items as $wpItem ) {?>
			<p<?php otw_sidebar_item_attributes( 'p', $wp_item_type, otw_wml_wp_item_attribute( $wp_item_type, 'ID', $wpItem ), $otw_sidebar_values, $wpItem )?>>
				<input type="checkbox" id="otw_sbi_<?php echo $wp_item_type?>_<?php echo otw_wml_wp_item_attribute( $wp_item_type, 'ID', $wpItem ) ?>"<?php otw_sidebar_item_attributes( 'c', $wp_item_type, otw_wml_wp_item_attribute( $wp_item_type, 'ID', $wpItem ), $otw_sidebar_values, array() )?> value="<?php echo otw_wml_wp_item_attribute( $wp_item_type, 'ID', $wpItem ) ?>" name="otw_sbi_<?php echo $wp_item_type?>[<?php echo otw_wml_wp_item_attribute( $wp_item_type, 'ID', $wpItem ) ?>]" /><label for="otw_sbi_<?php echo $wp_item_type?>_<?php echo otw_wml_wp_item_attribute( $wp_item_type, 'ID', $wpItem ) ?>"<?php otw_sidebar_item_attributes( 'l', $wp_item_type, otw_wml_wp_item_attribute( $wp_item_type, 'ID', $wpItem ), $otw_sidebar_values, $wpItem )?> ><a href="javascript:;"><?php echo otw_wml_wp_item_attribute( $wp_item_type, 'TITLE', $wpItem ) ?></a></label>
			</p>	
		<?php }?>
		
	<?php }else{ echo '&nbsp;'; }?>
<?php }?>
