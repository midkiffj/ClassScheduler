<?PHP
	ini_set("display_errors", 1);
	error_reporting(E_ALL);
	session_start();

	$title = 'Login';
	$active = 'login';

	// include $_SERVER['DOCUMENT_ROOT'].'/classScheduler/navScripts.php';

	$dcCASURL = "https://auth.dickinson.edu/cas/";
	$dcServicePage = "https://" . $_SERVER['HTTP_HOST'] . "/classScheduler/login.php";
	// echo $dcServicePage
	// $dcServicePage = "https://farmdatadev.dickinson.edu/login.php";

	// Are we trying to validate a service ticket?
	if(isset($_GET['ticket'])) {
		$ch = curl_init($dcCASURL . "serviceValidate?service=" . urlencode($dcServicePage) . "&ticket=" . $_GET['ticket']);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		$dcdata = curl_exec($ch);

		// Check for errors and display the error message
		if($errno = curl_errno($ch)) {
		    // $error_message = curl_strerror($errno);
		    die("<br>cURL error ({$errno})<br>Contact an administrator");
		}
		curl_close($ch);

		if(strpos($dcdata, "<cas:authenticationSuccess>") > 0) {
			preg_match_all("/<cas:user>(.*)<\/cas:user>/", $dcdata, $dcuser);
			$_SESSION['username'] = $dcuser[1][0];
			//$_SESSION['dbuser'] = 'dev';
			//$_SESSION['dbpass'] = 'foofoo';
			//$_SESSION['db'] = 'dfarm';
			//$_SESSION['bigfarm'] = 0;
		} else {
			echo "CAS ticket validation failed. Please try again.";
			$url = "https://".$_SERVER['HTTP_HOST']."/classScheduler/";
			echo '<form method="POST" action="'.$url.'">';
			echo '<input type ="submit" class="submitbutton" value = "Log In Again">';
			echo '</form>';
			die();
		}
	}
	$con=new mysqli("localhost","peeping","tom","span_scheduler");
					//check connection
	if ($con->connect_errno) {
	        echo "Failed to connect to MySQL: " . $con->connect_error;
	}
	if (isset($_SESSION['username'])) {
		$sql= "Select exists(select * from user where initials='".$_SESSION['username']."' and active = 1) as isUser";
		$check=$con->query($sql);
	    while ($users=$check->fetch_assoc()) {
	        if ($users['isUser']==0) {
				session_destroy();
				echo "Access Denied. You are not authorized to use the scheduler!";
				$url = "https://".$_SERVER['HTTP_HOST']."/classScheduler/logout.php";
				echo '<form method="POST" action="'.$url.'">';
				echo '<input type ="submit" class="submitbutton" value = "Logout">';
				echo '</form>';
				die();
			}
			// else {
	  //   		$sql= "Select exists(select * from user where initials='".$_SESSION['username']."' and admin=1) as isAdmin";
	  //           $adminCheck=$con->query($sql);
	  //   		while ($admin=$adminCheck->fetch_assoc()) {
	  //     			if ($admin['isAdmin']==0) {
		 //      			 	$_SESSION['admin']=0;
			// 		}else {
	  //   				$_SESSION['admin']=1;
			// 		}	
			// 	}
			// }
		}
	}
	// If we are not logged in, redirect to CAS
	if(!isset($_SESSION['username'])) {
		header("Location: " . $dcCASURL . "login?service=" . urlencode($dcServicePage));
		exit();
	} else {
		header("Location: options.php");
	}
?>
