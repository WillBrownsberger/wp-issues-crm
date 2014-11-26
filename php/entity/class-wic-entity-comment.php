<?php
/*
*
*	wic-entity-comment.php
*
*  Comment is a psuedo entity -- doesn't map directly to any database any entity and supports no updates or searching
*  Just a reporting link
*
*/



class WIC_Entity_Comment extends WIC_Entity_Multivalue {


	protected function set_entity_parms( $args ) {
		extract ( $args );
		$this->entity = 'comment';
		$this->entity_instance = $instance;
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
			"SELECT comment_ID, comment_date, comment_post_ID 
			FROM wp_comments 
			WHERE comment_author_email IN ( $where_string ) 
			ORDER BY comment_date DESC			
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
							'<li class = "constituent-comment-issue">' . __( ' ', 'wp-issues-crm' ) . 
								'<a href="' . esc_url( get_permalink( $guest_post_list->post->ID) ) .
									 '" title = "' . __('View Comment in Issue Context', 'wp-issues-crm' ) .'">' . 
										get_the_title( $guest_post_list->post->ID ) . 
								'</a>' .
							'</li>' .
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
				'</ul>' .
			'</li>';
		}
		$output .= '</ul>';

		return ( $output );						
	}

}