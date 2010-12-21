<?php
//  AcmlmBoard XD - User editing page
//  Access: administrators only

include("lib/common.php");

AssertForbidden("editProfile");

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

if(isset($_POST['realname']))
	$_POST['realname'] = htmlentities2($_POST['realname']);
if(isset($_POST['location']))
	$_POST['location'] = htmlentities2($_POST['location']);

if(isset($_POST['displayname']))
{
	if(!IsReallyEmpty($_POST['displayname']) || $_POST['displayname'] == $user['name'])
	{
		$_POST['displayname'] = "";
	}
	else
	{
		$_POST['displayname'] = htmlspecialchars($_POST['displayname']);
		$dispCheck = FetchResult("select count(*) from users where id != ".$user['id']." and (name = '".justEscape($_POST['displayname'])."' or displayname = '".justEscape($_POST['displayname'])."')", 0, 0);
		if($dispCheck > 0)
		{
			Alert("The display name you entered is already taken.");
			$_POST['displayname'] = "";
			$user['displayname'] = "";
			$_POST['action'] = "";
		}
	}
}

if($_POST['newPW'] != "" && $_POST['repeatPW'] != "" && $_POST['repeatPW'] != $_POST['newPW'])
{
	Alert("To change your password, you must type it twice without error.");
	$_POST['newPW'] = "";
	$_POST['action'] = "Not yet.";
}
else if($_POST['repeatPW'] == "")
	$_POST['newPW'] = "";


if($_POST['action'] == "Edit profile")
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

		$dimx = 16;
		$dimy = 16;
		$size = 100 * 1024;

		$validext = false;
		if($fext == ".png")
			$validext = true;
		if(!$validext)
			$error.="<li>Invalid file type, must be .png</li>";
			
		$fsize = $_FILES['minipic']['size'];
		$tmpfile = $_FILES['minipic']['tmp_name'];
		list($width, $height, $type) = getimagesize($tmpfile);

		if($type != 3)
			$error.="<li>Image must be in PNG format.</li>";
		if($width > $dimx || $height > $dimy)
			$error.="<li>Image must be at most ".$dimx." by ".$dimy." pixels.</li>";
		if($fsize > $size)
			$error.="<li>File size is too high, limit is ".$size." bytes, uploaded image is ".$fsize." bytes.</li>";
		if($error)
			Kill("Could not update your minipic for the following reason(s):<ul>".$error."</ul>");
		else
		{
			$file = "img/minipics/".$id.".png";
			copy($tmpfile, $file);
			$usempic = $file;
		}
	}

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
	$showemail = (int)($_POST['showemail'] == "on");
	$signsep = (int)($_POST['signsep'] != "on");

    $timezone = ((int)$_POST['timezoneH'] * 3600) + ((int)$_POST['timezoneM'] * 60) * ((int)$_POST['timezoneH'] < 0 ? -1 : 1);

	if($_POST['presetdate'] != "dummy")
		$_POST['dateformat'] = $_POST['presetdate'];
	if($_POST['presettime'] != "dummy")
		$_POST['timeformat'] = $_POST['presettime'];

	if($_POST['birthday'])
		$bday = strtotime($_POST['birthday'].", 12:00 PM");
	else
		$bday = 0;

	if($_POST['newPW'] != "")
	{
		$sha = hash("sha256", $_POST['newPW'].$salt, FALSE);
		$logdata['loguserid'] = $user['id'];
		$logdata['bull'] = hash('sha256', $user['id'].$sha.$salt, FALSE);
		$logdata_s = base64_encode(serialize($logdata));
		setcookie("logdata", $logdata_s, 2147483647);
	}

	$plugSets = array();
	foreach($pluginSettings as $setName => $setItem)
		$plugSets[$setName] = $_POST[$setName];
	//print_r($plugSets);
	$plugSets = serialize($plugSets);

	$qUser = "update users set ";
	if($sha)
		$qUser.= "password='".$sha."', ";
	$qUser.= "rankset = ".(int)$_POST['rankset'].", ";
	if(TitleCheck())
		$qUser.= "title = '".justEscape($_POST['title'])."', ";
	if(isset($usepic)) $qUser.= "picture='".$usepic."', ";
	if(isset($usempic)) $qUser.= "minipic='".$usempic."', ";
	$qUser.= "displayname = '".justEscape($_POST['displayname'])."', ";
	$qUser.= "sex = ".(int)$_POST['sex'].", ";
	$qUser.= "realname = '".justEscape(htmlspecialchars($_POST['realname']))."', ";
	$qUser.= "location = '".justEscape(htmlspecialchars($_POST['location']))."', ";
	$qUser.= "birthday = ".(int)$bday.", ";
	$qUser.= "bio = '".justEscape($_POST['bio'])."', ";
	$qUser.= "postheader = '".justEscape($_POST['postheader'])."', ";
	$qUser.= "signature = '".justEscape($_POST['signature'])."', ";
	$qUser.= "email = '".justEscape($_POST['email'])."', ";
	$qUser.= "homepageurl = '".justEscape($_POST['hpurl'])."', ";
	$qUser.= "homepagename = '".justEscape($_POST['hpname'])."', ";
	$qUser.= "theme = '".justEscape($_POST['theme'])."', ";
	$qUser.= "threadsperpage = ".$tpp.", ";
	$qUser.= "postsperpage = ".$ppp.", ";
	$qUser.= "timezone = ".$timezone.", ";
	$qUser.= "dateformat = '".justEscape($_POST['dateformat'])."', ";
	$qUser.= "timeformat = '".justEscape($_POST['timeformat'])."', ";
	$qUser.= "fontsize = ".$fontsize.", ";
	$qUser.= "signsep = '".$signsep."', ";
	$qUser.= "blocklayouts = '".$blocklayouts."', ";
	$qUser.= "usebanners = '".$usebanners."', ";
	$qUser.= "showemail = '".$showemail."', ";
	$qUser.= "pluginsettings = '".justEscape($plugSets)."' ";
	$qUser.= "where id=".$id." limit 1";
	//kill($qUser);
	$rUser = Query($qUser);
	Report("[b]".$user['name']."[/] edited ".HisHer($user)." profile. -> [g]#HERE#?uid=".$id, 1);
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

