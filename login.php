<?php
//  AcmlmBoard XD - Login page
//  Access: guests

$noAutoHeader = TRUE;
include("lib/common.php");

if($_POST['action'] == "logout")
{
	setcookie("logdata", 0);

	include("lib/header.php");
	Redirect("You are now logged out.","./","the main page");
}
elseif(!$_POST['action'])
{
	include("lib/header.php");
	write(
"
	<form action=\"login.php\" method=\"post\">
		<table class=\"outline margin width50\">
			<tr class=\"header0\">
				<th colspan=\"2\">
					Log in
				</th>
			</tr>
			<tr>
				<td class=\"cell2\">
					<label for=\"un\">User name</label>
				</td>
				<td class=\"cell0\">
					<input type=\"text\" id=\"un\" name=\"name\" style=\"width: 98%;\" maxlength=\"25\" />
				</td>
			</tr>
			<tr>
				<td class=\"cell2\">
					<label for=\"pw\">Password</label>
				</td>
				<td class=\"cell1\">
					<input type=\"password\" id=\"pw\" name=\"pass\" size=\"13\" maxlength=\"32\" />
				</td>
			</tr>
			<tr>
				<td class=\"cell2\"></td>
				<td class=\"cell1\">
					<label>
						<input type=\"checkbox\" name=\"session\" />
						This session only
					</label>
				</td>
			</tr>
			<tr class=\"cell2\">
				<td></td>
				<td>
					<input type=\"submit\" name=\"action\" value=\"Log in\" />
				</td>
			</tr>
		</table>
	</form>
");
} elseif($_POST['action'] == "Log in")
{
	$original = $_POST['pass'];
	$mdfive = md5($original);
	$brokensha = base64_encode(hash("sha256", $original, "sAltlOlscuZdSfjdSDhfjguvDigEnfjFjfjkDH"));
	$sha = hash("sha256", $original."sAltlOlscuZdSfjdSDhfjguvDigEnfjFjfjkDH", FALSE);
	$escapedName = justEscape($_POST['name']);

	$qUser = "select * from users where name='".$escapedName."' and password='".$sha."'";
	$rUser = Query($qUser);
	if(NumRows($rUser) == 0)
	{
		//Check if the password hash in DB was an old MD5 one.
		$wasMD5 = FetchResult("select count(*) from users where name='".$escapedName."' and password='".$mdfive."'", 0, 0);
		if(!$wasMD5)
			$wasMD5 = FetchResult("select count(*) from users where name='".$escapedName."' and password='".$brokensha."'", 0, 0);
		if($wasMD5)
		{
			//Consider the login attempt a success anyway and replace the password hash with the SHA version.
			Query("update users set password='".$sha."' where name='".$escapedName."'");
			$qUser = "select * from users where name='".$escapedName."' and password='".$sha."'";
			$rUser = Query($qUser);
		}
		else
		{
			include("lib/header.php");
			Kill("Invalid user name or password.<br /><a href=\"./\">Back to main</a> or <a href=\"login.php\">try again</a>.</div>");
		}
	}
	$user = Fetch($rUser);

	$logdata['loguserid'] = $user['id'];
	$logdata['bull'] = hash('sha256', $user['id'].$user['password'].$_SERVER['REMOTE_ADDR']."sAltlOlscuZdSfjdSDhfjguvDigEnfjFjfjkDH", FALSE);
	$logdata_s = base64_encode(serialize($logdata));

	if(isset($_POST['session']))
		setcookie("logdata", $logdata_s);
	else
		setcookie("logdata", $logdata_s, 2147483647);

	include("lib/header.php");
	Redirect("You are now logged in.","./","the main page");	
}

?>
