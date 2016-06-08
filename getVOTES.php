#!/usr/bin/php
<?
error_reporting(0);

$pluginName ="Election";
$myPid = getmypid();

$messageQueue_Plugin = "MessageQueue";
$MESSAGE_QUEUE_PLUGIN_ENABLED=false;

$DEBUG=true;

$skipJSsettings = 1;
require_once("/opt/fpp/www/config.php");
require_once("/opt/fpp/www/common.php");
//include_once("/opt/fpp/www/plugin.php");

include_once("functions.inc.php");
include_once "electionData.inc.php";
require ("lock.helper.php");

define('LOCK_DIR', '/tmp/');
define('LOCK_SUFFIX', '.lock');



$pluginConfigFile = $settings['configDirectory'] . "/plugin." .$pluginName;
if (file_exists($pluginConfigFile))
	$pluginSettings = parse_ini_file($pluginConfigFile);


//print_r($pluginSettings);


$logFile = $settings['logDirectory']."/".$pluginName.".log";

$messageQueuePluginPath = $pluginDirectory."/".$messageQueue_Plugin."/";

$messageQueueFile = urldecode(ReadSettingFromFile("MESSAGE_FILE",$messageQueue_Plugin));

if(($pid = lockHelper::lock()) === FALSE) {
	exit(0);

}

if(file_exists($messageQueuePluginPath."functions.inc.php"))
	{
		include $messageQueuePluginPath."functions.inc.php";
		$MESSAGE_QUEUE_PLUGIN_ENABLED=true;

	} else {
		logEntry("Message Queue Plugin not installed, some features will be disabled");
	}	


//if($MESSAGE_QUEUE_PLUGIN_ENABLED) {
//	$queueMessages = getNewPluginMessages("SMS");
//	print_r($queueMessages);
//} else {
//	logEntry("MessageQueue plugin is not enabled/installed");
//}	

//	print_r($pluginSettings);
//	echo "\n";
	
	//$SPORTS = urldecode(ReadSettingFromFile("SPORTS",$pluginName));
	$VOTES = urldecode($pluginSettings['VOTES']);
	$STATE = urldecode($pluginSettings['STATE']);
	
	//$ENABLED = urldecode(ReadSettingFromFile("ENABLED",$pluginName));
	$ENABLED = urldecode($pluginSettings['ENABLED']);
	
	//$SEPARATOR = urldecode(ReadSettingFromFile("SEPARATOR",$pluginName));
	$SEPARATOR = urldecode($pluginSettings['SEPARATOR']);
	
	//$LAST_READ = urldecode(ReadSettingFromFile("LAST_READ",$pluginName));
	$LAST_READ = $pluginSettings['LAST_READ'];
	$eYEAR = urldecode($pluginSettings['YEAR']);
	$eMONTH = urldecode($pluginSettings['MONTH']);
	$eDAY = urldecode($pluginSettings['DAY']);
	
	$eDATE = date(Ymd);
	$eDATE = "20160607";
	$eDATE = $eYEAR.$eMONTH.$eDAY;
	
	$USE_EDATE = urldecode($pluginSettings['EDATE']);
	
	

	//echo "enabled: ".$ENABLED."\n";
	
//echo "ENABLED: ".$ENABLED."\n";
if($ENABLED != "1" && $ENABLED != "on") {
	logEntry("Plugin Status: DISABLED Please enable in Plugin Setup to use & Restart FPPD Daemon");
	lockHelper::unlock();
	exit(0);
	
}
if(($USE_EDATE == 1 || $USE_EDATE == "on") && ($eDATE == "")) {
	logEntry("Plugin configured for election date and no date specified: EXITING");
	lockHelper::unlock();
	exit(0);
}

//$SEPARATOR = urldecode(ReadSettingFromFile("SEPARATOR",$pluginName));

$VOTES_READ = explode(",",$VOTES);

//echo "Incoming sports reading: \n";
//print_r($SPORTS_READ);
//print_r($SPORTS_DATA_ARRAY);

if($DEBUG) {
	echo "VOTES READ: ".print_r($VOTES_READ)."\n";
}
$messageText="";





