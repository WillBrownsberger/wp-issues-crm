<?php
/**
*
* class-wic-admin-main.php
*/


class WIC_Admin_Settings {
	/* 
	*  This is just a router for admin pages.  All admin pages point to this call back.
	*
	*/

	// for wp admin settings (not the main fields and field options)
	private $plugin_options;

	// sets up WP settings interface	
	public function __construct() { // class instantiated in plugin main 
		add_action('admin_init', array ( $this, 'settings_setup') );
		$this->plugin_options = get_option( 'wp_issues_crm_plugin_options_array' );
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

       // Privacy Settings
      add_settings_section(
            'privacy_settings', // setting ID
            'Privacy Settings', // Title
            array( $this, 'privacy_settings_legend' ), // Callback
            'wp_issues_crm_settings_page' // page ID ( a group of settings sections)
        ); 

		// naming of the callback with array elements (in the callbacks) is what ties the option array together 		
      add_settings_field(
            'all_posts_private', // field id
            'Make "Private" the default', // field label
            array( $this, 'all_posts_private_callback' ), // field call back 
            'wp_issues_crm_settings_page', // page 
            'privacy_settings' // settings section within page
       ); 
			
      add_settings_field(
            'hide_private_posts', // field id
            'Always hide private posts.', // field label
            array( $this, 'hide_private_posts_callback' ), // field call back 
            'wp_issues_crm_settings_page', // page 
            'privacy_settings' // settings section within page
       ); 


		// Postal Interface Settings
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

	/*
	*
	* Privacy Callbacks
	*
	*/
	// section legend call back
	public function privacy_settings_legend() {
		_e('<p>The "Issues" created within WP Issues CRM are just Wordpress posts that are automatically created as private. 
		Public posts cannot be created, nor their content altered, in WP_Issues_CRM. (Public posts
		can, however, be searched for as issues and viewed through WP_Issues_CRM.  Additionally, one can change the title and categories of pubic posts through WP Issues CRM.)</p>
		<p>From time to time, you may prefer to use the main Wordpress post editor, which has more features, to create or edit private issues.  
		To minimize risk of accidentally publicizing private issues through the Wordpress post editor, check the box below to 
		make "private" the default setting for all Wordpress posts.  Either way, you an always override the default visibility 
		setting in the "Publish" metabox in the Wordpress post editor.</p>
		<p>Private issues and posts are not visible on the front end of your website except to administrators and possibly the post authors.  So, there is no risk of disclosing private issues/posts,
		but if they are cluttering the administrator view of the front end, you can exclude them from front end queries using the setting here.</p>', 'wp-issues-crm' );
	}

	// setting field call back	
	public function all_posts_private_callback() {
		printf( '<input type="checkbox" id="use_postal_address_interface" name="wp_issues_crm_plugin_options_array[all_posts_private]" value="%s" %s />',
            1, checked( '1', isset ( $this->plugin_options['all_posts_private'] ), false ) );
	}

	// setting field call back	
	public function hide_private_posts_callback() {
		printf( '<input type="checkbox" id="use_postal_address_interface" name="wp_issues_crm_plugin_options_array[hide_private_posts]" value="%s" %s />',
            1, checked( '1', isset( $this->plugin_options['hide_private_posts'] ), false ) );
	}	
	
	/*
	*
	* Postal Address Interface Callbacks
	*
	*/
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
            1, checked( '1', isset ( $this->plugin_options['use_postal_address_interface'] ), false ) );
	}

	// setting field call back
	public function user_name_for_postal_address_interface_callback() {
		printf( '<input type="text" id="user_name_for_postal_address_interface" name="wp_issues_crm_plugin_options_array[user_name_for_postal_address_interface]"
				value ="%s" />', $this->plugin_options['user_name_for_postal_address_interface'] );
	
	}

	// call back for the option array (used by options.php in handling the form on return)
	public function sanitize ( $input ) {
		$new_input = array();
		if( isset( $input['all_posts_private'] ) ) {
            $new_input['all_posts_private'] = absint( $input['all_posts_private'] );
      } 
  		if( isset( $input['hide_private_posts'] ) ) {
            $new_input['hide_private_posts'] = absint( $input['hide_private_posts'] );
      } 
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
	     	<h2><?php _e( 'WP Issues CRM Settings', 'wp-issues-crm' ); ?></h2>
			<?php settings_errors(); ?>
         <form method="post" action="options.php">
         <?php
				submit_button( __( 'Save All Settings', 'wp-issues-crm' ) );         	
         	// set up nonce-checking for the single field we have put in this option group
				settings_fields ( 'wp_issues_crm_plugin_options') ;   
				// display fields, with names (in their callback definitions) which are elements of the single option array
				do_settings_sections( 'wp_issues_crm_settings_page' );
            submit_button( __( 'Save All Settings', 'wp-issues-crm' ) ); 
          ?>
          </form>
          
      </div>
 		<?php
	}

}