<?php

require_once('vars.php');
require_once('connect.php');

function fetchUser($db_connect,$all = true)
{
	if ($all) 
	{
		$AgentID = findAgentID($db_connect);
		$AddrID = findAddrID($db_connect);
		AgentAddr($db_connect,$AgentID,$AddrID);
	}

	if(isset($_COOKIE['chall']))
	{
		if (preg_match("/[0-9a-z]$/", $_COOKIE['chall']) && strlen($_COOKIE['chall']) == "32")
		{
			$sql  = " SELECT * FROM cookies";
			$sql .= " LEFT JOIN users ON UserID = UserID_Cookies ";
			$sql .= " WHERE Cookie = ?";
			
			$getUser = $db_connect->prepare($sql);
			$getUser->execute(array($_COOKIE['chall']));
			$User = $getUser->fetch();
			
			if (isset($User['CookieID']))
			{
				if ($all) 
				{
					$sql = "UPDATE cookies SET AgentID_Cookies=?, AddrID_Cookies=? WHERE CookieID=? ";
					$upCookie = $db_connect->prepare($sql);
					$upCookie->execute([$AgentID,$AddrID,$User['CookieID']]);
				}

				return $User;
			}
		}
	}
	
	setcookie('chall', null, -1, '/');
	return null;
}

function findAgentID($db_connect)
{
	$getAgent = $db_connect->prepare("SELECT AgentID FROM agent WHERE Agent = ?");
	$getAgent->execute(array($_SERVER['HTTP_USER_AGENT']));
	$Agent = $getAgent->fetch();
	
	if (isset($Agent['AgentID'])) {
		return $Agent['AgentID'];
	} else {
		$insertAgent = $db_connect->prepare("INSERT INTO agent(Agent) values(?)");
		$insertAgent->execute(array($_SERVER['HTTP_USER_AGENT']));
		return $db_connect->lastInsertId();
	}
}

function findAddrID($db_connect)
{
	$getAddr = $db_connect->prepare("SELECT AddrID,Views FROM addr WHERE Remote_Addr = ?");
	$getAddr->execute(array($_SERVER['REMOTE_ADDR']));
	$Addr = $getAddr->fetch();
	
	if (isset($Addr['AddrID'])) {
		$sql = "UPDATE addr SET Views=? WHERE AddrID=? ";
		$upAddr = $db_connect->prepare($sql);
		$upAddr->execute([++$Addr['Views'],$Addr['AddrID']]);

		return $Addr['AddrID'];
	} else {
		$insertAddr = $db_connect->prepare("INSERT INTO addr(Remote_Addr) values(?)");
		$insertAddr->execute(array($_SERVER['REMOTE_ADDR']));
		return $db_connect->lastInsertId();
	}
}

function AgentAddr($db_connect,$AgentID,$AddrID)
{
	$sql = "UPDATE addr SET AgentID_Addr=? WHERE AddrID=? ";
	$upAddr = $db_connect->prepare($sql);
	$upAddr->execute([$AgentID,$AddrID]);
	
	$sql = "UPDATE agent SET AddrID_Agent=? WHERE AgentID=? ";
	$upAgent = $db_connect->prepare($sql);
	$upAgent->execute([$AddrID,$AgentID]);
}

function isPass($user,$pass,$db_connect) {
	$pass = salt($pass);
	$getUser = $db_connect->prepare("SELECT UserID FROM users WHERE Username = ? AND Password = ? ");
	$getUser->execute(array($user,$pass));
	$fetchUser = $getUser->fetch();
	
	if (isset($fetchUser['UserID']))
		return true;
	else
		return false;
}

function salt($pass) 
{
	$salt = "GoldenAge";
	$pass = md5($pass);
	$pass = md5(sha1($pass.$salt));
	return $pass;
}

