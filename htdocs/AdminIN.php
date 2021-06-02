<?php

include('include/header.php');

require('include/vars.php');
require('include/connect.php');
require('include/func.php');

$User = fetchUser($db_connect);

if (isset($User['UserID']))
{
	if ($User['Admin'] && $User['Auth'])
	{
		echo '<h1>'.$User['Username'].'</h1>';
		echo (isset($User['Phone'])) ? '<h1>'.$User['Phone'].'</h1>' : '';
		
		counterTable($User);
		
		$printTransForm = true;
		
		if (isset($_POST['Phone'])) {
			if (!preg_match("/\D/", $_POST['Phone'])) {
				if (strlen($_POST['Phone']) == 11) {
					
					$Commit = true;
					$db_connect->beginTransaction();
					
					$getPhone = $db_connect->prepare("SELECT UserID,Gold,Surname,ShowMe FROM users WHERE Phone = ? FOR UPDATE");
					$getPhone->execute(array($_POST['Phone']));
					$Phone = $getPhone->fetch();
					
					if (isset($Phone['UserID'])) 
					{
						if (!preg_match("/\D/", $_POST['Gold']))
						{
							$_POST['Gold'] = (int)$_POST['Gold'];
							
							if ( $_POST['Gold'] >= 1 )
							{
								$printTransForm = false;
								
								if (isset($_POST['Confirm']))
								{
									$TakerGold = $Phone['Gold'] + $_POST['Gold'];
									$Bank = $User['Bank'] + $_POST['Gold'];
									
									$sql = "UPDATE users SET Gold=? WHERE UserID=?";
									$upTakerGold = $db_connect->prepare($sql);
									$upTakerGold->execute([$TakerGold,$Phone['UserID']]);
									
									$sql = "UPDATE users SET Bank=? WHERE UserID=?";
									$upGiverBank = $db_connect->prepare($sql);
									$upGiverBank->execute([$Bank,$User['UserID']]);
									
									$insertTransLog = $db_connect->prepare("INSERT INTO gold_log(GiverID,TakerID,BankLog,TakerGold,TheGive) values(?,?,?,?,?)");
									$insertTransLog->execute(array($User['UserID'],$Phone['UserID'],$User['Bank'],$Phone['Gold'],$_POST['Gold']));
									
									$db_connect->commit();
									$Commit = false;
									
									echo '<script type="text/javascript"> window.close(); </script>';
									header("Refresh:0");
									die();
								}
								
								$Phone['Surname'] = ($Phone['ShowMe']) ? $Phone['Surname'] : '';
								
								echo '<form method="POST" onSubmit="getElementById(\'trans\').disabled = \'true\';" >';
								echo '<h1 style="direction: rtl;color: #ff0;">سيتم إيداع مبلغ '.$_POST['Gold'].' مللي</h1>';
								echo '<h1 style="direction: rtl;color: #ff0;">في حساب '.$Phone['Surname'].' </h1>';
								echo '<h1 style="direction: rtl;color: #ff0;"> '.$_POST['Phone'].' </h1>';
								echo '<input value="'.$_POST['Phone'].'" name="Phone" type="hidden" />';
								echo '<input value="'.$_POST['Gold'].'" name="Gold" type="hidden" />';
								echo '<input value="1" name="Confirm" type="hidden" />';
								echo '<input id="trans" class="inputA" type="submit" value="إيداع" style="background: #4caf50" />';
								echo '</form>';
								
								echo'<form method="POST">
										<input onclick="window.close();" class="inputA" type="submit" value="الـغـاء" style="background: #b71a1a"/>
									</form>';
										
								
							} else { echo '<p style="color: #f00;">مينفعش تحول أقل من 1 mg</p>'; }
						} else { echo '<p style="color: #f00;">في خربطة في المبلغ</p>'; }
					} else { echo '<p style="color: #f00;">رقم الموبايل مش متسجل</p>'; }
					
					if ( $Commit ) { $db_connect->commit(); }
					
				} else { echo '<p style="color: #f00;">رقم الموبايل مش 11 رقم</p>'; }
			} else { echo '<p style="color: #f00;">في خربطة في رقم الموبايل</p>'; }
		}
		
		if ( $printTransForm ) { 
		
			printTransForm($User,$db_connect);
			
			echo'<form action="Log.php">
				<input class="inputA" type="submit" value="بحث السجلات" />
			</form>';
		
			echo'<form action="'.$path.'">
					<input class="inputA" type="submit" value="الرجوع للصفحة الرئيسية" style="background: #4caf50" />
				</form><br>';
		}
		
		printLog($User,$db_connect,0,0,1);
		
	} else { header("Location: ".$path); }
} else { header("Location: Login.php"); }

include('include/footer.php');

function printTransForm($User,$db_connect)
{
	$PhonePost = (isset($_POST['Phone'])) ? $_POST['Phone'] : '';
	$GoldPost = (isset($_POST['Gold'])) ? $_POST['Gold'] : '';
	
	
	echo '<form method="POST" target="_blank">';
	echo '<input placeholder="رقم الموبايل" value="'.$PhonePost.'" class="inputA" style="background: white;" name="Phone" type="number" required />';
	echo '<input id="GoldToSend" placeholder="المبلغ" value="'.$GoldPost.'" class="inputA" style="background: white;" name="Gold" type="number" min="1" required />';
	echo '<input class="inputA" type="submit" value="إيداع الرصيد" style="background: #4caf50" />';
	echo '</select></form>';
	
	echo'<form action="AdminOut.php">
			<input class="inputA" type="submit" value="سحب رصيد" />
		</form>';
}