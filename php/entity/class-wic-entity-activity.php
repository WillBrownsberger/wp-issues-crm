<?php
/*
*
*	wic-entity-activity.php
*
*/



class WIC_Entity_Activity extends WIC_Entity_Multivalue {

	public function update_row() {
		$new_update_row_object = new WIC_Form_Activity_Update ( $this->entity, $this->entity_instance );
		$new_update_row = $new_update_row_object->layout_form( $this->data_object_array, null, null );
		return $new_update_row;
	}



	protected function set_entity_parms( $args ) {
		extract ( $args );
		$this->entity = 'activity';
		$this->entity_instance = $instance;
	} 

/**************************************************
*
*  Support for particular object properties
*
***************************************************/


	public static function get_issue_options( $value ) {
		
		$open_posts = WIC_DB_Access_WP::get_wic_live_issues();

		$issues_array = array( array ( 'value' => '' , 'label' => __( 'Activity Issue?', 'wp-issues-crm' ) ) );	

		$value_in_option_list = false;			
		foreach ( $open_posts as $open_post ) {		
			$issues_array[] = array(
				'value'	=> $open_post->ID,
				'label'	=>	esc_html ( $open_post->post_title ),
			);
			if ( $value == $open_post->ID ) {
				$value_in_option_list = true;
			}
		}
		
		if ( ! $value_in_option_list && $value > '' ) {
			$issues_array[] = array (
				'value'	=> $value,			
				'label'	=> get_the_title( $value ),
			);
		}		
		
		return ( $issues_array );
	}


}