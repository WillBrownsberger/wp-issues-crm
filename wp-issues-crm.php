<?php
/**
 * Plugin Name: WP Issues CRM
 * Plugin URI: 
 * Description: Constituent Relationship Management for organizations that respond to constituents primarily on issues (e.g., legislators); does support basic case notes as well. 
 * Version: 1.0
 * Author: Will Brownsberger
 
 * License: GPL2
 *
 *  Copyright 2014  WILL BROWNSBERGER  (email : willbrownsberger@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

include plugin_dir_path( __FILE__ ) . 'definitions.php';




/**
 * SEARCH FORM AND LIST
 * 
 * 
 *
 */

// http://code.tutsplus.com/articles/create-wordpress-plugins-with-oop-techniques--net-20153
class WP_Issues_CRM {

/*
*
* field definition array for constituents
*
*/
	private $constituent_fields = array( 
	  			// 'slug',				'label',						'show online' 	'type' not implemented: 'readonly', 'required', 'searchable'
		array( 'first_name', 		'First Name',					true,			'text', ),		
		array( 'middle_name', 		'Middle Name',					false,		'text', ),		
		array( 'last_name', 			'Last Name',					true,			'text', ),		
		array( 'email', 				'eMail',							true,			'email',),		
		array( 'phone', 				'Land Line',					true,			'text', ),		
		array( 'mobile_phone',		'Mobile Phone',				true,			'text', ),
		array( 'street_address', 	'Street Address',				true,			'text', ),
		array( 'city', 				'City',							true,			'text', ),
		array( 'state',				'State', 						true,			'text', ),
		array( 'zip',					'Zip Code', 					true,			'text', ),
		array( 'job_title', 			'Job Title',					true,			'text', ),
		array( 'organization_name', 'Organization',				true,			'text', ),
		array( 'gender_id', 			'Gender',						true,			array ( 'M', 'F'), ),
		array( 'birth_date',			'Date of Birth',				true,			'date', ),
		array( 'is_deceased', 		'Is Deceased',					true,			'check' ),
		array( 'civicrm_id', 		'CiviCRM ID',					false,		'text', ),
		array( 'ss_id',				'Secretary of State ID',	false,		'text', ),
		array( 'VAN_id', 				'VAN ID',						false,		'text', ),
	);
	
	private $post_message = 'Welcome to the search screen';

	public function __construct() {
		add_shortcode( 'wp_issues_crm', array( $this, 'wp_issues_crm' ) );
	}

