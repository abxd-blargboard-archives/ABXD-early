<?php
//  AcmlmBoard XD - Private message sending/previewing page
//  Access: user

include("lib/common.php");

MakeCrumbs(array("Main"=>"./", "Private messages"=>"private.php", "New PM"=>""), "");

if(!$loguserid) //Not logged in?
	Kill("You must be logged in to send private messages.");

$pid = (int)$_GET['pid'];
if($pid)
{
	$qPM = "select * from pmsgs left join pmsgs_text on pid = pmsgs.id where userto = ".$loguserid." and pmsgs.id = ".$pid;
	$rPM = Query($qPM);
	if(NumRows($rPM))
	{
		$sauce = Fetch($rPM);
		$qUser = "select * from users where id = ".(int)$sauce['userfrom'];
		$rUser = Query($qUser);
		if(NumRows($rUser))
			$user = Fetch($rUser);
		else
			Kill("Unknown user.");
		$prefill = "[reply=\"".$user['name']."\"]".$sauce['text']."[/quote]";
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

/*
// "Banned users can't send PMs. Bad bad bad, quite often PMs are a good way for them to try and get unbanned." -- Mega-Mario
if($loguser['powerlevel'] == -1)
	Kill("You're banned.");
*/

$title = "Private messages";

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
	}
} else
{
	if($_POST['action'] == "Send")
		Alert("Enter a recipient and try again.", "Your PM has no recipient.");
	$_POST['action'] = "";
}

if($_POST['action']=="Send")
{
	if($_POST['title'])
	{
		$_POST['title'] = htmlentities2($_POST['title']);

		if($_POST['text'])
		{
			$post = htmlentities2(deSlashMagic($_POST['text']));
			$post = str_replace("\n","##TSURUPETTANYOUJO##", $post);
			TidyPost($post);
			$post = str_replace("##TSURUPETTANYOUJO##","\n", $post);
			$post = justEscape($post);

			$qPM = "insert into pmsgs (userto, userfrom, date, ip, msgread) values (".$to.", ".$loguserid.", ".time().", '".$_SERVER['REMOTE_ADDR']."', 0)";
			$rPM = Query($qPM);
			$pid = mysql_insert_id();

			$qPMT = "insert into pmsgs_text (pid,title,text) values (".$pid.", '".justEscape($_POST['title'])."', '".$post."')";
			$rPMT = Query($qPMT);

			Redirect("PM sent!","private.php","your PM box");
			exit();
		} else
		{
			Alert("Enter a message and try again.", "Your PM is empty.");
		}
	} else
	{
		Alert("Enter a title and try again.", "Your PM is untitled.");
	}
}

$_POST['title'] = htmlentities2(deSlashMagic($_POST['title']));
$_POST['text'] = htmlentities2(deSlashMagic($_POST['text']));

if($_POST['action']=="Preview")
{
	if($_POST['text'])
	{
		$_POST['realtitle'] = $_POST['title']; //store the real PM title in another field...
		$_POST['num'] = "---";
		$_POST['posts'] = "---";
		$_POST['id'] = "???";
		$_POST['uid'] = $loguserid;
		$copies = explode(",","title,name,picture,sex,powerlevel,avatar,postheader,signature,regdate,lastactivity,lastposttime");
		foreach($copies as $toCopy)
			$_POST[$toCopy] = $loguser[$toCopy];
		MakePost($_POST, 0, 0);
		$_POST['title'] = $_POST['realtitle']; //and put it back for the form.
	}
}

if($_POST['text']) $prefill = htmlval(deSlashMagic($_POST['text']));
if($_POST['title']) $trefill = htmlval(deSlashMagic($_POST['title']));

if(!isset($_POST['iconid']))
	$_POST['iconid'] = 0;

DoSmileyBar();
DoPostHelp();

print "<form action=\"sendprivate.php\" method=\"post\">";
print "<table class=\"outline margin width50\">";
print "<tr class=\"cell0\">";
print "<td style=\"width: 25%;\">To</td>";
print "<td><input type=\"text\" name=\"to\" style=\"width: 98%;\" maxlength=\"60\" value=\"".$_POST['to']."\"></input></td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td style=\"width: 25%;\">Title</td>";
print "<td><input type=\"text\" name=\"title\" style=\"width: 98%;\" maxlength=\"60\" value=\"".deSlashMagic($trefill)."\"></input></td>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td>Post</td>";
print "<td>";
print "<textarea id=\"text\" name=\"text\" rows=\"16\" style=\"width: 98%;\">".$prefill."</textarea>";
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
print "</form>";

MakeCrumbs(array("Main"=>"./", "Private messages"=>"private.php", "New PM"=>""), "");

doFooter();

?>
