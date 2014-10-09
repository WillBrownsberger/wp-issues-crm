function toggleConstituentForm() {
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

function destroyParentElement(elementID) {
	
	var destroyButtonParent = document.getElementById( elementID ).parentNode;
	destroyButtonParent.parentNode.removeChild(destroyButtonParent); 
}

function addNewInputElement() {
	var addPhoneButton =	document.getElementById( 'phone-button' );
	var addPhoneButtonParent = document.getElementById( 'phone-button' ).parentNode;
	var para = document.createElement("p");
	var node = document.createTextNode("This is new.");
	para.appendChild(node);
	addPhoneButtonParent.insertBefore(para,addPhoneButton); 
}


function moreFields(base) {

	// counter always unique since gets incremented on add, but not decremented on delete
	var counter = document.getElementById(base + '-row-counter').innerHTML;
	counter++;
	document.getElementById(base + '-row-counter').innerHTML = counter;
	
	var newFields = document.getElementById(base + '-row-x').cloneNode(true);
	newFields.id = '';
	newFields.style.display = 'block';
	var newField = newFields.childNodes;
	for (var i=0;i<newField.length;i++) {
		newField[i].value = '';
		var theName = newField[i].name
		newField[i].name = theName.replace('[x]','[' + counter + ']');
	}
	var insertHere = document.getElementById( base + '-add-button' );
	insertHere.parentNode.insertBefore(newFields,insertHere);
}

window.onload = moreFields;
