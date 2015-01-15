<?php
include "functions.php";

if($_POST['submit'])
{
	$username = strtoupper($_POST['username']);
	$password = md5($_POST['password']);
	$passwordRepeat = md5($_POST['passwordRepeat']);
	//echo "Checking input...<br />";
	opendb();
	if ($username == "" | $_POST['password'] == "" | $_POST['passwordRepeat'] == "") // Fields are empty
		$errorMess = "Du skal udfylde alle felter.";
	$query = mysql_query("SELECT * FROM users WHERE username = '" . $username . "'");
	$count = mysql_num_rows($query);
	if ($count != 0) // Username exists
		$errorMess = "Brugeren findes allerede.";
	if (strlen($username) != 3) // Username is not 3 chars
		$errorMess = "Initialer skal være på 3 tegn.";
	if ($password != $passwordRepeat) // Passwords do not match
		$errorMess = "Passwords er ikke ens.";
	if (!$errorMess) // If no errors, we create the user.
	{
		//echo "Input is OK<br />";
		//echo "Creating user...<br />";
		mysql_query("INSERT INTO users (username, password) VALUES ('" . $username . "','" . $password . "')");
		//echo "User created<br />";
		session_start();
		$_SESSION['user'] = $username;
		header("Refresh: 1; overview.php");
		echo "<br /><br /><center><p>Du blev oprettet!</p>";
		echo "<p>Bliver du ikke ført videre efter 2 sekunder, så <a href='overview.php'>tryk her</a></p></center>";
		exit;
	}
	else
		echo "<p class='error'>" . $errorMess . "</p>";
}

?>



<div id="topbar"><a href="index.php"> < Tilbage </a></div>

	<h3>Opret bruger</h3>

	<form method="post" action="newUser.php">
		Initialer: <input type="text" name="username" value="<?php echo $username ?>" /><br />
		Password: <input type="password" name="password" /><br />
		Gentag Password: <input type="password" name="passwordRepeat" /><br />
		<input type="submit" name="submit" value="Opret" />
	</form>
</body>

</html>