<?php
if( !function_exists( 'otw_sbm_index' ) ){
	function otw_sbm_index( $index, $sidebars_widgets ){
		
		global $wp_registered_sidebars, $otw_replaced_sidebars;
		
		if( isset( $otw_replaced_sidebars[ $index ] ) ){//we have set replacemend.
		
			$requested_objects = otw_get_current_object();
			
			//check if the new sidebar is valid for the current requested resource
			foreach( $otw_replaced_sidebars[ $index ] as $repl_sidebar ){
				
				if( isset( $wp_registered_sidebars[ $repl_sidebar ] ) ){
					
					if( $wp_registered_sidebars[ $repl_sidebar ]['status'] == 'active'  ){
						
						if( otw_filter_strict_sidebar_index( $repl_sidebar ) ){
							foreach( $requested_objects as $objects ){
							
								list( $object, $object_id ) = $objects;
							
								if( $object && $object_id ){
									
									$tmp_index = otw_validate_sidebar_index( $repl_sidebar, $object, $object_id );
									
									if( $tmp_index ){
										if ( !empty($sidebars_widgets[$tmp_index]) ){
											$sidebars_widgets[$tmp_index] = otw_filter_siderbar_widgets( $tmp_index, $sidebars_widgets );
											
											if( count( $sidebars_widgets[$tmp_index] ) ){
												$index = $tmp_index;
												break 2;
											}
										}
									}
									
								}//end hs object and object id
								
							}//end loop requested objects
						}
					}
				}
			}
		}
		
		return $index;
	}
}

/** check if sidebar is active
  * @param string
  * @return string
  */
if( !function_exists( 'otw_is_active_sidebar' ) ){
	function otw_is_active_sidebar( $index ){
		
		global $wp_registered_sidebars;
		
		$index = ( is_int($index) ) ? "sidebar-$index" : sanitize_title($index);
		
		$index = otw_sidebar_index( $index );
		
		if( isset( $wp_registered_sidebars[ $index ] ) ){
		
			if( !array_key_exists( 'status', $wp_registered_sidebars[ $index ] ) || ( $wp_registered_sidebars[ $index ]['status'] == 'active' ) ){
			
				$sidebars_widgets = wp_get_sidebars_widgets();
				
				if ( !empty($sidebars_widgets[$index]) ){
					
					$sidebars_widgets[$index] = otw_filter_siderbar_widgets( $index, $sidebars_widgets );
					
					if( count( $sidebars_widgets[$index] ) ){
						return true;
					}
				}
			}
			
		}
		
		return false;
	} 
}


/** check if given sidebar is valid for the given object and object_id without checing the widgets
  *  @param string
  *  @param string
  *  @param string
  *  @return string
  */
if( !function_exists( 'otw_validate_sidebar_index' ) ){
	function otw_validate_sidebar_index( $sidebar, $object, $object_id ){
	
		global $wp_registered_sidebars;
		
		$tmp_index = false;
		
		if( preg_match( "/^otw\-/", $sidebar ) ){
			
			if( isset( $wp_registered_sidebars[ $sidebar ]['validfor'][ $object ][ $object_id ] ) || isset( $wp_registered_sidebars[ $sidebar ]['validfor'][ $object ][ 'all' ] ) || empty( $wp_registered_sidebars[ $sidebar ]['replace'] ) ){
				$tmp_index = $sidebar;
			}elseif( preg_match( "/^cpt\_(.*)/", $object, $matches ) ){
				$cpt_object = 'customposttype';
				$cpt_object_id = $matches[1];
			
				if( isset( $wp_registered_sidebars[ $sidebar ]['validfor'][ $cpt_object ][ $cpt_object_id ] ) || isset( $wp_registered_sidebars[ $sidebar ]['validfor'][ $cpt_object ][ 'all' ] ) ){
					$tmp_index = $sidebar;
				}
			}
			
		}else{
			$tmp_index = $sidebar;
		}
		return $tmp_index;
	}
}

/** filter widget for given sidebar
  *
  *  @param string
  *  @param array
  *  @return array
  */
if( !function_exists( 'otw_filter_siderbar_widgets' ) ){
	function otw_filter_siderbar_widgets( $index, $sidebars_widgets ){
		
		global $wp_registered_sidebars, $otw_plugin_options;
		
		$filtered_widgets = array();
		
		if( array_key_exists( $index, $sidebars_widgets ) ){
		
			if( isset( $otw_plugin_options['activate_appearence'] ) && $otw_plugin_options['activate_appearence'] ){
				
				$requested_objects = otw_get_current_object();
				
				foreach( $requested_objects as $objects ){
					
					list( $object, $object_id ) = $objects;
					
					$tmp_index = otw_validate_sidebar_index( $index, $object, $object_id );
					
					if( $tmp_index ){
					
						$otw_wc_invisible = array();
						$otw_wc_visible = array();
						if( isset( $wp_registered_sidebars[ $tmp_index ]['widgets_settings'][ $object ]['_otw_wc'] ) ){
							$filtered = true;
							foreach( $wp_registered_sidebars[ $tmp_index ]['widgets_settings'][ $object ]['_otw_wc'] as $tmp_widget => $tmp_widget_value ){
								if( $tmp_widget_value == 'vis' ){
									$filtered_widgets[] = $tmp_widget;
									$otw_wc_visible[ $tmp_widget ] = $tmp_widget;
								}elseif( $tmp_widget_value == 'invis' ){
									$otw_wc_invisible[ $tmp_widget ] = $tmp_widget;
								}
							}
						}
						if( isset( $wp_registered_sidebars[ $tmp_index ]['widgets_settings'][ $object ][ $object_id ]['exclude_widgets'] ) ){
						
							foreach( $sidebars_widgets[ $tmp_index ] as $tmp_widget ){
								$filtered = true;
								if( !array_key_exists( $tmp_widget, $wp_registered_sidebars[ $tmp_index ]['widgets_settings'][ $object ][ $object_id ]['exclude_widgets'] ) ){
									
									if( !array_key_exists( $tmp_widget, $otw_wc_invisible ) && !array_key_exists( $tmp_widget, $otw_wc_visible )  ){
										$filtered_widgets[] = $tmp_widget;
									}
								}
							}
						}else{
							foreach( $sidebars_widgets[ $tmp_index ] as $tmp_widget ){
								$filtered = true;
								
								if( !array_key_exists( $tmp_widget, $otw_wc_invisible ) && !array_key_exists( $tmp_widget, $otw_wc_visible )  ){
									$filtered_widgets[] = $tmp_widget;
								}
							}
						}
						
						if( count( $filtered_widgets ) ){
							break;
						}
					}
				}
				
				if( isset( $filtered_widgets ) && is_array( $filtered_widgets ) && count( $filtered_widgets ) ){
					$collected_widgets = array();
					foreach( $filtered_widgets as $widget_order => $widget_name ){
						$collected_widgets[ $widget_name ] = $widget_order;
					}
					$collected_widgets = otw_filter_strict_widgets( $index, $collected_widgets );
					
					//fix the order of widgets
					if( is_array( $collected_widgets ) && count( $collected_widgets ) ){
						$filtered_widgets = array();
						asort( $collected_widgets );
						foreach( $collected_widgets as $tmp_widget => $tmp_order ){
							$filtered_widgets[] = $tmp_widget;
						}
					}
					else{
						$filtered_widgets = array();
					}
				}
				
			}else{
				$filtered_widgets = $sidebars_widgets[ $index ];
			}
		}
		return $filtered_widgets;
	}
}

