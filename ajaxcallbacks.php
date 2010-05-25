<?php
$noAutoHeader = TRUE;
$noViewCount = TRUE;
$noOnlineUsers = TRUE;
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
/*
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

	die($onlineUsers);
*/
	$fid = (int)$_GET['f'];
	die(OnlineUsers($fid));
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
die("Unknown action.");
?>
