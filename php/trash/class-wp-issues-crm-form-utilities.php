<?php
			

	
	public function create_multi_select_control ( $control_args ) {
		
		/* $control_args = array (
			'field_name_id' => name/id
			'field_label'	=>	label for field
			'placeholder' => label that will appear in drop down for empty string
			'value'		=> initial value 
			'label_class'			=> for css
			'field_input_class'			=> for css
			'select_array'	=>	the options for the selected -- key value array with keys 'value' and 'label' 
			'field_label_suffix'	=> any string to append to the field label in control (but not in drop down)
		*/								

		$label_suffix = '';
		$value = '';
		$label_class = 'wic-multi-select-group-label';
		$field_input_class = 'wic-input';
		$placeholder = '';
		$value = array(); 

		extract ( $control_args, EXTR_OVERWRITE ); 
		
		$field_label_suffix_span = ( $field_label_suffix > '' ) ? '<span class="wic-form-legend-flag">' .$field_label_suffix . '</span>' : '';

		$control = ( $field_label > '' ) ? '<label class="' . $label_class . '" for="' . esc_attr( $field_name_id ) . '">' . esc_attr( $field_label ) . '</label>' : '' ;

		$control .= '<div class = "wic_multi_select">';
				
		foreach ( $select_array as $option ) {

			$args = array(
				'field_name_id' 		=> $field_name_id . '[' . $option['value'] . ']',
				'field_label'			=>	$option['label'],
				'label_class'			=> 'wic-multi-select-label '  . $option ['class'],
				'input_class'			=> 'wic-multi-select-checkbox ', 
				'value'					=> in_array ( $option['value'], $value, false ),
				'read_only_flag'		=>	false,
				'field_label_suffix'	=> '',						
			);	
			$control .= '<p class = "wic_multi_select_item" >' . $this->create_check_control($args) . '</p>';
			
		}
		$control .= '</div>';
		return ( $control );
	
	}	



	/*
	* convert string with various possible white spaces and commas into comma separated	
	*/
	public function resanitize_individual_textcsv ( $textcsv ) {
		
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
	
	
	public function drop_down_issues() {
		
		global $wic_database_utilities;		
				
		$wic_issues_query = $wic_database_utilities->get_open_issues();

		$issues_array = array();
		
		if ( $wic_issues_query->have_posts() ) {		
			while ( $wic_issues_query->have_posts() ) {
				$wic_issues_query->the_post();
				$issues_array[] = array(
					'value'	=> $wic_issues_query->post->ID,
					'label'	=>	$wic_issues_query->post->post_title,
				);
			}
		}
		
		wp_reset_postdata();
		return $issues_array;

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


	public function format_select_array ( $select_array, $format, $select_parameter ) {
		
		$select_array = is_array( $select_array ) ? $select_array : $this->$select_array( $select_parameter );
		if ( 'control' == $format ) {
			return ( $select_array );		
		} elseif ( 'lookup' == $format ) {
			$reformatted_select_array = array();
			foreach ( $select_array as $pair ) {
				$reformatted_select_array[$pair['value']] = $pair['label'];
			} 	
			return ( $reformatted_select_array );
		}	
	}
	
}

$wic_form_utilities = new WP_Issues_CRM_Form_Utilities;