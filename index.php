<?php

$noAutoHeader = TRUE;
include("lib/common.php");

$numThreads = FetchResult("select count(*) from threads");
$numPosts = FetchResult("select count(*) from posts");
$stats = Plural($numThreads, "thread")." and ".Plural($numPosts,"post")." total";

$newToday = FetchResult("select count(*) from posts where date > ".(time() - 86400));
$newLastHour = FetchResult("select count(*) from posts where date > ".(time() - 3600));
$stats .= "<br />".Plural($newToday,"new post")." today, ".$newLastHour." last hour";

$numUsers = FetchResult("select count(*) from users");
$rLastUser = Query("select id,name,powerlevel,sex from users order by regdate desc limit 1");
$lastUser = Fetch($rLastUser);
$last = Plural($numUsers,"registered user")."<br />Newest: ".UserLink($lastUser);

$rOnlineUsers = Query("select id,name,sex,powerlevel,lastactivity,lastposttime,minipic from users where lastactivity > ".(time()-300)." or lastposttime > ".(time()-300)." order by name");
$onlineUsers = "";
$onlineUserCt = 0;
while($onlineUser = Fetch($rOnlineUsers))
{
	$loggedIn = ($onlineUser['lastpost'] <= $onlineUser['lastview']);
	$userLink = UserLink($onlineUser);
	if($onlineUser['minipic'])
		$userLink = "<img src=\"".$onlineUser['minipic']."\" alt=\"\" class=\"minipic\" />".$userLink;
	if(!$loggedIn)
		$userLink = "(".$userLink.")";
	$onlineUsers.=($onlineUserCt ? ", " : "").$userLink;
	$onlineUserCt++;
}
$onlineUsers = $onlineUserCt." user".(($onlineUserCt > 1 || $onlineUserCt == 0) ? "s" : "")." online".($onlineUserCt ? ": " : "").$onlineUsers;

$guests = FetchResult("select count(*) from guests where bot=0 and date > ".(time() - 300));
$bots = FetchResult("select count(*) from guests where bot=1 and date > ".(time() - 300));

if($guests)
	$onlineUsers .= " | ".Plural($guests,"guest");
if($bots)
	$onlineUsers .= " | ".Plural($bots,"bot");

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

include("lib/header.php");

?>
<div class="outline margin width100 smallFonts" style="overflow: auto;">
<div class="header0 cell2 center" style="overflow: auto;">
<div class="center" style="float: left; width: 75%; border-right: 1px solid black;"><?php print $stats; ?></div>
<?php print $last; ?>
</div>
<div class="header0 cell0 center" style="overflow: auto; border-top: 0px">
<?php print $onlineUsers; ?>
</div>
</div>
<?php
if($loguserid)
	DoPrivateMessageBar();
else
	print "<div id=\"pmNotice\" style=\"opacity: 1;\">Please <a href=\"login.php\">log in</a> or <a href=\"register.php\">create an account</a> to post.</div>";
?>

<table class="outline margin">
	<tr class="header1">
		<th></th>
		<th style="width: 75%">Forum title</th>
		<th>Threads</th>
		<th>Posts</th>
		<th style="width: 15%">Last Post</th>
	</tr>
	<?php

$rCategories = Query("select name,minpower from categories");
$category[] = "dummy";
while($cat = Fetch($rCategories))
	$category[] = array("name" => $cat['name'], "minpower" => $cat['minpower']);

$lastCatID = -1;
$forumsHad = array();

$rFora = Query("select * from forums order by catid, forder");
$postread = readpostread($loguserid);

$rMembers = Query("select name, id, powerlevel, sex from users");
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

while($forum = Fetch($rFora))
{
	if($category[$forum['catid']]['minpower'] > $pl)
		continue;

	if($forum['catid'] > $lastCatID)
	{
		$lastCatID = $forum['catid'];
		?>
		<tr class="header0">
			<th colspan="5"><?php print $category[$lastCatID]['name']; ?></th>
		</tr>
		<?php
	}
	$forum['description'] = str_replace("[trash]","",$forum['description']);
	$newstuff = 0;
	$NewIcon = "";
	$localMods = "";
	foreach($threadsRead as $trd)
		if($trd['forum'] == $forum['id'] && $trd['lastpostdate'] > $postread[$trd['id']])
			$newstuff = 1;

	if((!$loguserid && $forum['lastpostdate'] > time()-900) ||
		($loguserid && $newstuff))
		$NewIcon .= "new";
	if($NewIcon)
		$NewIcon = "<img src=\"img/status/".$NewIcon.".png\" alt=\"\"/>";

	foreach($mods as $mod)
		if($mod['forum'] == $forum['id'])
			$localMods .= UserLink($members[$mod['user']]). ", ";

	if($localMods)
		$localMods = "<br /><small>Moderated by: ".substr($localMods,0,strlen($localMods)-2)."</small>";

	if(array_key_exists($forum['id'], $forumsHad))
		continue;

	?>
		<tr class="cell1">
			<td class="cell0" style="width: 17px"><?php print $NewIcon; ?></td>
			<td>
				<h4><a href="forum.php?id=<?php print $forum['id']; ?>"><?php print $forum['title']; ?></a></h4>

				<?php print $forum['description']; ?>
				<?php print $localMods; ?>

			</td>
			<td class="center"><?php print $forum['numthreads']; ?></td>
			<td class="center"><?php print $forum['numposts']; ?></td>
			<td class="smallFonts center">
			<?php
				if($forum['lastpostdate'])
					print gmdate($dateformat, $forum['lastpostdate'])."<br />by ".UserLink($members[$forum['lastpostuser']]);
				else
					print "----";
			?>

			</td>
		</tr>
	<?php
	$forumsHad[$forum['id']] = $forum['name'];
}
print "</table>";

//print "Magic Quotes: ".get_magic_quotes_gpc();

doFooter();

function readpostread($userid)
{
  $postreads = Query("select * from threadsread where id=".$userid);
  while($read1 = Fetch($postreads))
  	$postread[$read1['thread']]=$read1['date'];
  return $postread;
}

?>
