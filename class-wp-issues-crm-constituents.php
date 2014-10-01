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
	  			// 'slug',				'label',						'show online' 	'type' 		like search   dedup search . . . . not implemented: 'readonly', 'required', 'searchable'
		array( 'first_name', 		'First Name',					true,			'text', 		true,				true,	),		
		array( 'middle_name', 		'Middle Name',					false,		'text', 		false, 			false,),		
		array( 'last_name', 			'Last Name',					true,			'text', 		true, 			true,),		
		array( 'email', 				'eMail',							true,			'email', 	true,				true,),		
		array( 'phone', 				'Land Line',					true,			'text',  	true, 			false,),		
		array( 'mobile_phone',		'Mobile Phone',				true,			'text', 		false, 			false,),
		array( 'street_address', 	'Street Address',				true,			'text', 		true, 			true,),
		array( 'city', 				'City',							true,			'text', 		false, 			false,),
		array( 'state',				'State', 						true,			'text', 		false, 			false,),
		array( 'zip',					'Zip Code', 					true,			'text', 		false, 			false,),
		array( 'job_title', 			'Job Title',					true,			'text', 		false, 			false,),
		array( 'organization_name','Organization',				true,			'text', 		false, 			false,),
		array( 'gender_id', 			'Gender',						false,		array ( 'M', 'F'),false,false, ),
		array( 'birth_date',			'Date of Birth',				true,			'date', 		false, 			false,),
		array( 'is_deceased', 		'Is Deceased',					false,		'check',  	false, 			false,),
		array( 'civicrm_id', 		'CiviCRM ID',					false,		'text',  	false,			false,),
		array( 'ss_id',				'Secretary of State ID',	false,		'text', 		false, 			false,),
		array( 'VAN_id', 				'VAN ID',						false,		'text', 		false, 			false,),
	);
	

	private $button_actions = array(
		'save' 	=>	'Save New Constituent Record',
		'search' => 'Search Constituents',
		'update' => 'Update Constituent Record',
	);
 	
	private $unset_value = null; // used as placeholder in function call by pointer	
 	
	public function __construct() {
		add_shortcode( 'wp_issues_crm_constituents', array( $this, 'wp_issues_crm_constituents' ) );
	}

	public function create_dup_check_fields_list() {
		$fields_string = '';
		foreach ( $this->constituent_fields as $field ) {
			if( $field[5] ) {
				$fields_string = ( $fields_string > '' ) ? $fields_string . ', ' : '';
				$fields_string .= $field[1];
			}		
		}
		return ( $fields_string . '.' );	
	}

	public function wp_issues_crm_constituents() {
		
		/* first check capabilities -- must be administrative user */
		if ( ! current_user_can ( 'activate_plugins' ) ) { 
			echo '<h3>' . __( 'Sorry, this function is only accessible to administrators.', 'simple-wp-crm' ) . '<h3>';
			return;
		} 

		if ( isset ( $_POST['main_button'] ) ) { // what did the user just ask to do
			$user_request = $_POST['main_button'];
		} elseif ( isset ( $_POST['redo_search_button'] ) ) {
			$user_request = 'search';	
		} else { 
			$user_request = 'reset';
		}
		
		 
		if( 'reset' == $user_request ) { // if form has not been submitted or has been reset		
			$this->display_form( 'reset', 'search', __( 'Enter values and search for constituents.', 'wp-issues-crm' ), $this->unset_value, null ) ;	
		} else {
			if( ! wp_verify_nonce($_POST['wp_issues_crm_constituent_nonce_field'], 'wp_issues_crm_constituent'))	{
				die ( 'Security check failed.' ); // if not nonce OK, die, otherwise continue  
			} else {
				//if ($_POST['first_name'] == 'import_this_muffa'  &&  $_POST['last_name'] == 'not_playing') {$this->import();} 
				$is_dedup_check = ( 'search' == $user_request ) ? false : true; // if doing update or save, $is_dedup_check  
				$wic_query = $this->search_constituents( $is_dedup_check, false ); // always need to do test for dup based on latest values -- false means not a direct test
				if ( 0 == $wic_query->found_posts && isset ( $_POST['constituent_id'] ) && ! isset( $_POST['redo_search_button'] ) ) {
					$wic_query = $this->search_constituents( $is_dedup_check, $_POST['constituent_id'] );				
				} 
				
				switch ( $user_request ) {	
					case 'search':
						if ( 0 == $wic_query->found_posts ) {
						  	$this->display_form( $user_request, 'save', __('No matching record found -- you can save as a new record.', 'wp-issues-crm' ) , $this->unset_value, null );
						} elseif ( 1 == $wic_query->found_posts ) {
							$this->display_form( $user_request, 'update', __('One matching record found -- you can update this record.', 'wp-issues-crm' ), $wic_query, null );
						} else {
							$this->display_form( $user_request, 'search', __( 'Adjust values to search for different constituents.', 'wp-issues-crm' ), $this->unset_value, null );
							echo $this->format_constituent_list( $wic_query );
						}						
						break;
					case 'update':							
						if ( 0 == $wic_query->found_posts || ( 1 == $wic_query->found_posts && $wic_query->post->ID == $_POST['constituent_id'] ) ) {
							$validation_errors = $this->validate_input();
							if ( $validation_errors > '' ) {
								$this->display_form( $user_request,  'update', __( 'Please correct form errors: ', 'wp-issues-crm' ) . $validation_errors, $this->unset_value, $_POST['constituent_id'] );
							} else {
								$return_post_id = $this->save_update_constituent( $_POST['constituent_id'], $wic_query );
								if ( ! is_numeric( $return_post_id ) ) { // alpha return_post_id is error string
									$this->display_form( $user_request, 'update', __( 'Please retry -- there were database errors. ', 'wp-issues-crm' ) . $return_post_id, $this->unset_value, $_POST['constituent_id'] );
								} else {
									$this->display_form( $user_request, 'update', __( 'Update successful -- you can further update this record.', 'wp-issues-crm' ), $this->unset_value,  $_POST['constituent_id'] );								
								}					
							}
						} else {
							$this->display_form( $user_request, 'search', __( 'Record not updated -- other records match the new combination of  ', 'wp-issues-crm' ) . $this->create_dup_check_fields_list(), $this->unset_value, null );
							echo $this->format_constituent_list( $wic_query );
						}						
						break;				
					case 'save':	
						if ( 0 == $wic_query->found_posts ) {
							$validation_errors = $this->validate_input();
							if ( $validation_errors > '' ) {
								$this->display_form( $user_request, 'save', __( 'Please correct form errors: ', 'wp-issues-crm' ) . $validation_errors, $this->unset_value, null );
							} else {
								$return_post_id = $this->save_update_constituent( false, $this->unset_value );
								if ( ! is_numeric( $return_post_id  ) ) { // alpha return_post_id is error string
									$this->display_form( $user_request, 'save', __( 'Please retry -- there were database errors: ', 'wp-issues-crm' ) . $return_post_id, $this->unset_value, null );
								} else {
									$this->display_form( $user_request, 'update', __( 'Record saved -- you can further update this record.', 'wp-issues-crm' ), $this->unset_value,  $return_post_id  );								
								}					
							}
						} else {
							$this->display_form( $user_request, 'search', __( 'Record not saved -- other records match the new combination of  ', 'wp-issues-crm' ) . $this->create_dup_check_fields_list(), $this->unset_value, null );
							echo $this->format_constituent_list( $wic_query );
						}						
						break;
				} // closes switch statement	
			} // close nonces OK	 		
 		} // close not a reset
   } // close function
	/*
	*
	* display form with parameters set based on search/update/save posture
	*
	*/
	
	public function display_form ( $last_user_request, $main_button_value, $form_notices, &$wic_query, $post_id ) {
		$main_button_label = $this->button_actions[$main_button_value];
		echo '<span style="color:green;"> $_POST:';  		
  		var_dump ($_POST);
  		echo '</span>';  

		?>
		<form id = "constituent-form" method="POST">
			<?php 
			/* notices section */
			if ( $form_notices != '' ) { ?>
		   	<div id="constituent-form-message-box" <strong><em><?php echo $form_notices; ?></em></strong></div>
		   <?php }
		   /* input meta fields */
			foreach ( $this->constituent_fields as $field ) {
				if( $field[2] ) { // if is a field displayed online
					$value = '';
					if ( ! ( 'reset' == $last_user_request ) ) { 
						if ( 'search' == $last_user_request && isset ( $wic_query ) ) { 
							// if original user ask was a search, logic above only passes query if exactly one found, so overlaying form values will lose no input
							$post_field_key =  '_wic_' . $field[0];
							$value =  $wic_query->post->$post_field_key;
						} else {
							// otherwise preserving user input after sanitization 
							$value = isset ($_POST[$field[0]]) ? sanitize_text_field( $_POST[$field[0]] ) : '';
						}
					}
					switch ( $field[3] ) {
						case 'email':						
						case 'text':
						case 'date':

							$contains = $field[4] ? __( ' contains ', 'wp-issues-crm' ) : '';
							?><p><label for="<?php echo $field[0] ?>"><?php echo __( $field[1], 'wp-issues-crm' ) . ' ' . $contains; ?></label>
							<input  id="<?php echo $field[0] ?>" name="<?php echo $field[0] ?>" type="text" value="<?php echo $value; ?>" /></p><?php 
							break;
					}
				}
			} // close foreach  
		
			if ( 'update' == $main_button_value || 'save' == $main_button_value ) { 
				/* input field for constituent notes (post content) */		
				$constituent_notes = '';				
				if ( 'search' == $last_user_request && isset ( $wic_query ) ) {
					$constituent_notes =  $wic_query->post->post_content;
				} elseif ( isset ($_POST['constituent_notes'] ) ) {
					$constituent_notes = $_POST['constituent_notes'];
				}
				?><p><label for="constituent_notes"><?php _e( "Constituent Notes", 'wp-issues-crm' ); ?></label></p>				
				<p><textarea id="constituent_notes" name="constituent_notes" rows="10" cols="50"><?php echo wp_kses_post( $constituent_notes ); ?></textarea></p><?php 

				/* post ID pass through for update cases (including update after save) */				
				$pass_through_post_id = null;				
				if ( isset ( $post_id ) ) {
 					$pass_through_post_id = $post_id;
				} elseif ( isset ( $wic_query->post->ID ) ) {
					$pass_through_post_id = $wic_query->post->ID;
				}
				?><input type = "hidden" id = "constituent_id" name = "constituent_id" value ="<?php echo $pass_through_post_id; ?>" /><?php					
			} 
	  
	  		/* buttons and nonces */ ?>
	  		<button id="main_button" name="main_button" type="submit" value = "<?php echo $main_button_value; ?>"><?php _e( $main_button_label, 'wp_issues_crm'); ?></button>	  
			<?php if ( 'update' == $main_button_value || 'save' == $main_button_value ) { ?>
				<button id="redo_search_button" name="redo_search_button" type="submit" value = "redo_search"><?php _e( 'Search Again', 'wp_issues_crm'); ?></button>
			<?php } ?>		 		
	  		<button id="reset_button" name="reset_button" type="submit" value = "<?php echo 'reset_form' ?>"><?php _e( 'Reset Form', 'wp_issues_crm'); ?></button>
	 		<?php wp_nonce_field( 'wp_issues_crm_constituent', 'wp_issues_crm_constituent_nonce_field', true, true ); ?>

		</form>
		
		<?php 
		
	}
		
	/*
	*  constituent search function
	*
	*/
   private function search_constituents( $is_dedup_check, $constituent_id ) {
		if ( ! $constituent_id ) {  	
	   	$meta_query_args = array(
	     		'relation'=> 'AND',
	     	);
			$index = 1;
	 		foreach ( $this->constituent_fields as $field ) {
	 			if( isset( $_POST[$field[0]] ) && ( ( ! $is_dedup_check ) || $field[5] ) )  { 
	 				$value = sanitize_text_field ( $_POST[$field[0]] );
	 				switch ($field[3]) { 
						case 'date': // preprocess dates
							$date_error = false;
							try {
								$test = new DateTime( $_POST[$field[0]] );
							}	catch ( Exception $e ) {
								$_POST[$field[0]] = __( 'Enter yyyy-mm-dd.', 'wp-issues-crm' );
								$value = '';
								$date_error = true;
							}	   			
			   			if ( ! $date_error ) {
			   				$_POST[$field[0]] = date_format($test, 'Y-m-d' );
			   				$value = $_POST[$field[0]];
			   			} 
						break;	 				
	 				}
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
	 	} else {
			$query_args = array (
				'p' => $constituent_id,
				'post_type' => 'wic_constituent',			
			);	 	
	 	}
 		$wic_query = new WP_Query($query_args);
 
 		return $wic_query;
	}
 	
 	public function format_constituent_list ( &$wic_query ) {
		$output =  '<h1> Found ' .  $wic_query->found_posts . ' constituents, showing ' .  $wic_query->post_count . ' </h1>';			
		$output .= '<ul>';	 		
 		while (  $wic_query->have_posts() ) {
			 $wic_query->next_post();
			$output .= '<li>' .  $wic_query->post->_wic_first_name .
							  ' ' .  $wic_query->post->_wic_last_name . 
							  ', ' .  $wic_query->post->_wic_street_address .
							  ', ' .  $wic_query->post->_wic_email .
				 '</li>'; 	 		
 		}
 		$output .= '</ul>';
		wp_reset_postdata();
		return $output;
   }
	/*
	*
	*	form validation function
	*
	*/   
   
   private function validate_input() {

   	$form_notices = '';
   	if ( '' == sanitize_text_field ( $_POST['first_name'] ) &&  '' == sanitize_text_field ( $_POST['last_name'] ) && '' == sanitize_text_field ( $_POST['email'] ) ) {
			$form_notices .= __( 'Please enter at least one of first name, last name or email. ', 'wp-issues-crm' );
			$this->error_flag = true;			   	
   	}
   	
   	foreach ( $this->constituent_fields as $field ) {
			if ( isset( $_POST[$field[0]] ) ) {
   			$_POST[$field[0]] = sanitize_text_field( $_POST[$field[0]] );
	   		if ( $_POST[$field[0]] > '' ) {
		   		if	( "email" == $field[3] && ! filter_var( $_POST[$field[0]], FILTER_VALIDATE_EMAIL ) ) {
		   			$this->error_flag = true;
		   			$form_notices .= __( 'Email address is not valid. ', 'wp-issues-crm' );
					}	
		   		if	( "date" == $field[3] )  {
		   			$date_error = false;
						try {
							$test = new DateTime( $_POST[$field[0]] );
						}	catch ( Exception $e ) {
							$form_notices .= __( 'Unsupported date format -- yy/mm/dd will work:', 'wp-issues-crm' );
							$date_error = true;
						}	   			
		   			if ( ! $date_error ) {
		   				$_POST[$field[0]] = date_format($test, 'Y-m-d' );
		   			} 
					}						   		
	   		}
   		} 
   	}
		
		$_POST['constituent_notes'] = wp_kses_post ( $_POST['constituent_notes'] );   	
   	
   	return ( $form_notices );
   } 
   /*
   *
	*  save_update_constituent() 
	*
	*/
   private function save_update_constituent( $constituent_id, &$wic_query ) {

   	// title is ln OR ln,fn OR fn OR email -- one of these is required in validation to be non-blank.	
		$title = 	isset ( $_POST['last_name'] ) ? $_POST['last_name'] : '';
		$title .= 	isset ( $_POST['first_name'] ) ? ( $title > '' ? ', ' : '' ) . $_POST['first_name'] : '';
		$title =		( '' == $title ) ? $_POST['email'] : $title;
		
		$post_args = array(
		  'post_content'   => $_POST['constituent_notes'], 
		  'post_title'     => $title,
		  'post_status'    => 'private',
		  'post_type'      => 'wic_constituent',
		  'comment_status' => 'closed' 
		); 
		
		if ( $constituent_id ) {
			$post_args['ID'] = $_POST['constituent_id'];
			if ( $_POST[ 'constituent_notes' ]	!= $wic_query->post->post_content ||
				$title 							 	!= $wic_query->post->post_title ) {
				$return_post_id = wp_update_post( $post_args );
			} else {
				$return_post_id = $constituent_id;			
			}
		} else {
			$return_post_id = wp_insert_post( $post_args );		
		}				

		if ( 0 == $return_post_id ) {
			$form_notices = __( 'Unknown error. Could not save/update constituent record.  Reset form, search for constituent and check results.', 'wp-issues-crm' );
			return $form_notices;					
		} else {
			foreach ( $this->constituent_fields as $field ) {
				if( $field[2] ) {
					$post_field_key =  '_wic_' . $field[0];
					if ( isset( $wic_query ) ) {
						$value =  $wic_query->post->$post_field_key;
						if( $_POST[$field[0]] != $wic_query->post->$post_field_key ) {
							$meta_return = update_post_meta ( $return_post_id, $post_field_key, $_POST[$field[0]] );
						} else {
							$meta_return = 1; 						
						}
					} else {
						$meta_return = add_post_meta ( $return_post_id, $post_field_key, $_POST[$field[0]] );
					}
				}
				if ( ! $meta_return ) {
					$form_notices = __( 'Unknown error. Could not save/update constituent details.  Reset form, search for constituent and check results.', 'wp-issues-crm' );
					return $form_notices;					
				}
			}
			return $return_post_id;
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