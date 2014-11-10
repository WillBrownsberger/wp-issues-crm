<?php
/*
*
*  class-wic-search-form.php
*
*/

class WIC_Form_Multivalue_Search extends WIC_Form_Parent  {
	
	protected function get_the_entity() {
		return ( $this->entity );	
	
	}
	
	protected function get_the_buttons(){}
	protected function get_the_header ( &$data_array ) {}
	protected function get_the_formatted_control ( $control ) {
		$args = array();
		return ( $control->search_control( $args ) ); 
	}
	protected function get_the_legends() {}
	protected $entity = '';
	
	public function __construct ( $entity ) {
		$this->entity = $entity; 
	}
	
		
	
	public function layout_form ( &$data_array, $message, $message_level ) {
		// $this->emit_debugging_information();
		?><div id='wic-multivalue-block'>
		<?php foreach ( $groups as $group ) {
			 '<div class = "wic-multivalue-field-subgroup" id = "wic-field-subgroup-' . esc_attr( $group->group_slug ) . '">';
				$group_fields = WIC_DB_Dictionary::get_fields_for_group ( $this->get_the_entity(), $group->group_slug );
				echo '<p>'; 
					$this->the_controls ( $group_fields, $data_array );
				echo '</p>';
				echo '</div></div>';
		} // close foreach group
		</div>
	<?php
	}
	protected function the_controls ( $fields, &$data_array ) {
		foreach ( $fields as $field ) {
		 echo $this->get_the_formatted_control ( $data_array[$field] ) . '</p>';
		}
	}
	
}