<?php
/*
* File: class-wp-issues-crm-constituents.php
*
* Description: this class manages the front end constituent search/update/add process -- note that these functions and also deletes can be done through backend 
* 
* @package wp-issues-crm
* add esc_attr, esc_html
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
		global $wic_constituent_definitions;
		foreach ( $wic_constituent_definitions->constituent_fields as $field )
			if ( $field['list'] > 0 ) { 		
 				 array_push( $this->constituent_fields, $field );
 			}
		$this->constituent_list = $this->format_constituent_list($wic_query);
	}


  public function format_constituent_list( &$wic_query) {
 		global $wic_constituent_definitions;
 		global $wic_base_definitions;
 		global $wic_form_utilities;
		$output = '<div id="wic-constituent-list"><form method="POST">' . 
			'<div class = "constituent-field-group wic-group-odd">
				<h2> Found ' . $wic_query->found_posts . ' constituents, showing ' . $wic_query->post_count . '</h2>' . 
				'<button id = "form-toggle-button-on-list" type="button" onclick = "toggleConstituentForm()">' . __( 'Show Search', 'wp-issues-crm' ) . '</button>' .
				'<button id = "constituent-export-button" class = "wic-form-button" type="button" >' . __( 'Export (not built yet)', 'wp-issues-crm' ) . '</button>' .
				'</div>';
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
				$output .= '<li><button name="wic_constituent_direct_button" type="submit" class = "constituent-list-button ' . $row_class . '" value =" '.  $wic_query->post->ID . '">';		
				$output .= '<ul class = "constituent-list-line">';			
					foreach ( $this->constituent_fields as $field ) {
						$key = $wic_base_definitions->wic_metakey . $field['slug'];
						$output .= '<li class = "cl-field cl-' . $field['slug'] . ' "> ';
						if ( isset ( $wic_query->post->$key ) ) {
							if ( ! is_array ( $wic_query->post->$key ) ) {
								$output .= esc_html ( $wic_query->post->$key );
							} else {
								// NB $wic_query->post->$key)[0][1] gets evaluated to whole ->post array b/c $key[0][1] evaluates to empty; 
								// cannot fix with parens, so two step this
								$row_array = $wic_query->post->$key;
								if ( 'phones' == $field['type'] ) { 
									$output .= $wic_form_utilities->format_phone($row_array[0][1]);
								} else {
									$output .= esc_html ( $row_array[0][1] );								
								}							
  							}
						}							
						$output .= '</li>';			
					}
			$output .='</ul></button></li>';
		}		
		$output .= '</ul>';
		$output .= 	wp_nonce_field( 'wp_issues_crm_post', 'wp_issues_crm_post_form_nonce_field', true, true ) .
		'</form></div>'; 
		
		return $output;
   } // close function
}	

