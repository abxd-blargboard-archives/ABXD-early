<?php
//  AcmlmBoard XD - User profile page
//  Access: all

include("lib/common.php");

if(isset($_POST['id']))
	$_GET['id'] = $_POST['id'];

if(!isset($_GET['id']))
	Kill("User ID unspecified.");

$id = (int)$_GET['id'];

$qUser = "select * from users where id=".$id;
$rUser = Query($qUser);
if(NumRows($rUser))
	$user = Fetch($rUser);
else
	Kill("Unknown user ID.");

if(isset($_GET['block']) && $loguserid)
{
	$block = (int)$_GET['block'];
	$qBlock = "select * from blockedlayouts where user=".$id." and blockee=".$loguserid;
	$rBlock = Query($qBlock);
	$isBlocked = NumRows($rBlock);
	if($block && !$isBlocked)
	{
		$qBlock = "insert into blockedlayouts (user, blockee) values (".$id.", ".$loguserid.")";
		$rBlock = Query($qBlock);
		Alert("Layout blocked.","Notice");
	} elseif(!$block && $isBlocked)
	{
		$qBlock = "delete from blockedlayouts where user=".$id." and blockee=".$loguserid." limit 1";
		$rBlock = Query($qBlock);
		Alert("Layout unblocked.","Notice");
	}
}

$canVote = ($loguser['powerlevel'] > 0 || ((time()-$loguser['regdate'])/86400) > 9);
if($loguserid == $id) $canVote = FALSE;

if($loguserid)
{
	$qBlock = "select * from blockedlayouts where user=".$id." and blockee=".$loguserid;
	$rBlock = Query($qBlock);
	$isBlocked = NumRows($rBlock);
	if($isBlocked)
		$blockLayoutLink = "<a href=\"profile.php?id=".$id."&amp;block=0\">Unblock layout</a> | ";
	else
		$blockLayoutLink = "<a href=\"profile.php?id=".$id."&amp;block=1\">Block layout</a> | ";

	if(isset($_GET['vote']) && $canVote)
	{
		$vote = (int)$_GET['vote'];
		if($vote > 1) $vote = 1;
		if($vote < -1) $vote = -1;
		$k = FetchResult("select count(*) from uservotes where uid=".$id." and voter=".$loguserid);
		if($k == 0)
			$qKarma = "insert into uservotes (uid, voter, up) values (".$id.", ".$loguserid.", ".$vote.")";
		else
			$qKarma = "update uservotes set up=".$vote." where uid=".$id." and voter=".$loguserid;
		$rKarma = Query($qKarma);
	}

	$qKarma = "select up from uservotes where uid=".$id." and voter=".$loguserid;
	$k = FetchResult($qKarma);
	if($k == -1)
		$karmaLinks = " <small>[Vote <a href=\"profile.php?id=".$id."&amp;vote=1\">up</a>/<a href=\"profile.php?id=".$id."&amp;vote=0\">down</a>]</small>";
	else if($k == 0)
		$karmaLinks = " <small>[Vote <a href=\"profile.php?id=".$id."&amp;vote=1\">up</a>]</small>";
	else if($k == 1)
		$karmaLinks = " <small>[Vote <a href=\"profile.php?id=".$id."&amp;vote=0\">down</a>]</small>";

	$groups = "";
	$comma = "";
	$qGroups = "select name from groups left join groupaffiliations on groups.id = groupaffiliations.gid where uid = ".$id;
	$rGroups = Query($qGroups);
	while($group = Fetch($rGroups))
	{
		$groups .= $comma . $group['name'];
		$comma = ", ";
	}	
}

$karma = 100;
$karmaWeights = array(5, 10, 10, 15);
$qKarma = "select powerlevel, up from uservotes left join users on id=voter where uid=".$id." and powerlevel > -1";
$rKarma = Query($qKarma);
while($k = Fetch($rKarma))
{
	if($k['up'])
		$karma += $karmaWeights[$k['powerlevel']];
	else
		$karma -= $karmaWeights[$k['powerlevel']];
}
if(!$canVote)
	$karmaLinks = "";

$daysKnown = (time()-$user['regdate'])/86400;

$qPosts = "select count(*) from posts where user=".$id;
$posts = FetchResult($qPosts);

$qThreads = "select count(*) from threads where user=".$id;
$threads = FetchResult($qThreads);

$averagePosts = sprintf("%1.02f", $user['posts'] / $daysKnown);
$averageThreads = sprintf("%1.02f", $threads / $daysKnown);

$score = ((int)$daysKnown * 2) + ($posts * 4) + ($threads * 8) + (($karma - 100) * 3);

if($user['minipic'])
	$minipic = "<img src=\"".$user['minipic']."\" alt=\"\" style=\"vertical-align: middle; \" />";

