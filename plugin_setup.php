<?php
//$DEBUG=true;

include_once "/opt/fpp/www/common.php";
include_once "functions.inc.php";
include_once "commonFunctions.inc.php";

//include the array of VOTES
include_once "electionData.inc.php";

$pluginName = "Election";

$pluginUpdateFile = $settings['pluginDirectory']."/".$pluginName."/"."pluginUpdate.inc";


$logFile = $settings['logDirectory']."/".$pluginName.".log";

logEntry("plugin update file: ".$pluginUpdateFile);

if(isset($_POST['updatePlugin']))
{
	logEntry("updating plugin...");
	$updateResult = updatePluginFromGitHub($gitURL, $branch="master", $pluginName);

	echo $updateResult."<br/> \n";
}

if(isset($_POST['submit']))
{
	$VOTES =  implode(',', $_POST["VOTES"]);

//	echo "Writring config fie <br/> \n";
	
	WriteSettingToFile("VOTES",$VOTES,$pluginName);
	WriteSettingToFile("ENABLED",urlencode($_POST["ENABLED"]),$pluginName);
	WriteSettingToFile("SEPARATOR",urlencode($_POST["SEPARATOR"]),$pluginName);
	WriteSettingToFile("LAST_READ",urlencode($_POST["LAST_READ"]),$pluginName);
}
//print_r($pluginSettings);

	
	
	//$VOTES = urldecode(ReadSettingFromFile("VOTES",$pluginName));
	$VOTES = urldecode($pluginSettings['VOTES']);
	
	//$ENABLED = urldecode(ReadSettingFromFile("ENABLED",$pluginName));
	$ENABLED = urldecode($pluginSettings['ENABLED']);
	
	//$SEPARATOR = urldecode(ReadSettingFromFile("SEPARATOR",$pluginName));
	$SEPARATOR = urldecode($pluginSettings['SEPARATOR']);
	
	//$LAST_READ = urldecode(ReadSettingFromFile("LAST_READ",$pluginName));
	$LAST_READ = $pluginSettings['LAST_READ'];
	
	if($SEPARATOR == "") {
		$SEPARATOR="|";
	}
	//echo "VOTES read: ".$VOTES."<br/> \n";
	

	if((int)$LAST_READ == 0 || $LAST_READ == "") {
		$LAST_READ=0;
	}
?>

<html>
<head>
</head>

<div id="<?echo $pluginName;?>" class="settings">
<fieldset>
<legend><?php echo $pluginName;?> Support Instructions</legend>

<p>Known Issues:
<ul>
<li>NONE</li>
</ul>

<p>Configuration:
<ul>
<li>Select the Election data you want to include in the list, shift click to multi select</li>
<li>Enable the plugin, Restart FPPD</li>
<li>Configure your separator that will appear between scores.. Default |
</ul>
<ul>
<li>Add the crontabAdd options to your crontab to have the Election get data every X minutes to process commands</li>
<li>NOTE: This plugin utilizes the MessageQueue plugin. Please install that plugin before configuring this plugin</li>
</ul>



<form method="post" action="/plugin.php?plugin=<?echo $pluginName;?>&page=plugin_setup.php">


<?
echo "<input type=\"hidden\" name=\"LAST_READ\" value=\"".$LAST_READ."\"> \n";

$restart=0;
$reboot=0;

echo "ENABLE PLUGIN: ";

if($ENABLED == "1") {
		echo "<input type=\"checkbox\" checked name=\"ENABLED\"> \n";
//PrintSettingCheckbox("Radio Station", "ENABLED", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");
	} else {
		echo "<input type=\"checkbox\"  name=\"ENABLED\"> \n";
}

echo "<p/> \n";

echo "VOTES: ";
printVOTESOptions();


echo "<p/> \n";


echo "<p/> \n";

echo "Separator: \n";

echo "<input type=\"text\" name=\"SEPARATOR\" size=\"3\" value=\"".$SEPARATOR."\"> \n";

?>
<p/>
<input id="submit_button" name="submit" type="submit" class="buttons" value="Save Config">


<?
 if(file_exists($pluginUpdateFile))
 {
 	//echo "updating plugin included";
	include $pluginUpdateFile;
}

?>
</form>
<p>To report a bug, please file it against the sms Control plugin project on Git: https://github.com/LightsOnHudson/FPP-Plugin-Election

</fieldset>
</div>
<br />
</html>
