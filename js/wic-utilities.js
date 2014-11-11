function togglePostForm() {
	var constituentForm = document.getElementById ( "wic-forms" );
	var display = constituentForm.style.display;
	var toggleButton	= document.getElementById ( "form-toggle-button" );
	var toggleButtonOnList = document.getElementById ( "form-toggle-button-on-list" );
	if ( "block" == display ) {
		constituentForm.style.display = "none";
		toggleButton.innerHTML = "Show Search";
		toggleButtonOnList.innerHTML = "Show Search";
	} else {
		constituentForm.style.display = "block";
		toggleButton.innerHTML = "Hide Search Form";
		toggleButtonOnList.innerHTML = "Hide Search Form";
	}
}

function togglePostFormSection( section ) {
	var constituentFormSection = document.getElementById ( section );
	var display = constituentFormSection.style.display;
	if ('' == display) {
		display = window.getComputedStyle(constituentFormSection, null).getPropertyValue('display');
	}
	var toggleButton	= document.getElementById ( section + "-show-hide-legend" );
	if ( "block" == display ) {
		constituentFormSection.style.display = "none";
		toggleButton.innerHTML = "Show";
	} else {
		constituentFormSection.style.display = "block";
		toggleButton.innerHTML = "Hide";
	}
}



function moreFields( base ) {

	// counter always unique since gets incremented on add, but not decremented on delete
	var counter = document.getElementById( base + '-row-counter' ).innerHTML;
	counter++;
	document.getElementById( base + '-row-counter' ).innerHTML = counter;
	
	var newFields = document.getElementById( base + '-row-template' ).cloneNode(true);

	/* set up row paragraph with  id and class */
	newFields.id = base + '-' + counter ;
	newFieldsClass = newFields.className; 
	newFields.className = newFieldsClass.replace('hidden-template', 'visible-templated-row') ;
	
	/* set up each field within row with indexed id, class and on-click (for destroy button) */
	var newField = newFields.childNodes;
	for (var i = 0; i < newField.length; i++ ) {
		if ( "BUTTON" != newField[i].tagName ) {
			var theName = newField[i].name;
			newField[i].name = theName.replace( 'row-template', counter );
			var theID = newField[i].id;
			newField[i].id = theID.replace( 'row-template', counter ); 
		} 
	}		
	
	var insertHere = document.getElementById( base + '-row-counter' );
	insertHere.parentNode.insertBefore( newFields, insertHere );
}