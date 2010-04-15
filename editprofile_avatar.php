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
	if($_POST['removepic'])
	{
		$usepic = "";
		if(substr($user['picture'],0,12) == "img/avatars/")
			@unlink($user['picture']);
	}
	if($fname = $_FILES['picture']['name'])
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

	if(isset($usepic) || isset($usempic))
	{
		$qUser = "update users set ";
		if(isset($usepic)) $qUser.= "picture='".$usepic."', ";
		if(isset($usempic)) $qUser.= "minipic='".$usempic."', ";
		$qUser.= "id = ".$id." "; //filler line
		$qUser.= "where id=".$id." limit 1";
		$rUser = Query($qUser);
		Redirect("Profile updated.","profile.php?id=".$id,($id == $loguserid ? "your" : "that user's") . " profile");
	}
}

$links  = "<div class=\"smallFonts outline margin cell1 width50\">";
$links .= "<a href=\"editprofile.php\">General</a> | ";
$links .= "<a href=\"editprofile_login.php\">Login</a> | ";
$links .= "<strong>Avatar</strong> | ";
$links .= "<a href=\"editprofile_bio.php\">Personal</a>";
$links .= "</div>";
print $links;

print "<form action=\"editprofile_avatar.php\" method=\"post\" enctype=\"multipart/form-data\">";
print "<table class=\"outline margin width50\">";

print "<tr class=\"header0\">";
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
