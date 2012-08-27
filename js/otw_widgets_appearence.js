function otw_init_appearence_dialog(){

	jQuery( 'p.sitem_selected > a, p.sitem_notselected > a').click( function(){
	
		var lObject = jQuery( this );
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
		var search_box = jQuery( this );
		otw_filter_wp_items( search_box )
	});
	jQuery('input.q_filter').keyup( function(){
		var search_box = jQuery( this );
		try{
			clearTimeout( window.otw_q_filter_timeout );
		}catch(e){}
		window.otw_q_filter_timeout = setTimeout( function(){otw_filter_wp_items( search_box )}, 300 );
	});
	jQuery('input.q_filter').keydown( function(){
		var search_box = jQuery( this );
		try{
			clearTimeout( window.otw_q_filter_timeout );
		}catch(e){}
		window.otw_q_filter_timeout = setTimeout( function(){otw_filter_wp_items( search_box )}, 300 );
	});
	jQuery('#otw_dialog_content').find('.sitem_toggle').click(function() {
		jQuery(this).parent().find( '.inside').toggleClass('closed');
	});
};

function otw_filter_wp_items( search_box ){

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