<?php
//  AcmlmBoard XD support - Post functions

include_once("geshi.php");

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

function MakeFlash($match)
{
	global $flashloops;
	$flashloops++;
	return "<div class=\"swf\" style=\"width: ".($match[1]+4)."px;\"><div class=\"swfmain\" id=\"swf".$flashloops."main\" style=\"width: ".$match[1]."px; height: ".$match[2]."px;\"></div><div class=\"swfcontrol\"><span class=\"swfbuttonoff\" id=\"swf".$flashloops."play\" onclick=\"startFlash(".$flashloops."); return false;\">&#x25BA;</span><span class=\"swfbuttonon\" id=\"swf".$flashloops."stop\" onclick=\"stopFlash(".$flashloops."); return false;\">&#x25A0;</span><span class=\"swfurl\" id=\"swf".$flashloops."url\">".$match[3]."</span></div></div>";
}

function GeshiCallback($matches)
{
	$geshi = new GeSHi(trim($matches[1]), "csharp", null);
	$geshi->set_header_type(GESHI_HEADER_NONE);
	$geshi->enable_classes();
	return "<div class=\"geshi\">".str_replace("\n", "", $geshi->parse_code())."</div>";
}

function GeshiCallbackL($matches)
{
	$geshi = new GeSHi(trim($matches[2]), $matches[1], null);
	$geshi->set_header_type(GESHI_HEADER_NONE);
	$geshi->enable_classes();
	return "<div class=\"geshi\">".str_replace("\n", "", $geshi->parse_code())."</div>";
}

function MakeWikipedia($matches)
{
	$wikiTitle = $matches[2];
	$escaped = str_replace(" ", "_", $matches[1]);
	return "[url=http://en.wikipedia.org/wiki/".$escaped."]".$wikiTitle."[/url]";
}

function MakeShortWikipedia($matches)
{
	$wikiTitle = $matches[1];
	$escaped = str_replace(" ", "_", $wikiTitle);
	return "[url=http://en.wikipedia.org/wiki/".$escaped."]".$wikiTitle."[/url]";
}

function MakeTrope($matches)
{
	$tropeTitle = $matches[1];
	$tropeText = $matches[2];
	return "[url=http://tvtropes.org/pmwiki/pmwiki.php/Main/".$tropeTitle."]".$tropeText."[/url]";
}

function MakeShortTrope($matches)
{
	$tropeTitle = $matches[1];
	$tropeText = "";
	for($i = 0; $i < strlen($tropeTitle); $i++)
	{
		if(ctype_upper($tropeTitle[$i]))
			$tropeText .= " ";
		$tropeText .= $tropeTitle[$i];
	}
	$tropeText = trim($tropeText);
	return "[url=http://tvtropes.org/pmwiki/pmwiki.php/Main/".$tropeTitle."]".$tropeText."[/url]";
}

function MakeUserLink($matches)
{
	global $members;
	$id = (int)$matches[1];
	if(!isset($members[$id]))
	{
		$rUser = Query("select id, name, powerlevel, sex from users where id=".$id);
		if(NumRows($rUser))
			$members[$id] = Fetch($rUser);
		else
			return UserLink(array('id' => 0, 'name' => "Unknown User", 'sex' => 0, 'powerlevel' => -1));
	}
	return UserLink($members[$id]);
}

function FilterJS($match)
{
	$url = html_entity_decode($match[2]);
	if (stristr($url, "javascript:"))
		return "";
	return $match[0];
}

function GetSyndrome($activity)
{
	include("syndromes.php");
	$soFar = "";
	foreach($syndromes as $minAct => $syndrome)
		if($activity >= $minAct)
			$soFar = "<em style=\"color: ".$syndrome[1].";\">".$syndrome[0]."</em><br />";
	return $soFar;
}

