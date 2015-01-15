<?php
session_start();
require "functions.php";
$username = checkSession();
opendb();

$id = $_GET['id'];
$sql = mysql_query("SELECT * FROM checks WHERE user = '" . $username . "' AND id = '" . $id . "'");
$entries = mysql_num_rows($sql);
?>

<!DOCTYPE HTML>
<html>
	<head>
		<title>
			Timer
		</title>
		<link rel="stylesheet" href="style.css" type="text/css" />
	</head>
	<body>
	<center>
	<br><br><br>
	
	<?php	
	if (isset($_POST['ja']))
	{
		mysql_query("DELETE FROM checks WHERE id='" . $_POST['deleteID'] . "'");
		header("Refresh: 2; overview.php");
		echo "<p><b>Slettet.</b></p>";
		echo "<p><a href='overview.php'>< Tilbage</a></p>";
		exit();
	}	
	if ($entries != 1)
	{
		header("Refresh: 2; overview.php");
		echo "<p><b>Denne forekomst kan ikke slettes, da den ikke tilhører denne bruger!</b></p>";
		echo "<p><a href='overview.php'>< Tilbage</a></p>";
		exit();
	}
	?>
	
	<p><b>Slet forekomst</b></p>
	<p>Er du helt sikker på du vil slette denne forekomst? Handlingen kan <b>ikke</b> fortrydes!</p><br>
	
	<table id="checks" width="600px">
		<tr>
			<th>Uge</th>
			<th>Dag</th>
			<th>Dato</th>
			<th>Check Ind</th>
			<th>Check Ud</th>
			<th>Pause</th>
			<th>Arbejdstid</th>
			<th>Kommentar</th>
		</tr>
	<?php
	while ($row = mysql_fetch_assoc($sql))
	{
		echo $row[0];
		$worktime = secondsToHours(hoursToSeconds($row['worktime']) - ($row['pause'] * 60));
		if ($row['checkin'] != "00:00:00" & $row['checkout'] == "00:00:00")
			echo "<tr class='thisCheck'>";
		else
			echo "<tr>";
		echo "<td>" . $row['week'] . "</td>";
		echo "<td>" . $row['day'] . "</td>";
		echo "<td>" . $row['date'] . "</td>";
		echo "<td>" . $row['checkin'] . "</td>";
		if ($row['checkout'] == "00:00:00")
			$row['checkout'] = "-";
		echo "<td>" . $row['checkout'] . "</td>";
		echo "<td>" . $row['pause'] . " min</td>";
		if ($row['worktime'] == "00:00:00")
			$worktime = "-";
		else
		{
			$worktime = hoursToSeconds($row['worktime']) - ($row['pause']*60);
			$worktime = secondsToHours($worktime);
		}
		echo "<td>" . $worktime . "</td>";
		echo "<td>" . $row['comment'] . "</td>";
		echo "</tr>";
	}
	?>
		</table><br><br>
	<form action="deleteEntry.php" method="POST">
	<input type="hidden" name="deleteID" value="<?php echo $id; ?>">
	<input type="submit" name="ja" value="Ja" />
	<a href="overview.php"><input type="submit" name="nej" value="Nej" /></a>
	</form>
	</center>

	</body>
</html>