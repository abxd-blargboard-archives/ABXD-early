<?php
//  AcmlmBoard XD - Rankset import tool
//  Access: administrators

include("lib/common.php");

if($loguser['powerlevel'] < 3)
	Kill("You must be an administrator to import ranksets.");

Query("truncate table ranks");
Query("truncate table ranksets");

print "<div>Ranksets cleared for refill.</div>";

include("ranksets.php");

$bads = array(" ","-",".",",");

print "<table class=\"outline margin width25\">";
print "<tr class=\"header1\"><th>Postcount</th><th>Rank</th></tr>";

foreach($ranks as $rankset)
{
	print "<tr class=\"header0\"><th colspan=\"2\">".$rankset['name']."</th></tr>";

	if(!$rankset['directory'])
		$rankset['directory'] = strtolower($rankset['name']);

	$index++;
	$description = "Set index is ".$index.". Base directory is <a href=\"img/ranks/".$rankset['directory']."/\"><code>".$rankset['directory']."</code></a>.";
	if($rankset['notolower'])
		$description .= " Set does not use lowercase filenames.";
	if($rankset['noimages'])
		$description .= " Set is text-only.";
	print "<tr class=\"cell1\"><td colspan=\"2\">".$description."</td></tr>";

	Query("insert into ranksets (name) values ('".$rankset['name']."')");

	foreach($rankset['ranks'] as $val => $text)
	{
		$img = "<img src=\"img/ranks/".$rankset['directory']."/".str_replace($bads,"",(!$rankset['notolower']?strtolower($text):$text)).".gif\" alt=\"".$text."\" /> ".($rankset['splitlines'] ? "<br />" : "").$text;
		if($val < 10 || $rankset['noimages'])
			$img = $text;
		print "<tr class=\"cell0\"><td>".$val."</td><td>".$img."</td></tr>";
		Query("insert into ranks (rset, num, text) values (".$index.", ".$val.", '".$img."')");
	}
}
print "</table>";

print "<div>The above ranks and sets have been imported.</div>";

doFooter();

?>
