<?php
//  AcmlmBoard XD - User editing page
//  Access: administrators only

include("lib/common.php");

if($loguserid == 0)
	Kill("You must be logged in to edit your profile.");

$id = $loguserid;

$qUser = "select * from users where id=".$id;
$rUser = Query($qUser);
if(NumRows($rUser))
	$user = Fetch($rUser);
else
	Kill("Unknown user ID.");

if(isset($_POST['realname']))
	$_POST['realname'] = htmlentities2($_POST['realname']);
if(isset($_POST['location']))
	$_POST['location'] = htmlentities2($_POST['location']);

if($_POST['action'] == "Edit user")
{
	if($_POST['birthday'])
		$bday = strtotime($_POST['birthday']);
	else
		$bday = 0;

	$qUser = "update users set ";
	$qUser.= "sex = ".(int)$_POST['sex'].", ";
	$qUser.= "realname = '".justEscape($_POST['realname'])."', ";
	$qUser.= "location = '".justEscape($_POST['location'])."', ";
	$qUser.= "birthday = ".(int)$bday.", ";
	$qUser.= "bio = '".justEscape($_POST['bio'])."', ";
	$qUser.= "email = '".justEscape($_POST['email'])."', ";
	$qUser.= "homepageurl = '".justEscape($_POST['hpurl'])."', ";
	$qUser.= "homepagename = '".justEscape($_POST['hpname'])."' ";
	$qUser.= "where id=".$id." limit 1";
	$rUser = Query($qUser);
	Redirect("Profile updated.","profile.php?id=".$id,($id == $loguserid ? "your" : "that user's") . " profile");
}

$sexes = array("Male","Female","N/A");
//$sexes = array("Makes babies?", "Yes please", "I'm Christian.");
$levels = array(-1 => "-1 - Banned", 0 => "0 - Normal user", 1 => "1 - Local Mod", 2 => "2 - Full Mod", 3 => "3 - Admin");

if($user['birthday'] == 0)
	$bday = "";
else
	$bday = gmdate("F j, Y", $user['birthday']);

$links  = "<div class=\"smallFonts outline margin cell1 width50\">";
$links .= "<a href=\"editprofile.php\">General</a> | ";
$links .= "<a href=\"editprofile_login.php\">Login</a> | ";
$links .= "<a href=\"editprofile_avatar.php\">Avatar</a> | ";
$links .= "<strong>Personal</strong>";
$links .= "</div>";
print $links;

print "<form action=\"editprofile_bio.php\" method=\"post\">";
print "<table class=\"outline margin width50\">";

print "<tr class=\"header0\">";
print "<th colspan=\"2\">Personal information</th>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td>Sex</td><td>".MakeOptions("sex",$user['sex'],$sexes)."</td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td><label for=\"rn\">Real name</label></td><td><input type=\"text\" id=\"rn\" name=\"realname\" value=\"".htmlval($user['realname'])."\" style=\"width: 98%;\" maxlength=\"60\" /></td>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td><label for=\"loc\">Location</label></td><td><input type=\"text\" id=\"loc\" name=\"location\" value=\"".htmlval($user['location'])."\" style=\"width: 98%;\" maxlength=\"60\" /></td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td><label for=\"bd\">Birthday</label></td><td class=\"smallFonts\"><input type=\"text\" id=\"bd\" name=\"birthday\" value=\"".$bday."\" style=\"width: 98%;\" maxlength=\"60\" /> (example: June 26, 1983. <a href=\"http://nl2.php.net/manual/en/function.strtotime.php\">More</a>)</td>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td><label for=\"bio\">Bio</label></td><td><textarea id=\"bio\" name=\"bio\" rows=\"8\" style=\"width: 98%;\">".htmlval($user['bio'])."</textarea></td>";
print "</tr>";

print "<tr class=\"header0\">";
print "<th colspan=\"2\">Contact information</th>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td><label for=\"email\">Email address</label></td><td><input type=\"text\" id=\"email\" name=\"email\" value=\"".$user['email']."\" style=\"width: 98%;\" maxlength=\"60\" /></td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td><label for=\"hpurl\">Homepage URL</label></td><td><input type=\"text\" id=\"hpurl\" name=\"hpurl\" value=\"".$user['homepageurl']."\" style=\"width: 98%;\" maxlength=\"200\" /></td>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td><label for=\"hpname\">Homepage name</label></td><td><input type=\"text\" id=\"hpname\" name=\"hpname\" value=\"".htmlval($user['homepagename'])."\" style=\"width: 98%;\" maxlength=\"60\" /></td>";
print "</tr>";

print "<tr class=\"header0\">";
print "<th colspan=\"2\">&nbsp;</th>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td></td><td><input type=\"submit\" name=\"action\" value=\"Edit user\"></input>";
print "<input type=\"hidden\" name=\"id\" value=\"".$id."\"></input></td>";
print "</tr>";

print "</table>";
print "</form>";

doFooter();

function MakeOptions($fieldName, $checkedIndex, $choicesList)
{
	$checks[$checkedIndex] = " checked=\"checked\"";
	foreach($choicesList as $key=>$val)
		$result .= "<input type=\"radio\" id=\"o".$key."\" name=\"".$fieldName."\" value=\"".$key."\"".$checks[$key]."></input><label for=\"o".$key."\">".$val."</label> ";
	return $result;
}

function MakeSelect($fieldName, $checkedIndex, $choicesList)
{
	$checks[$checkedIndex] = " selected=\"selected\"";
	$result = "<select id=\"".$fieldname."\" name=\"".$fieldName."\" size=\"1\">";
	foreach($choicesList as $key=>$val)
		$result .= "<option value=\"".$key."\"".$checks[$key].">".$val."</option>";
	$result .= "</select>";
	return $result;
}

function TitleCheck()
{
	global $user, $adminmode;
	if($adminmode || $user['title'] || $user['posts'] >= 200 || $user['powerlevel'] > 0) return 1;
	return 0;
}

function htmlval($text)
{
	//$text = str_replace("\"", "&quot;", $text);
	//$text = str_replace("<", "&lt;", $text);
	//return $text;
	return htmlentities($text, ENT_COMPAT, "UTF-8");
}

?>
