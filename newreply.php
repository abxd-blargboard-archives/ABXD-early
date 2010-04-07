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

if($thread['lastpostdate'] < time() - (2592000 * $autoLockMonths))
	Alert("You are about to bump an old thread. This is usually a very bad idea. Please think about what you are about to do before you press the Post button.");

if($_POST['action']=="Post")
{
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

		if($thread['lastposter']==$loguserid && $thread['lastpostdate']>=time()-86400 && $loguser['powerlevel']<3)
			Kill("You can't double post until it's been at least one day.");

		$qThreads = "update threads set lastposter=".$loguserid.", lastpostdate=".time().", replies=".($thread['replies']+1)." where id=".$tid." limit 1";
		$rThreads = Query($qThreads);

		$qUsers = "update users set posts=".($loguser['posts']+1).", lastposttime=".time()." where id=".$loguserid." limit 1";
		$rUsers = Query($qUsers);

		$qPosts = "insert into posts (thread, user, date, ip, num, options) values (".$tid.",".$loguserid.",".time().",'".$_SERVER['REMOTE_ADDR']."',".($loguser['posts']+1).", ".$options.")";
		$rPosts = Query($qPosts);
		$pid = mysql_insert_id();

		$qPostsText = "insert into posts_text (pid,text) values (".$pid.",'".$post."')";
		$rPostsText = Query($qPostsText);

		$qFora = "update forums set numposts=".($forum['numposts']+1).", lastpostdate=".time().", lastpostuser=".$loguserid." where id=".$fid." limit 1";
		$rFora = Query($qFora);

		Report("<message><action type=\"newreply\" id=\"".$pid."\" /><user id=\"".$loguserid."\">".$loguser['name']."</user><thread id=\"".$tid."\">".$thread['title']."</thread><forum id=\"".$fid."\">".$forum['title']."</forum></message>");
		Redirect("Posted!","thread.php?pid=".$pid."#".$pid,"the thread");
		exit();
	} else
		Alert("Enter a message and try again.","Your post is empty.");
}

if($_POST['text'])
{
	//$prefill = htmlentities2(stripslashes($_POST['text']));
	$prefill = htmlentities2(deSlashMagic($_POST['text']));
	$prefill = str_replace("\n","##TSURUPETTANYOUJO##", $prefill);
	TidyPost($prefill);
	$prefill = str_replace("##TSURUPETTANYOUJO##","\n", $prefill);
}

if($_POST['action']=="Preview")
{
	if($_POST['text'])
	{
		$previewPost['text'] = $prefill;
		$previewPost['num'] = "???";
		$previewPost['id'] = "???";
		if($_POST['nopl']) $previewPost['options'] = 1;
		MakePost($previewPost, $loguser, 0, $fid);
	} else
		Alert("Enter a message and try again.","Your post is empty.");
}

if(!$_POST['text']) $_POST['text'] = $post['text'];
if($_POST['text']) $prefill = htmlval(stripslashes($_POST['text']));

if($_GET['quote'])
{
	//$qQuote = "select * from posts left join posts_text on id=pid where id=".$_GET['quote'];
	//$rQuote = Query($qQuote);
	
	$qPost = "select * from posts where id=".$_GET['quote'];
	$rPost = Query($qPost);
	$post = Fetch($rPost);
	$qText = "select text,revision from posts_text where pid=".$post['id']." order by revision desc limit 1";
	$rText = Query($qText);
	$text = Fetch($rText);
	$quote = array_merge($post, $text);

//	if(NumRows($rQuote))
	{
//		$quote = Fetch($rQuote);

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

if($_POST['nopl'])
	$nopl = "checked=\"checked\"";

DoSmileyBar();
DoPostHelp();

print "<form action=\"newreply.php\" method=\"post\">";
print "<table class=\"outline margin width50\">";
print "<tr class=\"cell0\">";
print "<td><label for=\"text\">Post</label></td>";
print "<td>";
print "<textarea id=\"text\" name=\"text\" rows=\"16\" style=\"width: 98%;\">".$prefill."</textarea>";
print "</td>";
print "</tr>";
print "<tr class=\"cell2\">";
print "<td></td>";
print "<td>";
print "<input type=\"submit\" name=\"action\" value=\"Post\"></input>";
print "<input type=\"submit\" name=\"action\" value=\"Preview\"></input>";
print " <input type=\"checkbox\" name=\"nopl\" id=\"np\" $nopl></input> <label for=\"np\">Disable post layout</label>";
print "<input type=\"hidden\" name=\"id\" value=\"".$tid."\"></input>";
print "</td>";
print "</tr>";
print "</table>";
print "</form>";

//Revisions
$qPosts = "select * from posts where thread=".$tid." order by date desc limit 0,20";
//</revisions>
/*
//No revisions
$qPosts = "select * from posts left join posts_text on id=pid where thread=".$tid." order by date desc";
//</norevisions>
*/
$rPosts = Query($qPosts);
if(NumRows($rPosts))
{
	print "<table class=\"outline margin\">";
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
		print "<button style=\"float: right;\" onclick=\"insertQuote(".$post['id'].");\">Quote</button>";
		print CleanUpPost($post['text']);
		print "</td>";
		print "</tr>";
	}
	print "</table>";
}

MakeCrumbs(array("Main"=>"./", $forum['title']=>"forum.php?id=".$fid, $thread['title']=>"thread.php?id=".$tid, "New Reply"=>""), $links);

doFooter();

function htmlval($text)
{
	//$text = str_replace("\"", "&quot;", $text);
	//$text = str_replace("<", "&lt;", $text);
	//return $text;
	return htmlentities($text, ENT_COMPAT, "UTF-8");
}

?>
