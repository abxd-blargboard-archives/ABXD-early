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

$onlineUsers = OnlineUsers($fid);
?>
<script type="text/javascript" src="lib/Tween.js"></script>
<script type="text/javascript" src="lib/OpacityTween.js"></script>
<script type="text/javascript">
	window.onload = function() { startOnlineUsers(<?php print $fid; ?>); };
</script>
<div class="header0 cell1 center outline smallFonts" style="overflow: auto;">
<span id="onlineUsers">
<?php print $onlineUsers; ?>
</span>
</div>
<?php

if($_POST['poll'])
	MakeCrumbs(array("Main"=>"./", $forum['title']=>"forum.php?id=".$fid, "New Poll"=>""), $links);
else
	MakeCrumbs(array("Main"=>"./", $forum['title']=>"forum.php?id=".$fid, "New Thread"=>""), $links);

if(isset($_POST['title']))
	$_POST['title'] = htmlentities2($_POST['title']);
if(isset($_POST['pollQuestion']))
	$_POST['pollQuestion'] = htmlentities2($_POST['pollQuestion']);
	//$_POST['pollQuestion'] = htmlentities2(stripslashes($_POST['pollQuestion']));

if($_POST['text'] && CheckTableBreaks($_POST['text']))
{
	$_POST['action'] = "";
	Alert("This post would break the board layout.", "I'm sorry, Dave.");
}

if($_POST['text'] && $_POST['action'] != "")
{
	$words = explode(" ", $_POST['text']);
	$wordCount = count($words);
	if($wordCount < 2)
	{
		$_POST['action'] = "";
		Alert("Your post is too short to have any real meaning. Try a little harder.", "I'm sorry, Dave.");
	}
}

if($_POST['text'] && $_POST['action'] == "Post")
{
	$lastPost = time() - $loguser['lastposttime'];
	if($lastPost < 30)
	{
		$_POST['action'] = "";
		Alert("You're going too damn fast! Slow down a little.", "Hold your horses.");
	}
}

if($_POST['action']=="Post")
{
	if($_POST['text'] && $_POST['title'])
	{
		//$post = htmlentities2(stripslashes($_POST['text']));
		$post = htmlentities2($_POST['text']);
		$post = str_replace("\n","##TSURUPETTANYOUJO##", $post);
		TidyPost($post);
		$post = str_replace("##TSURUPETTANYOUJO##","\n", $post);
		$post = justEscape($post);

		$options = 0;
		if($_POST['nopl']) $options |= 1;
		if($_POST['nosm']) $options |= 2;

		if($_POST['iconid'])
		{
			$_POST['iconid'] = (int)$_POST['iconid'];
			if($_POST['iconid'] < 255)
				$iconurl = "img/icons/icon".$_POST['iconid'].".png";
			else
				$iconurl = justEscape($_POST['iconurl']);
		}

		//	Guess who forgot to make sure not every thread was a poll? XD -- Arbe
		if($_POST['poll'])
		{
			$qPoll = "insert into poll (question) values ('".justEscape($_POST['pollQuestion'])."')";
			$rPoll = Query($qPoll);
			$pod = mysql_insert_id();
			for($pops = 0; $pops < $_POST['pollOptions']; $pops++)
			{
				if($_POST['pollOption'.$pops])
				{
					$pollColor = filterPollColors($_POST['pollColor'.$pops]);
					$qPollOption = "insert into poll_choices (poll, choice, color) values (".$pod.", '".justEscape($_POST['pollOption'.$pops])."', '".$pollColor."')";
					$rPollOption = Query($qPollOption);
				}
			}
		}
		else
			$pod = 0;
		//Yeah, that was me ^^; -- Kawa

		$qThreads = "insert into threads (forum, user, title, icon, lastpostdate, lastposter, poll) values (".$fid.",".$loguserid.",'".justEscape($_POST['title'])."','".$iconurl."',".time().",".$loguserid.", ".$pod.")";
		$rThreads = Query($qThreads);
		$tid = mysql_insert_id();

		$qUsers = "update users set posts=".($loguser['posts']+1).", lastposttime=".time()." where id=".$loguserid." limit 1";
		$rUsers = Query($qUsers);

		$qPosts = "insert into posts (thread, user, date, ip, num, options, mood) values (".$tid.",".$loguserid.",".time().",'".$_SERVER['REMOTE_ADDR']."',".($loguser['posts']+1).", ".$options.", ".(int)$_POST['mood'].")";
		$rPosts = Query($qPosts);
		$pid = mysql_insert_id();

		$qPostsText = "insert into posts_text (pid,text) values (".$pid.",'".$post."')";
		$rPostsText = Query($qPostsText);

		$qFora = "update forums set numthreads=".($forum['numthreads']+1).", numposts=".($forum['numposts']+1).", lastpostdate=".time().", lastpostuser=".$loguserid.", lastpostid=".$pid." where id=".$fid." limit 1";
		$rFora = Query($qFora);

		Redirect("Posted!","thread.php?id=".$tid,"the thread");
		exit();
	} else
	{
		if($_POST['title'])
			Alert("Enter a message and try again.","Your post is empty.");
		else if($_POST['text'])
			Alert("Enter a thread title and try again.","Your thread is unnamed.");
		else
			Alert("Enter a message and a thread title and try again.","Your post is empty.");			
	}
}

