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
	
	public $labels = array (
		'singular' => '', 
		'plural'	  => ''	
	);

	public $sort_order = array (
		'orderby' => '', // field_slug
		'order'	  => '' // ASC or DSC
	);	
	
	public $max_records = 100;

	/* see class-wic-fields for associative array keys in individual field definition arrays */
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

		$next_form_output['guidance']				=  'Enter just a little information and do a full text search.'; // note 4a
		$next_form_output['search_notices']		=	'';					// note 4c
		$next_form_output['next_action'] 		=	'search';			// note 5
		$next_form_output['strict_match']		=	false;				// note 6
		$next_form_output['initial_form_state']= 	'wic-form-open';  // note 7 
		$next_form_output['initial_sections_open'] = array();			// note 8
	}

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


	/* the major actions that can be requested of the object -- search, save, update */
	
	protected function new () {
		
	
	}	
	
	protected function search() {
		initialize_fields_from_post()
	} 
	
	protected function save() {
		initialize_fields_from_post()
	}

	protected function update() {
		initialize_fields_from_post()
	}


	/* supporting functions -- display form */	
	protected function display_form() {
	}

	protected function construct_search_sql() {
	
		$join = '';
		$where = '';
		$values = array();
		
		foreach ( $wic_table_fields as $field ) {
			$search_clauses = $field->search_clauses();
			$join .= $search_clauses['join'];
			$where .= $search_clauses['where'];
			// each field will return an array of several values that need to be strung into main values array
			foreach ( $search_clauses['values'] as $value ) { 
				$values[] = $value;			
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
	
	}

	protected function construct_save_sql() {
	// not written yet --- just interface
		$join = '';
		$set = '';
		$values = array();
		
		foreach ( $wic_table_fields as $field ) {
			$set_clauses = $field->set_clauses();
			$join .= $set_clauses['join'];
			$set .= $set_clauses['set'];
			// each field will return an array of several values that need to be strung into main values array
			foreach ( $set_clauses['values'] as $value ) { 
				$values[] = $value;			
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
	
	}




}