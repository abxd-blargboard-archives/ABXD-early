<?php
//  AcmlmBoard XD - User editing page
//  Access: administrators only

include("lib/common.php");

if($loguserid == 0)
	Kill("You must be logged in to edit your profile.");

if($loguser['powerlevel'] == -1)
	Kill("No.");

$id = $loguserid;
$qUser = "select * from users where id=".$id;
$rUser = Query($qUser);
if(NumRows($rUser))
	$user = Fetch($rUser);
else
	Kill("Unknown user ID.");

$title = "Edit profile";

if(isset($_POST['name']))
	$_POST['name'] = htmlentities2($_POST['name']);
if(isset($_POST['realname']))
	$_POST['realname'] = htmlentities2($_POST['realname']);
if(isset($_POST['location']))
	$_POST['location'] = htmlentities2($_POST['location']);

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
					$r = imagesx($img1) / imagesy($img1);
					if($r > 1)
					{
						$img2=imagecreatetruecolor($dimx,floor($dimy / $r));
						imagecopyresampled($img2,$img1,0,0,0,0,$dimx,$dimy/$r,imagesx($img1),imagesy($img1));
					} else
					{
						$img2=imagecreatetruecolor(floor($dimx * $r), $dimy);
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
	if($_POST['removempic'])
	{
		$usempic = "";
		if(substr($user['minipic'],0,12) == "img/avatars/")
			@unlink($user['minipic']);
	}
	if($fname = $_FILES['minipic']['name'])
	{
		$fext = strtolower(substr($fname,-4));
		$error = "";

		$dimx = 12;
		$dimy = 12;
		$size = 100000;

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
	$tpp = (int)$_POST['tpp'];
	$ppp = (int)$_POST['ppp'];
	$fontsize = (int)$_POST['fontsize'];

	if($tpp > 99)
		$tpp = 99;
	if($tpp < 1)
		$tpp = 1;
	if($ppp > 99)
		$ppp = 99;
	if($ppp < 1)
		$ppp = 1;
	if($fontsize > 200)
		$fontsize = 200;
	if($fontsize < 20)
		$fontsize = 20;

	$globalblock = (int)($_POST['globalblock'] == "on");
	$blocklayouts = (int)($_POST['blocklayouts'] == "on");
	$usebanners = (int)($_POST['usebanners'] == "on");

	if($_POST['presetdate'] != "dummy")
		$_POST['dateformat'] = $_POST['presetdate'];
	if($_POST['presettime'] != "dummy")
		$_POST['timeformat'] = $_POST['presettime'];

	if($_POST['birthday'])
		$bday = strtotime($_POST['birthday']);
	else
		$bday = 0;

	$qUser = "update users set ";
	if($_POST['newPW']) $qUser.= "password='".md5($_POST['newPW'])."', ";
	$qUser.= "rankset = ".(int)$_POST['rankset'].", ";
	if(TitleCheck())
		$qUser.= "title = '".justEscape(CleanUpPost($_POST['title']))."', ";
	if(isset($usepic)) $qUser.= "picture='".$usepic."', ";
	if(isset($usempic)) $qUser.= "minipic='".$usempic."', ";
	$qUser.= "sex = ".(int)$_POST['sex'].", ";
	$qUser.= "realname = '".justEscape(CleanUpPost($_POST['realname']))."', ";
	$qUser.= "location = '".justEscape(CleanUpPost($_POST['location']))."', ";
	$qUser.= "birthday = ".(int)$bday.", ";
	$qUser.= "bio = '".justEscape($_POST['bio'])."', ";
	$qUser.= "postheader = '".justEscape($_POST['postheader'])."', ";
	$qUser.= "signature = '".justEscape($_POST['signature'])."', ";
	$qUser.= "email = '".justEscape($_POST['email'])."', ";
	$qUser.= "homepageurl = '".justEscape($_POST['hpurl'])."', ";
	$qUser.= "homepagename = '".justEscape($_POST['hpname'])."', ";
	$qUser.= "scheme = '".justEscape($newscheme)."', ";
	$qUser.= "threadsperpage = ".$tpp.", ";
	$qUser.= "postsperpage = ".$ppp.", ";
	$qUser.= "dateformat = '".justEscape($_POST['dateformat'])."', ";
	$qUser.= "timeformat = '".justEscape($_POST['timeformat'])."', ";
	$qUser.= "fontsize = ".$fontsize.", ";
	$qUser.= "blocklayouts = '".$blocklayouts."', ";
	$qUser.= "usebanners = '".$usebanners."' ";
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
	$themes[$themeFiles[$i]] .= " (".$c.")";
}

$datelist['dummy'] = "[select]";
$timelist['dummy'] = "[select]";

$dateformats=array('','m-d-y','d-m-y','y-m-d','Y-m-d','m/d/Y','d.m.y','M j Y','D jS M Y');
$timeformats=array('','h:i A','h:i:s A','H:i','H:i:s');

foreach($dateformats as $format)
	$datelist[$format] = ($format ? $format.' ('.gmdate($format).')':'');
foreach($timeformats as $format)
	$timelist[$format] = ($format ? $format.' ('.gmdate($format).')':'');

if($user['birthday'] == 0)
	$bday = "";
else
	$bday = gmdate("F j, Y", $user['birthday']);

print "<table class=\"outline margin width50\" id=\"tabs\"><tr><td class=\"cell0\">";
print "<button onclick=\"showEditProfilePart('general');\">General</button> ";
print "<button onclick=\"showEditProfilePart('login');\">Login</button> ";
print "<button onclick=\"showEditProfilePart('avatar');\">Avatar</button> ";
print "<button onclick=\"showEditProfilePart('personal');\">Personal</button> ";
print "<button onclick=\"showEditProfilePart('postlayout');\">Post Layout</button>";
print "</td></tr></table>";

print "<form action=\"editprofile.php\" method=\"post\" enctype=\"multipart/form-data\">";

print "<table class=\"outline margin width50\" id=\"general\" style=\"display: table;\">";
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
print "<th colspan=\"2\">Presentation</th>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td><label for=\"theme\">Theme</label></td><td>".MakeSelect("theme",$themeFiles[$user['scheme']],$themes, "onchange=\"ChangeTheme(this.value);\"")."</td>";
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
print "<td><label for=\"fontsize\">Font scale</label></td><td><input type=\"text\" id=\"fontsize\" name=\"fontsize\" value=\"".$user['fontsize']."\"></input></td>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td>Extras</td><td>";
print "<input type=\"checkbox\" id=\"bl\" name=\"blocklayouts\" ".($user['blocklayouts'] ? "checked=\"checked\"" : "")."></input> <label for=\"bl\">Block all layouts</label><br />";
print "<input type=\"checkbox\" id=\"ub\" name=\"usebanners\" ".($user['usebanners'] ? "checked=\"checked\"" : "")."></input> <label for=\"ub\">Use nice notification banners</label>";
print "</td>";
print "</tr>";
print "</table>";

print "<table class=\"outline margin width50\" id=\"login\" style=\"display: none;\">";
print "<tr class=\"header0\">";
print "<th colspan=\"2\">Login information</th>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td>User name</td><td>".htmlval($user['name'])."</td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td><label for=\"pw\">Password</label></td><td><input type=\"password\" id=\"pw\" name=\"newPW\" size=\"13\" maxlength=\"32\"></input> / Repeat: <input type=\"password\" name=\"repeatPW\" size=\"13\" maxlength=\"32\"></input></td>";
print "</tr>";
print "</table>";

print "<table class=\"outline margin width50\" id=\"avatar\" style=\"display: none;\">";
print "<tr class=\"header0\">";
print "<th colspan=\"2\">Avatar</th>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td><label for=\"pic\">Picture</label></td><td><input type=\"file\" id=\"pic\" name=\"picture\"  style=\"width: 98%;\"></input> ";
print "<input type=\"checkbox\" id=\"rp\" name=\"removepic\"></input><label for=\"rp\">Remove</label></td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td><label for=\"mpic\">Minipic</label></td><td><input type=\"file\" id=\"mpic\" name=\"minipic\" style=\"width: 98%;\"></input> ";
print "<input type=\"checkbox\" id=\"rmp\" name=\"removempic\"></input><label for=\"rmp\">Remove</label></td>";
print "</tr>";
print "</table>";

print "<table class=\"outline margin width50\" id=\"personal\" style=\"display: none;\">";
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
print "</table>";

print "<table class=\"outline margin width50\" id=\"postlayout\" style=\"display: none;\">";
print "<tr class=\"header0\">";
print "<th colspan=\"2\">Post layout</th>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td><label for=\"hd\">Header</label></td><td><textarea id=\"hd\" name=\"postheader\" rows=\"8\" style=\"width: 98%;\">".htmlval($user['postheader'])."</textarea></td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td><label for=\"sig\">Footer</label></td><td><textarea id=\"sig\" name=\"signature\" rows=\"8\" style=\"width: 98%;\">".htmlval($user['signature'])."</textarea></td>";
print "</tr>";
print "</table>";

print "<table class=\"outline margin width50\" id=\"button\"><tr><td class=\"cell0\">";
print "<input type=\"submit\" name=\"action\" value=\"Edit user\"></input>";
print "<input type=\"hidden\" name=\"id\" value=\"".$id."\"></input>";
print "</td></tr></table>";

print "</form>";

doFooter();

function MakeOptions($fieldName, $checkedIndex, $choicesList)
{
	$checks[$checkedIndex] = " checked=\"checked\"";
	foreach($choicesList as $key=>$val)
		$result .= "<input type=\"radio\" id=\"o".$key."\" name=\"".$fieldName."\" value=\"".$key."\"".$checks[$key]."></input><label for=\"o".$key."\">".$val."</label> ";
	return $result;
}

function MakeSelect($fieldName, $checkedIndex, $choicesList, $extras = "")
{
	$checks[$checkedIndex] = " selected=\"selected\"";
	$result = "<select id=\"".$fieldname."\" name=\"".$fieldName."\" size=\"1\" ".$extras." >";
	foreach($choicesList as $key=>$val)
		$result .= "<option value=\"".$key."\"".$checks[$key].">".$val."</option>";
	$result .= "</select>";
	return $result;
}

function TitleCheck()
{
	global $user, $adminmode, $customTitleThreshold;
	if($adminmode || $user['title'] || $user['posts'] >= $customTitleThreshold || $user['powerlevel'] > 0) return 1;
	return 0;
}

?>
