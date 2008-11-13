<?php
//  AcmlmBoard XD support - Login support

$bots = array(
	"Microsoft URL Control",
	"msnbot",
	"Yahoo! Slurp",
	"Googlebot",
	"Mediapartners-Google",
	"yetibot@naver.com"
);

$isBot = 0;
if(str_replace($bots,"x",$_SERVER['HTTP_USER_AGENT']) != $_SERVER['HTTP_USER_AGENT'])
	$isBot = 1;

//Delete oldies visitor from the guest list. We may re-add him/her later.
$qGuests = "delete from guests where ip='".$_SERVER['REMOTE_ADDR']."' or date < ".(time()-300);
$rGuests = Query($qGuests);

//Lift dated IP Bans
$qIPBan = "delete from ipbans where date != 0 and date < ".time();
$rIPBan = Query($qIPBan);

//Do IP Ban check
$qIPBan = "select * from ipbans where instr('".$_SERVER['REMOTE_ADDR']."', ip)=1";
$rIPBan = Query($qIPBan);
if(NumRows($rIPBan))
{
	$ipban = Fetch($rIPBan);
	print "You have been ".($ipban['date'] ? "" : "<strong>permanently</strong> ")."IP-banned from this board".($ipban['date'] ? " until ".date("M jS Y, G:i:s",$ipban['date']).". That's ".TimeUnits($ipban['date']-time())." left" : "").". Attempting to get around this in any way will result in worse things.";
	exit();
}

$logdata = unserialize(base64_decode($_COOKIE['logdata']));
$loguserid = (int)$logdata['loguserid'];
$loguserpw = $logdata['loguserpw'];

if($loguserid) //Are we logged in?
{
	$qLogUser = "select * from users where id=".$loguserid." and password='".$loguserpw."'";
	$rLogUser = Query($qLogUser);
	if(NumRows($rLogUser)) //We have at least one result.
	{
		$loguser = Fetch($rLogUser);
		$rLastView = "update users set lastactivity=".time().", lastip='".$_SERVER['REMOTE_ADDR']."', lasturl='".$thisURL."' where id=".$loguserid;
		$qLastView = Query($rLastView);

		$dateformat = $loguser['dateformat'].", ".$loguser['timeformat'];
	} else
	{
		//No result found? Eat the cookies.
		setcookie("logdata", 0);

		$qGuest = "insert into guests (date, ip, lasturl, useragent, bot) values (".time().", '".$_SERVER['REMOTE_ADDR']."', '".$thisURL."', '".$_SERVER['HTTP_USER_AGENT']."', ".$isBot.")";
		$rGuest = Query($qGuest);

		$loguser = array("name"=>"Guest", "powerlevel"=>0, "threadsperpage"=>50, "postsperpage"=>20, "scheme"=>0,"dateformat"=>"m-d-y","timeformat"=>"h:i A");
		$loguserid = 0;
	}
} else
{
	$qGuest = "insert into guests (date, ip, lasturl, useragent, bot) values (".time().", '".$_SERVER['REMOTE_ADDR']."', '".$thisURL."', '".$_SERVER['HTTP_USER_AGENT']."', ".$isBot.")";
	$rGuest = Query($qGuest);

	$loguser = array("name"=>"Guest", "powerlevel"=>0);
	$loguserid = 0;
}

?>
