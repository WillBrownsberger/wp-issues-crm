<?php
/**
*
* class-wic-admin-main.php
*/


class WIC_Admin_Main {
	/* 
	*  This is just a router for admin pages.  All admin pages point to this call back.
	*
	*/

	// for wp admin settings (not the main fields and field options)
	private $plugin_options;

	// for creating headers and distributing requests to appropriate class in admin_main
	private static $page_header_class_array = array (
		'wp-issues-crm-options' => array( 'Manage WP-Issues-CRM Option Groups', 'WIC_Entity_Option_Group' ),
		'wp-issues-crm-fields'	=> array( 'Customize WP-Issues-CRM Fields', 'WIC_Entity_Field' ),
	);

	/*****************************************************	
	*
	* arrangements for wp admin settings page
	*
	******************************************************/

	// sets up WP settings interface	
	public function __construct() { // class instantiated in plugin main 
		add_action( 'admin_menu', array ( $this, 'menu_setup' ) );
		add_action('admin_init', array ( $this, 'settings_setup') );
		$this->plugin_options = get_option( 'wp_issues_crm_plugin_options_array' );
	}	

	// add menu links to wp admin
	public function menu_setup () {
	//	add_menu_page( 'WP Issues CRM', 'WP Issues CRM', 'activate_plugins', 'wp-issues-crm-main', array ( $this, 'wp_issues_crm_main' ) ); // omit icon for now and also position		
		add_menu_page( 'WIC Settings', 'WP Issues CRM', 'activate_plugins', 'wp-issues-crm-settings', array ( $this, 'wp_issues_crm_settings' ) ); // omit icon for now and also position
		add_submenu_page( 'wp-issues-crm-settings', 'Fields', 'Fields', 'activate_plugins', 'wp-issues-crm-fields', array ( $this, 'admin_main' ) );
		add_submenu_page( 'wp-issues-crm-settings', 'Options', 'Options', 'activate_plugins', 'wp-issues-crm-options', array ( $this, 'admin_main' ) );
	}

	public function wp_issues_crm_main (){
		global $wp_issues_crm;
		$wp_issues_crm->wp_issues_crm();
	}
	
	// define setting
	public function settings_setup() {
		
		// registering only one setting, which will be an array -- will set up nonces when called
		register_setting(
			'wp_issues_crm_plugin_options', // Option Group
			'wp_issues_crm_plugin_options_array', // Option Name
			array ( $this, 'sanitize' ) // Sanitize call back
		);
			
		// settings sections and fields dictate what is output when do_settings_sections is called passing the page ID
		// here 'page' is collection of settings, and can, but need not, equal a menu registered page (but needs to be invoked on one)	
      add_settings_section(
            'postal_address_interface', // setting ID
            'Postal Zip Code Lookup Settings', // Title
            array( $this, 'postal_address_interface_legend' ), // Callback
            'wp_issues_crm_settings_page' // page ID ( a group of settings sections)
        ); 

		// naming of the callback with array elements (in the callbacks) is what ties the option array together 		
      add_settings_field(
            'use_postal_address_interface', // field id
            'Enable USPS Web Interface', // field label
            array( $this, 'use_postal_address_interface_callback' ), // field call back 
            'wp_issues_crm_settings_page', // page 
            'postal_address_interface' // settings section within page
       ); 
			
      add_settings_field(
            'user_name_for_postal_address_interface', // field id
            'USPS Web Tools User Name', // field label
            array( $this, 'user_name_for_postal_address_interface_callback' ), // field call back 
            'wp_issues_crm_settings_page', // page 
            'postal_address_interface' // settings section within page
       ); 
	}
	
