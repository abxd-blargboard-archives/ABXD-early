<?php

include("lib/common.php");

AssertForbidden("viewUploader");

$rootdir = "uploader";
if(!is_file($rootdir."/.htaccess"))
	file_put_contents($rootdir."/.htaccess", "options all -indexes");


if($uploaderWhitelist)
	$goodfiles = explode(" ", $uploaderWhitelist);

$badfiles = array("html", "htm", "php", "mht", "js");

if(isset($_POST['action']))
	$_GET['action'] = $_POST['action'];
if(isset($_POST['fid']))
	$_GET['fid'] = $_POST['fid'];

$quota = $uploaderCap * 1024 * 1024;
$pQuota = $personalCap * 1024 * 1024;
$totalsize = foldersize($rootdir);

if(isset($_GET['sort']) && $_GET['sort'] == "filename" || $_GET['sort'] == "date")
	$skey = $_GET['sort'];
else
	$skey = "date";

$sortOptions = "<div class=\"margin smallFonts\">Sort order: <ul class=\"pipemenu\">";
$sortOptions .= ($skey == "filename") ? "<li>Name</li>" : "<a href=\"uploader.php?sort=filename\">Name</a></li>";
$sortOptions .= ($skey == "date") ? "<li>Date</li>" : "<a href=\"uploader.php\">Date</a></li>";
$sortOptions .= "</ul></div>";
$sdir = ($skey == "date") ? " desc" : " asc";

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
			}
			else if($_FILES['newfile']['size'] > $uploaderMaxFileSize * 1024 * 1024)
			{
				Alert("File is too large. Maximum size is ".BytesToSize($uploaderMaxFileSize * 1024 * 1024).".");
			}
			else
			{
				$fname = $_FILES['newfile']['name'];
				$temp = $_FILES['newfile']['tmp_name'];
				$size = $_FILES['size']['size'];
				$parts = explode(".", $fname);
				$extension = end($parts);
				if($totalsize + $size > $quot)
				{
					Alert("Uploading \"".$fname."\" would break the quota.");
				}
				else if(in_array(strtolower($extension), $badfiles) || is_array($goodfiles) && !in_array(strtolower($extension), $goodfiles))
				{
					Alert("Forbidden file type.");
				}
				else
				{
					$description = strip_tags($_POST['description']);

					$newID = FetchResult("SELECT id+1 FROM uploader WHERE (SELECT COUNT(*) FROM uploader u2 WHERE u2.id=uploader.id+1)=0 ORDER BY id ASC LIMIT 1");
					if($newID < 1) $newID = 1;

					Query("insert into uploader (id, filename, description, date, user, private) values (".$newID.", '".justEscape($fname)."', '".justEscape($description)."', ".time().", ".$loguserid.",".$privateFlag.")");
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
else if($loguserid && $_GET['action'] == "multidel" && $_POST['del']) //several files
{
	$deleted = 0;
	foreach($_POST['del'] as $fid => $on)
	{
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
			$deleted++;
		}
	}
	Alert(format("{0} deleted.", Plural($deleted, "file")));
}
else if($_GET['action'] == "delete") //single file
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

$head = format(
"
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
");
if($loguserid)
	$head = str_replace("<tr class=\"header1\">","<tr class=\"header1\"><th style=\"width: 22px;\"><input type=\"checkbox\" id=\"ca\" onchange=\"checkAll();\" /></th>", $head);

if($loguserid && is_dir($rootdir."/".$loguserid) || $loguser['powerlevel'] > 2)
{
	if($loguser['powerlevel'] > 2)
		$entries = Query("select uploader.*, users.name, users.displayname, users.powerlevel, users.sex from uploader left join users on uploader.user = users.id where uploader.private = 1 order by user, ".$skey.$sdir);
	else
		$entries = Query("select uploader.*, users.name, users.displayname, users.powerlevel, users.sex from uploader left join users on uploader.user = users.id where uploader.user = ".$loguserid." and uploader.private = 1 order by ".$skey.$sdir);

	if(NumRows($entries) == 0)
	{
		$private = format(
	"
		<table class=\"outline margin\">
			<tr class=\"header1\">
				<th colspan=\"7\">Private Files</th>
			</tr>
			<tr class=\"cell1\">
				<td colspan=\"4\">
					Your personal folder is empty.
				</td>
			</tr>
		</table>
	");
	}
	else
	{
		$havePrivates = true;
		$private = format(
"
		<table class=\"outline margin\">
			<tr class=\"header0\">
				<th colspan=\"7\">Private Files</th>
			</tr>
			{0}
", $head);
		$lastID = -1;
		while($entry = Fetch($entries))
		{
			$delete = "";
			$multidel = "";
			if($loguserid)
				$multidel = "<td><input type=\"checkbox\" name=\"delete[".$entry['id']."]\" disabled=\"disabled\" /></td>";
			if($loguserid == $entry['user'] || $loguser['powerlevel'] > 2)
			{
				$delete = "<sup>&nbsp;<a href=\"uploader.php?action=delete&amp;fid=".$entry['id']."\">&#x2718;</a></sup>";
				$multidel = "<td><input type=\"checkbox\" name=\"del[".$entry['id']."]\" /></td>";
			}

			$cellClass = ($cellClass+1) % 2;

			if($entry['user'] != $lastID)
			{
				if($lastID == -1)
					$lastID = $entry['user'];
				else
				{
					$lastID = $entry['user'];
					$private .= format(
"
			<tr class=\"header1\">
				<th colspan=\"7\" style=\"height: 2px;\"></th>
			</tr>
");
				}
			}

			$private .= format(
	"
			<tr class=\"cell{0}\">
				{8}
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
		BytesToSize(@filesize($rootdir."/".$entry['user']."/".$entry['filename'])), UserLink($entry, "user"),
		$entry['user'], $multidel);
		}
	}
	$private .= "</table>";
	

}

$entries = Query("select uploader.*, users.name, users.displayname, users.powerlevel, users.sex from uploader left join users on uploader.user = users.id where uploader.private = 0 order by ".$skey.$sdir);

if(NumRows($entries) == 0 && !$havePrivates)
{
	$public = format(
"
	<table class=\"outline margin\">
		<tr class=\"header0\">
			<th colspan=\"7\">Public Files</th>
		</tr>
		<tr class=\"cell1\">
			<td colspan=\"4\">
				The uploader is empty.
			</td>
		</tr>
	</table>
");
}
else
{
	if($havePrivates)
		$head = str_replace("<input type=\"checkbox\" id=\"ca\" onchange=\"checkAll();\" />", "", $head);

	$public = format(
"
	<table class=\"outline margin\">
		<tr class=\"header0\">
			<th colspan=\"7\">Public Files</th>
		</tr>
		{0}
", $head);
	while($entry = Fetch($entries))
	{
		$delete = "";
		$multidel = "";
		if($loguserid)
			$multidel = "<td><input type=\"checkbox\" name=\"delete[".$entry['id']."]\" disabled=\"disabled\" /></td>";
		if($loguserid == $entry['user'] || $loguser['powerlevel'] > 2)
		{
			$delete = "&nbsp;<sup><a href=\"uploader.php?action=delete&amp;fid=".$entry['id']."\">&#x2718;</a></sup>";
			$multidel = "<td><input type=\"checkbox\" name=\"del[".$entry['id']."]\" /></td>";
		}
		$cellClass = ($cellClass+1) % 2;

		$public .= format(
"
		<tr class=\"cell{0}\">
			{7}
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
	BytesToSize(@filesize($rootdir."/".$entry['filename'])), UserLink($entry, "user"), $multidel);
	}
	if($loguserid)
		$public .= format("
			<tr class=\"header1\">
				<th style=\"text-align: right;\" colspan=\"6\">
					<input type=\"hidden\" name=\"action\" value=\"multidel\" />
					<a href=\"javascript:void();\" onclick=\"document.forms[2].submit();\">delete checked</a>
				</th>
			</tr>");
	$public .= "</table>";
}

$maxSizeMult = $uploaderMaxFileSize * 1024 * 1024;
if($loguserid && IsAllowed("useUploader"))
{
	$uploadPart = format(
"
<script type=\"text/javascript\">
	window.addEventListener(\"load\", function() { hookUploadCheck(\"newfile\", 1, {1}) }, false);
</script>
<form action=\"uploader.php\" method=\"post\" enctype=\"multipart/form-data\">
	<table class=\"outline margin\">
		<tr class=\"header0\">
			<th colspan=\"4\">Upload</th>
		</tr>
		<tr class=\"cell2\">
			<td>
				<input type=\"file\" id=\"newfile\" name=\"newfile\" style=\"width: 80%;\" />
			</td>
			<td>
				<input type=\"text\" name=\"description\" style=\"width: 80%;\" />
				<label>
					<input type=\"checkbox\" name=\"private\" />&nbsp;Private
				</label>
			</td>
			<td>
				<input type=\"submit\" id=\"submit\" name=\"action\" value=\"Upload\" disabled=\"disabled\" />
			</td>
		</tr>
		<tr class=\"cell1 smallFonts\">
			<td colspan=\"3\">
				The maximum upload size is {0} per file. You can uploaded the following types: {2}.
				<div id=\"sizeWarning\" style=\"display: none; font-weight: bold\">File is too large.</div>
				<div id=\"typeWarning\" style=\"display: none; font-weight: bold\">File is not an allowed type.</div>
			</td>
		</tr>
	</table>
</form>
", BytesToSize($maxSizeMult), $maxSizeMult, $uploaderWhitelist);
}

$bar = "&nbsp;0%";

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
		$alt = format("{0}&nbsp;of&nbsp;{1},&nbsp;{2}%", BytesToSize($totalsize), BytesToSize($quota), $width);
		$bar = format("<div class=\"pollbar\" style=\"width: {0}%; background: {2}\" title=\"{1}\">&nbsp;$width%</div>", $width, $alt, $color);
	}
}

write(
"
<div style=\"clear: both;\">
	<div class=\"pollbarContainer\" style=\"float: right; width: 50%;\">
		{2}
	</div>
	Public space usage: {0} of {1}
</div>
",	BytesToSize($totalsize), BytesToSize($quota), $bar);

$bar = "&nbsp;0%";

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
			$alt = format("{0}&nbsp;of&nbsp;{1},&nbsp;{2}%", BytesToSize($personalsize), BytesToSize($pQuota), $width);
			$bar = format("<div class=\"pollbar\" style=\"width: {0}%; background: {2}\" title=\"{1}\">&nbsp;$width%</div>", $width, $alt, $color);
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
",	BytesToSize($personalsize), BytesToSize($pQuota), $bar);
}

write($uploadPart);
write("<form method=\"post\" action=\"uploader.php\">");
write($sortOptions);
write($private);
write($public);
write("</form>");

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

?>
