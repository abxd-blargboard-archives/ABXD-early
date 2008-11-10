<?php
//  AcmlmBoard XD - Board index
//  Access: all

$noAutoHeader = TRUE;
include("lib/common.php");

$qThreads = "select count(*) from threads";
$numThreads = FetchResult($qThreads);
$qPosts = "select count(*) from posts";
$numPosts = FetchResult($qPosts);
$stats = Plural($numThreads, "thread")." and ".Plural($numPosts,"post")." total";

$qNewToday = "select count(*) from posts where date > ".(time() - 86400);
$newToday = FetchResult($qNewToday);
$qNewLastHour = "select count(*) from posts where date > ".(time() - 3600);
$newLastHour = FetchResult($qNewLastHour);
$stats .= "<br />".Plural($newToday,"new post")." today, ".$newLastHour." last hour";

$qUsers = "select count(*) from users";
$numUsers = FetchResult($qUsers);
$qLastUser = "select * from users order by regdate desc limit 1";
$rLastUser = Query($qLastUser);
$lastUser = Fetch($rLastUser);
$last = Plural($numUsers,"registered user")."<br />Newest: ".UserLink($lastUser);

$qOnlineUsers = "select id,name,sex,powerlevel,lastactivity,lastposttime,minipic from users where lastactivity > ".(time()-300)." or lastposttime > ".(time()-300)." order by name";
$rOnlineUsers = Query($qOnlineUsers);
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

$qGuests = "select count(*) from guests where bot=0 and date > ".(time() - 300);
$guests = FetchResult($qGuests);

$qBots = "select count(*) from guests where bot=1 and date > ".(time() - 300);
$bots = FetchResult($qBots);

if($guests)
	$onlineUsers .= " | ".Plural($guests,"guest");
if($bots)
	$onlineUsers .= " | ".Plural($bots,"bot");

if($loguserid)
{
	$qPrivates = "select * from pmsgs where userto = ".$loguserid." order by date desc limit 1";
	$rPrivates = Query($qPrivates);
	$privates = Fetch($rPrivates);
	$qUnread = "select count(*) from pmsgs where userto = ".$loguserid." and msgread=0";
	$unread= FetchResult($qUnread);
	$qTotal = "select count(*) from pmsgs where userto = ".$loguserid;
	$total = FetchResult($qTotal);
	if($unread)
	{
		$statusImg = "<img src=\"img/status/new.png\" alt=\"NEW\" /> ";
		$unreadPMs = " (".$unread." new)";
	}
	if($total)
	{
		$qUser = "select * from users where id = ".$privates['userfrom'];
		$rUser = Query($qUser);
		$user = Fetch($rUser);

		$lastPM = "<a href=\"showprivate.php?id=".$privates['id']."\">Last message</a> from ".UserLink($user)." on ".date($dateformat,$privates['date']);
	}
	$inbox = $statusImg."You have ".$total." private message".($total!=1 ? "s" : "").$unreadPMs.". ".$lastPM;
}

$pl = $loguser['powerlevel'];
if($pl == -1) $pl = 0;

if($loguserid && ($_GET['action'] == "markallread" || $_GET['action'] == "markasread" && isset($_GET['fid'])))
{
	$postread = readpostread($loguserid);
	if($_GET['action'] == "markasread" && isset($_GET['fid']))
		$qThreads = "select * from threads where forum=".$_GET['fid'];
	else
		$qThreads = "select * from threads";
	$rThreads = Query($qThreads);
	while($thread = Fetch($rThreads))
	{
		$tid = $thread['id'];
		if($thread['lastpostdate'] > $postread[$tid])
		{
			$qRead = "delete from threadsread where id=".$loguserid." and thread=".$tid;
			$rRead = Query($qRead);
			$qRead = "insert into threadsread (id,thread,date) values (".$loguserid.", ".$tid.", ".time().")";
			$rRead = Query($qRead);
		}
	}
	header('Location: index.php');
}

include("lib/header.php");