if($_POST['text'])
{
	//$prefill = htmlentities2(stripslashes($_POST['text']));
	$prefill = htmlentities2(deSlashMagic($_POST['text']));
	$prefill = str_replace("\n","##TSURUPETTANYOUJO##", $prefill);
	TidyPost($prefill);
	$prefill = str_replace("##TSURUPETTANYOUJO##","\n", $prefill);
}
if($_POST['title'])
	$trefill = htmlentities2(deSlashMagic($_POST['title']));
	//$trefill = htmlentities2(stripslashes($_POST['title']));

if($_POST['action']=="Preview")
{
	if($_POST['text'] && $_POST['title'])
	{
		//print_r($_POST);
		if($_POST['poll'])
		{
			print "<table class=\"outline margin\">";
			print "<tr class=\"header0\">";
			print "<th colspan=\"2\">Preview</th>";
			print "</tr>";
			print "<tr class=\"cell0\">";
			print "<td colspan=\"2\">".$_POST['pollQuestion']."</td>";
			print "</tr>";
			$options = array();
			$noColors = 0;
			$defaultColors = array(
				"#000000","#0000B6","#00B600","#00B6B6","#B60000","#B600B6","#B66700","#B6B6B6",
				"#676767","#6767FF","#67FF67","#67FFFF","#FF6767","#FF67FF","#FFFF67","#FFFFFF",);
			for($i = 0; $i < $_POST['pollOptions']; $i++)
			{
				$options[] = array("choice"=>$_POST['pollOption'.$i], "color"=>$_POST['pollColor'.$i]);
			}
			$totalVotes = count($options);
			foreach($options as $option)
			{
				if($option['color'] == "")
					$option['color'] = $defaultColors[($pops + 9) % 16];
				
				$votes = 1;

				$cellClass = ($cellClass+1) % 2;
				print "<tr class=\"cell".$cellClass."\">";
				if($loguserid && !$thread['closed'])
					print "<td>".$option['choice']."</td>";
				else
					print "<td>".$pc[$pops].$option['choice']."</td>";
				print "<td><img src=\"img/bargraph.php?z=".$votes."&amp;n=".$totalVotes."&amp;c=".str_replace("#","",$option['color'])."\" title=\"".$votes." of ".$totalVotes."\" alt=\"\" /></td>";
				print "</tr>";
				$pops++;
			}

			print "</table>";
		}
	
		$previewPost['text'] = $prefill;
		$previewPost['num'] = $loguser['posts']+1;
		$previewPost['posts'] = $loguser['posts']+1;
		$previewPost['id'] = "???";
		$previewPost['options'] = 0;
		if($_POST['nopl']) $previewPost['options'] |= 1;
		if($_POST['nosm']) $previewPost['options'] |= 2;
		$previewPost['mood'] = (int)$_POST['mood'];
		$previewPost['uid'] = $loguserid;
		$copies = explode(",","title,name,picture,sex,powerlevel,avatar,postheader,signature,regdate,lastactivity,lastposttime");
		foreach($copies as $toCopy)
			$previewPost[$toCopy] = $loguser[$toCopy];
		MakePost($previewPost, 0, $fid);
	} else
	{
		if($_POST['title'])
			Alert("Enter a message and try again.","Your post is empty.");
		else if($_POST['text'])
			Alert("Enter a thread title and try again.","Your thread is unnamed.");
		else
			Alert("Enter a message and a thread title and try again.","Your post is empty.");			
	}
}

if(!$_POST['text']) $_POST['text'] = $post['text'];
if($_POST['text']) $prefill = htmlval(deSlashMagic($_POST['text']));
if($_POST['title']) $trefill = htmlval(deSlashMagic($_POST['title']));

if(!isset($_POST['iconid']))
	$_POST['iconid'] = 0;

if($_POST['nopl'])
	$nopl = "checked=\"checked\"";
if($_POST['nosm'])
	$nosm = "checked=\"checked\"";

DoSmileyBar();
DoPostHelp();

