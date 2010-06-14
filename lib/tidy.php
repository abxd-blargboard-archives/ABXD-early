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

	// Save the tags in the temp array and remove them from where they were originally
	$regexps = array("@<style(.*?)</style(.*?)>(\r?\n?)@si", "@<link(.*?)>(\r?\n?)@si", "@<script(.*?)</script(.*?)>(\r?\n?)@si");
	foreach ($regexps as $regexp)
	{
		preg_match_all($regexp, $text, $temp, PREG_PATTERN_ORDER);
		$text = preg_replace($regexp, "", $text);
		$bucket = array_merge($bucket, $temp[0]);
	}

	// Remove duplicates
	$bucket = array_unique($bucket);

	// Put the tags back
	$newStyles = "<!-- head tags -->".implode("", $bucket)."<!-- /head tags -->";
	$text = str_replace("</head>", $newStyles."</head>", $text);
	$text = str_replace("<recaptcha", "<script", $text);
	return $text;
}

?>
