<?php
//  AcmlmBoard XD - Reply submission/preview page
//  Access: users

include("lib/common.php");

//print "<script type=\"text/javascript\">window.onload = function() { hookUpControls(); };</script>";

//if(!$loguserid) //Not logged in?
//	Kill("You must be logged in to post.");

if(isset($_POST['id']))
	$_GET['id'] = $_POST['id'];

if(!isset($_GET['id']))
	Kill("Thread ID unspecified.");

$tid = (int)$_GET['id'];

if($loguser['powerlevel'] == -1)
	Kill("You're banned.");

$qThread = "select * from threads where id=".$tid;
$rThread = Query($qThread);
if(NumRows($rThread))
{
	$thread = Fetch($rThread);
	$fid = $thread['forum'];
} else
	Kill("Unknown thread ID.");

$qFora = "select * from forums where id=".$fid;
$rFora = Query($qFora);
if(NumRows($rFora))
	$forum = Fetch($rFora);
else
	Kill("Unknown forum ID.");
$fid = $forum['id'];

if($forum['minpowerreply'] > $loguser['powerlevel'])
	Kill("Your power is not enough.");	

if($thread['closed'] && $loguser['powerlevel'] < 3)
	Kill("This thread is locked.");

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

MakeCrumbs(array("Main"=>"./", $forum['title']=>"forum.php?id=".$fid, $thread['title']=>"thread.php?id=".$tid, "New Reply"=>""), $links);

if($warnMonths > 0 && $thread['lastpostdate'] < time() - (2592000 * $warnMonths))
	Alert("You are about to bump an old thread. This is usually a very bad idea. Please think about what you are about to do before you press the Post button.");

if($_POST['text'] && CheckTableBreaks($_POST['text']))
{
	$_POST['action'] = "";
	Alert("This post would break the board layout.", "I'm sorry, Dave.");
}

if($_POST['text'])
{
	$words = explode(" ", $_POST['text']);
	$wordCount = count($words);
	if($wordCount < 2)
	{
		$_POST['action'] = "";
		Alert("Your post is too short to have any real meaning. Try a little harder.", "I'm sorry, Dave.");
	}
}

$ninja = FetchResult("select id from posts where thread=".$tid." order by date desc limit 0, 1",0,0);
if($_POST['action'] && isset($_POST['ninja']) && $_POST['ninja'] != $ninja)
{
	$_POST['action'] == "Preview";
	Alert("You got ninja'd. You might want to review the post made while you were typing before you submit yours.");
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

$postingAs = $loguserid;
$postingAsUser = $loguser;
if($_POST['username'] != "" && $_POST['password'] != "")
{
	//Entered another user's name and password. Look it up now.
	$qUser = "select * from users where name='".justEscape($_POST['username'])."' and password='".md5($_POST['password'])."'";
	$rUser = Query($qUser);
	if(NumRows($rUser))
	{
		$postingAsUser = Fetch($rUser);
		$postingAs = $postingAsUser['id'];
		$postingAsUser['uid'] = $postingAs;
	}
	else
	{
		Alert("Invalid user name or password.");
		$_POST['action'] = "";
		$_POST['password'] = "";
	}
}

if($_POST['action']=="Post")
{
	if($postingAs == 0)
		Kill("You must be logged in to post.");

	if($_POST['text'])
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

		if($thread['lastposter']==$postingAs && $thread['lastpostdate']>=time()-86400 && $postingAsUser['powerlevel']<3)
			Kill("You can't double post until it's been at least one day.");

		$qThreads = "update threads set lastposter=".$postingAs.", lastpostdate=".time().", replies=".($thread['replies']+1)." where id=".$tid." limit 1";
		$rThreads = Query($qThreads);

		$qUsers = "update users set posts=".($postingAsUser['posts']+1).", lastposttime=".time()." where id=".$postingAs." limit 1";
		$rUsers = Query($qUsers);

		$qPosts = "insert into posts (thread, user, date, ip, num, options, mood) values (".$tid.",".$postingAs.",".time().",'".$_SERVER['REMOTE_ADDR']."',".($postingAsUser['posts']+1).", ".$options.", ".(int)$_POST['mood'].")";
		$rPosts = Query($qPosts);
		$pid = mysql_insert_id();

		$qPostsText = "insert into posts_text (pid,text) values (".$pid.",'".$post."')";
		$rPostsText = Query($qPostsText);

		$qFora = "update forums set numposts=".($forum['numposts']+1).", lastpostdate=".time().", lastpostuser=".$postingAs.", lastpostid=".$pid." where id=".$fid." limit 1";
		$rFora = Query($qFora);

		Redirect("Posted!","thread.php?pid=".$pid."#".$pid,"the thread");
		exit();
	} else
		Alert("Enter a message and try again.","Your post is empty.");
}

