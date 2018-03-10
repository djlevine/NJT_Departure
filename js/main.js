/*
 *Limit request to server by waiting for the user to finish typing
 *Borrowed this solution from
 *https://stackoverflow.com/questions/4220126/run-javascript-function-when-user-finishes-typing-instead-of-on-key-up
 */
var typingTimer; //timer identifier
var input = document.getElementById('stnInput');

input.onkeyup = function(){
	clearTimeout(typingTimer);
	typingTimer = setTimeout(checkInput, 650);
};

input.onkeydown = function(){
	clearTimeout(typingTimer);
};

//Server side input check
function checkInput(){
	var xhttp = new XMLHttpRequest();
	//Prepare the Ajax request
	xhttp.onreadystatechange = function() {
	  	if (this.readyState == 4 && this.status == 200) {
	  		//Remove all children
	  		var oldContent = document.getElementById("results");
	  		var newContent = document.createElement('div');
	  		newContent.className = 'updateItem'
	  		while (oldContent.firstChild) {
	  		    oldContent.removeChild(oldContent.firstChild);
	  		}
	  		//Check if the station exists
	   		try{
	   			eval(myObj = JSON.parse(this.responseText));
	   			var length = eval(Object.keys(myObj).length);
	   		}
	   		catch(e){
	   			// console.log('Error returning results from server');
	   			var stns = document.createElement("span");
	   			stns.innerHTML = "<button class='stnButton'>An error has occurred. Please refresh the page and try again.</button>";
	   			newContent.appendChild(stns);
	   			document.getElementById('results').appendChild(newContent);	
	   		}
			for (var i = 0, l = length; i < l; i++) {
				var stns = document.createElement("span");
				if(myObj[i] != "error" && myObj[i] != "Invalid Station Name"){
					stns.innerHTML = "<button class='stnButton' onclick='getData(\""+myObj[i]+"\")'>" + myObj[i] + "</button>";
				} else {
					stns.innerHTML = "<button class='stnButton'>" + myObj[i] + "</button>";
				}
				newContent.appendChild(stns);
			}
			document.getElementById('results').appendChild(newContent);	   	
	  	}
	}
	var value = document.getElementById('stnInput').value;
	xhttp.open("GET", "includes/checkIn.php?q=" + value, true);
	xhttp.send();
}

//Get the requested departurevision information
function getData(value) {
	spnMSG = document.getElementById("spnMessage");
	spnMSG.innerText = "Getting information for " + value;
	document.getElementById("overlay").style.display = "block";
	// value = document.getElementById('stnInput').value;
	if(!value)value="";
	var xhttp = new XMLHttpRequest();

	//Prepare the Ajax request
	xhttp.onreadystatechange = function() {
	  	if (this.readyState == 4 && this.status == 200) {
	  		//Remove all children
	  		var oldContent = document.getElementById("results");
	  		while (oldContent.firstChild) {
	  		    oldContent.removeChild(oldContent.firstChild);
	  		}
	   		//var myObj = 
	   		try{
	   			eval(myObj = JSON.parse(this.responseText));
	   		} catch(e){
	   			myObj = '';
	   			console.log("Error");
	   			document.getElementById("overlay").style.display = "none";
	   		}

			var newTable = document.createElement('table');
			newTable.id = "departureV";
			newTable.innerHTML = 
			"<thead><tr id='tblHead'>" +
				"<th width='12%' >Departs</th>" +
				"<th>To</th>" +
				"<th width='11%'>Line</th>" +
				"<th width='8%'>Train</th>" +
				"<th width='6%'>trk</th>" +
				"<th width='18%'>Status</th>" +
			"</tr></thead>";
			document.getElementById('results').appendChild(newTable);
	
	   		for (var i = 0, l = Object.keys(myObj).length; i < l; i++) {
	   			var response = {
	   				stationCode:myObj[i]['stationCode'],
	   				line:myObj[i]['line'],
	   				lineAbrv:myObj[i]['lineAbrv'],
	   				destination:myObj[i]['destination'],
	   				trainNo:myObj[i]['trainNo'],
	   				trackNo:myObj[i]['trackNo'],
	   				departureT:myObj[i]['departureT'],
	   				stationPosition:myObj[i]['stationPosition'],
	   				textColor:myObj[i]['textColor'],
	   				background:myObj[i]['background'],
	   				status:myObj[i]['status'],
	   				inlineMSG:myObj[i]['inlineMSG']
	   			};
	   			writeToPage(response);
	   		}
	   		if (Object.keys(myObj).length == 0) {
	   			var response = {
	   				stationCode:'none',
	   				lineAbrv:'',
	   				destination:'No trains departing this station.',
	   				trainNo:'',
	   				trackNo:'',
	   				departureT:'',
	   				stationPosition:'',
	   				textColor:'',
	   				background:'',
	   				status:'',
	   				inlineMSG:''
	   			};
	   			writeToPage(response);
	   		}

	  	}
	};
	xhttp.open("GET", "includes/accessData.php?q=" + value, true);
	xhttp.send();
}

//Write departure information to the page
function writeToPage(response){
	if (response.trackNo == 'Single') {response.trackNo = 1;}
	var newContent = document.createElement('tr');
	newContent.className = 'updateItem ' + response.lineAbrv; //Leave the space after the classname or they all mush together
	newContent.style = "color:" + response.textColor + "; background:" + response.background + "; font-size: 14px;";
	newContent.innerHTML = 
			"<td>" + response.departureT + "</td>" +
			"<td align='left'>" + response.destination + "</td>" +
			"<td align='center'>" + response.lineAbrv + "</td>" +
			"<td align='center'>" + response.trainNo + "</td>" +
			"<td align='center'>" + response.trackNo  + "</td>" +
			"<td align='right'>" + response.status + "</td>";
	document.getElementById('departureV').appendChild(newContent);
	document.getElementById("overlay").style.display = "none";
}

// function writeToPage(response){
// 	var newContent = document.createElement('div');
// 	newContent.className = 'updateItem';
// 	newContent.innerHTML = 
// 		response.stationCode + "<br>" +
// 		response.line + "<br>" +
// 		response.lineAbrv + "<br>" +
// 		response.destination + "<br>" +
// 		response.trainNo + "<br>" +
// 		response.departureT + "<br>" +
// 		response.stationPosition + "<br>" +
// 		response.textColor + "<br>" +
// 		response.background + "<br>" +
// 		response.status + "<br>" +
// 		response.inlineMSG + "<br>";
// 	document.getElementById('results').appendChild(newContent);
// 	document.getElementById("overlay").style.display = "none";
// }