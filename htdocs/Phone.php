<?php

include('include/header.php');

require('include/vars.php');
require('include/connect.php');
require('include/func.php');

$User = fetchUser($db_connect);

if (isset($User['UserID'])) {
	
	if ($User['Auth']) {
		
		echo '<h1>'.$User['Username'].'</h1>';
		echo (isset($User['Phone'])) ? '<h1>'.$User['Phone'].'</h1>' : '';
		
		if (isset($_POST['phone'])) {
			
			$_POST['phone'] = strtr($_POST['phone'], array('٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9'));
			
			if (!preg_match("/\D/", $_POST['phone'])) {
				if (strlen($_POST['phone']) == 11) {
					
					$getPhone = $db_connect->prepare("SELECT UserID FROM users WHERE Phone = ?");
					$getPhone->execute(array($_POST['phone']));
					$Phone = $getPhone->fetch();
					
					if (!isset($Phone['UserID'])) {
						
						$sql = "UPDATE users SET Phone=? WHERE UserID=?";
						$upCookie = $db_connect->prepare($sql);
						$upCookie->execute([$_POST['phone'],$User['UserID']]);
						
						header("Refresh:0");
						die();
						
					} else { echo '<p style="color: #f00;">رقم الموبايل متسجل قبل كدة</p>'; }
				} else { echo '<p style="color: #f00;">رقم الموبايل مش 11 رقم</p>'; }
			} else { echo '<p style="color: #f00;">رقم الموبايل مينفعش حروف</p>'; }
		}
		
		include('include/phoneForm.php');
		
		echo'<form action="'.$path.'">
			<input class="inputA" type="submit" value="الرجوع للصفحة الرئيسية" style="background: #4caf50" />
		</form>';
		
	} else { header("Location: Auth.php"); }
	
} else {
	header("Location: Login.php");
	die();
}

include('include/footer.php');