<?php
//  AcmlmBoard XD - Profile editing page (personal level)
//  Access: users

include("lib/common.php");

if(!$loguserid)
	Kill("You're not logged in.");

$id = $loguserid;

if($loguser['powerlevel'] == -1)
	Kill("Banned users cannot edit their own profile.");

$qUser = "select * from users where id=".$id;
$rUser = Query($qUser);
if(NumRows($rUser))
	$user = Fetch($rUser);
else
	Kill("Unknown user ID &mdash; and that's impossible.");

if(isset($_POST['realname']))
	$_POST['realname'] = htmlentities($_POST['realname']);
if(isset($_POST['location']))
	$_POST['location'] = htmlentities($_POST['location']);

if($_POST['action'] == "Edit profile")
{
	//Lots of stolen avatar bullcrap
	if($_POST['removepic'])
	{
		$usepic = "";
		if(substr($user['picture'],0,12) == "img/avatars/")
			@unlink($user['picture']);
	} elseif($fname = $_FILES['picture']['name'])
	{
		$fext = strtolower(substr($fname,-4));
		$error = "";

		$exts = array(".png",".jpg",".gif");
		$dimx = 100;
		$dimy = 100;
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

		if(($fsize=$_FILES['picture']['size']) > $size && $loguser['powerlevel'] < 1)
			$error.="<li>File size is too high, limit is ".$size." bytes.</li>";

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
					$error.="<li>Bad image format.</li>";
			}

			$usepic = $file;
		} else
			Kill("Could not update your avatar for the following reason(s):<ul>".$error."</ul>");
	}

	//DNE - consider the minipic support an optional feature for later. -- Kawa
	if($_POST['removempic'])
	{
		$usempic = "";
		if(substr($user['minipic'],0,13) == "img/minipics/")
			@unlink($user['minipic']);
	} elseif($fname = $_FILES['minipic']['name'])
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
				$error.="<li>Image must be ".$dimx." by ".$dimy." pixels.</li>";

			$usempic = $file;
		}
		if($error)
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

	$blocklayouts = (int)($_POST['blocklayouts'] == "on");

	if($_POST['presetdate'])
		$_POST['dateformat'] = $_POST['presetdate'];
	if($_POST['presettime'])
		$_POST['timeformat'] = $_POST['presettime'];

	if($_POST['birthday'])
		$bday = strtotime($_POST['birthday']);

	$qUser = "update users set ";
	if($_POST['newPW']) $qUser.= "password='".md5($_POST['newPW'])."', ";
	$qUser.= "rankset = ".$_POST['rankset'].", ";
	if(TitleCheck()) $qUser.= "title = '".$_POST['title']."', ";
	if(isset($usepic)) $qUser.= "picture='".$usepic."', ";
	if(isset($usempic)) $qUser.= "minipic='".$usempic."', ";
	$qUser.= "sex = ".$_POST['sex'].", ";
	$qUser.= "realname = '".$_POST['realname']."', ";
	$qUser.= "location = '".$_POST['location']."', ";
	$qUser.= "birthday = ".$bday.", ";
	$qUser.= "bio = '".$_POST['bio']."', ";
	$qUser.= "postheader = '".$_POST['postheader']."', ";
	$qUser.= "signature = '".$_POST['signature']."', ";
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
	Redirect("Profile updated.","profile.php?id=".$id,"your profile.");
}

$sexes = array("Male","Female","N/A");
//$sexes = array("Makes babies?", "Yes please", "I'm Christian.");

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

print "<form action=\"editprofile.php\" method=\"post\" enctype=\"multipart/form-data\">";
print "<div class=\"border width50\">";
print "<table>";

print "<tr class=\"header0\">";
print "<th colspan=\"2\">Login information</th>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td>User name</td><td>".$user['name']."</td>";
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
	print "<td><label for=\"tit\">Title</td><td><input type=\"text\" id=\"tit\" name=\"title\" value=\"".$user['title']."\" size=\"40\" maxlength=\"255\" /></td>";
	print "</tr>";
}
print "<tr class=\"cell0\">";
print "<td><label for=\"pic\">Picture</label></td><td><input type=\"file\" id=\"pic\" name=\"picture\" size=\"40\"></input> ";
print "<input type=\"checkbox\" id=\"rp\" name=\"removepic\"></input><label for=\"rp\">Remove</label></td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td><label for=\"mpic\">Minipic</label></td><td><input type=\"file\" id=\"mpic\" name=\"minipic\" size=\"40\"></input> ";
print "<input type=\"checkbox\" id=\"rmp\" name=\"removempic\"></input><label for=\"rmp\">Remove</label></td>";
print "</tr>";

