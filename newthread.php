<?php
//  AcmlmBoard XD - Thread submission/preview page
//  Access: users

include("lib/common.php");

if(!$loguserid) //Not logged in?
	Kill("You must be logged in to post.");

if(isset($_POST['id']))
	$_GET['id'] = $_POST['id'];

if(!isset($_GET['id']))
	Kill("Forum ID unspecified.");

$fid = (int)$_GET['id'];

if($loguser['powerlevel'] == -1)
	Kill("You're banned.");

$qFora = "select * from forums where id=".$fid;
$rFora = Query($qFora);
if(NumRows($rFora))
	$forum = Fetch($rFora);
else
	Kill("Unknown forum ID.");

if($forum['locked'])
	Kill("This forum is locked.");

if($forum['minpowerthread'] > $loguser['powerlevel'])
	Kill("You are not allowed to post threads in this forum.");

if(!isset($_POST['poll']) || isset($_GET['poll']))
	$_POST['poll'] = $_GET['poll'];

if($_POST['poll'])
	MakeCrumbs(array("Main"=>"./", $forum['title']=>"forum.php?id=".$fid, "New Poll"=>""), $links);
else
	MakeCrumbs(array("Main"=>"./", $forum['title']=>"forum.php?id=".$fid, "New Thread"=>""), $links);

if(isset($_POST['title']))
	$_POST['title'] = htmlentities($_POST['title']);
if(isset($_POST['pollQuestion']))
	$_POST['pollQuestion'] = htmlentities(stripslashes($_POST['pollQuestion']))	;

if($_POST['action']=="Post")
{
	if($_POST['text'] && $_POST['title'])
	{
		$post = stripslashes($_POST['text']);
		$post = str_replace("\n","##TSURUPETTANYOUJO##", $post);
		TidyPost($post);
		$post = str_replace("##TSURUPETTANYOUJO##","\n", $post);
		$post = mysql_real_escape_string($post);

		$options = 0;
		if($_POST['nopl']) $options |= 1;

		if($_POST['iconid'])
		{
			if($_POST['iconid'] < 255)
				$iconurl = "img/icons/icon".$_POST['iconid'].".gif";
			else
				$iconurl = $_POST['iconurl'];
		}

		//	Guess who forgot to make sure not every thread was a poll? XD -- Arbe
		if($_POST['poll'])
		{
			$qPoll = "insert into poll (question) values ('".$_POST['pollQuestion']."')";
			$rPoll = Query($qPoll);
			$pod = mysql_insert_id();
			for($pops = 0; $pops < $_POST['pollOptions']; $pops++)
			{
				if($_POST['pollOption'.$pops])
				{
					$qPollOption = "insert into poll_choices (poll, choice, color) values (".$pod.", '".$_POST['pollOption'.$pops]."', '".$_POST['pollColor'.$pops]."')";
					$rPollOption = Query($qPollOption);
				}
			}
		}
		else
			$pod = 0;
		//Yeah, that was me ^^; -- Kawa

		$qThreads = "insert into threads (forum, user, title, icon, lastpostdate, lastposter, poll) values (".$fid.",".$loguserid.",'".$_POST['title']."','".$iconurl."',".time().",".$loguserid.", ".$pod.")";
		$rThreads = Query($qThreads);
		$tid = mysql_insert_id();

		$qUsers = "update users set posts=".($loguser['posts']+1).", lastposttime=".time()." where id=".$loguserid." limit 1";
		$rUsers = Query($qUsers);

		$qPosts = "insert into posts (thread, user, date, ip, num, options) values (".$tid.",".$loguserid.",".time().",'".$_SERVER['REMOTE_ADDR']."',".($loguser['posts']+1).", ".$options.")";
		$rPosts = Query($qPosts);
		$pid = mysql_insert_id();

		$qPostsText = "insert into posts_text (pid,text) values (".$pid.",'".$post."')";
		$rPostsText = Query($qPostsText);

		$qFora = "update forums set numthreads=".($forum['numthreads']+1).", numposts=".($forum['numposts']+1).", lastpostdate=".time().", lastpostuser=".$loguserid." where id=".$fid." limit 1";
		$rFora = Query($qFora);

		Redirect("Posted!","thread.php?id=".$tid,"the thread.");
		exit();
	} else
		Alert("Enter a message and try again.","Your post is empty.");
}

if($_POST['text'])
{
	$prefill = stripslashes($_POST['text']);
	$prefill = str_replace("\n","##TSURUPETTANYOUJO##", $prefill);
	TidyPost($prefill);
	$prefill = str_replace("##TSURUPETTANYOUJO##","\n", $prefill);
}
if($_POST['title'])
	$trefill = stripslashes($_POST['title']);

