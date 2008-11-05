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

function TidyPost(&$text)
{
	global $tidyconfig;
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
