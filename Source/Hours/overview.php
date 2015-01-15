<?php
session_start();
include "functions.php";
opendb();
$username = checkSession();
$today = dato();
// Stopur til at vise beregningstiden
$start = (float) array_sum(explode(' ',microtime()));
?>

<!DOCTYPE HTML>
<html>
	<head>
		<meta charset=utf-8>
		<title>
			Timer
		</title>
		<link rel="stylesheet" href="style.css" type="text/css" />
	</head>

	<body>
<?php

include "topbar.php";

// Se Check-in status. Hvis checked ind er status 1, ellers er den 0.
$sql = mysql_query("SELECT status FROM users WHERE username = '" . $username . "'");
$result = mysql_fetch_assoc($sql);
$status = $result['status'];

// Check for om det er første besøg og startsaldo derfor ikke er angivet.
$sql = mysql_query("SELECT * FROM users WHERE username = '" . $username . "' AND firstVisit='1'");
$firstVisit = mysql_num_rows($sql);
if ($firstVisit == 1)
{
	if (isset($_POST['saldo_save']))
	{		
		$t = $_POST['saldo_t'];
		$m = $_POST['saldo_m'];
		$s = $_POST['saldo_s'];
		
		if (!is_numeric($t) | !is_numeric($m) | !is_numeric($s))
		{
			echo "<p class='error'>Du skal indtaste et tal i alle felter!</p><br /><br />";
		}
		else if (($m > 59 | $m < 0) | ($s > 59 | $s < 0))
		{
			echo "<p class='error'>Værdien i minut og sekundfeltet skal være mellem 0 og 59!</p><br /><br />";
		}
		else
		{
			$balance = ($t * 3600) + ($m * 60) + $s;
			if ($_POST['plusminus'] == "minus")
				$balance = $balance - $balance - $balance;
			mysql_query("UPDATE users SET firstVisit='0', balance='" . $balance . "' WHERE username='" . $username . "'");
			echo "<br /><center>";
			echo "Din startbalance er registreret.<br />";
			echo "<a href='overview.php'>Ok</a></center>";
			exit();
		}
	}
	echo "<br><br><br><center>";
	echo "Angiv din balance, inden du oprettede en bruger.<br>Din balance er det antal timer du har opsparet eller er i underskud med.<br> Husk at en hel feriedag svarer til 7t 24m.<br/><br>";
	echo "<form method='POST' action='overview.php'>";
	echo "<select name='plusminus'><option value='plus'>+</option><option value='minus'>-</option></select><input type='text' name='saldo_t' size='4' />t &nbsp; &nbsp; <input type='text' name='saldo_m' size='2' />m &nbsp; &nbsp; <input type='text' name='saldo_s' size='2' />s <input type='submit' name='saldo_save' value='Gem' /><br />";
	echo "</form>";
	die();
}
else
{
	if ($_POST['checkstatus'] == "Check Ind")
	{
		if ($status == 1)
		{
			echo "<p class='error'>Du er allerede checket ind!</p><br />";
		}
		else
		{
			$week = uge();
			$day = dag();
			$checkin = tid();
			$status = 1;
			$checkedIn = true;
      $query = mysql_query("SELECT * FROM users WHERE username = '" . $username . "'");
			$exp = mysql_fetch_assoc($query);
			if (dag() == "Mandag")
			{
				$expPause = $exp['expMondayPause'];
			}
			else if (dag() == "Tirsdag")
			{
				$expPause = $exp['expTuesdayPause'];
			}
			else if (dag() == "Onsdag")
			{
				$expPause = $exp['expWednesdayPause'];
			}
			else if (dag() == "Torsdag")
			{
				$expPause = $exp['expThursdayPause'];
			}
			else if (dag() == "Fredag")
			{
				$expPause = $exp['expFridayPause'];
			}
			mysql_query("INSERT INTO checks (`user`, `week`, `day`, `date`, `checkin`, `pause`) VALUES ('" . $username . "', '" . $week . "', '" . $day . "', DATE_FORMAT(NOW(),'%Y.%m.%d'), '" . $checkin . "', '" . $expPause . "')");
			mysql_query("UPDATE users SET status=1 WHERE username='" . $username . "'");
		}
	}
	else if ($_POST['checkstatus'] == "Check Ud")
	{	
		if ($status == 0)
		{
			echo "<p class='error'>Du er allerede checket ud!</p><br />";
		}
		else
		{
			$sql = mysql_query("SELECT checkin FROM checks WHERE user = '" . $username . "' AND checkin != '00:00:00' AND checkout = '00:00:00' ORDER BY id desc");
			$result = mysql_fetch_assoc($sql);
			$lastcheckin = $result['checkin'];
			$checkout = tid();
			$worktime = worktime($lastcheckin, $checkout); // Pausen trækkes fra i oversigten. Ikke her.
			$status = 0;
			$checkedIn = false;
			mysql_query("UPDATE checks SET checkout='" . $checkout . "', worktime='" . $worktime . "' WHERE user='" . $username . "' AND date = DATE_FORMAT(NOW(),'%Y.%m.%d') AND checkin='" . $lastcheckin . "'");
			mysql_query("UPDATE users SET status=0 WHERE username='" . $username . "'");
		}
	}
}

