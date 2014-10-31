<?php
/*
*
* class-wic-table.php
*
* base class for wic tables/entities
*
*
* 
*/

abstract class WIC_Table {
	
	// misc parameters
	public $labels = array (
		'singular' => '', 
		'plural'	  => ''	
	);

	public $sort_order = array (
		'orderby' => '', // field_slug
		'order'	  => '' // ASC or DSC
	);	
	
	public $max_records = 100;

	/*
	* main definitions array
	* see class-wic-fields for associative array keys in individual field definition arrays */
	*/
	public $field_definitions = array( 
		array( // 1
			'dedup'	=>	true,	
			/*  . . . */
		),		
		/* . . . */
	);

	protected $field_groups = array ( // for form display
		array (
			'name'		=> '', 
			'label'		=>	'',  
			'legend'		=>	'', // fine print below group header in form
			'order'		=>	0, // numeric 
			'initial-open'	=> true, // open state on first form display
		),
	);
	
	/* will hold array of field objects after __construct */
	protected $fields = array();

	/* will hold exceptions from __construct */
	protected $error_messages = '';
	protected $missing_fields = '';
	protected $search_notices = '';
	protected $guidance					=  'Enter just a little information and do a full text search.'; // note 4a
	protected $search_notices			=	'';					// note 4c
	protected $next_action				=	'search';			// note 5
	protected $strict_match				=	false;				// note 6
	protected $initial_form_state		= 	'wic-form-open';  // note 7 
	protected $initial_sections_open =   array();			// note 8


	protected function __construct ( $action_requested ) {

		/* sort fields for form presentation */
		$this->field_definitions = multi_array_key_sort ( $this->field_definitions, 'order' );
		$this->initialize_from_post();
		$this->$action_requested;

	}

	protected function initialize_from_post() {
		/* for each defined field, instantiate a field object (sanitize and validate post input) */		
		$group_required_test = '';
		$group_required_label = '';		
		foreach ( $this->field_definitions as $args ) {
			$class_name = 'WIC_' .  $args['type'] . '_Field';
			${$args['name']} = new $class_name ( $args );
			$this->fields[] = ${$args['name']};  
			$this->error_messages .= ${$args['name']}->validation_errors;	
			if ( '' = ${$args['name']}->present && "individual" == ${$args['name']}->required )
				$this->missing_fields .= ' ' . sprintf( __( ' %s is a required field. ' , 'wp-issues-crm' ), ${$args['name']}->label );
			}
			if  ( "group" == ${$args['name']}->required ) {
 				$group_required .= ${$args['name']}->present;
 				$group_required_label .= ( '' == $group_required_label ) ? '' : ', ';	
 				$group_required_label .= ${$args['name']}->label;
			}
		if ( '' == $group_required_test && $group_required_label > '' ) {
			$this->missing_fields .= sprintf ( __( ' At least one among %s is required. ', 'wp-issues-crm' ), $group_required_label );
   	}
	}

/***************THE LOGIC FLOW TO REPLACE *********************
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
	
				// do last form requests and define form_notices and next_action based on results of sanitize_validate, search_wic_posts and save/update requests  
				switch ( $this->action_requested ) {	

								

				} // closes switch statement	
			} // closes handling of cases other than simple referring parent case
			 
			// prepare to show list of posts if found more than one
			if ( $show_list ) {
				$wic_list_posts = new WP_Issues_CRM_Posts_List ( $wic_query, $this->working_post_fields, $this->form_requested, 0, true );			
				$post_list = $wic_list_posts->post_list;
				if ( 'search' == $this->action_requested  && '' == $next_form_output['search_notices'] ) // always show form unless was a search and no search notices
					$next_form_output['initial_form_state'] = 'wic-form-closed';
			} else {
				$post_list = '';			
			}

			// prepare to show list of posts if found exactly one
			$children_list_output = '';
			if ( $next_form_output['wic_post_id'] > 0 && count( $this->child_types ) > 0 ) { 
				$children_lists = $wic_database_utilities->get_children_lists ( $next_form_output['wic_post_id'], $this->form_requested, $this->child_types );
				foreach ( $children_lists as $child_list ) { 
					$wic_list_posts = new WP_Issues_CRM_Posts_List ( $child_list['list_query'], $child_list['fields_array'], $child_list['child_type'], $next_form_output['wic_post_id'],  false );	
					$children_list_output = $wic_list_posts->post_list;		
				}			
			}

			// done with queries
			wp_reset_postdata();
			wp_reset_query();

 		} // close if not just a new query
 		
 		// deliver the results ( if new form)
 		ob_start();
 		$this->display_form( $next_form_output );
 		if ( isset ( $post_list ) ) {
			echo $post_list; 		
 		}
 		if ( isset ( $children_list_output ) ) {
 			echo $children_list_output;	
 		}
 		ob_end_flush();

   } // close function
	
/**************THE LOGIC FLOW TO REPLACE IS ABOVE *********************************************/


