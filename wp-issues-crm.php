<?php
/**
 * Plugin Name: WP Issues CRM
 * Plugin URI: 
 cd * Description: Constituent Relationship Management for organizations that respond to constituents primarily on issues (e.g., legislators); does support basic case notes as well. 
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

include plugin_dir_path( __FILE__ ) . 'definitions.php';





/**
 * SEARCH FORM AND LIST
 * 
 * 
 *
 */

	function simple_wp_crm($atts) {
		
		extract( shortcode_atts( array(
		'count' => 100
	        ), $atts ) );

		/* first check capabilities -- must be user */
		if ( ! current_user_can ( 'activate_plugins' ) ) {
			echo '<h3>' . __( 'Sorry, this function is only accessible to administrators', 'simple-wp-crm' ) . '<h3>';
		} else {
			echo 'welcome -- you have a short code!';		
		}

		if($count == 0){$count = 100;}
/*	
		$widget_query = new WP_Query( array(
			'post_type'           => 'constituents',
			'post_status'         => array( bbp_get_public_status_id(), bbp_get_closed_status_id() ),
			'posts_per_page'      => $count,
			'show_stickies' => true,
			'no_found_rows'       => true,
                        'order'               => 'DESC'
                        
		) );

        ?>
        <?php ob_start(); ?>   <!-- start an output buffer -->
	<?php while ( $widget_query->have_posts() ) : $widget_query->the_post(); 
	endwhile; 
          ?>


		<!-- Reset the $post global -->
	<?php	wp_reset_postdata();
	return ob_get_clean(); */
	}

/**
* Enqueue Styles and Scripts for Datatables
*  datatables js -- www.datatables.net
*


function bbp_resp_setup_datatables() {
	
	// this style is necessary, contains misc styles common to tabs and to datatables
	wp_register_style(
		'bbp_resp_tab_style',  
		plugins_url( 'jquery-ui-1.10.4.custom.min.0741.css' , __FILE__ ) // customized style from http://jqueryui.com/themeroller/
	);
	wp_enqueue_style('bbp_resp_tab_style');  
	
	/* datables styles and scripts 
	
	wp_register_style(
		'jquery_dataTables_themeroller_style',  
	   	'http://cdnjs.cloudflare.com/ajax/libs/datatables/1.9.4/css/jquery.dataTables_themeroller.css',
	   	array('bbp_resp_tab_style')
	);
	wp_enqueue_style('jquery_dataTables_themeroller_style');
	
	wp_register_style(
		'bbp_resp_datatables_styles',  
	   	plugins_url( 'bbp_resp_datatables_styles.css' , __FILE__ ),
	   	array('jquery_dataTables_themeroller_style')
	);
	wp_enqueue_style('bbp_resp_datatables_styles');
	
	wp_register_script(
	  	'bbp_resp_datatables',
		'http://cdnjs.cloudflare.com/ajax/libs/datatables/1.9.4/jquery.dataTables.min.js', 
	   	array('jquery')
	 );
	wp_enqueue_script('bbp_resp_datatables');
	
	wp_register_script(
		'add_bbp_datatables',
	  	plugins_url( 'add_bbp_datatables.js' , __FILE__ ),  // invokes datatables from the plugin reply and topic tables
	  	array('bbp_resp_datatables')
	);
	wp_enqueue_script('add_bbp_datatables');

}

add_action('wp_enqueue_scripts', 'bbp_resp_setup_datatables');
/**
 * Register Shortcodes
 *
 */

add_shortcode('simple-wp-crm', 'simple_wp_crm');

http://wpengineer.com/2173/custom-fields-wordpress-user-profile/
function fb_add_custom_user_profile_fields( $user ) {
?>

		<tr>
			<th>
				<label for="address"><?php _e('Address', 'your_textdomain'); ?>
			</label></th>
			<td>
				<input type="text" name="address" id="address" value="<?php echo esc_attr( get_the_author_meta( 'address', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description"><?php _e('Please enter Monicas address.', 'your_textdomain'); ?></span>
			</td>
		</tr>
	
<?php }

function fb_save_custom_user_profile_fields( $user_id ) {
	
	if ( !current_user_can( 'edit_user', $user_id ) )
		return FALSE;
	
	update_usermeta( $user_id, 'address', $_POST['address'] );
}

