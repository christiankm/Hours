<?php 
	echo "<div id='topbar'>";
	echo "Uge " . uge() . " - " . dag() . " " . dato() . " " . tid() . " - " . $username . " &#8226; "; 
	echo "<a href='mailto:CKM@Bang-Olufsen.dk?subject=Feedback til Timer'><img src='images/feedback.png' /></a> &#8226; ";
	echo "<a href='overview.php'>Overblik</a> &#8226; ";
	echo "<a href='add.php'>Tilf&oslash;j</a> &#8226; ";
	echo "<a href='settings.php'>Indstillinger</a> &#8226; ";
	echo "<a href='logout.php'>Log ud</a>";
	echo "</div>";