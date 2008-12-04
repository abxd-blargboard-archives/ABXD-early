<?php
//  AcmlmBoard XD - User account registration page
//  Access: any, but meant for guests.

include("lib/common.php");

$backtomain = "<br /><a href=\"./\">Back to main</a> or <a href=\"register.php\">try again</a>.";

function CheckProxy()
{
	global $backtomain;
	$f=@fopen("http://".$_SERVER['REMOTE_ADDR']."/","r");
	$d=@fread($f,4096);
	if(strpos($d,"Apache is working on your cPanel"))
		Kill("You appear to be connecting from an open proxy server. If that is not the case, stop any HTTP daemons running on port 80 and retry.".$backtomain);
	@fclose($f);
}

if(!isset($_POST['action']))
{
	print "<form action=\"register.php\" method=\"post\">";
	print "<div class=\"border width25\">";
	print "<table>";
	print "<tr class=\"header0\">";
	print "<th colspan=\"2\">Register</th>";
	print "</tr>";
	print "<tr>";
	print "<td class=\"cell2\"><label for=\"un\">User name</label></td>";
	print "<td class=\"cell0\"><input type=\"text\" id=\"un\" name=\"name\" size=\"25\" maxlength=\"25\"></input></td>";
	print "</tr>";
	print "<tr>";
	print "<td class=\"cell2\"><label for=\"pw\">Password</label></td>";
	print "<td class=\"cell1\"><input type=\"password\" id=\"pw\" name=\"pass\" size=\"13\" maxlength=\"13\"></input></td>";
	print "</tr>";
	print "<tr class=\"cell2\">";
	print "<td></td>";
	print "<td><input type=\"submit\" name=\"action\" value=\"Register\"></input></td>";
	print "</tr>";
	print "</table>";
	print "</div>";
	print "</form>";
} elseif($_POST['action'] == "Register")
{
	$name = htmlentities($_POST['name']);
	$cname = strtolower($name);
	$cname = str_replace(" ","",$cname);
	$cname = str_replace(" ","",$cname);

	$qUsers = "select name from users";
	$rUsers = Query($qUsers);
	while($user = Fetch($rUsers))
	{
		$uname = strtolower($user['name']);
		$uname = str_replace(" ","",$uname);
		$uname = str_replace(" ","",$uname);
		if($uname == $cname)
			break;
	}

	CheckProxy();

	$qIP = "select lastip from users where lastip='".$_SERVER['REMOTE_ADDR']."'";
	$rIP = Query($qIP);
	//$ipKnown = NumRows($rIP);

	if($uname == $cname)
		Kill("This user name is already taken. Please choose another.".$backtomain);
	elseif($name == "" || $cname == "")
		Kill("The user name must not be empty. Please choose one.".$backtomain);
	elseif($ipKnown)
		Kill("Another user is already using this IP address.".$backtomain);
	elseif(strlen($_POST['pass']) < 4)
		Kill("Your password must be at least four characters long.".$backtomain);

	$qUsers = "insert into users (name, password, regdate, lastactivity, lastip) values ('".$_POST['name']."', '".md5($_POST['pass'])."', ".time().", ".time().", '".$_SERVER['REMOTE_ADDR']."')";
	$rUsers = Query($qUsers);

	if(mysql_insert_id() == 1)
		Query("update users set powerlevel = 3 where id = 1;");

	Redirect("You are now registered!","login.php","the login page.");
}

doFooter();

?>