//http://codex.wordpress.org/Plugin_API/Action_Reference/personal_options_update
add_action( 'profile_personal_options', 'fb_add_custom_user_profile_fields' );
add_action( 'personal_options', 'fb_add_custom_user_profile_fields' );

add_action( 'personal_options_update', 'fb_save_custom_user_profile_fields' );
add_action( 'edit_user_profile_update', 'fb_save_custom_user_profile_fields' );

// http://codex.wordpress.org/Plugin_API/Filter_Reference/user_contactmethods
function modify_user_contact_methods( $user_contact) {
   var_dump($user_contact);
	/* Add user contact methods */
	$user_contact['mailing_address'] = __('Mailing Address'); 
	$user_contact['city'] = __('City'); 
	$user_contact['zip'] = __('Zip');
	$user_contact['landline'] = __('Landline');
	$user_contact['mobile'] = __('Mobile');

	/* Remove user contact methods */
	unset($user_contact['aim']);
	unset($user_contact['jabber']);
	unset($user_contact['website']);
	unset($user_contact['yim']);

	return $user_contact;
}

add_filter('user_contactmethods', 'modify_user_contact_methods');

add_filter( 'user_search_columns', 'filter_function_name', 10, 3 );
// http://codex.wordpress.org/Plugin_API/Filter_Reference/user_search_columns (user search from wp_comment table columns)
function filter_function_name( $search_columns, $search, $this ) {
    $search_columns[] = 'user_url';
    return $search_columns;
}
// http://wpsnipp.com/index.php/functions-php/remove-admin-color-scheme-options-from-user-profile/

if(is_admin()){
  remove_action("admin_color_scheme_picker", "admin_color_scheme_picker");
} 
/*
function admin_color_scheme() {

   global $_wp_admin_css_colors;
   global $_wp_show_admin_bar;
   $_wp_admin_css_colors = 0;
   $_wp_show_admin_bar = 0;
}
add_action('admin_head', 'admin_color_scheme');
*/

if ( ! function_exists( 'cor_remove_personal_options' ) ) {
  /**
   * Removes the leftover 'Visual Editor', 'Keyboard Shortcuts' and 'Toolbar' options.
   */
  function cor_remove_personal_options( $subject ) {
    $subject = preg_replace( '#<h3>Personal Options</h3>.+?/table>#s', '', $subject, 1 );
    return $subject;
  }

  function cor_profile_subject_start() {
    ob_start( 'cor_remove_personal_options' );
  }

  function cor_profile_subject_end() {
    ob_end_flush();
  }
}
add_action( 'admin_head-profile.php', 'cor_profile_subject_start' );
add_action( 'admin_footer-profile.php', 'cor_profile_subject_end' );



<?php
/**
 * Plugin Name: Frontend Post No Spam
 * Plugin URI: http://twowayconstituentcommunication.com
 * Description: Allows better anonymous or logged in front end posting.
 * Version: 1.0
 * Author: Will Brownsberger
 * Author URI: http://willbrownsberger.com
 * License: GPL2
 */
/*  Copyright 2014  WILL BROWNSBERGER  (email : willbrownsberger@gmail.com)

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

// include Akismet class
		include plugin_dir_path( __FILE__ ) . 'class-responsive-tabs-akismet.php';


//  enqueue widget styles

function responsive_tabs_setup_clippings_styles() 
{
	wp_register_style(
		'new_post_styles',  
	   	plugins_url( 'front-end-post.css' , __FILE__ ) // ,
	);
	wp_enqueue_style('new_post_styles');

}
add_action( 'wp_enqueue_scripts', 'responsive_tabs_setup_clippings_styles');

// Register Custom Status -- does not work in admin panel to create new columns as advertised.
 function new_post_spam_register() {

	$args = array(
		'label'                     => _x( 'new_post_spam', 'Status General Name', 'text_domain' ),
		'label_count'               => _n_noop( 'Spam Post (%s)',  'Spam Posts (%s)', 'text_domain' ), 
		'public'                    => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'exclude_from_search'       => true,
	);
	register_post_status( 'new_post_spam', $args );

}
// Hook into the 'init' action
add_action( 'init', 'new_post_spam_register', 0 ); 

function register_new_post_widget() {
	register_widget ( 'Front_Page_New_Post' );
}
add_action( 'widgets_init', 'register_new_post_widget' );


class WP_Issues_CRM {

	function __construct() {
		
	}

	function widget( $args, $instance ) {
		
 		extract($args, EXTR_SKIP);
 		
/*
*
* code to save new posts on submission 
*
*/

