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

$title = $forum['title'];

$qCat = "select * from categories where id=".$forum['catid'];
$rCat = Query($qCat);
if(NumRows($rCat))
{
	$cat = Fetch($rCat);
	if($cat['minpower'] > $pl)
		Kill("You are not allowed to see this category.");
} else
	Kill("Unknown category ID.");

//Autolock system
if($autoLockMonths > 0)
{
	$qLocker = "select id from threads where forum=".$fid." and closed = 0 and lastpostdate < ".(time() - (2592000 * $autoLockMonths)); //Math note: 60 seconds * 60 minutes * 24 hours * 30 days
	$rLocker = Query($qLocker);
	if(NumRows($rLocker))
		while($lockThread = Fetch($rLocker))
			Query("update threads set closed = 1 where id = ".$lockThread['id']);
}
//</autolock>

$isIgnored = FetchResult("select count(*) from ignoredforums where uid=".$loguserid." and fid=".$fid, 0, 0) == 1;
if(isset($_GET['ignore']))
{
	if(!$isIgnored)
	{
		Query("insert into ignoredforums values (".$loguserid.", ".$fid.")");
		Alert("Forum ignored. You will no longer see any \"New\" markers for this forum.");
	}
}
else if(isset($_GET['unignore']))
{
	if($isIgnored)
	{
		Query("delete from ignoredforums where uid=".$loguserid." and fid=".$fid);
		Alert("Forum unignored.");
	}
}

$isIgnored = FetchResult("select count(*) from ignoredforums where uid=".$loguserid." and fid=".$fid, 0, 0) == 1;
if($loguserid && $forum['minpowerthread'] <= $loguser['powerlevel'])
{
	if($isIgnored)
		$links .= "<li><a href=\"forum.php?id=".$fid."&amp;unignore\">Unignore Forum</a></li>";
	else
		$links .= "<li><a href=\"forum.php?id=".$fid."&amp;ignore\">Ignore Forum</a></li>";

	$links .= "<li><a href=\"newthread.php?id=".$fid."\">Post Thread</a></li>";
	$links .= "<li><a href=\"newthread.php?id=".$fid."&amp;poll=1\">Post Poll</a></li>";
}

