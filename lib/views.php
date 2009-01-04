<?php
//  AcmlmBoard XD support - View counter support

//Update view counter
if(!$isBot)
{
	$qViewCounter = "update misc set views = views + 1";
	$rViewCounter = Query($qViewCounter) or Kill(mysql_error());

	$qMisc = "select * from misc";
	$rMisc = Query($qMisc);
	$misc = Fetch($rMisc);

	//Milestone reporting
	if($misc['views'] % $viewcountInterval == 0)
	{
		if($loguserid == 1)
			$who = "you";
		elseif($loguserid)
			$who = $loguser['name'];
		else
			$who = "a guest at ".$_SERVER['REMOTE_ADDR'];

		$qPM = "insert into pmsgs (userto, userfrom, date, ip, msgread) values (1, 1, ".time().", 'System', 0)";
		$rPM = Query($qPM);
		$pid = mysql_insert_id();
		$qPM = "insert into pmsgs_text (pid, title, text) values (".$pid.", 'View ".$misc['views']." reached by ".$who."', 'Take a note.')";
		$rPM = Query($qPM);
	}
}

?>
