<?php
//  AcmlmBoard XD - Thread listing page
//  Access: all

include("lib/common.php");

if(!isset($_GET['id']))
	Kill("Forum ID unspecified.");

$fid = (int)$_GET['id'];

$pl = $loguser['powerlevel'];
if($pl == -1) $pl = 0;

$qFora = "select * from forums where id=".$fid;
$rFora = Query($qFora);
if(NumRows($rFora))
{
	$forum = Fetch($rFora);
	if($forum['minpower'] > $pl)
		Kill("You are not allowed to browse this forum.");
} else
	Kill("Unknown forum ID.");

//Autolock system
$qLocker = "select id from threads where forum=".$fid." and closed = 0 and lastpostdate < ".(time() - (2592000 * $autoLockMonths)); //Math note: 60 seconds * 60 minutes * 24 hours * 30 days
$rLocker = Query($qLocker);
if(NumRows($rLocker))
	while($lockThread = Fetch($rLocker))
		Query("update threads set closed = 1 where id = ".$lockThread['id']);
//</autolock>

if($loguserid && $forum['minpowerthread'] <= $loguser['powerlevel'])
{
	$links .= "<a href=\"newthread.php?id=".$fid."\">Post Thread</a> | ";
	$links .= "<a href=\"newthread.php?id=".$fid."&amp;poll=1\">Post Poll</a> | ";
}

$links = substr($links, 0, strlen($links) - 2);

MakeCrumbs(array("Main"=>"./", $forum['title']=>"forum.php?id=".$fid), $links);

$total = $forum['numthreads'];
$tpp = $loguser['threadsperpage'];
if(isset($_GET['from']))
	$from = $_GET['from'];
else
	$from = 0;

if(!$tpp) $tpp = 50;

$postread = readpostread($loguserid);

$qThreads = "select * from threads where forum=".$fid." order by sticky desc, lastpostdate desc limit ".$from.", ".$tpp;
$rThreads = Query($qThreads);

$numonpage = NumRows($rThreads);

for($i = $tpp; $i < $total; $i+=$tpp)
	$pagelinks .= " <a href=\"forum.php?id=".$fid."&amp;from=".$i."\">".(($i/$tpp)+1)."</a>";
if($pagelinks)
{
	$pagelinks = "<a href=\"forum.php?id=".$fid."\">1</a>".$pagelinks;
	print "<div class=\"smallFonts pages\">Pages: ".$pagelinks."</div>";
}

$ppp = $loguser['postsperpage'];
if(!$ppp) $ppp = 20;

if(NumRows($rThreads))
{
	print "<div class=\"border width100\">";
	print "<table>";
	print "<tr class=\"header1\">";
	print "<th style=\"width: 16px;\">&nbsp;</th>";
	print "<th style=\"width: 16px;\">&nbsp;</th>";
	print "<th style=\"width: 60%;\">Title</th>";
	print "<th>Started by</th>";
	print "<th>Views</th>";
	print "<th>Replies</th>";
	print "<th>Last post</th>";
	print "</tr>";

	while($thread = Fetch($rThreads))
	{
		$qLastPoster = "select * from users where id=".$thread['lastposter'];
		$rLastPoster = Query($qLastPoster);
		$last = Fetch($rLastPoster);
		$qStarterPoster = "select * from users where id=".$thread['user'];
		$rStarterPoster = Query($qStarterPoster);
		$starter = Fetch($rStarterPoster);
		$qLastPost = "select * from posts where thread=".$thread['id']." order by date asc limit 1";
		$rLastPost = Query($qLastPost);
		$lastp = Fetch($rLastPost);

		$NewIcon = "";
		if($thread['closed'])
			$NewIcon = "off";
		if($thread['replies'] >= $misc['hotcount'])
			$NewIcon .= "hot";
		if((!$loguserid && $thread['lastpostdate'] > time() - 900) ||
			($loguserid && $thread['lastpostdate'] > $postread[$thread['id']]))
			$NewIcon .= "new";
		if($NewIcon)
			$NewIcon = "<img src=\"img/status/".$NewIcon.".png\" alt=\"\"/>";

		if($thread['icon'])
			$ThreadIcon = "<img src=\"".$thread['icon']."\" alt=\"\"/>";
		else
			$ThreadIcon = "";

		$cellClass = ($cellClass+1) % 2;

		if($thread['sticky'])
			$cellClass = 2;

		if($thread['sticky'] == 0 && $haveStickies == 1)
		{
			$haveStickies = 2;
			print "<tr class=\"header0\"><th colspan=\"7\"></th></tr>";
		}
		if($thread['sticky'] && $haveStickies == 0) $haveStickies = 1;

		$poll = ($thread['poll'] ? "<img src=\"img/poll.png\" alt=\"Poll\" /> " : "");

		$pl = "";
		$total = $thread['replies'];
		for($i = $ppp; $i <= $total; $i+=$ppp)
			$pl .= " <a href=\"thread.php?id=".$thread['id']."&amp;from=".$i."\">".(($i/$ppp)+1)."</a>";
		if($pl)
			$pl = " <span class=\"smallFonts\">[<a href=\"thread.php?id=".$thread['id']."\">1</a>".$pl."]</span>";

		print "<tr class=\"cell".$cellClass."\">";
		print "<td class=\"cell2\" style=\"width: 17px;\">".$NewIcon."</td>";
		print "<td style=\"width: 17px;\">".$ThreadIcon."</td>";
		print "<td>".$poll."<a href=\"thread.php?id=".$thread['id']."\">".$thread['title']."</a>".$pl."</td>";
		print "<td>".UserLink($starter)."</td>";
		print "<td>".$thread['views']."</td>";
		print "<td>".$thread['replies']."</td>";
		print "<td class=\"smallFonts\">".date($dateformat,$thread['lastpostdate'])." by ".UserLink($last)."</td>";
		print "</tr>";
	}
	print "</table>";
	print "</div>";
} else
	if($forum['minpowerthread'] > $loguser['powerlevel'])
		Alert("You cannot start any threads here.", "Empty forum");
	elseif($loguserid)
		Alert("Would you like to <a href=\"newthread.php?id=".$fid."\">post something</a>?","Empty forum");
	else
		Alert("<a href=\"login.php\">Log in</a> so you can post something.", "Empty forum");

if($pagelinks)
	print "<div class=\"smallFonts pages\">Pages: ".$pagelinks."</div>";

MakeCrumbs(array("Main"=>"./", $forum['title']=>"forum.php?id=".$fid), $links);

doFooter();

function readpostread($userid){
  $postreads = Query("select * from threadsread where id=".$userid);
  while($read1 = Fetch($postreads))
  	$postread[$read1['thread']]=$read1['date'];
  return $postread;
}

?>
