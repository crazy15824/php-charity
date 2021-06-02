<?php

require_once('vars.php');
require_once('connect.php');

function orgTable(&$User,$Caption)
{
	$User['Debt'] = $User['Give'] - $User['Take'];
	$DebtColor = ($User['Debt'] < 0) ? '#f00' : '#0f0';
	$GiveColor = ($User['Give'] > 0) ? '#0f0' : '#f00';
	$TakeColor = ($User['Take'] > 0) ? '#f00' : '#0f0';
	$ShareColor = ($User['Share'] > 0) ? '#0f0' : '#f00';
	if (isset($User['Count']))
		$NumbColor = ($User['Count'] > 0) ? '#0f0' : '#f00';
	if (isset($User['Priority']))
		$Priority = ($User['Priority'] > 0) ? '#0f0' : '#f00';
	
	echo '<font size=4 color="#fff" >';
	echo '<table align="center" border="2" style="width:100%;max-width:75%;border-collapse:collapse;">';
	echo '<caption>'.$Caption.'</caption>';
	echo '<tr><td nowrap style="direction: ltr;width:50%;color:'.$GiveColor.';"> '.$User['Give'].' mg</td><td nowrap > رصيد دائن </td></tr>';
	echo '<tr><td nowrap style="direction: ltr;width:50%;color:'.$TakeColor.';"> '.$User['Take'].' mg</td><td nowrap > رصيد مدين </td></tr>';
	echo '<tr><td nowrap style="direction: ltr;width:50%;color:'.$DebtColor.';"> '.$User['Debt'].' mg</td><td nowrap  > الفرق (دائن - مدين)</td></tr>';
	echo '<tr><td nowrap style="direction: ltr;width:50%;color:'.$ShareColor.';"> '.$User['Share'].' mg</td><td nowrap > مبلغ المساهمة </td></tr>';
	if (isset($User['Count']))
		echo '<tr><td nowrap style="direction: rtl;width:50%;color:'.$NumbColor.';"> '.$User['Count'].'</td><td nowrap > عدد الأفراد </td></tr>';
	echo '</table></font><br>';
}

function SDGT(&$User,&$db_connect)
{
	$parStack = array('End');
	$relArr = array();
	$SDGT = array('Share' => 0, 'Give' => 0, 'Take' => 0);
	
	pushParentIDs($parStack,$User);
	putUser($relArr,$User);
	
	while(1)
	{
		$Flag = array_pop($parStack);
		if ( $Flag == 'End' ) Break;
		
		$DadUser = array();
		$MomUser = array();
		$Childrn = array();
		
		if ($Flag == 'Dad' || $Flag == 'Both')
		{
			$Dad = array_pop($parStack);
			if (isset($Dad['DadID']) && !isset($relArr[$Dad['DadID']]))
			{
				$DadUser = getUser($Dad['DadID'],$db_connect);
				putUser($relArr,$DadUser);
				
				$SibUsers = getSib($Dad['DadID'],$Dad['SonOrDauID'],$Childrn,$db_connect);
				
				$Chl = childLoop($SibUsers,$relArr,$Dad,$Childrn,$db_connect);
				
				$SDGT['Share'] += $Chl['Share'] + $DadUser['Share'];
				$SDGT['Give'] += $Chl['Give'] + $DadUser['Give'];
				$SDGT['Take'] += $Chl['Take'] + $DadUser['Take'];
			}
		}
		
		if ($Flag == 'Mom' || $Flag == 'Both')
		{
			$Mom = array_pop($parStack);
			if (isset($Mom['MomID']) && !isset($relArr[$Mom['MomID']]))
			{
				$MomUser = getUser($Mom['MomID'],$db_connect);
				putUser($relArr,$MomUser);
				
				$SibUsers = getSib($Mom['MomID'],$Mom['SonOrDauID'],$Childrn,$db_connect);
				
				$Chl = childLoop($SibUsers,$relArr,$Mom,$Childrn,$db_connect);
				
				$SDGT['Share'] += $Chl['Share'] + $MomUser['Share'];
				$SDGT['Give'] += $Chl['Give'] + $MomUser['Give'];
				$SDGT['Take'] += $Chl['Take'] + $MomUser['Take'];
			}
		}
		
		pushParentIDs($parStack,$MomUser);
		pushParentIDs($parStack,$DadUser);
	}
	
	$SDGT['Count'] = count($relArr);
	
	return array(&$SDGT,&$relArr);
}

