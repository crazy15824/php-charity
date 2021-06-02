<?php

require('include/vars.php');
require('include/connect.php');
require('include/func.php');

$User = fetchUser($db_connect,false);

if (isset($User['UserID']))
{
	$User['Credit'] *= ($User['Credit'] < 0) ? -1 : 1;
	
	$Bank = number_format($User['Bank']).' mg';
	$Gold = number_format($User['Gold']).' mg';
	$Orgn = number_format($User['Credit']).' mg';
	
	$myObj = array(
		'Bank' => $Bank,
		'Gold' => $Gold,
		'Orgn' => $Orgn);
	$myJSON = json_encode($myObj);
	
	echo $myJSON;
}