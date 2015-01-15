<div id="statistics">

<h3>Statistik</h3>
<?php
  
  $now = tid();
  
  // Get checkdata rows from db
  $checkdata = mysql_query("SELECT * FROM checks WHERE user = '" . $username . "' ORDER BY id desc");
  $checks = mysql_num_rows($checkdata);
  
  // Worktime today
  while ($worktimeRow = mysql_fetch_assoc($checkdata))
  {
    if ($worktimeRow['date'] == date('Y-m-d'))
    {
      if ($worktimeRow['checkin'] != "00:00:00" && $worktimeRow['checkout'] == "00:00:00" && $status == 1)
      {
        $WorktimeTodaysecs = $WorktimeTodaysecs + (hoursToSeconds($now) - hoursToSeconds($worktimeRow['checkin']));
      }
      else
      {
        $WorktimeTodaysecs = $WorktimeTodaysecs + (hoursToSeconds($worktimeRow['worktime']));
      }
      $pauseTodayS = $pauseTodayS + ($worktimeRow['pause'] * 60);
    }
  }
  $WorktimeTodayS = $WorktimeTodaysecs - $pauseTodayS;
  echo "Arbejdstid idag (ekskl. pause): " . secondsToLong($WorktimeTodayS) . "<br>";
  
  // Worktime for the rest of the day
  $query = mysql_query("SELECT * FROM users WHERE username = '" . $username . "'");
  $exp = mysql_fetch_assoc($query);
  if (dag() == "Mandag")
  {
    $expBeginTime = $exp['expMondayFrom'];
    $expEndTime = $exp['expMondayTo'];
  }
  else if (dag() == "Tirsdag")
  {
    $expBeginTime = $exp['expTuesdayFrom'];
    $expEndTime = $exp['expTuesdayTo'];
  }
  else if (dag() == "Onsdag")
  {
    $expBeginTime = $exp['expWednesdayFrom'];
    $expEndTime = $exp['expWednesdayTo'];
  }
  else if (dag() == "Torsdag")
  {
    $expBeginTime = $exp['expThursdayFrom'];
    $expEndTime = $exp['expThursdayTo'];
  }
  else if (dag() == "Fredag")
  {
    $expBeginTime = $exp['expFridayFrom'];
    $expEndTime = $exp['expFridayTo'];
  }
  else
  {
    $expBeginTime = "00:00:00";
    $expEndTime = "00:00:00";
  }
  $expWorktimesecs = (hoursToSeconds($expEndTime) - hoursToSeconds($expBeginTime));
  $expWorktime = secondsToHours($expWorktimesecs);
  $ExpectedWorktimeLeft = ($expWorktimesecs - $WorktimeTodaysecs);
  $ExpectedWorktimeEnd = hoursToSeconds($now) + $ExpectedWorktimeLeft;
  if ($expEndTime != "00:00:00")
  {
    if ($ExpectedWorktimeLeft > 0)
    {
      echo "Forventet tid tilbage (inkl. pause): " . secondsToLong($ExpectedWorktimeLeft) . " (kl. " . secondsToHours($ExpectedWorktimeEnd) . ")<br>";
    }
    else
    {
      echo "<font color='green'>Din forventede arbejdstid er nået.</font><br>";
    }
  }
  else
  echo "<font color='darkred'><b>For at se hvornår du opnår din forventede arbejdstid for idag, ret dine indstillinger.</b></font><br>";
  
  // Worktime during the week
  $sql_worktime = mysql_query("SELECT * FROM checks WHERE user = '" . $username . "' AND week = '" . uge() . "' AND YEAR(date) = '" . date('Y') . "' ORDER BY date desc");
  $WorktimeThisWeek = 0;
  while ($row = mysql_fetch_assoc($sql_worktime))
  {
    if ($row['worktime'] == date('00:00:00') & $status == 1)
    {
      $WorktimeThisWeek = (hoursToSeconds(tid()) - hoursToSeconds($row['checkin'])) - ($row['pause'] * 60);
    }
    else
    {
      $WorktimeThisWeek = $WorktimeThisWeek + (hoursToSeconds($row['worktime']) - ($row['pause'] * 60));
    }
  }
  
  // Hvis mere end 37 timer, vises teksten med grøn for at signalere overskud.
  if ($WorktimeThisWeek >= 133200)
  {
    $diff = secondsToHours($WorktimeThisWeek - 133200);
    echo "Arbejdstid i denne uge: " . secondsToHours($WorktimeThisWeek) . " (<font color='green'>+" . $diff . "</font>)<br>";
  }
  else
  {
    $diff = secondsToHours(133200 - $WorktimeThisWeek);
    echo "Arbejdstid i denne uge: " . secondsToHours($WorktimeThisWeek) . " (-" . $diff . ")<br>";
  }

  // Calculate balance
  $DatesCalculated = array();
  $startbalance = mysql_fetch_assoc(mysql_query("SELECT balance FROM users WHERE username = '" . $username . "'"));
  $balance = $startbalance['balance'];
  $sql = mysql_query("SELECT date,day,checkin,worktime,pause,comment FROM checks WHERE user = '" . $username . "'");
  while ($result = mysql_fetch_assoc($sql))
  {
    $worktimeThisDay = 0; // Reset variable
    $entriesThisDay = mysql_num_rows(mysql_query("SELECT date FROM checks WHERE user = '" . $username . "' AND date = '" . $result['date'] . "'"));
    
    // Sorter efter kommentar. Spring disse dage over da de ikke påvirker balancen.
    if ($result['comment'] == "Skole" || $result['comment'] == "Fri" || $result['comment'] == "Ferie" || $result['comment'] == "Syg" || $result['comment'] == "Udstationering")
    {
      continue;
    }
    else if ($result['comment'] == "Afspadsering")
    {
      // Ved afspadsering trækker vi en hel feriedag (7t 24m) fra balancen.
      $balance = $balance - 26640;
      continue;
    }
    // Hvis lørdag eller søndag, betegnes det som overarbejde, og balancen kan kun stige.
    else if ($result['day'] == "Lørdag" | $result['day'] == "Søndag")
    {
      if ($result['date'] == date("Y-m-d") && $result['worktime'] == date('00:00:00') && $status == 1)
      {
        $worktime = hoursToSeconds(tid()) - hoursToSeconds($result['checkin']) - ($result['pause'] * 60);
      }
      else
      {
        $worktime = hoursToSeconds($result['worktime']) - ($result['pause'] * 60);
      }
      $balance += $worktime;
      continue;
    }
    
    // Dage med 2 eller flere checks
    if ($entriesThisDay > 1 && in_array($result['date'], $DatesCalculated) == false)
    {
      $daysql = mysql_query("SELECT date,checkin,worktime,pause FROM checks WHERE user = '" . $username . "' AND date = '" . $result['date'] . "' ORDER BY date desc");
      while ($day = mysql_fetch_assoc($daysql))
      {
        if ($result['date'] == date("Y-m-d") && $day['worktime'] == date('00:00:00') && $status == 1)
        {
          $worktimeThisCheck = hoursToSeconds(tid()) - hoursToSeconds($day['checkin']) - ($day['pause'] * 60);
        }
        else
        {
          $worktimeThisCheck = hoursToSeconds($day['worktime']) - ($day['pause'] * 60);
        }
        $worktimeThisDay += $worktimeThisCheck;
      }
      $balanceToday = ($worktimeThisDay - 26640);
      $balance += $balanceToday;
      array_push($DatesCalculated, $result['date']);
    }
    else if ($entriesThisDay == 1)
    {
      if ($result['date'] == date("Y-m-d") && $result['worktime'] == date('00:00:00') && $status == 1)
      {
        $worktimeThisDay = hoursToSeconds(tid()) - hoursToSeconds($result['checkin']) - ($result['pause'] * 60);
      }
      else
      {
        $worktimeThisDay = hoursToSeconds($result['worktime']) - ($result['pause'] * 60);
      }
      
      $balanceToday = ($worktimeThisDay - 26640);
      $balance += $balanceToday;
    }
  }
  
  // Formater balance
  if ($balance >= 26640)
  {
    $feriedage = mround($balance / 26640, 0);
    $balance -= $feriedage * 26640;
  }
  
  if ($balance < 0)
  {
    $image = "negative";
    $color = "red";
  }
  else
  {
    $image = "positive";
    $color = "green";
  }
  
  $balance = str_replace("-", "", secondsToLong($balance));
  
  if ($feriedage == 1)
  echo "<br><b>Balance: <img src='images/" . $image . ".png' /> <font color='" . $color . "'>" . $feriedage . " feriedag, " . $balance . "</b></font>";
  else if ($feriedage > 1)
  echo "<br><b>Balance: <img src='images/" . $image . ".png' /> <font color='" . $color . "'>" . $feriedage . " feriedage, " . $balance . "</b></font>";
  else
  echo "<br><b>Balance: <img src='images/" . $image . ".png' /> <font color='" . $color . "'>" . $balance . "</b></font>";
  
  echo "<br><i>Første gang du checker ind på en dag, trækkes 7t 24m fra din balance.<br>Dette svarer til en arbejdsdag, og bliver genoptjent indtil du tjekker ud.</i>";
  
  ?>

</div>