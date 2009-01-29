<?php
//  AcmlmBoard XD - User editing page
//  Access: administrators only

include("lib/common.php");

if($loguser['powerlevel'] < 3 && (isset($_POST['id']) || isset($_GET['id'])))
	if($_POST['id'] != $loguserid)
		Kill("You're not allowed to edit other users' profiles.");

if(isset($_POST['id']) && !isset($_GET['id']))
	$_GET['id'] = $_POST['id'];

if(!isset($_GET['id']))
	$_GET['id'] = $loguserid;
//	Kill("User ID unspecified.");

$id = (int)$_GET['id'];

$adminmode = ($loguser['powerlevel'] == 3);
if(!$adminmode)
{
	if(isset($_POST['name']) || isset($_POST['powerlevel']) || isset($_POST['globalblock']))
		Kill("Admin-only changes detected.");
}

$qUser = "select * from users where id=".$id;
$rUser = Query($qUser);
if(NumRows($rUser))
	$user = Fetch($rUser);
else
	Kill("Unknown user ID.");

if(isset($_POST['name']))
	$_POST['name'] = htmlentities2($_POST['name']);
if(isset($_POST['realname']))
	$_POST['realname'] = htmlentities2($_POST['realname']);
if(isset($_POST['location']))
	$_POST['location'] = htmlentities2($_POST['location']);