// Vis check knapper.
if ($status == 1)
{
	echo "<br /><form method='post' action='overview.php'><img src='images/checkOut.png'/><input type='submit' name='checkstatus' id='checkOutButton' value='Check Ud'></form><br />";			
}		
else if ($status == 0)
{	
	echo "<br /><form method='post' action='overview.php'><img src='images/checkIn.png'/><input type='submit' name='checkstatus' id='checkInButton' value='Check Ind'></form><br />";
}

include 'statistics.php';
?>


	<div id="overview">
	<h3>Oversigt</h3>
	
	<center><p id="show_overview">
	Vis forekomster for : <a href="overview.php?range=thisweek">Denne uge</a> &#8226; <a href="overview.php?range=lastweek">Sidste uge</a> &#8226; <a href="overview.php?range=thismonth">Denne måned</a> &#8226; <a href="overview.php?range=3months">Seneste 3 måneder</a> &#8226; <a href="overview.php?range=6months">Seneste 6 måneder</a> &#8226; <a href="overview.php?range=thisyear">Dette år</a> &#8226; <a href="overview.php?range=coming">Kommende</a> &#8226; <a href="overview.php?range=all">Alle</a>
	</p></center>

	<?php
    // Get checkdata rows from db
    if ($_GET['range'] == "thisweek")
        $checkdata = mysql_query("SELECT * FROM checks WHERE user = '" . $username . "' AND week = '" . uge() . "' AND YEAR(date) = '" . date('Y') . "' ORDER BY date desc, checkin desc");
    else if ($_GET['range'] == "lastweek")
        $checkdata = mysql_query("SELECT * FROM checks WHERE user = '" . $username . "' AND week = '" . (uge()-1) . "' AND YEAR(date) = '" . date('Y') . "' ORDER BY date asc, checkin asc");
    else if ($_GET['range'] == "thismonth")
        $checkdata = mysql_query("SELECT * FROM checks WHERE user = '" . $username . "' AND MONTH(date) = '" . date('m') . "' AND YEAR(date) = '" . date('Y') . "' ORDER BY date asc, checkin asc");
    else if ($_GET['range'] == "3months")
        $checkdata = mysql_query("SELECT * FROM checks WHERE user = '" . $username . "' AND (MONTH(date) = '" . date('m') . "' OR MONTH(date) = '" . (date('m')-1) . "' OR MONTH(date) = '" . (date('m')-2) . "') AND YEAR(date) = '" . date('Y') . "' ORDER BY date asc, checkin asc");
    else if ($_GET['range'] == "6months")
        $checkdata = mysql_query("SELECT * FROM checks WHERE user = '" . $username . "' AND (MONTH(date) = '" . date('m') . "' OR MONTH(date) = '" . (date('m')-1) . "' OR MONTH(date) = '" . (date('m')-2) . "' OR MONTH(date) = '" . (date('m')-3) . "' OR MONTH(date) = '" . (date('m')-4) . "' OR MONTH(date) = '" . (date('m')-5) . "') AND YEAR(date) = '" . date('Y') . "' ORDER BY date asc, checkin asc");
    else if ($_GET['range'] == "thisyear")
        $checkdata = mysql_query("SELECT * FROM checks WHERE user = '" . $username . "' AND YEAR(date) = '" . date('Y') . "' ORDER BY date asc, checkin asc");
    else if ($_GET['range'] == "coming")
        $checkdata = mysql_query("SELECT * FROM checks WHERE user = '" . $username . "' AND date > '" . date('Y-m-d') . "' ORDER BY date asc, checkin asc");
    else if ($_GET['range'] == "all")
        $checkdata = mysql_query("SELECT * FROM checks WHERE user = '" . $username . "' ORDER BY date asc, checkin asc");
    else
        $checkdata = mysql_query("SELECT * FROM checks WHERE user = '" . $username . "' AND week = '" . uge() . "' AND YEAR(date) = '" . date('Y') . "' ORDER BY date desc, checkin desc");

	$checks = mysql_num_rows($checkdata);

    if ($checks > 0)
    {
?>

		<table id="checks">
			<tr>
				<th>Uge</th>
				<th>Dag</th>
				<th>Dato</th>
				<th>Check Ind</th>
				<th>Check Ud</th>
				<th>Pause</th>
				<th>Arbejdstid</th>
				<th>Kommentar</th>
				<th>Funktion</th>
			</tr>
			<?php 
        
		$RowNr = 1;
        while ($row = mysql_fetch_assoc($checkdata)) 
        {
			
            // Beregn arbejdstid fratrukket pausetid.
            $worktime = secondsToHours(hoursToSeconds($row['worktime']) - ($row['pause'] * 60));
            if ($row['checkin'] != "00:00:00" & $row['checkout'] == "00:00:00" & $row['date'] == date("Y-m-d") & $status == 1)
                echo "<tr class='thisCheck'>";
            else if ($row['comment'] == "Syg")
                echo "<tr bgcolor='#ff6666'>";
            else if ($row['comment'] == "Ferie" | $row['comment'] == "Fri")
                echo "<tr bgcolor='#77cc77'>";
            else if ($row['comment'] == "Skole")
                echo "<tr bgcolor='#3399ff'>";
            else if ($row['comment'] == "Afspadsering")
                echo "<tr bgcolor='#9966ff'>";
            else if ($row['comment'] == "Udstationering")
                echo "<tr bgcolor='#cc77dd'>";
            else
                echo "<tr>";
            echo "<td>" . $row['week'] . "</td>";
            echo "<td>" . $row['day'] . "</td>";
            $date = substr($row['date'], 8, 2) . "." . substr($row['date'], 5, 2) . "." . substr($row['date'], 2, 2);
            echo "<td>" . $date . "</td>";
            if ($row['comment'] == "Syg" | $row['comment'] == "Ferie" | $row['comment'] == "Fri" | $row['comment'] == "Skole" | $row['comment'] == "Afspadsering" | $row['comment'] == "Udstationering")
                echo "<td>-</td>";
            else
                echo "<td>" . $row['checkin'] . "</td>";
            if ($row['checkout'] == "00:00:00" | $row['comment'] == "Syg" | $row['comment'] == "Ferie" | $row['comment'] == "Fri" | $row['comment'] == "Skole" | $row['comment'] == "Afspadsering" | $row['comment'] == "Udstationering")
                echo "<td>-</td>";
            else
                echo "<td>" . $row['checkout'] . "</td>";				
            if ($row['comment'] == "Syg" | $row['comment'] == "Ferie" | $row['comment'] == "Fri" | $row['comment'] == "Skole" | $row['comment'] == "Afspadsering" | $row['comment'] == "Udstationering")
                echo "<td>-</td>";
            else
                echo "<td>" . $row['pause'] . " min</td>";
            if (($row['worktime'] == "00:00:00" & $status == 1) | $row['comment'] == "Syg" | $row['comment'] == "Ferie" | $row['comment'] == "Fri" | $row['comment'] == "Skole" | $row['comment'] == "Afspadsering" | $row['comment'] == "Udstationering")
                $worktime = "-";
            else
            {
                $worktime = hoursToSeconds($row['worktime']) - ($row['pause']*60);
                $worktime = secondsToHours($worktime);
            }
            echo "<td>" . $worktime . "</td>";
            echo "<td>" . $row['comment'] . "</td>";
            if ($row['comment'] == "Syg" | $row['comment'] == "Ferie" | $row['comment'] == "Fri" | $row['comment'] == "Skole" | $row['comment'] == "Afspadsering" | $row['comment'] == "Udstationering") // Vi kan kun slette forekomsten hvis den er særlig.
                echo "<td><a href='deleteEntry.php?id=" . $row['id'] . "'><img src='images/trash.png' height='18px' width='20px' /></a></td>";
            else if ($row['checkin'] != "00:00:00" & $row['checkout'] == "00:00:00" & $row['date'] == date("Y-m-d") & $status == 1) // Vi kan ikke slette forekomsten hvis den er aktiv. Kun ændre nogle værdier.
                echo "<td><a href='editEntry.php?id=" . $row['id'] . "'><img src='images/edit.png' height='18px' width='18px'/></a></td>";
            else
                echo "<td><a href='editEntry.php?id=" . $row['id'] . "'><img src='images/edit.png' height='18px' width='18px'/></a> <a href='deleteEntry.php?id=" . $row['id'] . "'><img src='images/trash.png' height='18px' width='20px' /></a></td>";
			echo "</tr>";
			$RowNr++;
        }
			?>
		</table>
		

		
		<h3>Farvebetydning</h3>
		<center><table>
			<tr>
				<td bgcolor="#ffffff" width="150px">Tidligere Check</td>
                <td bgcolor="#ffffaa" width="150px">Nuværende Check</td>
                <td bgcolor="#ff6666" width="150px">Syg</td>
                <td bgcolor="#77cc77" width="150px">Ferie / Fridag</td>
                <td bgcolor="#3399ff" width="150px">Skole</td>
                <td bgcolor="#9966ff" width="150px">Afspadsering</td>
                <td bgcolor="#cc77dd" width="150px">Udstationering</td>
			</tr>
		</table></center>
<?php 
    }
    else
    {
        echo "<br><center>Der er ingen forekomster.</center>";	
    }


    // Stop uret og udskriv tid.
    $end = (float) array_sum(explode(' ',microtime()));
    echo "<br><br><center><small>" . $checks . " poster læst på " . sprintf("%.4f", ($end-$start)) . " sek.</smaller></center>";

?>
<br><br><br>
</div>
<?php



closedb();
?>
	


	
	</body>
</html>