function getUser(&$UserID,&$db_connect)
{
	$sql  = " SELECT UserID,Username,Share,Give,Take,Father_ID,Mother_ID FROM users";
	$sql .= " WHERE UserID = ?";
	
	$getUser = $db_connect->prepare($sql);
	$getUser->execute(array($UserID));
	$User = $getUser->fetch(PDO::FETCH_ASSOC);
	
	return $User;
}

function pushParentIDs(&$parStack,&$popUser)
{
	$Dad = isset($popUser['Father_ID']);
	$Mom = isset($popUser['Mother_ID']);
	$Flag = "Non";
	
	if ($Mom)
	{
		$Flag = "Mom";
		array_push($parStack, array( 
			'ParID' => $popUser['Mother_ID'],
			'MomID' => $popUser['Mother_ID'], 
			'DadID' => $popUser['Father_ID'], 
			'SonOrDauID' => $popUser['UserID']
			));
	}
	
	if ($Dad)
	{
		$Flag = "Dad";
		array_push($parStack, array( 
			'ParID' => $popUser['Father_ID'],
			'DadID' => $popUser['Father_ID'],
			'MomID' => $popUser['Mother_ID'],
			'SonOrDauID' =>  $popUser['UserID']
			));
	}
	
	if ($Dad && $Mom) $Flag = "Both";
	if ($Flag != 'Non')
		array_push($parStack, $Flag);
}

function getSib(&$MomOrDadID,&$UserID,&$Childrn,&$db_connect)
{	
	$sql  = " SELECT UserID,Username,Share,Give,Take,Father_ID,Mother_ID FROM users";
	$sql .= " WHERE (Father_ID = ? OR Mother_ID = ?) AND UserID != ? ";
	
	$exeArr = array($MomOrDadID,$MomOrDadID,$UserID);
	
	foreach($Childrn as $Child)
	{
		$sql .= " AND UserID != ? ";
		array_push($exeArr, $Child['UserID']);
	}
	
	$getUsers = $db_connect->prepare($sql);
	$getUsers->execute($exeArr);
	$Users = $getUsers->fetchAll(PDO::FETCH_ASSOC);
	
	return $Users;
}

function getChildren(&$MomOrDadID,&$db_connect)
{	
	$sql  = " SELECT UserID,Username,Share,Give,Take FROM users";
	$sql .= " WHERE (Father_ID = ? OR Mother_ID = ?) ";
	
	$getUsers = $db_connect->prepare($sql);
	$getUsers->execute(array($MomOrDadID,$MomOrDadID));
	$Users = $getUsers->fetchAll(PDO::FETCH_ASSOC);
	
	return $Users;
}

function childLoop(&$CrntGenUsers,&$relArr,&$Par,&$Childrn,&$db_connect)
{
	$CrntGen = array( 'Share' => 0, 'Give' => 0, 'Take' => 0);
	
	foreach ($CrntGenUsers as $cGen )
	{
		$Half = 0.5;
		if (isset($cGen['Father_ID']))
			if ($Par['ParID'] == $Par['DadID'] )
				if ($cGen['Mother_ID'] == $Par['MomID']) {
					$Half = 1;
					array_push($Childrn, $cGen);
				}
		
		if (!isset($relArr[$cGen['UserID']]))
		{
			putUser($relArr,$cGen);
			
			$NextGenUsers = getChildren($cGen['UserID'],$db_connect);
			$NextGen = childLoop($NextGenUsers,$relArr,$Par,$Childrn,$db_connect);
			
			$CrntGen['Share'] += (($cGen['Share'] + $NextGen['Share']) * $Half);
			$CrntGen['Give'] += (($cGen['Give'] + $NextGen['Give']) * $Half);
			$CrntGen['Take'] += (($cGen['Take'] + $NextGen['Take']) * $Half);
		}
	}
	
	return $CrntGen;
}

function putUser(&$arr,&$User)
{
	$arr[$User['UserID']] = $User['Username'];
	$arr[$User['UserID']] .= ' => '. $User['Share'];
	$arr[$User['UserID']] .= ','. $User['Give'];
	$arr[$User['UserID']] .= ','. $User['Take'];
}
