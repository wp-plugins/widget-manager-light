var dialog_container = '';
jQuery(document).ready(function() {
	
	jQuery('a.widget-action').live('click', function(){
		otw_init_widgets();
	} );
	
	jQuery(this).ajaxComplete(function(event, XMLHttpRequest, ajaxOptions){
		
		if( ajaxOptions.data && ajaxOptions.data.search('action=save-widget')  ){
			otw_init_widgets();
		}
	})
	otw_init_widgets();
});
function otw_init_widgets(){
	
	/*set up appearence links*/
	var widget_blocks = jQuery( 'div.widgets-sortables' );
	widget_blocks.each( function(){
		
		var widget_block = jQuery( this );
		
		if( widget_block.attr('id').length ){
			
			var widget_containers = widget_block.find( 'div.widget' );
			
			var sidebar_id = widget_block.attr( 'id' );
			
			if( widget_containers.length && sidebar_id != 'wp_inactive_widgets' ){
				
				widget_containers.each( function(){
					var widget_id_container = jQuery( this ).find( 'input.widget-id' );
					
					if( widget_id_container.length ){
						widget_id = widget_id_container.val();
						
						var action_blocks = jQuery( this ).find( 'div.widget-content' );
						
						
						action_blocks.each( function(){
							var object = jQuery( this );
							
							var appearence_links = object.find( 'input.otw_appearence' );
							
							if( !appearence_links.length )
							{
								var req_url = 'admin-ajax.php?action=otw_wml_widget_dialog&sidebar=' + sidebar_id + '&widget=' + widget_id;
								
								new_action = jQuery( '<input type="button" class="button otw_appearence thickbox" name="Set Visibility" value="Set Visibility">' );
								new_action[0].widget_id = widget_id;
								new_action[0].sidebar_id = sidebar_id;
								new_action[0].href= req_url;
								
								object.append( new_action );
								tb_init( new_action );
							};
						});
					};
				});
			};
		};
	} );

};