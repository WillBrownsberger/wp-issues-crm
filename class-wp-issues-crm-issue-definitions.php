<?php
/*
*
*
* definitions for Issues -- regular posts  . . . no meta fields?
*
*
*
*/

class WP_Issues_CRM_Issue_Definitions {



	public function __construct() {
		$this->wic_post_fields = $this->initialize_post_fields_array (); // more to be done here once code fields - sort.
		add_action('add_meta_boxes', array ( $this, 'wic_call_live_issue_meta_box' ), 10, 2);
		add_action('save_post', array ($this, 'wic_save_live_issue_meta_box' ),10,2);

	}
	/*
	* add metabox for post width, following 
	*  http://www.wproots.com/complex-meta-boxes-in-wordpress/
	*
	*/
	
	public $wic_post_fields = array();
		
	private function initialize_post_fields_array() { 
		$output = array(
					array( // 5
		 		'dedup'	=>	false,
		 		'group'	=>	'Case Management',
		 		'label'	=>	'assigned',
		 		'like'	=>	false,
				'list'	=> '0',
		 		'online'	=>	true,
		 		'order'	=>	50,	
				'required'	=> '',
				'select_array' => '',
				'slug'	=> 'activity_type',
		 		'type'	=>	'select',
			),);
		return $output;
	}
	
	
	function wic_call_live_issue_meta_box($post_type, $post)
	{
	   add_meta_box(
	       'wic_live_issue_setting_box',
	       'Issue Live for WP Issues CRM',
	       array( $this, 'wic_live_issue_meta_box' ),
	       'post',
	       'side',
	       'high'
	   );
	}
	
	
	function wic_live_issue_meta_box( $post, $args ) {
		
		global $wic_form_utilities;		
		
	   wp_nonce_field(site_url(__FILE__), 'wic_live_issue_metabox_noncename');
	
      $wic_live_issue_options = array(
			array(
				'value' =>	'closed',
				'label' =>  'Closed for WP Issues CRM'
			),
			array(
				'value' =>	'open',
				'label' =>  'Open for WP Issues CRM' 
			),
		);	 
	   
		$value = ( null !== get_post_meta($post->ID, 'wic_live_issue', true) ) ? esc_attr( get_post_meta($post->ID, 'wic_live_issue', true)) : '';	   
	   
		$args = array (
			'field_name_id' => 'wic_live_issue',
			'field_label'	=>	'',
			'value'	=> $value,
			'read_only_flag'		=>	false, 
			'field_label_suffix'	=> '',
			'placeholder'			=> __( 'Open/Closed for WIC?', 'wp-issues-crm' ),
			'select_array' =>	$wic_live_issue_options,							
		);	  

		echo '<p>' . $wic_form_utilities->create_select_control ( $args ) . '</p>';
	}
	
	function wic_save_live_issue_meta_box($post_id, $post) {
	   if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
	   	return;
		}
	       
	   if( ! current_user_can( 'edit_post', $post_id ) ) {
	           return;
	   }
	
	   if ( isset($_POST['wic_live_issue_metabox_noncename']) && 
	   		wp_verify_nonce($_POST['wic_live_issue_metabox_noncename'], site_url(__FILE__)) && check_admin_referer(site_url(__FILE__), 'wic_live_issue_metabox_noncename'))   
	   		{
	           update_post_meta($post_id, 'wic_live_issue', $_POST['wic_live_issue'] );
			   }
	   
	   return;
	}

}

$wic_issue_definitions = new WP_Issues_CRM_Issue_Definitions;