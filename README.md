wp-issues-crm -- a constituent services database designed for legislative offices and other organizations that respond to issues related communications
and manage moderately complex constituent service cases.

php file naming conventions:
	one file per class
	prefix class-wp-issues-crm-class-name.php
	class-name will WP_Issues_CRM_Class_Name
	php subdirectory is first segment of class name -- e.g. form in WP_Issues_CRM_Form_Constituent_Search
		for forms, name the entity, then the action



Basic Architecture.

(1) 	The system is invoked by a short code, [wp_issues_crm] being placed on any wordpress page.  
		That shortcode invokes a class that displays latest issues and buttons for issues and constituents (this could be put in an array).
(2)	Whatever the entity chose, the button routes to the main form which processes the request based on the entity class definitions.
		It invokes the following.
				-- initialize blank form (see form-utilities class for full documentation of the elements of the form )
				-- if other than 'new' as requested action, proceeds to process input
					-- handles case of child records based on existence of a parent pointer (either search for a single record or save new)
						-- is there a referring parent, indicating coming from the parent's form? 						
							+ if search, do post_id based search and populate form for update
							+ if save, just offer new save form
						-- if coming back from itself, it handles input
							+ sanitize_valid_input 
								-- does sanitize text and strip slashes 
								-- applies validation rules to repeater arrays and plain strings
								-- populates form fields 
							+ does update/save -- same logic
					-- handles general case where dup checking is required 
							+ sanitize_validate
							+ always search -- either to find records or to check dups or just to populate if coming from a list 
							+ populate form if coming from search and find unique
							+ update/save if that was the request and no dups, otherwise revert to search and show dups list

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
				for select arrays, may want also to use a list_call_back property which will translate the selected value in a list context
			+ email is a text field that gets email validation
			+ date is a text field that gets date formatting through very flexible php datetime object -- will be displayed as lo/hi range in search
			+ check is a checkbox field (true false)
			+ readonly is a type that is searchable but not updateable -- handled differently in form according to action
				-- not displayed on save
				-- showed as readonly on update
				-- showed as input on search
				must add element readonly_subtype to determine type of control -- may be text or select
			+ updateonly is roughly reverse of readonly -- shows only on update screens, not searchable
				-- not displayed on search
				-- displayed as updateable on update or save screen
				must add element updateonly_subtype to determine type of control -- may be text or select
			! three special types are supported by option arrays within the constituent class and functions in form_utilities class 
				-- these fields are itemized in the array serialized_field_types in the base_definitions class
					+ emails -- repeating group of emails with type (arrays in constituent class definition support this)
					+ phones -- repeating group of phone numbers with type and extension
					+ addresses -- repeating group of addresses with type (street address and city-state-postalcode)
			! parent is special type that defines a parent relationship with another entity type ( actitivity type ) 
				the value assigned to a parent element is the post_id of the parent
				must add element wic_parent_type as the name of the entity 
				THESE ELEMENTS DEFINE AND CONTROL THE PARENT-CHILD RELATIONSHIP
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
	  	
