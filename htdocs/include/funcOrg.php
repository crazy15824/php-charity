<?php

function giveAskers(&$db_connect)
{
	$LoanSet = 2000;
	$db_connect->beginTransaction();
	
	$Shares = getShares($db_connect);
	if ($Shares >= $LoanSet)
	{
		$Askers = getAskers($db_connect);
		$Givers = getGivers($db_connect);
		
		foreach($Askers as $Taker)
		{
			if (isset($Givers[0]['UserID']))
					if ($Givers[0]['UserID'] == $Taker['UserID'])
						array_shift($Givers);
			
			if ($Shares >= $LoanSet) 
			{
				$Shares -= $LoanSet;
				$Loan = $LoanSet;
				$Taker['newGold'] = $Taker['Gold'];
				$Taker['newCredit'] = $Taker['Credit'];
				$Taker['TheGive'] = $LoanSet;
				
				$Giver = array_shift($Givers);
		
				if (isset($Giver['UserID']))
				{
					if ($Giver['Share'] >= $Loan) {
						upGiver($Giver,$Taker,$Loan,$db_connect);
					} else {
						$Loan -= $Giver['Gold'];
						upGiver($Giver,$Taker,$Giver['Gold'],$db_connect);
						
						foreach($Givers as $Giver)
						{
							if ($Giver['Share'] >= $Loan){
								upGiver($Giver,$Taker,$Loan,$db_connect);
							} else {
								$Loan -= $Giver['Gold'];
								upGiver($Giver,$Taker,$Giver['Gold'],$db_connect);
								array_shift($Givers);
							}
							
							if ($Loan == 0) break;
						}
					}
					
					upTaker($Taker,$db_connect);
					
				} else break;
			} else break;
		}
	}
	
	$db_connect->commit();
}

function getShares(&$db_connect)
{
	$sql  = " SELECT SUM(Share) AS Shares FROM users WHERE Share > 0";
	
	$getShares = $db_connect->prepare($sql);
	$getShares->execute(array());
	$Shares = $getShares->fetch(PDO::FETCH_ASSOC);
	
	return $Shares['Shares'];
}

function getGivers(&$db_connect)
{
	$sql  = " SELECT UserID,Username,Gold,Share,Credit,Bonus ";
	$sql .= " FROM users WHERE Share > 0 ";
	$sql .= " ORDER BY Credit ASC,Bonus ASC,Share ASC,Last_Take DESC,Ask_Date DESC FOR UPDATE";
	$getGivers = $db_connect->prepare($sql);
	$getGivers->execute(array());
	return $getGivers->fetchAll(PDO::FETCH_ASSOC);
}

function getAskers(&$db_connect)
{
	$sql  = " SELECT UserID,Username,Gold,Credit FROM users ";
	$sql .= " WHERE Ask_Date IS NOT NULL";
	$sql .= " ORDER BY Credit DESC,Bonus DESC,Share DESC,Last_Take ASC,Ask_Date ASC ";
	$getAskers = $db_connect->prepare($sql);
	$getAskers->execute(array());
	return $getAskers->fetchAll(PDO::FETCH_ASSOC);
}

function upGiver(&$Giver,&$Taker,$TheGive,&$db_connect)
{
	$Giver['Share'] -= $TheGive;
	$Giver['Bonus'] += $TheGive;
	$Giver['newGold'] = $Giver['Gold'] - $TheGive;
	$Giver['newCredit'] = $Giver['Credit'] + $TheGive;
	$Taker['newGold'] += $TheGive;
	$Taker['newCredit'] -= $TheGive;
	
	$insertLog = $db_connect->prepare("INSERT INTO gold_log(GiverID,TakerID,GiverGold,TakerGold,GiverCredit,TakerCredit,TheGive,Org) values(?,?,?,?,?,?,?,?)");
	$insertLog->execute(array($Giver['UserID'],$Taker['UserID'],$Giver['Gold'],$Taker['Gold'],$Giver['Credit'],$Taker['Credit'],$TheGive,1));
	
	$sql = "UPDATE users SET Gold=?,Credit=?,Bonus=?,Share=? WHERE UserID=?";
	$upGiver = $db_connect->prepare($sql);
	$upGiver->execute([$Giver['newGold'],$Giver['newCredit'],$Giver['Bonus'],$Giver['Share'],$Giver['UserID']]);
}

function upTaker(&$Taker,&$db_connect)
{
	$sql = "UPDATE users SET Gold=?,Credit=?,Last_Take=?,Ask_Date = Null WHERE UserID=?";
	$upTaker = $db_connect->prepare($sql);
	$upTaker->execute([$Taker['newGold'],$Taker['newCredit'],date("Y-m-d H:i:s"),$Taker['UserID']]);
}

function getAskersPir(&$db_connect,$UserID,$AskDate)
{	
	$sql  = " SELECT COUNT(UserID) AS Pir FROM users ";
	$sql .= " WHERE Ask_Date IS NOT NULL OR UserID = ? AND Ask_Date > ?";
	$sql .= " ORDER BY Credit DESC,Bonus DESC,Share DESC,Last_Take ASC,Ask_Date ASC ";
	$getPir = $db_connect->prepare($sql);
	$getPir->execute(array($UserID,date("Y-m-d H:i:s")));
	$PirArr = $getPir->fetch(PDO::FETCH_ASSOC);
	$Pir = $PirArr['Pir'];
	
	$txt = '';
	$exe = array();
	
	if ($AskDate) {
		$txt = ' OR UserID = ? ';
		$exe = array($UserID);
	}
	
	$sql  = " SELECT UserID FROM users ";
	$sql .= " WHERE Ask_Date IS NOT NULL".$txt;
	$sql .= " ORDER BY Credit DESC,Bonus DESC,Share DESC,Last_Take ASC,Ask_Date ASC ";
	$getAskers = $db_connect->prepare($sql);
	$getAskers->execute($exe);
	$Askers = $getAskers->fetchAll(PDO::FETCH_ASSOC);
	$AskersC = COUNT($Askers);
	
	if ($AskDate) {
		$Pir = 0;
		foreach($Askers as $k => $v) {
			if ($v['UserID'] == $UserID)
				break;
			$Pir++;
		}
	}

	return array($Pir,$AskersC);
}