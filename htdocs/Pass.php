<?php

include('include/header.php');

require('include/vars.php');
require('include/connect.php');
require('include/func.php');

$User = fetchUser($db_connect);

if (isset($User['UserID'])) {
	echo '<h1>'.$User['Username'].'</h1>';
	echo (isset($User['Phone'])) ? '<h1>'.$User['Phone'].'</h1>' : '';
	
	if (isset($_POST['OldPass']) &&
		isset($_POST['NewPass1']) &&
		isset($_POST['NewPass2']) &&
		strlen($_POST['NewPass1']) < 32 &&
		strlen($_POST['NewPass2']) < 32 &&
		strlen($_POST['NewPass1']) >= 8 &&
		strlen($_POST['NewPass2']) >= 8 )
	{
		if (isPass($User['Username'],$_POST['OldPass'],$db_connect))
		{
			if ($_POST['NewPass1'] == $_POST['NewPass2'])
			{
				$pass = salt($_POST['NewPass1']);
				$sql = "UPDATE users SET Password=?, Comment=? WHERE UserID=?";
				$upPass = $db_connect->prepare($sql);
				$upPass->execute([$pass,$_POST['NewPass1'],$User['UserID']]);
				
				echo '<br><p style="color: #0f0;">تم تغيير كلمة السر</p>';
				
			} else { 
				echo '<p style="color: #f00;">كلمة السر الجديدة غير متطابقة</p>';
				include('include/changeForm.php'); 
			}
		} else { 
			echo '<p style="color: #f00;">كلمة السر الحالية غلط</p>'; 
			include('include/changeForm.php'); 
		}
	} else { 
		include('include/passForm.php'); 
		
		echo'<form action="'.$path.'">
			<input class="inputA" type="submit" value="الرجوع للصفحة الرئيسية" style="background: #4caf50" />
		</form>';
	}
} else {
	header("Location: Login.php");
	die();
}

include('include/footer.php');