print "<div class=\"border width100 smallFonts\">";
print "<table>";
print "<tr class=\"cell2 center\"><td style=\"width: 85%;\">".$stats."</td>";
print "<td>".$last."</td></tr>";
print "<tr class=\"cell0 center\"><td colspan=\"2\">".$onlineUsers."</td></tr>";
if($loguserid)
	print "<tr class=\"cell1\"><td colspan=\"2\">".$inbox."</td></tr>";
print "</table>";
print "</div>";
//print "<div class=\"border cell0 center smallFonts\" style=\"margin-bottom: 4px;\">".$onlineUsers."</div>";
//if($loguserid)
//	print "<div class=\"border cell1 smallFonts\" style=\"margin-bottom: 4px;\">".$inbox."</div>";

$qCategories = "select * from categories where minpower <= ".$pl;
$rCategories = Query($qCategories);
if(NumRows($rCategories))
{
	print "<div class=\"border width100\">";
	print "<table>";
	print "<tr class=\"header1\">";
	print "<th></th>";
	print "<th style=\"width: 75%;\">Forum title</th>";
	print "<th>Threads</th>";
	print "<th>Posts</th>";
	print "<th>Last Post</th>";
	print "</tr>";
	while($category = Fetch($rCategories))
	{
		print "<tr class=\"header0\">";
		print "<th colspan=\"5\">".$category['name']."</th>";
		print "</tr>";
		$qFora = "select * from forums where catid=".$category['id']." and minpower <= ".$pl." order by forder";
		$rFora = Query($qFora);
		if(NumRows($rFora))
		{
			while($forum = Fetch($rFora))
			{
				$forum['description'] = str_replace("[trash]","",$forum['description']);

				$newstuff = 0;
				$postread = readpostread($loguserid);
				$qThreads = "select * from threads where forum=".$forum['id'];
				$rThreads = Query($qThreads);
				while($thread = Fetch($rThreads))
				{
					if($thread['lastpostdate'] > $postread[$thread['id']])
						$newstuff = 1;
				}

				$NewIcon = "";
				if((!$loguserid && $forum['lastpostdate'] > time()-900) ||
					($loguserid && $newstuff))
					$NewIcon .= "new";
				if($NewIcon)
					$NewIcon = "<img src=\"img/status/".$NewIcon.".png\" alt=\"\"/>";

				$localMods = "";
				$qMods = "select * from forummods where forum=".$forum['id'];
				$rMods = Query($qMods);
				if(NumRows($rMods))
				{
					while($mod = Fetch($rMods))
					{
						$qMod = "select name, id, powerlevel, sex from users where id=".$mod['user'];
						$rMod = Query($qMod);
						$mod2 = Fetch($rMod);
						$localMods .= UserLink($mod2).", ";
					}
					$localMods = "<br /><small>Moderated by: ".substr($localMods,0,strlen($localMods)-2)."</small>";
				}

				print "<tr class=\"cell1\">";
				print "<td class=\"cell0\" style=\"width: 17px;\">".$NewIcon."</td>";
				print "<td>";
				print "<h4><a href=\"forum.php?id=".$forum['id']."\">".$forum['title']."</a></h4>";
				print "".$forum['description']."";
				print $localMods;
				print "</td>";
				print "<td>".$forum['numthreads']."</td>";
				print "<td>".$forum['numposts']."</td>";
				print "<td class=\"smallFonts\">";
				print "";
				if($forum['lastpostdate'])
				{
					$qLastPoster = "select name, id, powerlevel, sex from users where id=".$forum['lastpostuser'];
					$rLastPoster = Query($qLastPoster);
					$last = Fetch($rLastPoster);
					print date($dateformat, $forum['lastpostdate'])." by ".UserLink($last);
				} else
				{
					print "----";
				}
				print "";
				print "</td>";
				print "</tr>";
			}
		}
	}
	print "</table>";
	print "</div>";
}

doFooter();

function readpostread($userid){
  $postreads = Query("select * from threadsread where id=".$userid);
  while($read1 = Fetch($postreads))
  	$postread[$read1['thread']]=$read1['date'];
  return $postread;
}

?>
