<?php
//  AcmlmBoard XD - Private message display page
//  Access: user, specifically the sender or reciever.

include("lib/common.php");

if(!loguserid)
	Kill("You must be logged in to view your private messages.");

if(!isset($_GET['id']))
	Kill("No PM specified.");

$id = (int)$_GET['id'];

if(isset($_GET['snooping']))
{
	if($loguser['powerlevel'] > 2)
		$qPM = "select * from pmsgs left join pmsgs_text on pid = pmsgs.id where pmsgs.id = ".$id;
	else
		Kill("No snooping for you.");
}
else
	$qPM = "select * from pmsgs left join pmsgs_text on pid = pmsgs.id where (userto = ".$loguserid." or userfrom = ".$loguserid.") and pmsgs.id = ".$id;

$rPM = Query($qPM);
if(NumRows($rPM))
	$pm = Fetch($rPM);
else
	Kill("Unknown PM");

$qUser = "select * from users where id = ".$pm['userfrom'];
$rUser = Query($qUser);
if(NumRows($rUser))
	$user = Fetch($rUser);
else
	Kill("Unknown user.");

if(!isset($_GET['snooping']))
{
	$qPM = "update pmsgs set msgread=1 where id=".$pm['id'];
	$rPM = Query($qPM);
} else
	Alert("You are snooping.");

$links = "<a href=\"sendprivate.php?pid=".$id."\">Send reply</a>";

MakeCrumbs(array("Main"=>"./", "Private messages"=>"private.php", $pm['title']=>""), $links);

$pmtitle = $pm['title']; //sender's custom title overwrites this below, so save it here

$pm['num'] = "---";
$pm['posts'] = "---";
$pm['id'] = "???";
$pm['uid'] = $user['id'];
$copies = explode(",","title,title,name,picture,sex,powerlevel,avatar,postheader,signature,regdate,lastactivity,lastposttime");
foreach($copies as $toCopy)
	$pm[$toCopy] = $user[$toCopy];
MakePost($pm, 0, 0, 1);

MakeCrumbs(array("Main"=>"./", "Private messages"=>"private.php", $pmtitle=>""), $links);

doFooter();

?>
