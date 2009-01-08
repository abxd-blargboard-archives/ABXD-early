<?php
//  AcmlmBoard XD - Login page
//  Access: guests

$noAutoHeader = TRUE;
include("lib/common.php");

if($_GET['action'] == "logout")
{
	//This protection measure exploits the fact that $loguser contains the -previous- lastip value, which is updated -after- $loguser is fetched.
	if($_SERVER['REMOTE_ADDR'] != $loguser['lastip'])
		if($loguserid)
			Kill("Haaaah, no.");

	setcookie("logdata", 0);

	include("lib/header.php");
	Redirect("You are now logged out.","./","the main page");
} elseif(!$_POST['action'])
{
	include("lib/header.php");
	print "<form action=\"login.php\" method=\"post\">";
	print "<div class=\"border width50\">";
	print "<table>";
	print "<tr class=\"header0\">";
	print "<th colspan=\"2\">Log in</th>";
	print "</tr>";
	print "<tr>";
	print "<td class=\"cell2\"><label for=\"un\">User name</label></td>";
	print "<td class=\"cell0\"><input type=\"text\" id=\"un\" name=\"name\" style=\"width: 98%;\" maxlength=\"25\"></input></td>";
	print "</tr>";
	print "<tr>";
	print "<td class=\"cell2\"><label for=\"pw\">Password</label></td>";
	print "<td class=\"cell1\"><input type=\"password\" id=\"pw\" name=\"pass\" size=\"13\" maxlength=\"13\"></input></td>";
	print "</tr>";
	print "<tr>";
	print "<td class=\"cell2\"></td>";
	print "<td class=\"cell1\"><input type=\"checkbox\" id=\"s\" name=\"session\"></input><label for=\"s\">This session only</label></td>";
	print "</tr>";
	print "<tr class=\"cell2\">";
	print "<td></td>";
	print "<td><input type=\"submit\" name=\"action\" value=\"Log in\"></input></td>";
	print "</tr>";
	print "</table>";
	print "</div>";
	print "</form>";
} elseif($_POST['action'] == "Log in")
{
	$qUser = "select * from users where name='".$_POST['name']."' and password='".md5($_POST['pass'])."'";
	$rUser = Query($qUser);
	if(NumRows($rUser))
	{
		$user = Fetch($rUser);
		$logdata['loguserid'] = $user['id'];
		$logdata['loguserpw'] = $user['password'];
		$logdata_s = base64_encode(serialize($logdata));

		if(isset($_POST['session']))
			setcookie("logdata", $logdata_s);
		else
			setcookie("logdata", $logdata_s, 2147483647);

		include("lib/header.php");
		Redirect("You are now logged in.","./","the main page");
	} else
	{
		include("lib/header.php");
		Kill("Invalid user name or password.<br /><a href=\"./\">Back to main</a> or <a href=\"login.php\">try again</a>.</div>");
	}
}

doFooter();

?>
