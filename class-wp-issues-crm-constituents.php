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
class WP_Issues_CRM_Constituents {

/*
*
* field definition array for constituents
*
*/
	private $constituent_fields = array( // fields must include so labelled first_name, last_name, email, otherwise may be replaced freely
	  			// 'slug',				'label',						'show online' 	'type' 		like search  . . . . not implemented: 'readonly', 'required', 'searchable'
		array( 'first_name', 		'First Name',					true,			'text', 		true,),		
		array( 'middle_name', 		'Middle Name',					false,		'text', 		false, ),		
		array( 'last_name', 			'Last Name',					true,			'text', 		true, ),		
		array( 'email', 				'eMail',							true,			'email', 	true,),		
		array( 'phone', 				'Land Line',					true,			'text',  	true,),		
		array( 'mobile_phone',		'Mobile Phone',				true,			'text', 		false, ),
		array( 'street_address', 	'Street Address',				true,			'text', 		true, ),
		array( 'city', 				'City',							true,			'text', 		false, ),
		array( 'state',				'State', 						true,			'text', 		false, ),
		array( 'zip',					'Zip Code', 					true,			'text', 		false, ),
		array( 'job_title', 			'Job Title',					true,			'text', 		false, ),
		array( 'organization_name', 'Organization',				true,			'text', 		false, ),
		array( 'gender_id', 			'Gender',						true,			array ( 'M', 'F'),	false, ),
		array( 'birth_date',			'Date of Birth',				true,			'date', 		false, ),
		array( 'is_deceased', 		'Is Deceased',					true,			'check',  	false, ),
		array( 'civicrm_id', 		'CiviCRM ID',					false,		'text',  	false,),
		array( 'ss_id',				'Secretary of State ID',	false,		'text', 		false, ),
		array( 'VAN_id', 				'VAN ID',						false,		'text', 		false, ),
	);
	
	private $error_flag = false;
	private $form_notices = '';
	private $single_hit_id = false;	
   private $wic_query = false;
   private $search_output = '';
   private $not_found_flag = false;
   	
	public function __construct() {
		add_shortcode( 'wp_issues_crm_constituents', array( $this, 'wp_issues_crm_constituents' ) );
	}