function CleanUpPost($postText, $poster = "", $noSmilies = false)
{
	global $smilies;
	LoadSmilies();

	$s = $postText; //stripslashes($postText);
	$s = str_replace("\r\n","\n", $s);

	$s = preg_replace_callback("'\[source=(.*?)\](.*?)\[/source\]'si", "GeshiCallbackL", $s);
	$s = preg_replace_callback("'\[source\](.*?)\[/source\]'si", "GeshiCallback", $s);

	$s = preg_replace_callback("'\[trope=(.*?)\](.*?)\[/trope\]'si", "MakeTrope", $s);
	$s = preg_replace_callback("'\[trope\](.*?)\[/trope\]'si", "MakeShortTrope", $s);
	$s = preg_replace_callback("'\[wiki=(.*?)\](.*?)\[/wiki\]'si", "MakeWikipedia", $s);
	$s = preg_replace_callback("'\[wiki\](.*?)\[/wiki\]'si", "MakeShortWikipedia", $s);

	$s = preg_replace_callback("'\[user=([0-9]+)\]'si", "MakeUserLink", $s);

	//$s = str_replace("Xkeeper","XKitten", $s); //I couldn't help myself -- Kawa
	//$s = preg_replace("'([c|C])lassic'si","\\1lbuttic", $s); //Same here -- Kawa

	//De-tabled [code] tag, based on BH's...
    $list  = array("<"   ,"\\\"" ,"\\\\" ,"\\'","\r"  ,"["    ,":"    ,")"    ,"_"    );
    $list2 = array("&lt;","\""   ,"\\"   ,"\'" ,"<br/>","&#91;","&#58;","&#41;","&#95;");
    $s = preg_replace("'\[code\](.*?)\[/code\]'sie",
					'\''."<code class=\"Code block\">".'\''
					.'.str_replace($list,$list2,\'\\1\').\'</code>\'',$s);


    //[blackhole89] - [svg] tag
    $svgin="'<?xml version=\"1.0\"?>"
          ."<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" "
	  ."\"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">"
	  ."<svg xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" width=\"\\1\" height=\"\\2\" onload=\"InitSMIL(evt)\">'";
    $svgout="'</svg>'";
    $svglist1 = array("\\\"","\\\\","\\'");
    $svglist2 = array("\"","\\","\'");
    $s=preg_replace("'\[svg ([0-9]+) ([0-9]+)\](.*?)\[/svg\]'sie",
       '\''."<embed src=\"data:image/svg+xml;base64,"
      .'\''.".base64_encode($svgin.".'str_replace($svglist1,$svglist2,\'\\3\')'.".$svgout).".'"'
      ."\\\" type=\\\"image/svg+xml\\\" width=".'\'\\1\' height=\'\\2\''." />\"",$s);

	$s = preg_replace("'\[b\](.*?)\[/b\]'si","<strong>\\1</strong>", $s);
	$s = preg_replace("'\[i\](.*?)\[/i\]'si","<em>\\1</em>", $s);
	$s = preg_replace("'\[u\](.*?)\[/u\]'si","<u>\\1</u>", $s);
	$s = preg_replace("'\[s\](.*?)\[/s\]'si","<del>\\1</del>", $s);

	$s = preg_replace("'<b>(.*?)\</b>'si","<strong>\\1</strong>", $s);
	$s = preg_replace("'<i>(.*?)\</i>'si","<em>\\1</em>", $s);
	$s = preg_replace("'<u>(.*?)\</u>'si","<span class=\"underline\">\\1</span>", $s);
	$s = preg_replace("'<s>(.*?)\</s>'si","<del>\\1</del>", $s);

	//Do we need this?
	//$s = preg_replace("'\[c=([0123456789ABCDEFabcdef]+)\](.*?)\[/c\]'si","<span style=\"color: #\\1\">\\2</span>", $s);

	$s = str_replace("\n","<br />", $s);

	//Blacklisted tags
	$badTags = array('script','iframe','frame','blink','textarea','noscript','meta','xmp','plaintext');
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

	$s = preg_replace("@(ba|Ban)?(n)?( )?Acmlmboard (1.[0123456789abcdABCDxX]+)@si", "$1$3Swiss cheese ($4)", $s);


	//Various other stuff
	$s = preg_replace("'display:'si", "display<em></em>:", $s);

	//$s = preg_replace("'([\s]+)([o])([n])([a-z]*)'si","\\1\\2<z>\\3\\4", $s);
	/*
	$s = preg_replace('/<([^>]+)bonw+s*=s*"[^>]+"/Uis', "<$1", $s);
	$s = preg_replace("/<([^>]+)bonw+s*=s*'[^>]+'/Uis", "<$1", $s);
	$s = preg_replace('/<([^>]+)bonw+s*=s*[^>s]+/i', "<$1", $s);
	*/
	$onEvents = array("load","unload","click","dblclick","mousedown","mouseup","mouseover","mousemove","mouseout","focus","blur","keypress","keydown","keyup","submit","reset","select","change");
	foreach($onEvents as $onEvent)
		$s = str_ireplace("on".$onEvent, "on<em></em>".$onEvent, $s);

	$s = preg_replace("'-moz-binding'si"," -mo<em></em>z-binding", $s);
	$s = preg_replace("'filter:'si","filter<em></em>:>", $s);
	$s = preg_replace("'javascript:'si","javascript<em></em>:>", $s);

	$s = str_replace("[spoiler]","<div class=\"spoiler\"><button onclick=\"toggleSpoiler(this.parentNode);\">Show spoiler</button><div class=\"spoiled hidden\">", $s);
	$s = str_replace("[/spoiler]","</div></div>", $s);

	$s = preg_replace("'\[url\](.*?)\[/url\]'si","<a href=\"\\1\">\\1</a>", $s);
	$s = preg_replace("'\[url=(.*?)\](.*?)\[/url\]'si","<a href=\"\\1\">\\2</a>", $s);
	$s = preg_replace("'\[img\](.*?)\[/img\]'si","<img src=\"\\1\" alt=\"\">", $s);
	$s = preg_replace("'\[img=(.*?)\](.*?)\[/img\]'si","<img src=\"\\1\" alt=\"\\2\" title=\"\\2\">", $s);

	$s = preg_replace_callback("'\[swf ([0-9]+) ([0-9]+)\](.*?)\[/swf\]'si", "MakeFlash", $s);

	//--with both object AND embed
	$s = preg_replace("'\[youtube\](.*?)\[/youtube\]'si","<object width=\"425\" height=\"344\"><param name=\"movie\" value=\"http://www.youtube.com/v/\\1&amp;hl=en&amp;fs=1\"></param><param name=\"allowFullScreen\" value=\"true\"></param><param name=\"allowscriptaccess\" value=\"never\"></param><embed src=\"http://www.youtube.com/v/\\1&amp;hl=en&amp;fs=1\" type=\"application/x-shockwave-flash\" allowscriptaccess=\"never\" allowfullscreen=\"false\" width=\"425\" height=\"344\"></embed></object>", $s);
	//--with only object (XHTML1/HTML401 Strict)
	//$s = preg_replace("'\[youtube\](.*?)\[/youtube\]'si","<object width=\"425\" height=\"344\"><param name=\"movie\" value=\"http://www.youtube.com/v/\\1&amp;hl=en&amp;fs=1\"></param><param name=\"allowFullScreen\" value=\"true\"></param><param name=\"allowscriptaccess\" value=\"never\"></param></object>", $s);

	$s = preg_replace("'\[youtube/loop\](.*?)\[/youtube\]'si","<object width=\"425\" height=\"344\"><param name=\"movie\" value=\"http://www.youtube.com/v/\\1&amp;hl=en&amp;fs=1&amp;loop=1\"></param><param name=\"allowFullScreen\" value=\"true\"></param><param name=\"allowscriptaccess\" value=\"never\"></param><embed src=\"http://www.youtube.com/v/\\1&amp;hl=en&amp;fs=1&amp;loop=1\" type=\"application/x-shockwave-flash\" allowscriptaccess=\"never\" allowfullscreen=\"false\" width=\"425\" height=\"344\"></embed></object>", $s);

	$s = preg_replace("'\[video\](.*?)\[/video\]'si","<video src=\"\\1\" width=\"425\" height=\"344\"  controls=\"controls\">Video not supported &mdash; <a href=\"\\1\">download</a></video>", $s);

	$s =  str_replace("[quote]","<blockquote><div><hr />", $s);
	$s =  str_replace("[/quote]","<hr /></div></blockquote>", $s);
	$s = preg_replace("'\[quote=\"(.*?)\" id=\"(.*?)\"\]'si","<blockquote><div><small><i>Posted by <a href=\"thread.php?pid=\\2#\\2\">\\1</a></i></small><hr />", $s);
	$s = preg_replace("'\[quote=(.*?)\]'si","<blockquote><div><small><i>Posted by \\1</i></small><hr />", $s);
	$s = preg_replace("'\[reply=\"(.*?)\"\]'si","<blockquote><div><small><i>Sent by \\1</i></small><hr />", $s);
	//$s = preg_replace("'\[reply=\"(.*?)\" id=\"(.*?)\"\]'si","<blockquote><div><small><i>Sent by \\1</i></small><hr />", $s);

	$s = preg_replace_callback("@(href|src)\s*=\s*\"([^\"]+)\"@si", "FilterJS", $s);
	$s = preg_replace_callback("@(href|src)\s*=\s*'([^']+)'@si", "FilterJS", $s);
	$s = preg_replace_callback("@(href|src)\s*=\s*([^\s>]+)@si", "FilterJS", $s);

	$s = preg_replace("'>>([0-9]+)'si",">><a href=\"thread.php?pid=\\1#\\1\">\\1</a>", $s);
	if($poster)
		$s = preg_replace("'/me '","<b>* ".$poster."</b> ", $s);

	//Smilies
	if(!$noSmilies)
	{
	/*
		for($i = 0; $i < count($smilies); $i++)
			$s = str_replace($smilies[$i]['code'], "«".$smilies[$i]['image']."»", $s);
		for($i = 0; $i < count($smilies); $i++)
			$s = str_replace("«".$smilies[$i]['image']."»", "<img src=\"img/smilies/".$smilies[$i]['image']."\" alt=\"".$smilies[$i]['code']."\" />", $s);
	*/
		for($i = 0; $i < count($smilies); $i++)
		{
			$preg_special = array("\\","^","$",".","*","+","?","|","(",")","[","]","{","}","@");
			$preg_special_escape = array("\\\\","\\^","\\$","\\.","\\*","\\+","\\?","\\|","\\(","\\)","\\[","\\]","\\{","\\}","\\@");

			$s = preg_replace("@<([^>]+)(".str_replace($preg_special, $preg_special_escape, $smilies[$i]['code']).")+([^>]+)>@si", "<$1##LOLDONTREPLACESMILIESINHTMLTAGZLOL##$3>", $s);
			$s = str_replace($smilies[$i]['code'], "«".$smilies[$i]['image']."»", $s);
			$s = str_replace("«".$smilies[$i]['image']."»", "<img src=\"img/smilies/".$smilies[$i]['image']."\" alt=\"".str_replace(">", "&gt;", $smilies[$i]['code'])."\" />", $s);
			$s = str_replace("##LOLDONTREPLACESMILIESINHTMLTAGZLOL##", $smilies[$i]['code'], $s);
		}
	}
	
	include("macros.php");
	foreach($macros as $macro => $img)
		$s = str_replace(":".$macro.":", "<img src=\"img/macros/".$img."\" alt=\":".$macro.":\" />", $s);

	return $s;
}

