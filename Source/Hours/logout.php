<?php

session_start();
session_destroy(); 
header("Refresh: 2; index.php");

?>

<html>
<head>
<link rel="stylesheet" href="style.css">
</head>
<body>
<br /><br /><br /><br />
<center>
<h2>Du er nu logget ud.</h2>
<p><a href="index.php">Log ind igen.</a>
</center>
</body>
</html>