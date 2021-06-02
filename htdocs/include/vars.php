<?php

$path = '/';
$URL = $_SERVER['SCRIPT_NAME'];
// echo $URL;
if (!($URL == $path               ||
      $URL == $path."index.php"   ||
      $URL == $path."Login.php"   ||
      $URL == $path."Signup.php"  ||
      $URL == $path."Logout.php"  ||
	  $URL == $path."Pass.php"    ||
	  $URL == $path."Phone.php"   ||
	  $URL == $path."Trans.php"   ||
	  $URL == $path."AdminIN.php" ||
	  $URL == $path."AdminOut.php"||
	  $URL == $path."Org.php"     ||
	  $URL == $path."OrgShare.php"||
	  $URL == $path."OrgGive.php" ||
	  $URL == $path."OrgTake.php" ||
	  $URL == $path."OrgPolic.php"||
	  $URL == $path."OrgBest.php" ||
	  $URL == $path."Profile.php" ||
	  $URL == $path."Auth.php"    ||
	  $URL == $path."Gold.php"    ||
	  $URL == $path."Log.php"     ||
	  $URL == $path."cp/index.php" ))
{
	header("Location: ".$path);
	die();
}


//Challen
$salt   = "GoldenAge";
$cookie = md5(time());
$cookie = md5(sha1($cookie.$salt));
$date   = date('y-m-d H:i:s', time());