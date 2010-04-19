<?php

function CheckAutobiographer()
{ }

function CheckCommentator()
{ }

function GetBadgeList()
{ return ""; }

/*
define("AUTOBIOGRAPHER", 1, true);
define("COMMENTATOR", 2, true);

function CheckAutobiographer()
{
	global $loguserid;
	if($loguserid == 0)
		return;
	//Always fetch a fresh profile for this one
	$user = Fetch(Query("select * from users where id=".$loguserid));
	if(($user['badges'] & AUTOBIOGRAPHER) == 0)
	{
		if($user['realname'] && $user['location'] && $user['birthday'] && $user['bio'] && $user['email'] && $user['homepageurl'] && $user['homepagename'])
		{
			$newBadges = $user['badges'] + AUTOBIOGRAPHER;
			Query("update users set badges = ".$newBadges." where id=".$loguserid);
		}
	}
}

function CheckCommentator()
{
	global $loguserid;
	if($loguserid == 0)
		return;
	if(($user['badges'] & COMMENTATOR) == 0)
	{
		$numComments = FetchResult("select count(*) from usercomments where cid=".$loguserid, 0, 0);
		print $numComments;
		if($numComments >= 20)
		{
			$newBadges = $user['badges'] + COMMENTATOR;
			Query("update users set badges = ".$newBadges." where id=".$loguserid);
		}
	}
}
function GetBadgeList()
{
	global $loguser;
	$badges = "";
	if($loguser['badges'] & AUTOBIOGRAPHER)
		$badges .= "<span class=\"badge bronze\">Autobiographer</span> ";
	if($loguser['badges'] & COMMENTATOR)
		$badges .= "<span class=\"badge bronze\">Commentator</span> ";
	return $badges;
}
*/

?>