	public function wp_issues_crm() {

		/* first check capabilities -- must be administrative user */
		if ( ! current_user_can ( 'activate_plugins' ) ) { 
			echo '<h3>' . __( 'Sorry, this function is only accessible to administrators.', 'simple-wp-crm' ) . '<h3>';
			return;
		} 
 
		/* test if form has been submitted and check nonce -- if so, process $_POST input */
		
		if(isset($_POST['submitted']) && isset($_POST['wp_issues_crm_constituent_search_nonce_field']) && 
		wp_verify_nonce($_POST['wp_issues_crm_constituent_search_nonce_field'], 'wp_issues_crm_constituent_search'))	{
			 
        	if ($_POST['first_name'] == 'import_this_muffa' ) 
        	{$this->import();} else {
		       

      	$meta_query_args = array(
        		'relation'=> 'AND',
        	);
			$index = 1;
	 		foreach ( $this->constituent_fields as $field ) {
	 			if( isset( $_POST[$field[0]] ) ) {
	 				if ( $_POST[$field[0]] > '' ) {
						$meta_query_args[$index] = array(
							'meta_key' 	=> '_wic_' . $field[0],
							'value'		=> $_POST[$field[0]],
							'compare'	=>	'=',
						);	 
						$index++;
					}	
	 			}
	 		}
	 		
	 		$query_args = array (
	 			'post_type' 	=>	'wic_constituent',
	 			'meta_query' 	=> $meta_query_args, 
	 		);
	 		
	 		$wic_query = new WP_Query($query_args);
			echo '<ul>';	 		
	 		while ( $wic_query->have_posts() ) {
				$wic_query->next_post();
				echo '<li>' . $wic_query->post->post_title . '</li>'; 	 		
	 		}
			echo '</ul>';
			var_dump( $wic_query->query);
		} // close not import this mf
 		} //close if cleared nonce check 
  
  		/* display form */
		echo '<br /> $_POST:';  		
  		var_dump ($_POST);
     $add_post = 'shit';
	  ?>
		<form id = "constituent-search-form" method="POST">
			<?php 
			$has_error = 1; 
			if ( $this->post_message != '' ) { 
				$message_class = $has_error ? 'wp-issues-crm-warning' : 'wp-issues-crm-update'; ?>
		   	<div id="new-post-message-box" class="<?php echo $message_class; ?>"><?php echo $this->post_message; ?></div>
		   <?php }
			foreach ( $this->constituent_fields as $field ) {
				if( $field[2] ) {
					switch ( $field[3] ) {
						case 'text':
							$value = isset ($_POST[$field[0]]) ? $_POST[$field[0]] : '';
							?><p><label for="<?php echo $field[0] ?>"><?php _e( $field[1], 'wp_issues_crm' ); ?></label>
							<input  id="<?php echo $field[0] ?>" name="<?php echo $field[0] ?>" type="text" value="<?php echo $value; ?>" /></p><?php
							break;
						case 'email':
							$value = isset ($_POST[$field[0]]) ? $_POST[$field[0]] : '';
							?><p><label for="<?php echo $field[0] ?>"><?php _e( $field[1], 'wp_issues_crm' ); ?></label>
							<input  id="<?php echo $field[0] ?>" name="<?php echo $field[0] ?>" type="text" value="<?php echo $value; ?>" 
							placeholder="<?php _e( 'Enter a valid email if available', 'wp_issues_crm' )?>" /></p><?php
							break;
							/* insert dates and text logic */
					}
				}
			}   ?>
	  
	  		<button id="new_post_submit" type="submit"><?php _e($add_post, 'wp_issues_crm'); ?></button>	  
	 		<?php wp_nonce_field('wp_issues_crm_constituent_search', 'wp_issues_crm_constituent_search_nonce_field', true, true); ?>
			<input type="hidden" name="submitted" id="submitted" value="true" />

		</form>
		<?php













   }
   /* import */
   public function import(){
	   
	   $i=0;
	   $seconds = 5000;
		set_time_limit ( $seconds );
	   
	   global $wpdb;
	   $contacts = $wpdb->get_results( 'select * from wp_swc_contacts' );
	   foreach ($contacts as $contact ) {
			if ( $i/1000 == floor($i/1000 ) ) {
				echo '<p>' . $i . 'records processed</p>';			
			}	   
		   $i++;
		   if ($i>3) break;
		   			
			$post_information = array(
				'post_title' => wp_strip_all_tags ( $contact->last_name . ', ' . $contact->first_name ),
				'post_type' => 'wic_constituent',
				'post_status' => 'private',
				'post_author' => 15,
				'ping_status' => 'closed',
			);
		
			$post_id = wp_insert_post($post_information);
			
			foreach ($this->constituent_fields as $field) {			
				if ( isset ( $contact->$field[0] ) ) {
					e
					if ( $contact->$field[0] > 0 and $contact->$field[0] > '' ) {
						add_post_meta ($post_id, '_wic_' . $field[0], $contact->$field[0] );							
					}				
				} 
			}
		}
		echo '<p>' . $i . 'records in total processed</p>';
	}
	
	
}	