function counterTable($User,$Org = false)
{
	$blanColor = ($User['Gold'] > 0) ? '#0f0' : '#f00';
	
	$BankHTML = '';
	$BankIF = '';
	$OrgHTML = '';
	$OrgIF = '';
	
	echo '<font size=4 color="#0f0" >';
	echo '<table align="center" border="2" style="width:100%;max-width:75%;border-collapse:collapse;">';
	if ($User['Admin'] && $User['Auth'])
	{
		$bankColor = ($User['Bank'] > 0) ? '#0f0' : '#f00';
		echo '<tr><td id="Bank" nowrap style="direction: ltr;color:'.$bankColor.'">'.number_format($User['Bank']).' mg</td><td nowrap width="50%"> رصيد الخزينة </td></tr>';
		
		$BankHTML = 'var oldBank = document.getElementById("Bank").innerHTML';
		$BankIF = ' || oldBank != obj["Bank"] ';
	}
	echo '<tr><td id="Gold" nowrap style="direction: ltr;color:'.$blanColor.'">'.number_format($User['Gold']).' mg</td><td nowrap width="50%"> رصيد المحفظة</td></tr>';
	if ($Org) 
	{
		if ($User['Credit'] >= 0)
		{
			$DebtColor = ($User['Credit'] == 0) ? '#f00' : '#0f0';
			$OrgTxt = 'رصيد دائن';
			$OrgColor = '#0f0';
		} else {
			$User['Credit'] *= -1;
			$DebtColor = '#f00';
			$OrgTxt = 'رصيد مدين';
			$OrgColor = '#f00';
		}
		
		echo '<tr><td id="Orgn" nowrap style="direction:ltr;width:50%;color:'.$DebtColor.';">'.number_format($User['Credit']).' mg</td>
					<td nowrap  style="direction:ltr;width:50%;color:'.$OrgColor.';" > '.$OrgTxt.' </td></tr>';
		
		$OrgHTML = 'var oldOrgn = document.getElementById("Orgn").innerHTML';
		$OrgIF = ' || oldOrgn != obj["Orgn"] ';
	}
	echo '</table></font><br>';
	
	
	echo '<script>
		
		function loadGold() {
			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					var oldGold = document.getElementById("Gold").innerHTML;
					'.$OrgHTML.'
					'.$BankHTML.'
					var obj = JSON.parse(this.responseText);
					if (oldGold != obj["Gold"] '.$OrgIF.$BankIF.') { location.reload(true); }
				}
			};
			xhttp.open("GET", "Gold.php", true);
			xhttp.send();
		}
		setInterval(function(){ loadGold(); }, 1000);
	</script>';
}

