<?php
/*
*
*	wic-entity-issue.php
*
*  This class is instantiated and takes control from the parent class in the parent constructor
*  It takes action on user requests which are the named functions.
*  It receives the $args passed from the button ( via the dashboard ).  
*	It is able to use generic functions from the parent.
*
*/

class WIC_Entity_Trend extends WIC_Entity_Parent {

	protected function set_entity_parms( $args ) { // 
		// accepts args to comply with abstract function definition, but as a top level entity does not process them -- no instance arg
		$this->entity = 'trend';
	} 

	// handle a request for a new search form
	protected function new_form() { 
		$this->new_form_generic( 'WIC_Form_Trend_Search' );
		return;
	}
	
	// handle a search request coming from a search form
	protected function form_search () { 
		$this->form_search_generic ( 'WIC_Form_Trend_Search_Again', 'WIC_Form_Trend_Search_Again');
		return;				
	}

	// handle search results
	protected function handle_search_results ( $wic_query, $not_found_form, $found_form ) {
		$sql = $wic_query->sql;
		if ( 0 == $wic_query->found_count ) {
			$message = __( 'No activity found matching search criteria.', 'wp-issues-crm' );
			$message_level =  'error';
			$form = new $not_found_form;
			$form->layout_form ( $this->data_object_array, $message, $message_level, $sql );			
		} else { 
			$message = sprintf( __( 'Issues with activity matching selection criteria -- found %s.' , 'wp-issues-crm' ), $wic_query->found_count );
			$message_level = 'guidance';	
			$form = new $found_form;
			$form->layout_form ( $this->data_object_array, $message, $message_level, $sql );
			$lister_class = 'WIC_List_Trend' ;
			$lister = new $lister_class;
			$list = $lister->format_entity_list( $wic_query, '' );
			echo $list;	
		}
	}

}