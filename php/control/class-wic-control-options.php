<?php
/*
*
* class-wic-control-options.php
*
* every select field will have a method in this class to retrieve options for it
*  	the name of that function will be get_{field_slug}_options()
*
* the alternatives to this approach that were considered are:
*		put static options in the WP option array -- this may make sense later, but probably still will retain the class methods here
*			other options are from diverse functions and I will have to create those functions and put them someplace anyway
*		put these in the select control -- bad to be touching that often
*		create my own options table structure -- bad more database calls for slim returns
*		
*
*/
Class WIC_Control_Options {

 }

