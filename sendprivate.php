<?php
//  AcmlmBoard XD - Private message sending/previewing page
//  Access: user

include("lib/common.php");

MakeCrumbs(array("Main"=>"./", "Private messages"=>"private.php", "New PM"=>""), "");

if(!$loguserid) //Not logged in?
	Kill("You must be logged in to post.");

$pid = (int)$_GET['pid'];
if($pid)
{
	$qPM = "select * from pmsgs left join pmsgs_text on pid = pmsgs.id where userto = ".$loguserid." and pmsgs.id = ".$pid;
	$rPM = Query($qPM);
	if(NumRows($rPM))
	{
		$sauce = Fetch($rPM);
		$qUser = "select * from users where id = ".$sauce['userfrom'];
		$rUser = Query($qUser);
		if(NumRows($rUser))
			$user = Fetch($rUser);
		else
			Kill("Unknown user.");
		$prefill = "[reply=\"".$user['name']."\" id=\"".$sauce['id']."\"]".$sauce['text']."[/quote]";
		$trefill = "Re: ".$sauce['title'];
		if(!isset($_POST['to']))
			$_POST['to'] = $user['name'];
	} else
		Kill("Unknown PM.");
}

$uid = (int)$_GET['uid'];
if($uid)
{
	$qUser = "select * from users where id = ".$uid;
	$rUser = Query($qUser);
	if(NumRows($rUser))
	{
		$user = Fetch($rUser);
		$_POST['to'] = $user['name'];
	} else
		Kill("Unknown user.");
}


if($loguser['powerlevel'] == -1)
	Kill("You're banned.");

if($_POST['to'])
{
	$qUser = "select id from users where name='".$_POST['to']."'";
	$rUser = Query($qUser);
	if(NumRows($rUser))
	{
		$user = Fetch($rUser);
		$to = $user['id'];
	} else
	{
		if($_POST['action']=="Send")
			Kill("Unknown user.");
		else
			$_POST['to'].="<- unknown";
	}
}

if($_POST['action']=="Send")
{
	if($_POST['text'])
	{
		$qPM = "insert into pmsgs (userto, userfrom, date, ip, msgread) values (".$to.", ".$loguserid.", ".time().", '".$_SERVER['REMOTE_ADDR']."', 0)";
		$rPM = Query($qPM);
		$pid = mysql_insert_id();

		$qPMT = "insert into pmsgs_text (pid,title,text) values (".$pid.", '".$_POST['title']."', '".$_POST['text']."')";
		$rPMT = Query($qPMT);

		Redirect("PM sent!","private.php","your PM box.");
		exit();
	} else
		Alert("Enter a message and try again.", "Your PM is empty.");
}

$_POST['title'] = htmlentities(stripslashes($_POST['title']));
$_POST['text'] = stripslashes($_POST['text']);

if($_POST['action']=="Preview")
{
	if($_POST['text'])
	{
		$_POST['num'] = "---";
		MakePost($_POST, $loguser, 0, 0);
	}
}

if($_POST['text'])
	$prefill = $_POST['text'];
if($_POST['title'])
	$trefill = $_POST['title'];

if(!isset($_POST['iconid']))
	$_POST['iconid'] = 0;

print "<form action=\"sendprivate.php\" method=\"post\">";
print "<div class=\"border width50\">";
print "<table>";
print "<tr class=\"cell0\">";
print "<td style=\"width: 25%;\">To</td>";
print "<td><input type=\"text\" name=\"to\" size=\"60\" maxlength=\"60\" value=\"".$_POST['to']."\"></input></td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td style=\"width: 25%;\">Title</td>";
print "<td><input type=\"text\" name=\"title\" size=\"60\" maxlength=\"60\" value=\"".stripslashes($trefill)."\"></input></td>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td>Post</td>";
print "<td>";
print "<textarea name=\"text\" rows=\"16\" cols=\"60\">".$prefill."</textarea>";
print "</td>";
print "</tr>";
print "<tr class=\"cell2\">";
print "<td></td>";
print "<td>";
print "<input type=\"submit\" name=\"action\" value=\"Send\"></input>";
print "<input type=\"submit\" name=\"action\" value=\"Preview\"></input>";
print "</td>";
print "</tr>";
print "</table>";
print "</div>";
print "</form>";

MakeCrumbs(array("Main"=>"./", $forum['title']=>"forum.php?id=".$fid, "New PM"=>""), "");

doFooter();

?>