if( !function_exists( 'otw_get_current_object' ) ){
	function otw_get_current_object(){
		
		global $current_user;
		
		$wp_query = $GLOBALS['wp_query'];
		
		$object_key = 0;
		
		$pre_objects = 0;
		
		$object = '';
		$object_id = 0;
		$object_type = 'flow';
		
		$current_object_key = $object_key;
		$objects[$current_object_key][0] = '';
		$objects[$current_object_key][1] = 0;
		$objects[$current_object_key][2] = 'flow';
		
		wp_reset_query();
		
		if( otw_installed_plugin( 'buddypress' ) ){
			global $bp;
		}
		
		if( is_page() ){
			$object = 'page';
			$query_object = $wp_query->get_queried_object();
			
			$object_id = $query_object->ID;
			
			if( otw_installed_plugin( 'buddypress' ) ){
				
				if( isset( $bp->pages->activity ) && ( $bp->pages->activity->id == $object_id ) ){
					$object = 'buddypress_page';
				}elseif( isset( $bp->pages->members ) && ( $bp->pages->members->id  == $object_id ) ){
					$object = 'buddypress_page';
				}
			}
			
			if( is_page_template() ){
				$template_string = get_page_template();
				$template_parts = explode( "/", $template_string );
				$o_id = $template_parts[ count( $template_parts ) - 1 ];
				if( $o_id != 'page.php' ){
					$objects[ $current_object_key + 1 ][0] = 'pagetemplate';
					$objects[ $current_object_key + 1 ][1] = $o_id;
					$objects[ $current_object_key + 1 ][2] = 'flow';
				}
			}
			
			
			$custom_taxonomies = get_taxonomies( array(  'public'   => true, '_builtin' => false ), 'object' );
			if( is_array( $custom_taxonomies ) && count( $custom_taxonomies ) ){
				
				foreach( $custom_taxonomies as $c_type => $c_type_info ){
					
					$post_taxonomies = wp_get_object_terms( $object_id, $c_type );
					
					if( is_array( $post_taxonomies ) && count( $post_taxonomies ) ){
						
						foreach( $post_taxonomies as $p_tax ){
							$object_key = count( $objects );
							$objects[ $object_key ][0] = 'page_in_ctx_'.$c_type;
							$objects[ $object_key ][1] = $p_tax->term_id;
							$objects[ $object_key ][2] = 'flow';
						}
					}
				}
			}
			
		}elseif( is_single() ){
			$post_type = get_post_type();
			
			$custom_post_types = get_post_types( array(  'public'   => true, '_builtin' => false ), 'object' );
			
			if( array_key_exists( $post_type, $custom_post_types )  ){
				
				$object = 'cpt_'.$post_type;
				$object_slug = get_query_var( $post_type );
				$posts = get_posts( array( 'name' => $object_slug, 'post_type' => $post_type, 'numberposts' => -1 ) );
				
				if( is_array( $posts ) && count( $posts ) ){
					$object_id = $posts[0]->ID;
				}
				
				$custom_taxonomies = get_taxonomies( array(  'public'   => true, '_builtin' => false ), 'object' );
				if( is_array( $custom_taxonomies ) && count( $custom_taxonomies ) ){
					
					foreach( $custom_taxonomies as $c_type => $c_type_info ){
						
						$post_taxonomies = wp_get_object_terms( $object_id, $c_type );
						
						if( is_array( $post_taxonomies ) && count( $post_taxonomies ) ){
							
							foreach( $post_taxonomies as $p_tax ){
								$object_key = count( $objects );
								$objects[ $object_key ][0] = $post_type.'_in_ctx_'.$c_type;
								$objects[ $object_key ][1] = $p_tax->term_id;
								$objects[ $object_key ][2] = 'flow';
							}
						}
					}
				}

				
			}else{
				$object = 'post';
				$query_object = $wp_query->get_queried_object();
				
				$object_id = $query_object->ID;
				
				if( $object_id ){
					$post_categories = wp_get_post_categories( $object_id );
					
					if( is_array( $post_categories ) && count( $post_categories ) ){
						foreach( $post_categories as $p_cat ){
							
							$object_key = count( $objects );
							
							$objects[ $object_key ][0] = 'postsincategory';
							$objects[ $object_key ][1] = $p_cat;
							$objects[ $object_key ][2] = 'flow';
						}
					}
					$post_tags = wp_get_post_tags( $object_id );
					if( is_array( $post_tags ) && count( $post_tags ) ){
						foreach( $post_tags as $p_tag ){
							$object_key = count( $objects );
							
							$objects[ $object_key ][0] = 'postsintag';
							$objects[ $object_key ][1] = $p_tag->term_id;
							$objects[ $object_key ][2] = 'flow';
						}
					}
					$custom_taxonomies = get_taxonomies( array(  'public'   => true, '_builtin' => false ), 'object' );
					if( is_array( $custom_taxonomies ) && count( $custom_taxonomies ) ){
						
						foreach( $custom_taxonomies as $c_type => $c_type_info ){
							
							$post_taxonomies = wp_get_object_terms( $object_id, $c_type );
							
							if( is_array( $post_taxonomies ) && count( $post_taxonomies ) ){
								
								foreach( $post_taxonomies as $p_tax ){
									$object_key = count( $objects );
									$objects[ $object_key ][0] = 'post_in_ctx_'.$c_type;
									$objects[ $object_key ][1] = $p_tax->term_id;
									$objects[ $object_key ][2] = 'flow';
								}
							}
						}
					}
				}
			}
			
		}elseif( is_category() ){
			$object = 'category';
			$query_object = $wp_query->get_queried_object();
			$object_id = $query_object->term_id;
			
		}elseif( is_tag() ){
			$object = 'posttag';
			$query_object = $wp_query->get_queried_object();
			$object_id = $query_object->term_id;
		}elseif( is_archive() ){
			$object = 'archive';
			$object_id = 0;
			
			$query_object = $wp_query->get_queried_object();
			
			if( is_author() ){
				$q_object = $wp_query->get_queried_object();
				
				$object = 'author_archive';
				$object_id = $q_object->data->ID;
			}elseif( is_tax() ){
				$q_object = $wp_query->get_queried_object();
				
				$object = 'ctx_'.$q_object->taxonomy;
				$object_id = $q_object->term_id;
			}
			elseif( isset( $wp_query->query['year'] ) && isset( $wp_query->query['monthnum'] ) && isset( $wp_query->query['daily'] ) ){
				$object_id = 'daily';
			}
			elseif( isset( $wp_query->query['year'] ) && isset( $wp_query->query['monthnum'] ) ){
				$object_id = 'monthly';
			}
			elseif( isset( $wp_query->query['year'] ) ){
				$object_id = 'yearly';
			}elseif( function_exists( 'is_shop' ) && function_exists( 'woocommerce_get_page_id' ) && is_shop() && woocommerce_get_page_id('shop') ){
				//woocommerce pages
				$object = 'page';
				$object_id = woocommerce_get_page_id('shop');
				
			}elseif( otw_installed_plugin( 'bbpress' ) && isset( $wp_query->post ) && $wp_query->post->ID == 0 && isset( $wp_query->queried_object_id ) && ( $wp_query->queried_object_id == 0 ) && isset( $wp_query->queried_object ) && ( $wp_query->queried_object->name == 'forum' ) ){
				//bbpress pages
				$object = 'bbp_page';
				$object_id = 'forums';
			}elseif( otw_installed_plugin( 'buddypress' ) && isset( $wp_query->post ) && isset( $wp_query->queried_object )&& isset( $wp_query->queried_object->ID ) && $wp_query->queried_object->ID && isset( $bp->pages->activity ) && ( $bp->pages->activity->id == $wp_query->queried_object->ID ) ){
				$object = 'buddypress_page';
				$object_id = $wp_query->queried_object->ID;
			}elseif( otw_installed_plugin( 'buddypress' ) && isset( $wp_query->post ) && isset( $wp_query->queried_object ) && isset( $wp_query->queried_object->ID ) && $wp_query->queried_object->ID && isset( $bp->pages->members ) && ( $bp->pages->members->id == $wp_query->queried_object->ID ) ){
				$object = 'buddypress_page';
				$object_id = $wp_query->queried_object->ID;
			}
		}else{
			if( !$object ){
				if( isset( $wp_query->queried_object ) && is_object(  $wp_query->queried_object ) && isset( $wp_query->queried_object->taxonomy ) && isset( $wp_query->queried_object->term_taxonomy_id ) && $wp_query->queried_object->term_taxonomy_id  ){
					$object = 'ctx_'.$wp_query->queried_object->taxonomy;
					$object_id = $wp_query->queried_object->term_taxonomy_id;
				}elseif( otw_installed_plugin( 'bbpress' ) && isset( $wp_query->bbp_is_search ) && $wp_query->bbp_is_search ){
					$object = 'bbp_page';
					$object_id = 'search';
				}elseif( otw_installed_plugin( 'bbpress' ) && isset( $wp_query->bbp_is_view ) && $wp_query->bbp_is_view && isset( $wp_query->query ) && isset( $wp_query->query['bbp_view'] ) && (  $wp_query->query['bbp_view'] == 'no-replies' )  ){
					$object = 'bbp_page';
					$object_id = 'noreplies';
				}elseif( otw_installed_plugin( 'bbpress' ) && isset( $wp_query->bbp_is_view ) && $wp_query->bbp_is_view && isset( $wp_query->query ) && isset( $wp_query->query['bbp_view'] ) && (  $wp_query->query['bbp_view'] == 'popular' ) ){
					$object = 'bbp_page';
					$object_id = 'mostpopular';
				}elseif( otw_installed_plugin( 'bbpress' ) && isset( $wp_query->bbp_is_single_user ) && $wp_query->bbp_is_single_user ){
					$object = 'bbp_page';
					$object_id = 'singleuser';
				}
			}
		}
		
		$objects[ $current_object_key ][0] = $object;
		$objects[ $current_object_key ][1] = $object_id;
		$objects[ $current_object_key ][2] = 'flow';
		
		//add Template Hierarchy as next object
		$object_key = count( $objects );
		
		if( ( $object_key == ( $pre_objects + 1 ) ) && !$objects[ $current_object_key ][0] ){
			$object_key = $current_object_key;
		}
		
		if( is_front_page() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = 'front';
			$objects[ $object_key ][2] = 'flow';
			$object_key++;
		}
		if( is_home() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = 'home';
			$objects[ $object_key ][2] = 'flow';
			$object_key++;
		}
		if( is_404() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = '404';
			$objects[ $object_key ][2] = 'flow';
			$object_key++;
		}
		if( is_search() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = 'search';
			$objects[ $object_key ][2] = 'flow';
			$object_key++;
		}
		if( is_date() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = 'date';
			$objects[ $object_key ][2] = 'flow';
			$object_key++;
		}
		if( is_author() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = 'author';
			$objects[ $object_key ][2] = 'flow';
			$object_key++;
		}
		if( is_category() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = 'category';
			$objects[ $object_key ][2] = 'flow';
			$object_key++;
		}
		if( is_tag() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = 'tag';
			$objects[ $object_key ][2] = 'flow';
			$object_key++;
		}
		if( is_tax() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = 'taxonomy';
			$objects[ $object_key ][2] = 'flow';
			$object_key++;
		}
		if( is_archive() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = 'archive';
			$objects[ $object_key ][2] = 'flow';
			$object_key++;
		}
		if( is_single() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = 'single';
			$objects[ $object_key ][2] = 'flow';
			$object_key++;
		}
		if( is_attachment() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = 'attachment';
			$objects[ $object_key ][2] = 'flow';
			$object_key++;
		}
		if( is_page() ){
			$objects[ $object_key ][0] = 'templatehierarchy';
			$objects[ $object_key ][1] = 'page';
			$objects[ $object_key ][2] = 'flow';
			$object_key++;
		}
		
		return $objects;
	} }
	
