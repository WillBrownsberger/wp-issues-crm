<?php
/*
* File: class-wp-issues-crm-main-form.php
*
* Description: this class manages the front end search/update/add process for multiple post types  
* 
* @package wp-issues-crm
* 
*
*/ 

class WP_Issues_CRM_Main_Form {
	/*
	*	Overview of major class functions:
	*		wp_issues_crm_main_form drives main logic of incoming form handling for search/update/save (no delete function)
	*		it calls $wic_form_utilities->sanitize_validate_input() early which does full sanitization (sanitize_text_field and stripslashes) on all except notes field
	*				notes field is sanitized (other than stripslashes) only on output to form (there via wp_kses_post and balancetags -- see notes in display_form)  
	*		it then does searches via $wic_database_utlities->search_wic_posts() (either as requested or as dup check for save or update); 
	*				no additional validation in searching (trust wp -- all access through standard query objects)
	*		if requested and validation passed, it does save/update via save_update_wic_post() -- 
	*				again, no additional validation or escaping (trust wp)
	*		finally it redisplays form through $this->display_form() -- form escapes all output and runs balancetags and wp_kses_post on constituent notes
	*				note -- display_form() relies on display controls from class-wp-issues-crm-definitions which do the escaping  
	*				with a couple of small noted excepts display form does not alter the array next_form_output. $_POST is never altered.
	* 
	*/    	
	
	/*
	*
	* field definitions for ready reference 
	*
	*/
		
	private $working_post_fields = array();
	private $working_post_field_groups = array();
	private $form_requested;
	private $action_requested;
	private $id_requested;
	private $wic_metakey; 

	private $button_actions = array(
		'save' 	=>	'Save New',
		'search' => 'Search',
		'update' => 'Update',
	);
 	
	public function __construct( $control_array ) {
		
		/* set up class variables */
		global $wic_base_definitions;
		global $wic_constituent_definitions;
		global $wic_activity_definitions;
		
		$this->form_requested 	= $control_array[0];
		$this->action_requested = $control_array[1];
		$this->id_requested 		= $control_array[2];
		$this->referring_parent = $control_array[3];
		
		// control array 0 is form_requested -- entity type -- constituent, activity or issue
		$field_source_string = 'wic_' . $control_array[0] . '_definitions';
			
		foreach ( $$field_source_string->wic_post_fields as $field )
			if ( $field['online'] ) { 		
 				 array_push( $this->working_post_fields, $field );
 			}
		$this->working_post_field_groups 	= $$field_source_string->wic_post_field_groups;
		$this->wic_metakey = &$wic_base_definitions->wic_metakey;

		/* invoke form and supporting database access functions */
		$this->wp_issues_crm_post_form( $control_array );
		 
	}

/*
*
*	This string only appears in form legend
*
*/
	public function create_dup_check_fields_list() {
	$fields_string = '';
		foreach ( $this->working_post_fields as $field ) {
			if( $field['dedup'] ) {
				$fields_string = ( $fields_string > '' ) ? $fields_string . ', ' : '';
				$fields_string .= $field['label'];
			}		
		}
		return ( $fields_string ); 	
	}
/*
*
*	Initializes blank form -- all form values and display switches set
*  	see inventory below
*
*/

