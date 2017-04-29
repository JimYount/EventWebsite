<?php
	// 	ShowDatabase.php: Pulls my remote database and outputs it to whatever website article it is 
	//	plugged into using html tags.
	//
	//	Author: Jim Yount
		
	
	// Logs into the database server and pulls all the events there by date and time.
		
	$dbh = new PDO('mysql:dbname=yourDatabaseName;host=yourDatabaseIP', 'yourDatabaseUsername', 'yourDatabasePassword');
	$set = $dbh->query('SELECT * FROM `AllAshevilleEvents` ORDER BY DATE, TIME');
	$lastdate = "";
	$lasttime = "";
	$thisdate = "";
	$thistime = "";
	$dateset = false;
	$month = "";
	$day = "";
	$ampm = 'am';
	
	
	// An array translating the venue names to their addresses.
	// This may eventually need to be part of the database table, 
	// but is still very small.
	
	$sites = array
	(
		array("The Orange Peel", "http://theorangepeel.net/events/"),
		array("Regal Biltmore Grande Stadium 15 & RPX","http://www.regmovies.com/theatres/theatre-folder/regal-biltmore-grande-stadium-15-rpx-8597")
	);
	
	
	// Table was the only way to make float: left divs obey my formatting.
	
	echo '<table>';
	
	
	// While there still events left to post
	
	while($db = $set->fetch()){
		
		// This translates the military time into am / pm format
		
		$ampm = 'am';
		$hour = (int)substr($db[2], 0, 2);
		if ($hour > 12){
			$hour = $hour - 12;
			$ampm = 'pm';
		}
		
		
		// Pulls the link for the particular venue.
		
		foreach ($sites as &$site) {
			if ($site[0] == $db[4]){
				$sitelink = $site[1];
			}
		}
		
		// Translate the numerical date back into human-readable form
		
		switch (substr($db[1], 5, 2)){
			case "01":
				$month = "January ";
				break;
			case "02":
				$month = "February ";
				break;
			case "03":
				$month = "March ";
				break;
			case "04":
				$month = "April ";
				break;
			case "05":
				$month = "May ";
				break;
			case "06":
				$month = "June ";
				break;
			case "07":
				$month = "July ";
				break;
			case "08":
				$month = "August ";
				break;
			case "09":
				$month = "September ";
				break;
			case "10":
				$month = "October ";
				break;
			case "11":
				$month = "November ";
				break;
			case "12":
				$month = "December ";
				break;
		}
		
		
		// Pulls the date number out of the Date object string and clears
		// zeros from the front
		
		$day = substr($db[1], 8, 2);
		
		if (substr($day, 0, 1) == "0"){
			$day = substr($day, 1, 1);
		}
		
		$thisdate = $db[1];
		$thistime = $db[2];
		
		//	For future date and time grouping. Does not look good with few elements per time / day
		/*
		if ($thisdate != $lastdate){
			echo '<tr style="border: none;">';
				echo '<td style="border: 0; font-size:30px; padding-top:40px; padding-bottom:40px;">';
				echo $month, $day, '</td>';
			echo '</tr>';
			echo '<tr style="border: none;">';
				echo '<td style="border: 0; font-size:20px; padding-top:10px; padding-bottom:10px;">';
				echo $hour, substr($db[2], 2, 3), ' ', $ampm, '</td>';
			echo '</tr>';
			echo '<tr style="border: none;">';
			
			$dateset = true;
		}
		
		if ($thistime != $lasttime && !$dateset){
			echo '<tr style="border: none;">';
				echo '<td style="border: 0; font-size:20px; padding-top:10px; padding-bottom:10px;">';
				echo $hour, substr($db[2], 2, 3), ' ', $ampm, '</td>';
			echo '</tr>';
			echo '<tr style="border: none;">';
		}
		*/
		
		
		// These commented bits before the divs are for the future grouping by date and time.
		// This is the div for each event post.
		
		//echo '<td style="border: 0">';
		echo '<div style="background-color: #000000; margin = auto; float: left; width:276px; max-width:276px; border: 2px solid #aaa; padding: 2px">';
		
			
			// This is the date and time at the event listing
			
			//echo '<tr style="border: none;">';
			echo '<div style="font-size:30px; margin = auto; float: left; width: 46%; padding: 2%">';
			echo $month, $day; 
			echo '</div>';
			echo '<div style="font-size:22px; margin = auto; float: left; width: 46%; padding: 2%">';
			echo $hour, substr($db[2], 2, 3), ' ', $ampm;
			echo '</div>';
			//echo '</tr>';
			
			
			//echo '<div style="font-size:30px; margin = auto; float: left; width: 46%; padding: 2%">';
			//echo '</div>';
			
			
			// These are the two inner columns of the event listing
			// these $db[] variables correspond to the database elements:
			// 1: eventdate, 2: eventTimeStr, 3: venue, 4: eventname, 5: eventtype,
			// 6: eventcost, 7: eventdescription, 8: buylink, 9: eventlink
			
			echo '<div style="margin = auto; float: left; width: 46%; padding: 2%; overflow:auto">';
			
				// The event name that links to the event
			
				if ($db[9] != ""){
					echo '<a href="', $db[9], '">';
				}
				
				echo '<div style="margin = auto; border: 3px solid #fff; padding: 2px; overflow:auto">';
					echo '<strong>', '<font color="White">',  $db[3], '</font>', '</strong>';
				echo '</div>';
				
				if ($db[9] != ""){
					echo '</a>';
				}
				
				
				// Venue and Cost linking to venue site and buy tickets
				
				echo '<a href="', $sitelink, '">';
				echo '<font color="Orange">', $db[4], '</font>', '</a>', '</br>', '</br>';
				
				if ($db[8] != ""){
					echo '<a href="', $db[8], '">';
				}
				
				echo '<font color="Yellow">', $db[6], '</font>';
				
				if ($db[8] != ""){
					echo '</a>';
				}
				
			echo '</div>';
		
			echo '<div style="margin = auto; float: left; width: 46%; padding: 2%; overflow:auto">';
				
				// The event category and description
			
				echo '<font color="Cyan">', $db[5], '</font>', '</br>', '</br>';
				echo '<font color="Green">', $db[7], '</font>';
			echo '</div>';
		echo '</div></td>';
		
		// Also having to do with date and time grouping
		/*
		if ($thistime != $lasttime){
			echo '</tr>';
		}
		
		if ($thistime != $lasttime && !$dateset){
			echo '</tr>';
		}
		
		$lastdate = $db[1];
		$lasttime = $db[2];
		$dateset = false;
		
		*/
		
		
	}
	
	echo '</table>';

	// How to close the database object.
	
	$dbh = NULL;
?>