<?php
//  AcmlmBoard XD - Thread editing page
//  Access: moderators

include("lib/common.php");

if(!$loguserid) //Not logged in?
	Kill("You must be logged in to post.");

if(isset($_POST['id']))
	$_GET['id'] = $_POST['id'];

if(!isset($_GET['id']))
	Kill("Thread ID unspecified.");

$tid = (int)$_GET['id'];

$qThread = "select * from threads where id=".$tid;
$rThread = Query($qThread);
if(NumRows($rThread))
	$thread = Fetch($rThread);
else
	Kill("Unknown thread ID.");

$canMod = CanMod($loguserid,$thread['forum']);

if(!$canMod && $thread['user'] != $loguserid)
	Kill("You are not allowed to edit threads.");

if(isset($_POST['title']))
	$_POST['title'] = htmlentities2($_POST['title']);

if($canMod)
{
	if($_GET['action']=="close")
	{
		$qThread = "update threads set closed=1 where id=".$tid;
		$rThread = Query($qThread);
		Redirect("Thread closed.","forum.php?id=".$thread['forum'],"the forum");
	} elseif($_GET['action']=="open")
	{
		$qThread = "update threads set closed=0 where id=".$tid;
		$rThread = Query($qThread);
		Redirect("Thread opened.","forum.php?id=".$thread['forum'],"the forum");
	} elseif($_GET['action']=="stick")
	{
		$qThread = "update threads set sticky=1 where id=".$tid;
		$rThread = Query($qThread);
		Redirect("Thread stickied.","forum.php?id=".$thread['forum'],"the forum");
	} elseif($_GET['action']=="unstick")
	{
		$qThread = "update threads set sticky=0 where id=".$tid;
		$rThread = Query($qThread);
		Redirect("Thread unsticked.","forum.php?id=".$thread['forum'],"the forum");
	} elseif($_POST['action']=="Move")
	{
		//Tweak forum counters
		$qForum = "update forums set numthreads=numthreads-1, numposts=numposts-".($thread['replies']+1)." where id=".$thread['forum'];
		$rForum = Query($qForum);
		$qForum = "update forums set numthreads=numthreads+1, numposts=numposts+".($thread['replies']+1)." where id=".$_POST['moveTo'];
		$rForum = Query($qForum);


		$qThread = "update threads set forum=".(int)$_POST['moveTo']." where id=".$tid;
		$rThread = Query($qThread);
		Redirect("Thread moved.","forum.php?id=".(int)$_POST['moveTo'],"the new forum");
	} elseif($_GET['action']=="delete")
	{
		$qPosts = "select id,user from posts where thread=".$tid;
		$rPosts = Query($qPosts);
		//Round up posts in this thread
		while($post = Fetch($rPosts))
		{
			//Delete this post
			$qPost = "delete from posts where id=".$post['id'];
			$qPostText = "delete from posts_text where pid=".$post['id'];
			$rPost = Query($qPost);
			$rPostText = Query($qPostText);

			//Find and decrease user's postcount
			$qUser = "select id from users where id=".$post['user'];
			$rUser = Query($qUser);
			$qUser = "update users set posts = posts - 1 where id=".$post['user'];
			$rUser = Query($qUser);

			//Decrease forum postcount
			$qForum = "update forums set numposts = numposts - 1 where id=".$thread['forum'];
			$rForum = Query($qForum);
		}
		//Delete the thread
		$qThread = "delete from threads where id=".$tid;
		$rThread = Query($qThread);

		//Decrease forum threadcount
		$qForum = "update forums set numthreads = numthreads - 1 where id=".$thread['forum'];
		$rForum = Query($qForum);

		if($thread['poll'])
		{
			//Delete poll things
			$qPoll = "delete from poll where id=".$thread['poll'];
			$rPoll = Query($qPoll);
			$qPollVotes = "delete from pollvotes where poll=".$thread['poll'];
			$rPollVotes = Query($qPollVotes);
			$qPollChoices = "delete from poll_choices where poll=".$thread['poll'];
			$rPollChoices = Query($qPollChoices);
		}

		Redirect("Thread deleted.","forum.php?id=".$thread['forum'],"the forum");
	} elseif($_GET['action'] == "trash")
	{
		$qForum = "select id, title from forums where description like '%[trash]%' limit 1";
		$rForum = Query($qForum);
		if(NumRows($rForum))
		{
			$forum = Fetch($rForum);
			$qThread = "update threads set forum=".$forum['id'].", closed=1 where id=".$tid." limit 1";
			$rThread = Query($qThread);

			//Tweak forum counters
			$qForum = "update forums set numthreads=numthreads-1, numposts=numposts-".($thread['replies']+1)." where id=".$thread['forum'];
			$rForum = Query($qForum);
			$qForum = "update forums set numthreads=numthreads+1, numposts=numposts+".($thread['replies']+1)." where id=".$forum['id'];
			$rForum = Query($qForum);

			Redirect("Thread trashed.","forum.php?id=".$thread['forum'],"the forum");
		} else
			Kill("Could not identify trash forum.");
	}

	if($_POST['action']=="Edit")
	{
		$isClosed = (isset($_POST['isClosed']) ? 1 : 0);
		$isSticky = (isset($_POST['isSticky']) ? 1 : 0);

		if($_POST['title'])
		{
			if($_POST['iconid'])
			{
				$_POST['iconid'] = (int)$_POST['iconid'];
				if($_POST['iconid'] < 255)
					$iconurl = "img/icons/icon".$_POST['iconid'].".png";
				else
					$iconurl = justEscape($_POST['iconurl']);
			}

			$qThreads = "update threads set title='".justEscape($_POST['title'])."', icon='".$iconurl."', closed=".$isClosed.", sticky=".$isSticky." where id=".$tid." limit 1";
			$rThreads = Query($qThreads);

			Redirect("Edited!","thread.php?id=".$tid,"the thread");
			exit();
		} else
			print "<div>Your thread title is empty. Enter a message and try again.</div>";
	}
}
else
{
	if($_POST['action']=="Edit")
	{
		if($_POST['title'])
		{
			$qThreads = "update threads set title='".justEscape($_POST['title'])."' where id=".$tid." limit 1";
			$rThreads = Query($qThreads);

			Redirect("Edited!","thread.php?id=".$tid,"the thread");
			exit();
		} else
			print "<div>Your thread title is empty. Enter a message and try again.</div>";
	}
}

