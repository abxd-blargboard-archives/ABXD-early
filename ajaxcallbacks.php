<?php
$noAutoHeader = TRUE;
$noViewCount = TRUE;
$noOnlineUsers = TRUE;
$noFooter = TRUE;
$ajax = TRUE;
include("lib/common.php");

$action = $_GET['a'];
$id = (int)$_GET['id'];
$hideTricks = "<a href=\"javascript:void(0)\" onclick=\"hideTricks(".$id.")\">Back</a>";
if($action == "q")	//Quote
{
	$qPost = "select * from posts where id=".$id;
	$rPost = Query($qPost);
	if(NumRows($rPost))
		$post = Fetch($rPost);
	else
		die("Unknown post ID.");

	$qThread = "select forum from threads where id=".$post['thread'];
	$rThread = Query($qThread);
	$thread = Fetch($rThread);
	$qForum = "select minpower from forums where id=".$thread['forum'];
	$rForum = Query($qForum);
	$forum = Fetch($rForum);
	if($forum['minpower'] > $loguser['powerlevel'])
	{
		$text = str_rot13("Pools closed due to not enough power. Prosecutors will be violated.");
	}
	else
	{
		$qText = "select text from posts_text where pid=".$id." order by revision desc limit 1";
		$text = FetchResult($qText, 0, 0);
	}

	$qPoster = "select name from users where id=".$post['user'];
	$rPoster = Query($qPoster);
	$poster = Fetch($rPoster);
	
	$text = preg_replace("'/me '","[b]* ".$poster['name']."[/b] ", $text);
	
	$reply = "[quote=\"".$poster['name']."\" id=\"".$id."\"]".$text."[/quote]";
	die($reply);
}
else if($action == "ou")	//Online Users
{
	die(OnlineUsers((int)$_GET['f']));
}
else if($action == "tf")	//Theme File
{
	include("css/themelist.php");
	$theme = $_GET['t'];
	if (!$themes[$theme]) die("css/default.css|img/themes/default/logo.png");
	$themeFile = "css/".$theme.".css";
	if(!file_exists($themeFile))
	{
		$themeFile = "css/".$theme.".php";
		if(!file_exists($themeFile))
			$themeFile = "css/default.css";
	}
	$logopic = "img/themes/default/logo.png";
	if(file_exists("img/themes/".$theme."/logo.png"))
		$logopic = "img/themes/".$theme."/logo.png";
	die($themeFile."|".$logopic);
}
else if($action == "ni")	//New Indicators
{
	$pl = $loguser['powerlevel'];

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

	$postreads = Query("select * from threadsread where id=".$loguserid);
	while($read1 = Fetch($postreads))
		$postread[$read1['thread']]=$read1['date'];

	$rCategories = Query("select name,minpower from categories");
	$category[] = "dummy";
	while($cat = Fetch($rCategories))
		$category[] = array("name" => $cat['name'], "minpower" => $cat['minpower']);

	$news = array();

	$rFora = Query("select f.* from forums f left join categories c on c.id=f.catid order by c.corder, c.id, f.forder");
	while($forum = Fetch($rFora))
	{
		if($category[$forum['catid']]['minpower'] > $pl)
			continue;

		$newstuff = 0;
		if(!$ignored[$forum['id']])
			foreach($threadsRead as $trd)
				if($trd['forum'] == $forum['id'] && $trd['lastpostdate'] > $postread[$trd['id']])
					$newstuff++;
		$news[] = $newstuff;		
	}

	die(join(",", $news));
}
elseif($action == "srl")	//Show Revision List
{
	$qPost = "select currentrevision, thread from posts where id=".$id;
	$rPost = Query($qPost);
	if(NumRows($rPost))
		$post = Fetch($rPost);
	else
		die("Unknown post ID #".$id.". ".$hideTricks);

	$qThread = "select forum from threads where id=".$post['thread'];
	$rThread = Query($qThread);
	$thread = Fetch($rThread);
	$qForum = "select minpower from forums where id=".$thread['forum'];
	$rForum = Query($qForum);
	$forum = Fetch($rForum);
	if($forum['minpower'] > $loguser['powerlevel'])
		die("No. ".$hideTricks);

	$reply = "Show revision: ";
	for($i = 0; $i <= $post['currentrevision']; $i++)
		$reply .= "<a href=\"javascript:void(0)\" onclick=\"showRevision(".$id.",".$i.")\">".$i."</a> ";
	$reply .= $hideTricks;
	die($reply);	
}
elseif($action == "sr")	//Show Revision
{

	$qPost = "select ";
	$qPost .=
		"posts.id, posts.date, posts.num, posts.deleted, posts.options, posts.mood, posts.ip, posts_text.text, posts_text.text, posts_text.revision, users.id as uid, users.name, users.displayname, users.rankset, users.powerlevel, users.title, users.sex, users.picture, users.posts, users.postheader, users.signature, users.signsep, users.globalblock, users.lastposttime, users.lastactivity, users.regdate, posts.thread";
	$qPost .= 
		" from posts left join posts_text on posts_text.pid = posts.id and posts_text.revision = ".(int)$_GET['rev']." left join users on users.id = posts.user";
	$qPost .= " where posts_text.pid=".$id;

	$rPost = Query($qPost);
	if(NumRows($rPost))
		$post = Fetch($rPost);
	else
		die("Unknown post ID #".$id." or revision missing.");

	$qThread = "select forum from threads where id=".$post['thread'];
	$rThread = Query($qThread);
	$thread = Fetch($rThread);
	$qForum = "select minpower from forums where id=".$thread['forum'];
	$rForum = Query($qForum);
	$forum = Fetch($rForum);
	if($forum['minpower'] > $loguser['powerlevel'])
		$post['text'] = "No.";

	LoadBlockLayouts();
	$isBlocked = $blocklayouts[$post['uid']] /* NumRows($rBlock) */ | $post['globalblock'] | $loguser['blocklayouts'] | $post['options'] & 1;
	$noSmilies = $post['options'] & 2;

	$tags = array();
	$rankHax = $post['posts'];
	if($post['num'] == "???")
		$post['num'] = $post['posts'];
	$post['posts'] = $post['num'];
	//Disable tags by commenting/removing this part.
	$tags = array
	(
		"numposts" => $post['num'],
		"5000" => 5000 - $post['num'],
		"20000" => 20000 - $post['num'],
		"30000" => 30000 - $post['num'],
		"rank" => GetRank($post),
	);

	$post['posts'] = $rankHax;

	if($post['postheader'] && !$isBlocked)
		$postHeader = str_replace('$theme', $theme, ApplyTags(CleanUpPost($post['postheader']), $tags));

	//Pirate Day filter
	if($hacks['postfilter'])
		include_once("filters.php");
	if($hacks['postfilter'] == 1 || "0919" == gmdate('md'))
		$post['text'] = PirateFilter($post['text']);
	else if($hacks['postfilter'] == 2)
		$post['text'] = ChefFilter($post['text']);
	else if($hacks['postfilter'] == 3)
		$post['text'] = KrautFilter($post['text']);
	else if($hacks['postfilter'] == 4)
		$post['text'] = JiveFilter($post['text']);
	else if($hacks['postfilter'] == 5)
		$post['text'] = FuddFilter($post['text']);

	$postText = ApplyTags(CleanUpPost($post['text'],$post['name'],$noSmilies), $tags);

	if($goggles)
	{
		$postText = str_replace("<!--", "<span style=\"color: #66ff66;\">&lt;!--", $postText);
		$postText = str_replace("-->", "--&gt;</span>", $postText);
	}

	if($post['signature'] && !$isBlocked)
	{
		$postFooter = ApplyTags(CleanUpPost($post['signature']), $tags);
		if(!$post['signsep'])
			$separator = "<br />_________________________<br />";
		else
			$separator = "<br />";
	}

	$reply = $postHeader.$postText.$separator.$postFooter;

	die($reply);	
}
elseif($action == "em")	//Email
{
	$blah = FetchResult("select email from users where id=".$id." and showemail=1");
	die($blah);
}
elseif($action == "vc")	//View Counter
{
	$blah = FetchResult("select views from misc");
	die(number_format($blah));
}

die("Unknown action.");
?>
