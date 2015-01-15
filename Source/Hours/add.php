<?php
session_start();
require "functions.php";
$username = checkSession();
opendb();
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
	?>

	<h3>Tilføj</h3>
	
	<?php	
	if (isset($_POST['add_event']) | isset($_POST['add_check']))
	{		
		$dato = date("Y-m-d", strtotime($_POST['year'] . "-" . $_POST['month'] . "-" . $_POST['day']));
		$checkDate = mysql_num_rows(mysql_query("SELECT * FROM checks WHERE user = '" . $username . "' AND date = '" . $dato . "'"));
		/*if ($checkDate > 0)
		{	
			header("Refresh: 2; overview.php");
			echo "<center><p><font color='red'><b>Der er allerede en begivenhed på denne dato: " . $_POST['day'] . "." . $_POST['month'] . "." . $_POST['year'] . ".<br>Begivenheden blev ikke oprettet!</b></font></p>";
			echo "<p><a onClick='history.go(-1);return true;'>< Tilbage</a></p></center>";
			exit();
		}*/

		if (isset($_POST['add_event']))
		{			
			$startTime = strtotime($_POST['year'] . "-" . $_POST['month'] . "-" . $_POST['day']);
			$endTime = strtotime($_POST['til_year'] . "-" . $_POST['til_month'] . "-" . $_POST['til_day']);
			
			if ($endTime < $startTime)
			{
				header("Refresh: 2; overview.php");
				echo "<center><p><font color='red'><b>Slutdatoen må ikke være tidligere end startdatoen!</b></font></p>";
				echo "<p><a onClick='history.go(-1);return true;'>< Tilbage</a></p></center>";
				exit();
			}

			if ($startTime == $endTime)
			{
				$week = (int)date("W", $startTime);
				$dag = (int)date("N", $startTime);
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
				$thisDate = date('Y-m-d', $startTime);
				mysql_query("INSERT INTO checks (`user`, `week`, `day`, `date`, `comment`) VALUES ('" . $username . "','" . $week . "','" . $dag . "', '" . $thisDate . "','" . $_POST['comment'] . "')");
			}
			else
			{
				for($time = $startTime; $time <= $endTime; $time = strtotime('+1 day', $time))
				{
					$week = (int)date("W", $time);
					$dag = (int)date("N", $time);
					if ($dag != 6 & $dag != 7)
					{
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
					}
					else
					{
						continue;
					}
					$thisDate = date('Y-m-d', $time);
					mysql_query("INSERT INTO checks (`user`, `week`, `day`, `date`, `comment`) VALUES ('" . $username . "','" . $week . "','" . $dag . "', '" . $thisDate . "','" . $_POST['comment'] . "')");
				}
			}
		}
		else if (isset($_POST['add_check']))
		{
			$checkin = strtotime($_POST['checkin']);
			$checkout = strtotime($_POST['checkout']);
			if ($checkout < $checkin)
			{
				header("Refresh: 2; overview.php");
				echo "<center><p><font color='red'><b>Check Ud må ikke være tidligere end Check Ind!</b></font></p>";
				echo "<p><a onClick='history.go(-1);return true;'>< Tilbage</a></p></center>";
				exit();
			}
			$dato = strtotime($_POST['year'] . "-" . $_POST['month'] . "-" . $_POST['day']);
			$thisDate = date('Y-m-d', $dato);
			$week = (int)date("W", $dato);
			$dag = (int)date("N", $dato);
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
			$worktime = date("H:i:s",($checkout - $checkin));
			mysql_query("INSERT INTO checks (`user`, `week`, `day`, `date`, `checkin`, `checkout`, `pause`, `worktime`, `comment`) VALUES ('" . $username . "','" . $week . "','" . $dag . "','" . $thisDate . "','" . $_POST['checkin'] . "','" . $_POST['checkout'] . "','" . $_POST['pause'] . "','" . $worktime . "','" . $_POST['comment'] . "')") or die(mysql_error());
		}
		closedb();
		header("Refresh: 2; overview.php");
		echo "<center><p><b>Tilføjet.</b></p>";
		echo "<p><a href='overview.php'>< Tilbage</a></p></center>";
		exit();
	}

	?>
	
	<center>

	<p><b>Begivenhed</b></p>
	<form action="add.php" method="POST">
	<table id="add">
		<tr>
			<th>Type</th>
				<td>
					<select name="comment">
						<option value="Fri">Fri</option>
						<option value="Ferie">Ferie</option>
						<option value="Skole">Skole</option>
						<option value="Syg">Syg</option>
						<option value="Afspadsering">Afspadsering</option>
						<option value="Udstationering">Udstationering</option>
					</select>
				</td>
			<th>Fra</th>
				<td>
					<select name="day">
						<option value="01">1</option>
						<option value="02">2</option>
						<option value="03">3</option>
						<option value="04">4</option>
						<option value="05">5</option>
						<option value="06">6</option>
						<option value="07">7</option>
						<option value="08">8</option>
						<option value="09">9</option>
						<option value="10">10</option>
						<option value="11">11</option>
						<option value="12">12</option>
						<option value="13">13</option>
						<option value="14">14</option>
						<option value="15">15</option>
						<option value="16">16</option>
						<option value="17">17</option>
						<option value="18">18</option>
						<option value="19">19</option>
						<option value="20">20</option>
						<option value="21">21</option>
						<option value="22">22</option>
						<option value="23">23</option>
						<option value="24">24</option>
						<option value="25">25</option>
						<option value="26">26</option>
						<option value="27">27</option>
						<option value="28">28</option>
						<option value="29">29</option>
						<option value="30">30</option>
						<option value="31">31</option>
					</select>
					/
					<select name="month">
						<option value="01">1</option>
						<option value="02">2</option>
						<option value="03">3</option>
						<option value="04">4</option>
						<option value="05">5</option>
						<option value="06">6</option>
						<option value="07">7</option>
						<option value="08">8</option>
						<option value="09">9</option>
						<option value="10">10</option>
						<option value="11">11</option>
						<option value="12">12</option>
					</select>
					 
					<select name="year">
						<option value="2012">2012</option>
						<option value="2013">2013</option>
						<option value="2014">2014</option>
						<option value="2015">2015</option>
						<option value="2016">2016</option>
						<option value="2017">2017</option>
						<option value="2018">2018</option>
						<option value="2019">2019</option>
						<option value="2020">2020</option>
						<option value="2021">2021</option>
						<option value="2022">2022</option>
						<option value="2023">2023</option>
						<option value="2024">2024</option>
						<option value="2025">2025</option>
						<option value="2026">2026</option>
						<option value="2027">2027</option>
						<option value="2028">2028</option>
						<option value="2029">2029</option>
						<option value="2030">2030</option>
					</select>
				</td>
			<th>Til</th>
				<td>
					<select name="til_day">
						<option value="01">1</option>
						<option value="02">2</option>
						<option value="03">3</option>
						<option value="04">4</option>
						<option value="05">5</option>
						<option value="06">6</option>
						<option value="07">7</option>
						<option value="08">8</option>
						<option value="09">9</option>
						<option value="10">10</option>
						<option value="11">11</option>
						<option value="12">12</option>
						<option value="13">13</option>
						<option value="14">14</option>
						<option value="15">15</option>
						<option value="16">16</option>
						<option value="17">17</option>
						<option value="18">18</option>
						<option value="19">19</option>
						<option value="20">20</option>
						<option value="21">21</option>
						<option value="22">22</option>
						<option value="23">23</option>
						<option value="24">24</option>
						<option value="25">25</option>
						<option value="26">26</option>
						<option value="27">27</option>
						<option value="28">28</option>
						<option value="29">29</option>
						<option value="30">30</option>
						<option value="31">31</option>
					</select>
					/
					<select name="til_month">
						<option value="01">1</option>
						<option value="02">2</option>
						<option value="03">3</option>
						<option value="04">4</option>
						<option value="05">5</option>
						<option value="06">6</option>
						<option value="07">7</option>
						<option value="08">8</option>
						<option value="09">9</option>
						<option value="10">10</option>
						<option value="11">11</option>
						<option value="12">12</option>
					</select>
					 
					<select name="til_year">
						<option value="2012">2012</option>
						<option value="2013">2013</option>
						<option value="2014">2014</option>
						<option value="2015">2015</option>
						<option value="2016">2016</option>
						<option value="2017">2017</option>
						<option value="2018">2018</option>
						<option value="2019">2019</option>
						<option value="2020">2020</option>
						<option value="2021">2021</option>
						<option value="2022">2022</option>
						<option value="2023">2023</option>
						<option value="2024">2024</option>
						<option value="2025">2025</option>
						<option value="2026">2026</option>
						<option value="2027">2027</option>
						<option value="2028">2028</option>
						<option value="2029">2029</option>
						<option value="2030">2030</option>
					</select>
				</td>
		</tr>
	</table>
	<input type="submit" name="add_event" value="Tilføj" />
	<a href="overview.php"><input type="submit" value="Annullér" /></a>
	</table>
	</form><br><br><br><br>
	
	<p><b>Check</b></p>
	<form action="add.php" method="POST">
	<table id="add">
		<tr>
			<th>Dato</th>
				<td>
					<select name="day">
						<option value="01">1</option>
						<option value="02">2</option>
						<option value="03">3</option>
						<option value="04">4</option>
						<option value="05">5</option>
						<option value="06">6</option>
						<option value="07">7</option>
						<option value="08">8</option>
						<option value="09">9</option>
						<option value="10">10</option>
						<option value="11">11</option>
						<option value="12">12</option>
						<option value="13">13</option>
						<option value="14">14</option>
						<option value="15">15</option>
						<option value="16">16</option>
						<option value="17">17</option>
						<option value="18">18</option>
						<option value="19">19</option>
						<option value="20">20</option>
						<option value="21">21</option>
						<option value="22">22</option>
						<option value="23">23</option>
						<option value="24">24</option>
						<option value="25">25</option>
						<option value="26">26</option>
						<option value="27">27</option>
						<option value="28">28</option>
						<option value="29">29</option>
						<option value="30">30</option>
						<option value="31">31</option>
					</select>
					/
					<select name="month">
						<option value="01">1</option>
						<option value="02">2</option>
						<option value="03">3</option>
						<option value="04">4</option>
						<option value="05">5</option>
						<option value="06">6</option>
						<option value="07">7</option>
						<option value="08">8</option>
						<option value="09">9</option>
						<option value="10">10</option>
						<option value="11">11</option>
						<option value="12">12</option>
					</select>
					 
					<select name="year">
						<option value="2012">2012</option>
						<option value="2013">2013</option>
						<option value="2014">2014</option>
						<option value="2015">2015</option>
						<option value="2016">2016</option>
						<option value="2017">2017</option>
						<option value="2018">2018</option>
						<option value="2019">2019</option>
						<option value="2020">2020</option>
						<option value="2021">2021</option>
						<option value="2022">2022</option>
						<option value="2023">2023</option>
						<option value="2024">2024</option>
						<option value="2025">2025</option>
						<option value="2026">2026</option>
						<option value="2027">2027</option>
						<option value="2028">2028</option>
						<option value="2029">2029</option>
						<option value="2030">2030</option>
					</select>
				</td>
			<th>Check Ind</th>
				<td><input type="text" size="8" name="checkin" value="00:00:00"></td>
			<th>Check Ud</th>
				<td><input type="text" size="8" name="checkout" value="00:00:00"></td>
			<th>Pause</th>
				<td><input type="text" size="5" name="pause" value="0"> min.</td>
			<th>Kommentar</th>
				<td><input type="text" name="comment"></td>
		</tr>
	</table>
	<input type="submit" name="add_check" value="Tilføj" />
	<a href="overview.php"><input type="submit" value="Annullér" /></a>
	</form>
	
	</center>
	</body>
</html>