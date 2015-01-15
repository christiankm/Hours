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
	
	if (isset($_POST['gem']))
	{
		/*if ($entries != 1)
     {
     header("Refresh: 2; overview.php");
     echo "<p><b>Denne forekomst kan ikke ændres, da den ikke tilhører denne bruger!</b></p>";
     echo "<p><a href='overview.php'>< Tilbage</a></p>";
     exit();
     }*/
		if ($_POST['pause'] == "")
			$_POST['pause'] = 0;
		if (isset($_POST['checkout']) & $_POST['checkout'] != "-")
		{
			$newWorktime = hoursToSeconds($_POST['checkout']) - hoursToSeconds($_POST['checkin']);
			$newWorktime = secondsToHours($newWorktime);
		}
		mysql_query("UPDATE checks SET checkin='" . $_POST['checkin'] . "', checkout='" . $_POST['checkout'] . "', pause='" . $_POST['pause'] . "', worktime='" . $newWorktime . "', comment='" . $_POST['comment'] . "' WHERE id='" . $_POST['editID'] . "'");
		header("Refresh: 2; overview.php");
		echo "<h4><b>Ændringerne blev gemt.</b></h4>";
		echo "<p><a href='overview.php'>< Tilbage</a></p>";
		exit();
	}
  
	?>

<p><b>Redigér forekomst</b></p><br><br>

<form action="editEntry.php" method="POST">
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
		echo "<td>" . "<input type='text' size='6' name='checkin' value='" . $row['checkin'] . "'></td>";
		if ($row['checkout'] == "00:00:00" & $row['date'] == date("Y-m-d"))
			echo "<td> - </td>";
		else
			echo "<td>" . "<input type='text' size='6' name='checkout' value='" . $row['checkout'] . "'></td>";
		echo "<td>" . "<input type='text' size='6' name='pause' value='" . $row['pause'] . "'> min</td>";
		if ($row['worktime'] == "00:00:00")
			$worktime = "-";
		else
		{
			$worktime = hoursToSeconds($row['worktime']) - ($row['pause']*60);
			$worktime = secondsToHours($worktime);
		}
		echo "<td>" . $worktime . "</td>";
		echo "<td>" . "<input type='text' name='comment' value='" . $row['comment'] . "'></td>";
		echo "</tr>";
	}
  
	?>

</table><br><br>
<input type="hidden" name="editID" value="<?php echo $id; ?>">
<input type="submit" name="gem" value="Gem" />
</form>
<a href="overview.php"><input type="submit" value="Annullér" /></a>
</center>

</body>
</html>