<?php
//  AcmlmBoard XD support - Tidy support

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
	"char-encoding"=>"utf8",
);

function htmlentities2($text)
{
	return htmlentities($text, ENT_COMPAT, "UTF-8");
}
function htmlentities3($text)
{
	$text = htmlentities($text, ENT_COMPAT, "UTF-8");
	$text = str_replace("&lt;", "<", $text);
	$text = str_replace("&gt;", ">", $text);
	$text = str_replace("&quot;", "\"", $text);
	$text = str_replace("&apos;", "\'", $text);
	return $text;
}

function TidyPost(&$text)
{
	global $tidyconfig;
	//print "\n\n\n".htmlentities($text, ENT_COMPAT, "UTF-8")."\n".$text."\n\n\n";
	$text = html_entity_decode($text);
	$text = trim(tidy_repair_string($text, $tidyconfig));
}

function TidyLayout(&$header, &$footer)
{
	global $tidyconfig;
	print "<!-- TIDYLAYOUT \n".$header."\n\n".$footer;
	$sep = "%%SNIP%%";
	$pl = trim(tidy_repair_string($header.$sep.$footer, $tidyconfig));
	$header = substr($pl, 0, strpos($pl, $sep));
	$footer = substr($pl, strpos($pl, $sep) + strlen($sep));
	print "\n\n".$header."\n\n".$footer."\n-->";
}

?>
