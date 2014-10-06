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
