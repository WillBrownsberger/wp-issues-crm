<?php
/*
* class-wic-db-field-list-object.php
*	just a convenience
*
*/

class WIC_DB_Field_List_Object {
	
	public $field_slug;
	public $field_type;
	public $field_label;
	public $listing_order;
	
	public function __construct ( $field_slug, $field_type, $field_label, $field_listing_order ) {
		$this->field_slug = $field_slug;
		$this->field_type = $field_type;
		$this->field_label = $field_label;
		$this->listing_order = $field_listing_order;	
	}

}