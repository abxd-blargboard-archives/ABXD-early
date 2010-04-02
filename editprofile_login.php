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

if(isset($_POST['name']))
	$_POST['name'] = htmlentities2($_POST['name']);

$newMD5 = md5($_POST['newPW']);
if($_POST['action'] == "Edit user")
{
	$wantToChange = FALSE;
	$allowedToChange = FALSE;
	//print "User PW: ".$user['password']."<br />";
	//print "New PW: ".$newMD5."<br />";
	if($_POST['newPW'] != "")
	{
		if($newMD5 != $user['password'])
			$wantToChange = TRUE;
	}
	if($_POST['repeatPW'] != "")
	{
		if($_POST['newPW'] == $_POST['repeatPW'])
			$allowedToChange = TRUE;
	}
	//print "Want to change: ".(int)$wantToChange."<br />";
	//print "Allowed to change: ".(int)$allowedToChange."<br />";
	if($wantToChange && !$allowedToChange)
	{
		Alert("To change your password, you must type it twice without error.");
		$_POST['action'] = "Not yet.";
	}
}

if($_POST['action'] == "Edit user")
{
	if($_POST['newPW'] && $_POST['repeatPW'] == $_POST['newPW'])
	{
		$qUser = "update users set ";
		$qUser.= "password='".md5($_POST['newPW'])."' ";
		$qUser.= "where id=".$id." limit 1";
		$rUser = Query($qUser);
		Redirect("Profile updated.","profile.php?id=".$id,($id == $loguserid ? "your" : "that user's") . " profile");
	}
	else if($_POST['newPW'] && $_POST['repeatPW'] != $_POST['newPW'])
	{
		if($_POST['repeatPW'])
		{
			Alert("To change your password, you must type it twice without error.");
			$_POST['newPW'] = "";
			$_POST['action'] = "Not yet.";
		}
	}
}

$links  = "<div class=\"smallFonts outline margin cell1 width50\">";
$links .= "<a href=\"editprofile.php\">General</a> | ";
$links .= "<strong>Login</strong> | ";
$links .= "<a href=\"editprofile_avatar.php\">Avatar</a> | ";
$links .= "<a href=\"editprofile_bio.php\">Personal</a>";
$links .= "</div>";
print $links;

print "<form action=\"editprofile_login.php\" method=\"post\">";
print "<table class=\"outline margin width50\">";

print "<tr class=\"header0\">";
print "<th colspan=\"2\">Login</th>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td>User name</td><td>".htmlval($user['name'])."</td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td><label for=\"pw\">Password</label></td><td><input type=\"password\" id=\"pw\" name=\"newPW\" size=\"13\" maxlength=\"32\"></input> / Repeat: <input type=\"password\" name=\"repeatPW\" size=\"13\" maxlength=\"32\"></input></td>";
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