function printLog(&$User,&$db_connect,$LogPage = 0,$Credit = 0,$Admin = 0)
{
	$Limit = '';
	$MonTxt = date('Y-m');
	$GetByDate = isset($_POST['Month']);
	$exe = array($User['UserID'],$User['UserID']);
	
	if ($GetByDate)
	{
		$time = strtotime($_POST['Month']);
		$Year = date('Y', $time);
		$Mon = date('m', $time);
		$MonTxt = date('Y-m', $time);
		if ($Admin)
			array_push($exe,$User['UserID']);
			
		array_push($exe,$Mon);
		array_push($exe,$Year);
		
	} else {
		$Limit = 'LIMIT 100';
		if ($Admin)
			array_push($exe,$User['UserID']);
	}
	
	$sql  = " SELECT a.Surname AS GiverName, b.Surname AS TakerName,";
	$sql .= " a.Phone AS GiverPhone, b.Phone AS TakerPhone,";
	$sql .= " a.ShowMe AS ShowGiver, b.ShowMe AS ShowTaker,";
	$sql .= " GiverID,TakerID,BankLog,GiverGold,TakerGold,TheGive,GiverCredit,TakerCredit,TransDate,Org FROM gold_log";
	$sql .= " INNER JOIN users a ON a.UserID = GiverID ";
	$sql .= " INNER JOIN users b ON b.UserID = TakerID ";
	$sql .= " WHERE (a.UserID = ? OR b.UserID = ?)";
	if ($Admin)
		$sql .= " AND BankLog IS NOT NULL AND GiverID = ?";
	if ($Credit)
		$sql .= " AND GiverCredit IS NOT NULL AND BankLog IS NULL ";
	if ($GetByDate)
		$sql .= " AND MONTH(TransDate) = ? AND YEAR(TransDate) = ?";
	$sql .= ' ORDER BY TransDate DESC '.$Limit;
	
	$getTransLogs = $db_connect->prepare($sql);
	$getTransLogs->execute($exe);
	$TransLogs = $getTransLogs->fetchAll();
	
	if ($Admin) {
		$arrTrans = adminTrans($TransLogs);
		$Caption = 'عمليات السحب والإيداع';
	}
	
	if ($Credit) {
		$arrTrans = creditTrans($TransLogs,$User['UserID']);
		$Caption = 'سجل الجمعية';
	}
	
	if (!$Admin && !$Credit) {
		$arrTrans = walletTrans($TransLogs,$User['UserID']);
		$Caption = 'سجل رصيد المحفظة';
	}
	
	
	if ($LogPage) {
		
		$SelArr = array(
			'Admin' 	=> '',
			'Wallet' 	=> '',
			'Credit' 	=> '');
		
		if (isset($_POST['Logs']))
			$SelArr[$_POST['Logs']] = 'Selected';
		else
			$SelArr['Wallet'] = 'Selected';
		
		echo '<form method="POST">';
		echo '<select class="inputA" style="background: white;" onchange="this.form.submit()" name="Logs">';
		if ($User['Admin'] && $User['Auth'])
			echo '<option value="Admin" '.$SelArr['Admin'].'> السحب والإيداع </option>';
		echo '<option value="Wallet" '.$SelArr['Wallet'].'> رصيد المحفظة </option>';
		echo '<option value="Credit" '.$SelArr['Credit'].'> تحويلات الجمعية </option>';
		echo '</select>';
		echo '<input class="inputA" type="month" name="Month" min="2020-01" max="'.date('Y-m').'" value="'.$MonTxt.'">';
		echo '<input class="inputA" type="submit" value="بحث" style="background: #4caf50" />';
		echo '</form><br>';
		
		echo '<font size=4px color="#fff" >';
		
		if ($Admin) {
			$Re = 'الإيداع';
			$Se = 'السحب';
		} else {
			$Re = 'المستلم';
			$Se = 'المرسل';
		}
		$RS = $arrTrans['Received'] - $arrTrans['Sended'];
		echo '<table align="center" border="2" style="width:100%;max-width:75%;border-collapse:collapse;">';
		echo '<caption>إجمالي المعاملات</caption>';
		echo '<tr><td> '.$arrTrans['Received'].' </td><td width="50%"> '.$Re.' </td></tr>';
		echo '<tr><td> '.$arrTrans['Sended'].' </td><td> '.$Se.' </td></tr>';
		echo '<tr><td> '.$RS.' </td><td> الفرق </td></tr>';
		echo '</table><br>';
		
		echo '</font>';
	}
	
	echo '<font size=4px color="#fff" >';
	
	echo '<table align="center" border="2" style="width:100%;max-width:75%;border-collapse:collapse;">';
	echo '<caption>'.$Caption.'</caption>';
	echo '<tr><td> التاريخ </td><td> الجهة </td><td> المبلغ </td><td> الرصيد </td></tr>';
	echo $arrTrans['Str'];
	echo '</table><br>';
	
	echo '</font>';
}

function createRow(&$row,&$v)
{
	$mTime = date(' j/n h:i a ', strtotime($v['TransDate']));
	return '<tr style="color:#'.$row['Color'].';">
					<td nowrap> '.$mTime.' </td>
					<td nowrap> '.$row['Dir'].$row['Person'].' <br> '.$row['Phone'].' </td>
					<td> '.$v['TheGive'].$row['Sign'].' </td>
					<td> '.$row['Log'].' </td>
				</tr>';
}

