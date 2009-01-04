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

function MakeSpoiler($match)
{
	global $spoilers;
	$spoilers++;
	return "<div class=\"spoiler\"><button onclick=\"document.getElementById('spoiler".$spoilers."').className='';\">Spoiler</button><div class=\"spoiled\" id=\"spoiler".$spoilers."\">";
}

function CleanUpPost($postText, $poster = "")
{
	global $smilies;
	LoadSmilies();

	$s = $postText;

	$s = str_replace("Xkeeper","XKitten", $s); //I couldn't help myself -- Kawa
	$s = preg_replace("'([c|C])lassic'si","\\1lbuttic", $s); //Same here -- Kawa

	$s = str_replace("[b]","<strong>", $s);
	$s = str_replace("[/b]","</strong>", $s);
	$s = str_replace("[i]","<em>", $s);
	$s = str_replace("[/i]","</em>", $s);
	$s = str_replace("[u]","<u>", $s);
	$s = str_replace("[/u]","</u>", $s);

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
	for($i = 0; $i < count($smilies); $i++)
		$s = str_replace($smilies[$i]['code'], "«".$smilies[$i]['image']."»", $s);
	for($i = 0; $i < count($smilies); $i++)
		$s = str_replace("«".$smilies[$i]['image']."»", "<img src=\"img/smilies/".$smilies[$i]['image']."\" alt=\"".$smilies[$i]['code']."\" />", $s);

	$s = str_replace("\n","<br />", $s);

	//Blacklisted tags
	$badTags = array('script','iframe','textarea','noscript','meta','xmp','plaintext');
	foreach($badTags as $tag)
	{
		$s = preg_replace("'<$tag(.*?)>'si", "&lt;$tag\\1>" ,$s);
		$s = preg_replace("'</$tag(.*?)>'si", "&lt;/$tag>", $s);
	}

	//Bad sites
	$s = preg_replace("'jul.rusted'si", "jul<span>.ru</span>sted", $s); //I didn't add this! Honest! -- Kawa
	$s = preg_replace("'goatse'si","goat<span>se</span>", $s);
	$s = preg_replace("'tubgirl'si","tub<span>girl</span>", $s);
	$s = preg_replace("'ogrish'si","og<span>ri</span>sh", $s);
	$s = preg_replace("'liveleak'si","liv<span>ele</span>ak", $s);
	$s = preg_replace("'charonboat'si","char<span>onb</span>oat", $s);
	$s = preg_replace("'shrewsburycollege'si","shrews<span>bury</span>college", $s);
	$s = preg_replace("'lemonparty'si","lemon<span>party</span>", $s);
	$s = preg_replace("'meatspin'si","me<span>atsp</span>in", $s);

	//Various other stuff
	$s = preg_replace("'display:'si", "display&#58;", $s);
	//$s = preg_replace("'([\s]+)([o])([n])([a-z]*)'si","\\1\\2<z>\\3\\4", $s);
	$s = preg_replace("'-moz-binding'si"," -mo<z>z-binding", $s);
	$s = preg_replace("'filter:'si","filter&#58;>", $s);
	$s = preg_replace("'javascript:'si","javascript&#58;>", $s);
	//$s =  str_replace("[spoiler]","<div style=\"color: black; background: black;\" class=\"smallFonts\"><span style=\"color: white; font-weight: bold;\">Spoiler:</span><br />", $s);
	//$s =  str_replace("[/spoiler]","</div>", $s);
	//$s = str_replace("[spoiler]","<div class=\"spoiler\"><button onclick=\"this.parentElement.children(1).className='';\">Spoiler</button><div class=\"spoiled\">", $s);
	$s = preg_replace_callback("'\[spoiler\]'si", "MakeSpoiler", $s);
	$s = str_replace("[/spoiler]","</div></div>", $s);
	$s = preg_replace("'\[url\](.*?)\[/url\]'si","<a href=\"\\1\">\\1</a>", $s);
	$s = preg_replace("'\[url=(.*?)\](.*?)\[/url\]'si","<a href=\"\\1\">\\2</a>", $s);
	$s = preg_replace("'\[img\](.*?)\[/img\]'si","<img src=\"\\1\" alt=\"\">", $s);
	$s = preg_replace("'\[img=(.*?)\](.*?)\[/img\]'si","<img src=\"\\1\" alt=\"\\2\" title=\"\\2\">", $s);

	$s =  str_replace("[quote]","<blockquote><div><hr />", $s);
	$s =  str_replace("[/quote]","<hr /></div></blockquote>", $s);
	$s = preg_replace("'\[quote=\"(.*?)\" id=\"(.*?)\"\]'si","<blockquote><div><small><i>Posted by <a href=\"thread.php?pid=\\2#\\2\">\\1</a></i></small><hr />", $s);
	$s = preg_replace("'\[quote=(.*?)\]'si","<blockquote><div><small><i>Posted by \\1</i></small><hr />", $s);
	$s = preg_replace("'\[reply=\"(.*?)\" id=\"(.*?)\"\]'si","<blockquote><div><small><i>Sent by \\1</i></small><hr />", $s);

	$s = preg_replace("'>>([0-9]+)'si",">><a href=\"thread.php?pid=\\1#\\1\">\\1</a>", $s);
	if($poster)
		$s = preg_replace("'/me '","<b>* ".$poster."</b> ", $s);

	return $s;
}

