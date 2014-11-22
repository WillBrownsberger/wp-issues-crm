<?php
/*
* class-wic-form.php
* creates a generic form layout for wic entities coupled to field group list
* entity classes may use different forms
*
*/

abstract class WIC_Form_Parent  {

	protected $message_level_to_css_convert = array(
		'guidance' 	=> 'wic-form-routine-guidance',
		'notice' 	=> 'wic-form-search-notices',
		'error' 		=> 'wic-form-errors-found',	
		'good_news'	=> 'wic-form-good-news',
	);

	abstract protected function get_the_entity();
	abstract protected function get_the_buttons();
	abstract protected function get_the_header( &$data_array );
	abstract protected function get_the_formatted_control( $control_args );
	abstract protected function get_the_legends( $sql = '' );
	
	protected function get_the_groups () {
		$groups = WIC_DB_Dictionary::get_form_field_groups( $this->get_the_entity() );
		return ($groups );
	}

	public function layout_form ( &$data_array, $message, $message_level, $sql = '' ) {

		// $this->emit_debugging_information();

		?><div id='wic-forms'>

		<form id = "<?php echo $this->get_the_form_id(); ?>" class="wic-post-form" method="POST" autocomplete = "on">

			<div class = "wic-form-field-group wic-group-odd">
			
				<h2><?php echo esc_html( $this->get_the_header( $data_array ) ) ?></h2> 
				
				<div id="post-form-message-box" class = "<?php echo $this->message_level_to_css_convert[$message_level]; ?>" ><?php echo esc_html( $message ); ?></div>
			   
			   <?php $buttons = $this->get_the_buttons(); 
			   		echo $buttons;	?>			   
			</div>   
		
			<?php
			
			$group_count = 0;
			$groups = $this->get_the_groups();
		   foreach ( $groups as $group ) {
				
				if ( $this->group_screen( $group ) ) {				
						   	
					$row_class = ( 0 == $group_count % 2 ) ? "wic-group-even" : "wic-group-odd";
					$group_count++;
					
					echo '<div class = "wic-form-field-group ' . $row_class . '" id = "wic-field-group-' . esc_attr( $group->group_slug  ) . '">';				
					
						$button_args = array (
							'class'			=> 	'field-group-show-hide-button',		
							'name_base'		=> 	'wic-inner-field-group-',
							'name_variable' => 	$group->group_slug ,
							'label' 			=> 	$group->group_label ,
							'show_initial' => 	$group->initial_open,
						);
				
						echo $this->output_show_hide_toggle_button( $button_args );			
					
						$show_class = $group->initial_open ? 'visible-template' : 'hidden-template';
						echo '<div class="' . $show_class . '" id = "wic-inner-field-group-' . esc_attr( $group->group_slug ) . '">' .					
						'<p class = "wic-form-field-group-legend">' . esc_html ( $group->group_legend )  . '</p>';
	
						$group_fields =  WIC_DB_Dictionary::get_fields_for_group ( $this->get_the_entity(), $group->group_slug );
						$this->the_controls ( $group_fields, $data_array );
							
					echo '</div></div>';	
					
				}	   
		   } // close foreach group
		
			// final button group div
			$row_class = ( 0 == $group_count % 2 ) ? "wic-group-even" : "wic-group-odd";
			echo '<div class = "wic-form-field-group ' . $row_class . '" id = "bottom-button-group">';?>
				<?php	echo $buttons; // output second instance of buttons ?>  		 		
		 		<?php wp_nonce_field( 'wp_issues_crm_post', 'wp_issues_crm_post_form_nonce_field', true, true ); ?>
				<?php echo $this->get_the_legends( $sql ); ?>
			</div>								
		</form>
		</div>
		
		<?php 
		
	}

	protected function get_the_form_id() {
		return( strtolower( str_replace( '_', '-', get_class( $this ) ) ) ); 
	}

	protected function the_controls ( $fields, &$data_array ) {
		foreach ( $fields as $field ) { 
			echo '<div class = "wic-control">' . $this->get_the_formatted_control ( $data_array[$field] ) . '</div>';
		}	

	}

	protected function emit_debugging_information() {
		echo '<span style="color:green;"> <br /> $_POST:';  		
			var_dump ($_POST);
  		echo '</span>';  

	}	
	
	protected function group_screen( $group ) { // allows child forms to screen out groups
		return ( true );	
	}

	/*
	*
	*	output show-hide-button
	*  calls togglePostFormSection in wic-utilities.js
	*
	*/
	public function output_show_hide_toggle_button( $args ) {

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

	static public function create_wic_form_button ( $control_array_plus_class ) { 
	
		$entity_requested			= '';
		$action_requested			= '';
		$id_requested				= 0 ;
		$referring_parent			= 0 ;
		$new_form 					= 'n'; // go straight to a save
		$button_class				= 'wic-form-button';
		$button_label				= '';
	

		extract ( $control_array_plus_class, EXTR_OVERWRITE );

		$button_value = $entity_requested . ',' . $action_requested  . ',' . $id_requested  . ',' . $referring_parent . ',' . $new_form;
	
		$button =  '<button class = "' . $button_class . '" type="submit" name = "wic_form_button" value = "' . $button_value . '">' . $button_label . '</button>';
		return ( $button );
	}


}