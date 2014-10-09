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

function destroyParentElement() {
	alert('wtf?');
	var destroyButtonParent = document.getElementById( 'destroy-button' ).parentNode;
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

var counter = 0;

function moreFields() {
	counter++;
	var newFields = document.getElementById('readroot').cloneNode(true);
	newFields.id = '';
	newFields.style.display = 'block';
	var newField = newFields.childNodes;
	for (var i=0;i<newField.length;i++) {
		var theName = newField[i].name
		
		if (theName)
			newField[i].name = theName.replace('0',counter);
		//if ( undefined != theName ) {alert('index of [ in ' + newField[i].name + 'is' + newField[i].name.indexOf("["));}
	}
	var insertHere = document.getElementById('writeroot');
	insertHere.parentNode.insertBefore(newFields,insertHere);
}

window.onload = moreFields;
