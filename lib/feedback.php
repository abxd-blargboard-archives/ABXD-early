<?php
//  AcmlmBoard XD support - System feedback

function Debug($s)
{
	print "<strong>Debug</strong>: ".$s."<br />";
}

//	Not really much different to kill()
function Alert($s, $t="Alert")
{
	print "<div class=\"border\"><div class=\"errort\"><strong>".$t."</strong></div><div class=\"errorc cell2\">".$s."</div></div>";
}

function Kill($s, $t="Error")
{
	print "<div class=\"border\"><div class=\"errort\"><strong>".$t."</strong></div><div class=\"errorc cell2\">".$s."</div></div>";
	doFooter();
	exit();
}

function Redirect($s,$t,$n)
{
	print "<div class=\"border\"><div class=\"errort\"><strong>".$s."</strong></div><div class=\"errorc cell2\">You will now be redirected to ".$n."...</div></div>";
	print "<meta http-equiv=\"REFRESH\" content=\"2;URL=$t\" />";
	doFooter();
	exit();
}

?>
