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
