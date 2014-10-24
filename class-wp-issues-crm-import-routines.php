<?php
  /* 
   *
   *
   *
   * import 
   *
   */
   
 class WP_Issues_CRM_Import_Routines {
 	
 	public function say_hello() {
		echo 'SAY HELLO'; 		
 	}
 	  
 	public  function import_boston() { // fields must include so formatted first_name, last_name, email
	   // NEEDS UPDATEING TO REFLECT ONLINE EXCLUSION
	   
		global $wic_constituent_definitions;
		global $wic_base_definitions;	
		global $wic_form_utilities;   
	   
	   $i=0;
	   $j=0;
	   $seconds = 5000;
		set_time_limit ( $seconds );
	   
	   global $wpdb;
	   $contacts = $wpdb->get_results( 'select * from boston_2sm_residents' );
	   foreach ($contacts as $contact ) {

		   if ($i>6) break;
		   $i++;
		   			
			$post_information = array(
				'post_title' => wp_strip_all_tags ( $contact->last_name . ', ' . $contact->first_name ),
				'post_type' => 'wic_constituent',
				'post_status' => 'private',
				'post_author' => 15,
				'ping_status' => 'closed',
			);
		
			$post_id = wp_insert_post($post_information);
			
			foreach ($wic_constituent_definitions->wic_post_fields as $field) {
				$value = '';	
				if ( ! in_array( $field['type'], $wic_base_definitions->serialized_field_types ) )	{ 	
					if ( isset ( $contact->$field['slug'] ) ) {
						$value = $contact->$field['slug'];	
						switch ($field['slug']){
							case 'gender': 
								$value = strtolower($value);
								break;
							case 'dob':
							case 'reg_date':
								$value = $value > '' ? $wic_form_utilities->validate_date($value) : '';
								break;
							case 'voter_status':
								$value = strtolower($value);
								$value = ( '' == $value ) ? 'x' : $value;
								break;	
							case 'party':
								$value = strtolower($value);	
								if ( $value > '' ) {
									if ( false === strpos ( 'druljgs', $value ) ) {
										$value = 'o'; 
									}
								}
								break;
							case 'zip':
								$value = '0' . $value;
								break;
						}
						if ($value > '' ) {
							$stored_record = add_post_meta ($post_id, $wic_base_definitions->wic_metakey . $field['slug'], $value );
							if ( $stored_record ) {
								$j++;						
							}
						}
					}
				} elseif ( 'addresses' == $field['type'] )  {
					/*	$address_array = array();
						if ( '' < $contact->zip ) { // requiring zip as component of address -- clean input first by adding zip where missing 
							$apt = ( $contact->apartment > '' ) ? ', Apt. ' . $contact->apartment : '';
							$address_array = array( 
								array(
							***	0, is this coming through OK?
									$contact->street_number . $contact->street_suffix . ' ' . $contact->street_name . $apt,
									$contact->city . ', MA  0' . $contact->zip,    						
								),
							);
							$string = serialize($address_array);
							$stored_record = add_post_meta ($post_id, $wic_base_definitions->wic_metakey . $field['slug'], $address_array );
							if ( $stored_record ) {
								$j++;						
							}
						} */
				} elseif ( 'phones' == $field['type'] )  {
					$phone_array = array();
					if ( '' < preg_replace( "/[^0-9]/", '', $contact->phone ) ) {
					  $phone_array = array(
					  		array (
								0,
								preg_replace( "/[^0-9]/", '', $contact->phone ),
								''					  
						  ),
					  );
						$stored_record = add_post_meta ($post_id, $wic_base_definitions->wic_metakey . $field['slug'], $phone_array );
						if ( $stored_record ) {
							$j++;						
						}
					}
				}
			} // close for each field

		} // close for each contact
 
		echo '<h1>' . $i . ' constituent records in total processed</h1>';
		echo '<h1>' . $j . ' meta records in total stored</h1>'; 
  
 	}  // close function  
 	  
   function import(){ // fields must include so formatted first_name, last_name, email
	   // NEEDS UPDATEING TO REFLECT ONLINE EXCLUSION
	   $i=0;
	   $j=0;
	   $seconds = 5000;
		set_time_limit ( $seconds );
	   
	   global $wpdb;
	   $contacts = $wpdb->get_results( 'select * from wp_swc_contacts' );
	   foreach ($contacts as $contact ) {
			if ( $i/1000 == floor($i/1000 ) ) {
				echo '<h3>' . $i . ' records processed</h3>';			
			}	   
		   $i++;
		   // if ($i>10) break;
		   			
			$post_information = array(
				'post_title' => wp_strip_all_tags ( $contact->last_name . ', ' . $contact->first_name ),
				'post_type' => 'wic_constituent',
				'post_status' => 'private',
				'post_author' => 15,
				'ping_status' => 'closed',
			);
		
			$post_id = wp_insert_post($post_information);
			
			foreach ($this->constituent_fields as $field) {			
				if ( isset ( $contact->$field['slug'] ) ) {
					if ( $contact->$field['slug'] > '' ) {
						$stored_record = add_post_meta ($post_id, $this->wic_metakey . $field['slug'], $contact->$field['slug'] );
						if ( $stored_record ) {
							$j++;						
						}
					}				
				} 
			}
		}
		echo '<h1>' . $i . ' constituent records in total processed</h1>';
		echo '<h1>' . $j . ' meta records in total stored</h1>';
	}


	public function run_phone_cleanup() {
			   $i=0;
	   $j=0;
	   $seconds = 5000;
		set_time_limit ( $seconds );
	   
	   global $wpdb;
	   $contacts = $wpdb->get_results( 'SELECT p.id as ID, max(m1.meta_value) as phone, max(m2.meta_value) as mobile from wp_posts p 
	   											left join wp_postmeta m1 on m1.post_id = p.ID and m1.meta_key = "wic_data_phone"
	   											left join wp_postmeta m2 on m2.post_ID = p.ID and m2.meta_key = "wic_data_mobile_phone"
	   											group by p.ID having max(m1.meta_value) is not null or max(m2.meta_value) is not null' );
	   foreach ($contacts as $contact ) {
			if ( $i/1000 == floor($i/1000 ) ) {
				echo '<h3>' . $i . ' records processed</h3>';			
			}	   
		   $i++;
		   // if ($i>10) break;
		   			
			
			$phone_array = array ();
			$index = 0;
				if ( preg_replace("/[^0-9]/", '', $contact->phone ) > '' )
					{ 
						$phone_array[$index] = array( '0' , preg_replace("/[^0-9]/", '', $contact->phone ), '' );
						$index++; 
					}
				if ( preg_replace("/[^0-9]/", '', $contact->mobile ) > '0' )
					{ 
						$phone_array[$index] = array( '0' , preg_replace("/[^0-9]/", '', $contact->mobile ), '' );
						$index++; 
					}			   
				
			/* var_dump($phone_array);
			echo '<br />'; */
			if ( $index > 0 ) {
				add_post_meta ($contact->ID, 'wic_data_phone_numbers', $phone_array );
			} 
		}

	}	
	
	public function run_email_cleanup() {
		$i=0;
		$seconds = 5000;
		set_time_limit ( $seconds );
	   
	   global $wpdb;
	   $contacts = $wpdb->get_results( 'SELECT p.id as ID, m1.meta_value as email from wp_posts p 
	   											inner join wp_postmeta m1 on m1.post_id = p.ID 
	   											where m1.meta_key = "wic_data_email"');
	   
	   foreach ($contacts as $contact ) {
		   $i++;
		   // if ($i>10) break;
		
			if ( $contact->email > '' ) {
		   			
				$email_array = array (
					array ( '0', $contact->email ),
					);

				add_post_meta ($contact->ID, 'wic_data_email_group', $email_array );
				
			} 
		}
		echo '<h3>' . $i . ' records processed</h3>';
	}
	
	public function run_address_cleanup() {
		$i=0;
		$seconds = 5000;
		set_time_limit ( $seconds );
	   
	   global $wpdb;
	   $contacts = $wpdb->get_results( 'SELECT p.id as ID, m1.meta_value as zip, m2.meta_value as street_address from wp_posts p 
	   											inner join wp_postmeta m1 on m1.post_id = p.ID
	   											inner join wp_postmeta m2 on m2.post_id = p.ID  
	   											where m1.meta_key = "wic_data_zip" and m2.meta_key = "wic_data_street_address"');
	   
	   foreach ($contacts as $contact ) {
		   $i++;
		   //if ($i>10) break;
		
			if ( $contact->zip > '' ) {
		   			
				$address_array = array (
					array ( '0', $contact->street_address, $contact->zip ),
					);

				add_post_meta ($contact->ID, 'wic_data_street_addresses', $address_array );
				
			} 
		}
		echo '<h3>' . $i . ' records processed</h3>';
	}
	
	public function delete_old_keys() {
			global $wpdb;
			$contacts = $wpdb->get_results( "delete from wp_postmeta where 
				meta_key = 'wic_data_zip' 
				or meta_key = 'wic_data_street_address' 
				or meta_key = 'wic_data_email' or
				meta_key = 'wic_data_phone' or 
				meta_key = 'wic_data_mobile_phone'" );
	} 
			
}

$wic_imports = new WP_Issues_CRM_Import_Routines;