if($user['rankset'])
{
	$currentRank = GetRank($user);
	$toNextRank = GetToNextRank($user);
	if($toNextRank)
		$toNextRank = Plural($toNextRank, "post");
}
if($user['title'])
	$title = str_replace("<br />", " &bull; ", $user['title']);
//$title = "";

if($user['homepageurl'])
{
	if($user['homepagename'])
		$homepage = "<a href=\"".$user['homepageurl']."\">".$user['homepagename']."</a> &mdash; ".$user['homepageurl'];
	else
		$homepage = "<a href=\"".$user['homepageurl']."\">".$user['homepageurl']."</a>";
}

print "<table><tr><td style=\"width: 60%;\">";

print "<table class=\"outline margin\">";

print "<tr class=\"header0\">";
print "<th colspan=\"2\">General information</th>";
print "</tr>";
print "<tr>";
print "<td class=\"cell0\" style=\"width: 20%;\">Name</td>";
print "<td class=\"cell1\">".$minipic.$user['name']."</td>";
print "</tr>";
if($title)
{
	print "<tr>";
	print "<td class=\"cell0\">Title</td>";
	print "<td class=\"cell1\">".$title."</td>";
	print "</tr>";
}
if($currentRank)
{
	print "<tr>";
	print "<td class=\"cell0\">Rank</td>";
	print "<td class=\"cell1\">".$currentRank."</td>";
	print "</tr>";
}
if($toNextRank)
{
	print "<tr>";
	print "<td class=\"cell0\">To next rank</td>";
	print "<td class=\"cell1\">".$toNextRank."</td>";
	print "</tr>";
}
print "<tr>";
print "<td class=\"cell0\">Karma</td>";
print "<td class=\"cell1\">".$karma.$karmaLinks."</td>";
print "</tr>";
print "<tr>";
print "<td class=\"cell0\">Total posts</td>";
print "<td class=\"cell1\">".$posts." (".$averagePosts." per day)</td>";
print "</tr>";
print "<tr>";
print "<td class=\"cell0\">Total threads</td>";
print "<td class=\"cell1\">".$threads." (".$averageThreads." per day)</td>";
print "</tr>";
print "<tr>";
print "<td class=\"cell0\">Registered on</td>";
print "<td class=\"cell1\">".gmdate($dateformat, $user['regdate'])." (".TimeUnits($daysKnown*86400)." ago)</td>";
print "</tr>";
print "<tr>";
print "<td class=\"cell0\">Score</td>";
print "<td class=\"cell1\">".$score." points</td>";
print "</tr>";
print "</tr>";
print "<tr>";
print "<td class=\"cell0\">Browser</td>";
print "<td class=\"cell1\">".$user['lastknownbrowser']."</td>";
print "</tr>";

if($user['badges'])
{
	print "<tr>";
	print "<td class=\"cell0\">Badges</td>";
	print "<td class=\"cell1\">".GetBadgeList()."</td>";
	print "</tr>";
}

if($groups)
{
	print "<tr>";
	print "<td class=\"cell0\">Groups</td>";
	print "<td class=\"cell1\">".$groups."</td>";
	print "</tr>";
}

print "<tr class=\"header0\">";
print "<th colspan=\"2\">Contact information</th>";
print "</tr>";
print "<tr>";
print "<td class=\"cell0\">Email address</td>";
print "<td class=\"cell1\">".CleanUpPost($user['email'])."</td>";
print "</tr>";
print "<tr>";
print "<td class=\"cell0\">Homepage</td>";
print "<td class=\"cell1\">".CleanUpPost($homepage)."</td>";
print "</tr>";

print "<tr class=\"header0\">";
print "<th colspan=\"2\">Presentation</th>";
print "</tr>";
print "<tr>";
print "<td class=\"cell0\">Theme</td>";
print "<td class=\"cell1\">".$themeNames[$user['scheme']]."</td>";
print "</tr>";
print "<tr>";
print "<td class=\"cell0\">Items per page</td>";
print "<td class=\"cell1\">".Plural($user['postsperpage'],"post").", ".Plural($user['threadsperpage'],"thread")."</td>";
print "</tr>";

print "<tr class=\"header0\">";
print "<th colspan=\"2\">Personal information</th>";
print "</tr>";
print "<tr>";
print "<td class=\"cell0\">Real name</td>";
print "<td class=\"cell1\">".$user['realname']."</td>";
print "</tr>";
print "<tr>";
print "<td class=\"cell0\">Location</td>";
print "<td class=\"cell1\">".$user['location']."</td>";
print "</tr>";
if($user['birthday'])
{
	$bday = gmdate("F j, Y", $user['birthday']);
	$age = " (".floor((time() - $user['birthday']) / 86400 / 365.2425)." years old)";
	print "<tr>";
	print "<td class=\"cell0\">Birthday</td>";
	print "<td class=\"cell1\">".$bday.$age."</td>";
	print "</tr>";
}
print "<tr>";
print "<td class=\"cell0\">Bio</td>";
print "<td class=\"cell1\">".CleanUpPost($user['bio'])."</td>";
print "</tr>";
print "</table>";
print "</div>";

