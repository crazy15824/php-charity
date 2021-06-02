<?php

include('include/header.php');

require('include/vars.php');
require('include/connect.php');
require('include/func.php');

$User = fetchUser($db_connect);

if (isset($User['UserID']))
{
	echo '<h1>'.$User['Username'].'</h1>';
	echo (isset($User['Phone'])) ? '<h1>'.$User['Phone'].'</h1>' : '';
	
	counterTable($User,true);
	
	echo'<form action="'.$path.'">
		<input class="inputA" type="submit" value="الرجوع للصفحة الرئيسية" />
	</form>';
	
	if (isset($_POST['Logs']))
	{
		if ($_POST['Logs'] == 'Wallet') 
		{
			printLog($User,$db_connect,1,0);
			
		} else if ($_POST['Logs'] == 'Credit') {
			
			printLog($User,$db_connect,1,1);
			
		} else if ($User['Admin'] && $User['Auth'] && $_POST['Logs'] == 'Admin')
			printLog($User,$db_connect,1,0,1);
		
	} else {
		printLog($User,$db_connect,1,0);
	}

} else { header("Location: Login.php"); }

include('include/footer.php');