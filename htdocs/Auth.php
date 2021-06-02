<?php

include('include/header.php');

require('include/vars.php');
require('include/connect.php');
require('include/func.php');

$User = fetchUser($db_connect);

if (isset($User['UserID'])) {
	
	echo '<h1>'.$User['Username'].'</h1>';
	
	if (!$User['Auth']) {
		
		if (isset($_POST['auth']))
		{
			if (isPass($User['Username'],$_POST['auth'],$db_connect)) {
				if (strlen($_POST['auth']) < 32) {
					if (strlen($_POST['auth']) >= 8) {
						
						$sql = "UPDATE cookies SET Auth=? WHERE CookieID=?";
						$upCookie = $db_connect->prepare($sql);
						$upCookie->execute([1,$User['CookieID']]);
						
						header("Refresh:0");
						die();
					}
				}
			} else { echo '<p style="color: #f00;">كلمة السر غلط</p>'; }
			
		} else { echo '<br><p style="color: #f00;">اكتب كلمة السر لتفعيل صلاحيات تحويل الرصيد وتعديل الحساب</p>'; }
		
		include('include/authForm.php');
		
	} else { echo '<br><p style="color: #0f0;">أنت دلوقت معاك صلاحيات كاملة</p>'; }	
	
	echo'<form action="Org.php">
			<input class="inputA" type="submit" value=" الرجوع لصفحة الجمعية" />
		</form>';
		
	echo'<form action="'.$path.'">
			<input class="inputA" type="submit" value="الرجوع للصفحة الرئيسية" style="background: #4caf50" />
		</form>';
		
} else {
	header("Location: Login.php");
	die();
}

include('include/footer.php');