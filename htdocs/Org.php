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
	$LastTake = ($User['Last_Take'] != Null) ? strtotime($User['Last_Take']) : 0;
	$NextTime = $LastTake + 2629800;
	
	$DoingSomeThing = false;
	
	if (isset($_POST['Ask']))
	{
		if (time() >= $NextTime)
		{
			$DoingSomeThing = true;
			
			$AskDate = (isset($User['Ask_Date'])) ? Null : date('Y-m-d H:i:s');
			
			$sql = "UPDATE users SET Ask_Date=? WHERE UserID=?";
			$upAskDate = $db_connect->prepare($sql);
			$upAskDate->execute([$AskDate,$User['UserID']]);
			
			header("Refresh:0");
		}
	}
	
	echo '<h1>'.$User['Username'].'</h1>';
	echo (isset($User['Phone'])) ? '<h1>'.$User['Phone'].'</h1>' : '';

	counterTable($User,true);
	
	if(!$DoingSomeThing)
	{
		$Shares = getShares($db_connect);
		$Shares = ($Shares == Null) ? 0 : $Shares;
		$SharesColor = ($Shares > 0) ? '0f0' : 'f00';
		
		$sql  = " SELECT SUM(Credit) AS AllGives FROM users WHERE Credit > 0 ";
		$getAllGives = $db_connect->prepare($sql);
		$getAllGives->execute(array());
		$AllGives = $getAllGives->fetch(PDO::FETCH_ASSOC);
		
		$AllGives['AllGives'] = ($AllGives['AllGives'] == Null) ? 0 : $AllGives['AllGives'];
		$AllGivesColor = ($AllGives['AllGives'] > 0) ? '0f0' : 'f00';
		
		
		$AskDatePir = ($User['Ask_Date'] == Null) ? 0 : 1;
		$PirArr = array('الأول','الثاني','الثالث');
		$Askers = getAskersPir($db_connect,$User['UserID'],$AskDatePir);
		$AskPir = ($Askers[0] >= 0 && $Askers[0] < 3) ? $PirArr[$Askers[0]] : $Askers[0]++.'th';
		$AskersColor = ($Askers[1] == 0) ? '0f0' : 'f00';
		
		$sql   = " SELECT COUNT(IF(Credit>0,UserID,NULL)) AS Givers, ";
		$sql  .= " COUNT(IF(Credit<0,UserID,NULL)) AS Takers FROM users ";
		$getSubs = $db_connect->prepare($sql);
		$getSubs->execute(array());
		$Subs = $getSubs->fetch(PDO::FETCH_ASSOC);
		
		echo '<font size=4 color="#fff" >';
		echo '<table align="center" border="2" style="width:100%;max-width:75%;border-collapse:collapse;">';
		echo '<caption>بيانات صندوق الجمعية</caption>';
		echo '<tr><td nowrap style="direction: ltr;width:50%;color:#'.$SharesColor.';"> '.number_format($Shares).' mg </td><td nowrap > الرصيد المتاح </td></tr>';
		echo '<tr><td nowrap style="direction: ltr;width:50%;color:#'.$AllGivesColor.';"> '.number_format($AllGives['AllGives']).' mg </td><td nowrap > إجمالي القروض </td></tr>';
		echo '<tr><td nowrap style="direction: ltr;width:50%;color:#'.$AskersColor.';"> '.$Askers[1].' </td><td nowrap > طلبات القروض </td></tr>';
		echo '<tr><td nowrap style="direction: ltr;width:50%;color:#0f0;"> '.$AskPir.' </td><td nowrap > أولوية الإقتراض </td></tr>';
		echo '<tr>
				<td nowrap style="direction: rtl;width:50%;color:#fff;">
					<span style="color:#0f0;"> '.$Subs['Givers'].' دائن </span> : 
					<span style="color:#f00;"> '.$Subs['Takers'].' مدين </span>
				</td><td nowrap > عدد المشتركين </td>
			</tr>';
		echo '</table></font><br>';
		
		if (!$User['Auth']) {
			echo'<form action="Auth.php">
				<input class="inputA" type="submit" value="تفعيل الصلاحيات" style="background: #ff9800" />
			</form>';
		}
		
		if ($User['Ask_Date'] == null) {
			$Style = '';
			$AskTxt = 'طلب قرض';
		} else {
			echo $User['Ask_Date'];
			$Style = ' style="background: #ff9800"';
			$AskTxt = 'إلغاء طلب القرض';
		}
		
		$disableAsk = '';
		if (time() <= $NextTime) {
			$disableAsk = 'Disabled';
			$AskTxt = date('Y-m-d', $NextTime);
		}
		
		echo'<form method="POST" >
			<input value="1" name="Ask" type="hidden" />
			<input class="inputA" type="submit" value="'.$AskTxt.'" '.$disableAsk.$Style.' />
		</form>';
		
		echo'<form action="OrgShare.php">
			<input class="inputA" type="submit" value="المشاركة بالجمعية" />
		</form>';
		
		echo'<form action="OrgGive.php">
			<input class="inputA" type="submit" value="تعاملات الجمعية" />
		</form>';
		
		echo'<form action="OrgBest.php">
				<input class="inputA" type="submit" value="أعلى المساهمين" />
			</form>';
			
		echo'<form action="OrgPolic.php" policies>
			<input class="inputA" type="submit" value="الشروط والأحكام" style="background: #ff9800"/>
		</form>';
		
		echo'<form action="'.$path.'">
			<input class="inputA" type="submit" value="الرجوع للصفحة الرئيسية" />
		</form><br>';
		
	} else {
		
		echo '<h1>...جاري التحميل...</h1>';
		
		echo'<form action="Org.php">
			<input class="inputA" type="submit" value="الرجوع لصفحة الجمعية" />
		</form>';
	
		echo'<form action="'.$path.'">
			<input class="inputA" type="submit" value="الرجوع للصفحة الرئيسية" style="background: #4caf50" />
		</form><br>';
	}
	
} else {
	header("Location: Login.php");
	die();
}

$endtime = microtime(true);
echo round(($endtime - $starttime), 3);
include('include/footer.php');

function pri(&$arr,$com)
{
	echo $com . ' : ';
	print_r($arr);
}
