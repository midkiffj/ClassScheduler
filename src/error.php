<?php
	echo '<div class="alert alert-danger alert-dismissible text-center" role="alert" style="margin:auto; max-width: 50%;">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  				<strong>Warning</strong>--Database query failed!';
  	echo '<br>This error has been logged and an administrator has been notified.';
	echo '</div>';

	// $file = $_SERVER['DOCUMENT_ROOT'].'/classScheduler/errorLog.txt';
	$file = '/tmp/errorLog.txt';
	$handle = fopen($file, "a");

	$errorLog = "";
	$date = date_create();
	$errorLog .= "Date: ".date_format($date, 'm-d-Y H:i:s');
	$errorLog .= "\nUser: ".$_SESSION['username'];
	$errorLog .= "\nDept: ".$_SESSION['dept'];
	$errorLog .= "\nError(s): ";
	for ($i = 0; $i < count($error); $i++) {
		$errorLog .= "\n\t$i: $error[$i]";
	}
  	$errorLog .= "\nQuery(s): "; 
  	for ($i = 0; $i < count($errorSql); $i++) {
		$errorLog .= "\n\t$i: $errorSql[$i]";
	}
	$errorLog .= "\n\n";
	fwrite($handle, $errorLog) or die('Error log write failed');
	
	//close the file
	fclose($handle);
?>