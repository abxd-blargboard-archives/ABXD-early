<?php
$noAutoHeader = TRUE;
$noViewCount = TRUE;
$noOnlineUsers = TRUE;
$noFooter = TRUE;
$ajax = TRUE;
include("lib/common.php");

$action = $_GET['a'];
$id = (int)$_GET['id'];
if($action == "q")
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
	$reply = "[quote=\"".$poster['name']."\" id=\"".$id."\"]".$text."[/quote]";
	die($reply);
}
else if($action == "o")
{
	die(OnlineUsers((int)$_GET['f']));
}
else if($action == "tf")
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
else if($action = "ni")
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

	$rFora = Query("select * from forums order by catid, forder");
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

die("Unknown action.");
?>
