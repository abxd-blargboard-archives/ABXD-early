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
					{0}
				</td>
			</tr>
		</table>
	</form>
",  $mailResetFrom == "" ? "" : "<button onclick=\"document.location = 'lostpass.php'; return false;\">Forgot password?</button>"
);
} elseif($_POST['action'] == "Log in")
{
	$original = $_POST['pass'];
	$escapedName = justEscape($_POST['name']);
	$qUser = "select * from users where name='".$escapedName."'";
	$rUser = Query($qUser);
	if(NumRows($rUser))
	{
		$user = Fetch($rUser);
		$sha = hash("sha256", $original.$salt.$user['pss'], FALSE);
		if($user['password'] != $sha)
		{
			include("lib/header.php");
			Report("A visitor from [b]".$_SERVER['REMOTE_ADDR']."[/] tried to log in as [b]".$user['name']."[/].");
			Kill("Invalid user name or password.<br /><a href=\"./\">Back to main</a> or <a href=\"login.php\">try again</a>.</div>");
		}
	}
	else
	{
		include("lib/header.php");
		Kill("Invalid user name or password.<br /><a href=\"./\">Back to main</a> or <a href=\"login.php\">try again</a>.</div>");
	}

	$logdata['loguserid'] = $user['id'];
	$logdata['bull'] = hash('sha256', $user['id'].$user['password'].$salt.$user['pss'], FALSE);
	$logdata_s = base64_encode(serialize($logdata));

	if(isset($_POST['session']))
		setcookie("logdata", $logdata_s);
	else
		setcookie("logdata", $logdata_s, 2147483647);

	include("lib/header.php");
	Report("[b]".$escapedName."[/] logged in.", 1);
	Redirect("You are now logged in.","./","the main page");	
}

?>
