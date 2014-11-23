<?php
			

	



	
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