$post_ID = 0;
$post_message = '';
$has_error = false;

/* do nothing unless submitted and passed nonce test */
if(isset($_POST['submitted']) && isset($_POST['twcc_front_page_new_post_nonce_field']) && 
		wp_verify_nonce($_POST['twcc_front_page_new_post_nonce_field'], 'twcc_front_page_new_post')) 
		{
			
      
   	$post_author = get_current_user_id() ? get_current_user_id() : $instance['guest_post_id'];	
		// var_dump($_POST);


		// for anonymous users, check current user id
		if (!get_current_user_id()) { 
				if(strlen(trim($_POST['post_guest_author'])) < 4) {
						$post_message .= '<li>Please enter a name with 4 or more characters.</li>';
						$has_error = true;
					}
						
					if(!filter_var(trim($_POST['post_guest_author_email']), FILTER_VALIDATE_EMAIL)) {
						$post_message .= '<li>Please enter a valid email.</li>';
						$has_error = true;
					} 
				}
		 
		// require some quantum of content to be entered    
		if(strlen(trim($_POST['post_title'])) < 6) {
			$post_message .= '<li>Please enter a title with 6 or more characters.</li>';
			$has_error = true;
		} 
		
		if(strlen(trim($_POST['twcc_new_post_content'])) < 50) {
			$post_message .= '<li>Post content must have at least 50 characters.</li>';
			$has_error = true;
		} 

      if($has_error == true) { 
          	$post_message = 'Your post has errors: <ul id= "new-post-error-list">'  . $post_message . '</ul>';
				$post_id = $_POST['post_id']; // if we are in this branch, form was submitted but had errors -- want to carry post_id whether 0 or already set
		}				
   	else {// if no errors, spam check and save or udpate post

					/* note, need to use generic akismet interface as Wordpress plugin class 
					does not offer functions serving non-comments compare bbpress which writes its own class using elements from 
					the main plugin and the class referenced here */ 
		   
		   $comment = array(
		        'author'    => trim($_POST['post_guest_author']),
		        'email'     => trim($_POST['post_guest_author_email']),
		        'body'      => trim($_POST['post_content']),
		        'permalink' => 'http://localhost/?frontpagetab=4'
		   );
		  
		  $akismet_api_key = apply_filters( 'akismet_get_api_key', defined('WPCOM_API_KEY') ? constant('WPCOM_API_KEY') : get_option('wordpress_api_key') );
		  
		  $akismet = new Akismet_TWCC('http://willbrownsberger.com', $akismet_api_key, $comment);
		 
		  if($akismet->errorsExist()) {
		      echo"Problem with Akismet spam detection.";
		  } else {
		      if($akismet->isSpam()) {
		         // $post_message .= '<li>Your post is activating our spam filters.</li>';
		         // $has_error = true;
		         $new_post_status = 'new_post_spam'; 
		      }
		  }
		 /* end akismet call */
      
	   		
		
		
		
			/* $all_of_it = $_POST['post_title']. $_POST['twcc_new_post_content'] . 
					$_POST['twcc_new_post_cat'] . $_POST['post_guest_author'] . 
						$_POST['post_guest_author_email'];
			$hash_crc32 = hash('crc32', $all_of_it);
			if(isset($_COOKIE[$hash_crc32]))
					echo 'you refreshed chump';
					else setcookie ($hash_crc32); */
			 /* possible TODO: provide protection against refresh dupe post.  This approach doesn't work because headers already sent */
			
			if (!$new_post_status=='new_post_spam')  $new_post_status = $instance['initial_post_status'];			
			
			$default_comment_status = get_option('default_comment_status');
			
			$post_information = array(
				'post_title' => stripslashes($_POST['post_title']),
				'post_content' => $_POST['twcc_new_post_content'],
				'post_type' => 'post',
				'post_status' => $new_post_status,
				'post_author' => $post_author,
				'post_category' => array($_POST['twcc_new_post_cat']),
				'comment_status' => $default_comment_status, //(take the default value)
				'ID' =>$_POST['post_id']
			);
		
			$post_id = wp_insert_post($post_information);
		
		   if (!get_current_user_id()) {
	
					if ( ! update_post_meta ($post_id, 'twcc_post_guest_author', $_POST['post_guest_author']) ) { 
					add_post_meta($post_id, 'twcc_post_guest_author', $_POST['post_guest_author'], true );
						}	
					if ( ! update_post_meta ($post_id, 'twcc_post_guest_author_email', $_POST['post_guest_author_email']) ) { 
					add_post_meta($post_id, 'twcc_post_guest_author_email', $_POST['post_guest_author_email'], true );
						}
			}; 
			
			if($post_id > 0)	{
			   if ($_POST['post_id']> 0) {	
			 		$post_message .= 'Your post, #' . $post_id . ', has been updated.'; 
			 	}
				else {
					$post_message .= 'Your new post has been submitted as post #'.$post_id . '.';
				}	
				$post_message .= '  You can edit below or ';
				if ($new_post_status =='pending' || $new_post_status == 'new_post_spam')
					{$post_message .= '<a href="/">return to home.</a> Your post is pending review.';}
				elseif ($new_post_status=='publish')
						{	$post_message .= '<a href="/?p='.$post_id.'"> view your finalized post.</a>';	}
			} // close $post_id > 0 i.e., message on successful insert or update
		} // close insert/updates conditional on passing error tests

} // close nonce tested updates