	/* the major actions that can be requested of the object -- search, save, update */
	
	protected function new () {
		
	
	}	
	
	protected function search() {
		initialize_from_post();
		$wic_query = $wpdb->get_results( prepare_search_sql ('new') );
		if ( 0 == $wpdb->num_rows; ) {
			$this->guidance	=	__( 'No matching record found. Try a save? ', 'wp-issues-crm' );
			$this->next_action 	=	'save';
		} elseif ( 1 == $wpdb->num_rows; ) {
			// overwrite form with that unique record's  values
			$this->populate_fields ( $wic_query );
			$this->guidance	=	__( 'One matching record found. Try an update?', 'wp-issues-crm' );
			$this->next_action 	=	'update';
		} else {
			$this->guidance	=	__( 'Multiple records found (results below). ', 'wp-issues-crm' );
			$this->next_action 	=	'search';
			$show_list = true;
		}						
	} 
	
	protected function save() {
		initialize_fields_post();
		$wic_query = $wpdb->get_results( prepare_search_sql ('dup') );
		$this->error_messages = $this->missing_fields . $this->error_messages;
		if ( 0 == $wpdb->num_rows || $this->error_messages > '' ) { // putting error condition here puts form error checking ahead of dup checking 
			if ( $this->error_messages > '' ) { 
				$this->guidance	=	__( 'Please correct form errors: ', 'wp-issues-crm' );
				$this->next_action 	=	'save';
			} else {
				$success = $wpdb->insert( $table, prepare_save_update_array() )
				if ( ! $success ) { 
					$this->guidance	=	__( 'Please retry -- there were database errors: ', 'wp-issues-crm' );
					$this->error_messages = __( 'Unknown database error in save/update.', 'wp-issues-crm' );
					$this->next_action 	=	'save';
				} else {
					$this->ID['value'] = $wpdb->insert_id;	
					$this->guidance	=	__( 'Record saved -- you can further update this record.', 'wp-issues-crm' );
					$this->next_action 	=	'update';
						/* fix this	if ( trim( $next_form_output[ 'wic_post_content' ] )  > '' ) { // parallels update to database
						$this->old_wic_post_content = $wic_form_utilities->format_wic_post_content( $this->wic_post_content ) . $this->old_wic_post_content;
						$this->wic_post_content = ''; */
					}
				}					
			}
		} else {
			$this->guidance = '';
			$this->search_notices	=	sprintf ( __( 'Record not saved -- other records match the new combination of %s. View matches below.', 'wp-issues-crm' ), $this->create_dup_check_fields_list());
			$this->next_action 	=	'search';
			$show_list = true;
		}						
		
	}

	protected function update() {
		initialize_fields_post();
		$wic_query = $wpdb->get_results( prepare_search_sql ('dup') );
		$this->error_messages = $this->missing_fields . $this->error_messages;
		// next form action will be update iff any of three possibilities  . . .
			// submitted non-dup dupcheck values (OK to do update) OR							
		if ( 0 == $wpdb->num_rows || 
			// submitted dupcheck values not changed (OK to do update) OR					
			( 1 == $wpdb->num_rows && $wic_query[0]->ID == $this->fields['ID']->value ) ||
			// there are form errors (must correct and resubmit update)  
			$this->error_messages > '' ) { 
			// next action is update after an update whether or not successful (unless poss dup)						
			$this->next_action 	=	'update'; 
			if ( $this->error_messages > '' ) { 
				$this->guidance	=	__( 'Please correct form errors: ', 'wp-issues-crm' );	
			} else {
				$success = $wpdb->insert( $table, prepare_save_update_array(), array ( 'ID' = $this->fields['ID']->value ) ); 
				if ( ! $success )  { 
					$this->guidance = __( 'Please retry -- there were database errors. ', 'wp-issues-crm' );
					$this->error_messages = __( 'Unknown database error in save/update.', 'wp-issues-crm' );
				} else { 
					$this->guidance = __( 'Update successful -- you can further update this record.', 'wp-issues-crm' );								
				/* fix this	if ( trim( $next_form_output[ 'wic_post_content' ] ) > '' ) { // update to database
						$this->old_wic_post_content'] = $wic_form_utilities->format_wic_post_content( $this->wic_post_content'] ) . $this->old_wic_post_content'];
						$this->wic_post_content'] = ''; */
					}
				}					
			}
		// error if form values match a record other than the original record	
		} else { 
			$this->guidance = '';						
			$this->fields['ID']->value = 0; // reset so search does not bring back the original record
			$this->search_notices	=	sprintf ( __( 'Record not updated -- other records match the new combination of %s. View matches below.', 'wp-issues-crm' ), $this->->create_dup_check_fields_list());
			$this->next_action 	=	'search';
			$show_list = true;
		}						

	}

	protected function populate_fields( $query_result ) {
		foreach ( $this->fields as $field ) {
			$field->set_value ( $query_result->$field['name'] );		
		}
	}
	

	protected function prepare_search_sql( $mode ) {
	
		$join = '';
		$where = '';
		$values = array();
		
		foreach ( $this->fields as $field ) {
			if ( ( 'dup' == $mode && $field->dedup ) || 'new' == $mode )  {
				$search_clauses = $field->search_clauses();
				$join .= $search_clauses['join'];
				$where .= $search_clauses['where'];
				// each field will return an array of several values that need to be strung into main values array
				foreach ( $search_clauses['values'] as $value ) { 
					$values[] = $value;			
				}
			} 		
		}
		
		$sql = $wpdb->prepare( "
					SELECT 	* 
					FROM 		$table
					$join
					WHERE 1=1 $where
					ORDER BY $this->sort_order['orderby'] $this->sort_order['order']
					LIMIT 0, $this->max_records
					",
				$values );	
			
		return ( $sql );
	
	}

	protected function prepare_save_update_array() {
				
		$save_update_array = array();		
		
		foreach ( $this->fields as $field ) {
			$field_data_array = $field->data_array();
			// each field will return an array of several values that need to be strung into main values array
			foreach ( $field_data_array as $datum ) { 
				$save_update_array[] = $datum;			
			} 		
		}
		
		return ( $save_update_array );
	
	}


	/* supporting functions -- display form */	
	protected function display_form() {

		/* echo '<span style="color:green;"> <br /> $_POST:';  		
  		var_dump ($_POST);
  		echo '</span>';  

		 echo '<span style="color:red;"> <br />next_form_output:';  		
  		var_dump ($next_form_output);
  		echo '</span>';   
		/* */

		?><div id='wic-forms' class = "<?php echo $next_form_output['initial_form_state'] ?>">

		<form id = "wic-post-form" method="POST" autocomplete = "on">

			<div class = "wic-form-field-group wic-group-odd">
			
				<?php if ( 'update' == $next_form_output['next_action'] || $this->referring_parent > 0 ) {
					$form_header = ${ 'wic_' . $this->form_requested . '_definitions' }->title_callback( $next_form_output );
				} else {
					$form_header = $this->button_actions[$next_form_output['next_action']] ;
				}
				echo '<h2>' . esc_html( $form_header ) . '</h2>'; 
				
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
			   
				/* prepare first instance of buttons */	
				$button_row = ''; // temp variable to be repeated at bottom of form
				$button_args_main = array(
					'form_requested'			=> $this->form_requested,
					'action_requested'		=> $next_form_output['next_action'],
					'button_label'				=> $this->button_actions[$next_form_output['next_action']],
				);					
				$button_row = $wic_form_utilities->create_wic_form_button( $button_args_main );

				if ( 'search' == $next_form_output['next_action'] && $this->dups_ok ) { 
					$button_args_go_direct_to_save_new = array(
						'form_requested'			=> $this->form_requested,
						'action_requested'		=> 'save',
						'button_label'				=> sprintf ( __( 'Add New %1$s', 'wp-issues-crm' ), ${ 'wic_' . $this->form_requested . '_definitions' }->wic_post_type_labels['singular'] ),
						'button_class'				=> 'wic-form-button second-position',
						'new_form'					=> 'y',						
						);	
					$button_row .= $wic_form_utilities->create_wic_form_button( $button_args_go_direct_to_save_new );
				}

 				if ( 'update' == $next_form_output['next_action'] ) { // show button for new child type(s)
					foreach ( $this->child_types as $entity_type ) {
						global ${ 'wic_' . $entity_type . '_definitions' };
							$button_args_child_button = array(
								'form_requested'			=> $entity_type,
								'action_requested'		=> 'save',
								'id_requested'				=> 0,
								'referring_parent'		=>	$next_form_output['wic_post_id'], // always isset if doing update
								'button_label'				=> sprintf ( __( 'Add New %1$s', 'wp-issues-crm' ), ${ 'wic_' . $entity_type . '_definitions' }->wic_post_type_labels['singular'] ),
								'button_class'				=> 'wic-form-button second-position',
							);
						$button_row .= $wic_form_utilities->create_wic_form_button( $button_args_child_button );					
					}					
				}
				
 				if ( 'save' == $next_form_output['next_action'] & 0 == $this->referring_parent ) { 
 					// show this on save, but not update -- on update, have too much data in form, need to reset; if referring parent, no search to do 
					$button_args_search_again = array(
						'form_requested'			=> $this->form_requested,
						'action_requested'		=> 'search',
						'button_label'				=> 'Search Again',
						'button_class'				=> 'wic-form-button second-position'
					);					
					$button_row .= $wic_form_utilities->create_wic_form_button( $button_args_search_again );
				}

				if ( $this->parent_pointer_slug > '' ) {
					$button_args_parent_button = array(
						'form_requested'			=> $this->parent_type,
						'action_requested'		=> 'search',
						'id_requested'				=> $next_form_output[$this->parent_pointer_slug],
						'button_label'				=> sprintf( __( 'Back to %1$s', 'wp-issues-crm'), $this->parent_type ), 
						'button_class'				=> 'wic-form-button second-position'
					);					
					$button_row .= $wic_form_utilities->create_wic_form_button( $button_args_parent_button );
				
				}

				// output first instance of buttons
				echo $button_row;
			echo '</div>';   
		
		
			/* initialize field footnotes and footnote legends */
			$required_individual = '';
			$required_group = '';
			$contains = '';
			$required_group_legend = '';
			$required_individual_legend = ''; 								
			$contains_legend = false;



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

						}



									
							case 'multi_select':
								$args['placeholder'] 			= __( 'Select', 'wp-issues-crm' ) . ' ' . $field['label'];
								$select_parameter =  isset ( $field['select_parameter'] ) ?  $field['select_parameter'] : '' ;
								$args['select_array']			=	$wic_form_utilities->format_select_array ( $field['select_array'], 'control', $select_parameter );
								$args['field_label_suffix']	= $required_individual . $required_group;								
								echo $wic_form_utilities->create_multi_select_control ( $args ) ;
								break; 
								
							case 'parent':
								$args['hidden_flag'] = true;
								echo $wic_form_utilities->create_text_control ( $args ); 
								break;
														
						}
					} // close foreach field				
				echo '</div></div>';		   
		   } // close foreach group
		
		
			// notes div -- show only on update save -- do full text searching as a post field, since doesn't really pertain to notes only
			if ( 'search' != $next_form_output['next_action'] ) {
				$row_class = ( 0 == $group_count % 2 ) ? "wic-group-even" : "wic-group-odd"; 
				$group_count++;
				echo '<div class = "wic-form-field-group ' . $row_class . '" id = "wic-post-content">';
				
				$show_initial = ( "update" == $next_form_output['next_action'] ); // show notes on update next			
				$show_initial = in_array( 'wic_post_content', $next_form_output['initial_sections_open'] ) ? true : $show_initial; // also were searched or updated 
				
				$button_args = array (
					'class'			=> 'field-group-show-hide-button',		
					'name_base'		=> 'wic-inner-field-group-',
					'name_variable' => 'wic-post-content',
					'label' 			=> __('Notes (or Post Content) ', 'wp-issues-crm' ),
					'show_initial' =>  ( $show_initial ),
				);
				
				echo $wic_form_utilities->output_show_hide_toggle_button( $button_args );
				
				$show_class = $show_initial ? 'visible-template' : 'hidden-template';
							
				echo '<div id = "wic-inner-field-group-wic-post-content" class="' . $show_class .'">';	

					
				echo '</div></div>'; 
			} 
						
			// final button group div
			$row_class = ( 0 == $group_count % 2 ) ? "wic-group-even" : "wic-group-odd";
			echo '<div class = "wic-form-field-group ' . $row_class . '" id = "bottom-button-group">';?>
				<?php if ( 'update' == $next_form_output['next_action'] && ! isset ( $wic_base_definitions->wic_post_types[$this->form_requested]['dedicated_table'] )) { ?>
					<p><a href="<?php echo( home_url( '/' ) ) . 'wp-admin/post.php?post=' . absint( $next_form_output['wic_post_id'] ) . '&action=edit' ; ?>" class = "wic-back-end-link"><?php printf ( __('Direct edit %2$s # %1$s <br/>', 'wp_issues_crm'), absint( $next_form_output['wic_post_id'] ) , $this->form_requested  ); ?></a></p>
				<?php } ?>		
			
				<input type = "hidden" id = "wic_post_id" name = "wic_post_id" value ="<?php echo absint( $next_form_output['wic_post_id'] ) ; ?>" />					
		  		
				<?php // output second instance of buttons
				echo $button_row; ?>		 		
		
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






}