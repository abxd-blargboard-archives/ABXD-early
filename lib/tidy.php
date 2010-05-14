<?php
//  AcmlmBoard XD support - Tidy support

function htmlentities2($text)
{
//	return htmlentities($text, ENT_COMPAT, "UTF-8");
	return $text;
}
function htmlentities3($text)
{
//	$text = htmlentities($text, ENT_COMPAT, "UTF-8");
//	$text = str_replace("&lt;", "<", $text);
//	$text = str_replace("&gt;", ">", $text);
//	$text = str_replace("&quot;", "\"", $text);
//	$text = str_replace("&apos;", "\'", $text);
	return $text;
}

if(function_exists('tidy_repair_string'))
{

	//See [http://tidy.sourceforge.net/docs/quickref.html] for specifics
	$tidyconfig = array
	(
		"show-body-only"=>1,
		"output-xhtml"=>1,
		"doctype"=>"strict",
		"logical-emphasis"=>1,
		"alt-text"=>"",
		"drop-proprietary-attributes"=>1,
		"wrap"=>0, //IMPORTANT -- wrapping introduces spurious newlines that WILL be converted to breaks by the board!
		"input-encoding"=>"utf8",
		"char-encoding"=>"utf8",
		"output-encoding"=>"utf8",
	);

	function TidyPost(&$text)
	{
		return;
		global $tidyconfig;
		$text = str_replace("\r", "", $text);
		$text = html_entity_decode($text);
		$text = trim(tidy_repair_string($text, $tidyconfig));
	}

	function TidyLayout(&$header, &$footer)
	{
		return;
		global $tidyconfig;
		print "<!-- TIDYLAYOUT \n".$header."\n\n".$footer;
		$sep = "%%SNIP%%";
		$pl = trim(tidy_repair_string($header.$sep.$footer, $tidyconfig));
		$header = substr($pl, 0, strpos($pl, $sep));
		$footer = substr($pl, strpos($pl, $sep) + strlen($sep));
		print "\n\n".$header."\n\n".$footer."\n-->";
	}
}
else
{
	function TidyPost(&$text)
	{
		return;
	}
	function TidyLayout(&$header, &$footer)
	{
		return;
	}
}


function OptimizeLayouts($text)
{
	$bucket = array();
	while(strpos($text, "<style") !== FALSE)
	{
		$startPos = strpos($text, "<style", $i);
		$endPos = strpos($text, "/style>", $startPos);
		$thisStyle = substr($text, $startPos, $endPos - $startPos + strlen("</style>") - 1);
		$text = str_replace($thisStyle, "", $text);
		$bucket[] = $thisStyle;
	}
	while(strpos($text, "<link") !== FALSE)
	{
		$startPos = strpos($text, "<link", $i);
		$endPos = strpos($text, "/>", $startPos);
		$thisStyle = substr($text, $startPos, $endPos - $startPos + strlen("/>"));
		$text = str_replace($thisStyle, "", $text);
		$bucket[] = $thisStyle;
	}
	while(strpos($text, "<script") !== FALSE)
	{
		$startPos = strpos($text, "<script", $i);
		$endPos = strpos($text, "/script>", $startPos);
		$thisStyle = substr($text, $startPos, $endPos - $startPos + strlen("/script>"));
		if(strpos($thisStyle, "api.recaptcha.net") !== FALSE)
		{
			$text = str_replace($thisStyle, str_replace("<script", "<recaptcha", $thisStyle), $text);
			continue;
		}
		$text = str_replace($thisStyle, "", $text);
		$bucket[] = $thisStyle;
	}
	$bucket = array_unique($bucket);
	$newStyles = "<!-- styles -->".implode("", $bucket)."<!-- /styles -->";
	$text = str_replace("</head>", $newStyles."</head>", $text);
	$text = str_replace("<recaptcha", "<script", $text);
	return $text;
}

?>
