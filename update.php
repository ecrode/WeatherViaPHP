<?php 
	// do not forget to setup the cron-job in order to make this process automated.
	// Required data from Weather Underground
	
	// you can define variables here or via URL, make sure to comment out or delete hardcoded variables to get them from URL
//	start of hardcoded variables
	// API key https://www.wunderground.com/weather/api/
	$wuAPI = "ebEXAMPLEab"; // change contents, keep quotes
	$wuID = "Kexample37";

	// Data needed from PWS weather
	$pwsID = "FexampleER"; // change contents, keep quotes
	// $pwsID = filter_var("FexampleER", FILTER_SANITIZE_STRING); // Example of sanitized variable, worth trying if you run into errors
	$psw = "1ExAmPlE."; // seems to dislike commas, try simplier password in case you get ID/pass error (periods "." are ok)
// 	End of hardcoded variables

	// get missing data from URL (if available)
	if(!isset($wuAPI))
		$wuAPI = filter_input(INPUT_GET,"wuAPI",FILTER_SANITIZE_STRING);
	if(!isset($wuID))
		$wuID = filter_input(INPUT_GET,"wuID",FILTER_SANITIZE_STRING);
	if(!isset($pwsID))
		$pwsID = filter_input(INPUT_GET,"pwsID",FILTER_SANITIZE_STRING);
	if(!isset($psw))
		$psw = filter_input(INPUT_GET,"psw",FILTER_SANITIZE_STRING);

	// start of code

	if(isset($wuAPI) && isset($wuID) && isset($pwsID) && isset($psw)){
		
		$wuData = file_get_contents('http://api.wunderground.com/api/' . $wuAPI . '/conditions/q/pws:' .$wuID . '.json');
		$data = json_decode($wuData,true);
		
		
		if(isset($data['current_observation'])){

			$date = new DateTime("@" . $data['current_observation']['observation_epoch']);
			
			$delta = time() - $data['current_observation']['observation_epoch'];
			
			if($delta > 2000){ // to get rid of old data spikes
				
				echo("The data from ".$delta." seconds ago was too old for trasfer, will retry on next attempt");
				
			} else {
				$url = "http://www.pwsweather.com/pwsupdate/pwsupdate.php?ID=". $pwsID ."&PASSWORD=". urlencode($psw) ."&dateutc=" . $date->format('Y-m-d+H:i:s') .
					($data['current_observation']['wind_degrees'] >= 0 ? "&winddir=" . $data['current_observation']['wind_degrees'] : '' ) . 
					($data['current_observation']['wind_mph'] >= 0 ? "&windspeedmph=" . $data['current_observation']['wind_mph'] : '' ) . 
					($data['current_observation']['wind_gust_mph'] >= 0 ? "&windgustmph=". $data['current_observation']['wind_gust_mph'] : "" ) .  
					// I would be impressed if anyone recorded temperatures close to absolute zero.
					($data['current_observation']['temp_f'] > -459 ? "&tempf=" . $data['current_observation']['temp_f'] : "" ) . 
					($data['current_observation']['precip_1hr_in'] >= 0 ? "&rainin=" . $data['current_observation']['precip_1hr_in']  : "" ) . 
					($data['current_observation']['precip_today_in'] >= 0 ? "&dailyrainin=" . $data['current_observation']['precip_today_in'] : "" ) . 
					($data['current_observation']['pressure_in'] >= 0 ? "&baromin=" . $data['current_observation']['pressure_in']  : "" ) . 
					($data['current_observation']['dewpoint_f'] > -100 ? "&dewptf=" . $data['current_observation']['dewpoint_f']  : "" ) . 
					(substr($data['current_observation']['relative_humidity'], 0, 1) <> '-' ? "&humidity=" . substr($data['current_observation']['relative_humidity'], 0, -1)  : "" ) . 
						"&softwaretype=ebviaphpV0.3&action=updateraw";
			
				
				
				$pwsdata =  file_get_contents($url);
				
				$results = explode("\n", $pwsdata);

				switch ($results[6]){ // 6 represents the 7th line (count starts at 0) which carries useful information
					case "ERROR: Not a vailid Station ID":
						echo (
'<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>PWSweather Error</title>
</head>
<body>
<h1>We got an error from PWS weather:</h1>
<p>Your PWS weather ID (pwsID) appears to be invalid</p>
</body> </html>');
						break;
					case "ERROR: Not a vailid Station ID/Password":
						echo (
'<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>PWSweather Error</title>
</head>
<body>
<h1>We got an error from PWS weather:</h1>
<p>Your PWS account password (psw) appears to be invalid</p>
</body> </html>');
						break;
					case "Data Logged and posted in METAR mirror.":
						echo("The latest data from ".$delta." seconds ago was transfered to PWS weather station " . $pwsID);
						break;
					default:
						echo $pwsdata;
						break;
				}
			}

			
		} else {
				//http_response_code(400); // bad request 
				// we got an error
				if(isset($data['response']['error'])){
				echo (
				'<!doctype html>
				<html>
				<head>
				<meta charset="utf-8">
				<title>Weather Underground Error</title>
				</head>
				<body>
				<h1>We got an error from Weather Underground:</h1><p>');

				switch($data['response']['error']['type']){
					case "keynotfound":
						echo('Your Weather Underground API key (wuAPI) appears to be invalid');
						break;
					case "Station:OFFLINE":
						echo('Your Weather Underground Station ID (wuID) appears to be invalid');
						break;
					default:
						echo('This appears to be a temporary error, please try again later</p>');
						echo("<p>Exact Error type: " . $data['response']['error']['type'] . "</p>");
						echo("<p>Which means: " . $data['response']['error']['description']);
						break;
				}
				echo ('</p></body> </html>');

			}
		}
		
	} else {
		echo (
		'<!doctype html>
		<html>
		<head>
		<meta charset="utf-8">
		<title>Insuficient Data</title>
		</head>
		<body>
		<p>Not enough URL or Hardcoded parameters</p>
		</body>
		</html>');
	}
	
	?>