/** overwrites sidebar index based otw sitebar settings
  * @param string
  * @return string
  */
if( !function_exists( 'otw_sidebar_index' ) ){
	function otw_sidebar_index( $index ){
	
		global $wp_registered_sidebars, $otw_replaced_sidebars;
		
		$sidebars_widgets = wp_get_sidebars_widgets();
		
		if( isset( $otw_replaced_sidebars[ $index ] ) ){//we have set replacemend.
			
			$requested_objects = otw_get_current_object();
			
			//check if the new sidebar is valid for the current requested resource
			foreach( $otw_replaced_sidebars[ $index ] as $repl_sidebar ){
				
				if( isset( $wp_registered_sidebars[ $repl_sidebar ] ) ){
					
					if( $wp_registered_sidebars[ $repl_sidebar ]['status'] == 'active'  ){
						
						foreach( $requested_objects as $objects ){
						
							list( $object, $object_id ) = $objects;
							
							if( $object && $object_id ){
								
								$tmp_index = otw_validate_sidebar_index( $repl_sidebar, $object, $object_id );
								
								if( $tmp_index ){
									if ( !empty($sidebars_widgets[$tmp_index]) ){
										$sidebars_widgets[$tmp_index] = otw_filter_siderbar_widgets( $tmp_index, $sidebars_widgets );
										
										if( count( $sidebars_widgets[$tmp_index] ) ){
											$index = $tmp_index;
											break 2;
										}
									}
								}
								
							}//end hs object and object id
							
						}//end loop requested objects
						
					}
				}
			}
		}
		return $index;
	}
}


