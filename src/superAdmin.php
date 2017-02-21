<?php

	$title = 'Super Admin';
  	$active = 'superAdmin';

  	include $_SERVER['DOCUMENT_ROOT'].'/classScheduler/navScripts.php';
  	include $_SERVER['DOCUMENT_ROOT'].'/classScheduler/connection.php';
  	$dept = $_SESSION['dept'];

  	if ($_SESSION['username']!="wahlst" && $_SESSION['username']!="midkiffj") {
  		header("Location: options.php");
  	}

  	$error = array();
  	$errorSql = array();
  	if (isset($_GET['initials']) && isset($_GET['name']) && isset($_GET['position']) && isset($_GET['dept']) && isset($_GET['begin']) && isset($_GET['end'])) {
  		$name = $dbcon->real_escape_string($_GET['name']);
  		$initials = $dbcon->real_escape_string($_GET['initials']);
  		$department = $dbcon->real_escape_string($_GET['dept']);
  		$begin = $_GET['begin'];
  		$end = $_GET['end'];
  		$pref = 0;
  		if (isset($_GET['pref'])) {
  			$pref = 1;
  		}
  		$staff = $_GET['position'];
     	$fac = 0;
     	$va = 0;
     	if ($staff == 'f') {
     		$fac = 1;
     	} else {
     		$va = 1;
     	}
	    $sql = "insert into dept_options values('$department',$begin,$end,$pref)";
    	$result=$dbcon->query($sql);
    	if (!$result) {
	      $error[] = $dbcon->error;
	      $errorSql[] = $sql;
	    }
	    $sql = "insert into user values('$name','$department','$initials',$fac,$va,1,1)";
	    $result=$dbcon->query($sql);
    	if (!$result) {
	      $error[] = $dbcon->error;
	      $errorSql[] = $sql;
	    }
  	}


	echo '<div class="container">';

	if (count($error) > 0) {
		include $_SERVER['DOCUMENT_ROOT'].'/classScheduler/error.php';
	}
?>
	<h1 class="text-center">Super Admin</h1>
	<hr>
	<h3 class="text-center" style="margin-top: 10px;"><u>Add New Department</u></h3>
	<div class="row" style="margin-top: 20px;">
		<div class="col-md-8 col-md-offset-2">
			<form method="GET" action="superAdmin.php">
				<div class="col-md-6">
					<legend>Department Admin</legend>
					<div class="form-group">
						<label for="name">Instructor Name:</label>
					  	<input class="form-control" type="text" id="name" name="name" required>
				    </div>
				    <div class="form-group">
						<label for="initials">Gateway Username:</label>
					 	 <input class="form-control" type="text" id="initials" name="initials" maxlength="15" required>
				    </div>
				    <div class="radio">
					  	<label>
							<input type="radio" id="faculty" name="position" value="f">
								Faculty
						  	</label>
					  	<label>
							<input type="radio" id="visit_adj" name="position" value="v">
								Visiting/Adjunct
						  	</label>
			    	</div>
		    	</div>
				<div class="col-md-6">
					<legend>Department Info</legend>
					<div class="form-group">
						<label for="name">Department Abbreviation:</label>
					  	<input class="form-control" type="text" id="dept" name="dept" max-length="5" required>
				    </div>
				    <div class="form-group">
		          		<!-- <h4><u>Academic Year:</u></h4> -->
		          		<label>Academic Year:</label>
		          		<div class="row">
		          			<div class="col-md-6">
		          				<label for="begin">From:</label>
	          					<input class="form-control" type="number" id="begin" name="begin" min="2000" max="9999" value="2016" required>
	          				</div>
	          				<div class="col-md-6">
	          					<label for="end">To:</label>
	          					 <input class="form-control" type="number" id="end" name="end" min="2000" max="9999" value="2017" required>
	          				</div>
	          			</div>
  					</div>	
  					<label for="pref">Preferences:</label>
		          	<div class="checkbox">
					  	<label>
							<input type="checkbox" id="pref" name="pref">
								Enable Instructor Preferences
					  	</label>
				  	</div>
				</div>
				<div>
					<button class="btn btn-lg btn-primary center-block" type="submit">Add Department</button>
				</div>
			</form>
		</div>
	</div>
	<!-- <hr>
	<h3 class="text-center">Add New 'Super' Admin</h3> -->
	
</div>
</body>

</html>