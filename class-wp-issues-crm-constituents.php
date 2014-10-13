<?php
/*
* File: class-wp-issues-crm-constituents.php
*
* Description: this class manages the front end constituent search/update/add process  
* 
* @package wp-issues-crm
* 
*
*/ 

class WP_Issues_CRM_Constituents {
	/*
	*	Overview of major class functions:
	*		wp_issues_crm_constituent drives main logic of form handling for constituent search/update/save (no delete function)
	*		it calls sanitize_validate_input() early which does full sanitization (sanitize_text_field and stripslashes) on all except notes field
	*				notes field is sanitized (other than stripslashes) only on output to form (there via wp_kses_post and balancetags -- see notes in display_form)  
	*		it then does searches via search_constituents() (either as requested or as dup check for save or update); 
	*				no additional validation in searching (trust wp -- all access through standard query objects)
	*		if requested and validation passed, it does save/update via save_update_constituent() -- 
	*				again, no additional validation or escaping (trust wp)
	*		finally it redisplays form through display_form() -- form escapes all output and runs balancetags and wp_kses_post on constituent notes
	*				note -- display_form() relies on display controls from class-wp-issues-crm-definitions which do the escaping  
	*				with a couple of small noted excepts display form does not alter the array next_form_output. $_POST is never altered.
	* 
	*/    	
	
	/*
	*
	* field definitions for ready reference array 
	*
	*/
		
	private $constituent_fields = array();
	private $constituent_field_groups = array(); 
	public $constituent_id;

	private $search_terms_max = 5; // don't allow searches that will likely degrade performance 

	private $button_actions = array(
		'save' 	=>	'Save New Constituent Record',
		'search' => 'Search Constituents',
		'update' => 'Update Constituent Record',
	);
 	
