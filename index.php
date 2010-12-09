<?php
$noAutoHeader = TRUE;

if(isset($_GET['fid']) && (int)$_GET['fid'] > 0)
	header("Location: forum.php?id=".(int)$_GET['fid']);
else if(isset($_GET['tid']) && (int)$_GET['tid'] > 0)
	header("Location: thread.php?id=".(int)$_GET['tid']);
else if(isset($_GET['uid']) && (int)$_GET['uid'] > 0)
	header("Location: profile.php?id=".(int)$_GET['uid']);
else if(isset($_GET['pid']) && (int)$_GET['pid'] > 0)
	header("Location: thread.php?pid=".(int)$_GET['pid']."#".(int)$_GET['pid']);

include("lib/common.php");

$numThreads = FetchResult("select count(*) from threads");
$numPosts = FetchResult("select count(*) from posts");
$stats = Plural($numThreads, "thread")." and ".Plural($numPosts,"post")." total";

$newToday = FetchResult("select count(*) from posts where date > ".(time() - 86400));
$newLastHour = FetchResult("select count(*) from posts where date > ".(time() - 3600));
$stats .= "<br />".Plural($newToday,"new post")." today, ".$newLastHour." last hour";

$numUsers = FetchResult("select count(*) from users");
$rLastUser = Query("select id,name,displayname,powerlevel,sex from users order by regdate desc limit 1");
$lastUser = Fetch($rLastUser);
$last = Plural($numUsers,"registered user")."<br />Newest: ".UserLink($lastUser);

$onlineUsers = OnlineUsers();

$pl = $loguser['powerlevel'];
if($pl == -1) $pl = 0;

if($loguserid && ($_GET['action'] == "markallread" || $_GET['action'] == "markasread" && isset($_GET['fid'])))
{
	$postread = readpostread($loguserid);
	if($_GET['action'] == "markasread" && isset($_GET['fid']))
		$rThreads = Query("select * from threads where forum=".(int)$_GET['fid']);
	else
		$rThreads = Query("select * from threads");
	while($thread = Fetch($rThreads))
	{
		$tid = $thread['id'];
		if($thread['lastpostdate'] > $postread[$tid])
		{
			$rRead = Query("delete from threadsread where id=".$loguserid." and thread=".$tid);
			$rRead = Query("insert into threadsread (id,thread,date) values (".$loguserid.", ".$tid.", ".time().")");
		}
	}
	header('Location: index.php');
}

$rBirthdays = Query("select birthday, id, name, displayname, powerlevel, sex from users where birthday > 0 order by name");
$birthdays = array();
while($user = Fetch($rBirthdays))
{
	runBucket("manglers", "userData", $user['id']);
	$b = $user['birthday'];
	if(gmdate("m-d", $b) == gmdate("m-d"))
	{
		$y = gmdate("Y") - gmdate("Y", $b);
		$birthdays[] = UserLink($user)." (".$y.")";
	}
}
if(count($birthdays))
	$birthdaysToday = implode(", ", $birthdays);

include("lib/header.php");

