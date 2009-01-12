<?php

if($_GET['full'])
	include("lib/common.php");
else
{
	include("lib/mysql.php");
	include("lib/snippets.php");
	include("lib/post.php");
}

if($_GET['uid'])
	$uid = (int)$_GET['uid'];
else
	die("No user ID specified.");

if($_GET['page'])
	$page = (int)$_GET['page'];

$qUser = "select * from users where id=".$uid;
$rUser = Query($qUser);
$user = Fetch($rUser);

$qComments = "select * from usercomments where uid=".$uid." order by id desc limit ".($page * 10).",10";
$rComments = Query($qComments);

print "<table>";
print "<tr class=\"header1\"><th colspan=\"2\">Comments about ".UserLink($user)."</th></tr>";

if(NumRows($rComments))
{
	while($comment = Fetch($rComments))
	{
		$cellClass = ($cellClass+1) % 2;
		$qPoster = "select * from users where id=".$comment['cid'];
		$rPoster = Query($qPoster);
		$poster = Fetch($rPoster);
		$c = "<tr>";
		$c .= "<td class=\"cell2 width25\">".UserLink($poster)."</td>";
		$c .= "<td class=\"cell".$cellClass."\">";
		$c .= CleanUpPost($comment['text']);
		$c .= "</td>";
		$c .= "</tr>";
		$f = $c . $f;
	}
	print $f;
} else
	print "<tr><td class=\"cell0\" colspan=\"2\">No comments.</td></tr>";

print "</table>";

?>