//Iterate through the themes, appending the number of users with that theme...
$themeList = "";

foreach($themes as $themeKey => $themeName)
{
	$qCount = "select count(*) from users where theme='".$themeKey."'";
	$numUsers = FetchResult($qCount);
	
	$preview = "img/themes/".$themeKey."/preview.png";
	if(is_file($preview))
		$preview = "<img src=\"".$preview."\" alt=\"".$themeName."\" style=\"float: left; margin-bottom: 0.5em;\" />";
	else
		$preview = "<span style=\"float: left; width: 260px; height: 80px;\">&nbsp;</span>";
	
	if(array_key_exists($themeKey, $themeBylines))
		$byline = "<br />".$themeBylines[$themeKey];
	else
		$byline = "";
	
	if($themeKey == $loguser['theme'])
		$selected = " checked=\"checked\"";
	else
		$selected = "";
	
	$themeList .= format(
"
	<label style=\"display: block; clear: left; padding: 0.5em; {6}\">
		<input type=\"radio\" name=\"theme\" value=\"{3}\"{4} onchange=\"ChangeTheme(this.value);\" />
			{2}
			<strong>{0}</strong>
			{1}<br />
			<br />
			{5}.
	</label>
",	$themeName, $byline, $preview, $themeKey, $selected, Plural($numUsers, "user"),
	($ii > 0 ? "border-top: 1px solid black;" : "") );
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

$pluginsHider = count($pluginSettings) ? "" : " style=\"display: none;\"";

write(
"
	<div class=\"margin width0\" id=\"tabs\">
		<button id=\"generalButton\" onclick=\"showEditProfilePart('general');\" class=\"selected\">General</button>
		<button id=\"personalButton\" onclick=\"showEditProfilePart('personal');\">Personal</button>
		<button id=\"postlayoutButton\" onclick=\"showEditProfilePart('postlayout');\">Post Layout</button>
		<button id=\"themeButton\" onclick=\"showEditProfilePart('theme');\">Theme</button>
		<button id=\"pluginButton\" onclick=\"showEditProfilePart('plugin');\"{0}>Extras</button>
	</div>

	<form action=\"editprofile.php\" method=\"post\" enctype=\"multipart/form-data\">
", $pluginsHider);

write(
"
		<table class=\"outline margin width50\" id=\"general\" style=\"display: table;\">
			<tr class=\"header0\">
				<th colspan=\"2\">
					Login information
				</th>
			</tr>
			<tr class=\"cell0\">
				<td>
					User name
				</td>
				<td>
					{0}
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
				<th colspan=\"2\">Appearance</th>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"displayname\">Display name</label>
					<sup title=\"Leave empty to use login name\">[?]</sup>
				</td>
				<td>
					<input type=\"text\" id=\"displayname\" name=\"displayname\" value=\"{1}\" style=\"width: 98%;\" maxlength=\"20\" />
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"rankset\">Rank set</label>
				</td>
				<td>
					{2}
				</td>
			</tr>
", htmlval($user['name']), $user['displayname'], MakeSelect("rankset",$user['rankset'],$ranksets));

if(TitleCheck())
{
	write(
	"
			<tr class=\"cell0\">
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
			<tr class=\"header0\">
				<th colspan=\"2\">
					Avatar
				</th>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"pic\">Picture</label>
				</td>
				<td>
					<input type=\"file\" id=\"pic\" name=\"picture\"  style=\"width: 98%;\" /> 
					<label>
						<input type=\"checkbox\" name=\"removepic\" />
						Remove
					</label>
				</td>
			</tr>
			<tr class=\"cell0\">
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
				<th colspan=\"2\">Presentation</th>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"tpp\">Threads per page</label>
				</td>
				<td>
					<input type=\"text\" id=\"tpp\" name=\"tpp\" value=\"{1}\" />
				</td>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"ppp\">Posts per page</label>
				</td>
				<td>
					<input type=\"text\" id=\"ppp\" name=\"ppp\" value=\"{2}\" />
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"df\">Date format</label>
				</td>
				<td>
					<input type=\"text\" id=\"df\" name=\"dateformat\" value=\"{3}\" />
					or preset:
					{4}
				</td>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"tf\">Time format</label>
				</td>
				<td>
					<input type=\"text\" id=\"tf\" name=\"timeformat\" value=\"{5}\" />
					or preset:
					{6}
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"fontsize\">Font scale</label>
				</td>
				<td>
					<input type=\"text\" id=\"fontsize\" name=\"fontsize\" value=\"{7}\" />
				</td>
			</tr>
			<tr class=\"cell0\">
				<td>
					Extras
				</td>
				<td>
					<label>
						<input type=\"checkbox\" name=\"blocklayouts\" {8} />
						Block all layouts
					</label><br />
					<label>
						<input type=\"checkbox\" name=\"usebanners\" {9} />
						Use nice notification banners
					</label>
				</td>
			</tr>
		</table>
",
	0,
	$user['threadsperpage'], $user['postsperpage'], $user['dateformat'],
	MakeSelect("presetdate", -1, $datelist), $user['timeformat'],
	MakeSelect("presettime", -1, $timelist), $user['fontsize'],
	($user['blocklayouts'] ? "checked=\"checked\"" : ""),
	($user['usebanners'] ? "checked=\"checked\"" : "")
);

write(
"
		<table class=\"outline margin width50\" id=\"personal\" style=\"display: none;\">
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
					{0}
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"rn\">Real name</label>
				</td>
				<td>
					<input type=\"text\" id=\"rn\" name=\"realname\" value=\"{1}\" style=\"width: 98%;\" maxlength=\"60\" />
				</td>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"loc\">Location</label>
				</td>
				<td>
					<input type=\"text\" id=\"loc\" name=\"location\" value=\"{2}\" style=\"width: 98%;\" maxlength=\"60\" />
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"bd\">Birthday</label>
				</td>
				<td class=\"smallFonts\">
					<input type=\"text\" id=\"bd\" name=\"birthday\" value=\"{3}\" style=\"width: 98%;\" maxlength=\"60\" />
					(example: June 26, 1983. <a href=\"http://nl2.php.net/manual/en/function.strtotime.php\">More</a>)
				</td>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"bio\">Bio</label>
				</td>
				<td>
					<textarea id=\"bio\" name=\"bio\" rows=\"8\" style=\"width: 98%;\">{4}</textarea>
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					Timezone offset
				</td>
				<td>
					<input type=\"text\" name=\"timezoneH\" size=\"2\" maxlength=\"3\" value=\"{8}\" />
					:
					<input type=\"text\" name=\"timezoneM\" size=\"2\" maxlength=\"3\" value=\"{9}\" />
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
					<input type=\"email\" id=\"email\" name=\"email\" value=\"{5}\" style=\"width: 50%;\" maxlength=\"60\" />
					<label>
						<input type=\"checkbox\" name=\"showemail\" {10}/>
						Public
					</label>
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"hpurl\">Homepage URL</label>
				</td>
				<td>
					<input type=\"text\" id=\"hpurl\" name=\"hpurl\" value=\"{6}\" style=\"width: 98%;\" maxlength=\"200\" />
				</td>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"hpname\">Homepage name</label>
				</td>
				<td>
					<input type=\"text\" id=\"hpname\" name=\"hpname\" value=\"{7}\" style=\"width: 98%;\" maxlength=\"60\" />
				</td>
			</tr>
		</table>
",	MakeOptions("sex",$user['sex'],$sexes), htmlval($user['realname']), htmlval($user['location']), $bday,
	htmlval($user['bio']), $user['email'], $user['homepageurl'], htmlval($user['homepagename']),
	(int)($user['timezone']/3600), floor(abs($user['timezone']/60)%60),
	($user['showemail'] ? "checked=\"checked\"" : ""));

write(
"
		<table class=\"outline margin width50\" id=\"postlayout\" style=\"display: none;\">
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
					<textarea id=\"hd\" name=\"postheader\" rows=\"16\" style=\"width: 98%;\">{0}</textarea>
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					<label for=\"sig\">Footer</label>
				</td>
				<td>
					<textarea id=\"sig\" name=\"signature\" rows=\"16\" style=\"width: 98%;\">{1}</textarea>
				</td>
			</tr>
			<tr class=\"cell0\">
				<td>
				</td>
				<td>
					<label>
						<input type=\"checkbox\" name=\"signsep\" {2} />
						Show signature separator
					</label>
				</td>
			</tr>
		</table>
",	htmlval($user['postheader']), htmlval($user['signature']), ($user['signsep'] ? "" : "checked=\"checked\""));

write(
"
		<table class=\"outline margin width50\" id=\"theme\" style=\"display: none;\">
			<tr class=\"header0\">
				<th>
					Theme
				</th>
			</tr>
			<tr>
				<td class=\"cell0 threadIcons\">
					{0}
				</td>
			</tr>
		</table>
",	$themeList);

$plugsets = "";
foreach($pluginSettings as $setName => $setItem)
{
	$c = ($c + 1) % 2;
	if($setItem['check'])
	{
		$checked = $setItem['value'] ? " checked=\"checked\"" : "";
		$plugsets .= format(
"
			<tr class=\"cell{0}\">
				<td>
					&nbsp;
				</td>
				<td>
					<label>
						<input type=\"checkbox\" name=\"{2}\" {3}/>
						{1}
					</label>
				</td>
			</tr>
",	$c, $setItem['label'], $setName, $checked);
	}
	else
	{
		$plugsets .= format(
"
			<tr class=\"cell{0}\">
				<td>
					{1}
				</td>
				<td>
					<input type=\"text\" name=\"{2}\" value=\"{3}\" style=\"width: 98%;\" maxlength=\"200\" />
				</td>
			</tr>
",	$c, $setItem['label'], $setName, htmlval($setItem['value']));
	}
}
if($plugsets == "")
	$plugsets = "<tr><td colspan=\"2\" class=\"cell0\">No active plugins with settings.</td></tr>";
write(
"
		<table class=\"outline margin width50\" id=\"plugin\" style=\"display: none;\">
			<tr class=\"header0\">
				<th colspan=\"2\">
					Plugins
				</th>
			</tr>
			{0}
		</table>
", $plugsets);

write(
"
		<div class=\"margin right width50\" id=\"button\">
			<input type=\"submit\" name=\"action\" value=\"Edit profile\" />
			<input type=\"hidden\" name=\"id\" value=\"{0}\" />
		</div>

	</form>
", $id);

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

function MakeSelect($fieldName, $checkedIndex, $choicesList, $extras = "")
{
	$checks[$checkedIndex] = " selected=\"selected\"";
	foreach($choicesList as $key=>$val)
		$options .= format("
					<option value=\"{0}\"{1}>{2}</option>", $key, $checks[$key], $val);
	$result = format(
"
				<select id=\"{0}\" name=\"{0}\" size=\"1\" {1} >{2}
				</select>
", $fieldName, $extras, $options);
	return $result;
}

function TitleCheck()
{
	global $user, $adminmode, $customTitleThreshold;
	if($adminmode || $user['title'] || $user['posts'] >= $customTitleThreshold || $user['powerlevel'] > 0) return 1;
	return 0;
}

function IsReallyEmpty($subject)
{
	$trimmed = trim(preg_replace("/&.*;/", "", $subject));
	return strlen($trimmed) != 0;
}

?>
