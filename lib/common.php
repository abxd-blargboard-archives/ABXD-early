<?php
//  AcmlmBoard XD support - Main hub

//User settings
$boardname = "AcmlmBoard XD";
$logoalt = "board?";
$logotitle = "Board2? This is KAWAAA!";
$dateformat = "M jS Y, G:i"; //"Oct 15th 2008, 16:29"

$autoLockMonths = 2; //number of 30-day "months" after which to auto-close threads.
//If set to 0, disables auto-closing.

//Version 1.1.1 update: this has been replaced with a friendly warning in newreply.php.
$viewcountInterval = 10000; //how many views until user 1 gets noticed in PM

$overallTidy = 0;

//reCAPTCHA keys
$publickey  = "6Le8rQUAAAAAABTmoJGH5rC3dqaq_Phjte3J-jwK";
$privatekey = "6Le8rQUAAAAAAJfo_ESinXA97SO241Foq4CyPAKr";


include("hacks.php");

if($overallTidy)
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
	$openers = substr_count($text, "<table>") + substr_count($text, "<div>");
	$closers = substr_count($text, "</table>") + substr_count($text, "</div>");
	return ($openers != $closers);
}

function Report($message)
{
/*
	$reporters = Query("select ip from reporters");
	while($reporter = Fetch($reporters))
	{
		if($server = @fsockopen($reporter['ip'], 6668, $errno, $errstr, 1))
		{
			fwrite($server, $message."\0");
			fclose($server);
		}
	}
*/
}

?>
