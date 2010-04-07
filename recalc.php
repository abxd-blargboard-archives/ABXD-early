<?php
//  AcmlmBoard XD - Report/content mismatch fixing utility
//  Access: all

include("lib/common.php");

print "<table class=\"outline margin width50\">";

print "<tr class=\"header1\"><th>Name</th><th>Actual</th><th>Reported</th><th>&nbsp;</th></tr>";

print "<tr class=\"header0\"><th colspan=\"4\">Counting user's posts&hellip;</th></tr>";
$qUsers = "select * from users";
$rUsers = Query($qUsers);
while($user = Fetch($rUsers))
{
	$cellClass = ($cellClass+1) % 2;
	print "<tr class=\"cell".$cellClass."\">";
	print "<td>".$user['name']."</td>";
	$qPosts = "select count(*) from posts where user=".$user['id'];
	$posts = FetchResult($qPosts);
	print "<td>".$posts."</td><td>".$user['posts']."</td>";
	print "<td style=\"background: ".($posts==$user['posts'] ? "green" : "red").";\"></td>";
	print "</tr>";

	$qUser = "update users set posts=".$posts." where id=".$user['id']." limit 1";
	$rUser = Query($qUser);
}

print "<tr class=\"header0\"><th colspan=\"4\">Counting thread replies&hellip;</th></tr>";
$qThreads = "select * from threads";
$rThreads = Query($qThreads);
while($thread = Fetch($rThreads))
{
	$cellClass = ($cellClass+1) % 2;
	print "<tr class=\"cell".$cellClass."\">";
	print "<td>".$thread['title']."</td>";
	$qPosts = "select count(*) from posts where thread=".$thread['id'];
	$posts = FetchResult($qPosts);
	print "<td>".($posts-1)."</td><td>".$thread['replies']."</td>";
	print "<td style=\"background: ".($posts-1==$thread['replies'] ? "green" : "red").";\"></td>";
	print "</tr>";

	$qThread = "update threads set replies=".($posts-1)." where id=".$thread['id']." limit 1";
	$rThread = Query($qThread);
}

print "<tr class=\"header0\"><th colspan=\"4\">Counting forum threads and posts&hellip;</th></tr>";
$qFora = "select * from forums";
$rFora = Query($qFora);
while($forum = Fetch($rFora))
{
	$cellClass = ($cellClass+1) % 2;
	print "<tr class=\"cell".$cellClass."\">";
	print "<td>".$forum['title']."</td>";
	$qThreads = "select * from threads where forum=".$forum['id'];
	$rThreads = Query($qThreads);
	$threads = NumRows($rThreads);

	$postcount = 0;
	while($thread = Fetch($rThreads))
	{
		$qPosts = "select count(*) from posts where thread=".$thread['id'];
		$posts = FetchResult($qPosts);
		$postcount += $posts;
	}
	print "<td>".$threads." / ".$postcount."</td><td>".$forum['numthreads']." / ".$forum['numposts']."</td>";
	print "<td style=\"background: ".($threads==$forum['numthreads'] && $postcount==$forum['numposts'] ? "green" : "red").";\"></td>";
	print "</tr>";

	$qForum = "update forums set numposts=".$postcount.", numthreads=".$threads." where id=".$forum['id']." limit 1";
	$rForum = Query($qForum);
}

print "<tr class=\"header0\"><th colspan=\"4\">All counters reset.</th></tr>";
print "</table>";

doFooter();

?>
