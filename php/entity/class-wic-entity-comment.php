<?php
/*
*
*	wic-entity-comment.php
*
*  Comment is a psuedo entity -- doesn't map directly to any database any entity and supports no updates or searching
*  Just a reporting link -- supports cross table queries
*
*/



class WIC_Entity_Comment extends WIC_Entity_Multivalue {

	public $entity;		// top level entity searched for or acted on ( e.g., constituents or issues )
	public $sql; 			// for search, the query executed;
	public $result = array(); 		// entity_object_array -- as saved, update or found( possibly multiple ) (each row as object with field_names properties)
	public $outcome; 		// true or false if error
	public $explanation; // reason for outcome
	public $found_count; // integer save/update/search # records found or acted on
	public $retrieve_limit;
	public $found_count_real;
	public $showing_count;

	protected function set_entity_parms( $args ) {
		$this->entity = 'comment';
	} 

	public static function create_comment_list ( &$doa ) {
		// this function spans multiple entities and levels and could be placed in multiple places
		// since it is a one off list -- there are no related update functions, viewing it as basically a presentation issue
		global $wpdb;
		$output = '';
		
		// grab emails for constituent in array				
		$known_emails_array = array();
		foreach ( $doa['email']->get_value() as $email_entity ) {
			( $email_entity->get_email_address() ); 
			$known_emails_array[] = $email_entity->get_email_address();		
		}
		if (  0 == count ( $known_emails_array ) ) {
			$output = '<h4>' . __( 'Cannot retrieve online activity without an email address', 'wp-issues-crm' ) . '</h4>';	
			return ( $output );	
		}

		// first check for twcc guest posts with that email 
		$meta_query_args = array ( 
			'relation' => 'AND',
			array (
				'key' => 'twcc_post_guest_author_email',
				'value' => $known_emails_array,
				'compare' => 'IN',			
			)
		);
		$args = array (
			'posts_per_page' =>-1,
			'ignore_sticky_posts' => true,
			'meta_query' => $meta_query_args
			);		

		$guest_post_list = new WP_Query ( $args );

		// hold that thought and check for comments
		$where_string = '';	
		foreach ( $known_emails_array as $email_address ) {
			$where_string .= ( '' == $where_string ) ? '%s' : ', %s';   		
		}

		$sql = $wpdb->prepare ( 
			"SELECT max( comment_ID ) as comment_ID, max( comment_date ) as comment_date, comment_post_ID 
			FROM wp_comments 
			WHERE comment_author_email IN ( $where_string ) 
			GROUP BY comment_post_ID
			ORDER BY max( comment_date ) DESC			
			", $known_emails_array ); 

		$result = $wpdb->get_results ( $sql );
		
		// if neither posts nor comments, quit 
		if ( 0 == count ( $result ) && ! $guest_post_list->have_posts() ) {
			$output = '<h4>' . __( 'No online activity found.', 'wp-issues-crm' ) . '</h4>';
			return ( $output );		
		}

		// otherwise run a consolidate list of whatever has come from both
		$output .= '<ul id = "constituent-comment-list">';

		// show posts first
		if ( $guest_post_list->have_posts() ) {
			while ( $guest_post_list->have_posts() ) {
				$guest_post_list->the_post();
					$output .= '<li class = "constituent-comment-row">' . 
						'<ul class = "constituent-comment-row" >' .
							'<li class = "constituent-comment-date"> ' . 
								date_i18n( 'Y-m-d', strtotime( $guest_post_list->post->post_date ) ) .', ' .
							'</li>' . 
							'<li class = "constituent-comment-issue">' . __( ' re ', 'wp-issues-crm' ) . 
								'<a href="' . esc_url( get_permalink( $guest_post_list->post->ID) ) .
									 '" title = "' . __('View Comment in Issue Context', 'wp-issues-crm' ) .'">' . 
										get_the_title( $guest_post_list->post->ID ) . 
								'</a>' .
							'</li>' .
							'<li class =  "comment-issue-link" >' . self::issue_link_button( $guest_post_list->post->ID  ) . '</li>' .					
						'</ul>' .
					'</li>';
			}
		}
		wp_reset_postdata();
		
		foreach ( $result as $comment ) {
			$output .= '<li class = "constituent-comment-row">' . 
				'<ul class = "constituent-comment-row" >' .
					'<li class = "constituent-comment-date"> ' . 
						date_i18n( 'Y-m-d', strtotime( $comment->comment_date ) ) . ', ' .
					'</li>' . 
					'<li class = "constituent-comment-issue">' . __( ' on ', 'wp-issues-crm' ) . 
						'<a href="' . esc_url( get_comment_link( $comment->comment_ID ) ) .
							 '" title = "' . __('View Comment in Issue Context', 'wp-issues-crm' ) .'">' . 
								get_the_title( $comment->comment_post_ID ) . 
						'</a>' .
					'</li>' .
					'<li class = "comment-issue-link" >' . self::issue_link_button( $comment->comment_post_ID ) . '</li>' .
				'</ul>' .
			'</li>';
		}
		$output .= '</ul>';

		return ( $output );						
	}

	private static function issue_link_button ( $id ) {
		$list_button_args = array(
			'entity_requested'	=> 'issue',
			'action_requested'	=> 'id_search',
			'button_class' 		=> 'wic-issue-link-button',
			'id_requested'			=> $id,
			'button_label' 		=> __( 'View Issue', 'wp-issues-crm' )				
			);			
		return ( WIC_Form_Parent::create_wic_form_button( $list_button_args ) );		
	}
	
	public function get_constituents_by_issue_id ( &$id_array ) {
		
		// designed to mimic return from a constituent search so it can be fed to constituent list

		global $wpdb;	
		
		// test elements of array, protect against remote risk of SQL injection 
		foreach ( $id_array as $id ) {
			if ( $id != absint ($id) ) {
				die ( __('Invalid elements in passed ID array -- WIC_Entity_Comment::get_constituents_by_id reporting', 'wp-issues-crm' ) );			
			}		
		}		
		
		$id_string = implode ( ',', $id_array );		
		// get constituents with activities
		
		$table =  $wpdb->prefix . 'wic_' . 'activity';
		$sql1 = 	"
			SELECT constituent_id 
			FROM $table
			WHERE	issue IN ( $id_string )
			GROUP BY constituent_id 
			";
		$first_array_of_constituent_ids = $wpdb->get_results ( $sql1 );
		 
	 	
		// now get constituents with comments
		$table =  $wpdb->prefix . 'wic_' . 'email';
		$sql2 = 	"		
			SELECT constituent_id  
			FROM wp_comments inner join $table email on comment_author_email = email_address
			WHERE comment_post_ID in ( $id_string )
			GROUP BY constituent_id
			";
		$second_array_of_constituent_ids = $wpdb->get_results ( $sql2 );
		
		// now pick up the authors of the posts
		$third_array_of_constituent_ids = array();

		// now create of the emails of the authors		
		$email_array = array();
		foreach ( $id_array as $id ) {
			$email = '';
			$guest_email = '';
			$regular_user_email = '';
			$guest_email = get_post_meta ( $id, 'twcc_post_guest_author_email', true );
			if ( '' == $guest_email )  {
				$sqltemp = "SELECT post_author from wp_posts where ID = $id ";
				$temp_result = $wpdb->get_results ( $sqltemp );
				$regular_user_email = get_the_author_meta ( 'user_email', $temp_result[0]->post_author ); 
			}
			if ( '' < $guest_email ) { 
				$email = $guest_email;
			} else {
				$email = $regular_user_email;			
			}
			if ( $email > '' ) {
				$email_array[] = $email;
			}				
		}
		
		// if author emails found, search for their constituent_id 
		$sql3 = '';
		if ( 0 < count ( $email_array ) ) {
			
			$where_string = '';
			foreach ( $email_array as $email_address ) {
				$where_string .= ( '' == $where_string ) ? '%s' : ', %s';   		
			}
			
			$table =  $wpdb->prefix . 'wic_' . 'email';
			$sql3 = $wpdb->prepare( "
				SELECT constituent_id 
				FROM $table
				WHERE email_address = %s
				GROUP BY constituent_id
				",
				$email_array );
					
			$third_array_of_constituent_ids = $wpdb->get_results( $sql3 );
		}  			

		// merge arrays of constituent IDs
		$simple_combined_array_of_ids = array();
		foreach ( $first_array_of_constituent_ids as $id ) {
			$simple_combined_array_of_ids[] = $id->constituent_id;		
		}		
		foreach ( $second_array_of_constituent_ids as $id ) {
			$simple_combined_array_of_ids[] = $id->constituent_id;		
		}
		foreach ( $third_array_of_constituent_ids as $id ) {
			$simple_combined_array_of_ids[] = $id->constituent_id;		
		}				
		
		$unique_ids = array_unique ( $simple_combined_array_of_ids );

		foreach ( $unique_ids as $id ) {
			$this->result[] = new WIC_Constituent_ID_Item ( $id );		
		}

		$this->found_count = count ( $unique_ids );
		$this->entity = 'constituent';
		$this->sql = '(1) ' . $sql1 . '; (2) ' . $sql2 . '; (3) ' . $sql3 . ';'; 
	} 
}

class WIC_Constituent_ID_Item {
	// this class just helps mimic the wpdb result return
	public $constituent_id;
	
	public function __construct ( $id ) {
		$this->ID = $id;	
	}

}