$wp_issues_crm = new WP_Issues_CRM;



 		
/*
*
* code to save new posts on submission 
*
*

$post_ID = 0;
$post_message = '';
$has_error = false;

// do nothing unless submitted and passed nonce test 
if(isset($_POST['submitted']) && isset($_POST['twcc_front_page_new_post_nonce_field']) && 
		wp_verify_nonce($_POST['twcc_front_page_new_post_nonce_field'], 'twcc_front_page_new_post')) 
		{
			
      
   	$post_author = get_current_user_id() ? get_current_user_id() : $instance['guest_post_id'];	
		// var_dump($_POST);


		// for anonymous users, check current user id
		if (!get_current_user_id()) { 
				if(strlen(trim($_POST['post_guest_author'])) < 4) {
						$post_message .= '<li>Please enter a name with 4 or more characters.</li>';
						$has_error = true;
					}
						
					if(!filter_var(trim($_POST['post_guest_author_email']), FILTER_VALIDATE_EMAIL)) {
						$post_message .= '<li>Please enter a valid email.</li>';
						$has_error = true;
					} 
				}
		 
		// require some quantum of content to be entered    
		if(strlen(trim($_POST['post_title'])) < 6) {
			$post_message .= '<li>Please enter a title with 6 or more characters.</li>';
			$has_error = true;
		} 
		
		if(strlen(trim($_POST['twcc_new_post_content'])) < 50) {
			$post_message .= '<li>Post content must have at least 50 characters.</li>';
			$has_error = true;
		} 

      if($has_error == true) { 
          	$post_message = 'Your post has errors: <ul id= "new-post-error-list">'  . $post_message . '</ul>';
				$post_id = $_POST['post_id']; // if we are in this branch, form was submitted but had errors -- want to carry post_id whether 0 or already set
		}				
   	else {// if no errors, spam check and save or udpate post

					// note, need to use generic akismet interface as Wordpress plugin class 
					//does not offer functions serving non-comments compare bbpress which writes its own class using elements from 
					//the main plugin and the class referenced here  
		   
		   $comment = array(
		        'author'    => trim($_POST['post_guest_author']),
		        'email'     => trim($_POST['post_guest_author_email']),
		        'body'      => trim($_POST['post_content']),
		        'permalink' => 'http://localhost/?frontpagetab=4'
		   );
		  

			
		
			
			$default_comment_status = get_option('default_comment_status');
			
			$post_information = array(
				'post_title' => stripslashes($_POST['post_title']),
				'post_content' => $_POST['twcc_new_post_content'],
				'post_type' => 'post',
				'post_status' => $new_post_status,
				'post_author' => $post_author,
				'post_category' => array($_POST['twcc_new_post_cat']),
				'comment_status' => $default_comment_status, //(take the default value)
				'ID' =>$_POST['post_id']
			);
		
			$post_id = wp_insert_post($post_information);
		

			
			if($post_id > 0)	{
			   if ($_POST['post_id']> 0) {	
			 		$post_message .= 'Your post, #' . $post_id . ', has been updated.'; 
			 	}
				else {
					$post_message .= 'Your new post has been submitted as post #'.$post_id . '.';
				}	
				$post_message .= '  You can edit below or ';
				if ($new_post_status =='pending' || $new_post_status == 'new_post_spam')
					{$post_message .= '<a href="/">return to home.</a> Your post is pending review.';}
				elseif ($new_post_status=='publish')
						{	$post_message .= '<a href="/?p='.$post_id.'"> view your finalized post.</a>';	}
			} // close $post_id > 0 i.e., message on successful insert or update
		} // close insert/updates conditional on passing error tests

} // close nonce tested updates




 
 		<?php if(get_current_user_id() == 0) { ?>
    					<p><input type="text" name="post_guest_author" id="post_guest_author" value="<?php if ( isset( $_POST['post_guest_author'] ) ) echo $_POST['post_guest_author']; ?>"/>
						<label for="post_guest_author"><?php _e('Your Name', 'twcc_text_domain') ?></label></p>		       
			        <p><input type="text" name="post_guest_author_email" id="post_guest_author_email" value="<?php if ( isset( $_POST['post_guest_author_email'] ) ) echo $_POST['post_guest_author_email']; ?>"/>
						<label for="post_guest_author_email"><?php _e('Your Email (will not be published)', 'twcc_text_domain') ?></label></p>		
		<?php } ?>
 
      <p><input type="text" name="post_title" id="post_title" value="<?php if ( isset( $_POST['post_title'] ) ) echo stripslashes($_POST['post_title']); ?>"/>
    	<label for="post_title"><?php _e('Post Title', 'twcc_text_domain') ?></label></p>
      
       <textarea name="twcc_new_post_content" id="twcc_new_post_content" rows="15" cols="60" class="required"><?php if ( isset( $_POST['twcc_new_post_content'] ) ) { if ( function_exists( 'stripslashes' ) ) { echo stripslashes( $_POST['twcc_new_post_content'] ); } else { echo $_POST['twcc_new_post_content']; } } ?></textarea>
               <!-- post Category -->
		<br /> <br />       
       <?php if($instance['show_category_select']) { 	            
                        
             	$dropdown_cat_args = array(
             			'show_option_all'    => '',
							'show_option_none'   => '',
							'orderby'            => 'name', 
							'order'              => 'ASC',
							'show_count'         => 0,
							'hide_empty'         => 0, 
							'child_of'           => 0,
							'exclude'            => '',
							'echo'               => 1,
							'selected'           => (int)$_POST['twcc_new_post_cat'],
							'hierarchical'       => 1, 
							'name'               => 'twcc_new_post_cat',
							'id'                 => 'twcc_new_post_cat',
							'class'              => 'postform',
							'depth'              => 0,
							'tab_index'          => 0,
							'taxonomy'           => 'category',
							'hide_if_empty'      => false,
             	);              
             
              wp_dropdown_categories($dropdown_cat_args ); ?>
          		<label for="twcc_new_post_cat">Discussion Category</label>
	             <br/><br />
	   <?php } ?>
	   

*/