/** overwrites the default dynamic sidebar function
  * @param string
  * @return string
  */
if( !function_exists( 'otw_dynamic_sidebar' ) ){
	function otw_dynamic_sidebar( $index = 1 ){
		
		global $wp_registered_sidebars, $wp_registered_widgets;
		
		if ( is_int($index) ) {
			$index = "sidebar-$index";
		} else {
			$index = sanitize_title($index);
			foreach ( (array) $wp_registered_sidebars as $key => $value ) {
				if ( sanitize_title($value['name']) == $index ) {
					$index = $key;
					break;
				}
			}
		}
		
		$index = otw_sidebar_index( $index );
		
		$sidebars_widgets = wp_get_sidebars_widgets();
		
		//filter widgets for ths sidebar
		if( !is_admin() ){
			$sidebars_widgets[ $index ] = otw_filter_siderbar_widgets( $index, $sidebars_widgets );
		}
		
		if ( empty($wp_registered_sidebars[$index]) || !array_key_exists($index, $sidebars_widgets) || !is_array($sidebars_widgets[$index]) || empty($sidebars_widgets[$index]) || !count($sidebars_widgets[$index]) )
			return false;
	
		$sidebar = $wp_registered_sidebars[$index];
		
		$did_one = false;
		foreach ( (array) $sidebars_widgets[$index] as $id ) {
	
			if ( !isset($wp_registered_widgets[$id]) ) continue;
	
			$params = array_merge(
				array( array_merge( $sidebar, array('widget_id' => $id, 'widget_name' => $wp_registered_widgets[$id]['name']) ) ),
				(array) $wp_registered_widgets[$id]['params']
			);
	
			// Substitute HTML id and class attributes into before_widget
			$classname_ = '';
			foreach ( (array) $wp_registered_widgets[$id]['classname'] as $cn ) {
				if ( is_string($cn) )
					$classname_ .= '_' . $cn;
				elseif ( is_object($cn) )
					$classname_ .= '_' . get_class($cn);
			}
			$classname_ = ltrim($classname_, '_');
			$params[0]['before_widget'] = sprintf($params[0]['before_widget'], $id, $classname_);
	
			$params = apply_filters( 'dynamic_sidebar_params', $params );
	
			$callback = $wp_registered_widgets[$id]['callback'];
	
			do_action( 'dynamic_sidebar', $wp_registered_widgets[$id] );
	
			if ( is_callable($callback) ) {
				call_user_func_array($callback, $params);
				$did_one = true;
			}
		}
		
		return $did_one;
	}
}

/** get wp items based on type
  * @param string
  * @return array
  */
