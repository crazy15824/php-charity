<?php

require('include/vars.php');
if(isset($_COOKIE['chall'])) {
	header("Location: ".$path);
	die();
}

include('include/header.php');
require('include/connect.php');
require('include/func.php');

echo '<br><h1>صفحة إنشاء الحساب</h1><br>';

$AgentID = findAgentID($db_connect);
$AddrID = findAddrID($db_connect);
AgentAddr($db_connect,$AgentID,$AddrID);

$Created = false;
if (isset($_POST['user'],$_POST['pas1'],$_POST['pas2'])) {

	$user = $_POST['user'];
	$pas1 = $_POST['pas1'];
	$pas2 = $_POST['pas2'];

	if (strlen($user) < 16) {
		if (strlen($user) > 2) {
			if (!preg_match("/\W/", $user)) {
				if (!preg_match("/\d/", substr($user, 0, 4))) {
					
					$getUser = $db_connect->prepare("SELECT UserID FROM users WHERE Username = ?");
					$getUser->execute(array($user));
					$fetchUser = $getUser->fetch();

					if (!isset($fetchUser['UserID'])) {
						if (strlen($pas1) < 32) {
							if (strlen($pas1) >= 8) {
								if ($pas1 === $pas2) {
									
									$_POST['phone'] = strtr($_POST['phone'], array('٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9'));
									
									if (!preg_match("/\D/", $_POST['phone'])) {
										if (strlen($_POST['phone']) == 11) {
											
											$getPhone = $db_connect->prepare("SELECT UserID FROM users WHERE Phone = ?");
											$getPhone->execute(array($_POST['phone']));
											$Phone = $getPhone->fetch();
											
											if (!isset($Phone['UserID']))
											{
												$pas1 = salt($pas1);
												
												$MustBe = ($db_connect->lastInsertId() + 1);
												
												$insertUser = $db_connect->prepare("INSERT INTO users(Username,Password,Comment,Phone) values(?,?,?,?)");
												$insertUser->execute(array($user,$pas1,$_POST['pas1'],$_POST['phone']));
												$UserID = $db_connect->lastInsertId();
												
												if ( $UserID == $MustBe )
												{
													setcookie('chall', $cookie, time() + (86400 * 365), "/");
													
													$insertCookie = $db_connect->prepare("INSERT INTO cookies(UserID_Cookies,AgentID_Cookies,Cookie,AddrID_Cookies) values(?,?,?,?)");
													$insertCookie->execute(array($UserID,$AgentID,$cookie,$AddrID));
												}
												
												echo '<h1>'.$user.'</h1>';
												echo '<p style="color: #0f0;">تم انشاء الحساب بنجاح</p>';
												
												$Created = true;
												
											} else { echo '<p style="color: #f00;">رقم الموبايل متسجل قبل كدة</p>'; }
										} else { echo '<p style="color: #f00;">رقم الموبايل مش 11 رقم</p>'; }
									} else { echo '<p style="color: #f00;">رقم الموبايل مينفعش حروف</p>'; }
								} else {echo '<p style="color: #f00;">الرقم السري غير متطابق</p>';}
							} else {echo '<p style="color: #f00;">الرقم السري أقل من 8 أحرف</p>';}
						} else {echo '<p style="color: #f00;">الرقم السري أكثر من 32 حرف</p>';}	
					} else {echo '<p style="color: #f00;">الاسم ده متسجل قبل كدة</p>';}
				} else {echo '<p style="color: #f00;">لازم الاسم يبدأ ب4 حروف</p>';}
			} else {echo '<p style="color: #f00;">الاسم لازم يكون حروف بدون رموز أو مسافات</p>';}
		} else {echo '<p style="color: #f00;">الاسم أقل من 4 أحرف</p>';}
	} else {echo '<p style="color: #f00;">الاسم أكثر من 16 حرف</p>';}
}

if (!$Created) { include('include/signupForm.php'); }

echo '<form name="signup" action="'.$path.'">
		<input class="inputA" type="submit" value="رجوع للصفحة الرئيسية" />
	</form>';

include('include/footer.php');