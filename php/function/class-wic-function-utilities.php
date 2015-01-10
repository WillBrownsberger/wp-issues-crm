<?php
/**
*
* class-wic-functions.pp
*
**/
 

class WIC_Function_Utilities { // functions that serve multiple entities	
	
	/*
	*  display option array of administrators of the system
	*/	
	public static function get_administrator_array() {
	
		$user_select_array = array();	
	
		$roles = array( 'Administrator', 'Editor', 'wic_constituent_manager' ) ;
		
			foreach ( $roles as $role ) {
			$user_query_args = 	array (
				'role' => $role,
				'fields' => array ( 'ID', 'display_name'),
			);						
			$user_list = new WP_User_query ( $user_query_args );
	
	
			foreach ( $user_list->results as $user ) {
				$temp_array = array (
					'value' => $user->ID,
					'label'	=> $user->display_name,									
				);
				array_push ( $user_select_array, $temp_array );								
			} 

		}
		
		return ( $user_select_array );

	}

	/*
	* extract label from value/label array
	*/
	public static function value_label_lookup ( $value, $options_array ) {
		if ( '' ==  $value ) {
			return ( '' );	
		}	
		foreach ( $options_array as $option ) {
				if ( $value == $option['value'] ) {
					return ( $option['label'] );			
				} 
			}
		return ( sprintf ( __('Option value (%s) missing in look up table.', 'wp-issues-crm' ), $value ) );
	}
	
	/*
	* convert dirty string with various possible white spaces and commas into clean compressed comma separated	
	*/
	public static function sanitize_textcsv ( $textcsv ) {
		
		$temp_tags = explode ( ',', $textcsv );
		
		$temp_tags2 = array();
		foreach ( $temp_tags as $tag ) {
			if ( sanitize_text_field ( stripslashes ( $tag ) ) > '' ) {
				$temp_tags2[] = sanitize_text_field ( stripslashes ( $tag ) );
			}			
		}
		$output_textcsv = implode ( ',', $temp_tags2 );
		return ( $output_textcsv );
	}	

}
