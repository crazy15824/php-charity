<?php

// $_SERVER['SERVER_NAME']

require('include/vars.php');
if(isset($_COOKIE['chall'])) {
	header("Location: ".$path);
	die();
}

include('include/header.php');
require('include/connect.php');
require('include/func.php');

echo '<br><h1>تسجيل الدخول</h1><br>';

$AgentID = findAgentID($db_connect);
$AddrID = findAddrID($db_connect);
AgentAddr($db_connect,$AgentID,$AddrID);

$LoginErr = false;

$getErr = $db_connect->prepare("SELECT LoginID,Counter,time FROM login_err WHERE AgentID_Err = ? AND AddrID_Err = ? ");
$getErr->execute(array($AgentID,$AddrID));
$Err = $getErr->fetch();

if (isset($Err['Counter']) && $Err['Counter'] >= 6 && time() < $Err['time'] + 30 )
{
	$LoginErr = true;
	echo '<p style="color: #f00;">لقد تجاوزت الحد المسموح به من المحاولات</p>';
	echo '<p style="color: #f00;">انتظر 30 ثانية</p>';
}

$user = "";

if ( !$LoginErr )
{
	if (isset($_POST['username'],$_POST['password'])) {

		$user = str_replace(' ', '', $_POST['username']);
		$pass = $_POST['password'];

		if (strlen($user) < 16) {
			if (strlen($user) > 3) {
				if (!preg_match("/\W/", $user)) {
					if (strlen($pass) < 32) {
						if (strlen($pass) >= 8) {
							
							$pass = salt($pass);
							
							$getUser = $db_connect->prepare("SELECT UserID,Username FROM users WHERE ( Username = ? OR Phone = ? ) AND Password = ? ");
							$getUser->execute(array($user,$user,$pass));
							$User = $getUser->fetch();
							
							if (isset($User['UserID'])) {
								
								setcookie('chall', $cookie, time() + (86400 * 365), "/");
								
								$getCookie = $db_connect->prepare("SELECT CookieID FROM cookies WHERE UserID_Cookies = ? AND AgentID_Cookies = ? ");
								$getCookie->execute(array($User['UserID'],$AgentID));
								$CookieID = $getCookie->fetch();
									
								if (isset($CookieID['0'])) {
									$sql = "UPDATE cookies SET Cookie=?, CookieDate=? WHERE CookieID=?";
									$upCookie = $db_connect->prepare($sql);
									$upCookie->execute([$cookie,$date,$CookieID['0']]);
								} else {
									$insertCookie = $db_connect->prepare("INSERT INTO cookies(UserID_Cookies,AgentID_Cookies,Cookie,AddrID_Cookies) values(?,?,?,?)");
									$insertCookie->execute(array($User['UserID'],$AgentID,$cookie,$AddrID));
								}
								
								header("Location: ".$path);
								die();
								
							} else {
								
								if(isset($Err['LoginID']) )
								{
									++$Err['Counter'];
									$Counter = ($Err['Counter'] >= 7) ? 0 : $Err['Counter'];
									
									$sql = "UPDATE login_err SET Username=?,Password=?,AgentID_Err=?,AddrID_Err=?,Counter=?,time=? WHERE LoginID=?";
									$upErr = $db_connect->prepare($sql);
									$upErr->execute([$_POST['username'],$_POST['password'],$AgentID,$AddrID,$Counter,time(),$Err['LoginID']]);
									
								} else {
									
									$insertErr = $db_connect->prepare("INSERT INTO login_err(Username,Password,AgentID_Err,AddrID_Err,time) values(?,?,?,?,?)");
									$insertErr->execute(array($_POST['username'],$_POST['password'],$AgentID,$AddrID,time()));
								}
								
								echo '<p style="color: #f00;">خطأ في الاسم أو كلمة السر</p>'; 
							}
						}
					}
				}
			}
		}
	}
}
include('include/loginForm.php');
include('include/footer.php');