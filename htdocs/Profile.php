<?php

include('include/header.php');

require('include/vars.php');
require('include/connect.php');
require('include/func.php');

$User = fetchUser($db_connect);

if (isset($User['UserID']))
{
	if ($User['Auth']) 
	{
		echo '<h1>'.$User['Username'].'</h1>';
		echo (isset($User['Phone'])) ? '<h1>'.$User['Phone'].'</h1>' : '';
		
		printProfile($User,'بيانات الحساب');
		
		$DoingSomeThing = false;
		
		if (isset($_POST['ShowMe'])) {
			$DoingSomeThing = true;
			upUser($User['UserID'],'ShowMe',$_POST['ShowMe'],$db_connect);
			header("Refresh:0");
		}
		
		if (isset($_POST['TakeMyDebit'])) {
			$DoingSomeThing = true;
			upUser($User['UserID'],'TakeMyDebit',$_POST['TakeMyDebit'],$db_connect);
			header("Refresh:0");
		}
		
		if (isset($_POST['Best'])) {
			$DoingSomeThing = true;
			upUser($User['UserID'],'Best',$_POST['Best'],$db_connect);
			header("Refresh:0");
		}
		
		if(!$DoingSomeThing)
		{
			echo'<form action="Pass.php">
				<input class="inputA" type="submit" value="تغيير كلمة السر" />
			</form>';
	
			echo'<form action="Phone.php">
					<input class="inputA" type="submit" value="تغيير رقم الموبايل" />
				</form>';
		
			echo'<form method="POST">';
			if ($User['ShowMe']) {
					echo '<input value="0" name="ShowMe" type="hidden" />';
					echo '<input class="inputA" type="submit" value="إخفاء اللقب عند التحويل" style="background: #ff9800" />';
			} else {
					echo '<input value="1" name="ShowMe" type="hidden" />';
					echo '<input class="inputA" type="submit" value="إظهار اللقب عند التحويل" />';
			} echo '</form>';
			
			
			echo'<form method="POST">';
			if ($User['TakeMyDebit']) {
					echo '<input value="0" name="TakeMyDebit" type="hidden" />';
					echo '<input class="inputA" type="submit" value="إلغاء خاصية تحويل الدين" style="background: #ff9800" />';
			} else {
					echo '<input value="1" name="TakeMyDebit" type="hidden" />';
					echo '<input class="inputA" type="submit" value="تفعيل خاصية تحويل الدين" />';
			} echo '</form>';
			
			echo'<form method="POST">';
			if ($User['Best']) {
					echo '<input value="0" name="Best" type="hidden" />';
					echo '<input class="inputA" type="submit" value="إلغاء أعلى المساهمين" style="background: #ff9800" />';
			} else {
					echo '<input value="1" name="Best" type="hidden" />';
					echo '<input class="inputA" type="submit" value="إظهار أعلى المساهمين" />';
			} echo '</form>';
			
			echo'<form action="'.$path.'">
				<input class="inputA" type="submit" value="الرجوع للصفحة الرئيسية" />
			</form><br>';
			
			$Dad = array();
			if (isset($User['Father_ID']))
				$Dad = getUser($User['Father_ID'],$db_connect);
			printProfile($Dad,'بيانات الأب');
			
			$Mom = array();
			if (isset($User['Mother_ID']))
				$Mom = getUser($User['Mother_ID'],$db_connect);
			printProfile($Mom,'بيانات الأم');
		}
	} else { header("Location: Auth.php"); }
} else { header("Location: Login.php"); }

include('include/footer.php');

function upUser(&$UserID,$Prop,$Value,&$db_connect)
{
	$sql = "UPDATE users SET ".$Prop."=? WHERE UserID=?";
	$upUser = $db_connect->prepare($sql);
	$upUser->execute([$Value,$UserID]);
}

function getUser(&$UserID,&$db_connect)
{
	$sql  = " SELECT First_Name,Full_Name,Surname,National_ID FROM users";
	$sql .= " WHERE UserID = ?";
	
	$getUser = $db_connect->prepare($sql);
	$getUser->execute(array($UserID));
	$User = $getUser->fetch(PDO::FETCH_ASSOC);
	
	return $User;
}

function printProfile(&$User,$Caption)
{
	$FirstName 		= (!empty($User['First_Name'])) ? $User['First_Name'] 	: '-';
	$FullName 		= (!empty($User['Full_Name'])) 	? $User['Full_Name'] 	: '-';
	$National_ID 	= (isset($User['National_ID']))	? $User['National_ID'] 	: '-';
	$Surname 		= (!empty($User['Surname']))	? $User['Surname'] 		: '-';
	
	echo '<font size=4 color="#fff" >';
	echo '<table align="center" border="2" style="width:100%;max-width:75%;border-collapse:collapse;">';
	echo '<caption>'.$Caption.'</caption>';
	echo '<tr><td nowrap style="direction: ltr;width:70%;"> '.$FirstName.' </td><td nowrap > الإسم الأول </td></tr>';
	echo '<tr><td nowrap style="direction: ltr;"> '.$FullName.' </td><td nowrap > إسم الأب ثلاثي </td></tr>';
	echo '<tr><td nowrap style="direction: ltr;"> '.$National_ID.' </td><td nowrap > الرقم القومي </td></tr>';
	echo '<tr><td nowrap style="direction: ltr;"> '.$Surname.' </td><td nowrap > اللقب </td></tr>';
	echo '</table></font><br>';
}