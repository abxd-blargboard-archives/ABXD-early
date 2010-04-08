<?php
//  AcmlmBoard XD support - Post functions

function LoadSmilies()
{
	global $smilies;
	if(isset($smilies))
		return;
	$qSmilies = "select * from smilies";
	$rSmilies = Query($qSmilies);
	while($smilies[] = Fetch($rSmilies));
}

function GetRank($poster)
{
	if($poster['rankset'] == 0)
		return "";
	$qRank = "select text from ranks where rset=".$poster['rankset']." and num<=".$poster['posts']." order by num desc limit 1";
	$rRank = Query($qRank);
	$rank = Fetch($rRank);
	return $rank['text']."<br />";
}

function GetToNextRank($poster)
{
	if($poster['rankset'] == 0)
		return 0;
	$qRank = "select num from ranks where rset=".$poster['rankset']." and num > ".$poster['posts']." limit 1";
	$rRank = Query($qRank);
	if(NumRows($rRank))
	{
		$rank = Fetch($rRank);
		return $rank['num'] - $poster['posts'];
	}
	return 0;
}

/*
function MakeSpoiler($match)
{
	global $spoilers;
	$spoilers++;
	return "<div class=\"spoiler\"><button onclick=\"document.getElementById('spoiler".$spoilers."').className='';\">Spoiler</button><div class=\"spoiled\" id=\"spoiler".$spoilers."\">";
}
*/

