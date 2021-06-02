<?php

$starttime = microtime(true);


include('include/header.php');

require('include/vars.php');
require('include/connect.php');
require('include/func.php');

$User = fetchUser($db_connect);

if (isset($User['UserID']))
{
	echo'<br><br><form action="Org.php">
			<input class="inputA" type="submit" value="الرجوع لصفحة الجمعية" />
		</form>';
	
	echo'<form action="'.$path.'">
		<input class="inputA" type="submit" value="الرجوع للصفحة الرئيسية" style="background: #4caf50" />
	</form><br>';
		
	echo '<h1>قائمة أعلى المساهمين</h1>';
	
	$sql  = " SELECT Surname,(Credit + Share) AS Cred FROM users ";
	$sql .= " WHERE (Credit + Share) > 0 ";
	$sql .= " AND Best = 1 AND Surname != ''";
	$sql .= " ORDER BY Cred DESC,Bonus DESC,Share DESC,Last_Take ASC,Ask_Date ASC LIMIT 100";
	
	$getBest = $db_connect->prepare($sql);
	$getBest->execute(array());
	$Best = $getBest->fetchAll(PDO::FETCH_ASSOC);
	
	$C = 0;
	echo '<font size=4 color="#fff" >';
	echo '<table align="center" border="2" style="width:100%;max-width:75%;border-collapse:collapse;">';
	foreach($Best as $v)
		echo '<tr><td nowrap style="direction: ltr;width:50%;color:#0f0;"> '.number_format($v['Cred']).' mg</td><td nowrap > '.$v['Surname'].' </td><td> '.++$C.' </td></tr>';
	echo '</table></font><br>';
	
} else { header("Location: Login.php"); }
include('include/footer.php');