<?php
//  AcmlmBoard XD - User editing page
//  Access: administrators only

include("lib/common.php");

//if($loguser['powerlevel'] < 3 && (isset($_POST['id']) || isset($_GET['id'])))
//	if($_POST['id'] != $loguserid)
//		Kill("You're not allowed to edit other users' profiles.");
//if($loguser['powerlevel'] < 3 && (isset($_POST['id']) || isset($_GET['id'])))
//	if((isset($_POST['id']) && $_POST['id'] != $loguserid) || (isset($_GET['id']) && $_GET['id'] != $loguserid))
//		Kill("You're not allowed to edit other users' profiles.");
if($loguser['powerlevel'] < 3)
		Kill("You're not allowed to edit other users' profiles.");

if(isset($_POST['id']) && !isset($_GET['id']))
	$_GET['id'] = $_POST['id'];

if(!isset($_GET['id']))
	$_GET['id'] = $loguserid;
//	Kill("User ID unspecified.");

$id = (int)$_GET['id'];

if($loguser['powerlevel'] < 3)
{
	if(isset($_POST['name']) || isset($_POST['level']) || isset($_POST['globalblock']))
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

$editVerb = "Edit user";
if($id == $loguserid)
	$editVerb = "Edit profile";

$newMD5 = md5($_POST['newPW']);
if($_POST['action'] == $editVerb)
{
	$wantToChange = FALSE;
	$allowedToChange = FALSE;
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
	if($wantToChange && !$allowedToChange)
	{
		Alert("To change your password, you must type it twice without error.");
		$_POST['action'] = "Not yet.";
	}
}

if($_POST['action'] == $editVerb)
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
					$r=imagesx($img1)/imagesy($img1);
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

		$dimx = 16;
		$dimy = 16;
		$size = 200000;

		$validext = false;
		if($fext == ".png")
			$validext = true;
		if(!$validext)
			$error.="<li>Invalid file type, must be .png</li>";

		if(($fsize=$_FILES['picture']['size']) > $size)
			$error.="<li>File size is too high, limit is ".$size." bytes.</li>";

		if(!$error)
		{
			$tmpfile = $_FILES['minipic']['tmp_name'];
			$file = "img/minipics/".$id.".png";

			list($width, $height, $type) = getimagesize($tmpfile);

			if($type != 3)
				$error.="<li>Image must be in PNG format.</li>";

			if($width <= $dimx && $height <= $dimy && !$error)
				copy($tmpfile,$file);
			else
				$error.="<li>Image must be at most ".$dimx." by ".$dimy." pixels.</li>";

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
	$signsep = (int)($_POST['signsep'] != "on");

    $timezone = ((int)$_POST['timezoneH'] * 3600) + ((int)$_POST['timezoneM'] * 60) * ((int)$_POST['timezoneH'] < 0 ? -1 : 1);

	if($_POST['presetdate'] != "dummy")
		$_POST['dateformat'] = $_POST['presetdate'];
	if($_POST['presettime'] != "dummy")
		$_POST['timeformat'] = $_POST['presettime'];

	if($_POST['birthday'])
		$bday = strtotime($_POST['birthday']);
	else
		$bday = 0;

	if($_POST['newPW'])
		$sha = base64_encode(hash("sha256", $_POST['newPW'], "sAltlOlscuZdSfjdSDhfjguvDigEnfjFjfjkDH"));

	$qUser = "update users set ";
	$qUser.= "name = '".justEscape($_POST['name'])."', ";
	if($sha)
		$qUser.= "password='".$sha."', ";
	$qUser.= "rankset = ".(int)$_POST['rankset'].", ";
	if(TitleCheck())
		$qUser.= "title = '".justEscape(CleanUpPost($_POST['title'], "", true))."', ";
	if(isset($usepic))
		$qUser.= "picture='".$usepic."', ";
	if(isset($usempic))
		$qUser.= "minipic='".$usempic."', ";
	$qUser.= "powerlevel = ".(int)$_POST['level'].", ";
	$qUser.= "globalblock = ".$globalblock.", ";
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
	$qUser.= "timezone = ".$timezone.", ";
	$qUser.= "dateformat = '".justEscape($_POST['dateformat'])."', ";
	$qUser.= "timeformat = '".justEscape($_POST['timeformat'])."', ";
	$qUser.= "fontsize = ".$fontsize.", ";
	$qUser.= "signsep = '".$signsep."', ";
	$qUser.= "blocklayouts = '".$blocklayouts."', ";
	$qUser.= "usebanners = '".$usebanners."' ";
	$qUser.= "where id=".$id." limit 1";
	$rUser = Query($qUser);

	if($user['powerlevel'] != (int)$_POST['level'])
	{
		$votes = Query("select uid from uservotes where voter=".$id);
		if(NumRows($votes))
			while($karmaChameleon = Fetch($votes))
				RecalculateKarma($karmaChameleon['uid']);
	}
	
	Redirect("Profile updated.","profile.php?id=".$id,($id == $loguserid ? "your" : "that user's") . " profile");
}

$sexes = array("Male","Female","N/A");
//$sexes = array("Makes babies?", "Yes please", "I'm Christian.");
$levels = array(-1 => "-1 - Banned", 0 => "0 - Normal user", 1 => "1 - Local Mod", 2 => "2 - Full Mod", 3 => "3 - Admin", 5 => "5 - System");

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
	$datelist[$format] = ($format ? $format.' ('.cdate($format).')':'');
foreach($timeformats as $format)
	$timelist[$format] = ($format ? $format.' ('.cdate($format).')':'');

if($user['birthday'] == 0)
	$bday = "";
else
	$bday = gmdate("F j, Y", $user['birthday']);

write(
"
	<form action=\"edituser.php\" method=\"post\" enctype=\"multipart/form-data\">
		<table class=\"outline margin width50\">

			<tr class=\"header0\">
				<th colspan=\"2\">
					Login information
				</th>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"un\">User name</label>
				</td>
				<td>
					<input type=\"text\" id=\"un\" name=\"name\" value=\"{0}\" style=\"width: 98%;\" maxlength=\"60\" />
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"pw\">Password</label>
				</td>
				<td>
					<input type=\"password\" id=\"pw\" name=\"newPW\" size=\"13\" maxlength=\"32\" />
					/ Repeat:
					<input type=\"password\" name=\"repeatPW\" size=\"13\" maxlength=\"32\" />
				</td>
			</tr>

			<tr class=\"header0\">
				<th colspan=\"2\">
					Appearance
				</th>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"rankset\">Rank set</label>
				</td>
				<td>
					{1}
				</td>
			</tr>
",	htmlval($user['name']), MakeSelect("rankset",$user['rankset'],$ranksets));

if(TitleCheck())
{
	write(
"
			<tr class=\"cell1\">
				<td>
					<label for=\"tit\">Title</label>
				</td>
				<td>
					<input type=\"text\" id=\"tit\" name=\"title\" value=\"{0}\" style=\"width: 98%;\" maxlength=\"255\" />
				</td>
			</tr>
", htmlval($user['title']));
}

write(
"
			<tr class=\"cell0\">
				<td>
					<label for=\"pic\">Picture</label>
				</td>
				<td>
					<input type=\"file\" id=\"pic\" name=\"picture\" style=\"width: 98%;\" />
					<label>
						<input type=\"checkbox\" name=\"removepic\" />
						Remove
					</label>
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"mpic\">Minipic</label>
				</td>
				<td>
					<input type=\"file\" id=\"mpic\" name=\"minipic\" style=\"width: 98%;\" />
					<label>
						<input type=\"checkbox\" name=\"removempic\" />
						Remove
					</label>
				</td>
			</tr>

			<tr class=\"header0\">
				<th colspan=\"2\">
					Administrative stuff
				</th>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"level\">Power level</label>
				</td>
				<td>
					{0}
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					Goggles
				</td>
				<td>
					<label>
						<input type=\"checkbox\" name=\"globalblock\" {1} />
						Globally block layout
					</label>
				</td>
			</tr>

			<tr class=\"header0\">
				<th colspan=\"2\">
					Personal information
				</th>
			</tr>
			<tr class=\"cell0\">
				<td>
					Sex
				</td>
				<td>
					{2}
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"rn\">Real name</label>
				</td>
				<td>
					<input type=\"text\" id=\"rn\" name=\"realname\" value=\"{3}\" style=\"width: 98%;\" maxlength=\"60\" />
				</td>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"loc\">Location</label>
				</td>
				<td>
					<input type=\"text\" id=\"loc\" name=\"location\" value=\"{4}\" style=\"width: 98%;\" maxlength=\"60\" />
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"bd\">Birthday</label>
				</td>
				<td class=\"smallFonts\">
					<input type=\"text\" id=\"bd\" name=\"birthday\" value=\"{5}\" style=\"width: 98%;\" maxlength=\"60\" />
					(example: June 26, 1983. <a href=\"http://nl2.php.net/manual/en/function.strtotime.php\">More</a>)
				</td>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"bio\">Bio</label>
				</td>
				<td>
					<textarea id=\"bio\" name=\"bio\" rows=\"8\" style=\"width: 98%;\">{6}</textarea>
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					Timezone offset
				</td>
				<td>
					<input type=\"text\" name=\"timezoneH\" size=\"2\" maxlength=\"3\" value=\"{23}\" />
					:
					<input type=\"text\" name=\"timezoneM\" size=\"2\" maxlength=\"3\" value=\"{24}\" />
				</td>
			</tr>

			<tr class=\"header0\">
				<th colspan=\"2\">
					Post layout
				</th>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"hd\">Header</label>
				</td>
				<td>
					<textarea id=\"hd\" name=\"postheader\" rows=\"8\" style=\"width: 98%;\">{7}</textarea>
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"sig\">Footer</label>
				</td>
				<td>
					<textarea id=\"sig\" name=\"signature\" rows=\"8\" style=\"width: 98%;\">{8}</textarea>
				</td>
			</tr>

			<tr class=\"header0\">
				<th colspan=\"2\">
					Contact information
				</th>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"email\">Email address</label>
				</td>
				<td>
					<input type=\"text\" id=\"email\" name=\"email\" value=\"{9}\" style=\"width: 98%;\" maxlength=\"60\" />
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"hpurl\">Homepage URL</label>
				</td>
				<td>
					<input type=\"text\" id=\"hpurl\" name=\"hpurl\" value=\"{10}\" style=\"width: 98%;\" maxlength=\"200\" />
				</td>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"hpname\">Homepage name</label>
				</td>
				<td>
					<input type=\"text\" id=\"hpname\" name=\"hpname\" value=\"{11}\" style=\"width: 98%;\" maxlength=\"60\" />
				</td>
			</tr>

			<tr class=\"header0\">
				<th colspan=\"2\">
					Presentation
				</th>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"theme\">Theme</label>
				</td>
				<td>
					{12}
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"tpp\">Threads per page</label>
				</td>
				<td>
					<input type=\"text\" id=\"tpp\" name=\"tpp\" value=\"{13}\" />
				</td>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"ppp\">Posts per page</label>
				</td>
				<td>
					<input type=\"text\" id=\"ppp\" name=\"ppp\" value=\"{14}\" />
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"df\">Date format</label>
				</td>
				<td>
					<input type=\"text\" id=\"df\" name=\"dateformat\" value=\"{15}\" />
					or preset: {16}
				</td>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"tf\">Time format</label>
				</td>
				<td>
					<input type=\"text\" id=\"tf\" name=\"timeformat\" value=\"{17}\" />
					or preset: {18}
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"fontsize\">Font scale</label>
				</td>
				<td>
					<input type=\"text\" id=\"fontsize\" name=\"fontsize\" value=\"{19}\" />
				</td>
			</tr>
			<tr class=\"cell0\">
				<td>
					Extras
				</td>
				<td>
					<label>
						<input type=\"checkbox\" id=\"bl\" name=\"blocklayouts\" {20} />
						Block all layouts
					</label><br />
					<label>
						<input type=\"checkbox\" id=\"ub\" name=\"usebanners\" {21} />
						Use nice notification banners
					</label><br />
					<label>
						<input type=\"checkbox\" name=\"signsep\" {25} />
						Show signature separator
					</label>
				</td>
			</tr>
		</table>
		<div class=\"margin right width50\" id=\"button\">
			<input type=\"submit\" name=\"action\" value=\"{22}\"></input>
			<input type=\"hidden\" name=\"id\" value=\"{26}\"></input>
		</div>
	</form>
",	MakeSelect("level",$user['powerlevel'],$levels), ($user['globalblock'] ? " checked=\"checked\"" : ""),
	MakeOptions("sex",$user['sex'],$sexes), htmlval($user['realname']), htmlval($user['location']),
	$bday, htmlval($user['bio']), htmlval($user['postheader']), htmlval($user['signature']),
	$user['email'], $user['homepageurl'], htmlval($user['homepagename']),
	MakeSelect("theme",$themeFiles[$user['scheme']],$themes),
	$user['threadsperpage'], $user['postsperpage'], $user['dateformat'],
	MakeSelect("presetdate", -1, $datelist), $user['timeformat'],
	MakeSelect("presettime", -1, $timelist), $user['fontsize'],
	($user['blocklayouts'] ? "checked=\"checked\"" : ""),
	($user['usebanners'] ? "checked=\"checked\"" : ""),
	$editVerb, (int)($loguser['timezone']/3600), floor(abs($loguser['timezone']/60)%60),
	($user['signsep'] ? "" : "checked=\"checked\""), $id
);

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
	global $user;
	if($user['title'] || $user['posts'] >= $customTitleThreshold || $user['powerlevel'] > 0) return 1;
	return 0;
}

?>
