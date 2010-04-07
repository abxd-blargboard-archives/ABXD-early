<?php

$n = $_GET['n'];
$z = $_GET['z'];
$c = $_GET['c'];

$r = hexdec(substr($c, 0, 2));
$g = hexdec(substr($c, 2, 2));
$b = hexdec(substr($c, 4, 2));

$im = imagecreatetruecolor(690,20);
imagealphablending($im, false);
imagesavealpha($im, true);

$trans = imagecolorallocatealpha($im, 0, 0, 0, 127);
$background = imagecolorallocatealpha($im, 2, 2, 2, 64);
$black = imagecolorallocate($im, 0, 0, 0);
$white = imagecolorallocate($im, 255, 255, 255);
$bar = imagecolorallocate($im, $r, $g, $b);
$outline = imagecolorallocate($im, $r - ($r / 3), $g - ($g / 3), $b - ($b / 3));
$dots = imagecolorallocatealpha($im, 255, 255, 255, 64);

imagefilledrectangle($im,0,0,690,20,$trans);

imagefilledrectangle($im, 0, 5, 600, 15, $background);
imagefilledrectangle($im, 0, 5, 600 * ($z/$n), 15, $bar);
imagerectangle($im, 0, 5, 600 * ($z/$n), 15, $outline);

for($i=0; $i <= 600; $i+=100)
	imageline($im, $i, 13, $i, 17, $dots);

$text = $z." vote".($z == 1 ? "" : "s").", ".(int)(100 * $z / $n)."%";

imagestring($im, 2, 607, 4, $text, $black);
imagestring($im, 2, 606, 3, $text, $white);

imagepng($im);

?>