function MakePost($post, $poster, $thread, $forum, $ispm=0)
{
	global $loguser, $loguserid, $dateformat, $theme;

	$qBlock = "select * from blockedlayouts where user=".$poster['id']." and blockee=".$loguserid;
	$rBlock = Query($qBlock);
	$isBlocked = NumRows($rBlock) | $poster['globalblock'] | $loguser['blocklayouts'] | $post['options'] & 1;

//Deletion behavior A -- post is simply made invisible.
	if($post['deleted'])
	{
		$meta = "Posted on ".date($dateformat,$post['date']);
		$links = "Post deleted | ";
		if(CanMod($loguserid,$forum) || $post['user'] == $loguserid)
			$links .= "<a href=\"editpost.php?id=".$post['id']."&amp;delete=2\">Undelete</a> | ";
		$links .= "ID: ".$post['id'];
		print "<div class=\"border\">";
		print "<a name=\"".$post['id']."\" />";
		print "<table class=\"post\">";
		print "<tr>";
		print "<td class=\"side\">";
		print UserLink($poster)."";
		print "</td>";
		print "<td class=\"smallFonts\" style=\"border-left: 0px none; border-right: 0px none;\">";
		print "".$meta."";
		print "</td>";
		print "<td class=\"smallFonts right\" style=\"border-left: 0px none;\">";
		print "".$links."";
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

		$meta = (!$ispm ? "Posted on " : 'Sent on ').date($dateformat,$post['date']);
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
	if($poster['title'])
		$sideBarStuff .= $poster['title']."<br />";
	else
	{
		$levelRanks = array(-1=>"Banned<br />", 0=>"", 1=>"Local mod<br />", 2=>"Full mod<br />", 3=>"Administrator<br />");
		$sideBarStuff .= $levelRanks[$poster['powerlevel']];
	}
	if($poster['picture'])
		$sideBarStuff .= "<img src=\"".$poster['picture']."\" alt=\"\" />";
	else
		$sideBarStuff .= "<div style=\"width: 50px; height: 50px;\">&nbsp;</div>";

	$lastpost = ($poster['lastposttime'] ? timeunits(time() - $poster['lastposttime']) : "none");
	$lastview = timeunits(time() - $poster['lastactivity']);

	$sideBarStuff .= "<br />Posts: ".$post['num']."/".$poster['posts'];
	$sideBarStuff .= "<br />Since: ".date($loguser['dateformat'], $poster['regdate']);
	$sideBarStuff .= "<br /><br />Last post: ".$lastpost;
	$sideBarStuff .= "<br />Last view: ".$lastview;

	if($poster['lastactivity'] > time() - 3600)
		$sideBarStuff .= "<br />User is <strong>online</strong>";

	print "<div class=\"border\">";
	if($post['id'] != "???")
		print "<a name=\"".$post['id']."\" />";
	print "<table class=\"post\">";
	print "<tr>";
	print "<td class=\"side\" rowspan=\"2\">";
	print UserLink($poster)."";
	print "<div class=\"smallFonts\">";
	print $sideBarStuff;
	print "</div>";
	print "</td>";
	print "<td class=\"meta\">";
	print "".$meta."";
	print "</td>";
	print "<td class=\"links\">";
	print "".$links."";
	print "</td>";
	print "</tr>";
	print "<tr>";
	print "<td class=\"post\" colspan=\"2\" ".$postbg.">";

	if($poster['postheader'] && !$isBlocked)
		print str_replace("\$theme", $theme, CleanUpPost($poster['postheader']));
	elseif($post['headtext'] && !$isBlocked)
		print str_replace("\$theme", $theme, CleanUpPost($post['headtext']));

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
