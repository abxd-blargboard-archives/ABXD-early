<?php
//  AcmlmBoard XD support - System feedback

function Debug($s)
{
	print "<strong>Debug</strong>: ".$s."<br />";
}

//	Not really much different to kill()
function Alert($s, $t="Alert")
{
	print "<div class=\"outline margin\"><div class=\"errort\"><strong>".$t."</strong></div><div class=\"errorc cell2\">".$s."</div></div>";
}

function Kill($s, $t="Error")
{
	global $noAutoHeader;
	print "<div class=\"outline margin\"><div class=\"errort\"><strong>".$t."</strong></div><div class=\"errorc cell2\">".$s."</div></div>";
	if(!$noAutoHeader)
		doFooter();
	exit();
}

function Redirect($s,$t,$n)
{
	print "<div class=\"outline margin\"><div class=\"errort\"><strong>".$s."</strong></div><div class=\"errorc cell2\">You will now be redirected to <a href=\"".$t."\">".$n."</a>…</div></div>";
	print "<meta http-equiv=\"REFRESH\" content=\"2;URL=$t\" />";
	doFooter();
	exit();
}

?>
