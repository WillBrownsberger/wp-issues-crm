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
	private static $activity_type_options = array ( 
		array(
			'value'	=> '0',
			'label'	=>	'eMail' ),
		array(
			'value'	=> '1',
			'label'	=>	'Call' ), 
		array(
			'value'	=> '2',
			'label'	=>	'Petition' ), 
		array(
			'value'	=> '3',
			'label'	=>	'Meeting' ),
		array(
			'value'	=> '4',
			'label'	=>	'Letter' ), 
		array(
			'value'	=> '5',
			'label'	=>	'Web Contact' ),
		array(
			'value'	=> '6',
			'label'	=>	'Conversion' ), 

		);

	public static function get_activity_type_options() {
		return self::$activity_type_options; 
	}

	public static function activity_date_set_default( $value )  {
		return ( date ( 'Y-m-d' ) );
	}	
	
	public static function get_issue_options( $value ) {
		
		$open_posts = WIC_DB_Access_WP::get_wic_live_issues();

		$issues_array = array();

		$value_in_option_list = false;			
		if ( $open_posts->have_posts() ) {		
			while ( $open_posts->have_posts() ) {
				$open_posts->the_post();
				$issues_array[] = array(
					'value'	=> $open_posts->post->ID,
					'label'	=>	$open_posts->post->post_title,
				);
				if ( $value == $open_posts->post->ID ) {
					$value_in_option_list = true;
				}
			}
		}
		
		if ( ! $value_in_option_list ) {
			$issues_array[] = array (
				'value'	=> $value,			
				'label'	=> get_the_title( $value ),
			);
		}		
		
		wp_reset_postdata();
		return $issues_array;

	}


	private static $pro_con_options = array ( 
		array(
			'value'	=> '0',
			'label'	=>	'Pro' ),
		array(
			'value'	=> '1',
			'label'	=>	'Con' ),
		);

	public static function get_pro_con_options() {
		return self::$pro_con_options; 
	}

	public static function get_activity_type_label( $lookup ) {
		foreach ( self::$activity_type_options as $select_item_array ) {
			if ( $lookup == $select_item_array['value'] ) {
				return ( $select_item_array['label'] );			
			} 
		}
	}

}