if( !function_exists( 'otw_get_wp_items' ) ){
	function otw_get_wp_items( $item_type ){
		switch( $item_type ){
			case 'page':
					$pages = get_pages();
					$pages = otw_group_items( $pages, 'ID', 'post_parent', 0 );
					return $pages;
				break;
			case 'post':
					return get_posts( array( 'numberposts' => -1 )  );
				break;
			case 'postsincategory':
					$categories = get_categories(array('hide_empty' => 0));
					$categories = otw_group_items( $categories, 'cat_ID', 'parent', 0 );
					return $categories;
				break;
			case 'postsintag':
					return get_terms( 'post_tag', '&orderby=name&hide_empty=0' );
				break;
			case 'category':
					$categories = get_categories(array('hide_empty' => 0));
					$categories = otw_group_items( $categories, 'cat_ID', 'parent', 0 );
					return $categories;
				break;
			case 'posttag':
					return get_terms( 'post_tag', '&orderby=name&hide_empty=0' );
				break;
			case 'pagetemplate':
					$templates = array();
					$all_templates = get_page_templates();
					
					if( is_array( $all_templates ) && count( $all_templates ) )
					{
						foreach( $all_templates as $page_template_name => $page_template_script )
						{
							$tplObject = new stdClass();
							$tplObject->name = $page_template_name;
							$tplObject->script = $page_template_script;
							$templates[] = $tplObject;
						}
					}
					return $templates;
				break;
			case 'archive':
					$archive_types = array();
					$a_types = array( 'daily' => 'Daily', 'monthly' => 'Monthly', 'yearly' => 'Yearly' );
					foreach( $a_types as $a_type => $a_name )
					{
						$aObject = new stdClass();
						$aObject->ID = $a_type;
						$aObject->name = $a_name;
						$archive_types[] = $aObject;
					}
					return $archive_types;
				break;
			case 'customposttype':
					return get_post_types( array(  'public'   => true, '_builtin' => false ), 'object' );
				break;
			case 'templatehierarchy':
					$h_types = array();
					$a_types = array( 
							'home'        =>    'Home',
							'front'       =>    'Front Page',
							'404'         =>    'Error 404 Page',
							'search'      =>    'Search',
							'date'        =>    'Date',
							'author'      =>    'Author',
							'category'    =>    'Category',
							'tag'         =>    'Tag',
							'taxonomy'    =>    'Taxonomy',
							'archive'     =>    'Archive',
							'single'      =>    'Singular',
							'attachment'  =>    'Attachment',
							'page'        =>    'Page'
						);
					
					foreach( $a_types as $a_type => $a_name )
					{
						$aObject = new stdClass();
						$aObject->ID = $a_type;
						$aObject->name = $a_name;
						$h_types[] = $aObject;
					}
					return $h_types;
				break;
			default:
					if( preg_match( "/^cpt_(.*)$/", $item_type, $matches ) ){
						return get_posts( array( 'post_type' =>  $matches[1], 'numberposts' => -1 )  );
					}elseif( preg_match( "/^ctx_(.*)$/", $item_type, $matches ) ){
						return get_terms( $matches[1], '&orderby=name&hide_empty=0' );
					}
				break;
		}
	}
}

/** group wp items by level for better view
 * 
 * @param array
 * @param string
 * @param string
 * @param string
 * @param integer
 * @return array
 */ 
if( !function_exists( 'otw_group_items' ) ){
	function otw_group_items( $items, $id, $parent, $level, $sub_level = 0 ){
		
		$result = array();
		
		if( is_array( $items ) && count( $items ) ){
			
			foreach( $items as $item ){
				
				if( $item->$parent == $level ){
					$item->_sub_level = $sub_level;
					$result[] = $item;
					
					$sub_items = otw_group_items( $items, $id, $parent, $item->$id, $sub_level + 1 );
					
					if( is_array( $sub_items ) && count( $sub_items ) ){
						foreach( $sub_items as $s_item ){
							$result[] = $s_item;
						}
					}
				}
			}
		}
		
		return $result;
	}
}

/** get the attribute of wp item
  *  @param string
  *  @param stdClass
  *  @return string
  */
if( !function_exists( 'otw_wml_wp_item_attribute' ) ){
	function otw_wml_wp_item_attribute( $item_type, $attribute, $object ){
		
		switch( $attribute ){
			
			case 'ID':
					switch( $item_type ){
						case 'postsincategory':
								return $object->cat_ID;
							break;
						case 'category':
								return $object->cat_ID;
							break;
						case 'postsintag':
								return $object->term_id;
							break;
						case 'posttag':
								return $object->term_id;
							break;
						case 'pagetemplate':
								return $object->script;
							break;
						case 'customposttype':
								return $object->name;
							break;
						case 'author_archive':
								return $object->ID;
							break;
						default:
								if( preg_match( "/^ctx_(.*)$/", $item_type, $matches ) ){
									return $object->term_id;
								}elseif( preg_match( "/^(.*)_in_ctx_(.*)$/", $item_type, $matches ) ){
									return $object->term_id;
								}
								
								return $object->ID;
							break;
					}
				break;
			case 'TITLE':
					switch( $item_type ){
						case 'page':
						case 'post':
								return $object->post_title;
							break;
						case 'customposttype':
								return $object->label;
							break;
						case 'author_archive':
								return $object->display_name;
							break;
						default:
								if( preg_match( "/^cpt_(.*)$/", $item_type, $matches ) ){
									return $object->post_title;
								}
								return $object->name;
							break;
					}
				break;
		}
	}
}

/** sidebar widgets hook
  *  @param array
  *  @return array
  */
if( !function_exists( 'otw_sidebars_widgets' ) ){
	function otw_sidebars_widgets( $sidebars_widgets ){
		
		global $otw_registered_sidebars, $otw_replaced_sidebars;
		
		if( !is_array( $otw_replaced_sidebars ) || !count( $otw_replaced_sidebars ) ){
		//	return $sidebars_widgets;
		}
		
		if( is_admin() ){
			return $sidebars_widgets;
		}
		
		foreach( $sidebars_widgets as $index => $widgets ){
			
			
			$tmp_index = otw_sbm_index( $index, $sidebars_widgets );
			
			if ( !empty($sidebars_widgets[$tmp_index]) ){
				$sidebars_widgets[$index] = otw_filter_siderbar_widgets( $tmp_index, $sidebars_widgets );
			}else{
				$sidebars_widgets[$index] = $sidebars_widgets[$tmp_index];
			}
			
		}
		return $sidebars_widgets;
	}
}

/**
 *  Load items by given params
 *  @param array attribute / type
 *  @return array
 **/