	/*
	*
	* wp_issues_crm_post_form -- function manages search/save/update of wic entity records (s, activities, issues -- all posts)
	* 
	* takes $_POST input and user requested action and applies case logic to do action and populate $next_form_output
	* calls display_form to do the display 
	*
	*
	*/
	public function wp_issues_crm_post_form( $control_array ) {
		
		global $wic_form_utilities;
		global $wic_database_utilities;
		
		/* first check capabilities -- must be administrative user */
		if ( ! current_user_can ( 'activate_plugins' ) ) { 
			echo '<h3>' . __( 'Sorry, this function is only accessible to administrators.', 'simple-wp-crm' ) . '<h3>';
			return;
		} 

		$next_form_output = array();
		$wic_form_utilities->initialize_blank_form( $next_form_output, $this->working_post_fields );

		
		// if new, nothing to process; no nonce to test
		if ( 'new' != $this->action_requested ) { 

			// test nonce before going further
			if( ! wp_verify_nonce($_POST['wp_issues_crm_post_form_nonce_field'], 'wp_issues_crm_post'))	{
				die ( 'Security check failed.' ); // if not nonce OK, die, otherwise continue  
			}
	
			if ( 0 == $this->id_requested ) { 
				// clean and validate POST input and populate next form output	
				$wic_form_utilities->sanitize_validate_input( $next_form_output, $this->working_post_fields );
				// do search in all submitted cases, but do only on dup check fields if request is a save or update (does not alter next_form_output)
				$search_mode = ( 'search' == $this->action_requested ) ? 'new' : 'dup';
			} else { 
				$search_mode = 'db_check';
				$next_form_output['wic_post_id']	= $this->id_requested;	
			}

			$wic_query = $wic_database_utilities->search_wic_posts( $search_mode, $next_form_output, $this->working_post_fields, $this->form_requested ); 
			
			// will show post list if found multiple or found a dup; default is false
			$show_list = false;			
			
			// do last form requests and define form_notices and next_action based on results of sanitize_validate, search_wic_posts and save/update requests  
			switch ( $this->action_requested ) {	
				case 'search':
					if ( 0 == $wic_query->found_posts ) {
						$next_form_output['guidance']	=	__( 'No matching record found. Try a save? ', 'wp-issues-crm' );
						$next_form_output['next_action'] 	=	'save';
					} elseif ( 1 == $wic_query->found_posts ) { // overwrite form with that unique record's  values
						foreach ( $this->working_post_fields as $field ) {
							$post_field_key =  $this->wic_metakey . $field['slug'];
							// the following isset check should be necessary only if a search requesting more than the maximum search terms is executed 
							// note -- don't need to unserialize phones, etc. -- wp_query does this. also automatic in save_update_wic_post  
							$next_form_output[$field['slug']] = isset ( $wic_query->post->$post_field_key ) ?  $wic_query->post->$post_field_key : '';
						}
						$next_form_output['wic_post_content'] = ''; // don't want to bring search notes automatically into update mode 
						$next_form_output['old_wic_post_content'] = isset ( $wic_query->post->post_content )  ? $wic_query->post->post_content: '';	
						$next_form_output['wic_post_id'] 	= $wic_query->post->ID;	
						$next_form_output['guidance']	=	__( 'One matching record found. Try an update?', 'wp-issues-crm' );
						$next_form_output['next_action'] 	=	'update';
					} else {
						$next_form_output['guidance']	=	__( 'Multiple records found (results below). ', 'wp-issues-crm' );
						$next_form_output['next_action'] 	=	'search';
						$show_list = true;
					}						
					break;
				case 'update':
					// after dup_check search, if updated values do not match any record or match the original record, proceed to update 							
					if ( 0 == $wic_query->found_posts || ( 1 == $wic_query->found_posts && $wic_query->post->ID == $next_form_output['wic_post_id'] ) ) {
						$next_form_output['next_action'] 	=	'update'; // always proceed to further update after an update whether or not successful (unless poss dup)
						if ( $next_form_output['error_messages'] > '' ) { // validation errors from sanitize_validate_input which is always called above (and, unlikely, any search errors)
							$next_form_output['guidance']	=	__( 'Please correct form errors: ', 'wp-issues-crm' );	
						} else {
							$outcome = $wic_database_utilities->save_update_wic_post( $next_form_output, $this->working_post_fields );
							if ( $outcome['notices'] > '' )  { 
								$next_form_output['guidance'] = __( 'Please retry -- there were database errors. ', 'wp-issues-crm' );
								$next_form_output['error_messages'] = $outcome['notices'];
							} else { 
								$next_form_output['guidance'] = __( 'Update successful -- you can further update this record.', 'wp-issues-crm' );								
								if ( trim( $next_form_output[ 'wic_post_content' ] ) > '' ) { // update to database
									$next_form_output['old_wic_post_content'] = $wic_form_utilities->format_wic_post_content( $next_form_output['wic_post_content'] ) . $next_form_output['old_wic_post_content'];
									$next_form_output['wic_post_content'] = '';
								}
							}					
						}
					// error if form values match a record other than the original record	
					} else { 
						$next_form_output['guidance'] = '';						
						$next_form_output['wic_post_id'] = 0; // reset so search does not bring back the original record
						$next_form_output['search_notices']	=	sprintf ( __( 'Record not updated -- other records match the new combination of %s. View matches below.', 'wp-issues-crm' ), $this->create_dup_check_fields_list());
						$next_form_output['next_action'] 	=	'search';
						$show_list = true;
					}						
					break;				
				case 'save':	
					if ( 0 == $wic_query->found_posts ) {
						if ( $next_form_output['error_messages'] > '' ) {
							$next_form_output['guidance']	=	__( 'Please correct form errors: ', 'wp-issues-crm' );
							$next_form_output['next_action'] 	=	'save';
						} else {
							$outcome = $wic_database_utilities->save_update_wic_post( $next_form_output, $this->working_post_fields );
							if ( $outcome['notices'] > ''  ) { // alpha return_post_id is error string
								$next_form_output['guidance']	=	__( 'Please retry -- there were database errors: ', 'wp-issues-crm' );
								$next_form_output['error_messages'] = $outcome['notices'];
								$next_form_output['next_action'] 	=	'save';
							} else {
								$next_form_output['wic_post_id'] = $outcome['post_id'];
								$next_form_output['guidance']	=	__( 'Record saved -- you can further update this record.', 'wp-issues-crm' );
								$next_form_output['next_action'] 	=	'update';
								if ( trim( $next_form_output[ 'wic_post_content' ] )  > '' ) { // parallels update to database
									$next_form_output['old_wic_post_content'] = $wic_form_utilities->format_wic_post_content( $next_form_output['wic_post_content'] ) . $next_form_output['old_wic_post_content'];
									$next_form_output['wic_post_content'] = '';
								}
							}					
						}
					} else {
						$next_form_output['guidance'] = '';
						$next_form_output['search_notices']	=	sprintf ( __( 'Record not saved -- other records match the new combination of %s. View matches below.', 'wp-issues-crm' ), $this->create_dup_check_fields_list());
						$next_form_output['next_action'] 	=	'search';
						$show_list = true;
					}						
					break;
			} // closes switch statement	

			// prepare to show list of posts if found more than one
			if ( $show_list ) {
				$wic_list_posts = new WP_Issues_CRM_Posts_List ( $wic_query, $this->working_post_fields, $this->form_requested );			
				$post_list = $wic_list_posts->post_list;
				if ( 'search' == $this->action_requested  && '' == $next_form_output['search_notices'] ) // always show form unless was a search and no search notices
					$next_form_output['initial_form_state'] = 'wic-form-closed';
			} else {
				$post_list = '';			
			}

			// done with query
			wp_reset_postdata();

 		} // close if returning form button values
 		
 		// deliver the results (blank if new form)
 		ob_start();
 		$this->display_form( $next_form_output );
 		if ( isset ( $post_list ) ) {
			echo $post_list; 		
 		}
 		ob_end_flush();

   } // close function
	
