<?php

include("lib/mysql.php");

print "<pre>";

$allrevisedposts = Query("select pid, text, revision from posts_text where revision > 0 order by pid asc, revision desc");
while($revision = Fetch($allrevisedposts))
{
	$deletion = "delete from posts_text where pid = ".$revision['pid']." and revision = ".($revision['revision']-1);
	print "<b>Query:</b> ".$deletion."\n";
	Query($deletion);
}

$remover = "update posts_text set revision = 0 where revision > 0";
print "<b>Query:</b> ".$remover."\n";
Query($remover);

?>
