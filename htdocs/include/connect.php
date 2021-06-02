<?php

require_once('vars.php');

$dsn 	= "mysql:host=localhost;dbname=gold";
$user 	= "root";
$pass 	= "";

$options = array(

		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
);

try{

	$db_connect = new PDO($dsn,$user,$pass,$options);
	$db_connect->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

}

catch(PDOException $e)
{
	echo "Connect Feild: ".$e->getMessage();
}