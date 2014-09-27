<?php
/*
* post-type and taxonomy definitions for simple-wp-crm
* 
*/

// Register Custom Post Type
function custom_post_type() {

	$labels = array(
		'name'                => _x( 'Constituents', 'Post Type General Name', 'simple-wp-crm' ),
		'singular_name'       => _x( 'Constituent', 'Post Type Singular Name', 'simple-wp-crm' ),
		'menu_name'           => __( 'Constituents', 'simple-wp-crm' ),
		'parent_item_colon'   => __( 'Parent Constituent', 'simple-wp-crm' ),
		'all_items'           => __( 'All Constituents', 'simple-wp-crm' ),
		'view_item'           => __( 'View Constituent', 'simple-wp-crm' ),
		'add_new_item'        => __( 'Add New Constituent', 'simple-wp-crm' ),
		'add_new'             => __( 'Add New', 'simple-wp-crm' ),
		'edit_item'           => __( 'Edit Constituent', 'simple-wp-crm' ),
		'update_item'         => __( 'Update Constituent', 'simple-wp-crm' ),
		'search_items'        => __( 'Search Constituent', 'simple-wp-crm' ),
		'not_found'           => __( 'Not found', 'simple-wp-crm' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'simple-wp-crm' ),
	);
	$capabilities = array(
		'edit_post'           => 'activate_plugins',
		'read_post'           => 'activate_plugins',
		'delete_post'         => 'activate_plugins',
		'edit_posts'          => 'activate_plugins',
		'edit_others_posts'   => 'activate_plugins',
		'publish_posts'       => 'activate_plugins',
		'read_private_posts'  => 'activate_plugins',
	);
	$args = array(
		'label'               => __( 'constituent', 'simple-wp-crm' ),
		'description'         => __( 'constituents -- people', 'simple-wp-crm' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'author', 'thumbnail', 'revisions', 'custom-fields', ),
		// comments are not private; labels inappropriate
		// general editor doesn't make sense
		'taxonomies'          => array( 'category' ),
		'hierarchical'        => false,
		'public'              => false, // controls if view link appears in edit menu (but not whether URL is visible on front end)
		'show_ui'             => false, // exclusively through our front end
		'show_in_menu'        => false, // exclusively through our front end
		'show_in_nav_menus'   => false, // not something that one would navigate to
		'show_in_admin_bar'   => false, // assure that all navigation to constituents goes through the plugin
		'menu_position'       => 10,    // irrelevant
		'can_export'          => false, // control export through own security
		'has_archive'         => false, // no support in general themes
		'exclude_from_search' => true,  // don't want in queries
		'publicly_queryable'  => false, // controls if URL to constituent is accessible on front end (independent of log in status) -- false for privacy
		'capabilities'        => $capabilities,
	);
	register_post_type( 'constituent', $args );

}

// Hook into the 'init' action
add_action( 'init', 'custom_post_type', 0 );

?>