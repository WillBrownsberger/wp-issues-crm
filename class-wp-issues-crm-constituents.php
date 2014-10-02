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
	
	private $constituent_field_groups = array (
		array (
			'name'		=> 'required',
			'label'		=>	'Identity',
			'legend'		=>	'Constituents must be identified by at least one of these fields.',
			'order'		=>	10,
		),
		array (
			'name'		=> 'contact',
			'label'		=>	'Contact Information',
			'legend'		=>	'',
			'order'		=>	20,
		),
		array (
			'name'		=> 'personal',
			'label'		=>	'Personal Information',
			'legend'		=>	'',
			'order'		=> 30,
		),
		array (
			'name'		=> 'links',
			'label'		=>	'Identity Codes',
			'legend'		=>	'Cannot be updated online.',
			'order'		=> 40,
		),	
	);
	
	private $constituent_fields = array( // fields must include so labelled first_name, last_name, email, otherwise may be replaced freely
	  			// 0-slug,			1-label,							2-online 		3-type 		4-like   		5-dedup 	6-group 		7-order search . . . . not implemented: 'readonly', 'required', 'searchable'
		array( 'first_name', 		'First Name',					true,			'text', 		true,				true,		'required', 	10,	),		
		array( 'middle_name', 		'Middle Name',					false,		'text', 		false, 			false,	'personal',		100,	),		
		array( 'last_name', 			'Last Name',					true,			'text', 		true, 			true,		'required',		20,	),		
		array( 'email', 				'eMail',							true,			'email', 	true,				true,		'required',		30,	),	
		array( 'phone', 				'Land Line',					true,			'text',  	true, 			false,	'contact',		70,	),		
		array( 'mobile_phone',		'Mobile Phone',				true,			'text', 		false, 			false,	'contact',		80,	),
		array( 'street_address', 	'Street Address',				true,			'text', 		true, 			true,		'contact',		40,	),
		array( 'city', 				'City',							true,			'text', 		false, 			false,	'contact',		50,	),
		array( 'state',				'State', 						true,			'text', 		false, 			false,	'contact',		60,	),
		array( 'zip',					'Zip Code', 					true,			'text', 		false, 			false,	'contact',		65,	),
		array( 'job_title', 			'Job Title',					true,			'text', 		false, 			false,	'personal',		90,	),
		array( 'organization_name','Organization',				true,			'text', 		false, 			false,	'personal',		95,	),
		array( 'gender_id', 			'Gender',						false,		array ( 'M', 'F'),false,	false, 	'personal',		85,	),
		array( 'birth_date',			'Date of Birth',				true,			'date', 		false, 			false,	'personal',		84,	),
		array( 'is_deceased', 		'Is Deceased',					false,		'check',  	false, 			false,	'personal',		87,	),
		array( 'civicrm_id', 		'CiviCRM ID',					false,		'text',  	false,			false,	'links',			1,	),
		array( 'ss_id',				'Secretary of State ID',	false,		'text', 		false, 			false,	'links',			3,	),
		array( 'VAN_id', 				'VAN ID',						false,		'text', 		false, 			false,	'links',			5, ),
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

		$next_form_output = array();
		// new or reset form
		if ( ! isset ( $_POST['main_button'] ) && ! isset ( $_POST['redo_search_button'] ) ) { 
			foreach ( $this->constituent_fields as $field ) {
				$next_form_output[$field[0]] 			=	'';
			}
			$next_form_output['constituent_notes']	=	'';
			$next_form_output['constituent_id']		=	false;			
			$next_form_output['form_notices']		=	__( 'Enter values and search for constituents.', 'wp-issues-crm' );
			$next_form_output['next_action'] 		=	'search';
			
		// working with form input
		} else {
			// test nonce before going further
			if( ! wp_verify_nonce($_POST['wp_issues_crm_constituent_nonce_field'], 'wp_issues_crm_constituent'))	{
				die ( 'Security check failed.' ); // if not nonce OK, die, otherwise continue  
			}

			// clean validate input	and pass through to next form, including hidden post ID field	
			$next_form_output = $this->sanitize_validate_input();
			
			// read button pushed to determine what the user asked to do (one or the other button is definitely set in current condition )
			if ( isset ( $_POST['main_button'] ) ) { 
				$user_request = $_POST['main_button']; // search, update or save
			} elseif ( isset ( $_POST['redo_search_button'] ) ) {
				$user_request = 'search';	
			} 

			// do search in all cases, but do only on dup check fields if request is a save or update 
			$is_dedup_check = ( 'search' == $user_request ) ? false : true;   
			$wic_query = $this->search_constituents( $is_dedup_check, $next_form_output ); 
			
			// if search was user request, and found exactly one record, overwrite form with that records values
			if ( 'search' == $user_request && 1 == $wic_query->found_posts ) {			
				foreach ( $this->constituent_fields as $field ) {
					$post_field_key =  '_wic_' . $field[0];
					$next_form_output[$field[0]] 				= $wic_query->post->$post_field_key;
				}
				$next_form_output['constituent_notes'] = $wic_query->post->post_content;	
				$next_form_output['constituent_id'] 	= $wic_query->post->ID;				
			} 

			// define next form cases based on form input 
			switch ( $user_request ) {	
				case 'search':
					if ( 0 == $wic_query->found_posts ) {
						$next_form_output['form_notices']	=	__( 'No matching record found. Try a save?', 'wp-issues-crm' );
						$next_form_output['next_action'] 	=	'save';
					} elseif ( 1 == $wic_query->found_posts ) {
						$next_form_output['form_notices']	=	__( 'One matching record found. Try an update?', 'wp-issues-crm' );
						$next_form_output['next_action'] 	=	'update';
					} else {
						$next_form_output['form_notices']	=	__( 'Multiple records found.', 'wp-issues-crm' );
						$next_form_output['next_action'] 	=	'search';
					}						
					break;
				case 'update':							
					if ( 0 == $wic_query->found_posts || ( 1 == $wic_query->found_posts && $wic_query->post->ID == $_POST['constituent_id'] ) ) {
						$next_form_output['next_action'] 	=	'update'; // always proceed to further update after an update whether or not successful 
						if ( $clean_input['form_notices'] > '' ) { // validation errors from sanitize_validate_input, always called above
							$next_form_output['form_notices']	=	__( 'Please correct form errors: ', 'wp-issues-crm' ) . $next_form_output['form_notices'];	
						} else {
							$outcome = $this->save_update_constituent();
							if ( $outcome['notices'] > '' )  { 
								$next_form_output['form_notices'] = __( 'Please retry -- there were database errors. ', 'wp-issues-crm' ) . $outcome['notices'];
							} else {
								$next_form_output['form_notices'] = __( 'Update successful -- you can further update this record.', 'wp-issues-crm' );								
							}					
						}
					} else {
						$next_form_output['form_notices']	=	 __( 'Record not updated -- other records match the combination of  ', 'wp-issues-crm' ) . $this->create_dup_check_fields_list();
						$next_form_output['next_action'] 	=	'search';
					}						
					break;				
				case 'save':	
					if ( 0 == $wic_query->found_posts ) {
						if ( $validation_errors > '' ) {
							$next_form_output['form_notices']	=	__( 'Please correct form errors: ', 'wp-issues-crm' ) . $next_form_output['form_notices'];
							$next_form_output['next_action'] 	=	'save';
						} else {
							$return_post_id = $this->save_update_constituent();
							if ( ! is_numeric( $return_post_id  ) ) { // alpha return_post_id is error string
								$next_form_output['form_notices']	=	__( 'Please retry -- there were database errors: ', 'wp-issues-crm' ) . $return_post_id;
								$next_form_output['next_action'] 	=	'save';
							} else {
								$next_form_output['form_notices']	=	__( 'Record saved -- you can further update this record.', 'wp-issues-crm' );
								$next_form_output['next_action'] 	=	'update';
							}					
						}
					} else {
						$next_form_output['form_notices']	=	__( 'Record not saved -- other records match the new combination of  ', 'wp-issues-crm' ) . $this->create_dup_check_fields_list();
						$next_form_output['next_action'] 	=	'search';
					}						
					break;
			} // closes switch statement	
 		} // close not a reset
 		
 		
 		$this->display_form( $next_form_output );
		if ( $wic_query->found_posts > 1 ) {
			echo $this->format_constituent_list( $wic_query );
		}
		wp_reset_postdata();
   } // close function
	/*
	*
	* display form with parameters set based on search/update/save posture
	*
	*/
	
	public function display_form ( &$next_form_output ) {
		
		echo '<span style="color:green;"> $_POST:';  		
  		var_dump ($next_form_output);
  		echo '</span>';  

		?>
		<form id = "constituent-form" method="POST">
			<?php 
			/* notices section */
			if ( $next_form_output['form_notices'] != '' ) { ?>
		   	<div id="constituent-form-message-box" <strong><em><?php echo $next_form_output['form_notices']; ?></em></strong></div>
		   <?php }
		   
			/* format meta fields */
			$sorted_groups = $this->multi_array_key_sort ( $this->constituent_field_groups, 'order' );	
		   foreach ( $sorted_groups as $group ) {
				$filtered_fields = $this->select_key ( $this->constituent_fields, 6, $group['name'] );
				$sorted_filtered_fields = $this->multi_array_key_sort( $filtered_fields, 7 );

				echo '<div class = "constituent-field-group" id = "' . $group['name'] . '">' .
					'<h2 class = "constituent-field-group-label">' . $group['label'] . '</h2>' .
					'<p class = "constituent-field-group-legend">' . $group['legend'] . '</p>';
					foreach ( $sorted_filtered_fields as $field ) {							
						if( $field[2] ) { // if is a field displayed online
							switch ( $field[3] ) {
								case 'email':						
								case 'text':
								case 'date':
		
									$contains = $field[4] ? __( ' contains ', 'wp-issues-crm' ) : '';
									?><p><label for="<?php echo $field[0] ?>"><?php echo __( $field[1], 'wp-issues-crm' ) . ' ' . $contains; ?></label>
									<input  id="<?php echo $field[0] ?>" name="<?php echo $field[0] ?>" type="text" value="<?php echo $next_form_output[$field[0]]; ?>" /></p><?php 
									break;
							}
						}
					} // close foreach 				
				echo '<div>';		   
		   }
		
			if ( 'search' != $next_form_output[['next_action']] ){ ?> 
				<p><label for="constituent_notes"><?php _e( "Constituent Notes", 'wp-issues-crm' ); ?></label></p>
				<p><textarea id="constituent_notes" name="constituent_notes" rows="10" cols="50"><?php $next_form_output['constituent_notes']; ?></textarea></p> 
			<?php } ?>
			
			<input type = "hidden" id = "constituent_id" name = "constituent_id" value ="<?php $next_form_output['constituent_id']; ?>" />					
	  		
	  		<button id="main_button" name="main_button" type="submit" value = "<?php echo $next_form_output['next_action']; ?>"><?php _e( $this->button_actions[$next_form_output['next_action']], 'wp_issues_crm'); ?></button>	  

			<?php if ( 'update' == $next_form_output['next_action'] || 'save' == $next_form_output['next_action'] ) { ?>
				<button id="redo_search_button" name="redo_search_button" type="submit" value = "redo_search"><?php _e( 'Search Again', 'wp_issues_crm'); ?></button>
			<?php } ?>		 		

	  		<button id="reset_button" name="reset_button" type="submit" value = "<?php echo 'reset_form' ?>"><?php _e( 'Reset Form', 'wp_issues_crm'); ?></button>

	 		<?php wp_nonce_field( 'wp_issues_crm_constituent', 'wp_issues_crm_constituent_nonce_field', true, true ); ?>

		</form>
		
		<?php 
		
	}
	/*
	*	sort array of arrays by one value of the arrays
	*
	*/		
	public function multi_array_key_sort ( $multi_array, $key )	{
		$temp = array();
		foreach ( $multi_array as $line_item ) {
			 $temp[$line_item[$key]] = $line_item;
		}
		ksort ($temp);
		$sorted_line_items = array();
		foreach ($temp as $key => $value ) {
			array_push( $sorted_line_items, $value );			
		}
		return ( $sorted_line_items) ;
	}

	/*
	*	filter array of arrays by one value of the arrays
	*
	*/		
	public function select_key ( $line_item_array, $key, $value )	{
		$filtered_line_items = array();
		foreach ( $line_item_array as $line_item ) {
			if ( $line_item[$key] == $value ) {
				array_push( $filtered_line_items, $line_item );
			}			
		}
		return ( $filtered_line_items ) ;
	}
		
		
	/*
	*  constituent search function
	*
	*/
   private function search_constituents( $is_dedup_check, &$next_form_output) {
		if ( ! $$next_form_output['constituent_id'] ) {  	
	   	$meta_query_args = array(
	     		'relation'=> 'AND',
	     	);
			$index = 1;
	 		foreach ( $this->constituent_fields as $field ) {
	 			if( $next_form_output[$field[0]] > '' && ( ( ! $is_dedup_check ) || $field[5] ) )  { 
					$meta_query_args[$index] = array(
						'key' 	=> '_wic_' . $field[0], // wants key, not meta_key, otherwise searches across all keys 
						'value'		=> $next_form_output[$field[0]],
						'compare'	=>	( $field[4] ? 'LIKE' : '=' ),
					);	 
					$index++;
				}		
	 		}
	 		
	 		$query_args = array (
	 			'posts_per_page' => 50,
	 			'post_type' 	=>	'wic_constituent',
	 			'meta_query' 	=> $meta_query_args, 
	 		);
	 	} else {
			$query_args = array (
				'p' => $$next_form_output['constituent_id'],
				'post_type' => 'wic_constituent',			
			);	 	
	 	} echo 'iam here'; 
	 
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
		return $output;
   }
	/*
	*
	*	sanitize_validate_input: form sanitization and validation function
	*  takes $_POST as direct input and returns cleaned and expanded array including all defined fields and validation messages
	*	converts dates from most formats to yyyy-mm-dd
	*  
	*/   
   
   private function sanitize_validate_input() {

		$clean_input = array();
   	$clean_input['form_notices'] = '';
    	
   	foreach ( $this->constituent_fields as $field ) {
			$clean_input[$field[0]] = isset( $_POST[$field[0]] ) ? sanitize_text_field( $_POST[$field[0]] ) : ''; 		
			if ( $clean_input[$field[0]] > '' ) {
	   		if	( "email" == $field[3] && ! filter_var( $clean_input[$field[0]], FILTER_VALIDATE_EMAIL ) ) {
	   			$clean_input['form_notices'] .= __( 'Email address is not valid. ', 'wp-issues-crm' );
				}	
	   		if	( "date" == $field[3] )  {
	   			$date_error = false;
					try {
						$test = new DateTime( $clean_input[$field[0]] );
					}	catch ( Exception $e ) {
						$clean_input['form_notices'] .= __( 'Unsupported date format -- yyyy-mm-dd will work.', 'wp-issues-crm' );
						$clean_input[$field[0]] = ''; // note will give no notice on search, but field will comeback blank
						$date_error = true;
					}	   			
	   			if ( ! $date_error ) {
	   				$clean_input[$field[0]] = date_format( $test, 'Y-m-d' );
	   			} 
				}						   		
   		}
   	}
		
		if ( '' == ( $clean_input['first_name'] . $clean_input['last_name'] . $clean_input['email'] ) ) {
			$clean_input['form_notices'] .= __( 'Please enter at least one of first name, last name or email. ', 'wp-issues-crm' );
   	}

		$clean_input['constituent_notes'] = isset ( $_POST['constituent_notes'] ) ? wp_kses_post ( $_POST['constituent_notes'] ) : '' ;   	
   	$clean_input['constituent_id'] = $_POST['constituent_id']; // always included in form; false if unknown;
   	return ( $clean_input );
   } 
   /*
   *
	*  save_update_constituent() 
	*
	*/
   private function save_update_constituent() { // taking values, (including constituent ID which > 0 indicates update) from next form output

		$outcome = array (
			'post_id'	=> 0,
		   'notices'	=> '', 
		);		

   	// title is ln OR ln,fn OR fn OR email -- one of these is required in validation to be non-blank.	
		$title = 	isset ( $next_form_output['last_name'] ) ? $next_form_output['last_name'] : '';
		$title .= 	isset ( $next_form_output['first_name'] ) ? ( $title > '' ? ', ' : '' ) . $next_form_output['first_name'] : '';
		$title =		( '' == $title ) ? $next_form_output['email'] : $title;
		
		$post_args = array(
		  'post_content'   => $next_form_output['constituent_notes'], 
		  'post_title'     => $title,
		  'post_status'    => 'private',
		  'post_type'      => 'wic_constituent',
		  'comment_status' => 'closed' 
		); 
		
		if ( $next_form_output['constituent_id'] > 0 ) { // if have constitutent ID, do update if notes or title changed
			$check_on_database = $this->search_constituents( false, $next_form_output['constituent_id'] );
			if ( ! isset ( $check_on_database->post->ID ) )  {
				$outcome['notices'] = __( 'Unknown error. Could not find record to update', 'wp-issues-crm' );
				return ( $outcome );			
			}
			$post_args['ID'] = $next_form_output['constituent_id'];
			if ( $next_form_output[ 'constituent_notes' ] != $check_on_database->post->post_content ||
				$title != $check_on_database->post->post_title ) {
				$outcome['post_id'] = wp_update_post( $post_args );
			} else {
				$outcome['post_id'] = $next_form_output['constituent_id'];			
			}
		} else {
			$outcome['post_id'] = wp_insert_post( $post_args );		
		}				

		if ( 0 == $outcome['post_id'] ) {
			$outcome['notices'] = __( 'Unknown error. Could not save/update constituent record.  Reset form, search for constituent and check results.', 'wp-issues-crm' );
			return ($outcome);					
		} else { // If save/update successful, update metafields
			foreach ( $this->constituent_fields as $field ) {
				if( $field[2] ) {
					$post_field_key =  '_wic_' . $field[0];
					if ( $next_form_output['constituent_id'] > 0 ) {
						if( $next_form_output[$field[0]] != $check_on_database->post->$post_field_key ) {
							$meta_return = update_post_meta ( $next_form_output['constituent_id'], $post_field_key, $next_form_output[$field[0]] );
						} else {
							$meta_return = 1; 						
						}
					} else {
						$meta_return = add_post_meta ( $outcome['post_id'], $post_field_key, $next_form_output[$field[0]] );
					}
				}
				if ( ! $meta_return ) {
					$outcome['notices'] = __( 'Unknown error. Could not save/update constituent details.  Reset form, search for constituent and check results.', 'wp-issues-crm' );
				}
			}
			return ( $outcome );
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