<?php
include_once 'credentials.php';
function db () {
  $creds = creds();
  $servername = "localhost";
  $username = $creds['sqlUser']; //replace with username for MYSQL database
  $password = $creds['sqlPass']; //replace with password for MYSQL database
  $dbname = "njtr_data";

  $checkDB = mysqli_connect($servername, $username, $password);
  if(!$checkDB){
    echo err("Unable to connect");// - Debugging error: " . mysqli_connect_errno());
  } 
  $result = mysqli_query($checkDB, "CREATE DATABASE IF NOT EXISTS $dbname");
   if (!$result) {
      echo err("Unable to create DB");// - Debugging error: "  . mysqli_connect_errno());
    }
  mysqli_close($checkDB);


  static $conn;
  $conn = mysqli_connect ($servername, $username, $password, $dbname);
  
   if (!$conn) {
      echo err("Unable to Connect to DB");// - Debugging error: "  . mysqli_connect_errno());
      exit();
    }

    return $conn;
}

//All POST requests to NJT come through here
function postRequest($api, $station = ""){
  $creds = creds();
  if(!$station) $station = "";
  $fileOpts = http_build_query(array(
      'username' => $creds['njtr_user'], //replace with NJTransit username
      'password' => $creds['njtr_key'], //replace with NJTransit API key
      'station' => $station
    ));
  $options = array(
  'http' =>
    array(
      'method'  => 'POST',
      'header' => 'Content-type: application/x-www-form-urlencoded',
      'content' => $fileOpts,
      'length' => strlen($fileOpts)
      )
    );
  $context = stream_context_create($options);
  $apiResult = file_get_contents($api, false, $context);
  return $apiResult;
}

/*This code is the real deal*/
function db_query($query){
  $conn = db();
  $result = mysqli_query($conn,$query);
  if(!$result){} else {}
  return $result;
}

function err($message){
  $data = array( 0 =>
    array(
    'stationCode' => 'err',
    'line' => '',
    'lineAbrv' => '',
    'destination' => 'Error',
    'trainNo' => '',
    'departureT' => '',
    'departureD' => '',
    'stationPosition' => '',
    'textColor' => '',
    'background' => '',
    'status' => $message,
    'inlineMSG' => '',
    )
  );
  return json_encode($data);
}

//Quick sanitizing function
function sanitizeSql($input){
  $conn = db();
  $input = mysqli_real_escape_string($conn, $input);
  return $input;
}

//Update schedule records
function updateRecord($table, $data){
  $data = array_map("strip_tags", $data); //Strip tags from array (this might replace sanitize?)
                                          //https://stackoverflow.com/questions/4861053/php-sanitize-values-of-a-array
  $sql = "INSERT INTO $table (".implode(", ", array_keys($data)).") VALUES ('".implode("', '", $data)."')";
  db_query($sql);
}

//Reset a specific table requires table name
function resetTable($table){
  db_query("DELETE FROM $table");
}

//Check if table exists, requires table name
//and SQL for creating the table as parameters
function checkTable($table, $sqlIn){
  $sql = "SHOW TABLES LIKE '$table'";
  $result = db_query($sql);
  $table_exists = mysqli_num_rows($result);
  if(!$table_exists){
    db_query($sqlIn);
  } else {
    resetTable($table);
  }
}

/*Setup SQL tables and get required information from
 * NJT to run the site. (DB will be setup automatically on connection)*/
function sqlSetup(){
	$stnTable = "CREATE TABLE `_stationCodes` (
		`stationCode` varchar(3) NOT NULL,
		`stationName` text NOT NULL,
		`updated` text NOT NULL
    )";
	checkTable("_stationCodes", $stnTable);

	// $api = 'http://njttraindata_tst.njtransit.com:8090/njttraindata.asmx/getStationListXML'; //Test
	$api = 'http://traindata.njtransit.com:8092/NJTTrainData.asmx/getStationListXML'; //Prod
	$postResults = simplexml_load_string(postRequest($api));
	foreach ($postResults -> STATION as $station) {
		$data = array(
			'stationCode' => sanitizeSql($station->STATION_2CHAR),	//Station 2 digit code
			'stationName' => sanitizeSql($station->STATIONNAME),     //Station Name
			'updated' => sanitizeSql('0') // Last updated time (default to zero)
		);
		updateRecord("_stationCodes", $data);
	}
	return array(0 => "Successfully setup 'njtr_data' database");
}
?>