<?php
//  AcmlmBoard XD - User account registration page
//  Access: any, but meant for guests.

$haveSecurimage = is_file("securimage/securimage.php");
if($haveSecurimage)
{
	session_start();
}

$noAutoHeader = TRUE;
include("lib/common.php");


$backtomain = "<br /><a href=\"./\">Back to main</a> or <a href=\"register.php\">try again</a>.";
$sexes = array("Male","Female","N/A");

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
	include("lib/header.php");
	write(
"
	<form action=\"register.php\" method=\"post\">
		<table class=\"outline margin width50\">
			<tr class=\"header0\">
				<th colspan=\"2\">
					Register
				</th>
			</tr>
			<tr>
				<td class=\"cell2\">
					<label for=\"un\">User name</label>
				</td>
				<td class=\"cell0\">
					<input type=\"text\" id=\"un\" name=\"name\" maxlength=\"20\" style=\"width: 98%;\" />
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
				<td class=\"cell2\">
					<label for=\"email\">Email address</label>
				</td>
				<td class=\"cell1\">
					<input type=\"text\" id=\"email\" name=\"email\" value=\"\" style=\"width: 98%;\" maxlength=\"60\" />
				</td>
			</tr>
			<tr>
				<td class=\"cell2\">
					Sex
				</td>
				<td class=\"cell1\">
					{0}
				</td>
			</tr>
			<tr>
				<td class=\"cell2\"></td>
				<td class=\"cell0\">
					<label>
						<input type=\"checkbox\" name=\"readFaq\" />
						I have read the <a href=\"faq.php\">FAQ</a>
					</label>
				</td>
			</tr>
			<tr>
				<td class=\"cell2\">
					<label for=\"tw\">The word</label>
				</td>
				<td class=\"cell1\">
					<input type=\"text\" id=\"tw\" name=\"theWord\" maxlength=\"100\" style=\"width: 98%;\" />
				</td>
			</tr>
", MakeOptions("sex",2,$sexes));

	if($haveSecurimage)
	{
		write(
"
			<tr>
				<td class=\"cell2\">
					Security
				</td>
				<td class=\"cell1\">
					<img id=\"captcha\" src=\"captcha.php\" alt=\"CAPTCHA Image\" />
					<button onclick=\"document.getElementById('captcha').src = 'captcha.php?' + Math.random(); return false;\">New</button><br />
					<input type=\"text\" name=\"captcha_code\" size=\"10\" maxlength=\"6\" />
				</td>
			</tr>
");
	}

	write(
"
			<tr class=\"cell2\">
				<td></td>
				<td>
					<input type=\"submit\" name=\"action\" value=\"Register\"/>
					<label>
						<input type=\"checkbox\" checked=\"checked\" name=\"autologin\" />
						Log in afterwards
					</label>
				</td>
			</tr>
		</table>
	</form>
");
}
elseif($_POST['action'] == "Register")
{
	$name = htmlspecialchars($_POST['name']);
	$cname = trim(str_replace(" ","", strtolower($name)));

	$qUsers = "select name, displayname from users";
	$rUsers = Query($qUsers);
	while($user = Fetch($rUsers))
	{
		$uname = trim(str_replace(" ", "", strtolower($user['name'])));
		if($uname == $cname)
			break;
		$uname = trim(str_replace(" ", "", strtolower($user['displayname'])));
		if($uname == $cname)
			break;
	}

	//CheckProxy();

	$qIP = "select lastip from users where lastip='".$_SERVER['REMOTE_ADDR']."'";
	$rIP = Query($qIP);
	$ipKnown = NumRows($rIP);

	if($uname == $cname)
		$err = "This user name is already taken. Please choose another.".$backtomain;
	else if($name == "" || $cname == "")
		$err = "The user name must not be empty. Please choose one.".$backtomain;
	elseif($ipKnown)
		$err = "Another user is already using this IP address.".$backtomain;
	else if(!$_POST['readFaq'])
		$err = "You really should <a href=\"faq.php\">read the FAQ</a>...";
	else if(strcasecmp($_POST['theWord'], $theWord))
		$err = "That's not the right word. Are you sure you really <a href=\"faq.php\">read the FAQ</a>?";
	else if(strlen($_POST['pass']) < 4)
		$err = "Your password must be at least four characters long.".$backtomain;

	if($haveSecurimage)
	{
		include("securimage/securimage.php");
		$securimage = new Securimage();
		if($securimage->check($_POST['captcha_code']) == false)
			$err = "You got the CAPTCHA wrong.".$backtomain;
	}

	if($err)
	{
		include("lib/header.php");
		Kill($err);
	}

	$sha = hash("sha256", $_POST['pass']."sAltlOlscuZdSfjdSDhfjguvDigEnfjFjfjkDH", FALSE);

	$qUsers = "insert into users (name, password, regdate, lastactivity, lastip, email, sex) values ('".justEscape($_POST['name'])."', '".$sha."', ".time().", ".time().", '".$_SERVER['REMOTE_ADDR']."', '".justEscape($_POST['email'])."', ".(int)$_POST['sex'].")";
	$rUsers = Query($qUsers);

	$uid = mysql_insert_id();
	if($uid == 1)
		Query("update users set powerlevel = 3 where id = 1;");

	if($_POST['autologin'])
	{
		$qUser = "select * from users where name='".justEscape($_POST['name'])."' and password='".md5($_POST['pass'])."'";
		$rUser = Query($qUser);
		$user = Fetch($rUser);

		$logdata['loguserid'] = $user['id'];
		$logdata['bull'] = hash('sha256', $user['id'].$user['password'].$_SERVER['REMOTE_ADDR']."sAltlOlscuZdSfjdSDhfjguvDigEnfjFjfjkDH", FALSE);
		$logdata_s = base64_encode(serialize($logdata));

		setcookie("logdata", $logdata_s, 2147483647);

		include("lib/header.php");
		Redirect("You are now registered and logged in.","./","the main page");
	} else
	{
		include("lib/header.php");
		Redirect("You are now registered!","login.php","the login page");
	}
}

function MakeOptions($fieldName, $checkedIndex, $choicesList)
{
	$checks[$checkedIndex] = " checked=\"checked\"";
	foreach($choicesList as $key=>$val)
		$result .= format("
					<label>
						<input type=\"radio\" name=\"{1}\" value=\"{0}\"{2} />
						{3}
					</label>", $key, $fieldName, $checks[$key], $val);
	return $result;
}
?>