if(!$_POST['title']) $_POST['title'] = $thread['title'];
if(strpos($thread['icon'],"img/icons/icon") === 0) //Has a default icon
	$_POST['iconid'] = substr($thread['icon'],14,1);
elseif($thread['icon'] == "") //Has no icon
	$_POST['iconid'] = 0;
else //Has custom icon
{
	$_POST['iconid'] = 255;
	$_POST['iconurl'] = $thread['icon'];
}

if(!isset($_POST['iconid'])) $_POST['iconid'] = 0;

$qFora = "select title, id from forums order by catid, id";
$rFora = Query($qFora);
while($forum = Fetch($rFora))
{
	$moveToTargets .= "<option value=\"".$forum['id']."\">".$forum['title']."</option>";
}

if($canMod)
{
	print "<form action=\"editthread.php\" method=\"post\">";
	print "<table class=\"outline margin width50\">";
	print "<tr class=\"cell0\">";
	print "<td><label for=\"tit\">Title</label></td>";
	print "<td><input type=\"text\" id=\"tit\" name=\"title\" style=\"width: 98%;\" maxlength=\"60\" value=\"".htmlval(deSlashMagic($_POST['title']))."\"></input><br /></td>";
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
	//	if($i % 10 == 9) print "<br />";
		$i++;
	}
	$check = "";
	if($_POST['iconid'] == 0) $check = "checked=\"checked\" ";
	print "<br/><input type=\"radio\" ".$check."id=\"noicon\" name=\"iconid\" value=\"0\"></input> <label for=\"noicon\">None</label> ";
	$check = "";
	if($_POST['iconid'] == 255)
	{
		$check = "checked=\"checked\" ";
		$iconurl = htmlval(deSlashMagic($_POST['iconurl']));
	}
	print "<input type=\"radio\" ".$check."name=\"iconid\" value=\"255\"></input> Custom:";
	print "<input type=\"text\" name=\"iconurl\" style=\"width: 98%;\" maxlength=\"100\" value=\"".$iconurl."\"></input>";
	print "</td>";
	print "</tr>";
	print "<tr class=\"cell2\">";
	print "<td>Extras</td>";
	print "<td>";
	print " <input type=\"checkbox\" id=\"cl\" name=\"isClosed\"".($thread['closed'] ? " checked=\"checked\"" : "")."></input><label for=\"cl\">Closed</label>";
	print " <input type=\"checkbox\" id=\"st\" name=\"isSticky\"".($thread['sticky'] ? " checked=\"checked\"" : "")."></input><label for=\"st\">Sticky</label>";
	print "</td>";
	print "</tr>";
	print "<tr class=\"cell2\">";
	print "<td></td>";
	print "<td>";
	print "<input type=\"submit\" name=\"action\" value=\"Edit\"></input>";
	//if($thread['closed'])
	//	print "<input type=\"button\" value=\"Open\" onclick=\"window.navigate('editthread.php?id=".$tid."&amp;action=open');\"></input>";
	//else
	//	print "<input type=\"button\" value=\"Close\" onclick=\"window.navigate('editthread.php?id=".$tid."&amp;action=close');\"></input>";
	print "<input type=\"button\" value=\"Delete\" onclick=\"window.navigate('editthread.php?id=".$tid."&amp;action=delete');\"></input>";

	print "<select name=\"moveTo\" size=\"1\">".$moveToTargets."</select>";
	print "<input type=\"submit\" name=\"action\" value=\"Move\"></input>";
	print "<input type=\"hidden\" name=\"id\" value=\"".$tid."\"></input>";
	print "</td>";
	print "</tr>";
	print "</table>";
	print "</form>";
}
else
{
	print "<form action=\"editthread.php\" method=\"post\">";
	print "<table class=\"outline margin width50\">";
	print "<tr class=\"cell0\">";
	print "<td><label for=\"tit\">Title</label></td>";
	print "<td><input type=\"text\" id=\"tit\" name=\"title\" style=\"width: 98%;\" maxlength=\"60\" value=\"".htmlval(deSlashMagic($_POST['title']))."\"></input><br /></td>";
	print "</tr>";
	print "<tr class=\"cell2\">";
	print "<td></td>";
	print "<td>";
	print "<input type=\"submit\" name=\"action\" value=\"Edit\"></input>";
	print "<input type=\"hidden\" name=\"id\" value=\"".$tid."\"></input>";
	print "</td>";
	print "</tr>";
	print "</table>";
	print "</form>";
}

doFooter();

?>
