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
	* field definitions for ready reference array 
	*
	*/
		
	private $constituent_fields = array();
	private $constituent_field_groups = array(); 

	private $search_terms_max = 5; // don't allow searches that will likely degrade performance 

	private $button_actions = array(
		'save' 	=>	'Save New Constituent Record',
		'search' => 'Search Constituents',
		'update' => 'Update Constituent Record',
	);
 	
	public function __construct() {
		add_shortcode( 'wp_issues_crm_constituents', array( $this, 'wp_issues_crm_constituents' ) );
		global $wic_definitions;
		foreach ( $wic_definitions->constituent_fields as $field )
			if ( $field['online'] ) { 		
 				 array_push( $this->constituent_fields, $field );
 			}
		$this->constituent_field_groups 	= &$wic_definitions->constituent_field_groups;
		$this->wic_metakey = &$wic_definitions->wic_metakey;
	}

	public function create_dup_check_fields_list() {
		$fields_string = '';
		foreach ( $this->constituent_fields as $field ) {
			if( $field['dedup'] ) {
				$fields_string = ( $fields_string > '' ) ? $fields_string . ', ' : '';
				$fields_string .= $field['label'];
			}		
		}
		return ( $fields_string . '.' );	
	}
/*
*
*	Initializes blank form -- all form values and display switches set
*  	see inventory below
*
*/
	public function initialize_blank_form(&$next_form_output)	{ 
			foreach ( $this->constituent_fields as $field ) {
				$next_form_output[$field['slug']] =	'';
			}
			$next_form_output['constituent_notes']	=	'';
			$next_form_output['constituent_id']		=	0;			
			$next_form_output['form_notices']		=	__( 'Enter values and search for constituents.', 'wp-issues-crm' );
			$next_form_output['next_action'] 		=	'search';
			$next_form_output['errors_found']		=	false;
			$next_form_output['strict_match']		=	false;
			$next_form_output['initial_form_state']= 	'wic-form-open';
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
	*			-- initialized to empty on reset or new; otherwise passed through from form by sanitize_validate_input
	*			-- not otherwise altered except are overlayed if found unique on search
	*		2. constituent notes
	*			-- same as 1
	*		3. constituent id 
	*			-- initialized to zero on reset or new; otherwise passed through from form by sanitize_validate_input
	*			-- set if search found unique (then offer update)
	* 			-- reset to zero if, on update attempt, found dups for new form values (then send back to search)
	*			-- set on save successful (then offer update) 
	*			-- so, is always zero if next_action is search or save and always set if next action is update
	*		4. form_notices
	*			-- modified by sanitize_validate_input, by search_constituents and by main logic in this function
	*		5. next_action (search/update/save) 
	*			-- set by main logic in this function 
	*		6.	errors_found -- set by main logic in this function; serves only to support formatting of message 
	*		7.	strict match check box -- passed through from form
	*		8. initial form state (show/hide)
	*
	*/
	public function wp_issues_crm_constituents() {

		/* first check capabilities -- must be administrative user */
		if ( ! current_user_can ( 'activate_plugins' ) ) { 
			echo '<h3>' . __( 'Sorry, this function is only accessible to administrators.', 'simple-wp-crm' ) . '<h3>';
			return;
		} 

		$next_form_output = array();
		$this->initialize_blank_form($next_form_output);
		
		// new or reset form -- serve blank fields
		if ( isset ( $_POST['main_button'] ) || isset ( $_POST['direct_button'] ) ) { 
			
			// test nonce before going further
			if( ! wp_verify_nonce($_POST['wp_issues_crm_constituent_nonce_field'], 'wp_issues_crm_constituent'))	{
				die ( 'Security check failed.' ); // if not nonce OK, die, otherwise continue  
			}
			
			if ( isset( $_POST['main_button'] ) ) { 
				$user_request = $_POST['main_button']; // search, update or save
				// clean and validate input and pass through to next form output, including hidden post ID field	
				$next_form_output = $this->sanitize_validate_input();
				// do search in all submitted cases, but do only on dup check fields if request is a save or update (does not alter next_form_output)
				$search_mode = ( 'search' == $user_request ) ? 'new' : 'dup';
			} elseif ( isset( $_POST['direct_button'] ) ) { // coming in from crm-constituents-list.php
				$user_request = 'search';
				$next_form_output['constituent_id']	= $_POST['direct_button'];
				$next_form_output['form_notices'] = '';
				$search_mode = 'db_check';		
			}

			$wic_query = $this->search_constituents( $search_mode, $next_form_output ); 
			
			// set flag to indicate that form message should highlight errors (from either search or update)
			$next_form_output['errors_found'] =  ( $next_form_output['form_notices'] > '' ) ? true : false;
			
			// show list if found multiple or found a dup
			$show_list = false;			
			
			// do last form requests and define form_notices and next_action based on results of sanitize_validate, search_constituents and save/update requests  
			switch ( $user_request ) {	
				case 'search':
					/* display through validation and search errors, but only as notices -- no forced action */
					$next_form_output['form_notices'] = ( $next_form_output['form_notices'] > '' ) ? __( ' Just FYI: ', 'wp-issues-crm' ) . $next_form_output['form_notices'] : '';
					if ( 0 == $wic_query->found_posts ) {
						$next_form_output['form_notices']	=	__( 'No matching record found. Try a save? ', 'wp-issues-crm' ) . $next_form_output['form_notices'];
						$next_form_output['next_action'] 	=	'save';
					} elseif ( 1 == $wic_query->found_posts ) { // overwrite form with that unique record's  values
						foreach ( $this->constituent_fields as $field ) {
							$post_field_key =  $this->wic_metakey . $field['slug'];
							// the following isset check should be necessary only if a search requesting more than the maximum search terms is executed 
							$next_form_output[$field['slug']] = isset ( $wic_query->post->$post_field_key ) ? $wic_query->post->$post_field_key : '';
						}
						$next_form_output['constituent_notes'] = $wic_query->post->post_content;	
						$next_form_output['constituent_id'] 	= $wic_query->post->ID;	
						$next_form_output['form_notices']	=	__( 'One matching record found. Try an update?', 'wp-issues-crm' ) . $next_form_output['form_notices'];
						$next_form_output['next_action'] 	=	'update';
					} else {
						$next_form_output['form_notices']	=	__( 'Multiple records found. ', 'wp-issues-crm' ) . $next_form_output['form_notices'];
						$next_form_output['next_action'] 	=	'search';
						$show_list = true;
					}						
					break;
				case 'update':
					// after dup_check search, if updated values do not match any record or match the original record, proceed to update 							
					if ( 0 == $wic_query->found_posts || ( 1 == $wic_query->found_posts && $wic_query->post->ID == $next_form_output['constituent_id'] ) ) {
						$next_form_output['next_action'] 	=	'update'; // always proceed to further update after an update whether or not successful (unless poss dup)
						if ( $next_form_output['form_notices'] > '' ) { // validation errors from sanitize_validate_input which is always called above (and, unlikely, any search errors)
							$next_form_output['form_notices']	=	__( 'Please correct form errors: ', 'wp-issues-crm' ) . $next_form_output['form_notices'];	
						} else {
							$outcome = $this->save_update_constituent( $next_form_output );
							if ( $outcome['notices'] > '' )  { 
								$next_form_output['form_notices'] = __( 'Please retry -- there were database errors. ', 'wp-issues-crm' ) . $outcome['notices'];
							} else {
								$next_form_output['form_notices'] = __( 'Update successful -- you can further update this record.', 'wp-issues-crm' );								
							}					
						}
					// error if form values match a record other than the original record	
					} else { 
						$next_form_output['constituent_id'] = 0;
						$next_form_output['form_notices']	=	 __( 'Record not updated -- other records match the combination of  ', 'wp-issues-crm' ) . $this->create_dup_check_fields_list();
						$next_form_output['next_action'] 	=	'search';
						$show_list = true;
					}						
					break;				
				case 'save':	
					if ( 0 == $wic_query->found_posts ) {
						if ( $next_form_output['form_notices'] > '' ) {
							$next_form_output['form_notices']	=	__( 'Please correct form errors: ', 'wp-issues-crm' ) . $next_form_output['form_notices'];
							$next_form_output['next_action'] 	=	'save';
						} else {
							$outcome = $this->save_update_constituent( $next_form_output );
							if ( $outcome['notices'] > ''  ) { // alpha return_post_id is error string
								$next_form_output['form_notices']	=	__( 'Please retry -- there were database errors: ', 'wp-issues-crm' ) . $outcome['notices'];
								$next_form_output['next_action'] 	=	'save';
							} else {
								$next_form_output['constituent_id'] = $outcome['post_id'];
								$next_form_output['form_notices']	=	__( 'Record saved -- you can further update this record.', 'wp-issues-crm' );
								$next_form_output['next_action'] 	=	'update';
							}					
						}
					} else {
						$next_form_output['form_notices']	=	__( 'Record not saved -- other records match the new combination of  ', 'wp-issues-crm' ) . $this->create_dup_check_fields_list();
						$next_form_output['next_action'] 	=	'search';
						$show_list = true;
					}						
					break;
			} // closes switch statement	

			// prepare to show list of constituents if found more than one
			if ( $show_list ) {
				$wic_list_constituents = new WP_Issues_CRM_Constituents_List ($wic_query);			
				$constituent_list = $wic_list_constituents->constituent_list;
				$next_form_output['initial_form_state'] = 'wic-form-closed';
			} else {
				$constituent_list = '';			
			}

			// done with query
			wp_reset_postdata();

 		} // close not a reset
 		
 		// deliver the results
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
	*
	* $next_form_output is previously populated with values for the following 
	* 		1. all meta fields defined as displayable for constituents
	*		2. constituent notes
	*		3. constituent id
	*		4. form_notices
	*		5. next_action (search/update/save) 
	*		6.	errors_found 
	*		7.	strict match check box
	*		8. initial form state
	* 
	*/
	
	public function display_form ( &$next_form_output ) {
		
		/*
		echo '<span style="color:green;"> $_POST:';  		
  		var_dump ($next_form_output);
  		echo '</span>'; */ 
		if ( 'wic-form-closed' == $next_form_output['initial_form_state'] ) {
			echo '<button id = "form-toggle-button" type="button" onclick = "toggleConstituentForm()">' . __( 'Show Search Form', 'wp-issues-crm' ) . '</button>';		
		}
		
		?><div id='wic-forms' class = "<?php echo $next_form_output['initial_form_state'] ?>">

		<form id = "constituent-form" method="POST" autocomplete = "on">
			<div class = "constituent-field-group wic-group-odd">
				<h2><?php _e( $this->button_actions[$next_form_output['next_action']], 'wp_issues_crm'); ?></h2>
				<?php 
				/* notices section */
				$notice_class = $next_form_output['errors_found'] ? 'wic-form-errors-found' : 'wic-form-no-errors';
				if ( $next_form_output['form_notices'] != '' ) { ?>
			   	<div id="constituent-form-message-box" class = "<?php echo $notice_class; ?>" ><?php echo $next_form_output['form_notices']; ?></div>
			   <?php }
			   
				/* first instance of buttons */		   
		  		?><button class = "wic-form-button" name="main_button" type="submit" value = "<?php echo $next_form_output['next_action']; ?>"><?php _e( $this->button_actions[$next_form_output['next_action']], 'wp_issues_crm'); ?></button>	  
	
				<?php if ( 'save' == $next_form_output['next_action'] ) {  // show this on save, but not update -- on update, have too much data in form, need to reset ?>  
					<button  class = "wic-form-button" name="main_button" type="submit" value = "search"><?php _e( 'Search Again', 'wp_issues_crm'); ?></button>
				<?php } ?>		 		
	
		  		<button  class = "wic-form-button" name="reset_button" type="submit" value = "<?php echo 'reset_form' ?>"><?php _e( 'Reset Form', 'wp_issues_crm'); ?></button>
			</div>   
		
			<?php
			/* initialize field flags and legends */
			$required_individual = '';
			$required_group = '';
			$contains = '';
			$required_group_legend = '';
			$required_individual_legend = ''; 								
			$contains_legend = false;


			/* format meta fields  -- loop through constituent field groups and within them through fields */
			$group_count = 0;
		   foreach ( $this->constituent_field_groups as $group ) {
				$filtered_fields = $this->select_key ( $this->constituent_fields, 'group', $group['name'] );
				$row_class = ( 0 == $group_count % 2 ) ? "wic-group-even" : "wic-group-odd";
				$group_count++;
				echo '<div class = "constituent-field-group ' . $row_class . '" id = "' . $group['name'] . '">' .
					'<h3 class = "constituent-field-group-label">' . $group['label'] . '</h3>' .
					'<p class = "constituent-field-group-legend">' . $group['legend'] . '</p>';
					foreach ( $filtered_fields as $field ) {	

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
						}								
						$contains = $field['like'] ? '(%)' : '';
						if( $field['like'] ) {
							$contains_legend = 'true';	
						}


						$field_type = is_array( $field['type'] ) ? 'dropdown' : $field['type']; 						
						switch ( $field_type ) {
							case 'email':						
							case 'text':
							case 'date':
								?><p><label class="wic-label" for="<?php echo $field['slug'] ?>"><?php echo __( $field['label'], 'wp-issues-crm' ) . ' ' . $required_individual . $required_group . $contains . ' '; ?></label>
								<input class="wic-input" id="<?php echo $field['slug'] ?>" name="<?php echo $field['slug'] ?>" type="text" value="<?php echo $next_form_output[$field['slug']]; ?>" /></p><?php 
								break;
							case 'readonly':
								if ( 'search' == $next_form_output['next_action'] || 'update' == $next_form_output['next_action'] ) {
									$readonly = ( 'update' == $next_form_output['next_action'] ) ? 'readonly' : '';
									?><p><label class="wic-label" for="<?php echo $field['slug'] ?>"><?php echo __( $field['label'], 'wp-issues-crm' ) . ' ' ; ?></label>
									<input class="wic-input"  id="<?php echo $field['slug'] ?>" name="<?php echo $field['slug'] ?>" type="text" value="<?php echo $next_form_output[$field['slug']]; ?>" <?php echo $readonly ?> /></p><?php
								} 
								break;
							case 'check':
								?><p><label class="wic-label" for="<?php echo $field['slug'] ?>"><?php echo __( $field['label'], 'wp-issues-crm' ) . ' ' ; ?></label>
								<input class="wic-input"  id="<?php echo $field['slug'] ?>" name="<?php echo $field['slug'] ?>" type="checkbox"  value="1" <?php checked( $next_form_output[$field['slug']], 1 );?> /></p><?php
								break;
							case 'dropdown':

								$selected = $next_form_output[$field['slug']];
								$not_selected_option = array (
									'value' 	=> '',
									'label'	=> 'Select ' . $field['label'],								
									);  
								$option_array =  $field['type'];
								array_push( $option_array, $not_selected_option );

								?><p><label class="wic-label" for="<?php echo $field['slug'] ?>"><?php echo __( $field['label'], 'wp-issues-crm' ) . $required_individual . $required_group ; ?></label>
								<select class="wic-input"  id="<?php echo $field['slug'] ?>" name="<?php echo $field['slug'] ?>" >
									<?php
									$p = '';
									$r = '';
									foreach ( $option_array as $option ) {
										$label = $option['label'];
										if ( $selected == $option['value'] ) { // Make selected first in list
											$p = '<option selected="selected" value="' . $option['value'] . '">' . $label . '</option>';
										} else {
											$r .= '<option value="' . $option['value'] . '">' . $label . '</option>';
										}
									}
								echo $p . $r .
								'</select></p>';						
						}
					} // close foreach 				
				echo '</div>';		   
		   }
		
			if ( 'search' != $next_form_output['next_action'] ){  
				$row_class = ( 0 == $group_count % 2 ) ? "wic-group-even" : "wic-group-odd"; 
				$group_count++;
				echo '<div class = "constituent-field-group ' . $row_class . '" id = "constituent-notes">';?>				
					<p><label for="constituent_notes"><?php _e( "Constituent Notes", 'wp-issues-crm' ); ?></label></p>
					<p><textarea class = "wic-input" id="constituent_notes" name="constituent_notes" rows="10" cols="50"><?php echo $next_form_output['constituent_notes']; ?></textarea></p>
				</div> 
			<?php } 
			
			$row_class = ( 0 == $group_count % 2 ) ? "wic-group-even" : "wic-group-odd";
			echo '<div class = "constituent-field-group ' . $row_class . '" id = "bottom-button-group">';?>
				<?php if ( 'update' == $next_form_output['next_action'] ) { ?>
					<p><a href="<?php echo( home_url( '/' ) ) . 'wp-admin/post.php?post=' . $next_form_output['constituent_id'] . '&action=edit' ; ?>" class = "back-end-link"><?php printf ( __('Direct edit constituent # %1$s <br/>', 'wp_issues_crm'), $next_form_output['constituent_id'] ); ?></a></p>
				<?php } ?>		
			
				<input type = "hidden" id = "constituent_id" name = "constituent_id" value ="<?php echo $next_form_output['constituent_id']; ?>" />					
		  		
		  		<button  class = "wic-form-button" id="main_button" name="main_button" type="submit" value = "<?php echo $next_form_output['next_action']; ?>"><?php _e( $this->button_actions[$next_form_output['next_action']], 'wp_issues_crm'); ?></button>	  
	
				<?php if ( 'save' == $next_form_output['next_action'] ) { ?>
					<button  class = "wic-form-button" id="redo_search_button" name="main_button" type="submit" value = "search"><?php _e( 'Search Again', 'wp_issues_crm'); ?></button>
				<?php } ?>		 		
	
		  		<button  class = "wic-form-button" id="reset_button" name="reset_button" type="submit" value = "<?php echo 'reset_form' ?>"><?php _e( 'Reset Form', 'wp_issues_crm'); ?></button>
	
		 		<?php wp_nonce_field( 'wp_issues_crm_constituent', 'wp_issues_crm_constituent_nonce_field', true, true ); ?>
	
			   
				<?php if ( $contains_legend ) { ?>
					<p><label class="wic-label" for="strict_match"><?php echo '(%) ' . __( 'Full-text search enabled for these fields -- require strict match instead? ' , 'wp-issues-crm' ) ; ?></label>
					<input  id="strict_match" name="strict_match" type="checkbox"  value="1" <?php checked( $next_form_output['strict_match'], 1 );?> /></p>
				<?php } ?>
	
				<?php if ( $required_individual_legend > '' ) { ?>
					<p><?php echo $required_individual_legend; ?> </p>
				<?php } ?> 								
	
				<?php if ( $required_group_legend > '' ) { ?>
					<p><?php echo $required_group_legend; ?> </p>
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
	*		new -- searches based on all meta fields
	*		db_check	-- searches based on constituent_id
	*		dup -- searches based on only dup_check metafields	
	*
	*/
   private function search_constituents( $search_mode, &$next_form_output) {

		if ( 'dup' == $search_mode || 'new' == $search_mode ) {  	
	   	$meta_query_args = array(
	     		'relation'=> 'AND',
	     	);
			$index = 1;
			$ignored_fields_list = '';
	 		foreach ( $this->constituent_fields as $field ) {
	 			if( $next_form_output[$field['slug']] > '' && ( 'new' == $search_mode  || $field['dedup'] ) )  { 
					if ( ( $index - 1 ) < $this->search_terms_max )	{		
							$meta_query_args[$index] = array(
							'key' 	=> $this->wic_metakey . $field['slug'], // wants key, not meta_key, otherwise searches across all keys 
							'value'		=> $next_form_output[$field['slug']],
							'compare'	=>	( ( $field['like'] && ! $next_form_output['strict_match'] ) ? 'LIKE' : '=' ),
						);	
					} else { 
						$ignored_fields_list = ( $ignored_fields_list == '' ) ? $field['label'] : ( $ignored_fields_list .= ', ' . $field['label'] ); 
					}
					$index++;
				}		
	 		}
	 		if ( $ignored_fields_list > '' ) {
	 			$next_form_output['form_notices'] = $next_form_output['form_notices'] . sprintf( __( 'Maximum %1$s search terms allowed to protect performance -- the search was executed, but excess search terms were ignored ( %2$s ).', 'wp-issues-crm' ), 
	 				$this->search_terms_max, $ignored_fields_list ); 
	 		} 
	 		$query_args = array (
	 			'posts_per_page' => 100,
	 			'post_type' 	=>	'wic_constituent',
	 			'meta_query' 	=> $meta_query_args, 
	 			'orderby'		=> 'title',
	 			'order'			=> 'ASC'
	 		);
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
	*	sanitize_validate_input: form sanitization and validation function
	*  takes $_POST as direct input and returns cleaned and possibly expanded array including
	*   
	* 		1. all meta fields defined as displayable for constituents
	*		2. constituent notes
	*		3. constituent id
	*		4. form_notices with validation errors
	*	  
	*	uses php converts DateTime object to recognize date formats and convert to yyyy-mm-dd
	*  
	*/   
   
   private function sanitize_validate_input() {

		$clean_input = array();
   	$clean_input['form_notices'] = '';
   	$group_required_test = '';
   	$group_required_label = '';
    	
   	foreach ( $this->constituent_fields as $field ) {
			$clean_input[$field['slug']] = isset( $_POST[$field['slug']] ) ? sanitize_text_field( $_POST[$field['slug']] ) : ''; 		
			
			if ( 'group' == $field['required'] ) {
				$group_required_test .=	$clean_input[$field['slug']];
				$group_required_label .= ( '' == $group_required_label ) ? '' : ', ';	
				$group_required_label .= $field['label'];	
			}

			if ( $clean_input[$field['slug']] > '' ) {
	   		if	( "email" == $field['type'] && ! filter_var( $clean_input[$field['slug']], FILTER_VALIDATE_EMAIL ) ) {
	   			$clean_input['form_notices'] .= __( 'Email address is not valid. ', 'wp-issues-crm' );
				}	
	   		if	( "date" == $field['type'] )  {
	   			$date_error = false;
					try {
						$test = new DateTime( $clean_input[$field['slug']] );
					}	catch ( Exception $e ) {
						$clean_input['form_notices'] .= $field['label'] .__( ' had unsupported date format -- yyyy-mm-dd will work. ', 'wp-issues-crm' );
						$clean_input[$field['slug']] = ''; 
						$date_error = true;
					}	   			
	   			if ( ! $date_error ) {
	   				$clean_input[$field['slug']] = date_format( $test, 'Y-m-d' );
	   			} 
				}						   		
   		} else {
				if( 'individual' == $field['required'] ) {
					$clean_input['form_notices'] .= ' ' . sprintf( __( ' On a record save, %s is a required field. ' , 'wp-issues-crm' ), $field['label'] );				
				}   		
   		}
   		
   	}
		
		if ( '' == $group_required_test && $group_required_label > '' ) {
			$clean_input['form_notices'] .= sprintf ( __( ' On a record save, at least one among %s is required. ', 'wp-issues-crm' ), $group_required_label );
   	}

		$clean_input['constituent_notes'] = isset ( $_POST['constituent_notes'] ) ? wp_kses_post ( $_POST['constituent_notes'] ) : '' ;   	
   	$clean_input['constituent_id'] = $_POST['constituent_id']; // always included in form; 0 if unknown;
		$clean_input['strict_match']	=	isset( $_POST['strict_match'] ) ? true : false; // only updated on the form; only affects search_constituents
		$clean_input['initial_form_state'] = 'wic-form-open';		
		
   	return ( $clean_input );
   } 
   /*
   *
	*  save_update_constituent
	*
	*  does save or update based on next form input ( update if constituent_id is populated with value > 0 ) 
	*
	*/
   private function save_update_constituent( &$next_form_output ) { 

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
		
		if ( $next_form_output['constituent_id'] > 0 ) { // if have constituent ID, do update if notes or title changed
			$check_on_database = $this->search_constituents( 'db_check', $next_form_output ); // bullet proofing and get values to see if changed
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
		// save or update error return with error
		if ( 0 == $outcome['post_id'] ) {
			$outcome['notices'] = __( 'Unknown error. Could not save/update constituent record.  Reset form, search for constituent and check results.', 'wp-issues-crm' );
			return ($outcome);					
		}
		// otherwise proceed to update metafields
		 foreach ( $this->constituent_fields as $field ) {
		 	if ( 'readonly' != $field['type'] ) {
				$post_field_key =  $this->wic_metakey . $field['slug'];
				// first handle existing post meta records already
				if ( $next_form_output['constituent_id'] > 0 ) { 
					if ( $next_form_output[$field['slug']] > '' ) { 
						if ( isset ( $check_on_database->post->$post_field_key ) ) {
							if( $next_form_output[$field['slug']] != $check_on_database->post->$post_field_key ) {
								$meta_return = update_post_meta ( $next_form_output['constituent_id'], $post_field_key, $next_form_output[$field['slug']] );
							} else {
								$meta_return = 1; // no action if field value already on db correctly
							} 
						} else { // no value yet on database 
							$meta_return = add_post_meta ( $next_form_output['constituent_id'], $post_field_key, $next_form_output[$field['slug']] );							
						}
					} else { // have empty field value
						if ( isset ( $check_on_database->post->$post_field_key ) ) {
							$meta_return = delete_post_meta( $next_form_output['constituent_id'], $post_field_key );					
						} else {
							$meta_return = 1; // no action of field is blank and meta record not exist					
						}
						
					}
				// new constituent record
				} else {
					if ( $next_form_output[$field['slug']] > '' ) {
						$meta_return = add_post_meta ( $outcome['post_id'], $post_field_key, $next_form_output[$field['slug']] );
					}
				}
				
				if ( ! $meta_return ) {
					$outcome['notices'] = __( 'Unknown error. Could not save/update constituent details.  Reset form, search for constituent and check results.', 'wp-issues-crm' );
				}
			}	
		} 
		
		return ( $outcome );
	}	  


   /* 
   *
   *
   *
   * import 
   *
   */
   function import(){ // fields must include so formatted first_name, last_name, email
	   // NEEDS UPDATEING TO REFLECT ONLINE EXCLUSION
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
				if ( isset ( $contact->$field['slug'] ) ) {
					if ( $contact->$field['slug'] > '' ) {
						$stored_record = add_post_meta ($post_id, $this->wic_metakey . $field['slug'], $contact->$field['slug'] );
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