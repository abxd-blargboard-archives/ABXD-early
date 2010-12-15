<?php

include("lib/common.php");

AssertForbidden("viewUploader");

$rootdir = "uploader";
//$goodfiles = array("png", "gif", "jpg", "txt", "zip", "rar", "7z", "jpeg", "mid", "spc", "nsf", "sid", "mod", "s3m", "xm", "it", "css", "ogg", "mp3", "ips", "ups");

if($uploaderWhitelist)
	$goodfiles = explode(" ", $uploaderWhitelist);

if(isset($_POST['action']))
	$_GET['action'] = $_POST['action'];
if(isset($_POST['fid']))
	$_GET['fid'] = $_POST['fid'];

$quota = $uploaderCap * 1024 * 1024;
$pQuota = $personalCap * 1024 * 1024;
$totalsize = foldersize($rootdir);

if($_GET['action'] == "Upload")
{
	AssertForbidden("useUploader");
	if($loguserid)
	{
		$targetdir = $rootdir;
		$quot = $quota;
		$privateFlag = 0;
		if($_POST['private'])
		{
			$targetdir = $rootdir."/".$loguserid;
			if(!is_dir($targetdir))
				mkdir($targetdir);
			$quot = $pQuota;
			$totalsize = foldersize($targetdir);
			$privateFlag = 1;
		}
		
		$files = scandir($targetdir);
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
				if($totalsize + $size > $quot)
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
					Query("insert into uploader (filename, description, user, private) values ('".justEscape($fname)."', '".justEscape($description)."', ".$loguserid.",".$privateFlag.")");
					copy($temp, $targetdir."/".$fname);
					Alert("File \"".$fname."\" has been uploaded.", "Okay");
					Report("[b]".$loguser['name']."[/] uploaded file \"[b]".$fname."[/]\"".($privateFlag ? " (privately)" : ""), $privateFlag); 
					
					//Requery to see the changes
					//$entries = Query("select uploader.*, users.name, users.displayname, users.powerlevel, users.sex from uploader left join users on uploader.user = users.id order by id desc");
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
		$entry = Fetch(Query("select * from uploader where id = ".$fid));
		if($entry['private'])
			@unlink($rootdir."/".$entry['user']."/".$entry['filename']);
		else
			@unlink($rootdir."/".$entry['filename']);
		Query("delete from uploader where id = ".$fid);
		Report("[b]".$loguser['name']."[/] deleted \"[b]".$entry['filename']."[/]\".", 1);
		Alert("Deleted \"".$entry['filename']."\".", "Okay");

		//Requery to see the changes
		//$entries = Query("select uploader.*, users.name, users.displayname, users.powerlevel, users.sex from uploader left join users on uploader.user = users.id order by id desc");
	}
	else
	{
		Alert("No such file or not yours to mess with.");
	}
}

$totalsize = foldersize($rootdir);

if($loguserid && is_dir($rootdir."/".$loguserid) || $loguser['powerlevel'] > 2)
{
	if($loguser['powerlevel'] > 2)
		$entries = Query("select uploader.*, users.name, users.displayname, users.powerlevel, users.sex from uploader left join users on uploader.user = users.id where uploader.private = 1 order by user, id desc");
	else
		$entries = Query("select uploader.*, users.name, users.displayname, users.powerlevel, users.sex from uploader left join users on uploader.user = users.id where uploader.user = ".$loguserid." and uploader.private = 1 order by id desc");

	if(NumRows($entries) == 0)
	{
		$theList = format(
	"
			<tr class=\"cell1\">
				<td colspan=\"4\">
					Your personal folder is empty.
				</td>
			</tr>
	");
	}
	else
	{
		$lastID = -1;
		while($entry = Fetch($entries))
		{
			$delete = "";
			if($loguserid == $entry['user'] || $loguser['powerlevel'] > 2)
				$delete = "<sup>[<a href=\"uploader.php?action=delete&amp;fid=".$entry['id']."\">d</a>]</sup>";
			$cellClass = ($cellClass+1) % 2;

			if($entry['user'] != $lastID)
			{
				if($lastID == -1)
					$lastID = $entry['user'];
				else
				{
					$lastID = $entry['user'];
					$theList .= format(
"
			<tr class=\"header1\">
				<th colspan=\"7\" style=\"height: 2px;\"></th>
			</tr>
");
				}
			}

			$theList .= format(
	"
			<tr class=\"cell{0}\">
				<td>
					<a href=\"{1}/{7}/{2}\">{2}</a>{3}
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
	",	$cellClass, $rootdir, $entry['filename'], $delete, $entry['description'],
		size_readable(@filesize($rootdir."/".$entry['user']."/".$entry['filename'])), UserLink($entry, "user"),
		$entry['user']);
		}
	}
	$theList .= format(
"
			<tr class=\"header1\">
				<th colspan=\"7\" style=\"height: 8px;\"></th>
			</tr>
");

}

$entries = Query("select uploader.*, users.name, users.displayname, users.powerlevel, users.sex from uploader left join users on uploader.user = users.id where uploader.private = 0 order by id desc");

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
",	$cellClass, $rootdir, $entry['filename'], $delete, $entry['description'],
	size_readable(@filesize($rootdir."/".$entry['filename'])), UserLink($entry, "user"));
	}
}

