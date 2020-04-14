function changebox() {
	
	// check testsend box if checked
	if(document.getElementById("testsend").checked == true) {
		
		// enable testsend button
		document.getElementById("testsend").disabled = false;
		
		// disable startsend button
		document.getElementById("startsend").disabled = true;
		
		// deactivate startsend button
		document.getElementById("startsend").checked = false;
		
	} else {
		
		// enable startsend button
		document.getElementById("startsend").disabled = false;
		
		// enable testsend button
		document.getElementById("testsend").disabled = false;
		
	}
	
}