if($_POST['action']=="Preview")
{
	if($_POST['text'] && $_POST['title'])
	{
		$previewPost['text'] = $prefill;
		$previewPost['num'] = "???";
		$previewPost['id'] = "???";
		if($_POST['nopl']) $previewPost['options'] = 1;
		MakePost($previewPost, $loguser, 0, $fid);
	} else
		Alert("Enter a message and try again.","Your post is empty.");
}

if(!isset($_POST['iconid']))
	$_POST['iconid'] = 0;

if($_POST['nopl'])
	$nopl = "checked=\"checked\"";

print "<form action=\"newthread.php\" method=\"post\">";
print "<div class=\"border width50\">";
print "<table>";
print "<tr class=\"cell0\">";
print "<td><label for=\"tit\">Title</label></td>";
print "<td><input type=\"text\" id=\"tit\" name=\"title\" size=\"60\" maxlength=\"60\" value=\"".$trefill."\"></input></td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td>Icon</td>";
print "<td>";
$i = 1;
while(is_file("img/icons/icon".$i.".gif"))
{
	$check = "";
	if($_POST['iconid'] == $i) $check = "checked=\"checked\" ";
	print "<input type=\"radio\" ".$check."id=\"icon".$i."\" name=\"iconid\" value=\"".$i."\"></input><label for=\"icon".$i."\"><img src=\"img/icons/icon".$i.".gif\" alt=\"Icon ".$i."\" /></label>";
	if($i % 10 == 9) print "<br />";
	$i++;
}
$check = "";
if($_POST['iconid'] == 0) $check = "checked=\"checked\" ";
print "<br/><input type=\"radio\" ".$check."id=\"noicon\" name=\"iconid\" value=\"0\"></input><label for=\"noicon\">None</label>";
$check = "";
if($_POST['iconid'] == 255) $check = "checked=\"checked\" ";
print "<input type=\"radio\" ".$check."name=\"iconid\" value=\"255\"></input><label for=\"iconurl\">Custom</label>";
print "<input type=\"text\" id=\"iconurl\" name=\"iconurl\" size=\"40\" maxlength=\"100\" value=\"".$_POST['iconurl']."\"></input>";
print "</td>";
print "</tr>";

if($_POST['poll'])
{
	print "<tr class=\"cell0\">";
	print "<td><label for=\"pq\">Poll question</label></td>";
	print "<td><input type=\"text\" id=\"pq\" name=\"pollQuestion\" value=\"".$_POST['pollQuestion']."\" size=\"60\" maxlength=\"100\"></input></td>";
	print "</tr>";
	print "<tr class=\"cell1\">";
	print "<td><label for=\"pn\">Number of options</label></td>";
	print "<td><input type=\"text\" id=\"pn\" name=\"pollOptions\" value=\"".$_POST['pollOptions']."\" size=\"2\" maxlength=\"2\"></input></td>";
	print "</tr>";
	for($pops = 0; $pops < $_POST['pollOptions']; $pops++)
	{
		$cellClass = ($cellClass+1) % 2;

		$fixed = htmlentities(stripslashes($_POST['pollOption'.$pops]));

		print "<tr class=\"cell".$cellClass."\">";
		print "<td><label for=\"p".$pops."\">Option ".($pops+1)."</label></td>";
		print "<td><input type=\"text\" id=\"p".$pops."\" name=\"pollOption".$pops."\" value=\"".$fixed."\" size=\"40\" maxlength=\"40\"></input> <label for=\"c".$pops."\">Color</label> ";
		print "<input type=\"text\" id=\"c".$pops."\" name=\"pollColor".$pops."\" value=\"".$_POST['pollColor'.$pops]."\" size=\"10\" maxlength=\"10\"></input></td>";
		print "</tr>";
	}
}

print "<tr class=\"cell0\">";
print "<td><label for=\"post\">Post</label></td>";
print "<td>";
print "<textarea id=\"post\" name=\"text\" rows=\"16\" cols=\"60\">".$prefill."</textarea>";
print "</td>";
print "</tr>";
print "<tr class=\"cell2\">";
print "<td></td>";
print "<td>";
if(!$_POST['poll'] || $_POST['pollOptions'])
	print "<input type=\"submit\" name=\"action\" value=\"Post\"></input>";
print "<input type=\"submit\" name=\"action\" value=\"Preview\"></input>";
print " <input type=\"checkbox\" name=\"nopl\" id=\"np\" $nopl></input> <label for=\"np\">Disable post layout</label>";
print "<input type=\"hidden\" name=\"id\" value=\"".$fid."\"></input>";
print "<input type=\"hidden\" name=\"poll\" value=\"".$_POST['poll']."\"></input>";
print "</td>";
print "</tr>";
print "</table>";
print "</div>";
print "</form>";

if($_POST['poll'])
	MakeCrumbs(array("Main"=>"./", $forum['title']=>"forum.php?id=".$fid, "New Poll"=>""), $links);
else
	MakeCrumbs(array("Main"=>"./", $forum['title']=>"forum.php?id=".$fid, "New Thread"=>""), $links);

doFooter();

?>