function CleanUpPost($postText, $poster = "", $noSmilies = false)
{
	global $smilies;
	LoadSmilies();

	$s = $postText; //stripslashes($postText);
	$s = str_replace("\r\n","\n", $s);

	//$s = str_replace("Xkeeper","XKitten", $s); //I couldn't help myself -- Kawa
	//$s = preg_replace("'([c|C])lassic'si","\\1lbuttic", $s); //Same here -- Kawa

	$s = str_replace("[b]","<strong>", $s);
	$s = str_replace("[/b]","</strong>", $s);
	$s = str_replace("[i]","<em>", $s);
	$s = str_replace("[/i]","</em>", $s);
	$s = str_replace("[u]","<u>", $s);
	$s = str_replace("[/u]","</u>", $s);
	$s = str_replace("[s]","<del>", $s);
	$s = str_replace("[/s]","</del>", $s);

	$s = str_replace("<b>","<strong>", $s);
	$s = str_replace("</b>","</strong>", $s);
	$s = str_replace("<i>","<em>", $s);
	$s = str_replace("</i>","</em>", $s);
	$s = str_replace("<u>","<span class=\"underline\">", $s);
	$s = str_replace("</u>","</span>", $s);

	//De-tabled [code] tag, based on BH's...
    $list  = array("<"   ,"\\\"" ,"\\\\" ,"\\'","\r"  ,"["    ,":"    ,")"    ,"_"    );
    $list2 = array("&lt;","\""   ,"\\"   ,"\'" ,"<br/>","&#91;","&#58;","&#41;","&#95;");
    $s = preg_replace("'\[code\](.*?)\[/code\]'sie",
					'\''."<code class=\"Code block\">".'\''
					.'.str_replace($list,$list2,\'\\1\').\'</code>\'',$s);

	//Smilies
	if(!$noSmilies)
	{
		for($i = 0; $i < count($smilies); $i++)
			$s = str_replace($smilies[$i]['code'], "«".$smilies[$i]['image']."»", $s);
		for($i = 0; $i < count($smilies); $i++)
			$s = str_replace("«".$smilies[$i]['image']."»", "<img src=\"img/smilies/".$smilies[$i]['image']."\" alt=\"".$smilies[$i]['code']."\" />", $s);
	}

	$s = str_replace("\n","<br />", $s);

	//Blacklisted tags
	$badTags = array('script','iframe','textarea','noscript','meta','xmp','plaintext');
	foreach($badTags as $tag)
	{
		$s = preg_replace("'<$tag(.*?)>'si", "&lt;$tag\\1>" ,$s);
		$s = preg_replace("'</$tag(.*?)>'si", "&lt;/$tag>", $s);
	}

	//Bad sites
	$s = preg_replace("'jul.rusted'si", "zophar.", $s); //I didn't add this! Honest! -- Kawa
	$s = preg_replace("'goatse'si","goat<span>se</span>", $s);
	$s = preg_replace("'tubgirl.com'si","www.youtube.com/watch?v=EK2tWVj6lXw", $s);
	$s = preg_replace("'ogrish.com'si","www.youtube.com/watch?v=2iveTJXcp6k", $s);
	$s = preg_replace("'liveleak.com'si","www.youtube.com/watch?v=xhLxnlNcxv8", $s);
	$s = preg_replace("'charonboat.com'si","www.youtube.com/watch?v=c9BA5e2Of_U", $s);
	$s = preg_replace("'shrewsburycollege.co.uk'si","www.youtube.com/watch?v=EK2tWVj6lXw", $s);
	$s = preg_replace("'lemonparty.com'si","www.youtube.com/watch?v=EK2tWVj6lXw", $s);
	$s = preg_replace("'meatspin.com'si","www.youtube.com/watch?v=2iveTJXcp6k", $s);

	//Various other stuff
	$s = preg_replace("'display:'si", "display<z>:</z>", $s);
	//$s = preg_replace("'([\s]+)([o])([n])([a-z]*)'si","\\1\\2<z>\\3\\4", $s);
	$s = preg_replace("'-moz-binding'si"," -mo<z>z-binding", $s);
	$s = preg_replace("'filter:'si","filter<z>:</z>>", $s);
	$s = preg_replace("'javascript:'si","javascript<z>:</z>>", $s);
	$s = str_replace("[spoiler]","<div class=\"spoiler\"><button onclick=\"toggleSpoiler(this.parentNode);\">Show spoiler</button><div class=\"spoiled hidden\">", $s);
	//$s = preg_replace_callback("'\[spoiler\]'si", "MakeSpoiler", $s);
	$s = str_replace("[/spoiler]","</div></div>", $s);
	$s = preg_replace("'\[url\](.*?)\[/url\]'si","<a href=\"\\1\">\\1</a>", $s);
	$s = preg_replace("'\[url=(.*?)\](.*?)\[/url\]'si","<a href=\"\\1\">\\2</a>", $s);
	$s = preg_replace("'\[img\](.*?)\[/img\]'si","<img src=\"\\1\" alt=\"\">", $s);
	$s = preg_replace("'\[img=(.*?)\](.*?)\[/img\]'si","<img src=\"\\1\" alt=\"\\2\" title=\"\\2\">", $s);

	//--with both object AND embed
	$s = preg_replace("'\[youtube\](.*?)\[/youtube\]'si","<object width=\"425\" height=\"344\"><param name=\"movie\" value=\"http://www.youtube.com/v/\\1&hl=en&fs=1\"></param><param name=\"allowFullScreen\" value=\"true\"></param><param name=\"allowscriptaccess\" value=\"never\"></param><embed src=\"http://www.youtube.com/v/\\1&hl=en&fs=1\" type=\"application/x-shockwave-flash\" allowscriptaccess=\"never\" allowfullscreen=\"false\" width=\"425\" height=\"344\"></embed></object>", $s);
	//--with only object (XHTML1/HTML401 Strict)
	//$s = preg_replace("'\[youtube\](.*?)\[/youtube\]'si","<object width=\"425\" height=\"344\"><param name=\"movie\" value=\"http://www.youtube.com/v/\\1&hl=en&fs=1\"></param><param name=\"allowFullScreen\" value=\"true\"></param><param name=\"allowscriptaccess\" value=\"never\"></param></object>", $s);

	$s = preg_replace("'\[video\](.*?)\[/video\]'si","<video src=\"\\1\" width=\"425\" height=\"344\"  controls=\"controls\">Video not supported &mdash; <a href=\"\\1\">download</a></video>", $s);

	$s =  str_replace("[quote]","<blockquote><div><hr />", $s);
	$s =  str_replace("[/quote]","<hr /></div></blockquote>", $s);
	$s = preg_replace("'\[quote=\"(.*?)\" id=\"(.*?)\"\]'si","<blockquote><div><small><i>Posted by <a href=\"thread.php?pid=\\2#\\2\">\\1</a></i></small><hr />", $s);
	$s = preg_replace("'\[quote=(.*?)\]'si","<blockquote><div><small><i>Posted by \\1</i></small><hr />", $s);
	$s = preg_replace("'\[reply=\"(.*?)\"\]'si","<blockquote><div><small><i>Sent by \\1</i></small><hr />", $s);
	//$s = preg_replace("'\[reply=\"(.*?)\" id=\"(.*?)\"\]'si","<blockquote><div><small><i>Sent by \\1</i></small><hr />", $s);

	$s = preg_replace("'>>([0-9]+)'si",">><a href=\"thread.php?pid=\\1#\\1\">\\1</a>", $s);
	if($poster)
		$s = preg_replace("'/me '","<b>* ".$poster."</b> ", $s);

	return $s;
}

