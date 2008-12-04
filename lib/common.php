<?php
//  AcmlmBoard XD support - Main hub

//Possibility: make the date/time format string a user profile field.
$dateformat = "M jS Y, G:i"; //"Oct 15th 2008, 16:29"

$autoLockMonths = 2; //number of 30-day "months" after which to auto-close threads.
//DO NOT set this to zero -- it is a factor in a multiplication!


if($overallTidy)
	ob_start();

if(!isset($title))
	$title = "";

//WARNING: These things need to be kept in a certain order of execution.

include("supersqlescape.php");
include("feedback.php");
include("mysql.php");

$thisURL = $_SERVER['SCRIPT_NAME'];
if($q = $_SERVER['QUERY_STRING'])
	$thisURL .= "?$q";

include("loguser.php");
include("views.php");
include("snippets.php");
include("tidy.php");
include("post.php");

include("css/themelist.php");
$themeFiles = array_keys($themes);
$themeNames = array_values($themes);
$theme = $themeFiles[$loguser['scheme']];
$logopic = "img/themes/default/logo.png";
if(file_exists("img/themes/".$theme."/logo.png"))
	$logopic = "img/themes/".$theme."/logo.png";

if($theme == "") $theme = "default";

if(!isset($noAutoHeader))
	include("header.php");

?>
