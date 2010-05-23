<?php
include("lib/common.php");

if(!$loguserid) //Not logged in?
	Kill("You must be logged in to edit your avatars.");

$title = "Mood avatars";

if(isset($_POST['action']))
{
	$mid = (int)$_POST['mid'];
	if($_POST['action'] == "Rename")
	{
		Query("update moodavatars set name='".justEscape($_POST['name'])."' where mid=".$mid." and uid=".$loguserid);
		Alert("Avatar renamed.", "Okay");
	}
	else if($_POST['action'] == "Delete")
	{
		Query("delete from moodavatars where uid=".$loguserid." and mid=".$mid);
		Query("update posts set mood=0 where user=".$loguserid." and mood=".$mid);
		if(file_exists("img/avatars/".$loguserid."_".$mid))
			unlink("img/avatars/".$loguserid."_".$mid);
		Alert("Avatar deleted.", "Okay");
	}
	else if($_POST['action'] == "Add")
	{
		$highest = FetchResult("select mid from moodavatars where uid=".$loguserid." order by mid desc limit 1");
		if($highest < 1)
			$highest = 1;
		$mid = $highest + 1;
		
		//Begin copypasta from edituser/editprofile_avatar...
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
				$file = "img/avatars/".$loguserid."_".$mid;
				
				if($_POST['name'] == "")
					$_POST['name'] = "#".$mid;

				Query("insert into moodavatars (uid, mid, name) values (".$loguserid.", ".$mid.", '".justEscape($_POST['name'])."')"); 

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
	}
}

print "<table class=\"margin outline width50\">";
$rMoods = Query("select mid, name from moodavatars where uid=".$loguserid." order by mid asc");
while($mood = Fetch($rMoods))
{
	$cellClass = ($cellClass+1) % 2;
	print "<tr class=\"cell".$cellClass."\">";
	print "<td style=\"width: 100px;\"><img src=\"img/avatars/".$loguserid."_".$mood['mid']."\" alt=\"\"></td>";
	print "<td>";
	print "<form method=\"post\" action=\"editavatars.php\">";
	print "<input type=\"hidden\" name=\"mid\" value=\"".$mood['mid']."\" />";
	print "<label for=\"name".$mood['mid']."\"></label> <input type=\"text\" id=\"name".$mood['mid']."\" name=\"name\" style=\"width: 60%;\" value=\"".htmlval($mood['name'])."\" />";
	print "<input type=\"submit\" name=\"action\" value=\"Rename\" />";
	print "<input type=\"submit\" name=\"action\" value=\"Delete\" />";
	print "</form>";
	print "</td>";
	print "</tr>";
}
print "<tr class=\"cell2\">";
print "<td>New</td>";
print "<td>";
print "<form method=\"post\" action=\"editavatars.php\" enctype=\"multipart/form-data\">";
print "<label for=\"newName\">Name:</label> <input type=\"text\" id=\"newName\" name=\"name\" style=\"width: 60%;\" /><br />";
print "<label for=\"pic\">Image:</label> <input type=\"file\" id=\"pic\" name=\"picture\"  style=\"width: 75%;\"></input>";
print "<input type=\"submit\" name=\"action\" value=\"Add\"></input>";
print "</form>";
print "</td>";
print "</table>";

DoFooter();

?>