function adminTrans(&$TransLogs)
{
	$arr = array(
		'Str' 		=> '', 
		'Sended' 	=> 0, 
		'Received'	=> 0 );
	
	foreach($TransLogs as $v)
	{
		if ($v['TheGive'] > 0) {
			// If admin give someone gold number and put his real gold in bank
			$row = array(
				'Log' 		=> $v['BankLog'],
				'Sign' 		=> '+',
				'Dir' 		=> 'إيداع ',
				'Person'	=> $v['TakerName'],
				'Phone'		=> $v['TakerPhone'],
				'Color'		=> '0f0' );
			$arr['Received'] += $v['TheGive'];
		} else {
			// If admin take someone's gold number and give him his real gold from bank
			$row = array(
				'Log' 		=> $v['BankLog'],
				'Sign' 		=> '-',
				'Dir' 		=> 'سحب ',
				'Person'	=> $v['TakerName'],
				'Phone'		=> $v['TakerPhone'],
				'Color'		=> 'f00' );
			$v['TheGive'] *= -1;
			$arr['Sended'] += $v['TheGive'];
		}
		
		$arr['Str'] .= createRow($row,$v);
	}
	
	return $arr;
}

function walletTrans(&$TransLogs,$UserID)
{
	$arr = array(
		'Str' 		=> '', 
		'Sended' 	=> 0, 
		'Received'	=> 0 );
	
	foreach($TransLogs as $v)
	{
		$CreateRow = false;
		
		// Bank Transactions
		if ($v['BankLog'] != Null && $v['TakerID'] == $UserID)
		{
			$CreateRow = true;
			
			if ($v['TheGive'] > 0) {
				// If you give your gold to Bank
				$row = goldIn($v);
				$arr['Received'] += $v['TheGive'];
			} else {
				// If bank give you back your gold
				$row = goldOut($v);
				$v['TheGive'] *= -1;
				$arr['Sended'] += $v['TheGive'];
			}
		}
		
		// Wallet Transactions
		if (($v['BankLog'] == Null && $v['GiverCredit'] == Null ) ||	// Normal Transaction Or
			($v['GiverCredit'] != Null && $v['Org']))					// Orgnaization Transation
		{
			$CreateRow = true;
			
			if ($v['GiverID'] == $UserID) {
				// If you give gold to someone to buy something from him
				// Or as debit
				$row = giveGold($v);
				$arr['Sended'] += $v['TheGive'];
			} else {
				// If someone give you gold to buy something from you
				// Or as debit
				$row = takeGold($v);
				$arr['Received'] += $v['TheGive'];
			}
		}
		
		if ($CreateRow)
			$arr['Str'] .= createRow($row,$v);
	}
	
	return $arr;
}

function creditTrans(&$TransLogs,$UserID)
{
	$arr = array(
		'Str' 		=> '', 
		'Sended' 	=> 0, 
		'Received'	=> 0 );
	
	foreach($TransLogs as $v)
	{
		if ($v['Org'])
		{
			// Creation of Credit
			if ($v['GiverID'] == $UserID) {
				// If you give gold to someone as debit, your credit will rise up
				$row = giveToOrg($v);
				$arr['Received'] += $v['TheGive'];
			} else {
				// If someone give you gold as debit, your credit will drops down
				$row = takeFromOrg($v);
				$arr['Sended'] += $v['TheGive'];
			}
		} else {
			
			// Give or Take, Credit or Debit
			if ($v['GiverID'] == $UserID) {
				if ($v['TheGive'] > 0) {
					// If you give your credit to someone to buy something from him
					$row = giveCredit($v);
					$arr['Sended'] += $v['TheGive'];
				} else {
					// If someone take your debit to buy something from you
					$row = giveDebit($v);
					$v['TheGive'] *= -1;
					$arr['Received'] += $v['TheGive'];
				}
			} else {
				if ($v['TheGive'] > 0) {
					// If someone give you his credit to buy something from you
					$row = takeCredit($v);
					$arr['Received'] += $v['TheGive'];
				} else {
					// If you take someone's debit to buy something from him
					$row = takeDebit($v);
					$v['TheGive'] *= -1;
					$arr['Sended'] += $v['TheGive'];
				}
			}
		}
		
		$arr['Str'] .= createRow($row,$v);
	}
	
	return $arr;
}

