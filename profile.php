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

if($user['minipic'])
	$minipic = "<img src=\"".$user['minipic']."\" alt=\"\" style=\"vertical-align: middle; \" />";

if($user['rankset'])
{
	$title = GetRank($user);
	$toNextRank = GetToNextRank($user);
	if($toNextRank)
		$toNextRank = Plural($toNextRank, "post");
}
if($user['title'])
	if($title)
		$title = str_replace("<br />","",$title)." &mdash; ".$user['title'];
	else
		$title = $user['title'];

if($user['homepageurl'])
{
	if($user['homepagename'])
		$homepage = "<a href=\"".$user['homepageurl']."\">".$user['homepagename']."</a> &mdash; ".$user['homepageurl'];
	else
		$homepage = "<a href=\"".$user['homepageurl']."\">".$user['homepageurl']."</a>";
}

print "<table><tr><td style=\"width: 60%;\">";

print "<div class=\"border\">";
print "<table>";

print "<tr class=\"header0\">";
print "<th colspan=\"2\">General information</th>";
print "</tr>";
print "<tr>";
print "<td class=\"cell0\" style=\"width: 20%;\">Name</td>";
print "<td class=\"cell1\">".$minipic.$user['name']."</td>";
print "</tr>";
print "<tr>";
print "<td class=\"cell0\">Title</td>";
print "<td class=\"cell1\">".$title."</td>";
print "</tr>";
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
print "<td class=\"cell1\">".date($dateformat, $user['regdate'])." (".TimeUnits($daysKnown*86400)." ago)</td>";
print "</tr>";

print "<tr class=\"header0\">";
print "<th colspan=\"2\">Contact information</th>";
print "</tr>";
print "<tr>";
print "<td class=\"cell0\">Email address</td>";
print "<td class=\"cell1\">".$user['email']."</td>";
print "</tr>";
print "<tr>";
print "<td class=\"cell0\">Homepage</td>";
print "<td class=\"cell1\">".$homepage."</td>";
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
	$bday = date("F j, Y", $user['birthday']);
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

if($_POST['action'] == "Post" && $loguserid)
{
	$qComment = "insert into usercomments (uid, cid, text) values (".$id.", ".$loguserid.", '".$_POST['text']."')";
	$rComment = Query($qComment);
}
print "<div class=\"border faq\" style=\"padding: 0px;\">";
$qComments = "select * from usercomments where uid=".$id." order by id desc limit ".($page * 10).",10";
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
	print $f;
} else
	print "<tr><td class=\"cell0\" colspan=\"2\">No comments.</td></tr>";
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
	This has to be one of the [b]longest[/b] preview messages ever seen in an AcmlmBoard.[quote=Barack Obama]I am Barack Obama and I approve this preview message.[/quote]Well, what more could you [url=http://en.wikipedia.org]want to know[/url]? Perhaps how to do the classic infinite loop?
	[code]10 PRINT \"Hello World!\"
	GOTO 10[/code]HEEEEY MACARONI! :D";
$previewPost['text'] = $preview;
//</fixedpreview>

$previewPost['num'] = "???";
$previewPost['id'] = "???";
MakePost($previewPost, $user, 0, 0);

if($loguser['powerlevel'] == 3)
	$links .= "<a href=\"edituser.php?id=".$id."\">Edit user</a> | ";
if($loguserid)
	$links .= "<a href=\"sendprivate.php?uid=".$id."\">Send PM</a> | ";
$links .= "<a href=\"listposts.php?id=".$id."\">Show posts</a> | ";
$links .= $blockLayoutLink;
$links = substr($links, 0, strlen($links) - 2);
print "<div class=\"smallFonts border cell1\">".$links."</div>";

doFooter();

?>
