<?php
error_reporting(E_ALL); ini_set('display_errors', 'on');
include_once 'db.php';

$q = strtoupper(isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '');
if ($q) {
	$query = "SELECT stationCode FROM _stationCodes WHERE stationName='$q'";
	if(mysqli_num_rows(db_query($query))==0) { echo err("Invalid station name"); }
	$result = db_query($query);
	while($row = $result->fetch_assoc()) {
	    $stationCode = $row["stationCode"];
	}
	echo currentSched($stationCode);
} else {
	echo err('Station not found.');
}


function currentSched($station){
	$checkTime = date('m:d:H:i:s', time() - 60);
	$sql = "SELECT updated FROM _stationCodes WHERE stationCode='$station'";
	$result = db_query($sql);
	while($row = $result->fetch_assoc()) {
        $updatedTime = $row['updated'];
	}
	if ($updatedTime < $checkTime) {
	// if ($checkTime == $checkTime) { // debugging - Always update info
		//If the schedule needs to be updated
		parseSched($station);
	} else {
		//If the schedule is up to date
	}
	return stationDB($station);
}

//Get Items from the NJT RSS Feed
function parseSched($station){
	$api = 'http://traindata.njtransit.com:8092/NJTTrainData.asmx/getTrainScheduleXML'; //Prod
	$postResults = simplexml_load_string(postRequest($api, $station));
	foreach ($postResults->STATION_2CHAR as $stationCode) {
		$sql = "CREATE TABLE `$stationCode` (
		    `stationCode` varchar(3),
		    `line` text,
		    `lineAbrv` varchar(5),
		    `destination` text,
		    `trainNo` varchar(6),
		    `trackNo` varchar(6),
		    -- `direction` varchar(15),
		    `departureT` varchar(20),
		    `departureD` varchar(20),
		    -- `dwell` int(3),
		    `stationPosition` int(3),
		    `textColor` varchar(15),
		    `background` varchar(15),
		    `status` text,
		    `inlineMSG` text
		  )";
		checkTable($stationCode, $sql);
		foreach ($postResults->ITEMS->ITEM as $element) {
		$data = array(
			'stationCode' => $stationCode,                   //Station 2 digit code
			'line' => $element->LINE,                        //NJT Line
			'lineAbrv' => $element->LINEABBREVIATION,        //NJT Line Abrv
			'destination' =>  sanitizeSql($element->DESTINATION),         //Destination
			'trainNo' => $element->TRAIN_ID,                 //Train schedule number
			'trackNo' => $element->TRACK,                 	 //Track number
			// 'direction' => $element->DIRECTION,           //Direction (east or west)
			'departureT' => date('g:i', strtotime($element->SCHED_DEP_DATE)),         //Scheduled departure time
			'departureD' => date('M d, Y', strtotime($element->SCHED_DEP_DATE)),      //Scheduled departure time
			// 'dwell' => $element->DWELL_TIME,               //Stopped time in Seconds
			'stationPosition' => $element->STATION_POSITION,  //Origin, Stop, or Term
			'textColor' => $element->FORECOLOR,               //Foreground color
			'background' => $element->BACKCOLOR,              //Background color
			'status' => $element->STATUS,                     //Current line status
			'inlineMSG' => trim($element->INLINEMSG)                //Current line message
		);
		updateRecord($stationCode, $data);
		}
		print_r(bannerMessage($postResults, $station));
	}
	$time = date('m:d:H:i:s');
	$updateSql = "UPDATE _stationCodes 
				  set updated = '$time' 
				  where stationCode = '$stationCode'";
	db_query($updateSql);
}

function stationDB($input){
	$departures = array();
	//Retrieve Station Banner Message
	$sqlMsg = "SELECT bannerMsg FROM _stationCodes WHERE stationCode='$input'";
	$resultMsg = db_query($sqlMsg);
	if ($resultMsg) {
		while($row = $resultMsg->fetch_assoc()) {
		     array_push($departures, $row);
		}
	}
	// Retrieve station departure data
	$sql = "SELECT * FROM $input";
	$result = db_query($sql);
	if ($result) {
		while($row = $result->fetch_assoc()) {
		     array_push($departures, $row);
		}
	}

	return json_encode($departures, JSON_FORCE_OBJECT);
}


function bannerMessage($postResults, $stationCode){
	$banner = $postResults->BANNERMSG;
	$updateSql = "UPDATE _stationCodes 
				  set bannerMsg = '$banner' 
				  where stationCode = '$stationCode'";
	db_query($updateSql);
}
?>