if (!function_exists( "otw_sbm_get_filtered_items" )){
	function otw_sbm_get_filtered_items( $type, $filter, $sidebar_id, $displayed_items = 20, $id_list = array() ){
	
		global $string_filter, $id_list_filter;
		
		$string_filter = $filter;
		$id_list_filter = $id_list;
		
		switch( $type )
		{
			case 'page':
			case 'post':
					$args = array();
					$args['post_type']      = $type;
					$args['posts_per_page'] = -1;
					if( count( $id_list_filter ) ){
						$args['post__in']       = $id_list_filter;
					}
					if( $string_filter ){
						add_filter( 'posts_where', 'otw_sbm_post_by_title' );
					}
					
					$posts_not_in = array();
					if( otw_installed_plugin( 'buddypress' ) ){
						
						global $bp;
						
						if( isset( $bp->pages->activity ) && $bp->pages->activity->id ){
							$posts_not_in[] = $bp->pages->activity->id;
						}
						if( isset( $bp->pages->members ) && $bp->pages->members->id ){
							$posts_not_in[] = $bp->pages->members->id;
						}
					}
					
					if( count( $posts_not_in ) ){
						$args['post__not_in'] = $posts_not_in;
					}
					
					$the_query = new WP_Query( $args );
					
					$all_items = count( $the_query->posts );
					
					$args['posts_per_page'] = ($displayed_items)?$displayed_items:-1;
					$args['orderby']        = 'ID';
					$args['order']          = 'DESC';
					
					$the_query = new WP_Query( $args );
					
					if( $string_filter ){
						remove_filter('posts_where', 'otw_sbm_post_by_title');
					}
					
					return array( $all_items, $the_query->posts );
				break;
			case 'category':
			case 'postsincategory':
					//first get all
					$args = array();
					$args['type']            = 'post';
					$args['hide_empty']      = 0;
					$args['number']          = 0;
					
					if( count( $id_list_filter ) ){
						sort( $id_list_filter );
						$args['include']  = $id_list_filter;
					}
					
					if( $string_filter ){
						$args['search'] = $string_filter;
					}
					
					$all_items = count( get_categories( $args ) );
					
					$args['number']          = ($displayed_items)?$displayed_items:0;
					$args['orderby']         = 'name';
					$args['order']           = 'ASC';
					
					return array( $all_items, get_categories( $args ) );
				break;
			case 'posttag':
			case 'postsintag':
					$args = array();
					$args['hide_empty']      = 0;
					$args['number']          = 0;
					
					if( count( $id_list_filter ) ){
						sort( $id_list_filter );
						$args['include']  = $id_list_filter;
					}
					
					if( $string_filter ){
						$args['search'] = $string_filter;
					}
					
					$all_items = count( get_terms( 'post_tag', $args ) );
					
					$args['number']          = ($displayed_items)?$displayed_items:0;
					$args['orderby']         = 'name';
					$args['order']           = 'ASC';
					
					return array( $all_items, get_terms( 'post_tag', $args ) );
				break;
			case 'author_archive':
					$args = array();
					$args['number']          = 0;
					
					if( count( $id_list_filter ) ){
						sort( $id_list_filter );
						$args['include']  = $id_list_filter;
					}
					
					if( $string_filter ){
						$args['search'] = '*'.$string_filter.'*';
					}
					
					$all_items = count( get_users( $args ) );
					
					$args['number']          = ($displayed_items)?$displayed_items:0;
					$args['orderby']         = 'display_name';
					$args['order']           = 'ASC';
					
					return array( $all_items, get_users( $args ) );
				break;

			case 'customposttype':
			case 'templatehierarchy':
			case 'pagetemplate':
			case 'archive':
					$items = otw_get_wp_items( $type );
					return array( count( $items ), $items );
				break;
			case 'userroles':
					$items = array();
					$wp_roles = new WP_Roles;
					$all_items = $wp_roles->get_names();
					$all_items['notlogged'] = __( 'Not Logged in' );
					
					foreach( $all_items as $u_role_code => $u_role_name ){
						
						if( $string_filter ){
							
							if( ( stripos( $u_role_name, $string_filter ) === false ) ){
								continue;
							}
						}
						
						$item = new stdClass();
						$item->ID = $u_role_code;
						if( $u_role_code != 'notlogged' ){
							$item->name = __( 'Logged in as ', 'otw_sbm' ).$u_role_name;
						}else{
							$item->name = $u_role_name;
						}
						
						$items[] = $item;
						
						if( $displayed_items > 0 && ( $displayed_items <= count( $items ) ) ){
							break;
						}
					}
					
					return array( count( $all_items ), $items );
				break;
			case 'wpmllanguages':
					if( function_exists( 'icl_get_languages' ) ){
						
						$wpml_languages = icl_get_languages( 'skip_missing=0' );
						
						$all_items = count( $wpml_languages );
						
						$items = array();
						foreach( $wpml_languages as $wpml_lang ){
							
							if( $string_filter ){
								
								if( ( stripos( $wpml_lang['translated_name'], $string_filter ) === false ) && ( stripos( $wpml_lang['translated_name'], $string_filter ) === false ) ){
									continue;
								}
							}
							
							$item = new stdClass();
							$item->ID = $wpml_lang['language_code'];
							$item->name = '<img src="'.$wpml_lang['country_flag_url'].'" alt="'.$wpml_lang['language_code'].'" border="0"/>&nbsp;'.$wpml_lang['native_name'];
							
							$items[] = $item;
							
						}
						return array( $all_items, $items );
					}
				break;
			case 'bbp_page':
					if( otw_installed_plugin( 'bbpress' ) ){
						
						$bbp_pages = array();
						
						$bbp_pages[] = array( 'id' => 'forums', 'name' => __( 'Forums', 'otw_sbm' ) );
						$bbp_pages[] = array( 'id' => 'noreplies', 'name' => __( 'Topics no reply', 'otw_sbm' ) );
						$bbp_pages[] = array( 'id' => 'mostpopular', 'name' => __( 'Topics popular', 'otw_sbm' ) );
						$bbp_pages[] = array( 'id' => 'search', 'name' => __( 'Search', 'otw_sbm' ) );
						$bbp_pages[] = array( 'id' => 'singleuser', 'name' => __( 'User pages', 'otw_sbm' ) );
						
						$all_items = count( $bbp_pages );
						
						$items = array();
						foreach( $bbp_pages as $bbp_page ){
							
							if( $string_filter ){
								
								if( stripos( $bbp_page['name'], $string_filter ) === false ){
									continue;
								}
							}
							
							$item = new stdClass();
							$item->ID = $bbp_page['id'];
							$item->name = $bbp_page['name'];
							
							$items[] = $item;
							
						}
						return array( $all_items, $items );
					}
				break;
			case 'buddypress_page':
					if( otw_installed_plugin( 'buddypress' ) ){
						global $bp;
						$buddypress_pages = array();
						
						if( isset( $bp->pages->activity ) && $bp->pages->activity->id ){
							$buddypress_pages[] = array( 'id' => $bp->pages->activity->id, 'name' => $bp->pages->activity->title.' '.__( 'page', 'otw_sbm' ) );
						}
						if( isset( $bp->pages->members ) && $bp->pages->members->id ){
							$buddypress_pages[] = array( 'id' => $bp->pages->members->id, 'name' => $bp->pages->members->title.' '.__( 'pages', 'otw_sbm' ) );
						}
						
						$all_items = count( $buddypress_pages );
						
						$items = array();
						foreach( $buddypress_pages as $buddypress_page ){
							
							if( $string_filter ){
								
								if( stripos( $buddypress_page['name'], $string_filter ) === false ){
									continue;
								}
							}
							
							$item = new stdClass();
							$item->ID = $buddypress_page['id'];
							$item->name = $buddypress_page['name'];
							
							$items[] = $item;
							
						}
						return array( $all_items, $items );
					}
					
				break;

			default:
					
					if( preg_match( "/^cpt_(.*)$/", $type, $matches ) ){
						
						$args = array();
						$args['post_type']      = $matches[1];
						$args['posts_per_page'] = -1;
						
						if( count( $id_list_filter ) ){
							$args['post__in']       = $id_list_filter;
						}
						
						if( $string_filter ){
							add_filter( 'posts_where', 'otw_sbm_post_by_title' );
						}
						$the_query = new WP_Query( $args );
						
						$all_items = count( $the_query->posts );
						
						$args['posts_per_page'] = ($displayed_items)?$displayed_items:-1;
						$args['orderby']        = 'ID';
						$args['order']          = 'DESC';
						
						$the_query = new WP_Query( $args );
						
						if( $string_filter ){
							remove_filter('posts_where', 'otw_sbm_post_by_title');
						}
						
						return array( $all_items, $the_query->posts );
					}elseif( preg_match( "/^ctx_(.*)$/", $type, $matches ) ){
						
						$args = array();
						$args['hide_empty']      = 0;
						$args['number']          = 0;
						
						if( count( $id_list_filter ) ){
							sort( $id_list_filter );
							$args['include']  = $id_list_filter;
						}
						
						if( $string_filter ){
							$args['search'] = $string_filter;
						}
						
						$all_items = count( get_terms( $matches[1], $args ) );
						
						$args['number']          = ($displayed_items)?$displayed_items:0;
						$args['orderby']         = 'name';
						$args['order']           = 'ASC';
						
						return array( $all_items, get_terms( $matches[1], $args ) );
					}elseif( preg_match( "/(.*)_in_ctx_(.*)$/", $type, $matches ) ){
						
						$args = array();
						$args['hide_empty']      = 0;
						$args['number']          = 0;
						
						if( count( $id_list_filter ) ){
							sort( $id_list_filter );
							$args['include']  = $id_list_filter;
						}
						
						if( $string_filter ){
							$args['search'] = $string_filter;
						}
						
						$all_items = count( get_terms( $matches[2], $args ) );
						
						$args['number']          = ($displayed_items)?$displayed_items:0;
						$args['orderby']         = 'name';
						$args['order']           = 'ASC';
						
						return array( $all_items, get_terms( $matches[2], $args ) );
					}
				break;
		}
		
		return array();
	}
}
if (!function_exists( "otw_sbm_post_by_title" )){
	function otw_sbm_post_by_title( $query ){
		
		global $string_filter, $id_list_filter;
		
		$query .= " AND post_title LIKE '%".$string_filter."%'";
		return $query;
	}
}
if( !function_exists( 'otw_get_strict_filters' ) ){
	function otw_get_strict_filters(){
		
		global $current_user;
		$filters = array();
		
		//apply user roles
		if ( function_exists('get_currentuserinfo') ){
			get_currentuserinfo();
		}
		
		if( isset( $current_user->ID ) && intval( $current_user->ID ) && isset( $current_user->roles ) && is_array( $current_user->roles ) && count( $current_user->roles ) ){
			
			$filter_key = count( $filters );
			$filters[ $filter_key ][0] = 'userroles';
			$filters[ $filter_key ][1] = array();
			foreach( $current_user->roles as $u_role ){
				$filters[ $filter_key ][1][] = $u_role;
			}
			$filters[ $filter_key ][2] = 'any';
		}
		else
		{
			$filter_key = count( $filters );
			$filters[ $filter_key ][0] = 'userroles';
			$filters[ $filter_key ][1] = array();
			$filters[ $filter_key ][1][] = 'notlogged';
			$filters[ $filter_key ][2] = 'any';
		}
		
		if( function_exists( 'icl_get_languages' ) && defined( 'ICL_LANGUAGE_CODE' ) ){
			
			$filter_key = count( $filters );
			$filters[ $filter_key ][0] = 'wpmllanguages';
			$filters[ $filter_key ][1] = array();
			$filters[ $filter_key ][1][] = ICL_LANGUAGE_CODE;
			$filters[ $filter_key ][2] = 'all';
		}
		return $filters;
	}
}
/**
 * check all colected widgets for a sidebar if match all strict filters
 * @param string sidebar index
 * @param array collected widgets
 * @return array
 */