if($loguserid && IsAllowed("useUploader"))
{
	$uploadPart = format(
"
		<tr class=\"header0\">
			<th colspan=\"4\">Upload</th>
		</tr>
		<tr class=\"cell2\">
			<td>
				<input type=\"file\" name=\"newfile\" style=\"width: 80%;\" />
			</td>
			<td colspan=\"2\">
				<input type=\"text\" name=\"description\" style=\"width: 80%;\" />
				<label>
					<input type=\"checkbox\" name=\"private\" />&nbsp;Private
				</label>
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
		$color = "green";
		if($width > 75)
			$color = "yellow";
		if($width > 90)
			$color = "orange";
		if($width > 100)
		{
			$width = 100;
			$color = "red;";
		}
		$alt = format("{0}&nbsp;of&nbsp;{1},&nbsp;{2}%", size_readable($totalsize), size_readable($quota), $width);
		$bar = format("<div class=\"pollbar\" style=\"width: {0}%; background: {2}\" title=\"{1}\">&nbsp;</div>", $width, $alt, $color);
	}
	else
	{
		$bar = "&nbsp;";
	}
}

write(
"
<div style=\"clear: both;\">
	<div class=\"pollbarContainer\" style=\"float: right; width: 50%;\">
			{2}
	</div>
	Disk space usage: {0} of {1}
</div>
",	size_readable($totalsize), size_readable($quota), $bar);

if($loguserid && is_dir($rootdir."/".$loguserid))
{
	$personalsize = foldersize($rootdir."/".$loguserid);
	if($personalsize > 0)
	{
		$width = floor(100 * ($personalsize / $pQuota));
		if($width > 0)
		{
			$color = "green";
			if($width > 75)
				$color = "yellow";
			if($width > 90)
				$color = "orange";
			if($width > 100)
			{
				$width = 100;
				$color = "red;";
			}
			$alt = format("{0}&nbsp;of&nbsp;{1},&nbsp;{2}%", size_readable($personalsize), size_readable($pQuota), $width);
			$bar = format("<div class=\"pollbar\" style=\"width: {0}%; background: {2}\" title=\"{1}\">&nbsp;</div>", $width, $alt, $color);
		}
		else
		{
			$bar = "&nbsp;";
		}
	}
	write(
"
<div style=\"clear: both;\">
	<div class=\"pollbarContainer\" style=\"float: right; width: 50%;\">
			{2}
	</div>
	Personal folder space usage: {0} of {1}
</div>
",	size_readable($personalsize), size_readable($pQuota), $bar);
}

write(
"
<form action=\"uploader.php\" method=\"post\" enctype=\"multipart/form-data\">
	<table class=\"outline margin\" style=\"clear: both;\">
		{1}
		<tr class=\"header0\">
			<th colspan=\"4\">File list</th>
		</tr>
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
		{0}
	</table>
</form>
",	$theList, $uploadPart);

//From the PHP Manual User Comments
function foldersize($path)
{
	$total_size = 0;
	$files = scandir($path);
	$files = array_slice($files, 2);
	foreach($files as $t)
	{
		//if(is_dir($t))
		//{
		//	//Recurse here
		//	$size = foldersize($path . "/" . $t);
		//	$total_size += $size;
		//}
		//else
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
