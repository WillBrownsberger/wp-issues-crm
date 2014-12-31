<?php
/*
* class-wic-form.php
* creates a generic form layout for wic entities coupled to field group list
* entity classes may use different forms
*
*/

abstract class WIC_Form_Parent  {
	
	/* 
	*
	*	This class standardizes all the forms that accept input in wp-issues-crm.
	*
	*  One public function -- layout form
	*	Abstract protected functions for child forms to fill in form details 
	*
	*	Also includes two public static functions for button creation -- create_wic_form_button and back_button
	*
	*/


	// associate form with entity in data dictionary
	abstract protected function get_the_entity();
	// get the field groups for the entity from the data dictionary	
	protected function get_the_groups () {
		$groups = WIC_DB_Dictionary::get_form_field_groups( $this->get_the_entity() );
		return ($groups );
	}

	// define the top row of buttons (return a row of wic_form_button s)
	abstract protected function get_the_buttons();
	// define the form message (return a message)
	abstract protected function format_message ( &$data_array, $message );
	// coloring of message box
	protected $message_level_to_css_convert = array(
		'guidance' 	=> 'wic-form-routine-guidance',
		'error' 		=> 'wic-form-errors-found',	
		'good_news'	=> 'wic-form-good-news',
	);
	// chose search, save or update controls
	abstract protected function get_the_formatted_control( $control_args );
	// (return legends)
	abstract protected function get_the_legends( $sql = '' );
	// screen out some groups of fields (return true or false)
	abstract protected function group_screen( $group );
	// add any special groups of fields 
	abstract protected function group_special( $group );
	// add material before last row of buttons	
	abstract	protected function pre_button_messaging ( &$data_array );
	// add material ( e.g., list ) after the form (not presently implemented in any child form)
	abstract protected function post_form_hook ( &$data_array ); 


	/* 
	* layout_form -- main form function
	* 	references data_dictionary for grouping of variables, but gets all values from passed array of controls $data_array
	*  
	*	arguments expected:
	*		$data_array, formatted as field_slug => control object
	*		$message, to be appended to generic form header
	*		$message_level -- determines color of message box -- according to $message_level_to_css_convert property of the form
	*			default options set in this abstract class are: guidance, error, good_news
	*		$sql = search sql to display in legend area as info 
	*/
	public function layout_form ( &$data_array, $message, $message_level, $sql = '' ) {
		?><div id='wic-forms'>

		<form id = "<?php echo $this->get_the_form_id(); ?>" class="wic-post-form" method="POST" autocomplete = "on">

			<?php // child class must define message, possibly using the $message in calling parameters of layout form			
			$message = $this->format_message ( $data_array, $message );	?>
				
			<div id="post-form-message-box" class = "<?php echo $this->message_level_to_css_convert[$message_level]; ?>" ><?php echo esc_html( $message ); ?></div>
			
			<?php // child class must define buttons, without receiving the calling parameters of layout form  
		   $buttons = $this->get_the_buttons(); 
		   		echo $buttons;	?>			   
		
			<?php	// set up buffers for field output[.=] in two areas of the screen
			$main_groups = '';
			$sidebar_groups = '';
			$groups = $this->get_the_groups();
		   foreach ( $groups as $group ) {
		   	// set up buffer for all group content
				$group_output = '';
				// child class must define a group screen ( return true or false )
				if ( $this->group_screen( $group ) ) {				

					$group_output .= '<div class = "wic-form-field-group" id = "wic-field-group-' . esc_attr( $group->group_slug  ) . '">';				
					
						$button_args = array (
							'class'			=> 	'field-group-show-hide-button',		
							'name_base'		=> 	'wic-inner-field-group-',
							'name_variable' => 	$group->group_slug ,
							'label' 			=> 	$group->group_label ,
							'show_initial' => 	$group->initial_open,
						);
				
						$group_output .= $this->output_show_hide_toggle_button( $button_args );			
					
						$show_class = $group->initial_open ? 'visible-template' : 'hidden-template';
						$group_output .= '<div class="' . $show_class . '" id = "wic-inner-field-group-' . esc_attr( $group->group_slug ) . '">' .					
						'<p class = "wic-form-field-group-legend">' . esc_html ( $group->group_legend )  . '</p>';
						
						// here is the main content -- either   . . .
						if ( $this->group_special ( $group->group_slug ) ) { 				// if implemented returns true -- run special function 
							$special_function = 'group_special_' . $group->group_slug; 	// must define the special function too 
							$group_output .= $this->$special_function( $data_array );
						} else {	// standard main form logic 	
							$group_fields =  WIC_DB_Dictionary::get_fields_for_group ( $this->get_the_entity(), $group->group_slug );
							$group_output .= $this->the_controls ( $group_fields, $data_array );
						}
							
					$group_output .= '</div></div>';	
					
				} 
  				if ( $group->sidebar_location ) {
					$sidebar_groups .= $group_output;  				
  				} else {
  					$main_groups .= $group_output;
  				}
		   } // close foreach group
		
			echo 	'<div id="wic-form-body">' . '<div id="wic-form-main-groups">' . $main_groups . '</div>' .
					'<div id="wic-form-sidebar-groups">' . $sidebar_groups . '</div>' . '</div>';		
		
			// child class may insert material here
			$this->pre_button_messaging( $data_array );		
		
			// final button group div
			echo '<div class = "wic-form-field-group" id = "bottom-button-group">';?>
				<?php	echo $buttons; // output second instance of buttons ?>  		 		
		 		<?php wp_nonce_field( 'wp_issues_crm_post', 'wp_issues_crm_post_form_nonce_field', true, true ); ?>
				<?php echo $this->get_the_legends( $sql ); ?>
			</div>								
		</form>
		<?php // child class may insert messaging here 
		$this->post_form_hook( $data_array ); ?>
		</div>
		
		<?php 
		
	}

