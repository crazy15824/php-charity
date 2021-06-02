<?php

include('include/header.php');

require('include/vars.php');
require('include/connect.php');
require('include/func.php');

$User = fetchUser($db_connect);

if (isset($User['UserID']))
{
	echo '<h1>'.$User['Username'].'</h1>';
	echo (isset($User['Phone'])) ? '<h1>'.$User['Phone'].'</h1>' : '';
	
	counterTable($User,true);
	
	$printTransForm = true;
	
	if (isset($_POST['Phone'])) {
		if ($User['Credit'] > 0) {
			if ($User['Auth']) {
				if (!preg_match("/\D/", $_POST['Phone'])) {
					if (strlen($_POST['Phone']) == 11) {
						
						$Commit = true;
						$db_connect->beginTransaction();
						
						$getPhone = $db_connect->prepare("SELECT UserID,Credit,Bonus,Surname,ShowMe FROM users WHERE Phone = ? FOR UPDATE");
						$getPhone->execute(array($_POST['Phone']));
						$Phone = $getPhone->fetch();
						
						if (isset($Phone['UserID'])) 
						{
							if ($User['UserID'] != $Phone['UserID'])
							{
								if (!preg_match("/\D/", $_POST['Gold']))
								{
									$_POST['Gold'] = (int)$_POST['Gold'];
									
									if ($_POST['Gold'] >= 1)
									{
										if ($_POST['Gold'] <= $User['Credit'])
										{
											$printTransForm = false;
											
											if (isset($_POST['Confirm']))
											{
												$GiverCredit = $User['Credit'] - $_POST['Gold'];
												$GiverBonus = $User['Bonus'] - $_POST['Gold'];
												
												$TakerCredit = $Phone['Credit'] + $_POST['Gold'];
												$TakerBonus = $Phone['Bonus'] + $_POST['Gold'];
												
												$sql = "UPDATE users SET Credit=?,Bonus=? WHERE UserID=?";
												$upGiver = $db_connect->prepare($sql);
												$upGiver->execute([$GiverCredit,$GiverBonus,$User['UserID']]);
												
												$sql = "UPDATE users SET Credit=?,Bonus=? WHERE UserID=?";
												$upTaker = $db_connect->prepare($sql);
												$upTaker->execute([$TakerCredit,$TakerBonus,$Phone['UserID']]);
												
												$insertTransLog = $db_connect->prepare("INSERT INTO gold_log(GiverID,TakerID,GiverCredit,TakerCredit,TheGive) values(?,?,?,?,?)");
												$insertTransLog->execute(array($User['UserID'],$Phone['UserID'],$User['Credit'],$Phone['Credit'],$_POST['Gold']));
												
												$db_connect->commit();
												$Commit = false;
												
												echo '<script type="text/javascript"> window.close(); </script>';
												header("Refresh:0");
												die();
											}
											
											$Phone['Surname'] = ($Phone['ShowMe']) ? $Phone['Surname'] : '';
											
											echo '<form method="POST" onSubmit="getElementById(\'trans\').disabled = \'true\';" >';
											echo '<h1 style="direction: rtl;color: #ff0;">سيتم تحويل مبلغ '.$_POST['Gold'].' مللي</h1>';
											echo '<h1 style="direction: rtl;color: #ff0;">رصيد دائن الى '.$Phone['Surname'].' </h1>';
											echo '<h1 style="direction: rtl;color: #ff0;"> '.$_POST['Phone'].' </h1>';
											echo '<input value="'.$_POST['Phone'].'" name="Phone" type="hidden" />';
											echo '<input value="'.$_POST['Gold'].'" name="Gold" type="hidden" />';
											echo '<input value="1" name="Confirm" type="hidden" />';
											echo '<input id="trans" class="inputA" type="submit" value="تحويل" style="background: #4caf50" />';
											echo '</form>';
											
											echo'<form method="POST">
													<input onclick="window.close();" class="inputA" type="submit" value="الـغـاء" style="background: #b71a1a"/>
												</form>';
												
										} else { echo '<p style="color: #f00;">أقصى مبلغ يمكن تحويله هو '.$User['Credit'].' مللي</p>'; }
									} else { echo '<p style="color: #f00;">مينفعش تحول أقل من 1 مللي</p>'; }
								} else { echo '<p style="color: #f00;">في خربطة في المبلغ</p>'; }
							} else { echo '<p style="color: #f00;">رقم الموبايل مخصص لحسابك</p>'; }
						} else { echo '<p style="color: #f00;">رقم الموبايل مش متسجل</p>'; }
						
						if ( $Commit ) { $db_connect->commit(); }
						
					} else { echo '<p style="color: #f00;">رقم الموبايل مش 11 رقم</p>'; }
				} else { echo '<p style="color: #f00;">في خربطة في رقم الموبايل</p>'; }
			} else { echo '<p style="color: #f00;">انت لا تملك صلاحيات التحويل</p>'; }
		} else { echo '<p style="color: #f00;">لا يوجد لديك رصيد دائن</p>'; }
	}
	
	if ( $printTransForm ) { 
		
		if (!$User['Auth']) {
			echo'<form action="Auth.php">
				<input class="inputA" type="submit" value="تفعيل الصلاحيات" style="background: #ff9800" />
			</form>';
		}
		
		printTransForm($User,$db_connect); 

		echo'<form action="OrgTake.php">
			<input class="inputA" type="submit" value="إستلام رصيد مدين" />
		</form>';
		
		echo'<form action="Log.php">
			<input class="inputA" type="submit" value="بحث السجلات" />
		</form>';
		
		echo'<form action="Org.php">
			<input class="inputA" type="submit" value=" الرجوع لصفحة الجمعية" />
		</form>';
		
		echo'<form action="'.$path.'">
				<input class="inputA" type="submit" value="الرجوع للصفحة الرئيسية" style="background: #4caf50" />
			</form><br>';
	}
	
	printLog($User,$db_connect,0,1);
	
} else {
	header("Location: Login.php");
	die();
}

include('include/footer.php');

function printTransForm($User,$db_connect)
{
	$PhonePost = (isset($_POST['Phone'])) ? $_POST['Phone'] : '';
	$GoldPost = (isset($_POST['Gold'])) ? $_POST['Gold'] : '';
	
	
	echo '<form method="POST" target="_blank">';
	echo '<input placeholder="رقم الموبايل" value="'.$PhonePost.'" class="inputA" style="background: white;" name="Phone" type="number" required />';
	echo '<input id="GoldToSend" placeholder="المبلغ" value="'.$GoldPost.'" class="inputA" style="background: white;" name="Gold" type="number" min="1" required />';
	echo '<input class="inputA" type="submit" value="تحويل رصيد دائن" style="background: #4caf50" />';
	echo '</select></form>';
}