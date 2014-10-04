<?php
/*
* File: class-wp-issues-crm-constituents.php
*
* Description: this class manages the front end constituent search/update/add process -- note that these functions and also deletes can be done through backend 
* 
* @package wp-issues-crm
* 
*
*/ 

// http://code.tutsplus.com/articles/create-wordpress-plugins-with-oop-techniques--net-20153
class WP_Issues_CRM_Constituents_List {
	/*
	*
	* field definitions for ready reference array 
	*
	*/
		
	private $constituent_fields = array();
	public $constituent_list;

	public function __construct( &$wic_query) {
		add_shortcode( 'wp_issues_crm_constituents', array( $this, 'wp_issues_crm_constituents' ) );
		global $wic_definitions;
		foreach ( $wic_definitions->constituent_fields as $field )
			if ( $field['online'] ) { 		
 				 array_push( $this->constituent_fields, $field );
 			}
		$this->wic_metakey = &$wic_definitions->wic_metakey;
		$this->constituent_list = $this->format_constituent_list($wic_query);
	}


  public function format_constituent_list( &$wic_query) {
 		// deliver the results
 		
		$output = '<form method="POST"><h1> Found ' . $wic_query->found_posts . ' constituents, showing ' . $wic_query->post_count . ' </h1>';	
		$output .= '<ul>';
		while ( $wic_query->have_posts() ) {
		$wic_query->next_post();
		$output .= '<li><button id="direct_button" name="direct_button" type="submit" class = "constituent-list-button" value =" '.  $wic_query->post->ID . '">' . $wic_query->post->wic_data_first_name .
		' ' . $wic_query->post->wic_data_last_name .
		', ' . $wic_query->post->wic_data_street_address .
		', ' . $wic_query->post->wic_data_email .
		'</button></li>';
		}
		$output .= 	wp_nonce_field( 'wp_issues_crm_constituent', 'wp_issues_crm_constituent_nonce_field', true, true ) .
		'</form>'; 
		
		return $output;
   } // close function
}	

// $wic_list_constituents = new WP_Issues_CRM_Constituents_List;