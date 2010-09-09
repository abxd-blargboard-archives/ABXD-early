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

if($id == $loguserid)
	Query("update users set newcomments = 0 where id=".$loguserid);

$canDeleteComments = ($id == $loguserid || $loguser['powerlevel'] > 2);

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
		$user['karma'] = RecalculateKarma($id);
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

$karma = $user['karma'];
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
	$minipic = "<img src=\"".$user['minipic']."\" alt=\"\" style=\"vertical-align: middle;\" />";

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

if($user['tempbantime'])
{

	write(
"
	<div class=\"outline margin cell1 smallFonts\">
		This user has been temporarily banned until {0} (GMT). That's {1} left.
	</div>
",	gmdate("M jS Y, G:i:s",$user['tempbantime']), TimeUnits($user['tempbantime'] - time())
	);
}

write("
	<table>
		<tr>
			<td style=\"width: 60%; border: 0px none; vertical-align: top; padding-right: 1em; padding-bottom: 1em;\">
				<table class=\"outline margin\">
					<tr class=\"header0\">
						<th colspan=\"2\">General information</th>
					</tr>
					<tr>
						<td class=\"cell0\" style=\"width: 20%;\">Name</td>
						<td class=\"cell1\">{0}{1}{2}</td>
					</tr>
",	$minipic, ($user['displayname'] ? $user['displayname'] : $user['name']),
	($user['displayname'] ? " (".$user['name'].")" : "")
);
if($title)
{
	write("
					<tr>
						<td class=\"cell0\">Title</td>
						<td class=\"cell1\">{0}</td>
					</tr>
", $title);
}
if($currentRank)
{
	write("
					<tr>
						<td class=\"cell0\">Rank</td>
						<td class=\"cell1\">{0}</td>
					</tr>
", $currentRank);
}
if($toNextRank)
{
	write("
					<tr>
						<td class=\"cell0\">To next rank</td>
						<td class=\"cell1\">{0}</td>
					</tr>
", $toNextRank);
}
write("
					<tr>
						<td class=\"cell0\">Karma</td>
						<td class=\"cell1\">{0}{1}</td>
					</tr>
					<tr>
						<td class=\"cell0\">Total posts</td>
						<td class=\"cell1\">{2} ({3} per day)</td>
					</tr>
					<tr>
						<td class=\"cell0\">Total threads</td>
						<td class=\"cell1\">{4} ({5} per day)</td>
					</tr>
					<tr>
						<td class=\"cell0\">Registered on</td>
						<td class=\"cell1\">{6} ({7} ago)</td>
					</tr>
					<tr>
						<td class=\"cell0\">Score</td>
						<td class=\"cell1\">{8}</td>
					</tr>
					<tr>
						<td class=\"cell0\">Browser</td>
						<td class=\"cell1\">{9}</td>
					</tr>
",	$karma, $karmaLinks, $posts, $averagePosts, $threads, $averageThreads,
	cdate($dateformat, $user['regdate']), TimeUnits($daysKnown*86400),
	$score, $user['lastknownbrowser']
);
if($groups)
{
	write("
					<tr>
						<td class=\"cell0\">Groups</td>
						<td class=\"cell1\">{0}</td>
					</tr>
", $groups);
}
if($loguser['powerlevel'] > 0)
{
	write("
					<tr>
						<td class=\"cell0\">Last known IP</td>
						<td class=\"cell1\">{1} {0}</td>
					</tr>
", $user['lastip'], IP2C($user['lastip']));
}

write("
					<tr class=\"header0\">
						<th colspan=\"2\">Contact information</th>
					</tr>
					<tr>
						<td class=\"cell0\">Email address</td>
						<td class=\"cell1\">{0}</td>
					</tr>
					<tr>
						<td class=\"cell0\">Homepage</td>
						<td class=\"cell1\">{1}</td>
					</tr>
",	CleanUpPost($user['email']), CleanUpPost($homepage)
);

write("
					<tr class=\"header0\">
						<th colspan=\"2\">Presentation</th>
					</tr>
					<tr>
						<td class=\"cell0\">Theme</td>
						<td class=\"cell1\">{0}</td>
					</tr>
					<tr>
						<td class=\"cell0\">Items per page</td>
						<td class=\"cell1\">{1}, {2}</td>
					</tr>
",	$themeNames[$user['scheme']],
	Plural($user['postsperpage'],"post"), Plural($user['threadsperpage'],"thread")
);

write("
					<tr class=\"header0\">
						<th colspan=\"2\">Personal information</th>
					</tr>
					<tr>
						<td class=\"cell0\">Real name</td>
						<td class=\"cell1\">{0}</td>
					</tr>
					<tr>
						<td class=\"cell0\">Location</td>
						<td class=\"cell1\">{1}</td>
					</tr>
",	strip_tags($user['realname']), strip_tags($user['location'])
);
if($user['birthday'])
{
	write("
					<tr>
						<td class=\"cell0\">Birthday</td>
						<td class=\"cell1\">{0} ({1} years old)</td>
					</tr>
",	cdate("F j, Y", $user['birthday']),
	floor((time() - $user['birthday']) / 86400 / 365.2425)
	);
}
write("
					<tr>
						<td class=\"cell0\">Bio</td>
						<td class=\"cell1\">{0}</td>
					</tr>
				</table>
			</td>
",	CleanUpPost($user['bio']));

if($canDeleteComments && $_GET['action'] == "delete")
{
	Query("delete from usercomments where uid=".$id." and id=".(int)$_GET['cid']);
}

$qComments = "select users.name, users.displayname, users.powerlevel, users.sex, usercomments.id, usercomments.cid, usercomments.text from usercomments left join users on users.id = usercomments.cid where uid=".$id." order by usercomments.id desc limit 0,10";
$rComments = Query($qComments);
$commentList = "";
$commentField = "";
if(NumRows($rComments))
{
	while($comment = Fetch($rComments))
	{
		if($canDeleteComments)
			$deleteLink = "<small style=\"float: right; margin: 0px 4px;\">[<a  href=\"profile.php?id=".$id."&amp;action=delete&amp;cid=".$comment['id']."\" title=\"Delete comment\">d</a>]</small>";
		$cellClass = ($cellClass+1) % 2;
		$thisComment = format(
"
						<tr>
							<td class=\"cell2 width25\">
								{0}
							</td>
							<td class=\"cell{1}\">
								{3}{2}
							</td>
						</tr>
",	UserLink($comment, "cid"), $cellClass, CleanUpPost($comment['text']), $deleteLink);
		$commentList = $thisComment . $commentList;
		if(!isset($lastCID))
			$lastCID = $comment['cid'];
	}
}
else
	$commentList = $thisComment = format(
"
						<tr>
							<td class=\"cell0\" colspan=\"2\">
								No comments.
							</td>
						</tr>
");

if($_POST['action'] == "Post" && IsReallyEmpty(strip_tags($_POST['text'])) && $loguserid && $loguserid != $lastCID)
{
	$_POST['text'] = strip_tags($_POST['text']);
	$qComment = "insert into usercomments (uid, cid, text) values (".$id.", ".$loguserid.", '".justEscape($_POST['text'])."')";
	$rComment = Query($qComment);
	Query("update users set newcomments = 1 where id=".$id);
	$lastCID = $loguserid;
	$thisComment = format(
"
						<tr>
							<td class=\"cell2 width25\">
								{0}
							</td>
							<td class=\"cell{1}\">
								{2}
							</td>
						</tr>
",	UserLink($loguser), 2, CleanUpPost(deSlashMagic($_POST['text'])));
	$commentList .= $thisComment;
}

//print "lastCID: ".$lastCID;

if($loguserid)
{
	$commentField = format(
"
								<form method=\"post\" action=\"profile.php\">
									<input type=\"hidden\" name=\"id\" value=\"{0}\" />
									<input type=\"text\" name=\"text\" style=\"width: 80%;\" maxlength=\"255\" />
									<input type=\"submit\" name=\"action\" value=\"Post\" />
								</form>
", $id);
	if($lastCID == $loguserid)
		$commentField = "You already have the last word.";
}

write(
"
			<td style=\"vertical-align: top; border: 0px none;\">
				<table class=\"outline margin\">
					<tr class=\"header1\">
						<th colspan=\"2\">
							Comments about {0}
						</th>
					</tr>
					{1}
					<tr>
						<td colspan=\"2\" class=\"cell2\">
							{2}
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
",	UserLink($user), $commentList, $commentField);

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
$previewPost['text'] = $profilePreviewText;
//</fixedpreview>

$previewPost['num'] = "???";
$previewPost['id'] = "???";
$previewPost['uid'] = $id;
$copies = explode(",","title,name,displayname,picture,sex,powerlevel,avatar,postheader,rankset,signature,signsep,posts,regdate,lastactivity,lastposttime");
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
write("
	<div class=\"smallFonts outline margin cell1\">
		{0}
	</div>
", $links);

$title = "Profile for ".$user['name'];

function IsReallyEmpty($subject)
{
	$trimmed = trim(preg_replace("/&.*;/", "", $subject));
	return strlen($trimmed) != 0;
}

function IP2C($ip)
{
	$q = @mysql_query("select cc from ip2c where ip_from <= inet_aton('".$ip."') and ip_to >= inet_aton('".$ip."')") or $r['cc'] = "";
	if($q) $r = @mysql_fetch_array($q);
	if($r['cc'])
		return " <img src=\"img/flags/".strtolower($r['cc']).".png\" alt=\"".$r['cc']."\" title=\"".$r['cc']."\" />";
}

?>
