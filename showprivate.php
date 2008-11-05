<?php
//  AcmlmBoard XD - Private message display page
//  Access: user, specifically the sender or reciever.

include("lib/common.php");

if(!loguserid)
	Kill("You must be logged in to view your private messages.");

if(!isset($_GET['id']))
	Kill("No PM specified.");

$id = (int)$_GET['id'];

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

$qPM = "update pmsgs set msgread=1 where userto=".$loguserid." and id=".$id;
$rPM = Query($qPM);

$links = "<a href=\"sendprivate.php?pid=".$id."\">Send reply</a>";

MakeCrumbs(array("Main"=>"./", "Private messages"=>"private.php", $pm['title']=>""), $links);

$pm['num'] = "---";
MakePost($pm, $user, 0, 0, $id);

MakeCrumbs(array("Main"=>"./", "Private messages"=>"private.php", $pm['title']=>""), $links);

?>
