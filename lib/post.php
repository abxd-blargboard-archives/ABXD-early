<?php
//  AcmlmBoard XD support - Post functions

include_once("geshi.php");
include_once("write.php");

function LoadSmilies($byOrder = FALSE)
{
	global $smilies, $smiliesOrdered;
	$smiliesR = array
	(
		')' => '\)',
		'(' => '\(',
		'/' => '\/',
		'+' => '\+',
		'|' => '\|',
		'^' => '\^',
		'?' => '\?',
		'[' => '\[',
		']' => '\]',
		'<' => '\<',
		'>' => '\>',
		':' => '\:',
		']' => '\]',
		'.' => '\.',
		'\'' => '\\\'',
	);
	if($byOrder)
	{
		if(isset($smiliesOrdered))
			return;
		$rSmilies = Query("select * from smilies order by id asc");
		$smiliesOrdered = array();
		while($smiley = Fetch($rSmilies))
		{
			//foreach ($smiliesR as $old => $new)
			//	$smiley['code'] = str_replace($old, $new, $smiley['code']);
			$smiliesOrdered[] = $smiley;
		}
	}
	else
	{
		if(isset($smilies))
			return;
		$rSmilies = Query("select * from smilies order by length(code) desc");
		$smilies = array();
		while($smiley = Fetch($rSmilies))
		{
			//foreach ($smiliesR as $old => $new)
			//	$smiley['code'] = str_replace($old, $new, $smiley['code']);
			$smilies[] = $smiley;
		}
	}
}