for($i=0;$i<=count($VOTES_READ)-1;$i++) {
	$VOTE_LOCATOR_INDEX=0;
	
	echo "looking for ".$VOTES_READ[$i]." in Array \n";
	
	foreach ($VOTES_DATA_ARRAY as $VOTE_LOCATORS) {
		echo "getting data title ".$VOTE_LOCATORS[0]."\n";
		//print_r($VOTE_LOCATORS);
		
	if($VOTE_LOCATORS[0] == $VOTES_READ[$i]) {
		
		$messageText .= $SEPARATOR." ".$VOTES_READ[$i]. " ".$SEPARATOR;
		
		echo "FOUND: ".$VOTES_READ[$i]."\n";
		echo "vot locate index = ".$VOTE_LOCATOR_INDEX."\n";
		
		//echo $SPORTS_READ[$i]. " is in Sports data array\n";
			
		$VOTE_DATA_URL = $VOTES_DATA_ARRAY[$VOTE_LOCATOR_INDEX][1];
		
		if($VOTES_READ[$i] == "STATE") {
			//need to append the STATE locator to the URL
			$VOTE_DATA_URL .= $STATE.".xml";
		}
		
		//fetch the information
		if($DEBUG) {
			echo "getting votes data from: ".$VOTE_DATA_URL."\n";
		}
		//$votesData = file_get_contents($VOTE_DATA_URL);
		//$simple = "<para><note>simple note</note></para>";
		$myVotes = simplexml_load_file($VOTE_DATA_URL);
		//$p = xml_parser_create();
		//xml_parse_into_struct($p, $votesData, $vals, $index);
		//xml_parser_free($p);
		
		if($DEBUG) {
		//	echo "Index array\n";
		//	print_r($index);
		//	echo "\nVals array\n";
		//	print_r($vals);
		//	print_r($votesData);
			//print_r($myVotes);
		}
			
		
		$VOTE_RACE_COUNT = count($myVotes->race);
		
		echo "Race count: ".$VOTE_RACE_COUNT."\n";
		
		for($r=0;$r<=$VOTE_RACE_COUNT-1;$r++) {
			
		//	echo "Race : ". ($r+1)."\n";
		//	echo "eDATE : ".$eDATE."\n";
		//	echo "Race date: ".$myVotes->race[$r]['eDate']."\n";
			
			
		//	foreach($myVotes->race[$r]->attributes() as $a => $b) {
				//echo $a,'="',$b,"\"\n";
			//}
			//echo "STATE: ".$myVotes->race[$r]['state']."\n";
			
			if($USE_EDATE == "1" || $USE_EDATE == "on") {
				echo "Searching for races with date : ".$eDATE."\n";
				if($eDATE == $myVotes->race[$r]['eDate']) {
					$messageText .= " ".$SEPARATOR." STATE: ".$myVotes->race[$r]['state']." ".$SEPARATOR;
				}
			
			$CANDIDATE_COUNT = count($myVotes->race[$r]->cand);
		//	echo "There are ".$CANDIDATE_COUNT." in race: ".$r."\n";
			
			for($c=0;$c<=$CANDIDATE_COUNT-1;$c++) {
				
			
				echo "CANDIDATE : ". ($c+1)."\n<br/> \n";
				$CANDIDATE_NAME = $myVotes->race[$r]->cand[$c]['name'];
				$CANDIDATE_VOTES = $myVotes->race[$r]->cand[$c]['votes'];
				$CANDIDATE_VOTES = (string)$CANDIDATE_VOTES;
				$CANDIDATE_VOTES = number_format($CANDIDATE_VOTES);
				
				echo "NAME: ".$CANDIDATE_NAME."\n";
				echo "VOTE: ".$CANDIDATE_VOTES."\n";
				echo "-----\n";
				
				if($USE_EDATE == "1" || $USE_EDATE == "on") {
					echo "Searching for races with date : ".$eDATE."\n";
					if($eDATE == $myVotes->race[$r]['eDate']) {
						
						$messageText .= " ".$CANDIDATE_NAME. ": ".$CANDIDATE_VOTES. " ".$SEPARATOR;
					
						//continue;
					}
					
				} 
				//foreach($myVotes->race[$r]->cand[$c]->attributes() as $e => $f) {
				//	echo $e,'="',$f,"\"\n";
				//}
			}
				
			}
			
		}
		

	
	}
	$VOTE_LOCATOR_INDEX++;
	

	
	}
	
	addNewMessage($messageText,$pluginName,$pluginData=$VOTES_READ[$i]);
	$messageText="";
	$messageLine="";
}

function search_in_array($value, $arr){

	$num = 0;
	for ($i = 0; $i < count($arr); ) {
		if($arr[$i][0] == $value) {
			$num++;
		}
		$i++;
	}
	return $num ;
}
lockHelper::unlock();
?>