if($_POST['action'] == "Edit user")
{
	//Lots of stolen avatar bullcrap
	if($_POST['removepic'])
	{
		$usepic = "";
		if(substr($user['picture'],0,12) == "img/avatars/")
			@unlink($user['picture']);
	} if($fname = $_FILES['picture']['name'])
	{
		$fext = strtolower(substr($fname,-4));
		$error = "";

		$exts = array(".png",".jpg",".gif");
		$dimx = 100;
		$dimy = 100;
		$dimxs = 60;
		$dimys = 60;
		$size = 30720;

		$validext = false;
		$extlist = "";
		foreach($exts as $ext)
		{
			if($fext == $ext)
			$validext = true;
			$extlist .= ($extlist ? ", " : "").$ext;
		}
		if(!$validext)
			$error.="<li>Invalid file type, must be either: ".$extlist."</li>";

		if(!$error)
		{
			$tmpfile = $_FILES['picture']['tmp_name'];
			$file = "img/avatars/".$id;

			if($loguser['powerlevel'])	//Are we at least a local mod?
				copy($tmpfile,$file);	//Then ignore the 100x100 rule.
			else
			{
				list($width, $height, $type) = getimagesize($tmpfile);

				if($type == 1) $img1 = imagecreatefromgif ($tmpfile);
				if($type == 2) $img1 = imagecreatefromjpeg($tmpfile);
				if($type == 3) $img1 = imagecreatefrompng ($tmpfile);

				if($width <= $dimx && $height <= $dimy && $type<=3)
					copy($tmpfile,$file);
				elseif($type <= 3)
				{
					if($r > 1)
					{
						$img2=imagecreatetruecolor($dimx,$dimy/$r);
						imagecopyresampled($img2,$img1,0,0,0,0,$dimx,$dimy/$r,imagesx($img1),imagesy($img1));
					} else
					{
						$img2=imagecreatetruecolor($dimx*$r,$dimy);
						imagecopyresampled($img2,$img1,0,0,0,0,$dimx*$r,$dimy,imagesx($img1),imagesy($img1));
					}
					imagepng($img2,$file);
				} else
					$error.="<li>Invalid format.</li>";
			}
			$usepic = $file;
		} else
			Kill("Could not update your avatar for the following reason(s):<ul>".$error."</ul>");
	}

	//DNE - consider the minipic support an optional feature for later. -- Kawa
	if($fname = $_FILES['minipic']['name'])
	{
		$fext = strtolower(substr($fname,-4));
		$error = "";

		$dimx = 12;
		$dimy = 12;
		$size = 30720;

		$validext = false;
		if($fext == ".png")
			$validext = true;
		if(!$validext)
			$error.="<li>Invalid file type, must be either: ".$extlist."</li>";

		if(($fsize=$_FILES['picture']['size']) > $size)
			$error.="<li>File size is too high, limit is ".$size." bytes.</li>";

		if(!$error)
		{
			$tmpfile = $_FILES['minipic']['tmp_name'];
			$file = "img/minipics/".$id.".png";

			list($width, $height, $type) = getimagesize($tmpfile);

			if($type != 3)
				$error.="<li>Image must be in PNG format.</li>";

			if($width == $dimx && $height == $dimy && !$error)
				copy($tmpfile,$file);
			else
			{
				$error.="<li>Image must be ".$dimx." by ".$dimy." pixels.</li>";
			}

			$usempic = $file;
		} else
			Kill("Could not update your minipic for the following reason(s):<ul>".$error."</ul>");
	}

	$newscheme = array_search($_POST['theme'],$themeFiles);

	if($tpp > 99)
		$tpp = 0;
	if($tpp < 1)
		$tpp = 0;
	if($ppp > 99)
		$ppp = 0;
	if($ppp < 1)
		$ppp = 0;

	$globalblock = (int)($_POST['globalblock'] == "on");
	$blocklayouts = (int)($_POST['blocklayouts'] == "on");

	if($_POST['presetdate'])
		$_POST['dateformat'] = $_POST['presetdate'];
	if($_POST['presettime'])
		$_POST['timeformat'] = $_POST['presettime'];

	if($_POST['birthday'])
		$bday = strtotime($_POST['birthday']);
	else
		$bday = 0;

	$qUser = "update users set ";
	if($adminmode)
		$qUser.= "name = '".$_POST['name']."', ";
	if($_POST['newPW']) $qUser.= "password='".md5($_POST['newPW'])."', ";
	$qUser.= "rankset = ".$_POST['rankset'].", ";
	if(TitleCheck())
		$qUser.= "title = '".htmlentities3($_POST['title'])."', ";
	if(isset($usepic)) $qUser.= "picture='".$usepic."', ";
	if(isset($usempic)) $qUser.= "minipic='".$usempic."', ";
	if($adminmode)
	{
		$qUser.= "powerlevel = ".$_POST['level'].", ";
		$qUser.= "globalblock = ".$globalblock.", ";
	}
	$qUser.= "sex = ".$_POST['sex'].", ";
	$qUser.= "realname = '".$_POST['realname']."', ";
	$qUser.= "location = '".$_POST['location']."', ";
	$qUser.= "birthday = ".(int)$bday.", ";
	$qUser.= "bio = '".htmlentities3($_POST['bio'])."', ";
	$qUser.= "postheader = '".htmlentities3($_POST['postheader'])."', ";
	$qUser.= "signature = '".htmlentities3($_POST['signature'])."', ";
	$qUser.= "email = '".$_POST['email']."', ";
	$qUser.= "homepageurl = '".$_POST['hpurl']."', ";
	$qUser.= "homepagename = '".$_POST['hpname']."', ";
	$qUser.= "scheme = '".$newscheme."', ";
	$qUser.= "threadsperpage = ".$_POST['tpp'].", ";
	$qUser.= "postsperpage = ".$_POST['ppp'].", ";
	$qUser.= "dateformat = '".$_POST['dateformat']."', ";
	$qUser.= "timeformat = '".$_POST['timeformat']."', ";
	$qUser.= "blocklayouts = '".$blocklayouts."' ";
	$qUser.= "where id=".$id." limit 1";
	$rUser = Query($qUser);
	Redirect("Profile updated.","profile.php?id=".$id,($id == $loguserid ? "your" : "that user's") . " profile");
}

$sexes = array("Male","Female","N/A");
//$sexes = array("Makes babies?", "Yes please", "I'm Christian.");
$levels = array(-1 => "-1 - Banned", 0 => "0 - Normal user", 1 => "1 - Local Mod", 2 => "2 - Full Mod", 3 => "3 - Admin");

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
	$themes[$themeFiles[$i]] .= " (".$c[0].")";
}

$dateformats=array('','m-d-y','d-m-y','y-m-d','Y-m-d','m/d/Y','d.m.y','M j Y','D jS M Y');
$timeformats=array('','h:i A','h:i:s A','H:i','H:i:s');

foreach($dateformats as $format)
	$datelist[$format] = ($format ? $format.' ('.date($format).')':'');
foreach($timeformats as $format)
	$timelist[$format] = ($format ? $format.' ('.date($format).')':'');

if($user['birthday'] == 0)
	$bday = "";
else
	$bday = date("F j, Y", $user['birthday']);

