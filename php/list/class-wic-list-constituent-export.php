<?php
/*
* File: class-wic-list-constituent_export.php
*
* Description: isolates export functions which are custom and bypasses database isolation classes 
* 
* @package wp-issues-crm
*
*/ 

class WIC_List_Constituent_Export {
	/*
	*
	*
	*/
	public static function assemble_list_for_export ( &$wic_query ) {
				// convert the array objects from $wic_query into a string

  		$id_list = '(';
		foreach ( $wic_query->result as $result ) {
			$id_list .= $result->ID . ',';		
		} 	
  		$id_list = trim($id_list, ',') . ')';

   	// go direct to database and do customized search
   	// create a new WIC access object and search for the id's

		global $wpdb;	

		$sql = 	"SELECT first_name, last_name,  
						GROUP_CONCAT( DISTINCT email_address SEPARATOR ';' ) as emails, 
						max( if ( address_type = 0, city, ' ' ) ) as city, 
						GROUP_CONCAT( DISTINCT phone_number SEPARATOR ';' ) as phones,
						max( if ( address_type = 0, address_line, ' '	) ) as address_line_1,
						max( if ( address_type = 0, concat ( city, ', ', state, ' ',  zip ), ' ' ) ) as address_line_2,
						max( if ( address_type = 0, zip, ' ' ) ) as zip, 
						c.* 
					FROM wp_wic_constituent c
					left join wp_wic_email e on e.constituent_id = c.Id
					left join wp_wic_phone p on p.constituent_id = c.ID
					left join wp_wic_address a on a.constituent_id = c.ID	
					WHERE c.ID IN $id_list
					GROUP BY c.ID
					"; 

		$results = $wpdb->get_results ( $sql, ARRAY_A ); 

		return ( $results );
	}


	public static function do_constituent_download ( $id ) {
		
		if ( ! current_user_can ( 'activate_plugins' ) ) { 
			WIC_Function_Utilities::wic_error ( 'Download permissions inadequate.', __FILE__, __LINE__, __METHOD__, true );			 
		} 		
		
		if ( isset($_POST['wp_issues_crm_post_form_nonce_field']) &&
			check_admin_referer( 'wp_issues_crm_post', 'wp_issues_crm_post_form_nonce_field')) 
		{ } WIC_Function_Utilities::wic_error ( 'Apparent cross-site scripting or configuration error.', __FILE__, __LINE__, __METHOD__, true );

		$search = WIC_DB_Access::get_search_from_search_log( $id );	
		$current_user = wp_get_current_user();		

		$wic_query = WIC_DB_Access_Factory::make_a_db_access_object( $search['entity'] );
		
		$search_parameters = array (		
			'select_mode' 		=> 'id',
			'sort_order' 		=> true,
			'compute_total' 	=> false,
			'retrieve_limit' 	=> 999999999,
			'show_deleted'		=> false
		);

		$wic_query->search ( $search['meta_query_array'], $search_parameters );

		if ( 'constituent' == $search['entity'] ) {
			$results = self::assemble_list_for_export( $wic_query ); 
		} elseif ( 'issue' ==  $search['entity'] ) {
			$issue_array = array();
			foreach ( $wic_query->result as $issue ) {
				$issue_array[] = $issue->ID;
			}
			$args = array (			
				'id_array' => $issue_array,
				'search_id' => $id,
				);
			$comment_query = new WIC_Entity_Comment ( 'get_constituents_by_issue_id', $args );
			$results = self::assemble_list_for_export( $comment_query ); 

		} 

		$fileName = 'wic-export-' . $current_user->user_firstname . '-' .  current_time( 'Y-m-d-H-i-s' )  .  '.csv' ; 
		
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Description: File Transfer');
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename={$fileName}");
		header("Expires: 0");
		header("Pragma: public");
		
		$fh = @fopen( 'php://output', 'w' );
		
		global $wpdb;
		$headerDisplayed = false;
		
		foreach ( $results as $data ) {
		    if ( !$headerDisplayed ) {
		        fputcsv($fh, array_keys($data));
		        $headerDisplayed = true;
		    }
		    fputcsv($fh, $data);
		}
		fclose($fh);

		WIC_DB_Access::mark_search_as_downloaded( $id );
		
		exit;
	}

}	

