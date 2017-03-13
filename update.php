<?php 

	// Required data from Weather Underground
	// API key https://www.wunderground.com/weather/api/
	$wuAPI = "ebEXAMPLEab"; // change contents, keep quotes
	$wuStation = "Kexample37";

	// Data needed from PWS weather
	$pwsID = "FexampleER"; // change contents, keep quotes
	$pwsPass = "1ExAmPlE."; // seems to dislike commas, try simplier password in case you get ID/pass error (periods "." are ok)

	// start of code

	$wuData = file_get_contents('http://api.wunderground.com/api/' . $wuAPI . '/conditions/q/pws:' .$wuStation . '.json');
	$data = json_decode($wuData,true);
	
	$date = new DateTime("@" . $data['current_observation']['observation_epoch']);
	
	$url = "http://www.pwsweather.com/pwsupdate/pwsupdate.php?ID=". $pwsID ."&PASSWORD=". urlencode($pwsPass) ."&dateutc=" . $date->format('Y-m-d+H:i:s') . "&winddir=" . $data['current_observation']['wind_degrees'] . "&windspeedmph=" . $data['current_observation']['wind_mph'] . "&windgustmph=". $data['current_observation']['wind_gust_mph'] . "&tempf=" . $data['current_observation']['temp_f'] . "&rainin=" . $data['current_observation']['precip_1hr_in'] . "&dailyrainin=" . $data['current_observation']['precip_today_in']. "&baromin=" . $data['current_observation']['pressure_in'] . "&dewptf=" . $data['current_observation']['dewpoint_f'] . "&humidity=" . substr($data['current_observation']['relative_humidity'], 0, -1) . "&softwaretype=ebviaphpV0.1&action=updateraw";
	
	echo file_get_contents($url);
	
	?>