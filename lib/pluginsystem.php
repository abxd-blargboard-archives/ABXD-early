<?php

$pluginList = array();
$pluginSettings = array();

function registerPlugin($pluginname)
{
	global $pluginList, $currentPlug;
	$pluginList[] = $pluginname;
	$currentPlug = $pluginname;
}

function registerSetting($settingname, $label, $check = false)
{
	global $pluginSettings, $currentPlug;
	$pluginSettings[$settingname] = array("label" => $label, "value" => "", "check" => $check);
}

function getSetting($settingname, $useUser = false)
{
	global $pluginSettings, $user;
	if(!$useUser) //loguser
	{
		if(array_key_exists($settingname, $pluginSettings))
			return $pluginSettings[$settingname]["value"];
	}
	else if($user['pluginsettings'] != "");
	{
		$settings = unserialize($user['pluginsettings']);
		if(!is_array($settings))
			return "";
		if(array_key_exists($settingname, $settings))
			return $settings[$settingname];
	}
	return "";
}

function register($bucket, $functionname, $argcount = 0)
{
	global $pluginbuckets;
	$pluginbuckets[$bucket][] = array("name" => $functionname, "args" => $argcount);
}

function runBucket($bucket)
{
	global $pluginbuckets;
	$args = func_get_args();
	if(isset($pluginbuckets[$bucket]))
		foreach($pluginbuckets[$bucket] as $func)
			call_user_func_array($func["name"], array_slice($args, 1, $func["args"]));
}

$pluginsDir = @opendir("plugins");
if($pluginsDir !== FALSE)
{
	while(($plugin = readdir($pluginsDir)) !== FALSE)
	{
		if($plugin[0] == ".") continue;
		if(substr($plugin,-4) != ".php") continue;
		$currentPlug = "";
		include "plugins/".$plugin;
	}
}

if($loguser['pluginsettings'] != "")
{
	$settings = unserialize($loguser['pluginsettings']);
	if(!is_array($settings))
		$settings = array();
	foreach($settings as $setName => $setVal)
		if(array_key_exists($setName, $pluginSettings))
			$pluginSettings[$setName]["value"] = $setVal;
}

?>
