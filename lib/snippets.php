<?php
//  AcmlmBoard XD support - Handy snippets

include_once("write.php");

function usectime()
{
	$t=gettimeofday();
	return $t[sec]+$t[usec] / 1000000;
}

function Plural($i, $s)
{
	if($i == 1) //For 1, just return that.
		return $i." ".$s;

	if(substr($s,-1) == "y") //Grammar Nazi strikes back!
		$s = substr($s, 0, strlen($s)-1)."ies"; //query -> queries
	else
		$s .= "s"; //record -> records

	return $i." ".$s;
}

function DoFooter($buffer)
{
	global $noFooter, $timeStart, $queries, $overallTidy, $boardname, $title;
	
	if(!$noFooter)
		$footer = format(
"
		<div class=\"footer\">
			AcmlmBoard XD by Kawa, Arbe, Mega-Mario et al &mdash; version 2.0.1<br />
			AcmlmBoard &copy; Jean-Fran&ccedil;ois Lapointe<br/>
			Page rendered in {0} seconds with {1}.<br />

			<a href=\"http://validator.w3.org/check?uri=referer\">
				<img src=\"img/xhtml10.png\" alt=\"Valid XHTML 1.0 Strict\" />
			</a>
			<a href=\"http://jigsaw.w3.org/css-validator/\">
				<img src=\"img/css.png\" alt=\"Valid CSS!\" />
			</a>
			<a href=\"rss2.php\">
				<img src=\"img/rss.png\" alt=\"RSS 2.0 feed available\" />
			</a>
			<a href=\"http://opera.com\">
				<img src=\"img/opera.png\" alt=\"Opera!\" />
			</a>
			<a href=\"http://acmlm.kafuka.org/board/\">
				<img src=\"img/board2.png\" alt=\"We're a spin-off from here!\" />
			</a>

		</div>
	</div>
</body>
</html>
",	sprintf("%1.3f",usectime()-$timeStart), Plural($queries, "MySQL query"));
	
	$boardTitle = $boardname;
	if($title != "")
		$boardTitle .= " &raquo; ".$title;
	
	$raw = $buffer . $footer;
	$raw = str_replace("<title>[[BOARD TITLE HERE]]</title>", "<title>".$boardTitle."</title>", $raw);

	if(!$overallTidy)
	{
		return $raw;
	}
		
	$tidyConfig = array
	(
		"show-body-only"=>0,
		"output-xhtml"=>1,
		"doctype"=>"strict",
		"logical-emphasis"=>1,
		"alt-text"=>"",
		"drop-proprietary-attributes"=>1,
		"wrap"=>0,
		"preserve-entities"=>1,
		"indent"=>1,
		"input-encoding"=>"utf8",
		"char-encoding"=>"utf8",
		"output-encoding"=>"utf8",
		"new-blocklevel-tags"=>"video",
	);

	if(function_exists(OptimizeLayouts))
		$raw = OptimizeLayouts($raw);
	$clean = tidy_repair_string($raw, $tidyConfig);
		
	$textareaFixed = str_replace("\r", "", $clean);
	$textareaFixed = str_replace(" </text", "</text", $textareaFixed);
	$textareaFixed = str_replace("\n</text", "</text", $textareaFixed);
	//$textareaFixed = str_replace("\n</text", "</text", $textareaFixed);
	return $textareaFixed;
}

function GetRainbowColor()
{
	$stime = gettimeofday();
	$h = (($stime[usec] / 5) % 600);
	if($h < 100)
	{
		$r = 255;
		$g = 155 + $h;
		$b = 155;
	}
	else if($h < 200)
	{
		$r = 255 - $h + 100;
		$g = 255;
		$b = 155;
	}
	else if($h < 300)
	{
		$r = 155;
		$g = 255;
		$b = 155 + $h - 200;
	}
	else if($h < 400)
	{
		$r = 155;
		$g = 255 - $h + 300;
		$b = 255;
	}
	else if($h < 500)
	{
		$r = 155 + $h - 400;
		$g = 155;
		$b = 255;
	}
	else
	{
		$r = 255;
		$g = 155;
		$b = 255 - $h + 500;
	}
	return substr(dechex($r * 65536 + $g * 256 + $b), -6);
}

function UserLink($user, $field = "id")
{
	global $hacks;

	$fpow = $user['powerlevel'];
	$fsex = $user['sex'];
	$fname = $user['name'];

	if($hacks['alwayssamepower'])
		$fpow = $hacks['alwayssamepower'] - 1;
	if($hacks['alwayssamesex'])
		$fsex = $hacks['alwayssamesex'];

	$classing = " class=\"nc" . $fsex . (($fpow == -1) ? "x" : $fpow)."\"";

	if($hacks['themenames'] == 1)
	{
		global $lastJokeNameColor;
		$classing = " style=\"color: ";
		if($lastJokeNameColor % 2 == 1)
			$classing .= "#E16D6D; \"";
		else
			$classing .= "#44D04B; \"";
		if($fpow == -1)
			$classing = " class=\"nc0x\"";
		$lastJokeNameColor++;
	} else if($hacks['themenames'] == 2 && $fpow > -1)
	{
		$classing = " style =\"color: #".GetRainbowColor()."\"";
	} else if($hacks['themenames'] == 3)
	{
		if($fpow == 3)
		{
			$fname = "Administration";
			$classing = " class=\"nc23\"";
		} else if($fpow == -1)
		{
			$fname = "Idiot";
			$classing = " class=\"nc2x\"";
		} else
		{
			$fname = "Anonymous";
			$classing = " class=\"nc22\"";
		}
	}

	return "<a href=\"profile.php?id=".$user[$field]."\"><span".$classing.">".$fname."</span></a>";
	return "<a href=\"profile.php?id=".$user[$field]."\"".$classing.">".$fname."</a>";
}

function CanMod($userid, $fid)
{
	global $loguser;
	if($loguser['powerlevel'] > 1)
		return 1;
	if($loguser['powerlevel'] == 1)
	{
		$qMods = "select * from forummods where forum=".$fid." and user=".$userid;
		$rMods = Query($qMods);
		if(NumRows($rMods))
			return 1;
	}
	return 0;
}

function MakeCrumbs($path, $links)
{
	foreach($path as $text=>$link)
	{
		$link = str_replace("&","&amp;",$link);
		if($link)
			$crumbs .= "<a href=\"".$link."\">".$text."</a> &raquo; ";
		else
			$crumbs .= $text. " &raquo; ";
	}
	$crumbs = substr($crumbs, 0, strlen($crumbs) - 8);
	
	write(
"
<div class=\"margin outline cell2\">
	<div style=\"float: right;\">
		<ul class=\"pipemenu smallFonts\">
			{0}
		</ul>
	</div>
	{1}
</div>
", $links, $crumbs);
}


function TimeUnits($sec)
{
	if($sec <    60) return "$sec sec.";
	if($sec <  3600) return floor($sec/60)." min.";
	if($sec < 86400) return floor($sec/3600)." hour".($sec >= 7200 ? "s" : "");
	return floor($sec/86400)." day".($sec >= 172800 ? "s" : "");
}

function DoPrivateMessageBar()
{
	global $loguserid, $loguser, $dateformat;

	if($loguserid)
	{
		$qUnread = "select count(*) from pmsgs where userto = ".$loguserid." and msgread=0";
		$unread= FetchResult($qUnread);
		$content = "";
		if($unread)
		{
			$pmNotice = $loguser['usebanners'] ? "id=\"pmNotice\" " : "";
			$qLast = "select * from pmsgs where userto = ".$loguserid." and msgread=0 order by date desc limit 0,1";
			$rLast = Query($qLast);
			$last = Fetch($rLast);
			$qUser = "select * from users where id = ".$last['userfrom'];
			$rUser = Query($qUser);
			$user = Fetch($rUser);
			$content .= format(
"
		You have {0}</a>. 
		<a href=\"showprivate.php?id={1}\">Last message</a> from {2} on {3}.
",	Plural($unread, "new <a href=\"private.php\">private message"), $last['id'],
	UserLink($user), cdate($dateformat, $last['date']));
		}
		
		if($loguser['newcomments'])
		{
			$content .= format(
"
		You {0} have new comments in your <a href=\"profile.php?id={1}\">profile</a>.
",	$content != "" ? "also" : "", $loguserid);		
		}

		write(
"
	<div {0} class=\"outline margin cell1 smallFonts\">
		{1}
	</div>
", $pmNotice, $content);
	}
}

function DoSmileyBar($taname = "text")
{
	global $smilies;
	$expandAt = 26;
	LoadSmilies(TRUE);
	
	write(
"
	<div class=\"PoRT margin\" style=\"width: 180px;\">
		<div class=\"errort\">
			<strong>Smilies</strong>
		</div>
		<div class=\"errorc cell0\" id=\"smiliesContainer\">
");
	if(count($smilies) > $expandAt)
		write("<button class=\"expander\" id=\"smiliesExpand\" onclick=\"expandSmilies();\">&#x25BC;</button>"); 
	print "<div class=\"smilies\" id=\"commonSet\">";
	for($i = 0; $i < count($smilies) - 1; $i++)
	{
		if($i == $expandAt)
			print "</div><div class=\"smilies\" id=\"expandedSet\">";
		$s = $smilies[$i];
		print "<img src=\"img/smilies/".$s['image']."\" alt=\"".htmlentities($s['code'])."\" title=\"".htmlentities($s['code'])."\" onclick=\"insertSmiley(' ".str_replace("'", "\'", $s['code'])." ');\" />";
	}
	write("
			</div>
		</div>
	</div>
");
}

function DoPostHelp()
{
?>
	<div class="PoRT margin" style="width: 180px;">
		<div class="errort"><strong>Post help</strong></div>
		<div class="errorc left cell0">
			<button class="expander" id="postHelpExpand" onclick="expandPostHelp();">&#x25BC;</button>
			<div id="commonHelp">
				<h4>Presentation</h4>
				[b]&hellip;[/b] &mdash; <strong>bold type</strong> <br />
				[i]&hellip;[/i] &mdash; <em>italic</em> <br />
				[u]&hellip;[/u] &mdash; <span class="underline">underlined</span> <br />
				[s]&hellip;[/s] &mdash; <del>Strikethrough</del><br />
			</div>
			<div id="expandedHelp">
				[code]&hellip;[/code] &mdash; <code>Code block</code> <br />
				[spoiler]&hellip;[/spoiler] &mdash; Spoiler block <br />
				[source]&hellip;[/source] &mdash; Colorcoded block, assuming C# <br />
				[source=&hellip;]&hellip;[/source] &mdash; Colorcoded block, specific language<sup title="bnf, c, cpp, csharp, html4strict, javascript, lolcode, lua, mysql, php, qbasic, vbnet, xml">[which?]</sup> <br />
				<br />
				<h4>Links</h4>
				[img]http://&hellip;[/img] &mdash; insert image <br />
				[url]http://&hellip;[/url] <br />
				[url=http://&hellip;]&hellip;[/url] <br />
				>>&hellip; &mdash; link to post by ID <br />
				[trope]&hellip;[/trope] &mdash; link to trope <br />
				[trope=&hellip;]&hellip;[/trope] <br/>
				[wiki]&hellip;[/wiki] &mdash; link to Wikipedia <br/>
				[wiki=&hellip;]&hellip;[/wiki] <br/>
				[user=##] &mdash; link to user's profile by ID <br />
				<br />
				<h4>Quotations</h4>
				[quote]&hellip;[/quote] &mdash; untitled quote<br />
				[quote=&hellip;]&hellip;[/quote] &mdash; "Posted by &hellip;"<br />
				[quote="&hellip;" id="&hellip;"]&hellip;[/quote] &mdash; "Post by &hellip;" with link by post ID<br />
				<br />
				<h4>Embeds</h4>
				[youtube]&hellip;[/youtube] &mdash; video ID only please <br />
				[video]&hellip;[/video] &mdash; requires URL to mp4 or ogv file <br />
				[swf ## ##]&hellip;[/swf] &mdash; insert SWF clip<br />
				[svg ## ##]&hellip;[/svg] &mdash; insert SVG image<br />
				<br />
			</div>
			Most plain HTML also allowed
		</div>
	</div>
<?php
}

function OnlineUsers($forum = 0)
{
	$forumClause = "";
	$browseLocation = " online";
       
	if ($forum)
	{
		$forumClause = " and lastforum=".$forum;
		$forumName = FetchResult("SELECT title FROM forums WHERE id=".$forum);
		$browseLocation = " browsing ".$forumName;
	}
       
	$rOnlineUsers = Query("select id,name,sex,powerlevel,lastactivity,lastposttime,minipic from users where (lastactivity > ".(time()-300)." or lastposttime > ".(time()-300).")".$forumClause." order by name");
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
	$onlineUsers = $onlineUserCt." user".(($onlineUserCt > 1 || $onlineUserCt == 0) ? "s" : "").$browseLocation.($onlineUserCt ? ": " : ".").$onlineUsers;

	$guests = FetchResult("select count(*) from guests where bot=0 and date > ".(time() - 300).$forumClause);
	$bots = FetchResult("select count(*) from guests where bot=1 and date > ".(time() - 300).$forumClause);

	if($guests)
		$onlineUsers .= " | ".Plural($guests,"guest");
	if($bots)
		$onlineUsers .= " | ".Plural($bots,"bot");
	       
	return $onlineUsers;
}

?>
