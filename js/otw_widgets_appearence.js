function otw_init_appearence_dialog(){

	jQuery( 'div.lf_items' ).each( function(){
	
		var matches = false;
		if( matches = jQuery( this ).parent().attr( 'id' ).match( /^otw_sbm_app_type_(.*)$/ ) ){
		
			otw_wml_load_app_items( matches[1], jQuery( this ).parent(), '' );
		}
	} );
	    
	jQuery( 'p.sitem_selected > a, p.sitem_notselected > a').click( function(){
		otw_sbm_click_app_item( this );
	});
	
	jQuery('a.all_vis, a.all_invis').click( function(){
	
		var cl_link =  jQuery( this );
		
		cl_link.addClass( 'all_loading' );
		
		var lParts = cl_link.attr( 'rel' ).split( '|' );
		
		var req_url = 'admin-ajax.php?action=otw_wml_widget_dialog&sidebar=' + lParts[0] + '&widget=' + lParts[1];
		
		var oLinks = cl_link.parents('div.postbox').find( 'p.sitem_notselected, p.sitem_selected' );
		oLinks.attr( 'class', 'sitem_loading' );
		
		var settings = {
					url: req_url,
					type: 'post',
					data: '&item_type=' + lParts[2] + '&otw_action=' + lParts[3],
					success:function( data ){
						cl_link.removeClass( 'all_loading' );
						cl_link.parents( 'div.all_vis_lnks' ).find( 'a.all_selected' ).removeClass( 'all_selected' );
						
						if( data == lParts[3] ){
							cl_link.addClass( 'all_selected' );
						};
						
						switch( data ){
						
							case 'invis':
									oLinks.attr( 'class', 'sitem_notselected' );
								break;
							default:
									oLinks.attr( 'class', 'sitem_selected' );
								break;
						};
					}
				};
				
		jQuery.ajax( settings );
	} );
	
	jQuery('input.q_filter').focus( function(){
		this.value='';
	});
	jQuery('input.q_filter').keyup( function(){
		var search_box = jQuery( this );
		try{
			clearTimeout( window.otw_q_filter_timeout );
		}catch(e){}
		window.otw_q_filter_timeout = setTimeout( function(){otw_wml_filter_wp_items( search_box )}, 300 );
	});
	jQuery('input.q_filter').keydown( function(){
		var search_box = jQuery( this );
		try{
			clearTimeout( window.otw_q_filter_timeout );
		}catch(e){}
		window.otw_q_filter_timeout = setTimeout( function(){otw_wml_filter_wp_items( search_box )}, 300 );
	});

	
	jQuery('#otw_dialog_content').find('.sitem_toggle').click(function() {
		jQuery(this).parent().find( '.inside').toggleClass('otw_closed');
	});
};

function otw_sbm_click_app_item( link ){

	var lObject = jQuery( link );
	var lClass = lObject.attr( 'class' );
	var lParent = lObject.parent();
	if( lClass.length )
	{
		var lParts = lClass.split( '|' );
		
		if( lParts.length == 4 )
		{
			var req_url = 'admin-ajax.php?action=otw_wml_widget_dialog&sidebar=' + lParts[0] + '&widget=' + lParts[1];
			
			var old_class = lParent.attr( 'class' );
			lParent.attr( 'class', 'sitem_loading' );
			
			var settings = {
				url: req_url,
				type: 'post',
				data: '&item_type=' + lParts[2] + '&item_id=' + lParts[3] + '&otw_action=update',
				success:function( data ){
				
					var t_data = data.trim();
					
					if( t_data == 'sitem_selected' || t_data == 'sitem_notselected' ){
						lParent.attr( 'class', t_data );
					}else if( t_data == 'sitem_selected_from_invis' ){
						lParent.attr( 'class', 'sitem_selected' );
						lParent.parents( 'div.postbox' ).find( 'a.all_selected' ).removeClass( 'all_selected' );
					}else if( t_data == 'sitem_selected_from_vis' ){
						lParent.attr( 'class', 'sitem_notselected' );
						lParent.parents( 'div.postbox' ).find( 'a.all_selected' ).removeClass( 'all_selected' );
					}else{
						lParent.attr( 'class', old_class );
					};
				}
			};
			
			jQuery.ajax( settings );
		};
	};
};

function otw_wml_filter_wp_items( search_box ){

	var parent = jQuery( search_box ).parent().parent();
	
	var matches = false;
	if( matches = parent.attr( 'id' ).match( /^otw_sbm_app_type_(.*)$/ ) ){
		return otw_wml_load_app_items( matches[1], parent, search_box.val() );
	}
	
	return;

	var regExp = new RegExp( search_box.val(), 'i' );
	var c_found = 0;
	search_box.parents('div.postbox').find( 'p.sitem_notselected a, p.sitem_selected a' ).each( function(){
	
		
		if( this.innerHTML.match( regExp ) ){
			jQuery( this ).parent().css( 'display', 'block' );
			c_found++;
		}else{
			jQuery( this ).parent().css( 'display', 'none' );
		}
	
	} );
}


function otw_wml_load_app_items( item_type, item_node, string_filter ){

	var post_params = { string_filter: string_filter, type: item_type, format: 'a_dialog' };
	
	var matches = false;
	if( matches = item_node.attr( 'rel' ).match( /^([0-9a-zA-Z\-\_]+)\|([0-9a-zA-Z\-\_]+)\|([0-9a-zA-Z\-\_]+)$/ ) )
	{
		post_params.sidebar = matches[1];
		post_params.widget  = matches[2];
	};
	
	item_node.find( 'div.otw_app_loading' ).show();
	
	var req_url = 'admin-ajax.php?action=otw_wml_items_by_type';
	
	var settings = {
		url: req_url,
		type: 'post',
		data: post_params,
		success:function( data ){
			
			var t_data = data.trim();
			jQuery( item_node ).find( 'div.lf_items' ).html( t_data );
			jQuery( item_node ).find( 'p.sitem_selected > a, p.sitem_notselected > a').click( function(){
				otw_sbm_click_app_item( this );
			});
			item_node.find( 'div.otw_app_loading' ).hide();
		}
	};
	
	jQuery.ajax( settings );
};