print "<form action=\"edituser.php\" method=\"post\" enctype=\"multipart/form-data\">";
print "<div class=\"border width50\">";
print "<table>";

print "<tr class=\"header0\">";
print "<th colspan=\"2\">Login information</th>";
print "</tr>";
print "<tr class=\"cell0\">";
if($adminmode)
{
	print "<td><label for=\"un\">User name</label></td><td><input type=\"text\" id=\"un\" name=\"name\" value=\"".$user['name']."\" style=\"width: 98%;\" maxlength=\"60\" /></td>";
} else
{
	print "<td>User name</td><td>".htmlval($user['name'])."</td>";
}
print "</tr>";
print "<tr class=\"cell1\">";
print "<td><label for=\"pw\">Password</label></td><td><input type=\"password\" id=\"pw\" name=\"newPW\" size=\"13\" maxlength=\"32\"></input></td>";
print "</tr>";

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
print "<tr class=\"cell0\">";
print "<td><label for=\"pic\">Picture</label></td><td><input type=\"file\" id=\"pic\" name=\"picture\"  style=\"width: 98%;\"></input> ";
print "<input type=\"checkbox\" id=\"rp\" name=\"removepic\"></input><label for=\"rp\">Remove</label></td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td><label for=\"mpic\">Minipic</label></td><td><input type=\"file\" id=\"mpic\" name=\"minipic\" style=\"width: 98%;\"></input> ";
print "<input type=\"checkbox\" id=\"rmp\" name=\"removempic\"></input><label for=\"rmp\">Remove</label></td>";
print "</tr>";

if($adminmode)
{
	print "<tr class=\"header0\">";
	print "<th colspan=\"2\">Administrative stuff</th>";
	print "</tr>";
	print "<tr class=\"cell0\">";
	print "<td><label for=\"level\">Power level</label></td><td>".MakeSelect("level",$user['powerlevel'],$levels)."</td>";
	print "</tr>";
	print "<tr class=\"cell1\">";
	print "<td>Goggles</td>";
	print "<td><input type=\"checkbox\" id=\"sb\" name=\"globalblock\"".($user['globalblock'] ? " checked=\"checked\"" : "")."></input> <label for=\"sb\">Globally block layout</label></td>";
	print "</tr>";
}

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
print "<td><label for=\"bio\">Bio</label></td><td><textarea id=\"bio\" name=\"bio\" rows=\"5\" style=\"width: 98%;\">".$user['bio']."</textarea></td>";
print "</tr>";

print "<tr class=\"header0\">";
print "<th colspan=\"2\">Post layout</th>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td><label for=\"hd\">Header</label></td><td><textarea id=\"hd\" name=\"postheader\" rows=\"5\" style=\"width: 98%;\">".$user['postheader']."</textarea></td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td><label for=\"sig\">Signature</label></td><td><textarea id=\"sig\" name=\"signature\" rows=\"5\" style=\"width: 98%;\">".$user['signature']."</textarea></td>";
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
print "<td></td><td><input type=\"checkbox\" id=\"bl\" name=\"blocklayouts\" ".($user['blocklayouts'] ? "checked=\"checked\"" : "")."></input> <label for=\"bl\">Block all layouts</label></td>";
print "</tr>";

print "<tr class=\"header0\">";
print "<th colspan=\"2\">&nbsp;</th>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td></td><td><input type=\"submit\" name=\"action\" value=\"Edit user\"></input>";
print "<input type=\"hidden\" name=\"id\" value=\"".$id."\"></input></td>";
print "</tr>";

print "</table>";
print "</div>";
print "</form>";

doFooter();

function MakeOptions($fieldName, $checkedIndex, $choicesList)
{
	$checks[$checkedIndex] = " checked=\"true\"";
	foreach($choicesList as $key=>$val)
		$result .= "<input type=\"radio\" id=\"o".$key."\" name=\"".$fieldName."\" value=\"".$key."\"".$checks[$key]."></input><label for=\"o".$key."\">".$val."</label> ";
	return $result;
}

function MakeSelect($fieldName, $checkedIndex, $choicesList)
{
	$checks[$checkedIndex] = " selected=\"true\"";
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
	$text = str_replace("\"", "&quot;", $text);
	$text = str_replace("<", "&lt;", $text);
	return $text;
}

?>
