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
		global $wic_definitions;
		foreach ( $wic_definitions->constituent_fields as $field )
			if ( $field['list'] > 0 ) { 		
 				 array_push( $this->constituent_fields, $field );
 			}
		$this->constituent_list = $this->format_constituent_list($wic_query);
	}


  public function format_constituent_list( &$wic_query) {
 		global $wic_definitions;
		$output = '<div id="wic-constituent-list"><form method="POST">' . 
			'<div class = "constituent-field-group wic-group-odd"><h2> Found ' . $wic_query->found_posts . ' constituents, showing ' . $wic_query->post_count . ' </h2></div>';
		$line_count = 1;	
		$output .= '<ul class = "constituent-list">' .  // out ul for the list
			'<li class = "cl-odd">' .							// header is a list item with a ul within it
				'<ul class = "cl-headers">';				
					foreach ( $this->constituent_fields as $field ) {
						$output .= '<li class = "cl-header cl-' . $field['slug'] . '">' . $field['label'] . '</li>';			
					}
			$output .= '</ul></li>';
			while ( $wic_query->have_posts() ) {
				$wic_query->next_post();
				$line_count++;
				$row_class = ( 0 == $line_count % 2 ) ? "cl-even" : "cl-odd";
				$output .= '<li><button name="direct_button" type="submit" class = "constituent-list-button ' . $row_class . '" value =" '.  $wic_query->post->ID . '">';		
				$output .= '<ul class = "constituent-list-line">';			
					foreach ( $this->constituent_fields as $field ) {
						$key = $wic_definitions->wic_metakey . $field['slug'];
						$output .= '<li class = "cl-field cl-' . $field['slug'] . ' "> ';
						if ( isset ( $wic_query->post->$key ) ) {
							$output .= $wic_query->post->$key;
						}							
						$output .= '</li>';			
					}
			$output .='</ul></button></li>';
		}		
		$output .= '</ul>';
		$output .= 	wp_nonce_field( 'wp_issues_crm_constituent', 'wp_issues_crm_constituent_nonce_field', true, true ) .
		'</form></div>'; 
		
		return $output;
   } // close function
}	

// $wic_list_constituents = new WP_Issues_CRM_Constituents_List;