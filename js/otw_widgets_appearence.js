function otw_init_appearence_dialog(){
	
	jQuery( 'div.lf_items' ).each( function(){
	
		var matches = false;
		if( matches = jQuery( this ).parent().attr( 'id' ).match( /^otw_sbm_app_type_(.*)$/ ) ){
		
			var parent_box = jQuery( this ).parents( 'div.sitems');
			otw_sbm_wv_load_items( parent_box );
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
									otw_set_selected_items( lParts[2], 'none' );
									oLinks.attr( 'class', 'sitem_notselected' );
								break;
							default:
									otw_set_selected_items( lParts[2], 'all' );
									oLinks.attr( 'class', 'sitem_selected' );
								break;
						};
					}
				};
				
		jQuery.ajax( settings );
	} );
	
	jQuery('input.otw_sbm_wv_q_filter').focus( function(){
		this.value='';
	});
	jQuery('input.otw_sbm_wv_q_filter').keyup( function(){
		var parent_box = jQuery( this ).parents( 'div.sitems');
		try{
			clearTimeout( window.otw_q_filter_timeout );
		}catch(e){}
		window.otw_q_filter_timeout = setTimeout( function(){ otw_sbm_wv_load_items( parent_box )}, 300 );
	});
	jQuery('input.otw_sbm_wv_q_filter').keydown( function(){
		var parent_box = jQuery( this ).parents( 'div.sitems');
		try{
			clearTimeout( window.otw_q_filter_timeout );
		}catch(e){}
		window.otw_q_filter_timeout = setTimeout( function(){ otw_sbm_wv_load_items( parent_box )}, 300 );
	});
	jQuery('div.otw_sidebar_wv_filter_show select').change( function(){
		var parent_box = jQuery( this ).parents( 'div.sitems');
		otw_sbm_wv_load_items( parent_box );
	});
	jQuery('div.otw_sidebar_wv_filter_order select').change( function(){
		var parent_box = jQuery( this ).parents( 'div.sitems');
		otw_sbm_wv_load_items( parent_box );
	});
	jQuery('div.otw_sidebar_wv_filter_clear a').click( function( event ){
		
		var parent_box = jQuery( this ).parents( 'div.sitems');
		var a_matches = false;
		if( a_matches = this.id.match( /^otw_type_(.*)_wv_clear$/ ) )
		{
			jQuery( '#otw_type_' + a_matches[1] + '_search_field' ).val( '' );
			jQuery( '#otw_type_' + a_matches[1] + '_show_field' ).val( 'all' );
			jQuery( '#otw_type_' + a_matches[1] + '_order_field' ).val( 'a_z' );
			jQuery( '#otw_type_' + a_matches[1] + '_page_field' ).val( '0' );
			
			jQuery( '#otw_type_' + a_matches[1] + '_per_page_field' ).attr( 'id', 'otw_type_' + a_matches[1] + '_per_page_field_old' );
			
			otw_sbm_wv_load_items( parent_box );
		};
	});

	jQuery('#otw_dialog_content').find('.sitem_toggle, .sitem_header').click(function() {
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
						
						if( t_data == 'sitem_selected' ){
							otw_set_selected_items( lParts[2], '+' );
						}else if( t_data == 'sitem_notselected' ){
							otw_set_selected_items( lParts[2], '-' );
						}
						
					}else if( t_data == 'sitem_selected_from_invis' ){
						lParent.attr( 'class', 'sitem_selected' );
						lParent.parents( 'div.postbox' ).find( 'a.all_selected' ).removeClass( 'all_selected' );
						otw_set_selected_items( lParts[2], '+' );
					}else if( t_data == 'sitem_selected_from_vis' ){
						lParent.attr( 'class', 'sitem_notselected' );
						lParent.parents( 'div.postbox' ).find( 'a.all_selected' ).removeClass( 'all_selected' );
						otw_set_selected_items( lParts[2], '-' );
					}else{
						lParent.attr( 'class', old_class );
					};
				}
			};
			
			jQuery.ajax( settings );
		};
	};
};

