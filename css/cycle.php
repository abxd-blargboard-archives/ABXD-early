<?php
header("Content-Type: text/css");

$curtime = getdate(time());
$min = $curtime['hours'] * 60 + $curtime['minutes'];

$hue = ($min / 2) % 360;
$sat = 50;
$hs = $hue.", ".$sat."%";
?>
/* AcmlmBoard XD - Daily Cycle */

body
{
	background: hsl(<?php print $hs; ?>, 5%);
}

.outline
{
	outline-color: hsl(<?php print $hs; ?>, 20%);
}

.cell0, table.post td.post
{
	background: hsl(<?php print $hs; ?>, 16%);
}

.cell1, table.post, .faq, .errorc
{
	background: hsl(<?php print $hs; ?>, 20%);
}

.cell2
{
	background: hsl(<?php print $hs; ?>, 28%);
}

.header0 th
{
	background: hsl(<?php print $hs; ?>, 32%);
}

.header1 th, .errort
{
	background: hsl(<?php print $hs; ?>, 40%);
}

.errort, .errorc
{
	padding: 0px 2px;
}

.errort
{
	text-align: center;
}

h3
{
	border-top: 0px none;
	border-bottom-color: hsl(<?php print $hs; ?>, 48%);
}

#pmNotice
{
	background: hsla(<?php print $hs; ?>, 48%, 0.75);
}

#pmNotice:hover
{
	background: hsl(<?php print $hs; ?>, 48%);
}