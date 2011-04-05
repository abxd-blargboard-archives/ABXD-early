<?php
//  AcmlmBoard XD - Private message display page
//  Access: user, specifically the sender or reciever.

include("lib/common.php");

AssertForbidden("viewPM");

if(!loguserid)
	Kill("You must be logged in to view your private messages.");

if(!isset($_GET['id']) && !isset($_POST['id']))
	Kill("No PM specified.");

$id = (int)(isset($_GET['id']) ? $_GET['id'] : $_POST['id']);

if(isset($_GET['snooping']))
{
	if($loguser['powerlevel'] > 2)
		$qPM = "select * from pmsgs left join pmsgs_text on pid = pmsgs.id where pmsgs.id = ".$id;
	else
		Kill("No snooping for you.");
}
else
	$qPM = "select * from pmsgs left join pmsgs_text on pid = pmsgs.id where (userto = ".$loguserid." or userfrom = ".$loguserid.") and pmsgs.id = ".$id;

$rPM = Query($qPM);
if(NumRows($rPM))
	$pm = Fetch($rPM);
else
	Kill("Unknown PM");

if($pm['drafting'] && $pm['userfrom'] != $loguserid)
	Kill("Unknown PM"); //could say "PM is addresssed to you, but is being drafted", but what they hey?

$qUser = "select * from users where id = ".$pm['userfrom'];
$rUser = Query($qUser);
if(NumRows($rUser))
	$user = Fetch($rUser);
else
	Kill("Unknown user.");
$bucket = "userMangler"; include("./lib/pluginloader.php");

if(!isset($_GET['snooping']) && $pm['userto'] == $loguserid)
{
	$qPM = "update pmsgs set msgread=1 where id=".$pm['id'];
	$rPM = Query($qPM);
	$links = "<a href=\"sendprivate.php?pid=".$pm['id']."\">Send reply</a>";
}
else if(!isset($_GET['snooping']) && $pm['drafting'])
{
	if($pm['userfrom'] != $loguserid)
		Kill("This PM is still being drafted.");
	else
		$draftEditor = true;
}
else if(isset($_GET['snooping']))
	Alert("You are snooping.");


$pmtitle = htmlspecialchars($pm['title']); //sender's custom title overwrites this below, so save it here

MakeCrumbs(array("Main"=>"./", "Private messages"=>"private.php", $pmtitle=>""), $links);


$pm['num'] = "preview";
$pm['posts'] = $user['posts'];
$pm['id'] = "???";
$pm['uid'] = $user['id'];
$copies = explode(",","title,name,displayname,picture,sex,powerlevel,avatar,postheader,signature,signsep,regdate,lastactivity,lastposttime");
foreach($copies as $toCopy)
	$pm[$toCopy] = $user[$toCopy];

if($draftEditor)
{
	write(
"
	<script type=\"text/javascript\">
			window.addEventListener(\"load\",  hookUpControls, false);
	</script>
");


	$qUser = "select name from users where id=".$pm['userto'];
	$rUser = Query($qUser);
	if(!NumRows($rUser))
	{
		if($_POST['action']=="Send")
			Kill("Unknown user.");
	}
	$user = Fetch($rUser);
	
	if($_POST['action'] == "Preview")
	{
		$pm['text'] = $_POST['text'];
		$pmtitle = $_POST['title'];
	}
	
	if($_POST['action'] == "Discard Draft")
	{
		Query("delete from pmsgs where id = ".$id);
		Query("delete from pmsgs_text where pid = ".$id);
		Redirect("PM draft discarded.","private.php","your PM box");
		exit();
	}
	
	if($_POST['action'] == "Send" || $_POST['action'] == "Update Draft")
	{
		if($_POST['title'])
		{
			$_POST['title'] = htmlentities2($_POST['title']);

			if($_POST['text'])
			{
				if($_POST['action'] == "Update Draft")
				{
					//$post = justEscape($post);
					$post = htmlentities2(deSlashMagic($_POST['text']));
					$post = preg_replace("'/me '","[b]* ".$loguser['name']."[/b] ", $post); //to prevent identity confusion
					$post = str_replace("\n","##TSURUPETTANYOUJO##", $post);
					TidyPost($post);
					$post = str_replace("##TSURUPETTANYOUJO##","\n", $post);
					$post = mysql_real_escape_string($post);
	
					$qPMT = "update pmsgs_text set title = '".justEscape($_POST['title'])."', text = '".$post."' where pid = ".$id;
					$rPMT = Query($qPMT);

					Redirect("PM draft updated!","private.php","your PM box");
					exit();
				}
				else
				{
					$qPM = "update pmsgs set drafting = 0 where id = ".$id;
					$rPM = Query($qPM);
					Redirect("PM sent!","private.php","your PM box");
					exit();
				}
			}
			else
				Alert("Enter a message and try again.", "Your PM is empty.");
		}
		else
			Alert("Enter a title and try again.", "Your PM is untitled.");
	}

	//if($_POST['text']) $prefill = htmlval($_POST['text']);
	//if($_POST['title']) $trefill = htmlval($_POST['title']);
	$prefill = $pm['text'];
	$trefill = $pmtitle;

	MakePost($pm, 0, 0, 1);

	Write(
"
	<script type=\"text/javascript\" src=\"lib/Tween.js\"></script>
	<script type=\"text/javascript\" src=\"lib/OpacityTween.js\"></script>
	<table style=\"width: 100%;\">
		<tr>
			<td style=\"vertical-align: top; border: none;\">
				<form action=\"showprivate.php\" method=\"post\">
					<table class=\"outline margin width100\">
						<tr class=\"header1\">
							<th colspan=\"2\">
								Send PM
							</th>
						</tr>
						<tr class=\"cell0\">
							<td>
								To
							</td>
							<td>
								{2}
							</td>
						</tr>
						<tr class=\"cell1\">
							<td>
								Title
							</td>
							<td>
								<input type=\"text\" name=\"title\" style=\"width: 98%;\" maxlength=\"60\" value=\"{1}\" />
							</td>
						<tr class=\"cell0\">
							<td>
								Post
							</td>
							<td>
								<textarea id=\"text\" name=\"text\" rows=\"16\" style=\"width: 98%;\">{0}</textarea>
							</td>
						</tr>
						<tr class=\"cell2\">
							<td></td>
							<td>
								<input type=\"submit\" name=\"action\" value=\"Send\" /> 
								<input type=\"submit\" name=\"action\" value=\"Preview\" />
								<input type=\"submit\" name=\"action\" value=\"Update Draft\" /> 
								<input type=\"submit\" name=\"action\" value=\"Discard Draft\" /> 
								<input type=\"hidden\" name=\"id\" value=\"{3}\" />
							</td>
						</tr>
					</table>
				</form>
			</td>
			<td style=\"width: 200px; vertical-align: top; border: none;\">
",	$prefill, $trefill, $user['name'], $id);

	DoSmileyBar();
	DoPostHelp();

	Write(
"
			</td>
		</tr>
	</table>
");
}
else
{
	MakePost($pm, 0, 0, 1);
}

MakeCrumbs(array("Main"=>"./", "Private messages"=>"private.php", $pmtitle=>""), $links);

?>
