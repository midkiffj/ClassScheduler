<?php
	session_start();
	if((!isset($_SESSION['username'])) || (!isset($_SESSION['dbUser'])) || (!isset($_SESSION['dbPass']))) {
		header("Location: login.php");
	}
	$host = "localhost";
	// $user = $_SESSION['dbuser'];
	// $pass = $_SESSION['dbpass'];
	// $dbName = $_SESSION['db'];
	// $user = $_SESSION['dbUser'];
	$user = $_SESSION['dbUser'];
	$pass = $_SESSION['dbPass'];
	$dbName = "span_scheduler";
	$dbcon = new mysqli($host, $user, $pass, $dbName);
	if ($dbcon->connect_errno) {
		$host = $_SERVER['HTTP_HOST']."";
	  	echo '<div class="container">
	  			<div class="alert alert-danger alert-dismissible text-center" role="alert" style="margin:auto; max-width:50%;">
	  				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	  				<strong>ERROR!</strong> Database connection failed. 
	  				<form method="GET" action="https://'.$host = $_SERVER['HTTP_HOST'].'/classScheduler/">
	  					<input type="submit" class="" value="Try re-logging into the system!">
	  				</form>
				</div>
			  </div>';
		die();
	}
	// } else {
	//   // $host = gethostname();
	//   $host = $_SERVER['HTTP_HOST']."";
	//   echo '<link type="text/css" href="/design.css" rel = "stylesheet">';
	//   echo  ("Connection Failed !! : ".mysql_error());
	//   echo '<br clear="all"/><br clear="all"/>';
	// $method = "http";
	// // HTTPSON
	// $method = "https";
	// // HTTPSOFF

	//   echo '<form method="POST" action="'.$method.'://'.$host.'"/>';
	//   echo '<input type ="submit" class="submitbutton" value = "Log In Again">';
	//   echo '</form>';
	//   die();
	// }
?>