if(!$noAjax)
	write(
"
	<script type=\"text/javascript\" src=\"lib/Tween.js\"></script>
	<script type=\"text/javascript\" src=\"lib/OpacityTween.js\"></script>
	<script type=\"text/javascript\">
		window.addEventListener(\"load\",  startOnlineUsers, false);
		window.addEventListener(\"load\",  startNewMarkers, false);
	</script>
");
write(
"
	<style type=\"text/css\">
		.ignored
		{
			opacity: 0.5;
		}
	</style>

	<div class=\"outline margin width100 smallFonts\" style=\"overflow: auto;\">
		<div class=\"header0 cell2 center\" style=\"overflow: auto;\">
			<div class=\"center\" style=\"float: left; width: 75%; border-right: 1px solid black;\">
				{0}
			</div>
			{1}
		</div>
",	$stats, $last);

if($birthdaysToday)
{
	write("
		<div class=\"header1 cell2\" style=\"border-top: 0px; text-align: center\">
			Birthdays today: {0}
		</div>", $birthdaysToday);
}	

runBucket("writers", "userBar");

write(
"
		<div id=\"rss\" style=\"float: right;\">
			<a href=\"rss2.php\"><img src=\"img/feed.png\" alt=\"RSS Feed\" title=\"RSS Feed\" /></a>
		</div>
		<div class=\"header0 cell1 center\" style=\"overflow: auto; border-top: 0px;\">
			&nbsp;
			<span id=\"onlineUsers\">
				{0}
			</span>
			&nbsp;
		</div>
	</div>
",	$onlineUsers);

if($loguserid)
	DoPrivateMessageBar();

$rCategories = Query("select name,minpower from categories");
$category[] = "dummy";
while($cat = Fetch($rCategories))
	$category[] = array("name" => $cat['name'], "minpower" => $cat['minpower']);

$lastCatID = -1;
$forumsHad = array();

$rFora = Query("select * from forums order by catid, forder");
$postread = readpostread($loguserid);

$rMembers = Query("select name, displayname, id, powerlevel, sex from users");
while($mem = Fetch($rMembers))
	$members[$mem['id']] = $mem;

$rMods = Query("select * from forummods");
$mods = array();
while($mod = Fetch($rMods))
	$mods[] = $mod;

$threadsRead = array();
$rThreadsRead = Query("select id, lastpostdate, forum from threads");
while($trd = Fetch($rThreadsRead))
	$threadsRead[$trd['id']] = $trd;

$ignored = array();
if($loguserid)
{
	$rIgnores = Query("select fid from ignoredforums where uid=".$loguserid);
	while($ignore = Fetch($rIgnores))
		$ignored[$ignore['fid']] = TRUE;
}

$theList = "";
while($forum = Fetch($rFora))
{
	if($category[$forum['catid']]['minpower'] > $pl || ($forum['hidden'] && $pl < 1))
		continue;

	runBucket("manglers", "forumList", $forum['id']);		

	if($forum['catid'] > $lastCatID)
	{
		$lastCatID = $forum['catid'];
		$theList .= format(
"
		<tr class=\"header0\">
			<th colspan=\"5\">
				{0}
			</th>
		</tr>
", $category[$lastCatID]['name']);
	}

	$forum['description'] = str_replace("[trash]","",$forum['description']);
	$newstuff = 0;
	$NewIcon = "";
	$localMods = "";
	if(!$ignored[$forum['id']])
		foreach($threadsRead as $trd)
			if($trd['forum'] == $forum['id'] && $trd['lastpostdate'] > $postread[$trd['id']])
				$newstuff++; //adding counters to the NEW part was easy -- this used to just set it to TRUE.
	
	$ignoreClass = $ignored[$forum['id']] ? " class=\"ignored\"" : "";

	if((!$loguserid && $forum['lastpostdate'] > time() - 900) || ($loguserid && $newstuff))
		$NewIcon = "<img src=\"img/status/new.png\" alt=\"New!\"/>".$newstuff;

	foreach($mods as $mod)
		if($mod['forum'] == $forum['id'])
			$localMods .= UserLink($members[$mod['user']]). ", ";

	if($localMods)
		$localMods = "<br /><small>Moderated by: ".substr($localMods,0,strlen($localMods)-2)."</small>";

	if(array_key_exists($forum['id'], $forumsHad))
		continue;

	if($forum['lastpostdate'])
	{
		$lastLink = "";
		if($forum['lastpostid'])
			$lastLink = "<a href=\"thread.php?pid=".$forum['lastpostid']."#".$forum['lastpostid']."\">&raquo;</a>";
		$lastLink = format("<span class=\"nom\">{0}<br />by </span>{1} {2}", cdate($dateformat, $forum['lastpostdate']), UserLink($members[$forum['lastpostuser']]), $lastLink);
	}
	else
		$lastLink = "----";


	$theList .= format(
"
		<tr class=\"cell1\">
			<td class=\"cell2 threadIcon newMarker\">
				{0}
			</td>
			<td>
				<h4{8}>
					<a href=\"forum.php?id={1}\">
						{2}
					</a>
				</h4>
				<span{8} class=\"nom\">
					{3}
					{4}
				</span>
			</td>
			<td class=\"center\">
				{5}
			</td>
			<td class=\"center\">
				{6}
			</td>
			<td class=\"smallFonts center\">
				{7}
			</td>
		</tr>
",	$NewIcon, $forum['id'], $forum['title'], $forum['description'], $localMods,
	$forum['numthreads'], $forum['numposts'], $lastLink, $ignoreClass);
	$forumsHad[$forum['id']] = $forum['name'];
}
write(
"
<table class=\"outline margin\" id=\"mainTable\">
	<tr class=\"header1\">
		<th style=\"width: 20px\"></th>
		<th style=\"width: 75%\">Forum title</th>
		<th>Threads</th>
		<th>Posts</th>
		<th style=\"width: 15%\">Last Post</th>
	</tr>
	{0}
</table>
",	$theList);

function readpostread($userid)
{
	$postreads = Query("select * from threadsread where id=".$userid);
	while($read1 = Fetch($postreads))
		$postread[$read1['thread']] = $read1['date'];
	return $postread;
}

?>
