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

if($_POST['action'] == "Edit user")
{
	$newscheme = array_search($_POST['theme'],$themeFiles);

	if($tpp > 99)
		$tpp = 0;
	if($tpp < 1)
		$tpp = 0;
	if($ppp > 99)
		$ppp = 0;
	if($ppp < 1)
		$ppp = 0;

	$blocklayouts = (int)($_POST['blocklayouts'] == "on");
	$usebanners = (int)($_POST['usebanners'] == "on");

	if($_POST['presetdate'])
		$_POST['dateformat'] = $_POST['presetdate'];
	if($_POST['presettime'])
		$_POST['timeformat'] = $_POST['presettime'];

	$qUser = "update users set ";
	if($_POST['newPW']) $qUser.= "password='".md5($_POST['newPW'])."', ";
	$qUser.= "rankset = ".(int)$_POST['rankset'].", ";
	if(TitleCheck())
		$qUser.= "title = '".justEscape($_POST['title'])."', ";
	$qUser.= "postheader = '".justEscape($_POST['postheader'])."', ";
	$qUser.= "signature = '".justEscape($_POST['signature'])."', ";
	$qUser.= "scheme = '".justEscape($newscheme)."', ";
	$qUser.= "threadsperpage = ".(int)$_POST['tpp'].", ";
	$qUser.= "postsperpage = ".(int)$_POST['ppp'].", ";
	$qUser.= "dateformat = '".justEscape($_POST['dateformat'])."', ";
	$qUser.= "timeformat = '".justEscape($_POST['timeformat'])."', ";
	$qUser.= "blocklayouts = '".$blocklayouts."', ";
	$qUser.= "usebanners = '".$usebanners."' ";
	$qUser.= "where id=".$id." limit 1";
	$rUser = Query($qUser);
	Redirect("Profile updated.","profile.php?id=".$id,($id == $loguserid ? "your" : "that user's") . " profile");
}

$qRanksets = "select name from ranksets";
$rRanksets = Query($qRanksets);
$ranksets[] = "None";
while($rankset = Fetch($rRanksets))
	$ranksets[] = $rankset['name'];

//Iterate through the themes, appending the number of users with that scheme...
for($i = 0; $i < count($themes); $i++)
{
	$qCount = "select count(*) from users where scheme=".$i;
	$c = FetchResult($qCount);
	$themes[$themeFiles[$i]] .= " (".$c.")";
}

$dateformats=array('','m-d-y','d-m-y','y-m-d','Y-m-d','m/d/Y','d.m.y','M j Y','D jS M Y');
$timeformats=array('','h:i A','h:i:s A','H:i','H:i:s');

foreach($dateformats as $format)
	$datelist[$format] = ($format ? $format.' ('.gmdate($format).')':'');
foreach($timeformats as $format)
	$timelist[$format] = ($format ? $format.' ('.gmdate($format).')':'');

$links  = "<div class=\"smallFonts outline margin cell1 width50\">";
$links .= "<strong>General</strong> | ";
$links .= "<a href=\"editprofile_login.php\">Login</a> | ";
$links .= "<a href=\"editprofile_avatar.php\">Avatar</a> | ";
$links .= "<a href=\"editprofile_bio.php\">Personal</a>";
$links .= "</div>";
print $links;

print "<form action=\"editprofile.php\" method=\"post\">";
print "<table class=\"outline margin width50\">";

print "<tr class=\"header0\">";
print "<th colspan=\"2\">Appearance</th>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td><label for=\"rankset\">Rank set</label></td><td>".MakeSelect("rankset",$user['rankset'],$ranksets)."</td>";
print "</tr>";
if(TitleCheck())
{
	print "<tr class=\"cell1\">";
	print "<td><label for=\"tit\">Title</td><td><input type=\"text\" id=\"tit\" name=\"title\" value=\"".htmlval($user['title'])."\" style=\"width: 98%;\" maxlength=\"255\" /></td>";
	print "</tr>";
}

print "<tr class=\"header0\">";
print "<th colspan=\"2\">Post layout</th>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td><label for=\"hd\">Header</label></td><td><textarea id=\"hd\" name=\"postheader\" rows=\"5\" style=\"width: 98%;\">".htmlval($user['postheader'])."</textarea></td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td><label for=\"sig\">Footer</label></td><td><textarea id=\"sig\" name=\"signature\" rows=\"5\" style=\"width: 98%;\">".htmlval($user['signature'])."</textarea></td>";
print "</tr>";

print "<tr class=\"header0\">";
print "<th colspan=\"2\">Presentation</th>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td><label for=\"theme\">Theme</label></td><td>".MakeSelect("theme",$themeFiles[$user['scheme']],$themes)."</td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td><label for=\"tpp\">Threads per page</label></td><td><input type=\"text\" id=\"tpp\" name=\"tpp\" value=\"".$user['threadsperpage']."\"></input></td>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td><label for=\"ppp\">Posts per page</label></td><td><input type=\"text\" id=\"ppp\" name=\"ppp\" value=\"".$user['postsperpage']."\"></input></td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td><label for=\"df\">Date format</label></td><td><input type=\"text\" id=\"df\" name=\"dateformat\" value=\"".$user['dateformat']."\"></input> or preset: ".MakeSelect("presetdate", -1, $datelist)."</td>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td><label for=\"tf\">Time format</label></td><td><input type=\"text\" id=\"tf\" name=\"timeformat\" value=\"".$user['timeformat']."\"></input> or preset: ".MakeSelect("presettime", -1, $timelist)."</td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td>Extras</td><td>";
print "<input type=\"checkbox\" id=\"bl\" name=\"blocklayouts\" ".($user['blocklayouts'] ? "checked=\"checked\"" : "")."></input> <label for=\"bl\">Block all layouts</label><br />";
print "<input type=\"checkbox\" id=\"ub\" name=\"usebanners\" ".($user['usebanners'] ? "checked=\"checked\"" : "")."></input> <label for=\"ub\">Use nice notification banners</label>";
print "</td>";
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
