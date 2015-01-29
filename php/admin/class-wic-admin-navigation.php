<?php
/**
*
* class-wic-admin-main.php
*/


class WIC_Admin_Navigation {
	/* 
	*  This is just a router from the top WP menu level 
	*  (routing for buttons within WP_Issues_CRM happens in WIC_Admin_Dashboard and within fields/options pages)
	*
	*/

	// sets up menu
	public function __construct() { // class instantiated in plugin main 
		add_action( 'admin_menu', array ( $this, 'menu_setup' ) );
	}	

	// add menu links to wp admin
	public function menu_setup () {
		add_menu_page( 'WP Issues CRM', 'WP Issues CRM', 'manage_wic_constituents', 'wp-issues-crm-main', array ( $this, 'do_dashboard' ), 'dashicons-smiley' ); // omit icon for now and also position		
		add_submenu_page( 'wp-issues-crm-main', 'Options', 'Options', 'activate_plugins', 'wp-issues-crm-options', array ( $this, 'do_options' ) );
		add_submenu_page( 'wp-issues-crm-main', 'Fields', 'Fields', 'activate_plugins', 'wp-issues-crm-fields', array ( $this, 'do_fields' ) );
		$wic_admin_settings = new WIC_Admin_Settings; // need to run this in the setup phase -- too late to register if wait until know on page
		add_submenu_page( 'wp-issues-crm-main', 'WIC Settings', 'Settings', 'activate_plugins', 'wp-issues-crm-settings', array ( $wic_admin_settings, 'wp_issues_crm_settings' ) ); // omit icon for now and also position
		add_submenu_page( 'wp-issues-crm-main', 'WIC Statistics', 'Statistics', 'manage_wic_constituents', 'wp-issues-crm-statistics', array ( $this, 'do_statistics' ) );	
	}


	/*
	*
	* the following four functions, which invoke the main working classes of the plugin, are not activated until navigation to them is known
	*
	*/

	public function do_dashboard (){ 
		self::admin_check_security( 'manage_wic_constituents' );
		echo '<div class="wrap"><h2>' . __( 'WP Issues CRM', 'wp-issues-crm' ) . '</h2>';	
		$wic_admin_dashboard = new WIC_Admin_Dashboard;
		echo '<div>';
	}
	
	public function do_fields () {
		self::admin_check_security( 'activate_plugins' );
		echo '<div class="wrap"><h2>' . __( 'Customize Fields', 'wp-issues-crm' ) . '</h2>';
			$wic_admin_field = new WIC_Entity_Data_Dictionary; 
				// not to be confused with the data dictionary cache itself, this class is the editor of the dictionary
		echo '<div>';
	}
	
	public function do_options () {
		self::admin_check_security( 'activate_plugins' );
		echo '<div class="wrap"><h2>'  .__( 'Manage Option Groups', 'wp-issues-crm' ) . '</h2>';		
			$wic_admin_option = new WIC_Entity_Option_Group;
		echo '<div>';
	}
	
	public function do_statistics () {
		self::admin_check_security( 'manage_wic_constituents' );
	 	WIC_Admin_Statistics::generate_storage_statistics(); 
	}
		

	private static function admin_check_security ( $required_capability ) {
		// is user logged in as administrator; if not, return
		if ( ! current_user_can ( $required_capability ) ) { 
			echo '<h3>' . __( 'Sorry, these settings are only accessible to administrators.', 'wp-issues-crm' ) . '<h3>';
			return;
		} 	
		// is the logged in user purporting to submit a previous form; if so, have a nonce?
		// note: no update action taken by subordinate classes without a button, 
		//   so either no button so no action, or have a button and a nonce, or die
		if ( isset ( $_POST['wic_form_button'] ) ) {
			// check nonces and die if not OK			
			if ( isset($_POST['wp_issues_crm_post_form_nonce_field']) &&
				check_admin_referer( 'wp_issues_crm_post', 'wp_issues_crm_post_form_nonce_field')) { // if OK, do nothing
			} else { 
				var_dump ( $_POST );
				die  ( '<h3>' .__( 'Nonce checking failure in WIC_Admin_Main.', 'wp-issues-crm' ) . '</h3>' ); 
				}	
		}	
	}

}