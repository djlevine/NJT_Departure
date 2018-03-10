<?php
include_once 'db.php';

echo json_encode(getInput(), JSON_FORCE_OBJECT);

function getInput(){
	//Lets make sure the user meant what they said
	$stationNames = array();
	$input = isset($_GET['q']) ? ucwords(htmlspecialchars($_GET['q'])) : '';
	/* Check for common alternative station names 
	 * (ie. NJT lists New York Penn as just New York where a user might type in Penn Station)*/
    $alternates = array("Penn Station","Broad St","Montclair State University","Mount Arlington","30th Street Station");
    if($input == "Stns"){
    	$stationNames = sqlSetup(); 
    	$input = null;
    }

	$result = db_query("SELECT * FROM _stationCodes");
	while($row = $result->fetch_assoc()) {
	    $stationCodes[] = $row["stationName"];
	}
  
	if($input){
        //Perform the alternate name check
		$stations = checkInput(array_shift(preg_grep("/$input/i", $alternates))).",$input";
      	$input = array_filter(explode(",",$stations));
      	//Check NJT database of station code to names 
		foreach ($input as $value) {
			$stations = preg_grep("/$value/i", $stationCodes);
			$stations = checkDupes($stations, $stationNames);
			$stationNames = array_merge($stationNames, $stations);
		}
	} else if($input == "" && $input !== null){$stationNames = $stationCodes;}

	if(!$stationNames){ $stationNames = array($input = 'Invalid Station Name'); }
  	sort(array_filter($stationNames));
	//print_r($stationNames); exit;
  	return array_values($stationNames);
}

// Is it a unique value?
function checkDupes($needle, $array){
	$dupArray = array();
	foreach ($needle as $value) {
		if (preg_grep("/$value/i", $array)) {} else { array_push($dupArray, $value);}
	}
	return $dupArray;
}

function checkInput($input){
	switch ($input) {
		/*
        case '':
			// echo err('Please choose a station');
			$result = db_query("SELECT * FROM _stationCodes");
			while($row = $result->fetch_assoc()) {
			    $stationCode[] = $row["stationName"];
			}
			// $stationCode = array('choose' => array_values($stationCode));
			echo json_encode($stationCode, JSON_FORCE_OBJECT);
			exit();
			break;
            */
		case 'Penn Station':
			$input = 'Newark Penn,New York';
			break;
		case 'Broad St':
			$input = 'Newark Broad';
			break;
		case 'Montclair State University':
			$input = 'Montclair,MSU';
			break;
		case 'Mount Arlington':
			$input = 'Mt. Arlington';
			break;
		case '30th Street Station':
			$input = 'Philadelphia';
			break;
		default:
        	$input;
			break;
	}
  return $input;
}
?>