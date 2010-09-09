<?php

include("lib/common.php");

$userdir = "uploader";
//$goodfiles = array("png", "gif", "jpg", "txt", "zip", "rar", "7z", "jpeg", "mid", "spc", "nsf", "sid", "mod", "s3m", "xm", "it", "css", "ogg", "mp3", "ips", "ups");

if($uploaderWhitelist)
	$goodfiles = explode(" ", $uploaderWhitelist);

if(isset($_POST['action']))
	$_GET['action'] = $_POST['action'];
if(isset($_POST['fid']))
	$_GET['fid'] = $_POST['fid'];

$quota = $uploaderCap * 1024 * 1024;
$totalsize = foldersize($userdir);

$entries = Query("select uploader.*, users.name, users.displayname, users.powerlevel, users.sex from uploader left join users on uploader.user = users.id order by id desc");

if($_GET['action'] == "Upload")
{
	if($loguserid)
	{
		$files = scandir($userdir);
		if(in_array($_FILES['newfile']['name'], $files))
			Alert("The file \"".$_FILES['newfile']['name']."\" already exists. Please delete the old copy before uploading a new one.");
		else
		{
			if($_FILES['newfile']['size'] == 0)
			{
				if($_FILES['newfile']['tmp_name'] == "")
					Alert("No file given.");
				else
					Alert("File is empty.");
			} else
			{
				$fname = $_FILES['newfile']['name'];
				$temp = $_FILES['newfile']['tmp_name'];
				$size = $_FILES['size']['size'];
				$extension = end(explode(".", $fname));
				if($totalsize + $size > $quota)
				{
					Alert("Uploading \"".$fname."\" would break the disk space quota.");
				}
				else if(is_array($goodfiles) && !in_array(strtolower($extension), $goodfiles))
				{
					Alert("Forbidden file type.");
				}
				else
				{
					$description = strip_tags($_POST['description']);
					Query("insert into uploader (filename, description, user) values ('".justEscape($fname)."', '".justEscape($description)."', ".$loguserid. ")");
					copy($temp, $userdir."/".$fname);
					Alert("File \"".$fname."\" has been uploaded.", "Okay");

					//Requery to see the changes
					$entries = Query("select uploader.*, users.name, users.displayname, users.powerlevel, users.sex from uploader left join users on uploader.user = users.id order by id desc");
				}
			}
		}
	}
	else
	{
		Alert("You must be logged in to upload.");
	}
}

if($_GET['action'] == "delete")
{
	$fid = (int)$_GET['fid'];

	if($loguser['powerlevel'] > 2)
		$check = FetchResult("select count(*) from uploader where id = ".$fid, 0, 0);
	else
		$check = FetchResult("select count(*) from uploader where user = ".$loguserid." and id = ".$fid, 0, 0);
	
	if($check)
	{
		$filename = FetchResult("select filename from uploader where id = ".$fid, 0, 0);
		@unlink($userdir."/".$filename);
		Query("delete from uploader where id = ".$fid);
		Alert("Deleted \"".$filename."\".", "Okay");

		//Requery to see the changes
		$entries = Query("select uploader.*, users.name, users.displayname, users.powerlevel, users.sex from uploader left join users on uploader.user = users.id order by id desc");
	}
	else
	{
		Alert("No such file or not yours to mess with.");
	}
}

$totalsize = foldersize($userdir);

if(NumRows($entries) == 0)
{
	$theList = format(
"
		<tr class=\"cell1\">
			<td colspan=\"4\">
				The uploader is empty.
			</td>
		</tr>
");
}
else
{
	while($entry = Fetch($entries))
	{
		$delete = "";
		if($loguserid == $entry['user'] || $loguser['powerlevel'] > 2)
			$delete = "<sup>[<a href=\"uploader.php?action=delete&amp;fid=".$entry['id']."\">d</a>]</sup>";
		$cellClass = ($cellClass+1) % 2;

		$theList .= format(
"
		<tr class=\"cell{0}\">
			<td>
				<a href=\"{1}/{2}\">{2}</a>{3}
			</td>
			<td>
				{4}
			</td>
			<td>
				{5}
			</td>
			<td>
				{6}
			</td>
		</tr>
",	$cellClass, $userdir, $entry['filename'], $delete, $entry['description'],
	size_readable(filesize($userdir."/".$entry['filename'])), UserLink($entry, "user"));
	}
}

if($loguserid)
{
	$uploadPart = format(
"
		<tr class=\"cell2\">
			<td>
				<input type=\"file\" name=\"newfile\" style=\"width: 80%;\" />
			</td>
			<td colspan=\"2\">
				<input type=\"text\" name=\"description\" style=\"width: 80%;\" />
			</td>
			<td>
				<input type=\"submit\" name=\"action\" value=\"Upload\" />
			</td>
		</tr>
");
}

if($totalsize > 0)
{
	$width = floor(100 * ($totalsize / $quota));
	if($width > 0)
	{
		$alt = format("{0}&nbsp;of&nbsp;{1},&nbsp;{2}%", size_readable($totalsize), size_readable($quota), $width);
		$bar = format("<div class=\"pollbar\" style=\"width: {0}%;\" title=\"{1}\">&nbsp;</div>", $width, $alt);
	}
	else
	{
		$bar = "&nbsp;";
	}
}

write(
"
<div class=\"\">
	<div class=\"pollbarContainer\" style=\"float: right; width: 50%;\">
			{4}
	</div>
	Disk space usage: {0} of {1}
</div>

<form action=\"uploader.php\" method=\"post\" enctype=\"multipart/form-data\">
	<table class=\"outline margin\">
		<tr class=\"header1\">
			<th>
				File
			</th>
			<th>
				Description
			</th>
			<th>
				Size
			</th>
			<th>
				Uploader
			</th>
		</tr>
		{2}
		{3}
	</table>
</form>
",	size_readable($totalsize), size_readable($quota), $theList, $uploadPart, $bar);

//From the PHP Manual User Comments
function foldersize($path)
{
	$total_size = 0;
	$files = scandir($path);
	$files = array_slice($files, 2);
	foreach($files as $t)
	{
		if(is_dir($t))
		{
			//Recurse here
			$size = foldersize($path . "/" . $t);
			$total_size += $size;
		}
		else
		{
			$size = filesize($path . "/" . $t);
			$total_size += $size;
		}
	}
	return $total_size;
}

function size_readable ($size, $retstring = '%01.2f %s')
{
	$sizes = array('B', 'KiB', 'MiB');
	$lastsizestring = end($sizes);
	foreach($sizes as $sizestring)
	{
		if($size < 1024)
			break;
		if($sizestring != $lastsizestring)
			$size /= 1024;
	}
	if($sizestring == $sizes[0])
		$retstring = '%01d %s'; // Bytes aren't normally fractional
	return sprintf($retstring, $size, $sizestring);
}

?>
