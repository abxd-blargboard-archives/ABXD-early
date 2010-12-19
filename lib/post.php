<?php
//  AcmlmBoard XD support - Post functions

include_once("geshi.php");
include_once("write.php");
include_once("filters.php");

function LoadSmilies($byOrder = FALSE)
{
	global $smilies, $smiliesOrdered;
	if($byOrder)
	{
		if(isset($smiliesOrdered))
			return;
		$rSmilies = Query("select * from smilies order by id asc");
		$smiliesOrdered = array();
		while($smiliesOrdered[] = Fetch($rSmilies));
	}
	else
	{
		if(isset($smilies))
			return;
		$rSmilies = Query("select * from smilies order by length(code) desc");
		$smilies = array();
		while($smilies[] = Fetch($rSmilies));
	}
}

function LoadBlocklayouts()
{
	global $blocklayouts, $loguserid;
	if(isset($blocklayouts))
		return;
	$rBlocks = Query("select * from blockedlayouts where blockee = ".$loguserid);
	while($block = Fetch($rBlocks))
		$blocklayouts[$block['user']] = 1;
	//$qBlock = "select * from blockedlayouts where user=".$post['uid']." and blockee=".$loguserid;
	//$rBlock = Query($qBlock);

}

function LoadRanks($rankset)
{
	global $ranks;	
	if(isset($ranks[$rankset]))
		return;
	$ranks[$poster['rankset']] = array();
	$rRanks = Query("select * from ranks where rset=".$rankset." order by num");
	while($rank = Fetch($rRanks))
		$ranks[$rankset][$rank['num']] = $rank['text'];
}

function GetRank($poster)
{
	global $ranks;
	if($poster['rankset'] == 0)
		return "";
	LoadRanks($poster['rankset']);
	$thisSet = $ranks[$poster['rankset']];
	if(!is_array($thisSet))
		return "";
	$ret = "";
	foreach($thisSet as $num => $text)
	{
		if($num > $poster['posts'])
			return $ret;
		$ret = $text;
	}

	/*
	$qRank = "select text from ranks where rset=".$poster['rankset']." and num<=".$poster['posts']." order by num desc limit 1";
	$rRank = Query($qRank);
	$rank = Fetch($rRank);
	return $rank['text'];
	*/
}

