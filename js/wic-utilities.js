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

function hideSelf( rowname ) {
	var row = document.getElementById ( rowname );
	rowClass =row.className; 
	var test = confirm ( "When you update, this row will be permanently deleted."  )
	if ( test == true ) {
		row.className = rowClass.replace( 'visible-templated-row', 'hidden-template' ) ;
	} else {
		document.getElementById( rowname + '[screen_deleted]' ).checked = false;	
	}
}


function moreFields( base ) {

	// counter always unique since gets incremented on add, but not decremented on delete
	var counter = document.getElementById( base + '-row-counter' ).innerHTML;
	counter++;
	document.getElementById( base + '-row-counter' ).innerHTML = counter;
	
	var newFields = document.getElementById( base + '[row-template]' ).cloneNode(true);
	
	/* set up row paragraph with  id and class */
	newFields.id = base + '[' + counter + ']' ;
	newFieldsClass = newFields.className; 
	newFields.className = newFieldsClass.replace('hidden-template', 'visible-templated-row') ;

	/* walk child nodes of template and insert current counter value as index*/
	replaceInDescendants ( newFields, 'row-template', counter, base);	

//	var insertHere = document.getElementById( base + '-row-counter' );
//	insertHere.parentNode.insertBefore( newFields, insertHere );

	var insertHere = document.getElementById( base + '[row-template]' );
	insertHere.parentNode.insertBefore( newFields, insertHere );
}

function replaceInDescendants ( template, oldValue, newValue, base  ) {
	var newField = template.childNodes;
	if ( newField.length > 0 ) {
		for ( var i = 0; i < newField.length; i++ ) {
			var theName = newField[i].name;
			if ( undefined != theName) {
				newField[i].name = theName.replace( oldValue, newValue );
			}
			var theID = newField[i].id;
			if ( undefined != theID)  {
				newField[i].id = theID.replace( oldValue, newValue );
			} 
			var theFor = newField[i].htmlFor;
			if ( undefined != theFor)  {
				newField[i].htmlFor = theFor.replace( oldValue, newValue );
			} 
			var theOnClick = newField[i].onclick;
			if ( undefined != theOnClick)  {
//				newClickVal = 'hideSelf(\'' + base + '-' + newValue + '\')' ;
				newClickVal = 'hideSelf(\'' + base + '[' + newValue + ']' + '\')' ;
				newField[i].setAttribute( "onClick", newClickVal );
			} 
			replaceInDescendants ( newField[i], oldValue, newValue, base )
		}
	}
}