	/*
	* function: display_form
	*
	* displays form with controls based on search/update/save next action and field definitions
	* values in the next_form_output array are never altered here -- this function only makes display decisions
	*   (exception is flatten or pop-up serialized repeater arrays for display)
	* $next_form_output is previously populated with values in wp_issues_crm_post_form() and functions 
	* see inventory of values and dispositions in comments before that function
	* 
	* All output data which is not hardcoded is escaped using one of the following: esc_attr, esc_html, esc_textarea (or absint if known should be integer)
	*   Note that the only data which not been run through validation previously is anything coming from a find and also wic_post_content -- but, all are escaped.
	*
	* Field def data is not considered hardcoded here, since may add interface later, and so is escaped in the same way.	
	*
	*
	*/
	
	public function display_form ( &$next_form_output ) {
		
		global $wic_base_definitions;
		global $wic_form_utilities; // access for functions ( field definitions already instantiated  in construct )
		/* var_dump( $next_form_output['initial_sections_open'] );

		 echo '<span style="color:green;"> <br /> $_POST:';  		
  		var_dump ($_POST);
  		echo '</span>';  

		 echo '<span style="color:red;"> <br />next_form_output:';  		
  		var_dump ($next_form_output);
  		echo '</span>';   
  		/*
  		echo '<span style="color:blue;"> <br />phone_numbers:';  		
  		var_dump ($_POST['phone_numbers']);
  		echo '</span>'; */  

		?><div id='wic-forms' class = "<?php echo $next_form_output['initial_form_state'] ?>">

		<form id = "wic-post-form" method="POST" autocomplete = "on">

			<div class = "wic-form-field-group wic-group-odd">
			
				<?php if ( 'update' == $next_form_output['next_action'] ) {
					$form_header = $next_form_output['first_name'] . ' ' . $next_form_output['last_name'];
					$form_header = ( '' == $form_header ) ? $next_form_output['emails'][0][1] : $form_header;
				} else {
					$form_header = __( $this->button_actions[$next_form_output['next_action']], 'wp_issues_crm');
				}
				echo '<h2>' . $form_header . '</h2>'; 
				
				if ( 'wic-form-closed' == $next_form_output['initial_form_state'] ) {
					echo '<button id = "form-toggle-button" type="button" onclick = "togglePostForm()">' . __( 'Show Search Form', 'wp-issues-crm' ) . '</button>';		
				} 
		
				/* notices section */
				if ( $next_form_output['next_action'] == 'search') {
					$notice_class = $next_form_output['search_notices'] > '' ?  'wic-form-search-notices' : 'wic-form-no-errors';
					$message = $next_form_output['guidance'] . ' ' .  $next_form_output['search_notices'] ; 	
				} else {
					$notice_class = $next_form_output['error_messages'] > '' ?  'wic-form-errors-found' : 'wic-form-no-errors';
					$message = $next_form_output['guidance'] . ' ' .  $next_form_output['error_messages'] ; 	
				} 				
				
				if ( $message > '' ) { ?>
			   	<div id="post-form-message-box" class = "<?php echo $notice_class; ?>" ><?php echo $message; ?></div>
			   <?php }
			   
				/* first instance of buttons */	
				$button_args_main = array(
					'form_requested'			=> $this->form_requested,
					'action_requested'		=> $next_form_output['next_action'],
					'button_label'				=> $this->button_actions[$next_form_output['next_action']],
				);					
				echo $wic_form_utilities->create_wic_form_button( $button_args_main );

 				if ( 'update' == $next_form_output['next_action'] ) { // show this on save, but not update -- on update, have too much data in form, need to reset
					foreach ( $wic_base_definitions->wic_post_types as $key => $entity_type ) {
							if ( $this->form_requested == $entity_type['parent_type'] ) {
							
							$button_args_child_button = array(
								'form_requested'			=> $key,
								'action_requested'		=> 'search',
								'referring_parent'		=>	$this->referring_parent,
								'button_label'				=> 'Add New ' . $entity_type['label_singular'],
								'button_class'				=> 'wic-form-button second-position',
							);
							echo $wic_form_utilities->create_wic_form_button( $button_args_child_button );					
						}					
					}					
				}
				
				
 				if ( 'save' == $next_form_output['next_action'] ) { // show this on save, but not update -- on update, have too much data in form, need to reset 
					$button_args_search_again = array(
						'form_requested'			=> $this->form_requested,
						'action_requested'		=> 'search',
						'button_label'				=> 'Search Again',
						'button_class'				=> 'wic-form-button second-position'
					);					
					echo $wic_form_utilities->create_wic_form_button( $button_args_search_again );
				}

			echo '</div>';   
		
		
			/* initialize field footnotes and footnote legends */
			$required_individual = '';
			$required_group = '';
			$contains = '';
			$required_group_legend = '';
			$required_individual_legend = ''; 								
			$contains_legend = false;
			$serialized_contains_legend = false;


			/* format meta fields  -- loop through field groups and within them through fields */
			$group_count = 0;
		   foreach ( $this->working_post_field_groups as $group ) {
		   	

						   	
				$filtered_fields = $this->select_key ( $this->working_post_fields, 'group', $group['name'] );
				$row_class = ( 0 == $group_count % 2 ) ? "wic-group-even" : "wic-group-odd";
				$group_count++;
				
				echo '<div class = "wic-form-field-group ' . $row_class . '" id = "wic-field-group-' . esc_attr( $group['name'] ) . '">';				
				
					$section_open = in_array( $group['name'], $next_form_output['initial_sections_open'] ) ? true : $group['initial-open'];				
				
					$button_args = array (
						'class'			=> 'field-group-show-hide-button',		
						'name_base'		=> 'wic-inner-field-group-',
						'name_variable' => $group['name'],
						'label' 			=> $group['label'],
						'show_initial' => $section_open,
					);
			
					echo $wic_form_utilities->output_show_hide_toggle_button( $button_args );			
				
					$show_class = $section_open ? 'visible-template' : 'hidden-template';
					echo '<div class="' . $show_class . '" id = "wic-inner-field-group-' . esc_attr( $group['name'] ) . '">' .					
					'<p class = "wic-form-field-group-legend">' . esc_html ( $group['legend'] )  . '</p>';

					foreach ( $filtered_fields as $field ) {	

		   			$field_type = $field['type'];

						/* set flags (toggling for each field) and legends (setting if for any field) */
						if ( 'update' == $next_form_output['next_action'] || 'save' == $next_form_output['next_action'] ) {
							$required_group = ( 'group' == $field['required'] ) ? '(+)' : '';
							if( 'group' == $field['required'] ) {
								$required_group_legend = '(+) ' . __('At least one among these fields must be supplied.', 'wp-issues-crm' );						
							}
							
							$required_individual = ( 'individual' == $field['required'] ) ? '*' : '';
							if( 'individual' == $field['required'] ) {
								$required_individual_legend = '* ' . __('Required field.', 'wp-issues-crm' );						
							}
						} else { // search case								
							$contains = $field['like'] ? '(%)' : '';

							if( $field['like'] ) {
								$contains_legend = 'true';	
							}

							if ( in_array( $field['type'], $wic_base_definitions->serialized_field_types ) ) {
								$contains =  '(%!)';
								$serialized_contains_legend = true;
							}
						}

						/* if have repeating fields, treat as string for search (can be new search or search where already working as array) */						
						if ( in_array( $field['type'], $wic_base_definitions->serialized_field_types ) ) {
							if ( 'search' == $next_form_output['next_action'] ) {
								$field_type = 'serialized_type_as_string';
								if ( is_array ( $next_form_output[$field['slug']] ) ) {							
									$next_form_output[$field['slug']] = $next_form_output[$field['slug']][0][1]; // first phone, email or address in array
								} // slightly breaking wp_issues_crm rules by altering this $next_form_out within this function, but really just selecting an element for display
								  // clearer to do it here in the context where it is needed (flattening array for use as search)
								  // see parallel kludge immediately below -- on search going to save, if have flat string from search, pop it up to an array 
							} else { 
								$field_type = 'serialized_type_as_array';
								// note that this branch can only be triggered when searched included phone and found no record, so going to save
								// if repeater was in search criterion and was found, then got the record populated with serialized array from database  
								if ( ! is_array ( $next_form_output[$field['slug']] ) && $next_form_output[$field['slug']] > '' ) {
									$next_form_output[$field['slug']] = array (
										array (
											'0',
											$next_form_output[$field['slug']],
											'', // extra values do no harm in this array
											'',
											'',
											'',
											'',
										),									
									);
								}
							}
						}

						/* set up arguments consistently passed to control functions */ 
						$args = array (
							'field_name_id'		=> $field['slug'],
							'field_label'			=>	$field['label'],
							'value'					=> $next_form_output[$field['slug']],
							'read_only_flag'		=>	false, 
							'field_label_suffix'	=> $required_individual . $required_group . $contains, 								
						);

						/* handle cases as additions to and overrides of those basic arguments */
						switch ( $field_type ) {
							case 'email':						
							case 'text':
							case 'serialized_type_as_string':
								echo '<p>' . $wic_form_utilities->create_text_control ( $args ) . '</p>'; 
								break;

							case 'date':
								if ( 'search' == $next_form_output['next_action'] ) { 
									$args = array (
										'field_name_id'		=> $field['slug'] . '_lo',
										'field_label'			=>	$field['label'] . ' >= ' ,
										'value'					=> $next_form_output[$field['slug'] . '_lo'],
										'read_only_flag'		=>	false, 
										'field_label_suffix'	=> '', 								
									);
									echo '<p>' . $wic_form_utilities->create_text_control ( $args ); 

									$args = array (
										'field_name_id'		=> $field['slug'] . '_hi',
										'field_label'			=>	__( 'and <=', 'wp_issues_crm' ),
										'label_class'			=> 'wic-label-2',
										'value'					=> $next_form_output[$field['slug']. '_hi'],
										'read_only_flag'		=>	false, 
										'field_label_suffix'	=> '', 								
									);
									echo $wic_form_utilities->create_text_control ( $args ) . '</p>'; 
								}	else {
									$args['field_label_suffix'] = $required_individual . $required_group;  								
									echo '<p>' . $wic_form_utilities->create_text_control ( $args ) . '</p>'; 
								} 
								break;

							case 'readonly': 
								if ( 'save' != $next_form_output['next_action'] ) { // do not display for save
									$args['read_only_flag'] = 	( 'update' == $next_form_output['next_action'] ); // true or false 
									echo '<p>' . $wic_form_utilities->create_text_control ( $args ) . '</p>'; 
								} 
								break;

							case 'check':
								echo '<p>' . $wic_form_utilities->create_check_control ( $args ) . '</p>'; 
								break;
								
							case 'select':
								$args['placeholder'] 			= __( 'Select', 'wp-issues-crm' ) . ' ' . $field['label'];
								$args['select_array']			=	$field['select_array'];
								$args['field_label_suffix']	= $required_individual . $required_group;								
								echo '<p>' . $wic_form_utilities->create_select_control ( $args ) . '</p>';
								break; 
							
							case 'serialized_type_as_array': // note -- non-arrays already intercepted above  
								$group_args	= array (
									'repeater_group_id'		=> $field['slug'],
									'repeater_group_label'		=> $field['label'],
									'repeater_group_data_array'	=>	$next_form_output[$field['slug']],
									'repeater_group_label_suffix'	=> $required_individual . $required_group . $contains,		
								);
								$repeater_function = 'create_' . $field['type'] . '_group';
								echo $wic_form_utilities->$repeater_function ( $group_args );
								break;
								
							case 'user':
								/* query users with specified role (s) */
								$user_query_args = 	array (
									'role' => $field['user_role'],
									'fields' => array ( 'ID', 'display_name'),
								);						
								$user_list = new WP_User_query ( $user_query_args );

								$user_select_array = array();
								foreach ( $user_list->results as $user ) {
									$temp_array = array (
										'value' => $user->ID,
										'label'	=> $user->display_name,									
									);
									array_push ( $user_select_array, $temp_array );								
								} 
							
								$args['placeholder'] 			= __( 'Select', 'wp-issues-crm' ) . ' ' . $field['label'];
								$args['select_array']			=	$user_select_array; 
								$args['field_label_suffix']	= $required_individual . $required_group;								
								echo '<p>' . $wic_form_utilities->create_select_control ( $args ) . '</p>';
								break; 
														
						}
					} // close foreach field				
				echo '</div></div>';		   
		   } // close foreach group
		
		
			// notes div
			$row_class = ( 0 == $group_count % 2 ) ? "wic-group-even" : "wic-group-odd"; 
			$group_count++;
			echo '<div class = "wic-form-field-group ' . $row_class . '" id = "wic-post-content">';
			
			$show_initial = ( "update" == $next_form_output['next_action'] ); // show notes on update next			
			$show_initial = in_array( 'wic_post_content', $next_form_output['initial_sections_open'] ) ? true : $show_initial; // also were searched or updated 
			
			$button_args = array (
				'class'			=> 'field-group-show-hide-button',		
				'name_base'		=> 'wic-inner-field-group-',
				'name_variable' => 'wic-post-content',
				'label' 			=> __('Notes', 'wp-issues-crm' ),
				'show_initial' =>  ( $show_initial ),
			);
			
			echo $wic_form_utilities->output_show_hide_toggle_button( $button_args );
			
			$show_class = $show_initial ? 'visible-template' : 'hidden-template';
						
			echo '<div id = "wic-inner-field-group-wic-post-content" class="' . $show_class .'">';	
				$args = array (
					'field_name_id'		=> 'wic_post_content',
					'field_label'			=>	__( "Note Text", 'wp-issues-crm' ),
					'value'					=> $next_form_output['wic_post_content'],
					'read_only_flag'		=>	false, 
					'field_label_suffix'	=> '(%!)', 								
					);			
				// show notes as input for search or as text area for update
				if ( 'search' == $next_form_output['next_action'] ){
					echo '<p>' . $wic_form_utilities->create_text_control ( $args ) . '</p>'; 
				} else {
					$args['field_label_suffix']	= '';
					$args['input_class'] = 'wic-input wic-wic-post-content';
					echo '<p>' . $wic_form_utilities->create_text_area_control($args) . '</p>';
					
					$args['field_name_id'] = 'old_wic_post_content';
					$args['read_only_flag']	= true;
					$args['input_class'] = 'hidden-template';
					$args['label_class'] = 'hidden-template';
					$args['value']	= $next_form_output['old_wic_post_content'];
					echo '<p>' . $wic_form_utilities->create_text_area_control($args) . '</p>';
					echo '<div id = "wic-old-wic-post-content">' .  balancetags( wp_kses_post ( $next_form_output['old_wic_post_content'] ), true ) . '</div>';
				}	
				/**
				* options considered for output sanitization of kses_post -- need to be good here, since new notes are just appended to old
				* with no filtering before this point ( on save/update take display value from prior form values (new appended to old), not database 	
				*		(1) esc_html not an option since shows html characters instead of using them format 
				*		(2) sanitize_text_field strips tags entirely
				*		(3) apply_filters('the_content', -- ) does nothing to address stray quotes or unbalanced tags (and would run shortcodes, etc.)
				*		(4) wp_kses_post leaves tags unbalanced but handles stray quotes
				*		(5) balancetags (with force set to true) still gets hurt by stray quotes
				*		CONCLUSION COMBINE 4 AND 5 -- EXPENSIVE, BUT APPROPRIATE, GIVEN RAW CONTENT BEING SERVED -- 
				*		NOTE: Wordpress does not bother to clean post_content up in this way (even through the admin interface) -- so conclude not necessary on save
				*      	-- only do it here for display; assume properly escaped for storage although not clean display
				*/
				
			echo '</div></div>'; 
						
			// final button group div
			$row_class = ( 0 == $group_count % 2 ) ? "wic-group-even" : "wic-group-odd";
			echo '<div class = "wic-form-field-group ' . $row_class . '" id = "bottom-button-group">';?>
				<?php if ( 'update' == $next_form_output['next_action'] ) { ?>
					<p><a href="<?php echo( home_url( '/' ) ) . 'wp-admin/post.php?post=' . absint( $next_form_output['wic_post_id'] ) . '&action=edit' ; ?>" class = "wic-back-end-link"><?php printf ( __('Direct edit %2$s # %1$s <br/>', 'wp_issues_crm'), absint( $next_form_output['wic_post_id'] ) , $this->form_requested  ); ?></a></p>
				<?php } ?>		
			
				<input type = "hidden" id = "wic_post_id" name = "wic_post_id" value ="<?php echo absint( $next_form_output['wic_post_id'] ) ; ?>" />					
		  		
		  		<?php echo $wic_form_utilities->create_wic_form_button( $button_args_main ); 	
		  		
				if ( 'update' == $next_form_output['next_action'] ) { // show this on save, but not update -- on update, have too much data in form, need to reset
					if ( isset ( $button_args_child_button ) ) {
							echo $wic_form_utilities->create_wic_form_button( $button_args_child_button );					
					}					
				}
				  
				if ( 'save' == $next_form_output['next_action'] ) { 
					echo $wic_form_utilities->create_wic_form_button( $button_args_search_again );
				} ?> 		 		
		
		 		<?php wp_nonce_field( 'wp_issues_crm_post', 'wp_issues_crm_post_form_nonce_field', true, true ); ?>

			   
				<?php if ( $contains_legend ) { 
					$text_control_args = array ( 
						'field_name_id'		=> 'strict_match',
						'field_label'			=>	'(%) ' . __( 'Full-text search conditionally enabled for these fields -- require strict match instead? ' , 'wp-issues-crm' ),
						'value'					=> $next_form_output['strict_match'],
						'read_only_flag'		=>	false, 
						'field_label_suffix'	=> '', 	
					);
					echo '<p class = "wic-form-legend">' . $wic_form_utilities->create_check_control ( $text_control_args ) . '</p>';
				} ?>	
				
				<?php if ( $serialized_contains_legend ) { 
					echo '<p class = "wic-form-legend" >(%!) ' . __( 'Full-text search always enabled for these fields.', 'wp-issues-crm'  ) . '</p>';
				} ?>	
				<?php if ( $required_individual_legend > '' ) { ?>
					<p class = "wic-form-legend"><?php echo $required_individual_legend; ?> </p>
				<?php } ?> 								
	
				<?php if ( $required_group_legend > '' ) { ?>
					<p class = "wic-form-legend"><?php echo $required_group_legend; ?> </p>
				<?php } ?> 
			</div>								

		</form>
		</div>
		
		<?php 
		
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
		
		

}	



 		