function MakePost($post, $poster, $thread, $forum, $ispm=0)
{
	global $loguser, $loguserid, $dateformat, $theme, $hacks;

	$qBlock = "select * from blockedlayouts where user=".$poster['id']." and blockee=".$loguserid;
	$rBlock = Query($qBlock);
	$isBlocked = NumRows($rBlock) | $poster['globalblock'] | $loguser['blocklayouts'] | $post['options'] & 1;

//Deletion behavior A -- post is simply made invisible.
	if($post['deleted'])
	{
		$meta = "Posted on ".gmdate($dateformat,$post['date']);
		$links = "Post deleted | ";
		if(CanMod($loguserid,$forum) || $post['user'] == $loguserid)
			$links .= "<a href=\"editpost.php?id=".$post['id']."&amp;delete=2\">Undelete</a> | ";
		$links .= "ID: ".$post['id'];
		print "<div class=\"outline margin\">";
		print "<a name=\"".$post['id']."\" />";
		print "<table class=\"post\">";
		print "<tr>";
		print "<td class=\"side\">";
		print UserLink($poster);
		print "</td>";
		print "<td class=\"smallFonts\" style=\"border-left: 0px none; border-right: 0px none;\">";
		print $meta;
		print "</td>";
		print "<td class=\"smallFonts right\" style=\"border-left: 0px none;\">";
		print $links;
		print "</td>";
		print "</tr>";
		print "</table>";
		print "</div>";
		return;
	}
//</Deletion method A>

	if($ispm == 1)
		$thread = $ispm;

	if($thread)
	{
		$links = "";
		if(!$ispm)
		{
			$links .= "<a href=\"thread.php?pid=".$post['id']."#".$post['id']."\">Link</a> | ";
			if($thread && $loguser['powerlevel'] > -1)
				$links .= "<a href=\"newreply.php?id=".$thread."&amp;quote=".$post['id']."\">Quote</a> | ";
			if(CanMod($loguserid, $forum) || $post['user'] == $loguserid && $loguser['powerlevel'] > -1)
				$links .= "<a href=\"editpost.php?id=".$post['id']."\">Edit</a> | ";
			if(CanMod($loguserid, $forum))
				$links .= "<a href=\"editpost.php?id=".$post['id']."&amp;delete=1\">Delete</a> | ";
			$links .= "ID: ".$post['id'];
			if($loguser['powerlevel'] > 0)
			$links .= " | ".$post['ip'];
		}

		$meta = (!$ispm ? "Posted on " : 'Sent on ').gmdate($dateformat,$post['date']);
		//Revisions
		if($post['revision'])
			$meta .= " (revision ".($post['revision']+1).")";
		//</revisions>
	} else
		$meta = "Sample post";

	if($forum==-1)
		$meta = "Posted in <a href=\"thread.php?id=".$thread['id']."\">".$thread['title']."</a>";

	if($poster['postbg'])
		$postbg = " style=\"background: url(".$poster['postbg'].");\"";

	$sideBarStuff .= GetRank($poster);
	if($sideBarStuff)
		$sideBarStuff .= "";
	if($poster['title'])
		$sideBarStuff .= $poster['title']."<br />";
	else
	{
		$levelRanks = array(-1=>"Banned", 0=>"", 1=>"Local mod", 2=>"Full mod", 3=>"Administrator");
		$sideBarStuff .= $levelRanks[$poster['powerlevel']]."<br />";
	}
	if($poster['picture'])
		$sideBarStuff .= "<img src=\"".$poster['picture']."\" alt=\"\" />";
	else
		$sideBarStuff .= "<div style=\"width: 50px; height: 50px;\">&nbsp;</div>";

	$lastpost = ($poster['lastposttime'] ? timeunits(time() - $poster['lastposttime']) : "none");
	$lastview = timeunits(time() - $poster['lastactivity']);

	$sideBarStuff .= "<br />Posts: ".$post['num']."/".$poster['posts'];
	$sideBarStuff .= "<br />Since: ".gmdate($loguser['dateformat'], $poster['regdate']);
	$sideBarStuff .= "<br /><br />Last post: ".$lastpost;
	$sideBarStuff .= "<br />Last view: ".$lastview;

	if($hacks['themenames'] == 3)
	{
		$sideBarStuff = "";
		$isBlocked = 1;
	}

	if($poster['lastactivity'] > time() - 3600)
		$sideBarStuff .= "<br />User is <strong>online</strong>";

	print "<div class=\"outline margin\">";
	if($post['id'] != "???")
		print "<a name=\"".$post['id']."\" />";
	print "<table class=\"post\">";
	print "<tr>";
	
	//Neritic uses a split sidebar.
	//print "<td class=\"side\" rowspan=\"2\">";
	//print UserLink($poster);
	print "<td class=\"side topbar".$poster['id']."_1\">";
	print UserLink($poster);
	print "</td>";
	print "<td class=\"meta topbar".$poster['id']."_2\">";
	print $meta;
	print "</td>";
	print "<td class=\"links topbar".$poster['id']."_2\">";
	print $links;
	print "</td>";
	print "</tr>";

	print "<tr>";
	print "<td class=\"side sidebar".$poster['id']."\">";
	print "<div class=\"smallFonts\">";
	print $sideBarStuff;
	print "</div>";
	print "</td>";
	print "<td class=\"post mainbar".$poster['id']."\" colspan=\"2\" ".$postbg.">";

	if($poster['postheader'] && !$isBlocked)
		print str_replace("\"".$theme, $theme, CleanUpPost($poster['postheader']));
	elseif($post['headtext'] && !$isBlocked)
		print str_replace("\"".$theme, $theme, CleanUpPost($post['headtext']));

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

	print CleanUpPost($post['text'],$poster['name']);

	if($poster['signature'] && !$isBlocked)
	{
		if(!$poster['signsep'])
			print "<br />_________________________<br />";
		print CleanUpPost($poster['signature']);
	} elseif($post['signtext'] && !$isBlocked)
	{
		if(!$poster['signsep'])
			print "<br />_________________________<br />";
		print CleanUpPost($post['signtext']);
	}
	print "</td>";
	print "</tr>";
	print "</table>";
	print "</div>";
}

?>