if( !function_exists( 'otw_filter_strict_widgets' ) ){
	function otw_filter_strict_widgets( $index, $collected_widgets ){
		
		global  $wp_registered_sidebars;
		
		$filters = otw_get_strict_filters();
		
		$strict_filtered_widgets = $collected_widgets;
		
		if( is_array( $filters ) && count( $filters ) ){
			
			if( isset( $wp_registered_sidebars[ $index ] ) ){
				
				if( is_array( $strict_filtered_widgets ) && count( $strict_filtered_widgets ) ){
				
					$filters = otw_get_strict_filters();
					
					foreach( $collected_widgets as $widget => $widget_order){
						
						foreach( $filters as $filter ){
							
							switch( $filter[2] ){
								case 'any':
										$match_any = false;
										
										if( isset( $wp_registered_sidebars[$index]['widgets_settings'] ) &&  isset( $wp_registered_sidebars[$index]['widgets_settings'][$filter[0]] ) ){
											
											if( isset( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ]['_otw_wc'] ) && isset( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ]['_otw_wc'][ $widget ] ) && in_array( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ]['_otw_wc'][ $widget ] , array( 'vis', 'invis' ) )  ){
												
												if( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ]['_otw_wc'][ $widget ] == 'vis' ){
													$match_any = true;
												}
											}else{
												foreach( $filter[1] as $v_filter ){
													
													if( !isset( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ][ $v_filter ] ) || !isset( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ][ $v_filter ]['exclude_widgets'] ) || !isset( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ][ $v_filter ]['exclude_widgets'][$widget] ) ){
														$match_any = true;
														break;
													}
												}
											}
										}elseif( isset( $wp_registered_sidebars[$index]['widgets_settings'] ) && !isset( $wp_registered_sidebars[$index]['widgets_settings'][$filter[0]] ) ){
											$match_any = true;
										}
										
										if( !$match_any && isset( $strict_filtered_widgets[ $widget ] ) ){
											unset( $strict_filtered_widgets[ $widget ] );
										}
									break;
								case 'all':
										$dont_match_one = false;
										
										if( isset( $wp_registered_sidebars[$index]['widgets_settings'] ) &&  isset( $wp_registered_sidebars[$index]['widgets_settings'][$filter[0]] ) ){
										
											if( isset( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ]['_otw_wc'] ) && isset( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ]['_otw_wc'][ $widget ] ) ){
												
												if( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ]['_otw_wc'][ $widget ] == 'invis' ){
													$dont_match_one = true;
												}
											}else{
												foreach( $filter[1] as $v_filter ){
													
													if( isset( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ][ $v_filter ] ) && isset( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ][ $v_filter ]['exclude_widgets'] ) && isset( $wp_registered_sidebars[$index]['widgets_settings'][ $filter[0] ][ $v_filter ]['exclude_widgets'][$widget] ) ){
														$dont_match_one = true;
													}
												}
											}
										}
										
										if( $dont_match_one && isset( $strict_filtered_widgets[ $widget ] ) ){
											unset( $strict_filtered_widgets[ $widget ] );
										}
									break;
							}
						}
					}
				}
			}
		}
		return $strict_filtered_widgets;
	}
}
/**
 * check if given sidebar match all strict filters
 * @param index sidebar index
 * @return boolean
 */
