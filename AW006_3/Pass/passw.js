function check(form) { /*function to check userid & password*/
	/*the following code checkes whether the entered userid and password are matching*/
	if(form.userid.value == "aw006" && form.pswrd.value == "passwordchata") {
		window.open('adminOptions.php', '_self')/*opens the target page while Id & password matches*/
	}
	else {
		alert("Error Password or Username")/*displays error message*/
	}
}