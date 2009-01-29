<?php
//  AcmlmBoard XD - Post editing page
//  Access: users

include("lib/common.php");

if(!$loguserid)
	Kill("You must be logged in to edit your posts.");

if($loguser['powerlevel'] < 0)
	Kill("Banned users can't edit their posts.");

if(isset($_POST['id']))
	$_GET['id'] = $_POST['id'];

if(!isset($_GET['id']))
	Kill("Post ID unspecified.");

$pid = (int)$_GET['id'];

/*
//No revisions
$qPost = "select * from posts left join posts_text on pid=id where id=".$pid;
$rPost = Query($qPost);
if(NumRows($rPost))
{
	$post = Fetch($rPost);
	$tid = $post['thread'];
} else
	Kill("Unknown post ID.");
//</norevisions>
*/
//Revisions
$qPost = "select * from posts where id=".$pid;
$rPost = Query($qPost);
if(NumRows($rPost))
{
	$post = Fetch($rPost);
	$tid = $post['thread'];
	$qText = "select text,revision from posts_text where pid=".$pid." order by revision desc limit 1";
	$rText = Query($qText);
	$text = Fetch($rText);
	$post = array_merge($post, $text);
} else
	Kill("Unknown post ID.");
//</revisions>

$qThread = "select * from threads where id=".$tid;
$rThread = Query($qThread);
if(NumRows($rThread))
{
	$thread = Fetch($rThread);
} else
{
	Kill("Unknown thread ID.");
}

$fid = $thread['forum'];

/*
//Deletion behavior B -- post is actually removed from the DB.
if($_GET['delete'])
{
	if(!CanMod($loguserid, $fid))
		Kill("You're not allowed to delete posts.");
	$qPosts = "delete from posts where id=".$pid." limit 1";
	$qPostsText = "delete from posts_text where pid=".$pid." limit 1";
	$qThreads = "update threads set replies=replies-1 where id=".$tid." limit 1";
	$qFora = "update forums set numposts=numposts-1 where id=".$fid." limit 1";
	$qUsers = "update users set posts=posts-1 where id=".$post['user']." limit 1";
	$rPosts = Query($qPosts);
	$rPostsText = Query($qPostsText);
	$rThreads = Query($qThreads);
	$rFora = Query($qFora);
	$rUsers = Query($qUsers);
	Redirect("Deleted!","thread.php?id=".$tid,"the thread");
	exit();
}
//</Deletion method B>
*/
//Deletion behavior A -- post is simply made invisible.
if($_GET['delete']==1)
{
	if(!CanMod($loguserid,$fid))
		Kill("You're not allowed to delete posts.");
	$qPosts = "update posts set deleted=1 where id=".$pid." limit 1";
	$rPosts = Query($qPosts);
	Redirect("Deleted!","thread.php?id=".$tid,"the thread");
	exit();
} elseif($_GET['delete']==2)
{
	if(!CanMod($loguserid,$fid))
		Kill("You're not allowed to undelete posts.");
	$qPosts = "update posts set deleted=0 where id=".$pid." limit 1";
	$rPosts = Query($qPosts);
	Redirect("Restored!","thread.php?id=".$tid,"the thread");
	exit();
}
//</Deletion method A>

if(!CanMod($loguserid, $fid) && $post['user'] != $loguserid)
	Kill("You are not allowed to edit posts.");

if($thread['locked'])
	Kill("This thread is locked.");

if($_POST['action']=="Edit")
{
	if($_POST['text'])
	{
		$post = htmlentities2(stripslashes($_POST['text']));
		$post = str_replace("\n","##TSURUPETTANYOUJO##", $post);
		TidyPost($post);
		$post = str_replace("##TSURUPETTANYOUJO##","\n", $post);
		$post = mysql_real_escape_string($post);

		$options = 0;
		if($_POST['nopl']) $options |= 1;

/*
//No revisions
		$qPostsText = "update posts_text set text='".$post."' where pid=".$pid." limit 1";
		$rPostsText = Query($qPostsText);
//</norevisions>
*/
//Revisions
		$qRev = "select max(revision) from posts_text where pid=".$pid;
		$rRev = Query($qRev);
		$rev = Fetch($rRev);
		$rev = $rev[0]; //note: no longer a fetched row.
		$rev++;
		$qPostsText = "insert into posts_text (pid,text,revision) values (".$pid.", '".$post."', ".$rev.")";
		$rPostsText = Query($qPostsText);
//</revisions>

		$qPosts = "update posts set options='".$options."' where id=".$pid." limit 1";
		$rPosts = Query($qPosts);

		Redirect("Edited!","thread.php?id=".$tid,"the thread");
		exit();
	} else
		Alert("Enter a message and try again.","Your post is empty.");
}

if($_POST['text'])
{
	$prefill = htmlentities2(stripslashes($_POST['text']));
	$prefill = str_replace("\n","##TSURUPETTANYOUJO##", $prefill);
	TidyPost($prefill);
	$prefill = str_replace("##TSURUPETTANYOUJO##","\n", $prefill);
}

if($_POST['action']=="Preview")
{
	$qUser = "select * from users where id=".$post['user'];
	$rUser = Query($qUser);
	if(NumRows($rUser))
		$user = Fetch($rUser);
	else
		Kill("Unknown user ID.");

	if($_POST['text'])
	{
		$previewPost['text'] = $prefill;
		$previewPost['num'] = $post['num'];
		$previewPost['id'] = $pid;
		if($_POST['nopl']) $previewPost['options'] = 1;
		MakePost($previewPost, $user, 0, $fid);
	} else
		Alert("Enter a message and try again.","Your post is empty.");
}

if(!$_POST['text']) $_POST['text'] = stripslashes($post['text']);

if($_POST['text']) $prefill = stripslashes($_POST['text']);

if($_POST['nopl'])
	$nopl = "checked=\"checked\"";

DoSmileyBar();

print "<form action=\"editpost.php\" method=\"post\">";
print "<div class=\"border width50\">";
print "<table>";
print "<tr class=\"cell0\">";
print "<td>Post</td>";
print "<td>";
print "<textarea id=\"text\" name=\"text\" rows=\"16\" style=\"width: 98%;\">".$prefill."</textarea><br />";
print "</td>";
print "</tr>";
print "<tr class=\"cell2\">";
print "<td></td>";
print "<td>";
print "<input type=\"submit\" name=\"action\" value=\"Edit\"></input>";
print "<input type=\"submit\" name=\"action\" value=\"Preview\"></input>";
print " <input type=\"checkbox\" name=\"nopl\" id=\"np\" $nopl></input> <label for=\"np\">Disable post layout</label>";
print "<input type=\"hidden\" name=\"id\" value=\"".$pid."\"></input>";
print "</td>";
print "</tr>";
print "</table>";
print "</div>";
print "</form>";

doFooter();

?>