if( !function_exists( 'otw_filter_strict_sidebar_index' ) ){
	function otw_filter_strict_sidebar_index( $index ){
		
		global $wp_registered_sidebars;
		
		$result = true;
		
		$filters = otw_get_strict_filters();
		
		if( is_array( $filters ) && count( $filters ) ){
			
			if( $result ){
				
				foreach( $filters as $filter ){
					
					switch( $filter[2] ){
					
						case 'any':
								$match_any = false;
								if( isset( $wp_registered_sidebars[ $index ]['validfor'][ $filter[0] ] ) && is_array( $wp_registered_sidebars[ $index ]['validfor'][ $filter[0] ] ) && count( $wp_registered_sidebars[ $index ]['validfor'][ $filter[0] ] ) ){
									
									if( isset( $wp_registered_sidebars[ $index ]['validfor'][ $filter[0] ]['all'] ) ){
										$match_any = true;
									}else{
										foreach( $filter[1] as $s_filter ){
											
											if( array_key_exists( $s_filter, $wp_registered_sidebars[ $index ]['validfor'][ $filter[0] ] ) ){
											
												$match_any = true;
												break;
											}
										}
									}
								}
								if( !$match_any ){
									$result = false;
								}
							break;
						case 'all':
								$dont_match_one = false;
								
								foreach( $filter[1] as $s_filter ){
								
									if( isset( $wp_registered_sidebars[ $index ]['validfor'][ $filter[0] ] ) && is_array( $wp_registered_sidebars[ $index ]['validfor'][ $filter[0] ] ) && count( $wp_registered_sidebars[ $index ]['validfor'][ $filter[0] ] ) ){
										
										if( !isset( $wp_registered_sidebars[ $index ]['validfor'][ $filter[0] ]['all'] ) ){
											
											if( !array_key_exists( $s_filter, $wp_registered_sidebars[ $index ]['validfor'][ $filter[0] ] ) ){
												$dont_match_one = true;
												break;
											}
										}
									}else{
										$dont_match_one = true;
										break;
									}
								}
								if( $dont_match_one ){
									$result = false;
								}
							break;
					}
				}
			}
		}
		
		return $result;
		
	}
}

/**
 * Check if external plugin is installed
 *
 * @param string - plugin name
 * @return boolean
 */
if( !function_exists( 'otw_installed_plugin' ) ){
	function otw_installed_plugin( $plugin_name ){
		
		$installed = false;
		switch( $plugin_name ){
			case 'bbpress':
					if(function_exists( 'bbp_get_db_version_raw') && bbp_get_db_version_raw() ){
						$installed = true;
					}
				break;
			case 'wpml':
					if( function_exists( 'icl_get_languages' ) ){
						$installed = true;
					}
				break;
			case 'buddypress':
					if( class_exists( 'BuddyPress' ) ){
						
						global $bp;
						
						if( strtolower( get_class( $bp ) ) == 'buddypress' )
						{
							$installed = true;
						}
					}
				break;
		}
		
		return $installed;
	}
}
?>