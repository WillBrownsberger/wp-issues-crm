<?php
/*
*
*  class-wic-search-form.php
*
*/


class WIC_Constituent_Search_Form extends WIC_Form  {

	global $wic_constituent;

	protected function get_the_buttons();
	protected function get_the_header();
	protected function get_the_notice_level();
	protected function get_the_notice();
	protected function get_the_groups();
	protected function get_the_controls();
	protected function get_the_legends()
	
	protected function get_the_controls() {
		$controls = $wic_constituent->prepare_html_form_search_controls();	
	}

	protected function get_the_groups() {
		$controls = $wic_constituent->field_groups;	
	}


	
	/* supporting functions -- display form */	
	protected function search_form() {

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