print "<script type=\"text/javascript\" src=\"lib/jscolor/jscolor.js\"></script>";
print "<form action=\"newthread.php\" method=\"post\">";
print "<table class=\"outline margin width50\">";
print "<tr class=\"cell0\">";
print "<td><label for=\"tit\">Title</label></td>";
print "<td><input type=\"text\" id=\"tit\" name=\"title\" style=\"width: 98%;\" maxlength=\"60\" value=\"".$trefill."\"></input></td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td>Icon</td>";
print "<td>";
$i = 1;
while(is_file("img/icons/icon".$i.".png"))
{
	$check = "";
	if($_POST['iconid'] == $i) $check = "checked=\"checked\" ";
	print "<input type=\"radio\" ".$check."id=\"icon".$i."\" name=\"iconid\" value=\"".$i."\"></input> <label for=\"icon".$i."\"><img src=\"img/icons/icon".$i.".png\" alt=\"Icon ".$i."\" /></label> ";
	//if($i % 12 == 13) print "<br />";
	$i++;
}
$check = "";
if($_POST['iconid'] == 0) $check = "checked=\"checked\" ";
print "<br/><input type=\"radio\" ".$check."id=\"noicon\" name=\"iconid\" value=\"0\"></input> <label for=\"noicon\">None</label> ";
$check = "";
if($_POST['iconid'] == 255) $check = "checked=\"checked\" ";
print "<input type=\"radio\" ".$check."name=\"iconid\" value=\"255\"></input> <label for=\"iconurl\">Custom</label> ";
print "<input type=\"text\" id=\"iconurl\" name=\"iconurl\" style=\"width: 98%;\" maxlength=\"100\" value=\"".htmlval(deSlashMagic($_POST['iconurl']))."\"></input>";
print "</td>";
print "</tr>";

if($_POST['poll'])
{
	print "<tr class=\"cell0\">";
	print "<td><label for=\"pq\">Poll question</label></td>";
	print "<td><input type=\"text\" id=\"pq\" name=\"pollQuestion\" value=\"".htmlval(deSlashMagic($_POST['pollQuestion']))."\" style=\"width: 98%;\" maxlength=\"100\"></input></td>";
	print "</tr>";
	print "<tr class=\"cell1\">";
	print "<td><label for=\"pn\">Number of options</label></td>";
	print "<td><input type=\"text\" id=\"pn\" name=\"pollOptions\" value=\"".$_POST['pollOptions']."\" size=\"2\" maxlength=\"2\"></input></td>";
	print "</tr>";
	$first = true;
	for($pops = 0; $pops < $_POST['pollOptions']; $pops++)
	{
		$cellClass = ($cellClass+1) % 2;

		$fixed = htmlval(deSlashMagic($_POST['pollOption'.$pops]));

		print "<tr class=\"cell".$cellClass."\">";
		print "<td><label for=\"p".$pops."\">Option ".($pops+1)."</label></td>";
		print "<td><input type=\"text\" id=\"p".$pops."\" name=\"pollOption".$pops."\" value=\"".$fixed."\" style=\"width: 50%;\" maxlength=\"40\"></input>&nbsp;<label for=\"c".$pops."\">Color</label>&nbsp;";
		print "<input type=\"text\" id=\"c".$pops."\" name=\"pollColor".$pops."\" value=\"".filterPollColors($_POST['pollColor'.$pops])."\" size=\"10\" maxlength=\"10\" class=\"color {hash:true,required:false,pickerFaceColor:'black',pickerFace:3,pickerBorder:0,pickerInsetColor:'black',pickerPosition:'left'}\"></input>".($first ? "&nbsp;(#rrggbb)" : "")."</td>";
		print "</tr>";
		$first = false;
	}
}

if($_POST['mood'])
	$moodSelects[(int)$_POST['mood']] = "selected=\"selected\" ";
$moodOptions = "<option ".$moodSelects[0]."value=\"0\">[Default avatar]</option>\n";
$rMoods = Query("select mid, name from moodavatars where uid=".$loguserid." order by mid asc");
while($mood = Fetch($rMoods))
	$moodOptions .= "<option ".$moodSelects[$mood['mid']]."value=\"".$mood['mid']."\">".$mood['name']."</option>\n";

print "<tr class=\"cell0\">";
print "<td><label for=\"post\">Post</label></td>";
print "<td>";
print "<textarea id=\"text\" name=\"text\" rows=\"16\" style=\"width: 98%;\">".$prefill."</textarea>";
print "</td>";
print "</tr>";
print "<tr class=\"cell2\">";
print "<td></td>";
print "<td>";
if(!$_POST['poll'] || $_POST['pollOptions'])
	print "<input type=\"submit\" name=\"action\" value=\"Post\"></input> ";
print "<input type=\"submit\" name=\"action\" value=\"Preview\"></input>";
print " <select size=\"1\" name=\"mood\">".$moodOptions."</select>";
print " <input type=\"checkbox\" name=\"nopl\" id=\"np\" $nopl></input> <label for=\"np\">Disable post layout</label>";
print " <input type=\"checkbox\" name=\"nosm\" id=\"ns\" $nosm></input> <label for=\"ns\">Disable smilies</label>";
print "<input type=\"hidden\" name=\"id\" value=\"".$fid."\"></input>";
print "<input type=\"hidden\" name=\"poll\" value=\"".stripslashes($_POST['poll'])."\"></input>";
print "</td>";
print "</tr>";
print "</table>";
print "</form>";

if($_POST['poll'])
	MakeCrumbs(array("Main"=>"./", $forum['title']=>"forum.php?id=".$fid, "New Poll"=>""), $links);
else
	MakeCrumbs(array("Main"=>"./", $forum['title']=>"forum.php?id=".$fid, "New Thread"=>""), $links);

doFooter();

?>