	// section legend call back
	public function postal_address_interface_legend() {
		_e('<p>WP Issues CRM includes an interface to the <a href="https://www.usps.com/business/web-tools-apis/address-information.htm">United States Postal Service Address Information API.</a>  
		This service will standardize and add zip codes to addresses entered for constituents.</p>  <p>To use it, you need to get a User Name from the USPS:</p>
		<ol><li>Register for USPS Web Tools by filling out <a href="https://registration.shippingapis.com/">an online form.</a></li>
			<li>After completing this form, you will receive an email from the USPS.  Forward that email back to 
			<a href="mailto:uspstechnicalsupport@mailps.custhelp.com">uspstechnicalsupport@mailps.custhelp.com</a> with the subject line "Web Tools API Access"
			and content simply asking for access.</li>
			<li>The USPS will reply seeking confirmation essentially that the access is not for bulk processing and will promptly grant you access.</li>
			<li>Once they have sent an email granting access to the API, enter Username that they give you below and enable the Interface.  Note that you do not need to
			enter the password that they give you.</li>.
		</ol>', 'wp-issues-crm' );
				
	}

	// setting field call back	
	public function use_postal_address_interface_callback() {
		printf( '<input type="checkbox" id="use_postal_address_interface" name="wp_issues_crm_plugin_options_array[use_postal_address_interface]" value="%s" %s />',
            1, checked( '1',  $this->plugin_options['use_postal_address_interface'], false ) );
	}

	// setting field call back
	public function user_name_for_postal_address_interface_callback() {
		printf( '<input type="text" id="user_name_for_postal_address_interface" name="wp_issues_crm_plugin_options_array[user_name_for_postal_address_interface]"
				value ="%s" />', $this->plugin_options['user_name_for_postal_address_interface'] );
	
	}

	// call back for the option array (used by options.php in handling the form on return)
	public function sanitize ( $input ) {
		$new_input = array();
		if( isset( $input['use_postal_address_interface'] ) ) {
            $new_input['use_postal_address_interface'] = absint( $input['use_postal_address_interface'] );
      } 
		if( isset( $input['user_name_for_postal_address_interface'] ) ) {
            $new_input['user_name_for_postal_address_interface'] = sanitize_text_field( $input['user_name_for_postal_address_interface'] );
      } 
      return ( $new_input );      
	}


	// menu page with form
	public function wp_issues_crm_settings () {
		?>
      <div class="wrap">
      	<?php screen_icon(); ?>
      	<h2>WP Issues CRM </h2>
			<?php settings_errors(); ?>
         <form method="post" action="options.php">
         <?php
         	// set up nonce-checking for the single field we have put in this option group
				settings_fields ( 'wp_issues_crm_plugin_options') ;   
				// display fields, with names (in their callback definitions) which are elements of the single option array
				do_settings_sections( 'wp_issues_crm_settings_page' );
            submit_button( __( 'Save Postal Interface Settings', 'wp-issues-crm' ) ); 
          ?>
          </form>
          
          <?php $this->generate_storage_statistics(); ?>
      </div>
 		<?php
	}

	/*
	*
	*	main administrative routing function 
	*
	*/

	public static function admin_main () {
		
		self::admin_check_security();

		echo '<div id="wrap"><h2>' . __( self::$page_header_class_array[$_GET['page']][0], 'wp-issues-crm' ) . '</h2>'; 
		if ( 'wp-issues-crm-settings' == $_GET['page'] ||  
			'wp-issues-crm-fields' == $_GET['page'] ) {
			$wic_admin_field = new WIC_Entity_Data_Dictionary;
		} elseif ( 'wp-issues-crm-options' == $_GET['page'] ) {
			$wic_admin_option = new WIC_Entity_Option_Group;			
		}	
		echo '<div>';
   }

	public static function admin_check_security () {
		// is user logged in as administrator; if not, return
		if ( ! current_user_can ( 'activate_plugins' ) ) { 
			echo '<h3>' . __( 'Sorry, these settings are only accessible to administrators.', 'wp-issues-crm' ) . '<h3>';
			return;
		} 	
		// is the logged in user purporting to submit a previous form; if so, have a nonce?
		// no update action taken by subordinate classes without a button
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
	
	// display function on settings page
	private static function generate_storage_statistics() {
		
		global $wpdb;

		$filter = $wpdb->prefix . 'wic_%';
		echo '<h3>' . __( 'Storage Statistics', 'wp-issues-crm' ) . '</h3>';
		$table = $wpdb->get_results (
			"
			SHOW TABLE STATUS like '$filter'		
			",
			ARRAY_A );
		
		echo '<table id="wp-issues-crm-stats"><tr>' .
					'<th class = "wic-statistic-text">' . __( 'Table Name', 'wp-issues-crm' ) . '</th>' .
					'<th class = "wic-statistic">' . __( 'Row Count', 'wp-issues-crm' ) . '</th>' .					
					'<th class = "wic-statistic">' . __( 'Data Storage', 'wp-issues-crm' ) . '</th>' .
					'<th class = "wic-statistic">' . __( 'Index Storage', 'wp-issues-crm' ) . '</th>' .
					'<th class = "wic-statistic">' . __( 'Total Storage', 'wp-issues-crm' ) . '</th>' .
					'<th class = "wic-statistic-text">' . __( 'Created', 'wp-issues-crm' ) . '</th>'	.	
					'<th class = "wic-statistic-text">' . __( 'Last Updated', 'wp-issues-crm' ) . '</th>'	.								
				'</tr>';
		
		$total_data_storage = 0;
		$total_index_storage = 0;
		
		foreach ( $table as $row ) { 
			echo '<tr>' .
			'<td class = "wic-statistic-table-name">' . $row['Name'] . '</td>' .
			'<td class = "wic-statistic" >' . $row['Rows'] . '</td>' .
			'<td class = "wic-statistic" >' . sprintf("%01.1f", $row['Data_length'] / 1024  )  . ' Kb' . '</td>' .
			'<td class = "wic-statistic" >' . sprintf("%01.1f",$row['Index_length'] / 1024 ) . ' Kb' . '</td>' .
			'<td class = "wic-statistic" >' . sprintf("%01.1f", ( $row['Index_length'] + $row['Data_length'] ) / 1024 )  . ' Kb' . '</td>' .
			'<td>' . $row['Create_time'] . '</td>' .	 	
			'<td>' . $row['Update_time'] . '</td>' .	
			'</tr>';
			$total_data_storage += $row['Data_length'];
			$total_index_storage += $row['Index_length'];

		} 
			echo '<tr>' .
			'<td class = "wic-statistic-table-name">' . __( 'Total for WP_Issues_CRM', 'wp-issues-crm') . '</td>' .
			'<td>' . '--'. '</td>' .
			'<td class = "wic-statistic" >' . sprintf("%01.1f", $total_data_storage / 1024  )  . ' Kb' . '</td>' .
			'<td class = "wic-statistic" >' . sprintf("%01.1f", $total_index_storage / 1024 ) . ' Kb' . '</td>' .
			'<td class = "wic-statistic" >' . sprintf("%01.1f", ( $total_data_storage + $total_index_storage ) / 1024 )  . ' Kb' . '</td>' .
			'<td>' . '--'. '</td>' .	 	
			'<td>' . '--' . '</td>' .	
			'</tr>';


		echo '</table>';
	}
	
}