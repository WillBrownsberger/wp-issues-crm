<?php
/*
*
*
*
*
*
*
*/

class WP_Issues_CRM_Issues_Definitions {

	public function __construct() {
		
		add_action('add_meta_boxes', array ( $this, 'wic_live_issue_meta_box' ), 10, 2);
		add_action('save_post', array ($this, 'wic_save_live_issue_meta_box' ),10,2);

	}
	/*
	* add metabox for post width, following 
	*  http://www.wproots.com/complex-meta-boxes-in-wordpress/
	*
	*/
	
	function wic_live_issue_meta_box($post_type, $post)
	{
	   add_meta_box(
	       'wic_live_issue_box',
	       'Issue Live for WP Issues CRM',
	       'wic_live_issue_box',
	       'post',
	       'side',
	       'high'
	   );
	}
	
	
	function twcc_post_width_meta_box($post, $args)
	{
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
	
	
	
	
	
	function wic_save_live_issue_meta_box($post_id, $post)
	{
	   if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
	       return;
	
	   if('page' == $_POST['post_type'])
	   {
	       if(!current_user_can('edit_page', $post_id))
	           return;
	   }
	   else
	       if(!current_user_can('edit_post', $post_id))
	           return;
	
	   if(isset($_POST['wic_live_issue_metabox_noncename']) && wp_verify_nonce($_POST['wic_live_issue_metabox_noncename'], site_url(__FILE__)) && check_admin_referer(site_url(__FILE__), 'twcc_metabox_noncename'))
	   {
	           update_post_meta($post_id, 'wic_live_issue', $_POST['wic_live_issue'] );
	   }
	   return;
	}

}

$wic_issues_definitions = new WP_Issues_CRM_Issues_Definitions;