function MakePost($post, $thread, $forum, $ispm=0)
{
	global $loguser, $loguserid, $dateformat, $theme, $hacks;

	$qBlock = "select * from blockedlayouts where user=".$post['uid']." and blockee=".$loguserid;
	$rBlock = Query($qBlock);
	$isBlocked = NumRows($rBlock) | $post['globalblock'] | $loguser['blocklayouts'] | $post['options'] & 1;

//Deletion behavior A -- post is simply made invisible.
	if($post['deleted'])
	{
		$meta = "Posted on ".gmdate($dateformat,$post['date']);
		$links = "Post deleted | ";
		if(CanMod($loguserid,$forum) || $post['uid'] == $loguserid)
			$links .= "<a href=\"editpost.php?id=".$post['id']."&amp;delete=2\">Undelete</a> | ";
		$links .= "ID: ".$post['id'];
		print "<div class=\"outline margin\">";
		print "<a name=\"".$post['id']."\"></a>";
		print "<table class=\"post\">";
		print "<tr>";
		print "<td class=\"side userlink\">";
		print UserLink($post, "uid");
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
			if($thread && $loguser['powerlevel'] > -1 && $forum != -2)
				$links .= "<a href=\"newreply.php?id=".$thread."&amp;quote=".$post['id']."\">Quote</a> | ";
			if(CanMod($loguserid, $forum) || $post['uid'] == $loguserid && $loguser['powerlevel'] > -1 && !$post['closed'])
				$links .= "<a href=\"editpost.php?id=".$post['id']."\">Edit</a> | ";
			if(CanMod($loguserid, $forum))
				$links .= "<a href=\"editpost.php?id=".$post['id']."&amp;delete=1\">Delete</a> | ";
			if($forum != -2)
				$links .= "ID: <a href=\"newreply.php?id=".$thread."&amp;link=".$post['id']."\">".$post['id']."</a>";
			else
				$links .= "ID: ".$post['id'];
			if($loguser['powerlevel'] > 0)
				$links .= " | ".$post['ip'];
		}

		$meta = (!$ispm ? "Posted on " : 'Sent on ').gmdate($dateformat,$post['date']);
		//Threadlinks for listpost.php
		if($forum == -2)
			$meta .= " in <a href=\"thread.php?id=".$post['thread']."\">".$post['threadname']."</a>";
		//Revisions
		if($post['revision'])
			$meta .= " (revision ".($post['revision']+1).")";
		//</revisions>
	} else
		$meta = "Sample post";

	if($forum==-1)
		$meta = "Posted in <a href=\"thread.php?id=".$thread['id']."\">".$thread['title']."</a>";

	//if($post['postbg'])
	//	$postbg = " style=\"background: url(".$post['postbg'].");\"";

	$sideBarStuff .= GetRank($post);
	if($sideBarStuff)
		$sideBarStuff .= "";
	if($post['title'])
		$sideBarStuff .= $post['title']."<br />";
	else
	{
		$levelRanks = array(-1=>"Banned", 0=>"", 1=>"Local mod", 2=>"Full mod", 3=>"Administrator");
		$sideBarStuff .= $levelRanks[$post['powerlevel']]."<br />";
	}
	$sideBarStuff .= GetSyndrome($post['activity']);
	if($post['picture'])
	{
		if($post['mood'] > 0 && file_exists("img/avatars/".$post['uid']."_".$post['mood']))
			$sideBarStuff .= "<img src=\"img/avatars/".$post['uid']."_".$post['mood']."\" alt=\"\" />";
		else
			$sideBarStuff .= "<img src=\"".$post['picture']."\" alt=\"\" />";
	}
	else
		$sideBarStuff .= "<div style=\"width: 50px; height: 50px;\">&nbsp;</div>";

	$lastpost = ($post['lastposttime'] ? timeunits(time() - $post['lastposttime']) : "none");
	$lastview = timeunits(time() - $post['lastactivity']);

	$sideBarStuff .= "<br />Posts: ".$post['num']."/".$post['posts'];
	$sideBarStuff .= "<br />Since: ".gmdate($loguser['dateformat'], $post['regdate']);
	$sideBarStuff .= "<br /><br />Last post: ".$lastpost;
	$sideBarStuff .= "<br />Last view: ".$lastview;

	if($hacks['themenames'] == 3)
	{
		$sideBarStuff = "";
		$isBlocked = 1;
	}

	if($post['lastactivity'] > time() - 3600)
		$sideBarStuff .= "<br />User is <strong>online</strong>";

	print "<div class=\"outline margin\">";
	if($post['id'] != "???")
		print "<a name=\"".$post['id']."\" />";
	print "<table class=\"post\">";
	print "<tr>";
	
	//Neritic uses a split sidebar.
	//print "<td class=\"side\" rowspan=\"2\">";
	//print UserLink($poster);
	print "<td class=\"side userlink topbar".$post['uid']."_1\">";
	print UserLink($post, "uid");
	print "</td>";
	print "<td class=\"meta right topbar".$post['uid']."_2\">";
	print "<div style=\"float: left;\">".$meta."</div>";
	//print $meta;
	//print "</td>";
	//print "<td class=\"links topbar".$post['uid']."_2\">";
	print $links;
	print "</td>";
	print "</tr>";

	print "<tr>";
	print "<td class=\"side sidebar".$post['uid']."\">";
	print "<div class=\"smallFonts\">";
	print $sideBarStuff;
	print "</div>";
	print "</td>";
	print "<td class=\"post mainbar".$post['uid']."\" colspan=\"2\">";

	if($post['postheader'] && !$isBlocked)
		print str_replace('$theme', $theme, CleanUpPost($post['postheader']));

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

	print CleanUpPost($post['text'],$post['name']);

	if($post['signature'] && !$isBlocked)
	{
		if(!$post['signsep'])
			print "<br />_________________________<br />";
		print CleanUpPost($post['signature']);
	}
	print "</td>";
	print "</tr>";
	print "</table>";
	print "</div>";
}

?>
