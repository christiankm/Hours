<?php
  
  session_start();
  error_reporting(E_ALL);
  ini_set('display_errors', '0');
  
  require "functions.php";
  
  opendb();
  
  if ($_POST['login'])
  {
    $username = strtoupper(htmlspecialchars(stripslashes(mysql_real_escape_string($_POST['username']))));
    $password = md5(htmlspecialchars(stripslashes(mysql_real_escape_string($_POST['password']))));
    $query = mysql_query("SELECT * FROM users WHERE username = '" . $username . "'");
    $users = mysql_num_rows($query);
    $query = mysql_query("SELECT * FROM users WHERE username = '" . $username . "' AND password = '" . $password . "'");
    $matches = mysql_num_rows($query);
    
    if ($username == "" | $_POST['password'] == "") // Fields not filled
    {
      $error = "<p class='error'>Begge felter skal udfyldes.</p>";
    }
    else if ($users == 0) // User does not exist
    {
      $error = "<p class='error'>Brugeren findes ikke.</p>";
    }
    else if ($matches != 1) // Password is not correct for this user
    {
      $error = "<p class='error'>Password er ikke korrekt.</p>";
    }
    else // Log user in
    {
      $_SESSION['user'] = $username;
      header("Location: overview.php");
      exit();
    }
  }
?>

<html>
<head>
<title>
Timer
</title>
<link rel="stylesheet" href="style.css" type="text/css" />
</head>

<body>

<div id="topbar">
<a href='mailto:CKM@Bang-Olufsen.dk?subject=Feedback til Timer'><img src='images/feedback.png' /></a> &#8226;
<a href='index.php'>Login</a> &#8226;
<a href='about.php'>Om Timer</a> &#8226;
<a href='mailto:CKM@Bang-Olufsen.dk'>Kontakt</a>
</div>
<center>
<br /><br />
<img src="images/clock.png" />
<!--<p><i>Af Christian Mitteldorf</i></p>-->
<br /><br /><br />
<!--<h2>Login</h2>-->
<table id="login">
<form method="post" action="index.php">
<tr>
<td colspan="2"><?php echo $error; ?></td>
</tr>
<tr>
<th>Initialer</th>
<td><input type="text" name="username" value="" /></td>
</tr>
<tr>
<th>Password</th>
<td><input type="password" name="password" value="" /></td>
</tr>
<tr>
<td></td>
<td colspan="2"><input type="submit" name="login" value="Login" /></td>
</tr>
</form>
<tr>
<td></td>
<td><small><a href="newUser.php">Opret ny bruger</a></small><br />
<!--<small><a href="forgotPass.php">Glemt kode</a>--></small></td>
</tr>
</table>
</center>
</body>
</html>