	public function __construct( $constituent_id ) {

		/* set up class variables */
		global $wic_definitions;
		foreach ( $wic_definitions->constituent_fields as $field )
			if ( $field['online'] ) { 		
 				 array_push( $this->constituent_fields, $field );
 			}
		$this->constituent_field_groups 	= &$wic_definitions->constituent_field_groups;
		$this->wic_metakey = &$wic_definitions->wic_metakey;
		$this->constituent_id = $constituent_id;		

		/* invoke form and supporting database access functions */
		$this->wp_issues_crm_constituents( $constituent_id );
		 
	}

/*
*
*	This string only appears in form legend
*
*/
	public function create_dup_check_fields_list() {
	$fields_string = '';
		foreach ( $this->constituent_fields as $field ) {
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
	private function initialize_blank_form(&$next_form_output)	{ 
	
			/* these values may later go into database */			
			foreach ( $this->constituent_fields as $field ) { // note 1 below 
				$next_form_output[$field['slug']] =	'';
				if ( 'date' == $field['type'] ) {
					$next_form_output[$field['slug'] . '_lo'] = '';				
					$next_form_output[$field['slug'] . '_hi'] = '';
				}
			}
			$next_form_output['constituent_notes']	=	''; 					// note 2 below 
			$next_form_output['old_constituent_notes']	=	''; 					// note 2 below 			
			$next_form_output['constituent_id']		=	$this->constituent_id;	// note 3 below

			/*	these values are only for form setup */	
			$next_form_output['guidance']				=  'Enter just a little information and do a full text search for constituents.'; // note 4a below
			$next_form_output['error_messages']		=	'';					// note 4b below
			$next_form_output['search_notices']		=	'';					// note 4c below
			$next_form_output['next_action'] 		=	'search';			// note 5 below
			$next_form_output['strict_match']		=	false;				// note 6 below
			$next_form_output['initial_form_state']= 	'wic-form-open';  // note 7 below 
			$next_form_output['initial_sections_open'] = array();			// note 8 below
			
		}

	/*
	*
	* wp_issues_crm_constituent -- function manages search/save/update of constituent records
	* 
	* takes $_POST input and user requested action and applies case logic to do action and populate $next_form_output
	* calls display_form to do the display 
	*
	* the components of $next_form_output are: 
	* 		1. all meta fields defined as displayable for constituents
	*			-- initialized to empty on new; otherwise passed through (clean) from form by sanitize_validate_input
	*			-- not otherwise altered except: 
	*					* are overlayed with database if found unique on search (in main logic below) 
	*						-- NOTE: no sanitization or validation of db content -- goes straight to form and escaped going out and validated going coming back
	*					* within display_form, may flatten or pop-up array for repeating fields (going from search to save/update or vice versa)
	*				   * within search_constituents will flatten array for repeating fields
	*					
	*		2. constituent notes, old constituent notes
	*			-- initialized to empty on new
	*			-- constituent notes behaves like other form elements, except 
	*					* wiped out on found record ( so don't use for update )
	*					* wiped out successful save/update (since added to old)
	*			--	old constituent notes behaves like other form elements except 
	*					* is displayed as readonly 
	*					* is refreshed from database after successful save update
	*					* takes value of constituent notes from data base on found record
	*			-- update appends constituent_notes to old
	*		3. constituent id 
	*			-- initialized to zero on new; otherwise passed through from form by sanitize_validate_input
	*			-- set if search found unique (then offer update)
	* 			-- reset to zero if, on update attempt, found dups for new form values (then send back to search)
	*			-- set on save successful (then offer update) 
	*			-- so, is always zero if next_action is search or save and always set if next action is update
	*			--	can be passed as non-zero on class instantiation
	*		4a. guidance ( if set, always displayed by form )
	*			-- generated by main logic in this function to go with context
	*		4b. error_messages (shown only on save/updates) 
	*			-- generated by sanitize_validate_input; presence is switch to stop save or update
	*		4c. search_notices (shown only on searches )
	*			-- generated by sanitize_validate_input and by search_constituents
	*		Note: if message should be displayed in both searches and save/updates, must be appended to both b and c.		
	*		5. next_action (search/update/save) 
	*			-- set by main logic in this function 
	*		6.	strict match check box -- passed through from form
	*		7. initial form state (show/hide) --- set by main logic in this function
	*		8. field groups that have changes are pushed onto this array in search and update functions so that will show these sections open again 
	*
	*/
	public function wp_issues_crm_constituents() {
		
		global $wic_definitions;
		
		/* first check capabilities -- must be administrative user */
		if ( ! current_user_can ( 'activate_plugins' ) ) { 
			echo '<h3>' . __( 'Sorry, this function is only accessible to administrators.', 'simple-wp-crm' ) . '<h3>';
			return;
		} 

		$next_form_output = array();
		$this->initialize_blank_form($next_form_output);

		// if coming from main constituent form or from a constituent list . . . 
		if ( isset ( $_POST['wic_constituent_main_button'] ) || isset ( $_POST['wic_constituent_direct_button'] ) ) { 

			// test nonce before going further
			if( ! wp_verify_nonce($_POST['wp_issues_crm_constituent_nonce_field'], 'wp_issues_crm_constituent'))	{
				die ( 'Security check failed.' ); // if not nonce OK, die, otherwise continue  
			}
			
			if ( isset( $_POST['wic_constituent_main_button'] ) ) { 
				$user_request = $_POST['wic_constituent_main_button']; // search, update or save
				// clean and validate POST input and populate next form output	
				$this->sanitize_validate_input($next_form_output);
				// do search in all submitted cases, but do only on dup check fields if request is a save or update (does not alter next_form_output)
				$search_mode = ( 'search' == $user_request ) ? 'new' : 'dup';
			} elseif ( isset( $_POST['wic_constituent_direct_button'] ) ) { // coming in from crm-constituents-list.php
				$user_request = 'search';
				$next_form_output['constituent_id']	= $_POST['wic_constituent_direct_button'];
				$search_mode = 'db_check';		
			}

			$wic_query = $this->search_constituents( $search_mode, $next_form_output ); 
			
			// will show constituent list if found multiple or found a dup; default is false
			$show_list = false;			
			
			// do last form requests and define form_notices and next_action based on results of sanitize_validate, search_constituents and save/update requests  
			switch ( $user_request ) {	
				case 'search':
					if ( 0 == $wic_query->found_posts ) {
						$next_form_output['guidance']	=	__( 'No matching record found. Try a save? ', 'wp-issues-crm' );
						$next_form_output['next_action'] 	=	'save';
					} elseif ( 1 == $wic_query->found_posts ) { // overwrite form with that unique record's  values
						foreach ( $this->constituent_fields as $field ) {
							$post_field_key =  $this->wic_metakey . $field['slug'];
							// the following isset check should be necessary only if a search requesting more than the maximum search terms is executed 
							// note -- don't need to unserialize phones, etc. -- wp_query does this. also automatic in save_update_constituent  
							$next_form_output[$field['slug']] = isset ( $wic_query->post->$post_field_key ) ?  $wic_query->post->$post_field_key : '';
						}
						$next_form_output['constituent_notes'] = ''; // don't want to bring search notes automatically into update mode 
						$next_form_output['old_constituent_notes'] = isset ( $wic_query->post->post_content )  ? $wic_query->post->post_content: '';	
						$next_form_output['constituent_id'] 	= $wic_query->post->ID;	
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
					if ( 0 == $wic_query->found_posts || ( 1 == $wic_query->found_posts && $wic_query->post->ID == $next_form_output['constituent_id'] ) ) {
						$next_form_output['next_action'] 	=	'update'; // always proceed to further update after an update whether or not successful (unless poss dup)
						if ( $next_form_output['error_messages'] > '' ) { // validation errors from sanitize_validate_input which is always called above (and, unlikely, any search errors)
							$next_form_output['guidance']	=	__( 'Please correct form errors: ', 'wp-issues-crm' );	
						} else {
							$outcome = $this->save_update_constituent( $next_form_output );
							if ( $outcome['notices'] > '' )  { 
								$next_form_output['guidance'] = __( 'Please retry -- there were database errors. ', 'wp-issues-crm' );
								$next_form_output['error_messages'] = $outcome['notices'];
							} else { 
								$next_form_output['guidance'] = __( 'Update successful -- you can further update this record.', 'wp-issues-crm' );								
								if ( trim( $next_form_output[ 'constituent_notes' ] ) > '' ) { // update to database
									$next_form_output['old_constituent_notes'] = $wic_definitions->format_constituent_notes( $next_form_output['constituent_notes'] ) . $next_form_output['old_constituent_notes'];
									$next_form_output['constituent_notes'] = '';
								}
							}					
						}
					// error if form values match a record other than the original record	
					} else { 
						$next_form_output['guidance'] = '';						
						$next_form_output['constituent_id'] = 0; // reset so search does not bring back the original record
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
							$outcome = $this->save_update_constituent( $next_form_output );
							if ( $outcome['notices'] > ''  ) { // alpha return_post_id is error string
								$next_form_output['guidance']	=	__( 'Please retry -- there were database errors: ', 'wp-issues-crm' );
								$next_form_output['error_messages'] = $outcome['notices'];
								$next_form_output['next_action'] 	=	'save';
							} else {
								$next_form_output['constituent_id'] = $outcome['post_id'];
								$next_form_output['guidance']	=	__( 'Record saved -- you can further update this record.', 'wp-issues-crm' );
								$next_form_output['next_action'] 	=	'update';
								if ( trim( $next_form_output[ 'constituent_notes' ] )  > '' ) { // parallels update to database
									$next_form_output['old_constituent_notes'] = $wic_definitions->format_constituent_notes( $next_form_output['constituent_notes'] ) . $next_form_output['old_constituent_notes'];
									$next_form_output['constituent_notes'] = '';
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

			// prepare to show list of constituents if found more than one
			if ( $show_list ) {
				$wic_list_constituents = new WP_Issues_CRM_Constituents_List ($wic_query);			
				$constituent_list = $wic_list_constituents->constituent_list;
				if ( 'search' == $user_request  && '' == $next_form_output['search_notices'] ) // always show form unless was a search and no search notices
					$next_form_output['initial_form_state'] = 'wic-form-closed';
			} else {
				$constituent_list = '';			
			}

			// done with query
			wp_reset_postdata();

 		} // close if returning form button values
 		
 		// deliver the results (blank if new form)
 		ob_start();
 		$this->display_form( $next_form_output );
 		if ( isset ( $constituent_list ) ) {
			echo $constituent_list; 		
 		}
 		ob_end_flush();

   } // close function
	
	/*
	* function: display_form
	*
	* displays form with controls based on search/update/save next action and field definitions
	* values in the next_form_output array are never altered here -- this function only makes display decisions
	*   (exception is flatten or pop-up serialized repeater arrays for display)
	* $next_form_output is previously populated with values in wp_issues_crm_constituents() and functions 
	* see inventory of values and dispositions in comments before that function
	* 
	* All output data which is not hardcoded is escaped using one of the following: esc_attr, esc_html, esc_textarea (or absint if known should be integer)
	*   Note that the only data which not been run through validation previously is anything coming from a find and also constituent_notes -- but, all are escaped.
	*
	* Field def data is not considered hardcoded here, since may add interface later, and so is escaped in the same way.	
	*
	*
	*/
	
	public function display_form ( &$next_form_output ) {
		
		global $wic_definitions; // access for functions ( field definitions already instantiated  in construct )
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

		<form id = "constituent-form" method="POST" autocomplete = "on">

			<div class = "constituent-field-group wic-group-odd">
			
				<?php if ( 'update' == $next_form_output['next_action'] ) {
					$form_header = $next_form_output['first_name'] . ' ' . $next_form_output['last_name'];
					$form_header = ( '' == $form_header ) ? $next_form_output['emails'][0][1] : $form_header;
				} else {
					$form_header = __( $this->button_actions[$next_form_output['next_action']], 'wp_issues_crm');
				}
				echo '<h2>' . $form_header . '</h2>'; 
				
				if ( 'wic-form-closed' == $next_form_output['initial_form_state'] ) {
					echo '<button id = "form-toggle-button" type="button" onclick = "toggleConstituentForm()">' . __( 'Show Search Form', 'wp-issues-crm' ) . '</button>';		
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
			   	<div id="constituent-form-message-box" class = "<?php echo $notice_class; ?>" ><?php echo $message; ?></div>
			   <?php }
			   
				/* first instance of buttons */		   
		  		?><button class = "wic-form-button" name="wic_constituent_main_button" type="submit" value = "<?php echo $next_form_output['next_action']; ?>"><?php _e( $this->button_actions[$next_form_output['next_action']], 'wp_issues_crm'); ?></button>	  
	
				<?php if ( 'save' == $next_form_output['next_action'] ) {  // show this on save, but not update -- on update, have too much data in form, need to reset ?>  
					<button  class = "wic-form-button second-position" name="wic_constituent_main_button" type="submit" value = "search"><?php _e( 'Search Again', 'wp_issues_crm'); ?></button>
				<?php } ?>		 		

			</div>   
		
			<?php
			/* initialize field footnotes and footnote legends */
			$required_individual = '';
			$required_group = '';
			$contains = '';
			$required_group_legend = '';
			$required_individual_legend = ''; 								
			$contains_legend = false;
			$serialized_contains_legend = false;


			/* format meta fields  -- loop through constituent field groups and within them through fields */
			$group_count = 0;
		   foreach ( $this->constituent_field_groups as $group ) {
		   	

						   	
				$filtered_fields = $this->select_key ( $this->constituent_fields, 'group', $group['name'] );
				$row_class = ( 0 == $group_count % 2 ) ? "wic-group-even" : "wic-group-odd";
				$group_count++;
				
				echo '<div class = "constituent-field-group ' . $row_class . '" id = "wic-field-group-' . esc_attr( $group['name'] ) . '">';				
				
					$section_open = in_array( $group['name'], $next_form_output['initial_sections_open'] ) ? true : $group['initial-open'];				
				
					$button_args = array (
						'class'			=> 'field-group-show-hide-button',		
						'name_base'		=> 'wic-inner-field-group-',
						'name_variable' => $group['name'],
						'label' 			=> $group['label'],
						'show_initial' => $section_open,
					);
			
					echo $wic_definitions->output_show_hide_toggle_button( $button_args );			
				
					$show_class = $section_open ? 'visible-template' : 'hidden-template';
					echo '<div class="' . $show_class . '" id = "wic-inner-field-group-' . esc_attr( $group['name'] ) . '">' .					
					'<p class = "constituent-field-group-legend">' . esc_html ( $group['legend'] )  . '</p>';

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

							if ( in_array( $field['type'], $wic_definitions->serialized_field_types ) ) {
								$contains =  '(%!)';
								$serialized_contains_legend = true;
							}
						}

						/* if have repeating fields, treat as string for search (can be new search or search where already working as array) */						
						if ( in_array( $field['type'], $wic_definitions->serialized_field_types ) ) {
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
								echo '<p>' . $wic_definitions->create_text_control ( $args ) . '</p>'; 
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
									echo '<p>' . $wic_definitions->create_text_control ( $args ); 

									$args = array (
										'field_name_id'		=> $field['slug'] . '_hi',
										'field_label'			=>	__( 'and <=', 'wp_issues_crm' ),
										'label_class'			=> 'wic-label-2',
										'value'					=> $next_form_output[$field['slug']. '_hi'],
										'read_only_flag'		=>	false, 
										'field_label_suffix'	=> '', 								
									);
									echo $wic_definitions->create_text_control ( $args ) . '</p>'; 
								}	else {
									$args['field_label_suffix'] = $required_individual . $required_group;  								
									echo '<p>' . $wic_definitions->create_text_control ( $args ) . '</p>'; 
								} 
								break;

							case 'readonly': 
								if ( 'save' != $next_form_output['next_action'] ) { // do not display for save
									$args['read_only_flag'] = 	( 'update' == $next_form_output['next_action'] ); // true or false 
									echo '<p>' . $wic_definitions->create_text_control ( $args ) . '</p>'; 
								} 
								break;

							case 'check':
								echo '<p>' . $wic_definitions->create_check_control ( $args ) . '</p>'; 
								break;
								
							case 'select':
								$args['placeholder'] 			= __( 'Select', 'wp-issues-crm' ) . ' ' . $field['label'];
								$args['select_array']			=	$field['select_array'];
								$args['field_label_suffix']	= $required_individual . $required_group;								
								echo '<p>' . $wic_definitions->create_select_control ( $args ) . '</p>';
								break; 
							
							case 'serialized_type_as_array': // note -- non-arrays already intercepted above  
								$group_args	= array (
									'repeater_group_id'		=> $field['slug'],
									'repeater_group_label'		=> $field['label'],
									'repeater_group_data_array'	=>	$next_form_output[$field['slug']],
									'repeater_group_label_suffix'	=> $required_individual . $required_group . $contains,		
								);
								$repeater_function = 'create_' . $field['type'] . '_group';
								echo $wic_definitions->$repeater_function ( $group_args );
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
								echo '<p>' . $wic_definitions->create_select_control ( $args ) . '</p>';
								break; 
														
						}
					} // close foreach field				
				echo '</div></div>';		   
		   } // close foreach group
		
		
			// constituent notes div
			$row_class = ( 0 == $group_count % 2 ) ? "wic-group-even" : "wic-group-odd"; 
			$group_count++;
			echo '<div class = "constituent-field-group ' . $row_class . '" id = "constituent-notes">';
			
			$show_initial = ( "update" == $next_form_output['next_action'] ); // show notes on update next			
			$show_initial = in_array( 'constituent_notes', $next_form_output['initial_sections_open'] ) ? true : $show_initial; // also were searched or updated 
			
			$button_args = array (
				'class'			=> 'field-group-show-hide-button',		
				'name_base'		=> 'wic-inner-field-group-',
				'name_variable' => 'constituent-notes',
				'label' 			=> __('Constituent Notes', 'wp-issues-crm' ),
				'show_initial' =>  ( $show_initial ),
			);
			
			echo $wic_definitions->output_show_hide_toggle_button( $button_args );
			
			$show_class = $show_initial ? 'visible-template' : 'hidden-template';
						
			echo '<div id = "wic-inner-field-group-constituent-notes" class="' . $show_class .'">';	
			// echo '<h3 class = "constituent-field-group-label">' . __('Constituent Notes', 'wp-issues-crm' ) . '</h3>';
				$args = array (
					'field_name_id'		=> 'constituent_notes',
					'field_label'			=>	__( "Note Text", 'wp-issues-crm' ),
					'value'					=> $next_form_output['constituent_notes'],
					'read_only_flag'		=>	false, 
					'field_label_suffix'	=> '(%!)', 								
					);			
				// show constituent notes as input for search or as text area for update
				if ( 'search' == $next_form_output['next_action'] ){
					echo '<p>' . $wic_definitions->create_text_control ( $args ) . '</p>'; 
				} else {
					$args['field_label_suffix']	= '';
					$args['input_class'] = 'wic-input wic-constituent-notes';
					echo '<p>' . $wic_definitions->create_text_area_control($args) . '</p>';
					
					$args['field_name_id'] = 'old_constituent_notes';
					$args['read_only_flag']	= true;
					$args['input_class'] = 'hidden-template';
					$args['label_class'] = 'hidden-template';
					$args['value']	= $next_form_output['old_constituent_notes'];
					echo '<p>' . $wic_definitions->create_text_area_control($args) . '</p>';
					echo '<div id = "wic-old-constituent-notes">' .  balancetags( wp_kses_post ( $next_form_output['old_constituent_notes'] ), true ) . '</div>';
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
			echo '<div class = "constituent-field-group ' . $row_class . '" id = "bottom-button-group">';?>
				<?php if ( 'update' == $next_form_output['next_action'] ) { ?>
					<p><a href="<?php echo( home_url( '/' ) ) . 'wp-admin/post.php?post=' . absint( $next_form_output['constituent_id'] ) . '&action=edit' ; ?>" class = "wic-back-end-link"><?php printf ( __('Direct edit constituent # %1$s <br/>', 'wp_issues_crm'), absint( $next_form_output['constituent_id'] ) ); ?></a></p>
				<?php } ?>		
			
				<input type = "hidden" id = "constituent_id" name = "constituent_id" value ="<?php echo absint( $next_form_output['constituent_id'] ) ; ?>" />					
		  		
		  		<button  class = "wic-form-button" id="wic_constituent_main_button" name="wic_constituent_main_button" type="submit" value = "<?php echo $next_form_output['next_action']; ?>"><?php _e( $this->button_actions[$next_form_output['next_action']], 'wp_issues_crm'); ?></button>	  
	
				<?php if ( 'save' == $next_form_output['next_action'] ) { ?>
					<button  class = "wic-form-button second-position" id="redo_search_button" name="wic_constituent_main_button" type="submit" value = "search"><?php _e( 'Search Again', 'wp_issues_crm'); ?></button>
				<?php } ?>		 		
		
		 		<?php wp_nonce_field( 'wp_issues_crm_constituent', 'wp_issues_crm_constituent_nonce_field', true, true ); ?>
	
			   
				<?php if ( $contains_legend ) { 
					$text_control_args = array ( 
						'field_name_id'		=> 'strict_match',
						'field_label'			=>	'(%) ' . __( 'Full-text search conditionally enabled for these fields -- require strict match instead? ' , 'wp-issues-crm' ),
						'value'					=> $next_form_output['strict_match'],
						'read_only_flag'		=>	false, 
						'field_label_suffix'	=> '', 	
					);
					echo '<p class = "wic-constituent-form-legend">' . $wic_definitions->create_check_control ( $text_control_args ) . '</p>';
				} ?>	
				
				<?php if ( $serialized_contains_legend ) { 
					echo '<p class = "wic-constituent-form-legend" >(%!) ' . __( 'Full-text search always enabled for these fields.', 'wp-issues-crm'  ) . '</p>';
				} ?>	
				<?php if ( $required_individual_legend > '' ) { ?>
					<p class = "wic-constituent-form-legend"><?php echo $required_individual_legend; ?> </p>
				<?php } ?> 								
	
				<?php if ( $required_group_legend > '' ) { ?>
					<p class = "wic-constituent-form-legend"><?php echo $required_group_legend; ?> </p>
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
		
		
	/*
	*  search_constituents
	*	does search based on passed array of form fields in one of three search modes:
	*		new -- searches based on all meta fields (this value is passed only when user request = 'search')
	*		db_check	-- searches based on constituent_id
	*		dup -- searches based on only dup_check metafields	(this value passed only when user request = 'save' or 'update' )
	*
	*  note -- trusts Wordpress to escape strings for query -- they have had slashes and tags stripped in input validation, 
	*  but might have quotes & reserved words
	*/
   private function search_constituents( $search_mode, &$next_form_output) {
		
		global $wic_definitions;		
		
		if ( 'dup' == $search_mode || 'new' == $search_mode ) {  	
	   	$meta_query_args = array(
	     		'relation'=> 'AND',
	     	);
			$index = 1;
			$ignored_fields_list = '';

	 		foreach ( $this->constituent_fields as $field ) {
				if ( 'date' == $field['type'] && 'new' == $search_mode ) { // handle date as range in new searches 
					if ( $next_form_output[$field['slug'] . '_lo'] > '' || $next_form_output[$field['slug'] . '_hi'] > '' ) {
					array_push( $next_form_output['initial_sections_open'], $field['group'] ); // show field's section open in next form
						if ( $next_form_output[$field['slug'] . '_lo'] > '' ) { 
							if ( ( $index - 1 ) < $this->search_terms_max )	{ 	
								$meta_query_args[$index] = array(
									'key' 	=> $this->wic_metakey . $field['slug'], // wants 'key' as key , not 'meta_key', otherwise searches across all meta_keys 
									'value'		=> $next_form_output[$field['slug'] . '_lo'],
									'compare'	=>	'>=',
								);	
							} else { 
								$ignored_fields_list = ( $ignored_fields_list == '' ) ? $field['label'] . ' (low) '  : ( $ignored_fields_list .= ', ' . $field['label'] . ' (low) ' ); 
							}
							$index++;
						}	
						if ( $next_form_output[$field['slug'] . '_hi'] > '' ) {
							if ( ( $index - 1 ) < $this->search_terms_max )	{		
								$meta_query_args[$index] = array(
									'key' 	=> $this->wic_metakey . $field['slug'], // wants 'key' as key , not 'meta_key', otherwise searches across all meta_keys 
									'value'		=> $next_form_output[$field['slug'] . '_hi'],
									'compare'	=>	'<=',
								);	
							} else { 
								$ignored_fields_list = ( $ignored_fields_list == '' ) ? $field['label'] . ' (high) ' : ( $ignored_fields_list .= ', ' . $field['label'] . ' (high) ' ); 
							}
							$index++;
						}					
					}	
				} else { // standard = or like handling (including for dates in dedup mode)
		 			if( $next_form_output[$field['slug']] > '' && ( 'new' == $search_mode  || $field['dedup'] ) )  { 
		 				array_push( $next_form_output['initial_sections_open'], $field['group'] ); // show field's section open in next form
						if ( ( ( $index - 1 ) < $this->search_terms_max ) || $field['dedup'] )	{ // allow possibility to set more dedup fields than allowed search fields		
							if ( is_array( $next_form_output[$field['slug']] ) ) { // happens only for phone, email, street address; regardless of next action, have to flatten for search
								$meta_value = $next_form_output[$field['slug']][0][1]; // the first, phone, email or street address
							} else {
								$meta_value = $next_form_output[$field['slug']];
							}
							$meta_query_args[$index] = array(
								'key' 	=> $this->wic_metakey . $field['slug'], // wants 'key' as key , not 'meta_key', otherwise searches across all meta_keys 
								'value'		=> $meta_value,
								'compare'	=>	(  // do strict match in dedup mode
														( $field['like'] && ! $next_form_output['strict_match'] && 'new' == $search_mode ) ||
														in_array( $field['type'], $wic_definitions->serialized_field_types ) 
													) ? 'LIKE' : '=' ,
							);	
						} else { 
							$ignored_fields_list = ( $ignored_fields_list == '' ) ? $field['label'] : ( $ignored_fields_list .= ', ' . $field['label'] ); 
						}
						$index++;
					}	
				}		
	 		}
	 		if ( $ignored_fields_list > '' ) {
	 			$next_form_output['search_notices'] .= sprintf( __( 'Note: Maximum %1$s search terms allowed to protect performance -- the search was executed, but excess search terms were ignored ( %2$s ).', 'wp-issues-crm' ), 
	 				$this->search_terms_max, $ignored_fields_list ); 
				$next_form_output['error_messages'] .= sprintf( __( 'Note: Maximum %1$s search terms allowed to protect performance -- the search was executed, but excess search terms were ignored ( %2$s ).', 'wp-issues-crm' ), 
	 				$this->search_terms_max, $ignored_fields_list ); 
	 		} 
	 		$query_args = array (
	 			'posts_per_page' => 100,
	 			'post_type' 	=>	'wic_constituent',
	 			'meta_query' 	=> $meta_query_args, 
	 			'orderby'		=> 'title',
	 			'order'			=> 'ASC',
	 			's'				=> $next_form_output['constituent_notes'] ,
	 		);
	 		
	 		if ( $next_form_output['constituent_notes']  > '' ) {
				array_push( $next_form_output['initial_sections_open'], 'constituent-notes' ); // show field's section open in next form
			}	 		
	 		
	 	} elseif ( 'db_check' == $search_mode ) { 
			$query_args = array (
				'p' => $next_form_output['constituent_id'],
				'post_type' => 'wic_constituent',			
			);	 	
	 	} 

 		$wic_query = new WP_Query($query_args);
 
 		return $wic_query;
	}

	/*
	*
	*	sanitize_validate_input: form sanitization and validation function:
	*  takes blank array and populates it from $_POST while sanitizing it
	*   
	* 		1. handles all meta fields defined as displayable for constituents
	*		2. constituent notes
	*		3. constituent id
	*		4. form_notices with validation errors
	*
	*	The following sanitization/validation is done:
	*
	*	(1) All fields have stripslashes applied
	*  (2) All fields except constituent notes have sanitize_text_field applied -- "Checks for invalid UTF-8, 
	*			Convert single < characters to entity, strip all tags, remove line breaks, tabs and extra white space, strip octets."
	*	(3) Constituent notes is not sanitized or validated other than by stripping slashes (trust Wordpress on search/save)
	*	(4) All content validation rules are applied 
	*			- required fields
	*			- email formatting 
	*			- date formatting
	*			- phones compressed to numeric only
	*			- note select fields are compressed to numeric or slash/textsanitized
	*	  
	*	uses php converts DateTime object to recognize date formats and convert to yyyy-mm-dd
	*  
	*/   
   
   private function sanitize_validate_input( &$clean_input ) {
   	// takes initialized blank working array and populates it. 
   	$group_required_test = '';
   	$group_required_label = '';
		global $wic_definitions; 
		$possible_validator = '';   	
    	
   	foreach ( $this->constituent_fields as $field ) {
   		
   		if ( in_array( $field['type'], $wic_definitions->serialized_field_types ) && isset( $_POST[$field['slug']] ) ) {
 	 			// if array, load array, sanitizing all fields and cleaning/validating (using array validation function)	  		
 				if ( is_array( $_POST[$field['slug']] ) ) {
		  			$validation_function = 'validate_' . $field['type'];
					$repeater_count = 0;
					foreach( $_POST[$field['slug']] as $key => $value ) {	
						if ( 'row-template' !== $key ) { // skip template row -- NB:  true: 0 == 'alphastring' false: 0 != 'alphastring true 0 !== 'alphastring'
							$test_repeater = $wic_definitions->$validation_function($value);
							if ( $test_repeater['present'] ) { // skip rows that validate to absent
								$clean_input[$field['slug']][$repeater_count] = $test_repeater['result'];
								$repeater_count++;
								if ( $test_repeater['error'] > '' ) {
									$clean_input['error_messages'] .= ' ' . $test_repeater['error'] . ' ' . $field['label'] . ' ' . $repeater_count . '. '; 
								}
							}	 						
						}
					}
				} else { // non array for serialized field is only from a search -- compress/sanitize, but not validate
					if ( 'phones' == $field['type'] ) {
						$clean_input[$field['slug']] = preg_replace("/[^0-9]/", '', $_POST[$field['slug']] );
					} else {
						$clean_input[$field['slug']] = stripslashes( sanitize_text_field( $_POST[$field['slug']] ) );
					}
				} // close non-array for serialized fields
			} else { // not a serialized field and/or not set	-- do clean and also individual field validators
 				$clean_input[$field['slug']] = isset( $_POST[$field['slug']] ) ? stripslashes( sanitize_text_field( $_POST[$field['slug']] ) ) : '';
 				$possible_validator =  'validate_individual_' . $field['type'];
 				if ( $clean_input[$field['slug']] > '' && method_exists ( $wic_definitions, $possible_validator )  ) {
					 $clean_input['error_messages'] .= $wic_definitions->$possible_validator( $clean_input[$field['slug']] );				
 				} 
			}

			// add date hi-lo ranges to array and standardize all dates to yyyy-mm-dd 
			if ( 'date' == $field['type'] ) {
				$clean_input[$field['slug'] . '_lo' ] = isset( $_POST[$field['slug'] . '_lo' ] ) ? stripslashes( sanitize_text_field( $_POST[$field['slug'] . '_lo' ] ) ) : '';			
				$clean_input[$field['slug'] . '_hi' ] = isset( $_POST[$field['slug'] . '_hi' ] ) ? stripslashes( sanitize_text_field( $_POST[$field['slug'] . '_hi' ] ) ) : '';
				if ( $clean_input[$field['slug']] > '' ) {
					$clean_input[$field['slug']]  = $this->validate_date( $clean_input[$field['slug']] );
					if ( '' == $clean_input[$field['slug']] ) {
						$clean_input['error_messages'] .= $field['label'] .__( ' had unsupported date format -- yyyy-mm-dd will work. ', 'wp-issues-crm' );					
					}
				} 
				if ( $clean_input[$field['slug'] . '_lo' ]  > '' ) {
					$clean_input[$field['slug'] . '_lo' ]  = $this->validate_date( $clean_input[$field['slug'] . '_lo' ] );
					if ( '' == $clean_input[$field['slug'] . '_lo' ] ) {
						$clean_input['search_notices'] .= $field['label'] . ' (low) ' . __( ' had unsupported date format -- yyyy-mm-dd will work. ', 'wp-issues-crm' );					
					}
				}				
				if ( $clean_input[$field['slug'] . '_hi' ]  > '' ) {
					$clean_input[$field['slug']  . '_hi' ]  = $this->validate_date( $clean_input[$field['slug'] . '_hi' ] );
					if ( '' == $clean_input[$field['slug'] . '_hi' ] ) {
						$clean_input['search_notices'] .= $field['label'] . ' (high) ' . __( ' had unsupported date format -- yyyy-mm-dd will work. ', 'wp-issues-crm' );					
					}
				}							
			}		
			
			// do test for group required (including first among any repeater fields)
			if ( 'group' == $field['required'] ) {
				$group_required_test .=	is_array ( $clean_input[$field['slug']] ) ? $clean_input[$field['slug']][0][1] : $clean_input[$field['slug']] ;
				$group_required_label .= ( '' == $group_required_label ) ? '' : ', ';	
				$group_required_label .= $field['label'];	
			}

			// do individual field required tests and for non-blank to email validation
			if ( ! $clean_input[$field['slug']] > ''  ) { // note array always > '' and we do not store blank arrays, so this suffices for the array fields 
				if( 'individual' == $field['required'] ) {
					$clean_input['error_messages'] .= ' ' . sprintf( __( ' %s is a required field. ' , 'wp-issues-crm' ), $field['label'] );				
				}   		
   		}
   	}
		
		// outside the loop -- test group requires after all fields passed 
		if ( '' == $group_required_test && $group_required_label > '' ) {
			$clean_input['error_messages'] .= sprintf ( __( ' At least one among %s is required. ', 'wp-issues-crm' ), $group_required_label );
   	}

		$clean_input['constituent_notes'] = isset ( $_POST['constituent_notes'] ) ? stripslashes ( ( $_POST['constituent_notes'] ) ) : '' ;
		$clean_input['old_constituent_notes'] = isset ( $_POST['old_constituent_notes'] ) ? stripslashes ( $_POST['old_constituent_notes'] ) : '' ;
   	$clean_input['constituent_id'] = absint ( $_POST['constituent_id'] ); // always included in form; 0 if unknown;
		$clean_input['strict_match']	=	isset( $_POST['strict_match'] ) ? true : false; // only updated on the form; only affects search_constituents
		$clean_input['initial_form_state'] = 'wic-form-open';		
   } 
	/*
	* date sanitization function
	*
	*/   
	public function validate_date ( $possible_date ) {
		try {
			$test = new DateTime( $possible_date );
		}	catch ( Exception $e ) {
			return ( '' );
		}	   			
 		return ( date_format( $test, 'Y-m-d' ) );
	}
   
   /*
   *
	*  save_update_constituent
	*
	*  does save or update based on next form input ( update if constituent_id is populated with value > 0 ) 
	*	
	*  note: here do serialization (and on extraction, so could change db interface for repeating fields with change here and in update/populate)
	*  serialization is built into save meta, so no actual change in this code to reflect array handling
	*
	*	note: trusting wordpress for data escaping on save -- no validation of post_content, except on display -- see comments in display form
	*/
   private function save_update_constituent( &$next_form_output ) { 
		
		global $wic_definitions;

		$outcome = array (
			'post_id'	=> 0,
		   'notices'	=> '', 
		);		
		
		// for title, use group email if have it, otherwise use individual email 
		$email_for_title = '';
		if ( isset( $next_form_output['email_group'] ) ) {
			$email_for_title = isset( $next_form_output['email_group'][0][1] ) ? $next_form_output['email_group'][0][1]  : '';
		} 
		if ( '' == $email_for_title ) {
			$email_for_title = isset( $next_form_output['email'] ) ? $next_form_output['email_group']  : ''; 
		}
		
   	// title is ln OR ln,fn OR fn OR email -- one of these is required in validation to be non-blank.	
		$title = 	isset ( $next_form_output['last_name'] ) ? $next_form_output['last_name'] : '';
		$title .= 	isset ( $next_form_output['first_name'] ) ? ( $title > '' ? ', ' : '' ) . $next_form_output['first_name'] : '';
		$title =		( '' == $title ) ? $email_for_title : $title;
		
		$post_args = array(
		  'post_title'     => $title,
		  'post_status'    => 'private',
		  'post_type'      => 'wic_constituent',
		  'comment_status' => 'closed' 
		); 
		
		if ( $next_form_output['constituent_id'] > 0 ) { // if have constituent ID, do update if notes or title changed
			$check_on_database = $this->search_constituents( 'db_check', $next_form_output ); // bullet proofing and get values to see if changed
			if ( ! isset ( $check_on_database->post->ID ) )  {
				$outcome['notices'] = __( 'Unknown error. Could not find record to update', 'wp-issues-crm' );
				return ( $outcome );			
			} 
			$post_args['ID'] = $next_form_output['constituent_id'];
/*			if ( $next_form_output[ 'constituent_notes' ] != $check_on_database->post->post_content ||
				$title != $check_on_database->post->post_title ) { -- these were replaced by next two lines lines*/
			if ( trim( $next_form_output[ 'constituent_notes' ] )   > '' || $title != $check_on_database->post->post_title ) {
				array_push( $next_form_output['initial_sections_open'], 'constituent_notes' ); // show field's section open in next form
				$post_args['post_content'] = $wic_definitions->format_constituent_notes( $next_form_output['constituent_notes'] )  . $check_on_database->post->post_content;
				$outcome['post_id'] = wp_update_post( $post_args ); 
			} else {
				$post_args['post_content'] = $wic_definitions->format_constituent_notes( $next_form_output['constituent_notes'] );
				$outcome['post_id'] = $next_form_output['constituent_id'];			
			}
		} else {
			$outcome['post_id'] = wp_insert_post( $post_args );		
		}				
		// save or update error return with error
		if ( 0 == $outcome['post_id'] ) {
			$outcome['notices'] = __( 'Unknown error. Could not save/update constituent record.  Do new constituent search on same constituent to check for partial results.', 'wp-issues-crm' );
			return ($outcome);					
		}
		// otherwise proceed to update metafields
		 foreach ( $this->constituent_fields as $field ) {
		 	// note: in the not read only branch, explicitly set meta_return in every case 
		 	if ( 'readonly' != $field['type'] ) {
				// note: add/update post meta automatically serializes arrays!				
				$post_field_key =  $this->wic_metakey . $field['slug'];
				// first handle existing post meta records already
				if ( $next_form_output['constituent_id'] > 0 ) { 
					if ( $next_form_output[$field['slug']] > '' ) { 
						if ( isset ( $check_on_database->post->$post_field_key ) ) {
							if( $next_form_output[$field['slug']] != $check_on_database->post->$post_field_key ) {
								array_push( $next_form_output['initial_sections_open'], $field['group'] ); // show field's section open in next form
								$meta_return = update_post_meta ( $next_form_output['constituent_id'], $post_field_key, $next_form_output[$field['slug']] );
							} else {
								$meta_return = 1; // no action if field value already on db correctly
							} 
						} else { // no value yet on database 
							array_push( $next_form_output['initial_sections_open'], $field['group'] ); // show field's section open in next form
							$meta_return = add_post_meta ( $next_form_output['constituent_id'], $post_field_key, $next_form_output[$field['slug']] );							
						}
					} else { // have empty field value
						if ( isset ( $check_on_database->post->$post_field_key ) ) {
							array_push( $next_form_output['initial_sections_open'], $field['group'] ); // show field's section open in next form
							$meta_return = delete_post_meta( $next_form_output['constituent_id'], $post_field_key );					
						} else {
							$meta_return = 1; // no action of field is blank and meta record not exist					
						}
						
					}
				// new constituent record
				} else { 
					if ( $next_form_output[$field['slug']] > '' ) { 
						$meta_return = add_post_meta ( $outcome['post_id'], $post_field_key, $next_form_output[$field['slug']] );
					} else { // for blank field set return to be OK (no action was taken)
						$meta_return = 1;					
					}
				}
				
				if ( ! $meta_return ) {
					$outcome['notices'] = sprintf( __( 'Unknown error. Could not save constituent detail -- %1$s.   Do new constituent search on same constituent to check for partial results.', 'wp-issues-crm' ), $field['label'] );
				}
			}	
		} 
		
		return ( $outcome );
	}	  
}	



 		