$onlineUsers = OnlineUsers($fid);
Write(
"
	<script type=\"text/javascript\" src=\"lib/Tween.js\"></script>
	<script type=\"text/javascript\" src=\"lib/OpacityTween.js\"></script>
	<script type=\"text/javascript\">
		window.onload = function() { startOnlineUsers({0}); };
	</script>
	<div class=\"header0 cell1 center outline smallFonts\" style=\"overflow: auto;\">
		<span id=\"onlineUsers\">
			{1}
		</span>
	</div>
",	$fid, $onlineUsers);

MakeCrumbs(array("Main"=>"./", $forum['title']=>"forum.php?id=".$fid), $links);

$total = $forum['numthreads'];
$tpp = $loguser['threadsperpage'];
if(isset($_GET['from']))
	$from = (int)$_GET['from'];
else
	$from = 0;

if(!$tpp) $tpp = 50;

$postread = readpostread($loguserid);

$qThreads = "select * from threads where forum=".$fid." order by sticky desc, lastpostdate desc limit ".$from.", ".$tpp;
$rThreads = Query($qThreads);

$rMembers = Query("select name, displayname, id, powerlevel, sex from users");
while($mem = Fetch($rMembers))
	$members[$mem['id']] = $mem;

$numonpage = NumRows($rThreads);

for($i = $tpp; $i < $total; $i+=$tpp)
	if($i == $from)
		$pagelinks .= " ".(($i/$ppp)+1);
	else
		$pagelinks .= " <a href=\"forum.php?id=".$fid."&amp;from=".$i."\">".(($i/$tpp)+1)."</a>";
if($pagelinks)
{
	if($from == 0)
		$pagelinks = " 1".$pagelinks;
	else
		$pagelinks = "<a href=\"forum.php?id=".$fid."\">1</a>".$pagelinks;
	Write("<div class=\"smallFonts pages\">Pages: {0}</div>", $pagelinks);
}

$ppp = $loguser['postsperpage'];
if(!$ppp) $ppp = 20;

DoPrivateMessageBar();

if(NumRows($rThreads))
{	
	$forumList = "";
	while($thread = Fetch($rThreads))
	{
		$last = $members[$thread['lastposter']];
		$starter = $members[$thread['user']];

		$tags = ParseThreadTags($thread['title']);

		$NewIcon = "";
		$newstuff = 0;
		if($thread['closed'])
			$NewIcon = "off";
		if($thread['replies'] >= $misc['hotcount'])
			$NewIcon .= "hot";
		if((!$loguserid && $thread['lastpostdate'] > time() - 900) ||
			($loguserid && $thread['lastpostdate'] > $postread[$thread['id']]) &&
			!$isIgnored)
		{
			$NewIcon .= "new";
			$newstuff++;
		}
		if($NewIcon)
			$NewIcon = "<img src=\"img/status/".$NewIcon.".png\" alt=\"\"/>";

		if($thread['icon'])
			$ThreadIcon = "<img src=\"".htmlspecialchars($thread['icon'])."\" width=\"15\" alt=\"\"/>";
		else
			$ThreadIcon = "";

		$cellClass = ($cellClass + 1) % 2;

		//if($thread['sticky'])
		//	$cellClass = 2;

		if($thread['sticky'] == 0 && $haveStickies == 1)
		{
			$haveStickies = 2;
			$forumList .= "<tr class=\"header1\"><th colspan=\"7\" style=\"height: 8px;\"></th></tr>";
		}
		if($thread['sticky'] && $haveStickies == 0) $haveStickies = 1;

		$poll = ($thread['poll'] ? "<img src=\"img/poll.png\" alt=\"Poll\" /> " : "");

		$pl = "";
		$total = $thread['replies'];
		for($i = $ppp; $i <= $total; $i+=$ppp)
			$pl .= " <a href=\"thread.php?id=".$thread['id']."&amp;from=".$i."\">".(($i/$ppp)+1)."</a>";
		if($pl)
			$pl = " <span class=\"smallFonts\">[<a href=\"thread.php?id=".$thread['id']."\">1</a>".$pl."]</span>";

		$lastLink = "";
		if($thread['lastpostid'])
			$lastLink = " <a href=\"thread.php?pid=".$thread['lastpostid']."#".$thread['lastpostid']."\">&raquo;</a>";

		$forumList .= Format(
"
		<tr class=\"cell{0}\">
			<td class=\"cell2 threadIcon\">{1}</td>
			<td class=\"threadIcon\" style=\"border-right: 0px none;\">
				{2}
			</td>
			<td style=\"border-left: 0px none;\">
				{3}
				<a href=\"thread.php?id={4}\">
					{5}
				</a>
				{6}
				{7}
			</td>
			<td>
				{8}
			</td>
			<td class=\"center\">
				{9}
			</td>
			<td class=\"center\">
				{10}
			</td>
			<td class=\"smallFonts\">
				{11}<br />
				by {12} {13}</td>
		</tr>
",	$cellClass, $NewIcon, $ThreadIcon, $poll, $thread['id'], htmlspecialchars($thread['title']), $pl, $tags,
	UserLink($starter), $thread['replies'], $thread['views'],
	cdate($dateformat,$thread['lastpostdate']), UserLink($last), $lastLink);
	}
	Write(
"
	<table class=\"outline margin width100\">
		<tr class=\"header1\">
			<th style=\"width: 20px;\">&nbsp;</th>
			<th style=\"width: 16px;\">&nbsp;</th>
			<th style=\"width: 60%;\">Title</th>
			<th>Started by</th>
			<th>Replies</th>
			<th>Views</th>
			<th>Last post</th>
		</tr>
		{0}
	</table>
",	$forumList);
} else
	if($forum['minpowerthread'] > $loguser['powerlevel'])
		Alert("You cannot start any threads here.", "Empty forum");
	elseif($loguserid)
		Alert("Would you like to <a href=\"newthread.php?id=".$fid."\">post something</a>?","Empty forum");
	else
		Alert("<a href=\"login.php\">Log in</a> so you can post something.", "Empty forum");

if($pagelinks)
	Write("<div class=\"smallFonts pages\">Pages: {0}</div>", $pagelinks);

MakeCrumbs(array("Main"=>"./", $forum['title']=>"forum.php?id=".$fid), $links);

ForumJump();

function readpostread($userid)
{
	$postreads = Query("select * from threadsread where id=".$userid);
	while($read1 = Fetch($postreads))
		$postread[$read1['thread']] = $read1['date'];
	return $postread;
}

function ForumJump()
{
	global $fid, $loguser;
	$fora = Query("select id, title from forums where minpower <= ".$loguser['powerlevel'] . " order by catid asc, forder asc");
	$jump = "<select onchange=\"document.location='forum.php?id=' + this.options[this.selectedIndex].value;\">";
	while($forum = Fetch($fora))
		$jump .= format("<option value=\"{0}\"{2}>{1}</option>", $forum['id'], $forum['title'], ($forum['id'] == $fid ? " selected=\"selected\"" : ""));
	$jump .= "</select>";
	print $jump;
}

?>
