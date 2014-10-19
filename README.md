All entity/field specific logic appears in definitions classes

base-definitions identifies entities with post-types for each class -- constituent, activity, issue

classes ( labeled {entity}-definitions -- e.g., constituent-definitions ) include arrays with the name $wic_post_fields that define all post fields
used for that class.  These arrays have the following elemnts built into them.  
		
		-- dedup indicates whether field should be on list of fields tested for deduping (true/false)	  	
	  	-- group is just for form layout purposes (there is an array of groups)
	  	-- label is the front facing name 
	  	-- like indicates whether full text searching is enabled for the field (true/false)	  	
	  	-- list include in standard constituent lists -- if > 0 value is % width for display -- should sum to 100 to keep on one line.
	  	-- online is whether field should appear at all in online access (may or may not be updateable -- that is determined by type) -- true/false
	  	-- order is just for form layout purposes (there is only one order parameter and (trap for the unwary) it must be unique across all groups 
	  	    	duping an order value will hide a field -- order will sort within groups.  Use a numeric value, non-string value for sort consistency.	  	
	  	-- required may be false/blank, group or individual.  If group, at least one in group must be provided.
		-- slug is the no-spaces name of the field
	  	-- type determines what control is displayed for the field ( may be readonly ) and also validation/search save logic
			+ text -- plain vanilla
			+ select -- select field
				must add element select_array which be an array or within the definition class or a callback function within the form_utilities class
				must add element select_parameter, which will be passed to the callback function ( can be empty string, but must be defined )
			+ email is a text field that gets email validation
			+ date is a text field that gets date formatting through very flexible php datetime object -- will be displayed as lo/hi range in search
			+ check is a checkbox field (true false)
			+ readonly is a type that is searchable but not updateable -- handled differently in form according to action
				-- not displayed on save
				-- showed as readonly on update
				-- showed as input on search
				must add element readonly_subtype to determine type of control -- may be text or select
			! three special types are supported by option arrays within the constituent class and functions in form_utilities class 
				-- these fields are itemized in the array serialized_field_types in the base_definitions class
					+ emails -- repeating group of emails with type (arrays in constituent class definition support this)
					+ phones -- repeating group of phone numbers with type and extension
					+ addresses -- repeating group of addresses with type (street address and city-state-postalcode)
			! parent is special type that defines a parent relationship with another entity type ( actitivity type ) 
				the value assigned to a parent element is the post_id of the parent
				must add element wic_parent_type as the name of the entity
		--	wp_query_parameter may be empty or may be among the following wordpress query parameters
			all allowed values are chosen to support wp_query string of form wp_query_parameter=x where x is string or numeric depending on parameter
			see http://codex.wordpress.org/Class_Reference/WP_Query in case of confusion
			+ author ( author id )
			+ cat ( cat ID, includes children of category )
			+ tag ( tag slug )
			+ date
			+ s (generates full text search of all post content title and meta fields )
			+ post_status
			+ [taxonomy] ( not implemented for a later version)
			+++  if empty or not defined, the field is handled as a meta field

				
the definition classes for each entity also include field groups for diplay purposes -- array is named $wic_post_field_groups
		-- name is a slug for the group
		-- label shows as title for the gorup
		-- legend is fine print below the title
		-- order is order of the groups for display (must be unique to the group)
			note that the field order (in the field array) must be unique across all groups, but will be sorted within group
		-- initial open determines the drop down state on first form display (true/false), 
			will be overriden to true on second displays if group fields are accessed 
						
NO FIELD SPECIFIC SWITCHES APPEAR OUTSIDE THESE DEFINITION ARRAYS (EXCEPT, AS NOTED, FOR THE REPEATING GROUPS )
	  	
