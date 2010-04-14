<?php

$isFirefox = FALSE;
$isIE6 = FALSE;

//$lastKnownBrowser = $_SERVER['HTTP_USER_AGENT'];
$lastKnownBrowser = "Something";

$knownBrowsers = array
(
	"MSIE" => "Internet Explorer",
	"Nintendo Wii" => "Wii Internet Channel", //Opera/9.30 (Nintendo Wii; U; ; 3642; nl)
	"Nintendo DS" => "Nintendo DS Browser", //Opera/9.50 (Nintendo DSi; Opera/507; U; en-US)
	"Opera" => "Opera",
	"MozillaDeveloperPreview" => "Firefox dev",
	"Firefox" => "Firefox",
	"Chrome" => "Chrome",
	"Safari" => "Safari",
	"Konqueror" => "Konqueror",
	"Mozilla" => "Mozilla",
	"Lynx" => "Lynx",
	"Nokia" => "Nokia mobile",
);

$knownOSes = array
(
	"HTC_" => "HTC mobile",
	"Windows 4.0" => "Windows 95",
	"Windows 4.1" => "Windows 98",
	"Windows 4.9" => "Windows ME",
	"Windows NT 5.0" => "Windows NT",
	"Windows NT 5.1" => "Windows XP",
	"Windows NT 5.2" => "Windows XP 64",
	"Windows NT 6.0" => "Windows Vista",
	"Windows NT 6.1" => "Windows 7",
	"Windows Mobile" => "Windows Mobile",
	"Linux" => "Linux",
	"Mac OS X" => "Mac OS X %",
);

$ua = $_SERVER['HTTP_USER_AGENT'];

foreach($knownBrowsers as $code => $name)
{
	if (strpos($ua, $code) !== FALSE)
	{
		//$version = substr($ua, strpos($ua, $code) + strlen($code), 6);
		//$version = preg_replace('/[^0-9,.]/','',$version);
		
		$versionStart = strpos($ua, $code) + strlen($code);
		$version = GetVersion($ua, $versionStart);

		if (strpos($ua, "Opera/9.80") !== FALSE || $code == "Safari" && strpos($ua, "Version/") !== FALSE)
			$version = substr($ua, strpos($ua, "Version/") + 8);

		$isFirefox = ($code == "Firefox");
		$isIE6 = (strpos($ua, "MSIE 6.") !== FALSE);

		$lastKnownBrowser = $name." ".$version;
		break;
	}
}

$os = "";
foreach($knownOSes as $code => $name)
{
	if (strpos($ua, $code) !== FALSE)
	{
		$os = $name;
		
		if(strpos($name, "%") !== FALSE)
		{
			$versionStart = strpos($ua, $code) + strlen($code);
			$version = GetVersion($ua, $versionStart);
			$os = str_replace("%", $version, $os);
		}
	
		$lastKnownBrowser .= " on ".$os;
		break;
	}
}

$lastKnownBrowser .= "<!-- ".$ua." -->"; 

function GetVersion($ua, $versionStart)
{
	$numDots = 0;
	$version = "";
	for($i = $versionStart; $i < strlen($ua); $i++)
	{
		$ch = $ua[$i];
		if($ch == ';')
			break;
		if($ch == '_' && strpos($ua, "Mac OS X"))
			$ch = '.';
		if($ch == '.')
		{
			$numDots++;
			if($numDots == 3)
				break;
		}
		if(strpos("0123456789.", $ch) !== FALSE)
			$version .= $ch;
	}
	return $version;
}

?>