	public function wp_issues_crm_constituents() {

		/* first check capabilities -- must be administrative user */
		if ( ! current_user_can ( 'activate_plugins' ) ) { 
			echo '<h3>' . __( 'Sorry, this function is only accessible to administrators.', 'simple-wp-crm' ) . '<h3>';
			return;
		} 
		
		* 
		/* 
		*
		* if form has been submitted 
		*
		*/
		if( isset ( $_POST['main_button'] ) ) {
			
			// carry through post id through multiple resubmits 
			if ( isset ( $_POST['constituent_id'] ) {
				$this->single_hit_id = $_POST['constituent_id'];
			}

			// if search has been submitted and nonce OK, process as search  
			if( 'search_submitted' == $_POST['main_button']  && isset($_POST['wp_issues_crm_constituent_search_nonce_field']) && 
				wp_verify_nonce($_POST['wp_issues_crm_constituent_search_nonce_field'], 'wp_issues_crm_constituent_search'))	{
			// hack until build import interface        	
        	if ($_POST['first_name'] == 'import_this_muffa'  &&  $_POST['last_name'] == 'not_playing') 
	        	{$this->import();} else {
				$this->search_constituents();
			} // close not import this mf
	 		} //close search submitted

			//if update or save has been submitted and nonce OK, process as update or save 
			*
			*/
			if	( 	( 'update_submitted' == $_POST['main_button']  && isset($_POST['wp_issues_crm_constituent_update_nonce_field']) && 
						wp_verify_nonce( $_POST['wp_issues_crm_constituent_update_nonce_field'], 'wp_issues_crm_constituent_update') ) || 
					( 'save_new_record' == $_POST['main_button']  && isset($_POST['wp_issues_crm_constituent_save_new_nonce_field']) && 
						wp_verify_nonce( $_POST['wp_issues_crm_constituent_save_new_nonce_field'], 'wp_issues_crm_constituent_save_new') ) 
				) { 
				$this->save_update_constituent();
			} // close test of nonces on update save conditions	 		
 		} // close if main button clicked 
  
  		/*
  		*
  		* display form with parameters set based on search/update/save posture
  		*
  		*/
		echo '<br /><br /><span style="color:green;"> $_POST:';  		
  		var_dump ($_POST);
  		echo '</span><br />'; 
  		/*
  		* default case -- unvisited or reset form  
  		*/
  		if ( ! isset ( $_POST ['main_button'] ) ) {
  		  	$main_button_value	= 'search_submitted';	
	     	$main_button_label 	= 'Search Constituents';
	     	$this->form_notices = 'Search for constituents.';
	     	$nonce_mode   = 'search';
		/*
		* search submitted cases
		*/
	   } elseif ( 'search_submitted' == $_POST['main_button'] ) {
			// if single record found looking up form values from record to offer update 	
	   	if ( $this->single_hit_id ) {
		  	  	$main_button_value	= 'update_submitted';	
	   	  	$main_button_label 	= 'Update Constituent Record';
	   	  	$this->form_notices = 'Constituent record found -- update constituent or reset form.';
	   	  	$nonce_mode = 'update';
			// no records found in search mode 
   		}	elseif ( $this->not_found_flag ) {
	  		  	$main_button_value	= 'save_new_record';	
		     	$main_button_label 	= 'Save New Record';
		     	$this->form_notices =  __( 'No matching record found -- you can save as a new record.', 'wp-issues-crm' );
		     	$nonce_mode   = 'save_new';   	  	
			// multiple records found in search mode    	
   		} else {
	  		  	$main_button_value	= 'search_submitted';	
		     	$main_button_label 	= 'Search Constituents';
		    	$this->form_notices =  $this->wic_query->found_posts . __( ' constituents found. Search again or browse and select from list below.', 'wp-issues-crm' );
		     	$nonce_mode   = 'search';
	     	}
   	/*
   	* update submitted cases
   	*/	     	
     	} elseif ( 'update_submitted' == $_POST['main_button'] ) {
	     	$main_button_value = 'update_submitted';
	     	$main_button_label = 'Update Constituent Record'; 
	  		$nonce_mode = 'update';
     		if ( $this->error_flag ) {
	     		$this->form_notices .= __( ' Please correct and resubmit update.', 'wp-issues-crm' );
     		} else {
	     		$this->form_notices = __( 'Update Successful. Showing record as updated.', 'wp-issues-crm' );
  			}
  		/*
  		* save submitted cases (error processing depends on whether have changed uniqueness of record after offering save)
  		*/
     	} elseif ( 'save_new_record' == $_POST['main_button'] ) {
     		if ( $this->error_flag ) {
     			if ( $this->not_found_flag ) {
			     	$main_button_value = 'save_new_record';
			     	$main_button_label = 'Save New Constituent Record'; 
			  		$nonce_mode = 'save';
		     		$this->form_notices .= __( ' Please correct and resubmit new record.', 'wp-issues-crm' );
	     		} elseif ( $this->single_hit_id ) {
			     	$main_button_value = 'update_submitted';
			     	$main_button_label = 'Update Constituent Record'; 
			  		$nonce_mode = 'update';
		     		$this->form_notices .= __( ' Matching record found.  Proceed to update.', 'wp-issues-crm' );
				} elseif ( $this->search_output> '' ) {
		  		  	$main_button_value	= 'search_submitted';	
			     	$main_button_label 	= 'Search Constituents';
			  		$nonce_mode = 'search';
		     		$this->form_notices .= __( ' Multiple records found. Not saved.', 'wp-issues-crm' ); 
	     		}    		
     		} else {
		     	$main_button_value = 'update_submitted';
		     	$main_button_label = 'Update Constituent Record'; 
		  		$nonce_mode = 'update';
	     		$this->form_notices = __( 'New record saved. Showing record as saved.', 'wp-issues-crm' );
  			} 
  		}
	  ?>
		<form id = "constituent-search-form" method="POST">
			<?php 
			/* notices section */
			if ( $this->form_notices != '' ) { 
				$message_class = $this->error_flag ? 'wp-issues-crm-warning' : 'wp-issues-crm-update'; ?>
		   	<div id="new-post-message-box" class="<?php echo $message_class; ?>"><strong><em><?php echo $this->form_notices; ?></em></strong></div>
		   <?php }
		   /* input meta fields */
			foreach ( $this->constituent_fields as $field ) {
				if( $field[2] ) {
					switch ( $field[3] ) {
						case 'email':						
						case 'text':
						case 'date':
							if ( $this->single_hit_id && 'search_submitted' == $_POST['main_button'] ) {
								$post_field_key =  '_wic_' . $field[0];
								$value = $this->wic_query->post->$post_field_key;
							} elseif ( isset ( $_POST['reset_button'] ) ) { 
								$value = '';							
							} else {
								$value = isset ($_POST[$field[0]]) ? sanitize_text_field( $_POST[$field[0]] ) : '';
							}
							$contains = $field[4] ? __( '(contains)', 'wp-issues-crm' ) : '';
							?><p><label for="<?php echo $field[0] ?>"><?php echo __( $field[1], 'wp-issues-crm' ) . ' ' . $contains; ?></label>
							<input  id="<?php echo $field[0] ?>" name="<?php echo $field[0] ?>" type="text" value="<?php echo $value; ?>" /></p><?php 
							break;
					}
				}
			}   
			/* input field for constituent notes (post content) */
			if ( 'update' == $nonce_mode || 'save' == $nonce_mode ) { 
				remove_filter( 'the_content', 'wpautop' );
				if (  isset ( $this->wic_query->post ) ) echo 'yes, mf';
				$notes_value =  ( isset ( $this->wic_query ) ) ? $this->wic_query->post->post_content : $_POST['constituent_notes']; ?>
				<p><label for="constituent_notes"><?php _e( "Constituent Notes", 'wp-issues-crm' ); ?></label></p>				
				<p><textarea id="constituent_notes" name="constituent_notes" rows="10" cols="50"><?php echo apply_filters ( 'the_content', $notes_value ); ?></textarea></p>
				<input type = "hidden" id = "constituent_ID" name = "constituent_ID" value ="<?php echo $this->single_hit_id; ?>" />  
			<?php 
				add_filter( 'the_content', 'wpautop' );
			} 
	  
	  		/* buttons and nonces */ ?>
	  		<button id="main_button" name="main_button" type="submit" value = "<?php echo $main_button_value; ?>"><?php _e( $main_button_label, 'wp_issues_crm'); ?></button>	  
	  		<button id="reset_button" name="reset_button" type="submit" value = "<?php echo 'reset_form' ?>"><?php _e( 'Reset Form', 'wp_issues_crm'); ?></button>
	 		<?php wp_nonce_field('wp_issues_crm_constituent_' . $nonce_mode , 'wp_issues_crm_constituent_' . $nonce_mode . '_nonce_field', true, true); ?>

		</form>
		
		<?php
		/* 
		* display search results below form 
		*/		
		
		echo $this->search_ouput;
		unset ( $this->search_ouput ) ;

   } /* close function wp_issues_crm_constituents */

	/*
	*
	*  constituent search function
	*
	*/
   private function search_constituents() {
   	$meta_query_args = array(
     		'relation'=> 'AND',
     	);
		$index = 1;
 		foreach ( $this->constituent_fields as $field ) {
 			if( isset( $_POST[$field[0]] ) ) {
 				$value = sanitize_text_field ( $_POST[$field[0]] );
 				if ( $value > '' ) {
					$meta_query_args[$index] = array(
						'key' 	=> '_wic_' . $field[0], // wants key, not meta_key, otherwise searches across all keys 
						'value'		=> $value,
						'compare'	=>	( $field[4] ? 'LIKE' : '=' ),
					);	 
					$index++;
				}	
 			}
 		}
 		
 		$query_args = array (
 			'posts_per_page' => 50,
 			'post_type' 	=>	'wic_constituent',
 			'meta_query' 	=> $meta_query_args, 
 		);
 
 		$this->wic_query = new WP_Query($query_args);
 		if ($this->wic_query->found_posts > 1 ) {
			$this->search_ouput =  '<h1> Found ' . $this->wic_query->found_posts . ' constituents, showing ' . $this->wic_query->post_count . ' </h1>';			
			$this->search_ouput .= '<ul>';	 		
	 		while ( $this->wic_query->have_posts() ) {
				$this->wic_query->next_post();
				$this->search_ouput .= '<li>' . $this->wic_query->post->_wic_first_name .
								  ' ' . $this->wic_query->post->_wic_last_name . 
								  ', ' . $this->wic_query->post->_wic_street_address .
								  ', ' . $this->wic_query->post->_wic_email .
					 '</li>'; 	 		
	 		}
	 		$this->search_ouput .= '</ul>';
	 		
		} else if ( 1 == $this->wic_query->found_posts ) { 
	 			$this->single_hit_id = $this->wic_query->post->ID;
	 	} else {
				$this->not_found_flag = true;
				$this->single_hit_id = false;			 	
	 	}			
		wp_reset_postdata();
   }
	/*
	*
	*	form validation function
	*
	*/   
   
   private function validate_input() {
   	
   	$this->search_constituents();
   	
   	if ( ! $this->not_found_flag ) {
			$this->form_notices .= __( 'Record no longer unique. ', 'wp-issues-crm' );
			$this->error_flag = true;			   	
   	}
   	
   	$this->form_notices = '';
   	if ( '' == sanitize_text_field ( $_POST['first_name'] ) &&  '' == sanitize_text_field ( $_POST['last_name'] ) && '' == sanitize_text_field ( $_POST['email'] ) ) {
			$this->form_notices .= __( 'Please enter at least one of first name, last name or email. ', 'wp-issues-crm' );
			$this->error_flag = true;			   	
   	}
   	
   	foreach ( $this->constituent_fields as $field ) {
			if ( isset( $_POST[$field[0]] ) ) {
   			$_POST[$field[0]] = sanitize_text_field( $_POST[$field[0]] );
	   		if ( $_POST[$field[0]] > '' ) {
		   		if	( "email" == $field[3] && ! filter_var( $_POST[$field[0]], FILTER_VALIDATE_EMAIL ) ) {
		   			$this->error_flag = true;
		   			$this->form_notices .= __( 'Email address is not valid. ', 'wp-issues-crm' );
					}	
		   		if	( "date" == $field[3] )  {
	   				$test = new DateTime( $_POST[$field[0]] );
	   				if ( $test ) {
	   					 $_POST[$field[0]] = date_format($test, 'Y-m-d' );
	   				} else {
			   			$this->error_flag = true;
			   			$this->form_notices .= __( 'Please reenter date -- YY/MM/DD will work. ', 'wp-issues-crm' );
		   			}
					}						   		
	   		}
   		} 
   	}
		
		$_POST['constituent_notes'] = wp_kses_post ( $_POST['constituent_notes'] );   	
   	
   } 
   /*
   *
	*  save_update_constituent() 
	*
	*/
   private function save_update_constituent() {
   	
		$this->validate_input();
		if ( ! $this->error_flag ) {
			// title is ln OR ln,fn OR fn OR email -- one of these is required in validation to be non-blank.	
			$title = 	isset ( $_POST['last_name'] ) ? $_POST['last_name'] : '';
			$title .= 	isset ( $_POST['first_name'] ) ? ( $title > '' ? ', ' : '' ) . $_POST['first_name'] : '';
			$title =		( '' == $title ) ? $_POST['email'] : $title;
			
			$post = array(
			  'post_content'   => $_POST['constituent_notes'], 
			  'post_title'     => $title,
			  'post_status'    => 'private',
			  'post_type'      => 'wic_constituent',
			  'comment_status' => 'closed' 
			); 
			
			if ( $this->single_hit_id > 0 ) {
				$post['ID'] = $this->single_hit_id;
			} elseif ( isset ( $_POST['constituent_ID'] ) ) {
				$post['ID'] = $_POST['constituent_ID'];
			}					
			
			$return_post_id = wp_insert_post( $post );
			echo  'returning' . $return_post_id;
			if ( ! $return_post_id ) {
				$this->error_flag = true;
				$this->form_notices = __( 'Unknown error. Could not save/update constituent record.  Reset form, search for constituent and check results.', 'wp-issues-crm' );
				return;					
			} else {
				$this->single_hit_id = $return_post_id; // to carry through on repeat updates	if did a save		
			}
								
			foreach ( $this->constituent_fields as $field ) {
				if( $field[2] ) {
					$meta_return = update_post_meta ( $return_post_id, '_wic_' . $field[0], $_POST[$field[0]] );
				}
				if ( ! $meta_return ) {
					$this->error_flag = true;
					$this->form_notices = __( 'Unknown error. Could not save/update constituent details.  Reset form, search for constituent and check results.', 'wp-issues-crm' );
					return;					
				}
			}
			
								

		}				
	}	  


   /* 
   *
   *
   *
   * import 
   *
   */
   function import(){ // fields must include so formatted first_name, last_name, email
	   
	   $i=0;
	   $j=0;
	   $seconds = 5000;
		set_time_limit ( $seconds );
	   
	   global $wpdb;
	   $contacts = $wpdb->get_results( 'select * from wp_swc_contacts' );
	   foreach ($contacts as $contact ) {
			if ( $i/1000 == floor($i/1000 ) ) {
				echo '<h3>' . $i . ' records processed</h3>';			
			}	   
		   $i++;
		   // if ($i>10) break;
		   			
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
					if ( $contact->$field[0] > '' ) {
						$stored_record = add_post_meta ($post_id, '_wic_' . $field[0], $contact->$field[0] );
						if ( $stored_record ) {
							$j++;						
						}
					}				
				} 
			}
		}
		echo '<h1>' . $i . ' constituent records in total processed</h1>';
		echo '<h1>' . $j . ' meta records in total stored</h1>';
	}
	
	
}	

$wp_issues_crm = new WP_Issues_CRM_Constituents;



 		
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