function otw_sbm_wv_load_items( item_node ){
	
	var matches = false;
	
	if( matches = item_node.attr( 'rel' ).match( /^([0-9a-zA-Z\-\_]+)\|([0-9a-zA-Z\-\_]+)\|([0-9a-zA-Z\-\_]+)$/ ) ){
		
		var item_type = matches[3]
		
		var post_params = { string_filter: '', type: item_type, format: 'a_dialog' };
		post_params.sidebar = matches[1];
		post_params.widget  = matches[2];
		
		post_params.string_filter = jQuery( '#otw_type_' + item_type + '_search_field' ).val();
		post_params.show = jQuery( '#otw_type_' + item_type + '_show_field' ).val();
		post_params.order = jQuery( '#otw_type_' + item_type + '_order_field' ).val();
		post_params.page = jQuery( '#otw_type_' + item_type + '_page_field' ).val();
		
		if( jQuery( '#otw_type_' + item_type + '_per_page_field' ).size() ){
			post_params.per_page = jQuery( '#otw_type_' + item_type + '_per_page_field' ).val();
		}
		
		item_node.find('div.otw_sidebar_wv_item_filter').addClass( 'sitem_loading' );
		
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
				item_node.find('div.otw_sidebar_wv_item_filter').removeClass( 'sitem_loading' );
				otw_init_wv_item_pager( item_type );
				otw_set_selected_items( item_type, 'no' );
			}
		};
		
		jQuery.ajax( settings );
	};
	
};
function otw_set_selected_items( item_type, action ){

	var total_selected = Number( jQuery( '#otw_total_selected_' + item_type ).val() );
	
	switch( action ){
	
		case '+':
				total_selected = total_selected + 1;
			break;
		case '-':
				if( total_selected > 0 ){
					total_selected = total_selected - 1;
				}
			break;
		case 'all':
				total_selected = Number( jQuery( '#otw_total_items_' + item_type ).val() );
			break;
		case 'none':
				total_selected = 0;
			break;
	}
	
	jQuery( '#otw_sbm_app_type_' + item_type ).find( 'div.otw_sbm_selected_items span.otw_selected_items_number' ).html( total_selected );
	if( total_selected == 1 ){
		jQuery( '#otw_sbm_app_type_' + item_type ).find( 'div.otw_sbm_selected_items span.otw_seleted_items_plural' ).hide();
		jQuery( '#otw_sbm_app_type_' + item_type ).find( 'div.otw_sbm_selected_items span.otw_selected_items_singular' ).show();
	}else{
		jQuery( '#otw_sbm_app_type_' + item_type ).find( 'div.otw_sbm_selected_items span.otw_seleted_items_plural' ).show();
		jQuery( '#otw_sbm_app_type_' + item_type ).find( 'div.otw_sbm_selected_items span.otw_selected_items_singular' ).hide();
	}
	jQuery( '#otw_sbm_app_type_' + item_type ).find( 'div.otw_sbm_selected_items').show();
	jQuery( '#otw_total_selected_' + item_type ).val( total_selected );
};
function otw_init_wv_item_pager( item_type ){
	
	jQuery('#otw_type_' + item_type + '_per_page_field').change( function( event ){
		var parent_box = jQuery( this ).parents( 'div.sitems');
		otw_sbm_wv_load_items( parent_box );
	});
	jQuery('#otw_sbm_app_type_' + item_type + ' .otw_sidebar_pager_links a ').click( function(){
		
		if( jQuery( this ).attr( 'rel' ).match( /^\d+$/ ) ){
			var parent_box = jQuery( this ).parents( 'div.sitems');
			jQuery( '#otw_type_' + item_type + '_page_field' ).val( jQuery( this ).attr( 'rel' ) );
			otw_sbm_wv_load_items( parent_box );
		};
		
	} );
}
function otw_filter_wp_items( search_box ){

	var parent = jQuery( search_box ).parent().parent();
	
	var matches = false;
	if( matches = parent.attr( 'id' ).match( /^otw_sbm_app_type_(.*)$/ ) ){
		return otw_sbm_load_app_items( matches[1], parent, search_box.val() );
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

function otw_sbm_load_app_items( item_type, item_node, string_filter ){

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