print "<tr class=\"header0\">";
print "<th colspan=\"2\">Personal information</th>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td>Sex</td><td>".MakeOptions("sex",$user['sex'],$sexes)."</td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td><label for=\"rn\">Real name</label></td><td><input type=\"text\" id=\"rn\" name=\"realname\" value=\"".$user['realname']."\" size=\"40\" maxlength=\"60\" /></td>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td><label for=\"loc\">Location</label></td><td><input type=\"text\" id=\"loc\" name=\"location\" value=\"".$user['location']."\" size=\"40\" maxlength=\"60\" /></td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td><label for=\"bd\">Birthday</label></td><td><input type=\"text\" id=\"bd\" name=\"birthday\" value=\"".$bday."\" size=\"40\" maxlength=\"60\" /></td>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td><label for=\"bio\">Bio</label></td><td><textarea id=\"bio\" name=\"bio\" rows=\"5\" cols=\"60\">".$user['bio']."</textarea></td>";
print "</tr>";

print "<tr class=\"header0\">";
print "<th colspan=\"2\">Post layout</th>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td><label for=\"hd\">Header</label></td><td><textarea id=\"hd\" name=\"postheader\" rows=\"5\" cols=\"60\">".$user['postheader']."</textarea></td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td><label for=\"sig\">Signature</label></td><td><textarea id=\"sig\" name=\"signature\" rows=\"5\" cols=\"60\">".$user['signature']."</textarea></td>";
print "</tr>";

print "<tr class=\"header0\">";
print "<th colspan=\"2\">Contact information</th>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td><label for=\"email\">Email address</label></td><td><input type=\"text\" id=\"email\" name=\"email\" value=\"".$user['email']."\" size=\"40\" maxlength=\"60\" /></td>";
print "</tr>";
print "<tr class=\"cell1\">";
print "<td><label for=\"hpurl\">Homepage URL</label></td><td><input type=\"text\" id=\"hpurl\" name=\"hpurl\" value=\"".$user['homepageurl']."\" size=\"40\" maxlength=\"200\" /></td>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td><label for=\"hpname\">Homepage name</label></td><td><input type=\"text\" id=\"hpname\" name=\"hpname\" value=\"".$user['homepagename']."\" size=\"40\" maxlength=\"60\" /></td>";
print "</tr>";

print "<tr class=\"header0\">";
print "<th colspan=\"2\">Presentation</th>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td><label for=\"theme\">Theme</label></td><td>".MakeSelect("theme",$themeFiles[$user['scheme']],$themes)."</td>";
print "</tr>";
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
print "</tr>";
print "<tr class=\"cell1\">";
print "<td></td><td><input type=\"checkbox\" id=\"bl\" name=\"blocklayouts\" ".($user['blocklayouts'] ? "checked=\"checked\"" : "")."></input> <label for=\"bl\">Block layouts</label></td>";
print "</tr>";

print "<tr class=\"header0\">";
print "<th colspan=\"2\">&nbsp;</th>";
print "</tr>";
print "<tr class=\"cell0\">";
print "<td></td><td><input type=\"submit\" name=\"action\" value=\"Edit profile\"></input></td>";
print "</tr>";

print "</table>";
print "</div>";
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
	$checks[$checkedIndex] = " selected=\"true\"";
	$result = "<select id=\"".$fieldname."\" name=\"".$fieldName."\" size=\"1\">";
	foreach($choicesList as $key=>$val)
		$result .= "<option value=\"".$key."\"".$checks[$key].">".$val."</option>";
	$result .= "</select>";
	return $result;
}

function TitleCheck()
{
	global $user;
	if($user['title'] || $user['posts'] >= 20 || $user['powerlevel'] > 0) return 1;
	return 0;
}

?>
