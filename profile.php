<?php
//  AcmlmBoard XD - User profile page
//  Access: all

include("lib/common.php");

AssertForbidden("viewProfile");

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
$bucket = "userMangler"; include("./lib/pluginloader.php");

if($id == $loguserid)
	Query("update users set newcomments = 0 where id=".$loguserid);

$canDeleteComments = ($id == $loguserid || $loguser['powerlevel'] > 2) && IsAllowed("deleteComments");

if(isset($_GET['block']) && $loguserid)
{
	AssertForbidden("blockLayouts");
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

$canVote = ($loguser['powerlevel'] > 0 || ((time()-$loguser['regdate'])/86400) > 9) && IsAllowed("vote");
if($loguserid == $id) $canVote = FALSE;

if($loguserid)
{
	if(IsAllowed("blockLayouts"))
	{
		$qBlock = "select * from blockedlayouts where user=".$id." and blockee=".$loguserid;
		$rBlock = Query($qBlock);
		$isBlocked = NumRows($rBlock);
		if($isBlocked)
			$blockLayoutLink = "<li><a href=\"profile.php?id=".$id."&amp;block=0\">Unblock layout</a></li>";
		else
			$blockLayoutLink = "<li><a href=\"profile.php?id=".$id."&amp;block=1\">Block layout</a></li>";
	}

	if(isset($_GET['vote']) && $canVote)
	{
		$vote = (int)$_GET['vote'];
		if($vote > 1) $vote = 1 ;
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
	$minipic = "<img src=\"".$user['minipic']."\" alt=\"\" style=\"vertical-align: middle;\" />&nbsp;";

if($user['rankset'])
{
	$currentRank = GetRank($user);
	$toNextRank = GetToNextRank($user);
	if($toNextRank)
		$toNextRank = Plural($toNextRank, "post");
}
if($user['title'])
	$title = str_replace("<br />", " &bull; ", strip_tags(CleanUpPost($user['title'], "", true), "<b><strong><i><em><span><s><del><img><a><br><small>"));
//$title = "";

if($user['homepageurl'])
{
	if($user['homepagename'])
		$homepage = "<a href=\"".$user['homepageurl']."\">".$user['homepagename']."</a> &mdash; ".$user['homepageurl'];
	else
		$homepage = "<a href=\"".$user['homepageurl']."\">".$user['homepageurl']."</a>";
}

$emailField = "Private";
if($user['email'] == "")
{
	$emailField = "None given";
}
elseif($user['showemail'])
{
	$emailField = "<span id=\"emailField\">Public <button style=\"font-size: 0.7em;\" onclick=\"LoadSomething('emailField', 'ajaxcallbacks.php?a=em&amp;id=".$id."',  false);\">Show</button></span>";
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



$profileParts = array();

$foo = array();
$foo['Name'] = $minipic . htmlspecialchars($user['displayname'] ? $user['displayname'] : $user['name']) . ($user['displayname'] ? " (".$user['name'].")" : "");
if($title)
	$foo['Title'] = $title;
if($currentRank)
	$foo['Rank'] = $currentRank;
if($toNextRank)
	$foo['To next rank'] = $toNextRank;
$foo['Karma'] = $karma.$karmaLinks;
$foo['Total posts'] = format("{0} ({1} per day)", $posts, $averagePosts);
$foo['Total threads'] = format("{0} ({1} per day)", $threads, $averageThreads);
$foo['Registered on'] = format("{0} ({1} ago)", cdate($dateformat, $user['regdate']), TimeUnits($daysKnown*86400));
$foo['Score'] = $score;
$foo['Browser'] = $user['lastknownbrowser'];
if($loguser['powerlevel'] > 0)
	$foo['Last known IP'] = $user['lastip'] . " " . IP2C($user['lastip']);	
$profileParts['General information'] = $foo;

$foo = array();
$foo['Email address'] = $emailField;
if($homepage)
	$foo['Homepage'] = CleanUpPost($homepage);
$profileParts['Contact information'] = $foo;

$foo = array();
$foo['Theme'] = $themes[$user['theme']];
$foo['Items per page'] = Plural($user['postsperpage'],"post") . ", " . Plural($user['threadsperpage'],"thread");
$profileParts['Presentation'] = $foo;

$foo = array();
if($user['realname'])
	$foo['Real name'] = strip_tags($user['realname']);
if($user['location'])
	$foo['Location'] = strip_tags($user['location']);
if($user['birthday'])
	$foo['Birthday'] = format("{0} ({1} old)", cdate("F j, Y", $user['birthday']), Plural(floor((time() - $user['birthday']) / 86400 / 365.2425), "year"));
if($user['bio'])
	$foo['Bio'] = CleanUpPost($user['bio']);
if(count($foo))
	$profileParts['Personal information'] = $foo;

$prepend = "";
$bucket = "profileTable"; include("./lib/pluginloader.php");

write("
	<table>
		<tr>
			<td style=\"width: 60%; border: 0px none; vertical-align: top; padding-right: 1em; padding-bottom: 1em;\">
				{0}
				<table class=\"outline margin\">
", $prepend);
$cc = 0;
foreach($profileParts as $partName => $fields)
{
	write("
					<tr class=\"header0\">
						<th colspan=\"2\">{0}</th>
					</tr>
", $partName);
	foreach($fields as $label => $value)
	{
		$cc = ($cc + 1) % 2;
		write("
							<tr>
								<td class=\"cell2\">{0}</td>
								<td class=\"cell{2}\">{1}</td>
							</tr>
", str_replace(" ", "&nbsp;", $label), $value, $cc);
	}
}

write("
				</table>
");

$bucket = "profileLeft"; include("./lib/pluginloader.php");
write("
			</td>
");

if($canDeleteComments && $_GET['action'] == "delete")
{
	AssertForbidden("deleteComments");
	Query("delete from usercomments where uid=".$id." and id=".(int)$_GET['cid']);
}

$qComments = "select users.name, users.displayname, users.powerlevel, users.sex, usercomments.id, usercomments.cid, usercomments.text from usercomments left join users on users.id = usercomments.cid where uid=".$id." order by usercomments.date desc limit 0,10";
$rComments = Query($qComments);
$commentList = "";
$commentField = "";
if(NumRows($rComments))
{
	while($comment = Fetch($rComments))
	{
		if($canDeleteComments)
			$deleteLink = "<small style=\"float: right; margin: 0px 4px;\"><a  href=\"profile.php?id=".$id."&amp;action=delete&amp;cid=".$comment['id']."\" title=\"Delete comment\">&#x2718;</a></small>";
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
",	UserLink($comment, "cid"), $cellClass, PutASmileOnThatFace(htmlspecialchars($comment['text'])), $deleteLink);
		$commentList = $thisComment . $commentList;
		if(!isset($lastCID))
			$lastCID = $comment['cid'];
	}
}
else
{
	$commentsWasEmpty = true;
	$commentList = $thisComment = format(
"
						<tr>
							<td class=\"cell0\" colspan=\"2\">
								No comments.
							</td>
						</tr>
");
}

if($_POST['action'] == "Post" && IsReallyEmpty(strip_tags($_POST['text'])) && $loguserid && $loguserid != $lastCID)
{
	AssertForbidden("makeComments");
	$_POST['text'] = strip_tags($_POST['text']);
	$newID = FetchResult("SELECT id+1 FROM usercomments WHERE (SELECT COUNT(*) FROM usercomments u2 WHERE u2.id=usercomments.id+1)=0 ORDER BY id ASC LIMIT 1");
	if($newID < 1) $newID = 1;
	$qComment = "insert into usercomments (id, uid, cid, date, text) values (".$newID.", ".$id.", ".$loguserid.", ".time().", '".justEscape($_POST['text'])."')";
	$rComment = Query($qComment);
	if($loguserid != $id)
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
",	UserLink($loguser), 2, PutASmileOnThatFace(htmlspecialchars($_POST['text'])));
	if($commentsWasEmpty)
		$commentList = "";
	$commentList .= $thisComment;
}

//print "lastCID: ".$lastCID;

if($loguserid)
{
	$commentField = format(
"
								<div>
									<form method=\"post\" action=\"profile.php\">
										<input type=\"hidden\" name=\"id\" value=\"{0}\" />
										<input type=\"text\" name=\"text\" style=\"width: 80%;\" maxlength=\"255\" />
										<input type=\"submit\" name=\"action\" value=\"Post\" />
									</form>
								</div>
", $id);
	if($lastCID == $loguserid)
		$commentField = "You already have the last word.";
	if(!IsAllowed("makeComments"))
		$commentField = "You are not allowed to post usercomments.";
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
",	UserLink($user), $commentList, $commentField);

$bucket = "profileRight"; include("./lib/pluginloader.php");

write(
"
			</td>
		</tr>
	</table>
");

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

$previewPost['num'] = "preview";
$previewPost['id'] = "preview";
$previewPost['uid'] = $id;
$copies = explode(",","title,name,displayname,picture,sex,powerlevel,avatar,postheader,rankset,signature,signsep,posts,regdate,lastactivity,lastposttime");
foreach($copies as $toCopy)
	$previewPost[$toCopy] = $user[$toCopy];

$previewPost['activity'] = FetchResult("select count(*) from posts where user = ".$id." and date > ".(time() - 86400), 0, 0);

MakePost($previewPost, 0, 0);

if($loguser['powerlevel'] > 2)
{
	if(IsAllowed("editUser"))
		$links .= "<li><a href=\"edituser.php?id=".$id."\">Edit user</a></li>";
	if(IsAllowed("snoopPM"))
		$links .= "<li><a href=\"private.php?user=".$id."\">Show PMs</a></li>";
}
if($loguserid && IsAllowed("sendPM"))
	$links .= "<li><a href=\"sendprivate.php?uid=".$id."\">Send PM</a><li>";
if(IsAllowed("listPosts"))
	$links .= "<li><a href=\"listposts.php?id=".$id."\">Show posts</a></li>";
$links .= $blockLayoutLink;
write("
	<ul class=\"smallFonts margin pipemenu\">
		{0}
	</ul>
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

function PutASmileOnThatFace($s)
{
	global $smilies;
	LoadSmilies();
	$s = preg_replace_callback("'\[user=([0-9]+)\]'si", "MakeUserLink", $s);
	for($i = 0; $i < count($smilies); $i++)
	{
		$preg_special = array("\\","^","$",".","*","+","?","|","(",")","[","]","{","}","@");
		$preg_special_escape = array("\\\\","\\^","\\$","\\.","\\*","\\+","\\?","\\|","\\(","\\)","\\[","\\]","\\{","\\}","\\@");

		$s = preg_replace("@<([^>]+)(".str_replace($preg_special, $preg_special_escape, $smilies[$i]['code']).")+([^>]+)>@si", "<$1##LOLDONTREPLACESMILIESINHTMLTAGZLOL##$3>", $s);
		$s = str_replace($smilies[$i]['code'], "«".$smilies[$i]['image']."»", $s);
		$s = str_replace("«".$smilies[$i]['image']."»", "<img src=\"img/smilies/".$smilies[$i]['image']."\" alt=\"".str_replace(">", "&gt;", $smilies[$i]['code'])."\" />", $s);
		$s = str_replace("##LOLDONTREPLACESMILIESINHTMLTAGZLOL##", $smilies[$i]['code'], $s);
	}
	return $s;
}

?>