// output widget title
		
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		echo  $before_widget;
		if ( $title )	echo $before_title . $title . $after_title;
 		echo '<div id = "new-post-widget">';	
// output before text matter 		
 		echo $instance['before_text'] ;
// output form
?>
<?php if (current_user_can('edit_posts') || get_user_by('id',$instance['guest_post_id']) ) { 
  // only show form if user can edit posts or anonymous users can post to the guest ID;  
  // note that if logged in user cannot edit posts, but anonymous can, the logged in user will be allowed to post  
 
?><form action="" id="front-page-new-post-form" method="POST">
     
		<?php if ( $post_message != '' ) { 
					$message_class = $has_error ? 'twcc-warning' : 'twcc-update'; ?>
		    <div id="new-post-message-box" class="<?php echo $message_class; ?>"><?php echo $post_message; ?></div>
		    <?php } ?> 
 
 		<?php if(get_current_user_id() == 0) { ?>
    					<p><input type="text" name="post_guest_author" id="post_guest_author" value="<?php if ( isset( $_POST['post_guest_author'] ) ) echo $_POST['post_guest_author']; ?>"/>
						<label for="post_guest_author"><?php _e('Your Name', 'twcc_text_domain') ?></label></p>		       
			        <p><input type="text" name="post_guest_author_email" id="post_guest_author_email" value="<?php if ( isset( $_POST['post_guest_author_email'] ) ) echo $_POST['post_guest_author_email']; ?>"/>
						<label for="post_guest_author_email"><?php _e('Your Email (will not be published)', 'twcc_text_domain') ?></label></p>		
		<?php } ?>
 
      <p><input type="text" name="post_title" id="post_title" value="<?php if ( isset( $_POST['post_title'] ) ) echo stripslashes($_POST['post_title']); ?>"/>
    	<label for="post_title"><?php _e('Post Title', 'twcc_text_domain') ?></label></p>
      
       <textarea name="twcc_new_post_content" id="twcc_new_post_content" rows="15" cols="60" class="required"><?php if ( isset( $_POST['twcc_new_post_content'] ) ) { if ( function_exists( 'stripslashes' ) ) { echo stripslashes( $_POST['twcc_new_post_content'] ); } else { echo $_POST['twcc_new_post_content']; } } ?></textarea>
               <!-- post Category -->
		<br /> <br />       
       <?php if($instance['show_category_select']) { 	            
                        
             	$dropdown_cat_args = array(
             			'show_option_all'    => '',
							'show_option_none'   => '',
							'orderby'            => 'name', 
							'order'              => 'ASC',
							'show_count'         => 0,
							'hide_empty'         => 0, 
							'child_of'           => 0,
							'exclude'            => '',
							'echo'               => 1,
							'selected'           => (int)$_POST['twcc_new_post_cat'],
							'hierarchical'       => 1, 
							'name'               => 'twcc_new_post_cat',
							'id'                 => 'twcc_new_post_cat',
							'class'              => 'postform',
							'depth'              => 0,
							'tab_index'          => 0,
							'taxonomy'           => 'category',
							'hide_if_empty'      => false,
             	);              
             
              wp_dropdown_categories($dropdown_cat_args ); ?>
          		<label for="twcc_new_post_cat">Discussion Category</label>
	             <br/><br />
	   <?php } ?>
	   
		<?php wp_nonce_field('twcc_front_page_new_post', 'twcc_front_page_new_post_nonce_field'); ?>

		<input type="hidden" name="submitted" id="submitted" value="true" />

		<input type="hidden" name="post_id" value="<?php echo $post_id; ?>"> 

		<?php $add_post = ($post_id > 0) ? 'Update' : 'Save'; ?>
		<button id="new_post_submit" type="submit"><?php _e($add_post, 'twcc_text_domain') ?></button>
		<br />
</form>
<?php
 }// end output new post form
