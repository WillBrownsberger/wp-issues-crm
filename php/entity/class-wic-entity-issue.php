<?php
/*
*
*	wic-entity-activity.php
*
*/



class WIC_Entity_Activity extends WIC_Entity_Multivalue {


	protected function set_entity_parms( $args ) {
		extract ( $args );
		$this->entity = 'issue';
		$this->entity_instance = $instance;
	} 
/*
* options considered for output sanitization of post content-- need to be good here, since new notes are just appended to old
* with no filtering before this point ( on save/update take display value from prior form values (new appended to old), not database
* (1) esc_html not an option since shows html characters instead of using them format
* (2) sanitize_text_field strips tags entirely
* (3) apply_filters('the_content', -- ) does nothing to address stray quotes or unbalanced tags (and would run shortcodes, etc.)
* (4) wp_kses_post leaves tags unbalanced but handles stray quotes
* (5) balancetags (with force set to true) still gets hurt by stray quotes
* CONCLUSION COMBINE 4 AND 5 IF ECHOING BACK TO SCREEN WITHOUT ESCAPING, BUT NOTHING IF JUST SAVING OR COMING BACK THROUGH ESC_HTML
* NOTE: Wordpress does not bother to clean post_content up in this way (even through the admin interface) -- so conclude not necessary on save
* -- only do it here for display; assume properly escaped for storage although not clean display
*/

}