	// return form identity in css form
	protected function get_the_form_id() {
		return( strtolower( str_replace( '_', '-', get_class( $this ) ) ) ); 
	}

	// prepare controls allowing child form to define arguments selected for control ( save, update, search ) 
	protected function the_controls ( $fields, &$data_array ) {
		$controls_output = '';
		foreach ( $fields as $field ) {
			$controls_output .= '<div class = "wic-control" id = "wic-control-' . str_replace( '_', '-' , $field ) . '">' . $this->get_the_formatted_control ( $data_array[$field] ) . '</div>';
		}	
		return ( $controls_output );
	}

	/*
	*
	*	output show-hide-button ( field group headers are set up as buttons that show/hide field groups)
	*  calls togglePostFormSection in wic-utilities.js
	*
	*/
	protected function output_show_hide_toggle_button( $args ) {

		$class 			= 'field-group-show-hide-button';		
		$name_base 		= 'wic-inner-field-group-'  ;
		$name_variable = ''; // group['name']
		$label = ''; // $group['label']
		$show_initial = true;
		
		extract( $args, EXTR_OVERWRITE );

		$show_legend = $show_initial ? __( 'Hide', 'wp-issues-crm' ) : __( 'Show', 'wp-issues-crm' );

		
		$button =  '<button ' . 
		' class = "' . $class . '" ' .
		' id = "' . $name_base . esc_attr( $name_variable ) . '-toggle-button" ' .
		' type = "button" ' .
		' onclick="togglePostFormSection(\'' . $name_base . esc_attr( $name_variable ) . '\')" ' .
		' >' . esc_html ( $label ) . '<span class="show-hide-legend" id="' . $name_base . esc_attr( $name_variable ) .
		'-show-hide-legend">' . $show_legend . '</span>' . '</button>';

		return ($button);
	}


	/*
	*
	*	All buttons named wic_form_button are answered by the button handler in WIC_Dashboard_Main
	*	This function is the exclusive creator of submit buttons in the system.  
	*	It defines the interface to the button handler.  
	*
	*/
	static public function create_wic_form_button ( $control_array_plus_class ) { 
	
		$entity_requested			= '';
		$action_requested			= '';
		$id_requested				= 0 ;
		$button_class				= 'wic-form-button';
		$button_label				= '';
		$title						= '';
	

		extract ( $control_array_plus_class, EXTR_OVERWRITE );

		$button_value = $entity_requested . ',' . $action_requested  . ',' . $id_requested;
	
		$button =  '<button class = "' . $button_class . '" title = "' . $title . '" type="submit" name = "wic_form_button" value = "' . $button_value . '">' . $button_label . '</button>';
		return ( $button );
	}

	// just a back button 
	static public function backbutton ( $class ) {
		return ( '<button 
				class= "wic-form-button ' . $class . '" 
				type="button" 
				onclick = "history.go(-1); return true;">' .
					 __( 'Go Back', 'wp-issues-crm' ) . 
				'</button>'
				);
	}
}