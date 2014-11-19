
	


/*******************************************************************************
*
*  WIC Text CSV Field
*
*******************************************************************************/

class WIC_TextCSV_Control extends WIC_Control_Parent {

	/*
	* convert string with various possible white spaces and commas into comma separated	
	*/
	public function sanitize ( $textcsv ) {
		
		$textcsv = strip_tags( sanitize_text_field ( $textcsv ) );
		
		$temp_tags = str_replace ( ',', ' ', $textcsv )	;	
		$temp_tags = explode ( ' ', $textcsv );
		
		$temp_tags2 = array();
		foreach ( $temp_tags as $tag ) {
			if ( trim($tag) > '' ) {
				$temp_tags2[] = trim($tag);
			}			
		}
		$output_textcsv = implode ( ',', $temp_tags); 		
		return ( $output_textcsv );
	}	

}
 
  
   

    	
/*
   	$clean_input['wic_post_id'] = absint ( $_POST['wic_post_id'] ); // always included in form; 0 if unknown;
		$clean_input['strict_match']	=	isset( $_POST['strict_match'] ) ? true : false; // only updated on the form; only affects search_wic_posts
		$clean_input['initial_form_state'] = 'wic-form-open';	
		
   } 
	

	

	
	

	
	

	public function create_multi_select_control ( $control_args ) {
		
		/* $control_args = array (
			'field_slug' => name/id
			'field_label'	=>	label for field
			'placeholder' => label that will appear in drop down for empty string
			'value'		=> initial value 
			'label_class'			=> for css
			'field_input_class'			=> for css
			'select_array'	=>	the options for the selected -- key value array with keys 'value' and 'label' 
			'field_label_suffix'	=> any string to append to the field label in control (but not in drop down)
										

		$label_suffix = '';
		$value = '';
		$label_class = 'wic-multi-select-group-label';
		$field_input_class = 'wic-input';
		$placeholder = '';
		$value = array(); 

		extract ( $control_args, EXTR_OVERWRITE ); 
		
		$field_label_suffix_span = ( $field_label_suffix > '' ) ? '<span class="wic-form-legend-flag">' .$field_label_suffix . '</span>' : '';

		$control = ( $field_label > '' ) ? '<label class="' . $label_class . '" for="' . esc_attr( $field_slug ) . '">' . esc_attr( $field_label ) . '</label>' : '' ;

		$control .= '<div class = "wic_multi_select">';
				
		foreach ( $select_array as $option ) {

			$args = array(
				'field_slug' 		=> $field_slug . '[' . $option['value'] . ']',
				'field_label'			=>	$option['label'],
				'label_class'			=> 'wic-multi-select-label '  . $option ['class'],
				'input_class'			=> 'wic-multi-select-checkbox ', 
				'value'					=> in_array ( $option['value'], $value, false ),
				'readonly'		=>	false,
				'field_label_suffix'	=> '',						
			);	
			$control .= '<p class = "wic_multi_select_item" >' . $this->create_check_control($args) . '</p>';
			
		}
		$control .= '</div>';
		return ( $control );
	
	}	




	public function wic_get_post_title( $post_id ) {

/*		global $wic_database_utilities;	
		$post_query = $wic_database_utilities->wic_get_post( $post_id );
		$title = $post_query->posts[0]->post_title;
		wp_reset_postdata();
*/
		$title = get_the_title ( $post_id ); 
		return $title;
					
	}

	public function wic_get_user_list ( $role ) {
		/* query users with specified role (s) -- empty string returns all */
		$user_query_args = 	array (
			'role' => $role,
			'fields' => array ( 'ID', 'display_name'),
		);						
		$user_list = new WP_User_query ( $user_query_args );

		$user_select_array = array();
		foreach ( $user_list->results as $user ) {
			$temp_array = array (
				'value' => $user->ID,
				'label'	=> $user->display_name,									
			);
			array_push ( $user_select_array, $temp_array );								
		} 

		return ( $user_select_array );

	}


	public $category_select_array = array();
	private $category_array_depth = 0;

	public function wic_get_category_list ( $parent ) {
		
		$this->category_array_depth++;		
		
		$args = array(
			'orderby'                  => 'name',
			'order'                    => 'ASC',
			'hide_empty'               => 0,
			'taxonomy'                 => 'category',
			'pad_counts'               => true, 
			'parent'							=> $parent,
		); 

		$categories = get_categories( $args );
		if ( 0 < count ( $categories ) ) {		
			foreach ( $categories as $category ) {
				$temp_array = array (
					'value' => $category->term_id,
					'label' => $category->name,
					'class' => 'wic-multi-select-depth-' . $this->category_array_depth,
				);			
				$this->category_select_array[] = $temp_array;
				$this->wic_get_category_list ($category->term_id);	
			}
		}
		$this->category_array_depth--;
		return ( $this->category_select_array );
	} 



	public function wic_get_post_categories ( $post_id ) {
		 
		$categories = get_the_category ( $post_id );
		$return_list = '';
		foreach ( $categories as $category ) {
			$return_list .= ( '' == $return_list ) ? $category->cat_name : ', ' . $category->cat_name;		
				}
		return ( $return_list ) ;	
	}

	public function wic_get_post_author_display_name ( $user_id ) {
		
		$display_name = '';		
		if ( isset ( $user_id ) ) { 
			if ( $user_id > 0 ) {
				$user =  get_users( array( 'fields' => array( 'display_name' ), 'include' => array ( $user_id ) ) );
				$display_name = $user[0]->display_name; // best to generate an error here if this is not set on non-zero user_id
			}
		}
		return ( $display_name );
	}


	
}

$wic_form_utilities = new WP_Issues_CRM_Form_Utilities;