function ApplySmilies($text)
{
	global $smilies;
	foreach($smilies as $s)
	{
		$text = preg_replace("/\b(".$s['code'].")\b/si", "<img src=\"img/smilies/".htmlentities($s['image'])."\" />", $text);
	}
	return $text;
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

$text = "";
function CleanUpPost($postText, $poster = "", $noSmilies = false, $noBr = false)
{
	global $smilies, $text;
	static $orig, $repl;
	LoadSmilies();

	$s = $postText; //stripslashes($postText);
	$s = str_replace("\r\n","\n", $s);

	$s = preg_replace_callback("'\[source=(.*?)\](.*?)\[/source\]'si", "GeshiCallbackL", $s);
	$s = preg_replace_callback("'\[source\](.*?)\[/source\]'si", "GeshiCallback", $s);

	$s = preg_replace_callback("'\[user=([0-9]+)\]'si", "MakeUserLink", $s);

	//$s = str_replace("Xkeeper","XKitten", $s); //I couldn't help myself -- Kawa
	//$s = preg_replace("'([c|C])lassic'si","\\1lbuttic", $s); //Same here -- Kawa

	//De-tabled [code] tag, based on BH's...
    $list  = array("<"   ,"\\\"" ,"\\\\" ,"\\'","\r"  ,"["    ,":"    ,")"    ,"_"    );
    $list2 = array("&lt;","\""   ,"\\"   ,"\'" ,"<br/>","&#91;","&#58;","&#41;","&#95;");
    $s = preg_replace("'\[code\](.*?)\[/code\]'sie",
					'\''."<code class=\"Code block\">".'\''
					.'.str_replace($list,$list2,\'\\1\').\'</code>\'',$s);

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
	$badTags = array('script','iframe','frame','blink','textarea','noscript','meta','xmp','plaintext','marquee','embed','object');
	foreach($badTags as $tag)
	{
		$s = preg_replace("'<$tag(.*?)>'si", "&lt;$tag\\1>" ,$s);
		$s = preg_replace("'</$tag(.*?)>'si", "&lt;/$tag>", $s);
	}

	//Bad sites
	$s = preg_replace("'goatse'si","goat<span>se</span>", $s);
	$s = preg_replace("'tubgirl.com'si","www.youtube.com/watch?v=EK2tWVj6lXw", $s);
	$s = preg_replace("'ogrish.com'si","www.youtube.com/watch?v=2iveTJXcp6k", $s);
	$s = preg_replace("'liveleak.com'si","www.youtube.com/watch?v=xhLxnlNcxv8", $s);
	$s = preg_replace("'charonboat.com'si","www.youtube.com/watch?v=c9BA5e2Of_U", $s);
	$s = preg_replace("'shrewsburycollege.co.uk'si","www.youtube.com/watch?v=EK2tWVj6lXw", $s);
	$s = preg_replace("'lemonparty.com'si","www.youtube.com/watch?v=EK2tWVj6lXw", $s);
	$s = preg_replace("'meatspin.com'si","www.youtube.com/watch?v=2iveTJXcp6k", $s);

	//$s = preg_replace("@(\ba|\Ban)?(n)?( )?Acmlmboard (1\.[0123456789abcdABCDxX]+)@si", "$1$3Swiss cheese ($4)", $s);


	//Various other stuff
	//[SUGGESTION] Block "display: none" instead of just "display:" -- Mega-Mario
	$s = preg_replace("'display:'si", "display<em></em>:", $s);

	//$s = preg_replace("'([\s]+)([o])([n])([a-z]*)'si","\\1\\2<z>\\3\\4", $s);
	/*
	$s = preg_replace('/<([^>]+)bonw+s*=s*"[^>]+"/Uis', "<$1", $s);
	$s = preg_replace("/<([^>]+)bonw+s*=s*'[^>]+'/Uis", "<$1", $s);
	$s = preg_replace('/<([^>]+)bonw+s*=s*[^>s]+/i', "<$1", $s);
	*/
	/*
	$onEvents = array("load","unload","click","dblclick","mousedown","mouseup","mouseover","mousemove","mouseout","focus","blur","keypress","keydown","keyup","submit","reset","select","change","resize", "error", "scroll");
	foreach($onEvents as $onEvent)
		$s = str_ireplace("on".$onEvent, "on<em></em>".$onEvent, $s);
	*/
	$s = preg_replace("@(on)([^<>]+?)=@si", "$2=", $s);
	$s = EatThatPork($s); //has to be done before escaping "on" or it'll eat that too.
	//$s = str_replace("on", "&#x6F;&#x6E;", $s);
	//$s = str_replace("On", "&#x4F;&#x6E;", $s);
	//$s = str_replace("oN", "&#x6F;&#x4E;", $s);
	//$s = str_replace("ON", "&#x4F;&#x4E;", $s);
	
	$s = preg_replace("'-moz-binding'si"," -mo<em></em>z-binding", $s);
	$s = preg_replace("'filter:'si","filter<em></em>:>", $s);
	$s = preg_replace("'javascript:'si","javascript<em></em>:>", $s);

	$s = str_replace("[spoiler]","<div class=\"spoiler\"><button onclick=\"toggleSpoiler(this.parentNode);\">Show spoiler</button><div class=\"spoiled hidden\">", $s);
	$s = preg_replace("'\[spoiler=(.*?)\]'si","<div class=\"spoiler\"><button onclick=\"toggleSpoiler(this.parentNode);\" class=\"named\">\\1</button><div class=\"spoiled hidden\">", $s);
	$s = str_replace("[/spoiler]","</div></div>", $s);

	$s = preg_replace("'\[url\](.*?)\[/url\]'si","<a href=\"\\1\">\\1</a>", $s);
	$s = preg_replace("'\[url=[\'\"]?(.*?)[\'\"]?\](.*?)\[/url\]'si","<a href=\"\\1\">\\2</a>", $s);
	$s = preg_replace("'\[url=(.*?)\](.*?)\[/url\]'si","<a href=\"\\1\">\\2</a>", $s);
	$s = preg_replace("'\[img\](.*?)\[/img\]'si","<img src=\"\\1\" alt=\"\">", $s);
	$s = preg_replace("'\[img=(.*?)\](.*?)\[/img\]'si","<img src=\"\\1\" alt=\"\\2\" title=\"\\2\">", $s);

	$s =  str_replace("[quote]","<blockquote><div><hr />", $s);
	$s =  str_replace("[/quote]","<hr /></div></blockquote>", $s);
	$s = preg_replace("'\[quote=\"(.*?)\" id=\"(.*?)\"\]'si","<blockquote><div><small><i>Posted by <a href=\"thread.php?pid=\\2#\\2\">\\1</a></i></small><hr />", $s);
	$s = preg_replace("'\[quote=(.*?)\]'si","<blockquote><div><small><i>Posted by \\1</i></small><hr />", $s);
	$s = preg_replace("'\[reply=\"(.*?)\"\]'si","<blockquote><div><small><i>Sent by \\1</i></small><hr />", $s);

	$bucket = "bbCode"; include("./lib/pluginloader.php");

	$s = preg_replace_callback("@(href|src)\s*=\s*\"([^\"]+)\"@si", "FilterJS", $s);
	$s = preg_replace_callback("@(href|src)\s*=\s*'([^']+)'@si", "FilterJS", $s);
	$s = preg_replace_callback("@(href|src)\s*=\s*([^\s>]+)@si", "FilterJS", $s);

	$s = preg_replace("'>>([0-9]+)'si",">><a href=\"thread.php?pid=\\1#\\1\">\\1</a>", $s);
	if($poster)
		$s = preg_replace("'/me '","<b>* ".$poster."</b> ", $s);

	//Smilies
	if(!$noSmilies)
	{
		if (!isset($orig))
		{
			$orig = $repl = array();
			for ($i = 0; $i < count($smilies); $i++)
			{
				$orig[] = "/(?<=.\W|\W.|^\W)".preg_quote($smilies[$i]['code'], "/")."(?=.\W|\W.|\W$)/";
				$repl[] = "<img src=\"img/smilies/".$smilies[$i]['image']."\" />";
			}
		}
		$s = preg_replace($orig, $repl, " ".$s." ");
		$s = substr($s, 1, -1);
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
	if(!stristr($text, "&"))
		return $text;
	$s = $text;
	foreach($tags as $tag => $val)
		$s = str_replace("&".$tag."&", $val, $s);
	if(is_numeric($tags['numposts']))
		$s = preg_replace('@&(\d+)&@sie', 'max($1 - '.$tags['numposts'].', 0)', $s);
	else
		$s = preg_replace("'&(\d+)&'si", "preview", $s);
	return $s;
}

$sideBarStuff = "";
$sideBarData = 0;
function MakePost($post, $thread, $forum, $ispm=0)
{
	global $loguser, $loguserid, $dateformat, $theme, $hacks, $isBot, $blocklayouts, $postText, $sideBarStuff, $sideBarData;
	
	$sideBarStuff = "";
	
	if(isset($_GET['pid']))
		$highlight = (int)$_GET['pid'];

	//$qBlock = "select * from blockedlayouts where user=".$post['uid']." and blockee=".$loguserid;
	//$rBlock = Query($qBlock);
	LoadBlockLayouts();
	$isBlocked = $blocklayouts[$post['uid']] /* NumRows($rBlock) */ | $post['globalblock'] | $loguser['blocklayouts'] | $post['options'] & 1;
	$noSmilies = $post['options'] & 2;
	$noBr = $post['options'] & 4;

	if($post['deleted'] && !$ispm)
	{
		$meta = "Posted on ".cdate($dateformat,$post['date']);
		$links = "<ul class=\"pipemenu\"><li>Post deleted</li>";
		if(CanMod($loguserid,$forum))
		{
			if (IsAllowed("editPost", $post['id']))
				$links .= "<li><a href=\"editpost.php?id=".$post['id']."&amp;delete=2\">Undelete</a></li>";
			$links .= "<li><a href=\"#\" onclick=\"ReplacePost(".$post['id'].",true); return false;\">View</a></li>";
		}
		$links .= "<li>ID: ".$post['id']."</li></ul>";
		write(
"
		<table class=\"post margin\" id=\"post{0}\">
			<tr>
				<td class=\"side userlink\">
					<a name=\"{0}\"></a>
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
			if ($post['unfoldhax'])
			{
				$links = "<ul class=\"pipemenu\"><li>Post deleted</li>";
				if (IsAllowed("editPost", $post['id']))
					$links .= "<li><a href=\"editpost.php?id=".$post['id']."&amp;delete=2\">Undelete</a></li>";
				$links .= "<li><a href=\"#\" onclick=\"ReplacePost(".$post['id'].",false); return false;\">Close</a></li>";
				$links .= "<li>ID: ".$post['id']."</li></ul>";
			}
			else
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
		
	$sideBarStuff .= "<br />\nSince: ".cdate($loguser['dateformat'], $post['regdate'])."<br />";

	$bucket = "sidebar"; include("./lib/pluginloader.php");

	$sideBarStuff .= "<br />\nLast post: ".$lastpost;
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
		"numdays" => floor((time()-$post['regdate'])/86400),
		"date" => cdate($dateformat,$post['date']),
		"rank" => GetRank($post),
	);
	$bucket = "amperTags"; include("./lib/pluginloader.php");

	$post['posts'] = $rankHax;
	if($post['postheader'] && !$isBlocked)
		$postHeader = str_replace('$theme', $theme, ApplyTags(CleanUpPost($post['postheader'], "", $noSmilies, true), $tags));

	$postText = ApplyTags(CleanUpPost($post['text'],$post['name'], $noSmilies, $noBr), $tags);
	
	$bucket = "postMangler"; include("./lib/pluginloader.php");

	if($post['signature'] && !$isBlocked)
	{
		$postFooter = ApplyTags(CleanUpPost($post['signature'], "", $noSmilies, true), $tags);
		if(!$post['signsep'])
			$separator = "<br />_________________________<br />";
		else
			$separator = "<br />";
	}

	$postCode =
"
		<table class=\"post margin {14}\" id=\"post{13}\">
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


//Scans for any numerical entities that decode to the 7-bit printable ASCII range and removes them.
//This makes a last-minute hack impossible where a javascript: link is given completely in absurd and malformed entities.
function EatThatPork($s)
{
	$s = preg_replace_callback("/(&#)([X|x]*)([a-fA-f0-9]+(?![a-zA-Z0-9]))(;*)/", "CheckKosher", $s);
	return $s;
}

function CheckKosher($matches)
{
	$num = ltrim($matches[3], "0");
	if($matches[2])
		$num = hexdec($num);
	if($num < 127)
		return ""; //"&#xA4;";
	else
		return "&#x".dechex($num).";"; //$matches[0];
}

?>