if($_POST['text'])
{
	//$prefill = htmlentities2(stripslashes($_POST['text']));
	$prefill = htmlentities2(deSlashMagic($_POST['text']));
	//$prefill = str_replace("\n","##TSURUPETTANYOUJO##", $prefill);
	//TidyPost($prefill);
	//$prefill = str_replace("##TSURUPETTANYOUJO##","\n", $prefill);
}

if($_POST['action']=="Preview")
{
	if($_POST['text'])
	{
		$previewPost['text'] = $prefill;
		$previewPost['num'] = $postingAsUser['posts']+1;
		$previewPost['posts'] = $postingAsUser['posts']+1;
		$previewPost['id'] = "???";
		$previewPost['uid'] = $postingAs;
		$copies = explode(",","title,name,picture,sex,powerlevel,avatar,postheader,signature,regdate,lastactivity,lastposttime");
		foreach($copies as $toCopy)
			$previewPost[$toCopy] = $postingAsUser[$toCopy];
		$previewPost['mood'] = (int)$_POST['mood'];
		$previewPost['options'] = 0;
		if($_POST['nopl']) $previewPost['options'] |= 1;
		if($_POST['nosm']) $previewPost['options'] |= 2;
		MakePost($previewPost, 0, $fid);
	} else
		Alert("Enter a message and try again.","Your post is empty.");
}

if(!$_POST['text']) $_POST['text'] = $post['text'];
if($_POST['text']) $prefill = htmlval(deSlashMagic($_POST['text']));

if($_GET['link'])
{
	$prefill = ">>".(int)$_GET['link']."\r\n\r\n";
}
else if($_GET['quote'])
{
	$qQuote = "select * from posts left join posts_text on posts_text.pid = posts.id and posts_text.revision = posts.currentrevision where id=".$_GET['quote'];
	$rQuote = Query($qQuote);
	
//	$qPost = "select * from posts where id=".(int)$_GET['quote'];
//	$rPost = Query($qPost);
//	$post = Fetch($rPost);
//	$qText = "select text,revision from posts_text where pid=".$post['id']." order by revision desc limit 1";
//	$rText = Query($qText);
//	$text = Fetch($rText);
//	$quote = array_merge($post, $text);

	if(NumRows($rQuote))
	{
		$quote = Fetch($rQuote);

		//SPY CHECK!
		$qThread = "select forum from threads where id=".$quote['thread'];
		$rThread = Query($qThread);
		$thread = Fetch($rThread);
		$qForum = "select minpower from forums where id=".$thread['forum'];
		$rForum = Query($qForum);
		$forum = Fetch($rForum);
		if($forum['minpower'] > $postingAsUser['powerlevel'])
			$quote['text'] = str_rot13("Pools closed due to not enough power. Prosecutors will be violated.");

		$qPoster = "select * from users where id=".$quote['user'];
		$rPoster = Query($qPoster);
		$poster = Fetch($rPoster);
		$prefill = "[quote=\"".$poster['name']."\" id=\"".$quote['id']."\"]".htmlval($quote['text'])."[/quote]";
		$prefill = str_replace("/me", "[b]* ".$poster['name']."[/b]", $prefill);
	}
}

if($_POST['nopl'])
	$nopl = "checked=\"checked\"";
if($_POST['nosm'])
	$nosm = "checked=\"checked\"";

DoSmileyBar();
DoPostHelp();