else {
		echo '<h3>New post form not configured for anonymous input, please login to post. </h3>';
		}

 		echo $instance['after_text'];
		echo '</div>';
		echo $after_widget ;
} // close widget output


/*
* update the widget itself
*/


	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['before_text'] = force_balance_tags($new_instance['before_text']);
		$instance['after_text'] = force_balance_tags($new_instance['after_text']);
		$instance['guest_post_id'] = absint($new_instance['guest_post_id']);
		$instance['initial_post_status'] = strip_tags($new_instance['initial_post_status']);
		$instance['show_category_select'] = absint($new_instance['show_category_select']);
		return $instance;
	}

/*
* form for update of new widget
*
*/

	function form( $instance ) {
		$title  = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$before_text = isset( $instance['before_text'] ) ?  $instance['before_text']  : '';
		$after_text = isset( $instance['after_text'] ) ?  $instance['after_text']  : '';
		$guest_post_id = isset( $instance['guest_post_id'] ) ?  $instance['guest_post_id']  : 0;
		$initial_post_status = isset( $instance['initial_post_status'] ) ?  $instance['initial_post_status']  : '';
		$show_category_select = isset( $instance['show_category_select'] ) ?  $instance['show_category_select']  : 0;
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'before_text' ); ?>"><?php _e( 'Text before form:<br />' ); ?></label>
		 <textarea type="text" cols="40" rows="5"  id="<?php echo $this->get_field_id( 'before_text' ); ?>"  name="<?php echo $this->get_field_name( 'before_text' ); ?>"><?php echo $before_text;?> </textarea>

		<p><label for="<?php echo $this->get_field_id( 'after_text' ); ?>"><?php _e( 'Text after form:<br />' ); ?></label>
		 <textarea type="text" cols="40" rows="5"  id="<?php echo $this->get_field_id( 'after_text' ); ?>"  name="<?php echo $this->get_field_name( 'after_text' ); ?>"><?php echo $after_text;?> </textarea>

		<p><label for="<?php echo $this->get_field_id( 'guest_post_id' ); ?>"><?php _e( 'User ID# for guest posts if to be permitted (adding a valid ID # here will permit them):' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'guest_post_id' ); ?>" name="<?php echo $this->get_field_name( 'guest_post_id' ); ?>" type="text" value="<?php echo $guest_post_id; ?>" size="8" /></p>

<?php
       $post_status_options = array(
		'0' => array(
			'value' =>	'pending',
			'label' =>      'Save new posts as pending '
		),
		'1' => array(
			'value' =>	'publish',
			'label' =>  	'Save new posts as published' 
		),
		
			);
	$selected = $initial_post_status; 	 
    ?><label for="initial_post_status">Choose post status for new posts<br /> </label>
		<select id="<?php echo $this->get_field_id( 'initial_post_status' ); ?>" name="<?php echo $this->get_field_name( 'initial_post_status' ); ?>">   	
<?php
	$p = '';
	$r = '';
	foreach (  $post_status_options as $option ) {
	    	$label = $option['label'];
		if ( $selected == $option['value'] ) // Make selected first in list
		     $p = "\n\t<option style=\"padding-right: 10px;\" selected='selected' value='" . esc_attr( $option['value'] ) . "'>$label</option>";
		else $r .= "\n\t<option style=\"padding-right: 10px;\" value='" . esc_attr( $option['value'] ) . "'>$label</option>";
               	}
 	echo $p . $r. '</select>';
   ?><p><label for="<?php echo $this->get_field_id( 'show_category_select' ); ?>"><?php _e( 'Allow user to assign category: ' ); ?></label><?php
   echo  '<input type="checkbox" id="'. $this->get_field_id('show_category_select')  .'" name="'. $this->get_field_name('show_category_select')  .'" value="1" ' . checked( '1',  $instance['show_category_select'] , false ) .'/></p>';
   
 
       
	}
}



