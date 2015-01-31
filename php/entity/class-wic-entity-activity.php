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

		$user_id = get_current_user_id();		

		// get last issue viewed by user or modified by user (if not author may not show as mod)
		$args = array ( 'id_requested' => $user_id ); // spoof a button handoff by the request handler
		$entity = new WIC_Entity_Issue( 'get_latest_no_form', $args ); // initialize the data_object_array with the latest
		$latest_viewed_issue = $entity->get_current_ID_and_title(); // pull info from the doa

		// initialize issues dropdown array with blank and latest viewed
		$issues_array = array( 
			array ( 'value' => '' , 'label' => __( 'Activity Issue?', 'wp-issues-crm' ) ),
			array ( 'value' => $latest_viewed_issue['current'] , 'label' => $latest_viewed_issue['title'] ),
		);	

		// set up to make sure that the existing value, on an update, appears in the list		
		$value_in_option_list = ( $value == $latest_viewed_issue['current'] );

		// add latest used issues to the array
		$recent_issues = WIC_DB_Access_WP::get_wic_latest_issues( $user_id, 3 );
		foreach ( $recent_issues as $recent_issue ) {		
			$issues_array[] = array(
				'value'	=> $recent_issue->ID,
				'label'	=>	esc_html ( $recent_issue->post_title ),
			);
			if ( $value == $recent_issue->ID ) {
				$value_in_option_list = true;
			}
		}
		
		// add WIC live issues to the array
		$open_posts = WIC_DB_Access_WP::get_wic_live_issues();			
		foreach ( $open_posts as $open_post ) {		
			$issues_array[] = array(
				'value'	=> $open_post->ID,
				'label'	=>	esc_html ( $open_post->post_title ),
			);
			if ( $value == $open_post->ID ) {
				$value_in_option_list = true;
			}
		}
		
		// add current value if missing
		if ( ! $value_in_option_list && $value > '' ) {
			$issues_array[] = array (
				'value'	=> $value,			
				'label'	=> get_the_title( $value ),
			);
		}		
		
		return ( $issues_array );
	}


}