if($_POST['mood'])
	$moodSelects[(int)$_POST['mood']] = "selected=\"selected\" ";
$moodOptions = "<option ".$moodSelects[0]."value=\"0\">[Default avatar]</option>\n";
$rMoods = Query("select mid, name from moodavatars where uid=".$postingAs." order by mid asc");
while($mood = Fetch($rMoods))
	$moodOptions .= "<option ".$moodSelects[$mood['mid']]."value=\"".$mood['mid']."\">".$mood['name']."</option>\n";

$ninja = FetchResult("select id from posts where thread=".$tid." order by date desc limit 0, 1",0,0);

print "<form action=\"newreply.php\" method=\"post\">";
print "<input type=\"hidden\" name=\"ninja\" value=\"".$ninja."\">";
print "<table class=\"outline margin width50\">";
print "<tr class=\"cell0\">";
print "<td><label for=\"uname\">User name</label></td>";
print "<td><input type=\"text\" id=\"uname\" name=\"username\" value=\"".htmlval($postingAsUser['name'])."\" style=\"width: 98%;\" maxlength=\"25\" /></td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td><label for=\"upass\">Password</label></td>";
print "<td><input type=\"password\" id=\"upass\" name=\"password\" value=\"".$_POST['password']."\" size=\"13\" maxlength=\"13\" /></td>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td><label for=\"text\">Post</label></td>";
print "<td>";
print "<textarea id=\"text\" name=\"text\" rows=\"16\" style=\"width: 98%;\">".$prefill."</textarea>";
print "</td>";
print "</tr>";
print "<tr class=\"cell2\">";
print "<td></td>";
print "<td>";
print "<input type=\"submit\" name=\"action\" value=\"Post\"></input> ";
print "<input type=\"submit\" name=\"action\" value=\"Preview\"></input>";
print " <select size=\"1\" name=\"mood\">".$moodOptions."</select>";
print " <input type=\"checkbox\" name=\"nopl\" id=\"np\" $nopl></input> <label for=\"np\">Disable post layout</label>";
print " <input type=\"checkbox\" name=\"nosm\" id=\"ns\" $nosm></input> <label for=\"ns\">Disable smilies</label>";
print "<input type=\"hidden\" name=\"id\" value=\"".$tid."\"></input>";
print "</td>";
print "</tr>";
print "</table>";
print "</form>";

//$qPosts = "select * from posts left join posts_text on pid=id where revision = currentrevision and thread=".$tid." and deleted=0 order by date desc limit 0,20";
$qPosts = "select ";
$qPosts .=
	"posts.id, posts.date, posts.num, posts.deleted, posts.options, posts.mood, posts.ip, posts_text.text, posts_text.text, posts_text.revision, users.id as uid, users.name, users.rankset, users.powerlevel, users.sex, users.posts";
$qPosts .= 
	" from posts left join posts_text on posts_text.pid = posts.id and posts_text.revision = posts.currentrevision left join users on users.id = posts.user";
$qPosts .= " where thread=".$tid." and deleted=0 order by date desc limit 0, 20";

$rPosts = Query($qPosts);
if(NumRows($rPosts))
{
	print "<table class=\"outline margin\">";
	while($post = Fetch($rPosts))
	{
		$cellClass = ($cellClass+1) % 2;

		$poster = $post;
		$poster['id'] = $post['uid'];

		print "<tr>";
		print "<td class=\"cell2\">".UserLink($poster)."</td>";
		print "<td class=\"cell".$cellClass."\">";
		print "<button style=\"float: right;\" onclick=\"insertQuote(".$post['id'].");\">Quote</button>";
		print "<button style=\"float: right;\" onclick=\"insertChanLink(".$post['id'].");\">Link</button>";
		print CleanUpPost($post['text'], $poster['name']);
		print "</td>";
		print "</tr>";
	}
	print "</table>";
}

MakeCrumbs(array("Main"=>"./", $forum['title']=>"forum.php?id=".$fid, $thread['title']=>"thread.php?id=".$tid, "New Reply"=>""), $links);

doFooter();

?>