print "</td><td style=\"vertical-align: top;\">";

print "<div class=\"outline margin faq\" style=\"padding: 0px;\">";
$qComments = "select * from usercomments where uid=".$id." order by id desc limit 0,10";
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
		if(!isset($lastCommenter))
			$lastCommenter = $poster;
	}
}
else
	$f = "<tr><td class=\"cell0\" colspan=\"2\">No comments.</td></tr>";
if($_POST['action'] == "Post" && $loguserid && ($loguserid != $lastCommenter['id']))
{
	$_POST['text'] = strip_tags($_POST['text']);
	$qComment = "insert into usercomments (uid, cid, text) values (".$id.", ".$loguserid.", '".justEscape($_POST['text'])."')";
	$rComment = Query($qComment);
	$lastCommenter['id'] = $loguserid;
	$c = "<tr>";
	$c .= "<td class=\"cell2 width25\">".UserLink($loguser)."</td>";
	$c .= "<td class=\"cell2\">";
	$c .= CleanUpPost(deSlashMagic($_POST['text']));
	$c .= "</td>";
	$c .= "</tr>";
	$f = $f . $c;
	CheckCommentator();
}
print $f;
if($loguserid)
{
	print "<tr><td class=\"cell2 smallFonts\" colspan=\"2\">";
	if($lastCommenter['id'] == $loguserid)
		print "You already have the last word.";
	else
	{
		print "<form method=\"post\" action=\"profile.php\">";
		print "<input type=\"hidden\" name=\"id\" value=\"".$id."\"></input>";
		print "<input type=\"text\" name=\"text\" style=\"width: 80%;\" maxlength=\"255\"></input> ";
		print "<input type=\"submit\" name=\"action\" value=\"Post\"></input>";
		print "</form>";
	}
	print "</td></tr>";
}
print "</table>";
print "</div>";

print "</td></tr></table>";

/*
//Randomized previews
$previews = array
(
	"(sample text)", //from AcmlmBoard 1.8a and 2.0a2
	"[quote=Spock]A sample quote, with a <a href=\"about:blank\">link</a>, for testing your layout.[/quote](sample text)", //from ProtoBoard
	"[quote=\"The Joker\" id=\"4\"]Why so <a href=\"profile.php?id=".$id."\">serious</a>?[/quote]Because I heard it before. And it wasn't funny then.", //from "The Dark Knight" and "The Killing Joke"
	"[quote=Barack Obama]I am Barack Obama and I approve this preview message.[/quote](sample post)",
);
$previewPost['text'] = $previews[array_rand($previews)];
//</randompreviews>
*/
//Fixed preview
$preview =
	"This is a sample post. It demonstrates various kinds of [i]markup[/i] and quotes and such.
	This has to be one of the [b]longest[/b] preview messages ever seen in an AcmlmBoard.[quote=Barack Obama]I am Barack Obama and I approve this preview message.[quote=some guy]This is a nested quote. See what you get.[/quote][/quote]Well, what more could you [url=http://en.wikipedia.org]want to know[/url]? Perhaps how to do the classic infinite loop?
	[source=qbasic]10 PRINT \"Hello World!\"
20 GOTO 10[/source]HEEEEY MACARONI! :D";
$previewPost['text'] = $preview;
//</fixedpreview>

$previewPost['num'] = "???";
$previewPost['id'] = "???";
$previewPost['uid'] = $id;
$copies = explode(",","title,name,picture,sex,powerlevel,avatar,postheader,rankset,signature,posts,regdate,lastactivity,lastposttime");
foreach($copies as $toCopy)
	$previewPost[$toCopy] = $user[$toCopy];

$previewPost['activity'] = FetchResult("select count(*) from posts where user = ".$id." and date > ".(time() - 86400), 0, 0);

MakePost($previewPost, 0, 0);

if($loguser['powerlevel'] == 3)
	$links .= "<a href=\"edituser.php?id=".$id."\">Edit user</a> | <a href=\"private.php?user=".$id."\">Show PMs</a> | ";
if($loguserid)
	$links .= "<a href=\"sendprivate.php?uid=".$id."\">Send PM</a> | ";
$links .= "<a href=\"listposts.php?id=".$id."\">Show posts</a> | ";
$links .= $blockLayoutLink;
$links = substr($links, 0, strlen($links) - 2);
print "<div class=\"smallFonts outline margin cell1\">".$links."</div>";

doFooter();

?>
