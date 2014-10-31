<?php
/*
* class-wic-form.php
*
*/

abstract class WIC_Form  {

	abstract protected function get_the_buttons();
	abstract protected function get_the_header();
	abstract protected function get_the_notice_level();
	abstract protected function get_the_notice();
	abstract protected function get_the_groups();
	abstract protected function get_the_controls();
	abstract protected function get_the_legends()
	
	protected $working_groups = array();
	protected $working_controls = array();
	
	public function __construct () {
		$this->working_groups = get_the_groups();
		$this->working_controls = get_the_controls();
		$this->layout_form();	
	}	
	
	
	public function layout_form () {
	
		emit_debugging_information();

		?><div id='wic-forms'>

		<form id = "wic-post-form" method="POST" autocomplete = "on">

			<div class = "wic-form-field-group wic-group-odd">
			
				<h2><?php echo esc_html( get_the_title() ) ?></h2> 
				
				<div id="post-form-message-box" class = "<?php echo get_the_notice_level(); ?>" ><?php echo esc_html( get_the_notice() ); ?></div>
			   
			   <?php $buttons = get_the_buttons(); 
			   		echo $buttons;	?>			   
			</div>   
		
			<?php
			
			$group_count = 0;
		   foreach ( $this->working_groups as $group ) {
						   	
				$filtered_controls = $this->select_key ( $this->working_controls, 'group', $group['name'] );
				$row_class = ( 0 == $group_count % 2 ) ? "wic-group-even" : "wic-group-odd";
				$group_count++;
				
				echo '<div class = "wic-form-field-group ' . $row_class . '" id = "wic-field-group-' . esc_attr( $group['name'] ) . '">';				
				
					$button_args = array (
						'class'			=> 'field-group-show-hide-button',		
						'name_base'		=> 'wic-inner-field-group-',
						'name_variable' => $group['name'],
						'label' 			=> $group['label'],
						'show_initial' => $group['section_open'],
					);
			
					echo $this->output_show_hide_toggle_button( $button_args );			
				
					$show_class = $group['section_open'] ? 'visible-template' : 'hidden-template';
					echo '<div class="' . $show_class . '" id = "wic-inner-field-group-' . esc_attr( $group['name'] ) . '">' .					
					'<p class = "wic-form-field-group-legend">' . esc_html ( $group['legend'] )  . '</p>';

					foreach ( $filtered_controls as $control ) {	
		   			echo $control['control']; 
					}																				
						
				echo '</div></div>';		   
		   } // close foreach group
		
		
			// notes div -- show only on update save -- do full text searching as a post field, since doesn't really pertain to notes only
		} 
						
			// final button group div
			$row_class = ( 0 == $group_count % 2 ) ? "wic-group-even" : "wic-group-odd";
			echo '<div class = "wic-form-field-group ' . $row_class . '" id = "bottom-button-group">';?>
				
				<?php	echo $buttons; ?>  // output second instance of buttons		 		
		
		 		<?php wp_nonce_field( 'wp_issues_crm_post', 'wp_issues_crm_post_form_nonce_field', true, true ); ?>
			   
				<?php echo get_the_legends ?>
				
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
		
	private function emit_debugging_information() {
		echo '<span style="color:green;"> <br /> $_POST:';  		
  		var_dump ($_POST);
  		echo '</span>';  

		 echo '<span style="color:red;"> <br />next_form_output:';  		
  		var_dump ($next_form_output);
  		echo '</span>';   
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

}