function GetToNextRank($poster)
{
	global $ranks;
	if($poster['rankset'] == 0)
		return "";
	LoadRanks($poster['rankset']);
	$thisSet = $ranks[$poster['rankset']];
	if(!is_array($thisSet))
		return 0;
	$ret = 0;
	foreach($thisSet as $num => $text)
	{
		$ret = $num - $poster['posts'];
		if($num > $poster['posts'])
			return $ret;
	}	
	
	/*
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
	*/
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
	return format(
"
	<div class=\"swf\" style=\"width: {0}px;\">
		<div class=\"swfmain\" id=\"swf{4}main\" style=\"width: {1}px; height: {2}px;\">
		</div>
		<div class=\"swfcontrol\">
			<span class=\"swfbuttonoff\" id=\"swf{4}play\" onclick=\"startFlash({4}); return false;\">
				&#x25BA;
			</span>
			<span class=\"swfbuttonon\" id=\"swf{4}stop\" onclick=\"stopFlash({4}); return false;\">
				&#x25A0;
			</span>
			<span class=\"swfurl\" id=\"swf{4}url\">
				{3}
			</span>
		</div>
	</div>
", $match[1] + 4, $match[1], $match[2], $match[3], $flashloops);
}

function GeshiCallback($matches)
{
	$geshi = new GeSHi(trim($matches[1]), "csharp", null);
	$geshi->set_header_type(GESHI_HEADER_NONE);
	$geshi->enable_classes();
	return format("<div class=\"geshi\">{0}</div>", str_replace("\n", "", $geshi->parse_code()));
}

function GeshiCallbackL($matches)
{
	$geshi = new GeSHi(trim($matches[2]), $matches[1], null);
	$geshi->set_header_type(GESHI_HEADER_NONE);
	$geshi->enable_classes();
	return format("<div class=\"geshi\">{0}</div>", str_replace("\n", "", $geshi->parse_code()));
}

function MakeWikipedia($matches)
{
	$wikiTitle = $matches[2];
	$escaped = str_replace(" ", "_", $matches[1]);
	return format("[url=http://en.wikipedia.org/wiki/{0}]{1}[/url]", $escaped, $wikiTitle);
}

function MakeShortWikipedia($matches)
{
	$wikiTitle = $matches[1];
	$escaped = str_replace(" ", "_", $wikiTitle);
	return format("[url=http://en.wikipedia.org/wiki/{0}]{1}[/url]", $escaped, $wikiTitle);
}

function MakeTrope($matches)
{
	$tropeTitle = $matches[1];
	$tropeText = $matches[2];
	return format("[url=http://tvtropes.org/pmwiki/pmwiki.php/Main/{0}]{1}[/url]", $tropeTitle, $tropeText);
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
	return format("[url=http://tvtropes.org/pmwiki/pmwiki.php/Main/{0}]{1}[/url]", $tropeTitle, $tropeText);
}

function MakeUserLink($matches)
{
	global $members;
	$id = (int)$matches[1];
	if(!isset($members[$id]))
	{
		$rUser = Query("select id, name, displayname, powerlevel, sex from users where id=".$id);
		if(NumRows($rUser))
			$members[$id] = Fetch($rUser);
		else
			return UserLink(array('id' => 0, 'name' => "Unknown User", 'sex' => 0, 'powerlevel' => -1));
	}
	return UserLink($members[$id]);
}

function ApplyNetiquetteToLinks($match)
{
	if (substr($match[1], 0, 7) != 'http://')
		return $match[0];

	if (stripos($match[1], 'http://'.$_SERVER['SERVER_NAME']) === 0)
		return $match[0];

	return $match[0].' target="_blank"';
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

function CleanUpPost($postText, $poster = "", $noSmilies = false, $noBr = false)
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

	if($noBr == FALSE)
		$s = str_replace("\n","<br />", $s);

	//Blacklisted tags
	$badTags = array('script','iframe','frame','blink','textarea','noscript','meta','xmp','plaintext');
	foreach($badTags as $tag)
	{
		$s = preg_replace("'<$tag(.*?)>'si", "&lt;$tag\\1>" ,$s);
		$s = preg_replace("'</$tag(.*?)>'si", "&lt;/$tag>", $s);
	}

	//Bad sites
	//$s = preg_replace("'jul.rusted'si", "zophar.", $s); //I didn't add this! Honest! -- Kawa
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
	//[SUGGESTION] Block "display: none" instead of just "display:" -- Mega-Mario
	$s = preg_replace("'display:'si", "display<em></em>:", $s);

	//$s = preg_replace("'([\s]+)([o])([n])([a-z]*)'si","\\1\\2<z>\\3\\4", $s);
	/*
	$s = preg_replace('/<([^>]+)bonw+s*=s*"[^>]+"/Uis', "<$1", $s);
	$s = preg_replace("/<([^>]+)bonw+s*=s*'[^>]+'/Uis", "<$1", $s);
	$s = preg_replace('/<([^>]+)bonw+s*=s*[^>s]+/i', "<$1", $s);
	*/
	$onEvents = array("load","unload","click","dblclick","mousedown","mouseup","mouseover","mousemove","mouseout","focus","blur","keypress","keydown","keyup","submit","reset","select","change","resize", "error");
	foreach($onEvents as $onEvent)
		$s = str_ireplace("on".$onEvent, "on<em></em>".$onEvent, $s);

	$s = preg_replace("'-moz-binding'si"," -mo<em></em>z-binding", $s);
	$s = preg_replace("'filter:'si","filter<em></em>:>", $s);
	$s = preg_replace("'javascript:'si","javascript<em></em>:>", $s);

	$s = str_replace("[spoiler]","<div class=\"spoiler\"><button onclick=\"toggleSpoiler(this.parentNode);\">Show spoiler</button><div class=\"spoiled hidden\">", $s);
	$s = preg_replace("'\[spoiler=(.*?)\]'si","<div class=\"spoiler\"><button onclick=\"toggleSpoiler(this.parentNode);\" class=\"named\">\\1</button><div class=\"spoiled hidden\">", $s);
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

	//<a href="http://tindeck.com/listen/givy"><img src="http://tindeck.com/image/givy/stats.png"border=0></a>
	$s = preg_replace("'\[tindeck\](.*?)\[/tindeck\]'si", "<a href=\"http://tindeck.com/listen/\\1\"><img src=\"http://tindeck.com/image/\\1/stats.png\" alt=\"Tindeck\" /></a>", $s);

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
	
	$s = preg_replace_callback("@<a[^>]+href\s*=\s*\"(.*?)\"@si", 'ApplyNetiquetteToLinks', $s);
	$s = preg_replace_callback("@<a[^>]+href\s*=\s*'(.*?)'@si", 'ApplyNetiquetteToLinks', $s);
	$s = preg_replace_callback("@<a[^>]+href\s*=\s*([^\"'][^\s>]*)@si", 'ApplyNetiquetteToLinks', $s);	

	include("macros.php");
	foreach($macros as $macro => $img)
		$s = str_replace(":".$macro.":", "<img src=\"img/macros/".$img."\" alt=\":".$macro.":\" />", $s);

	return $s;
}

function ApplyTags($text, $tags)
{
	$s = $text;
	foreach($tags as $tag => $val)
		$s = str_replace("&".$tag."&", $val, $s);
	if(is_numeric($tags['numposts']))
		$s = preg_replace('@&(\d+)&@sie', 'max($1 - '.$tags['numposts'].', 0)', $s);
	else
		$s = preg_replace("'&(\d+)&'si", "preview", $s);
	return $s;
}

function MakePost($post, $thread, $forum, $ispm=0)
{
	global $loguser, $loguserid, $dateformat, $theme, $hacks, $isBot, $blocklayouts, $postText;
	if(isset($_GET['pid']))
		$highlight = (int)$_GET['pid'];

	//$qBlock = "select * from blockedlayouts where user=".$post['uid']." and blockee=".$loguserid;
	//$rBlock = Query($qBlock);
	LoadBlockLayouts();
	$isBlocked = $blocklayouts[$post['uid']] /* NumRows($rBlock) */ | $post['globalblock'] | $loguser['blocklayouts'] | $post['options'] & 1;
	$noSmilies = $post['options'] & 2;
	$noBr = $post['options'] & 4;

	if($post['deleted'] && !ispm)
	{
		$meta = "Posted on ".cdate($dateformat,$post['date']);
		$links = "<ul class=\"pipemenu\"><li>Post deleted</li>";
		if(CanMod($loguserid,$forum) || $post['uid'] == $loguserid && IsAllowed("editPost", $post['id']))
			$links .= "<li><a href=\"editpost.php?id=".$post['id']."&amp;delete=2\">Undelete</a></li>";
		$links .= "<li>ID: ".$post['id']."</li></ul>";
		write(
"
		<div class=\"outline margin\">
			<a name=\"{0}\"></a>
			<table class=\"post\">
				<tr>
					<td class=\"side userlink\">
						{1}
					</td>
					<td class=\"smallFonts\" style=\"border-left: 0px none; border-right: 0px none;\">
						{2}
					</td>
					<td class=\"smallFonts right\" style=\"border-left: 0px none;\">
						{3}
					</td>
				</tr>
			</table>
		</div>
",	$post['id'], UserLink($post, "uid"), $meta, $links
);
		return;
	}

	if($ispm == 1)
		$thread = $ispm;

	if($thread)
	{
		$links = "";
		if(!$ispm && !$isBot)
		{
			$links .= "<ul class=\"pipemenu\"><li><a href=\"thread.php?pid=".$post['id']."#".$post['id']."\">Link</a></li>";
			if($thread && $loguser['powerlevel'] > -1 && $forum != -2 && IsAllowed("makeReply", $thread))
				$links .= "<li><a href=\"newreply.php?id=".$thread."&amp;quote=".$post['id']."\">Quote</a></li>";
			if(CanMod($loguserid, $forum) || $post['uid'] == $loguserid && $loguser['powerlevel'] > -1 && !$post['closed'] && IsAllowed("editPost", $post['id']))
				$links .= "<li><a href=\"editpost.php?id=".$post['id']."\">Edit</a></li>";
			if(CanMod($loguserid, $forum) && IsAllowed("editPost", $post['id']))
				$links .= "<li><a href=\"editpost.php?id=".$post['id']."&amp;delete=1\">Delete</a></li>";
			if($forum != -2 && IsAllowed("makeReply", $thread))
				$links .= "<li>ID: <a href=\"newreply.php?id=".$thread."&amp;link=".$post['id']."\">".$post['id']."</a></li>";
			else
				$links .= "<li>ID: ".$post['id']."</li>";
			if($loguser['powerlevel'] > 0)
				$links .= "<li>".$post['ip']."</li>";
			$links .= "</ul>";
		}

		$meta = (!$ispm ? "Posted on " : 'Sent on ').cdate($dateformat,$post['date']);
		//Threadlinks for listpost.php
		if($forum == -2)
			$meta .= " in <a href=\"thread.php?id=".$post['thread']."\">".htmlspecialchars($post['threadname'])."</a>";
		//Revisions
		if($post['revision'])
		{
			if(CanMod($loguserid, $forum))
				$meta .= " (<a href=\"javascript:void(0);\" onclick=\"showRevisions(".$post['id'].")\">revision ".$post['revision']."</a>)";
			else
				$meta .= " (revision ".$post['revision'].")";
		}
		//</revisions>
	}
	else
		$meta = "Sample post";

	if($forum==-1)
		$meta = "Posted in <a href=\"thread.php?id=".$thread['id']."\">".htmlspecialchars($thread['title'])."</a>";

	//if($post['postbg'])
	//	$postbg = " style=\"background: url(".$post['postbg'].");\"";

	$sideBarStuff .= GetRank($post);
	if($sideBarStuff)
		$sideBarStuff .= "<br />";
	if($post['title'])
		$sideBarStuff .= strip_tags(CleanUpPost($post['title'], "", true), "<b><strong><i><em><span><s><del><img><a><br><small>")."<br />";
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

	if($post['num'] != "preview")
		$sideBarStuff .= "<br />\nPosts: ".$post['num']."/".$post['posts'];
	else
		$sideBarStuff .= "<br />\nPosts: ".$post['posts'];
	
	$sideBarStuff .= "<br />\nSince: ".cdate($loguser['dateformat'], $post['regdate']);
	$sideBarStuff .= "<br /><br />\nLast post: ".$lastpost;
	$sideBarStuff .= "<br />\nLast view: ".$lastview;

	if($hacks['themenames'] == 3)
	{
		$sideBarStuff = "";
		$isBlocked = 1;
	}

	if($post['lastactivity'] > time() - 3600)
		$sideBarStuff .= "<br />\nUser is <strong>online</strong>";

	if($post['id'] != "preview")
		$anchor = "<a name=\"".$post['id']."\" />";
	if(!$isBlocked)
	{
		$topBar1 = "topbar".$post['uid']."_1";
		$topBar2 = "topbar".$post['uid']."_2";
		$sideBar = "sidebar".$post['uid'];
		$mainBar = "mainbar".$post['uid'];
	}

	$tags = array();
	$rankHax = $post['posts'];
	//if($post['num'] == "preview")
	//	$post['num'] = $post['posts'];
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

	$postText = ApplyTags(CleanUpPost($post['text'],$post['name'],$noSmilies, $noBr), $tags);
	
	runBucket("postManglers");

	if($post['signature'] && !$isBlocked)
	{
		$postFooter = ApplyTags(CleanUpPost($post['signature']), $tags);
		if(!$post['signsep'])
			$separator = "<br />_________________________<br />";
		else
			$separator = "<br />";
	}

	$postCode =
"
		<table class=\"post margin {14}\">
			<tr>
				<td class=\"side userlink {1}\">
					{0}
					{5}
				</td>
				<td class=\"meta right {2}\">
					<div style=\"float: left;\" id=\"meta_{13}\">
						{7}
					</div>
					<div style=\"float: left; display: none;\" id=\"dyna_{13}\">
						Hi.
					</div>
					{8}
				</td>
			</tr>
			<tr>
				<td class=\"side {3}\">
					<div class=\"smallFonts\">
						{6}
					</div>
				</td>
				<td class=\"post {4}\" id=\"post_{13}\">

					{9}

					{10}

					{12}
					{11}

				</td>
			</tr>
		</table>
";

	write($postCode,
			$anchor, $topBar1, $topBar2, $sideBar, $mainBar,
			UserLink($post, "uid"), $sideBarStuff, $meta, $links,
			$postHeader, $postText, $postFooter, $separator, $post['id'], $post['id'] == $highlight ? "highlightedPost" : "");

}

?>
