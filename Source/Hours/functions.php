<?php

date_default_timezone_set('Europe/Copenhagen');

/** Check session */
function checkSession()
{
	if (isset($_SESSION['user']))
	{
		$username = $_SESSION['user'];
		return $username;
	}
	else
	{
		echo "<link rel='stylesheet' href='style.css' type='text/css' />";
		echo "<br><br><center>Du er ikke logget ind!</center><br>";
		echo "<center><a href='index.php'>Log ind nu.</a></center><br>";
		die();
	}	
}	

function loggedIn()
{
	(isset($_SESSION['user'])) ? return true : return false;
}

function dag()
{
	//$localtime = localtime(time(), true);
	$dag = date("N");
	switch ($dag)
	{
		case "1":
			$dag = "Mandag";
			break;
		case "2":
			$dag = "Tirsdag";
			break;
		case "3":
			$dag = "Onsdag";
			break;
		case "4":
			$dag = "Torsdag";
			break;
		case "5":
			$dag = "Fredag";
			break;
		case "6":
			$dag = "Lørdag";
			break;
		case "7":
			$dag = "Søndag";
			break;
	}
	return $dag;
}

function uge()
{
	$uge = date("W");
	return $uge;
}

function dato()
{
	/*$localtime = localtime(time(), true);
	$date = str_pad($localtime['tm_mday'], 2, "0", STR_PAD_LEFT);
	$month = str_pad($localtime['tm_mon'] + 1, 2, "0", STR_PAD_LEFT);
	$year = str_pad($localtime['tm_year'] - 100, 2, "0", STR_PAD_LEFT);
	$dato = $date . "." . $month . "." . $year;*/
    $dato = date("d.m.y");
	return $dato;
}

function tid()
{
	$tid = date("H:i:s");
	/*$localtime = localtime(time(), true);
	$timediff = +1; // Timeforskel i forhold til GMT+00
	$sommertid = 1; // Ja = 1, nej = 0
	$tid = str_pad($localtime['tm_hour'] + $timediff + $sommertid, 2, "0", STR_PAD_LEFT) . ":" . str_pad($localtime['tm_min'], 2, "0", STR_PAD_LEFT) . ":" . str_pad($localtime['tm_sec'], 2, "0", STR_PAD_LEFT);*/
	return $tid;
}

function worktime($checkin, $checkout)
{
//echo "Worktime: <br>";
	$checkin = strtotime($checkin);
   // echo $checkin . "<br>";
	$checkout = strtotime($checkout);
    //echo $checkout . "<br>";
	$worktime = $checkout - $checkin;
   // echo $worktime . "<br>";
	//$worktime = date("H:i:s", $worktime);
    $worktime = secondsToHours($worktime);
   // echo $worktime . "<br>";
	return $worktime;
}

function mround($val, $pre = 0)
{
    return (int) ($val * pow(10, $pre)) / pow(10, $pre);
}

function timeBetween($from,$to) 
{
	list($firstMinutes, $firstSeconds) = explode(':', $from);
	list($secondMinutes, $secondSeconds) = explode(':', $to);
	$firstSeconds += ($firstMinutes * 60);
	$secondSeconds += ($secondMinutes * 60);
	$difference = $secondSeconds - $firstSeconds;
	return $difference;
}

function hoursToSeconds ($hour) // $hour must be a string type: "HH:mm:ss"
{ 
    $parse = array();
    if (!preg_match ('#^(?<hours>[\d]{2}):(?<mins>[\d]{2}):(?<secs>[\d]{2})$#',$hour,$parse)) {
         // Throw error, exception, etc
         echo "<font color='red'>Could not convert '" . $hour . "' to seconds using hoursToSeconds()</font><br>";
    }
    return (int) $parse['hours'] * 3600 + (int) $parse['mins'] * 60 + (int) $parse['secs'];
}

function secondsToHours($seconds) 
{
    // If negative seconds we make it positive, and add a '-' sign at the beginning of the output.
	if ($seconds < 0)
	{
		$seconds = $seconds * -1;
		$hms = "-";
	}
	else
	{
		$hms = "";
	}
    // do the hours first: there are 3600 seconds in an hour, so if we divide
    // the total number of seconds by 3600 and throw away the remainder, we're
    // left with the number of hours in those seconds
	$h = intval(intval($seconds) / 3600);
	// dividing the total seconds by 60 will give us the number of minutes
    // in total, but we're interested in *minutes past the hour* and to get
    // this, we have to divide by 60 again and then use the remainder
	$m = intval(($seconds / 60) % 60);
	// seconds past the minute are found by dividing the total number of seconds
    // by 60 and using the remainder
	$s = intval($seconds % 60);
	$hms .= str_pad($h, 2, "0", STR_PAD_LEFT) . ":" . str_pad($m, 2, "0", STR_PAD_LEFT) . ":" . str_pad($s, 2, "0", STR_PAD_LEFT);
	return $hms;
}

function secondsToLong($seconds) 
{
	// If negative seconds we make it positive, and add a '-' sign at the beginning of the output.
	if ($seconds < 0)
	{
		$seconds = $seconds * -1;
		$output = "-";
	}
	else
	{
		$output = "";
	}
	// 86400 seconds in a day
	$d = intval(intval($seconds) / 86400);
	$seconds -= $d * 86400;
    // there are 3600 seconds in an hour, so if we divide
    // the total number of seconds by 3600 and throw away the remainder, we're
    // left with the number of hours in those seconds
	$h = intval(intval($seconds) / 3600);
	$seconds -= $h * 3600;
	// dividing the total seconds by 60 will give us the number of minutes
    // in total, but we're interested in *minutes past the hour* and to get
    // this, we have to divide by 60 again and then use the remainder
	$min = intval(($seconds / 60) % 60);
	$seconds -= $min * 60;
	// seconds past the minute are found by dividing the total number of seconds
    // by 60 and using the remainder
	$s = intval($seconds % 60);
	$seconds -= $s * 60;
	
	if ($d != 0)
		$output .= $d . " d ";
	if ($h != 0)
		$output .= $h . "t ";
	if ($min != 0)
		$output .= $min . "m ";
	if ($s != 0)
		$output .= $s . "s ";
	return $output;
}

function opendb()
{
	include "config.php";
	$connect = mysql_connect($dbhost, $dbuser, $dbpass);
	mysql_set_charset("utf8");
	if ($connect)
	{
		//echo "Connected to database.<br />";
		mysql_select_db($dbname, $connect) or mysql_error();
	}
	else
	{
		echo "Could not connect to database: " . mysql_error();
		exit();
	}
}

function closedb()
{
	mysql_close() or die(mysql_error());
	if(!$connect)
	{
		//echo "Closed connection to database.";
	}
	else
		echo "Could not close db connection: " . mysql_error();
}

?>
