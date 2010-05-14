<?php
//  AcmlmBoard XD - User account registration page
//  Access: any, but meant for guests.

$noAutoHeader = TRUE;
include("lib/common.php");

if($publickey)
{
	require_once('lib/recaptchalib.php');
	$capt_resp  = null;
	$capt_error = null;
}

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
	print "<form action=\"register.php\" method=\"post\">";
	print "<table class=\"outline margin width50\">";
	print "<tr class=\"header0\">";
	print "<th colspan=\"2\">Register</th>";
	print "</tr>";
	print "<tr>";
	print "<td class=\"cell2\"><label for=\"un\">User name</label></td>";
	print "<td class=\"cell0\"><input type=\"text\" id=\"un\" name=\"name\" maxlength=\"25\" style=\"width: 98%;\"></input></td>";
	print "</tr>";
	print "<tr>";
	print "<td class=\"cell2\"><label for=\"pw\">Password</label></td>";
	print "<td class=\"cell1\"><input type=\"password\" id=\"pw\" name=\"pass\" size=\"13\" maxlength=\"13\"></input></td>";
	print "</tr>";
	print "<tr>";
	print "<td class=\"cell2\"><label for=\"email\">Email address</label></td>";
	print "<td class=\"cell1\"><input type=\"text\" id=\"email\" name=\"email\" value=\"\" style=\"width: 98%;\" maxlength=\"60\" /></td>";
	print "</tr>";
	print "<tr>";
	print "<td class=\"cell2\">Sex</td>";
	print "<td class=\"cell1\">".MakeOptions("sex",2,$sexes)."</td>";
	print "</tr>";
	print "<tr>";
	print "<td class=\"cell2\"></td>";
	print "<td class=\"cell0\"><input type=\"checkbox\" id=\"rf\" name=\"readFaq\"></input><label for=\"rf\">I have read the <a href=\"faq.php\">FAQ</a></label></td>";
	print "</tr>";
	print "<tr>";
	print "<td class=\"cell2\"><label for=\"tw\">The word</label></td>";
	print "<td class=\"cell1\"><input type=\"text\" id=\"tw\" name=\"theWord\" maxlength=\"100\" style=\"width: 98%;\"></input></td>";
	print "</tr>";
	if($publickey)
	{
		print "<tr>";
		print "<td class=\"cell2\">Security</td>";
		print "<td class=\"cell1\">".recaptcha_get_html($publickey)."</td>";
		print "</tr>";
	}
	print "<tr class=\"cell2\">";
	print "<td></td>";
	print "<td><input type=\"submit\" name=\"action\" value=\"Register\"></input> <input type=\"checkbox\" id=\"n\" checked=\"checked\" name=\"autologin\"></input><label for=\"s\">Log in afterwards</label></td>";
	print "</tr>";
	print "</table>";
	print "</form>";
} elseif($_POST['action'] == "Register")
{
	$name = htmlentities2($_POST['name']);
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
	elseif($publickey)
	{
		$resp = recaptcha_check_answer ($privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
		if(!$resp->is_valid)
			$err = "You got the CAPTCHA wrong.".$backtomain;
	}

	if($err)
	{
		include("lib/header.php");
		Kill($err);
	}

	$qUsers = "insert into users (name, password, regdate, lastactivity, lastip, email, sex) values ('".justEscape($_POST['name'])."', '".md5($_POST['pass'])."', ".time().", ".time().", '".$_SERVER['REMOTE_ADDR']."', '".justEscape($_POST['email'])."', ".(int)$_POST['sex'].")";
	$rUsers = Query($qUsers);

	$uid = mysql_insert_id();
	if($uid == 1)
		Query("update users set powerlevel = 3 where id = 1;");

	Report("<message><action type=\"newuser\" id=\"".$uid."\" /><user id=\"".$uid."\">".$_POST['name']."</user></message>");

	if($_POST['autologin'])
	{
		$qUser = "select * from users where name='".justEscape($_POST['name'])."' and password='".md5($_POST['pass'])."'";
		$rUser = Query($qUser);
		$user = Fetch($rUser);
		$logdata['loguserid'] = $user['id'];
		$logdata['loguserpw'] = $user['password'];
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

doFooter();

function MakeOptions($fieldName, $checkedIndex, $choicesList)
{
	$checks[$checkedIndex] = " checked=\"checked\"";
	foreach($choicesList as $key=>$val)
		$result .= "<input type=\"radio\" id=\"o".$key."\" name=\"".$fieldName."\" value=\"".$key."\"".$checks[$key]."></input><label for=\"o".$key."\">".$val."</label> ";
	return $result;
}

?>
