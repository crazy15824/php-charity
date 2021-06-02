<?php

include('include/header.php');
include('include/vars.php');

// Check if user logout
if (isset($_POST['out'])) {
	
	include('include/logoutForm.php');
	
	if (isset($_POST['yes'])) {
		setcookie('chall', null, -1, '/');
		header("Location: ".$path);
		die();
	}
	
	if (isset($_POST['no'])) {
		header("Location: ".$path);
		die();
	}
} else 
	header("Location: ".$path);

include('include/footer.php');