<?php

$starttime = microtime(true);

include('include/header.php');

require('include/vars.php');
require('include/connect.php');
require('include/func.php');
require('include/funcOrg.php');

giveAskers($db_connect);

$User = fetchUser($db_connect);

if (isset($User['UserID']))
{
	if ($User['Auth']) 
	{
		echo '<h1>'.$User['Username'].'</h1>';
		echo (isset($User['Phone'])) ? '<h1>'.$User['Phone'].'</h1>' : '';

		counterTable($User,true);
		
		$DoingSomeThing = false;
		
		if (isset($_POST['Share']))
		{
			if ($_POST['Share'] >= 0 && $_POST['Share'] <= $User['Gold'] )
			{
				$DoingSomeThing = true;
				
				$sql = "UPDATE users SET Share=? WHERE UserID=?";
				$upShare = $db_connect->prepare($sql);
				$upShare->execute(array($_POST['Share'],$User['UserID']));
				
				header("Refresh:0");
			} else { echo '<p style="color: #f00;">رصيدك لا يكفي</p>'; }
		}
		
		if(!$DoingSomeThing)
		{
			
			// $SDGT = SDGT($User,$db_connect);
			
			$ShareColor = ($User['Share'] > 0) ? '#0f0' : '#f00';
			echo '<font size=4 color="#0f0" >';
			echo '<table align="center" border="2" style="width:100%;max-width:75%;border-collapse:collapse;">';
			echo '<tr><td nowrap style="direction: ltr;width:50%;color:'.$ShareColor.';"> '.number_format($User['Share']).' mg</td><td nowrap > مبلغ المساهمة </td></tr>';
			echo '</table></font><br>';
	
			if (!$User['Auth']) {
				echo'<form action="Auth.php">
					<input class="inputA" type="submit" value="تفعيل الصلاحيات" style="background: #ff9800" />
				</form>';
			}
			
			if ($User['Share'] > 0)
			{
				echo '<form method="POST">';
				echo '<input value="0" name="Share" type="hidden" />';
				echo '<input class="inputA" type="submit" value="إلغاء الإشتراك" style="background: #ff9800" />';
				echo '</form>';
			}
			
			$DisShare = ($User['Gold'] > 0) ? '' : 'Disabled';
			echo '<form method="POST">';
			echo '<input placeholder="اكتب مبلغ المساهمة" class="inputA" style="background: white;" name="Share" type="number" max="24000" min="1" required />';
			echo '<input class="inputA" type="submit" value="مشاركة" style="background: #4caf50" '.$DisShare.' />';
			echo '</form>';
			
			echo'<form action="OrgBest.php">
				<input class="inputA" type="submit" value="أعلى المساهمين" />
			</form>';
			
			echo'<form action="Org.php">
				<input class="inputA" type="submit" value="الرجوع لصفحة الجمعية" />
			</form>';
			
			echo'<form action="'.$path.'">
				<input class="inputA" type="submit" value="الرجوع للصفحة الرئيسية" />
			</form><br>';
			
			// orgTable($SDGT[0],'إجمالي بيانات العائلة');
			
		} else {
			
			echo '<h1>...جاري التحميل...</h1>';
			
			echo'<form action="Org.php">
				<input class="inputA" type="submit" value="الرجوع لصفحة الجمعية" />
			</form>';
		
			echo'<form action="'.$path.'">
				<input class="inputA" type="submit" value="الرجوع للصفحة الرئيسية" style="background: #4caf50" />
			</form><br>';
		}
	} else { header("Location: Auth.php"); }
} else { header("Location: Login.php"); }

$endtime = microtime(true);
echo round(($endtime - $starttime), 3);
include('include/footer.php');

function pri(&$arr,$com)
{
	echo $com . ' : ';
	print_r($arr);
}