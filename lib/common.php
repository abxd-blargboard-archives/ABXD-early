<?php
//  AcmlmBoard XD support - Main hub

//User settings
$boardname = "ABXD/NikoBoard";
$logoalt = "Niko's Board";
$logotitle = "Totally not Kawa's Board";
//$poratitle = "Points of Required Attention Span&trade;";
$dateformat = "M jS Y, G:i"; //"Oct 15th 2008, 16:29"

$autoLockMonths = 2; //number of 30-day "months" after which to auto-close threads.
//If set to 0, disables auto-closing.

//Version 1.1.1 update: this has been replaced with a friendly warning in newreply.php.
$viewcountInterval = 10000; //how many views until user 1 gets noticed in PM

$overallTidy = 1;

$cynthiaUA = "Cynthetiq 0.24 on Windows 7 <!-- Cynthetiq/0.24 (Windows NT 6.1; U; en; .NET CLR 3.5.30729) -->";

//hack settings:
//	forcetheme -> theme ID, -1 to disable
//	themenames -> 0 - disabled
//				  1 - christmas
//				  2 - rainbow
//				  3 - anonymous
//	postfilter -> 0 - disabled
//				  1 - pirate
//				  2 - swedish
//				  3 - kraut
//				  4 - jive
//				  5 - fudd
//$hacks['forcetheme'] = 1;
//$hacks['themenames'] = 2;
//$hacks['postfilter'] = 3;
include("settings.php");

//if($overallTidy)
	ob_start();

if(!isset($title))
	$title = "";

//WARNING: These things need to be kept in a certain order of execution.

include("mysql.php");
include("supersqlescape.php");
include("feedback.php");

$thisURL = $_SERVER['SCRIPT_NAME'];
if($q = $_SERVER['QUERY_STRING'])
	$thisURL .= "?$q";

include("snippets.php");
include("loguser.php");

if(!isset($noViewCount))
	include("views.php");

include("tidy.php");
include("post.php");

include("css/themelist.php");
$themeFiles = array_keys($themes);
$themeNames = array_values($themes);
$theme = $themeFiles[$loguser['scheme']];
$themeFile = $theme.".css";
if(!file_exists("css/".$themeFile))
{
	$themeFile = $theme.".php";
	if(!file_exists("css/".$themeFile))
		$themeFile = "default.css";
}
$logopic = "img/themes/default/logo.png";
if(file_exists("img/themes/".$theme."/logo.png"))
	$logopic = "img/themes/".$theme."/logo.png";

if(!isset($noAutoHeader))
	include("header.php");


function justEscape($text)
{
	if(get_magic_quotes_gpc())
		return mysql_real_escape_string(stripslashes($text));
	else
		return mysql_real_escape_string($text);
}

function deSlashMagic($text)
{
	if(get_magic_quotes_gpc())
		return stripslashes($text);
	else
		return $text;
}

//Simple version -- may expand later.
function CheckTableBreaks($text)
{
	$text = strtolower($text);
	$openers = substr_count($text, "<table") + substr_count($text, "<div");
	$closers = substr_count($text, "</table>") + substr_count($text, "</div>");
	return ($openers != $closers);
}

function htmlval($text)
{
	//$text = str_replace("\"", "&quot;", $text);
	//$text = str_replace("<", "&lt;", $text);
	//return $text;
	return htmlentities($text, ENT_COMPAT, "UTF-8");
}

function filterPollColors($input)
{
/*
	$valid = "#0123456789ABCDEFabcdef";
	$output = "";
	for($i = 0; $i < strlen($input); $i++)
		if(strpos($valid, $input[$i]) !== FALSE)
			$output .= $input[$i];
	return $output;
*/
	return preg_replace("@[^#0123456789abcdef]@si", "", $input);
}

function RecalculateKarma($uid)
{
	$karma = 100;
	$karmaWeights = array(5, 10, 10, 15);
	$qKarma = "select powerlevel, up from uservotes left join users on id=voter where uid=".$uid." and powerlevel > -1";
	$rKarma = Query($qKarma);
	while($k = Fetch($rKarma))
	{
		if($k['up'])
			$karma += $karmaWeights[$k['powerlevel']];
		else
			$karma -= $karmaWeights[$k['powerlevel']];
	}
	Query("update users set karma=".$karma." where id=".$uid);
	return $karma;
}

function ParseThreadTags(&$title)
{
	preg_match_all("/\[(.*?)\]/", $title, $matches);
	foreach($matches[1] as $tag)
	{
		$title = str_replace("[".$tag."]", "", $title);
		$tag = strtolower($tag);
		
		//Start at a hue that makes "18" red.
		$hash = -105;
		for($i = 0; $i < strlen($tag); $i++)
			$hash += ord($tag[$i]);

		//That multiplier is only there to make "nsfw" and "18" the same color.
		$color = "hsl(".(($hash * 57) % 360).", 70%, 40%)";
		
		$tags .= "<span class=\"threadTag\" style=\"background-color: ".$color.";\">".$tag."</span>";
	}
	if($tags)
		$tags = " ".$tags;
	return $tags;
}

?>
