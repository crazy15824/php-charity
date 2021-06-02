<?php

$starttime = microtime(true);


include('include/header.php');

require('include/vars.php');
require('include/connect.php');
require('include/func.php');

$User = fetchUser($db_connect);

if (isset($User['UserID']))
{
	
	if(!isset($User['Phone'])) {
		header("Location: Phone.php");
		die();
	}
	
	if ($User['Auth']) {
		
		if (isset($_POST['delAuth'])) {
			$sql = "UPDATE cookies SET Auth=? WHERE UserID_Cookies=?";
			$upCookie = $db_connect->prepare($sql);
			$upCookie->execute([0,$User['UserID']]);
			
			header("Refresh:0");
			die();
		}
		
		if (isset($_POST['delCookies'])) {
			
			$delCookies = $db_connect->prepare("DELETE FROM cookies WHERE UserID_Cookies = ? AND CookieID != ? ");
			$delCookies->execute(array($User['UserID'],$User['CookieID']));
			
			header("Refresh:0");
			die();
		}
	}
	
	echo '<h1>'.$User['Username'].'</h1>';
	echo (isset($User['Phone'])) ? '<h1>'.$User['Phone'].'</h1>' : '';

	counterTable($User);
	
	echo'<form action="Trans.php">
			<input class="inputA" type="submit" value="تحويل الرصيد" />
		</form>';
		
	echo'<form action="Org.php">
		<input class="inputA" type="submit" value="الجمعية الخيرية" />
	</form>';
	
	if ($User['Admin'] && $User['Auth'])
	{
		echo'<form action="AdminIN.php">
			<input class="inputA" type="submit" value="خزينة البنك" />
		</form>';
	}
	
	echo'<form action="Log.php">
			<input class="inputA" type="submit" value="بحث السجلات" />
		</form>';
		
	echo'<form action="Profile.php">
			<input class="inputA" type="submit" value="بيانات الحساب" />
		</form>';
	
	if ($User['Auth']) {
		
		echo'<form name="delAuth" method="POST">
				<input name="delAuth" type="hidden" value="delAuth" />
				<input id="delAuth" class="inputA" type="submit" value="إلغاء الصلاحيات" style="background: #ff9800" />
			</form>';
			
		$getCookies = $db_connect->prepare("SELECT CookieID FROM cookies WHERE UserID_Cookies = ? AND CookieID != ? ");
		$getCookies->execute(array($User['UserID'],$User['CookieID']));
		$Cookies = $getCookies->fetchAll();

		$ars = "'تأكيد الخروج'";
		if (count($Cookies) > 0) {
			
			echo '<form name="delCookies" method="POST">
						<input name="delCookies" type="hidden" value="delCookies" />
						<input class="inputA" type="submit" onclick="return confirm('.$ars.')" value="خروج الكل ('.count($Cookies).')" style="background: #ff9800" />
					</form>';
		}
	} else {
		echo'<form action="Auth.php">
			<input class="inputA" type="submit" value="تفعيل الصلاحيات" />
		</form>';
	}
	
	echo'<form name="out" method="POST" action="Logout.php">
			<input name="out" type="hidden" value="out" />
			<input class="inputA" type="submit" value="تسجيل خروج" style="background: #b71a1a" />
		</form>';
	
	// echo '<h3>الذهب عنصر نادر، الأوراق سهلة التصنيع</h3>';

	// echo '<br><h1>أماكن تقبل التعامل بالمحفظة</h1>';
	// echo '<font size=4 color="#0f0" >';
	// echo '<table align="center" border="2" style="width:100%;max-width:75%;border-collapse:collapse;">';
	// echo '<tr><td nowrap style="direction: rtl;color:"> الموبايل </td><td nowrap > العنوان </td></tr>';
	// echo '<tr><td nowrap style="direction: rtl;color:"> - </td><td nowrap > - </td></tr>';
	// echo '</table></font><br>';
	
} else {
	header("Location: Login.php");
	die();
}

$endtime = microtime(true);
echo round(($endtime - $starttime), 3);
include('include/footer.php');