/****************************************************************/
/****************************************************************/
/****************************************************************/
/********************** Wallet Transaction **********************/
/****************************************************************/
/****************************************************************/
/****************************************************************/

function goldIn(&$v)
{
	return array(
		'Log' 		=> $v['TakerGold'],
		'Sign' 		=> '+',
		'Dir' 		=> 'من ',
		'Person'	=> 'عملية ',
		'Phone'		=> 'الإيداع',
		'Color'		=> '0ff'
	);
}

function goldOut(&$v)
{
	return array(
		'Log' 		=> $v['TakerGold'],
		'Sign' 		=> '-',
		'Dir' 		=> 'إلي ',
		'Person'	=> 'عملية ',
		'Phone'		=> 'السحب',
		'Color'		=> 'ff0'
	);
}

function giveGold(&$v)
{
	return array(
		'Log' 		=> $v['GiverGold'],
		'Sign' 		=> '-',
		'Dir' 		=> 'إلى ',
		'Person'	=> ($v['Org']) ? 'الجمعية' : $v['TakerName'],
		'Phone'		=> ($v['Org']) ? 'الخيرية' : $v['TakerPhone'],
		'Color'		=> 'f00'
	);
}

function takeGold(&$v)
{
	return array(
		'Log' 		=> $v['TakerGold'],
		'Sign' 		=> '+',
		'Dir' 		=> 'من ',
		'Person'	=> ($v['Org']) ? 'الجمعية' : $v['GiverName'],
		'Phone'		=> ($v['Org']) ? 'الخيرية' : $v['GiverPhone'],
		'Color'		=> '0f0'
	);
}

/****************************************************************/
/****************************************************************/
/****************************************************************/
/******************** Credit Transactions ***********************/
/****************************************************************/
/****************************************************************/
/****************************************************************/

function takeFromOrg(&$v)
{
	return array(
		'Log' 		=> $v['TakerCredit'],
		'Sign' 		=> '-',
		'Dir' 		=> 'من ',
		'Person'	=> 'الجمعية',
		'Phone'		=> 'الخيرية',
		'Color'		=> 'f00'
	);
}

function giveToOrg(&$v)
{
	return array(
		'Log' 		=> $v['GiverCredit'],
		'Sign' 		=> '+',
		'Dir' 		=> 'إلى ',
		'Person'	=> 'الجمعية',
		'Phone'		=> 'الخيرية',
		'Color'		=> '0f0'
	);
}

function giveDebit(&$v)
{
	return array(
		'Log' 		=> '<span style="color:#f00">'.($v['GiverCredit'] * -1).'</span>',
		'Sign' 		=> '+',
		'Dir' 		=> 'من ',
		'Person'	=> $v['TakerName'],
		'Phone'		=> $v['TakerPhone'],
		'Color'		=> '0f0'
	);
}

function takeDebit(&$v)
{
	return array(
		'Log' 		=> '<span style="color:#0f0">'.$v['TakerCredit'].'</span>',
		'Sign' 		=> '-',
		'Dir' 		=> 'عن ',
		'Person'	=> $v['GiverName'],
		'Phone'		=> $v['GiverPhone'],
		'Color'		=> 'f00'
	);
}

function giveCredit(&$v)
{
	return array(
		'Log' 		=> $v['GiverCredit'],
		'Sign' 		=> '-',
		'Dir' 		=> 'إلى ',
		'Person'	=> $v['TakerName'],
		'Phone'		=> $v['TakerPhone'],
		'Color'		=> 'f00'
	);
}

function takeCredit(&$v)
{
	return array(
		'Log' 		=> $v['TakerCredit'],
		'Sign' 		=> '+',
		'Dir' 		=> 'من ',
		'Person'	=> $v['GiverName'],
		'Phone'		=> $v['GiverPhone'],
		'Color'		=> '0f0'
	);
}