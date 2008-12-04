<?php
//  AcmlmBoard XD - Reply submission/preview page
//  Access: users

include("lib/common.php");

if(!$loguserid) //Not logged in?
	Kill("You must be logged in to post.");

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

if($forum['minpower'] > $loguser['powerlevel'])
	Kill("Your power is not enough.");

if($thread['closed'] && $loguser['powerlevel'] < 3)
	Kill("This thread is locked.");

MakeCrumbs(array("Main"=>"./", $forum['title']=>"forum.php?id=".$fid, $thread['title']=>"thread.php?id=".$tid, "New Reply"=>""), $links);

if($_POST['action']=="Post")
{
	if($_POST['text'])
	{
		$post = stripslashes($_POST['text']);
		$post = str_replace("\n","##TSURUPETTENYOUJO##", $post);
		TidyPost($post);
		$post = str_replace("##TSURUPETTENYOUJO##","\n", $post);
		$post = mysql_real_escape_string($post);

		if($thread['lastposter']==$loguserid && $thread['lastpostdate']>=time()-86400 && $loguser['powerlevel']<3)
			Kill("You can't double post until it's been at least one day.");

		$qThreads = "update threads set lastposter=".$loguserid.", lastpostdate=".time().", replies=".($thread['replies']+1)." where id=".$tid." limit 1";
		$rThreads = Query($qThreads);

		$qUsers = "update users set posts=".($loguser['posts']+1).", lastposttime=".time()." where id=".$loguserid." limit 1";
		$rUsers = Query($qUsers);

		$qPosts = "insert into posts (thread, user, date, ip, num) values (".$tid.",".$loguserid.",".time().",'".$_SERVER['REMOTE_ADDR']."',".($loguser['posts']+1).")";
		$rPosts = Query($qPosts);
		$pid = mysql_insert_id();

		$qPostsText = "insert into posts_text (pid,text) values (".$pid.",'".$post."')";
		$rPostsText = Query($qPostsText);

		$qFora = "update forums set numposts=".($forum['numposts']+1).", lastpostdate=".time().", lastpostuser=".$loguserid." where id=".$fid." limit 1";
		$rFora = Query($qFora);

		Redirect("Posted!","thread.php?pid=".$pid."#".$pid,"the thread.");
		exit();
	} else
		Alert("Enter a message and try again.","Your post is empty.");
}

if($_POST['text'])
{
	$prefill = stripslashes($_POST['text']);
	$prefill = str_replace("\n","##TSURUPETTENYOUJO##", $prefill);
	TidyPost($prefill);
	$prefill = str_replace("##TSURUPETTENYOUJO##","\n", $prefill);
}

if($_POST['action']=="Preview")
{
	if($_POST['text'])
	{
		$previewPost['text'] = $prefill;
		$previewPost['num'] = "???";
		$previewPost['id'] = "???";
		MakePost($previewPost, $loguser, 0, $fid);
	} else
		Alert("Enter a message and try again.","Your post is empty.");
}

if($_GET['quote'])
{
	$qQuote = "select * from posts left join posts_text on id=pid where id=".$_GET['quote'];
	$rQuote = Query($qQuote);
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
		if($forum['minpower'] > $loguser['powerlevel'])
			$quote['text'] = str_rot13("Pools closed due to not enough power. Prosecutors will be violated.");

		$qPoster = "select * from users where id=".$quote['user'];
		$rPoster = Query($qPoster);
		$poster = Fetch($rPoster);
		$prefill = "[quote=\"".$poster['name']."\" id=\"".$quote['id']."\"]".$quote['text']."[/quote]";
	}
}

print "<form action=\"newreply.php\" method=\"post\">";
print "<div class=\"border width50\">";
print "<table>";
print "<tr class=\"cell0\">";
print "<td><label for=\"text\">Post</label></td>";
print "<td>";
print "<textarea id=\"text\" name=\"text\" rows=\"16\" cols=\"60\">".$prefill."</textarea>";
print "</td>";
print "</tr>";
print "<tr class=\"cell2\">";
print "<td></td>";
print "<td>";
print "<input type=\"submit\" name=\"action\" value=\"Post\"></input>";
print "<input type=\"submit\" name=\"action\" value=\"Preview\"></input>";
print "<input type=\"hidden\" name=\"id\" value=\"".$tid."\"></input>";
print "</td>";
print "</tr>";
print "</table>";
print "</div>";
print "</form>";

//Revisions
$qPosts = "select * from posts where thread=".$tid." order by date desc";
//</revisions>
/*
//No revisions
$qPosts = "select * from posts left join posts_text on id=pid where thread=".$tid." order by date desc";
//</norevisions>
*/
$rPosts = Query($qPosts);
if(NumRows($rPosts))
{
	print "<div class=\"border\">";
	print "<table>";
	while($post = Fetch($rPosts))
	{
		$cellClass = ($cellClass+1) % 2;

//Revisions
		$qText = "select text,revision from posts_text where pid=".$post['id']." order by revision desc limit 1";
		$rText = Query($qText);
		$text = Fetch($rText);
		$post = array_merge($post, $text);
//</revisions>

		$qPoster = "select * from users where id=".$post['user'];
		$rPoster = Query($qPoster);
		$poster = Fetch($rPoster);
		print "<tr>";
		print "<td class=\"cell2\">".UserLink($poster)."</td>";
		print "<td class=\"cell".$cellClass."\">";
		print CleanUpPost($post['text']);
		print "</td>";
		print "</tr>";
	}
	print "</table>";
	print "</div>";
}

MakeCrumbs(array("Main"=>"./", $forum['title']=>"forum.php?id=".$fid, $thread['title']=>"thread.php?id=".$tid, "New Reply"=>""), $links);

doFooter();

?>
