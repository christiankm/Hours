<?php 
session_start();
require "functions.php";
$username = checkSession();
?>

<!DOCTYPE HTML>
<html>
	<head>
		<title>
			Timer - Indstillinger
		</title>
		<link rel="stylesheet" href="style.css" type="text/css" />
	</head>
	<body>
<?php
include "topbar.php";
opendb();
// Get usersettings rows from db
$sql = mysql_query("SELECT * FROM users WHERE username = '" . $username . "'");
$settings = mysql_fetch_assoc($sql);
	

?>
<br /><br />
<h3>Indstillinger</h3>


<table id="settings">

<?php 
// Hvis der er trykket gem, gemmer vi indstillinger i db og viser en OK-besked. Ellers viser vi 'Gem'-knappen.
if ($_POST['save'])
{
	// Gem 'Forventede arbejdstider'
	mysql_query("UPDATE users SET expMondayFrom='" . $_POST['expMondayFrom'] . "', expTuesdayFrom='" . $_POST['expTuesdayFrom'] . "', expWednesdayFrom='" . $_POST['expWednesdayFrom'] . "', expThursdayFrom='" . $_POST['expThursdayFrom'] . "', expFridayFrom='" . $_POST['expFridayFrom'] . "' WHERE username='" . $username . "'");
	mysql_query("UPDATE users SET expMondayTo='" . $_POST['expMondayTo'] . "', expTuesdayTo='" . $_POST['expTuesdayTo'] . "', expWednesdayTo='" . $_POST['expWednesdayTo'] . "', expThursdayTo='" . $_POST['expThursdayTo'] . "', expFridayTo='" . $_POST['expFridayTo'] . "' WHERE username='" . $username . "'");
	mysql_query("UPDATE users SET expMondayPause='" . $_POST['expMondayPause'] . "', expTuesdayPause='" . $_POST['expTuesdayPause'] . "', expWednesdayPause='" . $_POST['expWednesdayPause'] . "', expThursdayPause='" . $_POST['expThursdayPause'] . "', expFridayPause='" . $_POST['expFridayPause'] . "' WHERE username='" . $username . "'");
	
	// Vis at ændringer er gemt.
	echo "<center><i>Dine ændringer er gemt!</i></center><br>";
	// Hent nye data fra db
	$sql = mysql_query("SELECT * FROM users WHERE username = '" . $username . "'");
	$settings = mysql_fetch_assoc($sql);
}

// Udregn totale antal forventede timer.
$totalExpectedTime = (hoursToSeconds($settings['expMondayTo']) - hoursToSeconds($settings['expMondayFrom'])) + (hoursToSeconds($settings['expTuesdayTo']) - hoursToSeconds($settings['expTuesdayFrom'])) + (hoursToSeconds($settings['expWednesdayTo']) - hoursToSeconds($settings['expWednesdayFrom'])) + (hoursToSeconds($settings['expThursdayTo']) - hoursToSeconds($settings['expThursdayFrom'])) + (hoursToSeconds($settings['expFridayTo']) - hoursToSeconds($settings['expFridayFrom']));
$totalExpectedTime = $totalExpectedTime - (($settings['expMondayPause'] + $settings['expTuesdayPause'] + $settings['expWednesdayPause'] + $settings['expThursdayPause'] + $settings['expFridayPause']) * 60 );
if ($totalExpectedTime < 133200) // Hvis mindre end 37 timer, vises teksten med rød.
{
	$diff = secondsToHours(133200 - $totalExpectedTime);
	$totalExpectedTime = "<font color='red'>" . secondsToHours($totalExpectedTime) . " ( -" . $diff . " )</font>";
}
else if ($totalExpectedTime == 133200)
{
	$totalExpectedTime = "<font color='green'>" . secondsToHours($totalExpectedTime) . "</font>";
}
else
{
	$diff = secondsToHours($totalExpectedTime - 133200);
	$totalExpectedTime = "<font color='green'>" . secondsToHours($totalExpectedTime) . " ( +" . $diff . " )</font>";
}

// Udskriv indstillinger.
echo "<form method='POST' action='settings.php'>";
echo "<tr><th colspan='2' class='title'>Forventede arbejdstider</th></tr>";
echo "<tr><th>Mandag</th><td><input type='text' name='expMondayFrom' size='7' value='" . $settings['expMondayFrom'] . "' /> - <input type='text' name='expMondayTo' size='7' value='" . $settings['expMondayTo'] . "' /> inkl. <input type='text' name='expMondayPause' size='2' value='" . $settings['expMondayPause'] . "' /> min. pause</td></tr>";
echo "<tr><th>Tirsdag</th><td><input type='text' name='expTuesdayFrom' size='7' value='" . $settings['expTuesdayFrom'] . "' /> - <input type='text' name='expTuesdayTo' size='7' value='" . $settings['expTuesdayTo'] . "' /> inkl. <input type='text' name='expTuesdayPause' size='2' value='" . $settings['expTuesdayPause'] . "' /> min. pause</td></tr>";
echo "<tr><th>Onsdag</th><td><input type='text' name='expWednesdayFrom' size='7' value='" . $settings['expWednesdayFrom'] . "' /> - <input type='text' name='expWednesdayTo' size='7' value='" . $settings['expWednesdayTo'] . "' /> inkl. <input type='text' name='expWednesdayPause' size='2' value='" . $settings['expWednesdayPause'] . "' /> min. pause</td></tr>";
echo "<tr><th>Torsdag</th><td><input type='text' name='expThursdayFrom' size='7' value='" . $settings['expThursdayFrom'] . "' /> - <input type='text' name='expThursdayTo' size='7' value='" . $settings['expThursdayTo'] . "' /> inkl. <input type='text' name='expThursdayPause' size='2' value='" . $settings['expThursdayPause'] . "' /> min. pause</td></tr>";
echo "<tr><th>Fredag</th><td><input type='text' name='expFridayFrom' size='7' value='" . $settings['expFridayFrom'] . "' /> - <input type='text' name='expFridayTo' size='7' value='" . $settings['expFridayTo'] . "' /> inkl. <input type='text' name='expFridayPause' size='2' value='" . $settings['expFridayPause'] . "' /> min. pause</td></tr>";
echo "<tr><th>I alt</th><td><center>" . $totalExpectedTime . "</center></td></tr>";

echo "<tr><th colspan='2'><center><input type='submit' class='submit' name='save' value='Gem ændringer' /><center></th></tr>";

closedb();
?>
</table>

</body>
</html>