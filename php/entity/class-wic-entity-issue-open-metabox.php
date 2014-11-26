<?php
/*
*
*	class-wic-entity-issue-open-metabox.php
*
*/

Class WIC_Entity_Issue_Open_Metabox {

	public function __construct() {
		add_action('add_meta_boxes', array ( $this, 'wic_call_live_issue_meta_box' ), 10, 2);
		add_action('save_post', array ($this, 'wic_save_live_issue_meta_box' ),10,2);
	}
	
	/* following http://www.wproots.com/complex-meta-boxes-in-wordpress/ */	
	function wic_call_live_issue_meta_box($post_type, $post)
	{
	   add_meta_box(
	       'wic_live_issue_setting_box',
	       __( 'Issue Open/Closed for WP Issues CRM', 'wp-issues-crm' ),
	       array( $this, 'wic_live_issue_meta_box' ),
	       'post',
	       'side',
	       'high'
	   );
	}
	
	
	function wic_live_issue_meta_box( $post, $args ) {
		
		global $wic_form_utilities;		
		
	   wp_nonce_field( site_url(__FILE__), 'wic_live_issue_metabox_noncename' );
	
      $wic_live_issue_options = array(
			array(
				'value' =>	'',
				'label' =>  'Open/Closed?' 
			),
			array(
				'value' =>	'open',
				'label' =>  'Open for WP Issues CRM' 
			),
			array(
				'value' =>	'closed',
				'label' =>  'Closed for WP Issues CRM'
			),
		);	 
	   
		$value = ( null !== get_post_meta($post->ID, 'wic_data_wic_live_issue', true) ) ? esc_attr( get_post_meta($post->ID, 'wic_data_wic_live_issue', true)) : '';	   
	   
		$args = array (
			'field_slug' => 'wic_data_wic_live_issue',
			'field_label'	=>	'',
			'label_class'	=>	'',
			'input_class'	=>	'',
			'field_slug_css' 	=>	'',
			'value'	=> $value,
			'field_label_suffix'	=> '',
			'option_array' => $wic_live_issue_options, 
		);	  

		echo '<p>' . WIC_Control_Select::create_control ( $args) . '</p>';
	}
	
	function wic_save_live_issue_meta_box($post_id, $post) {
	   if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
	   	return;
		}
	       
	   if( ! current_user_can( 'edit_post', $post_id ) ) {
	           return;
	   }
	
	   if ( isset($_POST['wic_live_issue_metabox_noncename']) && 
   		wp_verify_nonce($_POST['wic_live_issue_metabox_noncename'], site_url(__FILE__)) && check_admin_referer(site_url(__FILE__), 'wic_live_issue_metabox_noncename')) {
           	update_post_meta($post_id, 'wic_data_wic_live_issue', $_POST['wic_data_wic_live_issue'] );
		   }
   
	   return;
	}

	public function title_callback( &$next_form_output ) {
		
		// for title, use group email if have it, otherwise use individual email 
		$title = isset ( $next_form_output['post_title'] ) ? $next_form_output['post_title'] : 'untitled';  
		
		return  ( $title );
	}
	
}

$wic_issue_open_metabox = new WIC_Entity_Issue_Open_Metabox;