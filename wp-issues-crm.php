<?php
/**
 * Plugin Name: WP Issues CRM
 * Plugin URI: 
 * Description: Constituent Relationship Management for organizations that respond to constituents primarily on issues (e.g., legislators); does support basic case notes as well. 
 * Version: 1.0
 * Author: Will Brownsberger
 
 * License: GPL2
 *
 *  Copyright 2014  WILL BROWNSBERGER  (email : willbrownsberger@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

include plugin_dir_path( __FILE__ ) . 'class-wp-issues-crm-definitions.php'; // note that order of load may matter here -- want definitions constructed first to sort field arrays once for all
include plugin_dir_path( __FILE__ ) . 'class-wp-issues-crm-constituents.php';
include plugin_dir_path( __FILE__ ) . 'class-wp-issues-crm-constituents-list.php';


function wp_issue_crm_setup_styles() {

	wp_register_style(
		'wp-issues-crm-styles',
		plugins_url( 'wp-issues-crm.css' , __FILE__ ) // ,
		);
	wp_enqueue_style('wp-issues-crm-styles');

}

add_action( 'wp_enqueue_scripts', 'wp_issue_crm_setup_styles');

function wp_issue_crm_list_width_styles() {
	
	global $wic_definitions;
	$output = '<!-- width styles for wp-issues-crm constituent list --><style>';
	foreach ( $wic_definitions->constituent_fields as $field ) {
		if ( $field['list'] > 0 ) { 		
 			$output .= '.cl-' . $field['slug'] . '{ width:' . $field['list'] . '%;}'; 
 		}
	}
	$output .= '</style>';
	echo $output;
}
add_action( 'wp_head', 'wp_issue_crm_list_width_styles' );


function wic_utilities_script_setup() {
	if ( !is_admin() ) {
		wp_register_script(
			'wic-utilities',
			plugins_url( 'wic-utilities.js' , __FILE__ ) 
		);
		
	wp_enqueue_script('wic-utilities');
	}
}
add_action('wp_enqueue_scripts', 'wic_utilities_script_setup');


class WP_Issues_CRM {

	public function __construct() {
		add_shortcode( 'wp_issues_crm', array( $this, 'wp_issues_crm' ) );
	}
		
	public function wp_issues_crm() {

		// here declare as global all the  classes that implement forms
		global $wp_issues_crm_constituents;
		 
		// here run list of forms to buttons 
		echo '<form id = "top-level-form" method="POST" autocomplete = "on">';
		echo '<button class = "wic-form-button" type="submit" name = "wic_top_menu_button" value = "dashboard">' . __( 'Dashboard (not built yet)', 'wp-issues-crm' ) . '</button>';
		echo '<button class = "wic-form-button" type="submit" name = "wic_top_menu_button" value = "constituents">' . __( 'New Constituent Search', 'wp-issues-crm' ) . '</button>';
		echo '<button class = "wic-form-button" type="submit" name = "wic_top_menu_button" value = "issues">' . __( 'New Issue (not built yet)', 'wp-issues-crm' ) . '</button>';
		echo '</form>';		

		// list all button names for subsidiary forms and the top menu button		
		if ( // default is show dashboard
				! isset ( $_POST['wic_constituent_main_button'] ) &&
				! isset ( $_POST['wic_constituent_direct_button'] ) && 
				! isset ( $_POST['wic_top_menu_button'] )

				) 
		{
			$this->show_dashboard();
		} elseif ( isset ( $_POST['wic_top_menu_button'] ) ) { // act on buttons
			switch ( $_POST['wic_top_menu_button'] ) {
				case 'dashboard':
					$this->show_dashboard();
					break;
				case 'constituents':
					$wp_issues_crm_constituents = new WP_Issues_CRM_Constituents( 0 );
					break;
			}
		} else { // route second level buttons from classes	
			if ( isset ( $_POST['wic_constituent_main_button'] ) 
			 ||  isset ( $_POST['wic_constituent_direct_button'] ) )	{ 
				$wp_issues_crm_constituents = new WP_Issues_CRM_Constituents( 0 ); 
		 	}
		 	// invoke each subsidiary form's button names  
		} 
	}
	
	public function show_dashboard() {
		// $store_value = serialize($_POST, true);
		echo ' store_value :';
		$phones = $_POST['phone'];
		// var_dump($phones);
		$stringtest = serialize( $phones );
		// echo 'wtfwillyourock' . $stringtest;
		echo '<br /> -- this shit is cool' . strpos($stringtest, 'wtfxa');		
		echo '<div id = "dashboard-area" class = "constituent-field-group wic-group-odd">';
		echo '<h1>Dashboard under development</h1>' . 
		'<h2>"New constituent search" only option implemented so far on this screen. </h2>' .
		'<h2> The search option feeds through to constituent save and update functions, which are fully implemented.</h2>' . 			
		'<p id = "just a paragraph">Lots of other bullshit. <button id="destroy-button" onclick="destroyParentElement()" type="button">Destroy</button></p>' .  		
		'<button id="phone-button" onclick="addNewInputElement()" type="button">Add</button></p>' .'</div>' ;
?><div id="readroot" style="display: none">

	<input type="button" value="Remove review"
		onclick="this.parentNode.parentNode.removeChild(this.parentNode);" /><br /><br />

	<input name='phone[0][*]' type='text' value="" />
	<input name='phone[0][**]' type='text' value="" />
	<input name='phone[0][***]' type='text' value="" />

</div>

<form method="post" action="/test/">
	<span id="writeroot"></span>
	<input type="button" onclick="moreFields()" value="Give me more fields!" />
	<input type="submit" value="Send form" />

</form>
<?php

// http://www.quirksmode.org/dom/domform.html

	}
}

$wp_issues_crm = new WP_Issues_CRM;
