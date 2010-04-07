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

if((int)$_GET['delete'] == 1)
{
	if(!CanMod($loguserid,$fid))
		Kill("You're not allowed to delete posts.");
	$qPosts = "update posts set deleted=1 where id=".$pid." limit 1";
	$rPosts = Query($qPosts);
	Redirect("Deleted!","thread.php?id=".$tid,"the thread");
	exit();
} elseif((int)$_GET['delete'] == 2)
{
	if(!CanMod($loguserid,$fid))
		Kill("You're not allowed to undelete posts.");
	$qPosts = "update posts set deleted=0 where id=".$pid." limit 1";
	$rPosts = Query($qPosts);
	Redirect("Restored!","thread.php?id=".$tid,"the thread");
	exit();
}

if(!CanMod($loguserid, $fid) && $post['user'] != $loguserid)
	Kill("You are not allowed to edit posts.");

if($thread['locked'])
	Kill("This thread is locked.");

if($_POST['action']=="Edit")
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

		$qRev = "select max(revision) from posts_text where pid=".$pid;
		$rRev = Query($qRev);
		$rev = Fetch($rRev);
		$rev = $rev[0]; //note: no longer a fetched row.
		$rev++;
		$qPostsText = "insert into posts_text (pid,text,revision) values (".$pid.", '".$post."', ".$rev.")";
		$rPostsText = Query($qPostsText);

		$qPosts = "update posts set options='".$options."' where id=".$pid." limit 1";
		$rPosts = Query($qPosts);

		Report("<message><action type=\"postedit\" id=\"".$pid."\" /><user id=\"".$loguserid."\">".$loguser['name']."</user><thread id=\"".$tid."\">".$thread['title']."</thread><forum id=\"".$fid."\">".$forum['title']."</forum></message>");
		Redirect("Edited!","thread.php?id=".$tid,"the thread");
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

if(!$_POST['text']) $_POST['text'] = $post['text'];
if($_POST['text']) $prefill = htmlval(stripslashes($_POST['text']));
//if(!$_POST['text']) $_POST['text'] = $post['text'];
//if($_POST['text']) $prefill = $_POST['text'];

if($_POST['nopl'])
	$nopl = "checked=\"checked\"";

DoSmileyBar();

print "<form action=\"editpost.php\" method=\"post\">";
print "<table class=\"outline margin width50\">";
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
print "</form>";

doFooter();

function htmlval($text)
{
	//$text = str_replace("\"", "&quot;", $text);
	//$text = str_replace("<", "&lt;", $text);
	//return $text;
	return htmlentities($text, ENT_COMPAT, "UTF-8");
}

?>
