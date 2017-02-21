	<?php

	$title = 'Admin - Department';
  	$active = 'adminDepartment';

  	include $_SERVER['DOCUMENT_ROOT'].'/classScheduler/navScripts.php';
  	include $_SERVER['DOCUMENT_ROOT'].'/classScheduler/connection.php';
  	$dept = $_SESSION['dept'];

  	if ($_SESSION['admin']!=1) {
  		header("Location: options.php");
  	}

  	$error = array();
  	$errorSql = array();
  	$initDoub = null;
  	$facPrefs = null;


  	if (isset($_GET['name']) && isset($_GET['initials']) && isset($_GET['position']) && isset($_GET['user'])) {
  		$user = $dbcon->real_escape_string($_GET['user']);
  		$name = $dbcon->real_escape_string($_GET['name']);
  		$initials = $dbcon->real_escape_string($_GET['initials']);
     	$staff = $_GET['position'];
     	$fac = 0;
     	$va = 0;
     	if ($staff == 'f') {
     		$fac = 1;
     	} else {
     		$va = 1;
     	}
     	if ($va == 1) {
     		$res = $dbcon->query("select faculty from user where initials='$initials' and dept='$dept'");
	     	if ($row = $res->fetch_assoc()) {
	     		if ($row['faculty'] == 1) {
	     			$facPrefs = 1;
	     		}
	     	}
	    }
	    $sql = "update user set name='$name', initials='$initials', faculty=$fac, visit_adj=$va where initials='$user'";
	    $result=$dbcon->query($sql);
    	if (!$result) {
		  $error[] = $dbcon->error;
		  $errorSql[] = $sql;
		}
  	} elseif (isset($_GET['name']) && isset($_GET['initials']) && isset($_GET['position'])) {
		$name = $dbcon->real_escape_string($_GET['name']);
  		$initials = $dbcon->real_escape_string($_GET['initials']);
     	$staff = $_GET['position'];
     	$fac = 0;
     	$va = 0;
     	if ($staff == 'f') {
     		$fac = 1;
     	} else {
     		$va = 1;
     	}
     	$sql = "";
     	$res = $dbcon->query("select * from user where initials = '$initials' and dept='$dept'");
     	if (mysqli_num_rows($res) > 0) {
     		$initDoub = 1;
     		$sql = "update user set active = 1 where initials='$initials' and dept='$dept'";
     	} else {
	    	$sql = "insert into user values('$name','$dept','$initials',$fac,$va,0,1)";
		}
    	$result=$dbcon->query($sql);
    	if (!$result) {
		  $error[] = $dbcon->error;
		  $errorSql[] = $sql;
		}
  	} elseif (isset($_GET['initials']) && isset($_GET['delete'])){
  		$initials = $dbcon->real_escape_string($_GET['initials']);
  		$sql = "update user set active = 0 where initials='$initials' and dept='$dept'";
  		$result = $dbcon->query($sql);
  		if (!$result) {
	      $error[] = $dbcon->error;
	      $errorSql[] = $sql;
	    }
  		$sql = array();
  		$sql[] = "delete from prof_time where initials='$initials' and dept='$dept'";
  		$sql[] = "delete from prof_sems where initials='$initials' and dept='$dept'";
  		$sql[] = "delete from prof_pref where initials='$initials' and dept='$dept'";
  		$sql[] = "delete from teaching where initials='$initials' and dept='$dept'";
  		$sql[] = "delete from prof_class where initials='$initials' and dept='$dept'";
  		for($i=0; $i<count($sql); $i++){
  			$result = $dbcon->query($sql[$i]);
  			if (!$result) {
		      $error[] = $dbcon->error;
		      $errorSql[] = $sql[$i];
		    }
  		}
  	}

  	if (isset($_GET['begin']) && isset($_GET['end'])) {
  		$begin = $_GET['begin'];
  		$end = $_GET['end'];
  		$pref = 0;
  		if (isset($_GET['pref'])) {
  			$pref = 1;
  		}
  		$sql= "Select exists(select * from dept_options where dept='$dept') as hasOpts";
  		$result = $dbcon->query($sql);
  		if (!$result) {
	      $error[] = $dbcon->error;
	      $errorSql[] = $sql;
	    }
  		while ($row = $result->fetch_assoc()) {
  			if ($row['hasOpts']==1) {
				$update = "update dept_options set begin = $begin, end = $end, pref = $pref where dept = '$dept'";
  				$res = $dbcon->query($update);
  				if (!$res) {
			      $error[] = $dbcon->error;
			      $errorSql[] = $update;
			    }
  			} else {
  				$insert = "insert into dept_options values('$dept',$begin,$end)";
  				$res = $dbcon->query($insert);
  				if (!$res) {
			      $error[] = $dbcon->error;
			      $errorSql[] = $insert;
			    }
  			}
  		}
  		
  	}

  	echo '<div class="container">';

	if (count($error) > 0) {
		include $_SERVER['DOCUMENT_ROOT'].'/classScheduler/error.php';
	}

	if ($initDoub || $facPrefs) {
		echo "<div class=\"alert alert-dismissible alert-warning text-center\" role=\"alert\">
			  	<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
			  		<span aria-hidden=\"true\">&times;</span>
				</button>";
		if ($initDoub) {
	  		echo "<p><strong>Alert!</strong> You added an instructor with a <i>known</i> username. This user has been made active (if he/she wasn't already).</p>
	  			<p>No additional instructors have been added.</p>";
	  	} else {
  			echo "<p><strong>Warning!</strong> You have changed a faculty to a visiting/adjunct instructor.</p>
	  			<p>This instructor will no longer get preferences for the schedule.</p>";
	  	}
	  	echo "</div>";
	}
?>
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="exampleModalLabel"></h4>
      </div>
      <div class="modal-body">
        <form class="" id="modForm" name="modForm" method="GET" action="adminDepartment.php">
          	<div class="form-group">
          		<h4><u>Academic Year:</u></h4>
          		<label for="begin">From:
          			<input class="form-control" type="number" id="begin" name="begin" min="2000" max="9999" required>
          		</label>
          		<label for="begin">To:
          			<input class="form-control" type="number" id="end" name="end" min="2000" max="9999" required>
          		</label>
  			</div>	
          	<div class="checkbox">
          		<h4><u>Preferences:</u></h4>
			  	<label>
					<input type="checkbox" id="pref" name="pref">
						Enable Instructor Preferences
			  	</label>
		  	</div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" form="modForm">Save</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="instructorModal" tabindex="-1" role="dialog" aria-labelledby="instructorModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="instructorModalLabel"></h4>
      </div>
      <div class="modal-body">
        <form class="" id="instrForm" name="instrForm" method="GET" action="adminDepartment.php">
          	<div class="form-group">
				<label for="name">Instructor Name:</label>
			  	<input class="form-control" type="text" id="name" name="name" required>
		    </div>
		    <div class="form-group">
				<label for="user">Gateway Username:</label>
			 	 <input class="form-control" type="text" id="initials" name="initials" maxlength="15" required>
		    </div>
		    <div class="form-group">
	    		<label for="user">Current Username: </label>
	    		<input type="text" class="form-control" id="user" name="user" readonly>
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
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" form="instrForm">Save</button>
      </div>
    </div>
  </div>
</div>
<div class="container">
	<h1 class="text-center">Admin - Department</h1>
	<?php
		$sql = "select * from dept_options where dept = '$dept'";
		$result = $dbcon->query($sql);
		while ($row = $result->fetch_assoc()) {
			if ($row['pref'] == 1) {
				echo '<h3 class="text-center">Preferences Enabled</h3>';
			}
			else {
				echo '<h3 class="text-center">Preferences Disabled</h3>';
			}
			echo '<h3 class="text-center">('.$row['begin'].'-'.$row['end'].')</h3>';
		}
	?>
	<div class="center-block text-center">
		<div type="button" class="btn btn-default" data-toggle="modal" data-target="#exampleModal">Update Department</div>
	</div>
	<hr>

	<div class="row" style="margin-top: 20px;">
		<div class="col-md-3">
		</div>
	    <div class="col-md-6">
			<legend class="text-center">Add Instructor:</legend>
			<form method="GET" action="adminDepartment.php">
				<div class="row">
					<div class="col-md-3">
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="name">Instructor Name:</label>
						  	<input class="form-control" type="text" id="name" name="name" required>
					    </div>
				    </div>
			    </div>
			    <div class="row">
					<div class="col-md-3">
					</div>
					<div class="col-md-6">
					    <div class="form-group">
							<label for="initials">Gateway Username:</label>
						 	 <input class="form-control" type="text" id="initials" name="initials" maxlength="15" required>
					    </div>
				    </div>
			    </div>
			    <div class="row">
					<div class="col-md-3">
					</div>
					<div class="col-md-6 center-block text-center">
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
		    	</div>
				<button class="btn btn-primary center-block" type="submit" style="margin-top: 5px;">Add Instructor</button>
			</form>
	    </div>
	</div>
	<div class="row">
	    <div class="col-md-6 text-center center-block">
			<legend>Current Faculty:</legend>
				  <?php
					$result=$dbcon->query("Select initials, name from user where faculty = 1 and active = 1 and dept='$dept'");
					if (mysqli_num_rows($result)>0) {
						echo '<table style="width: 80%; margin: auto;" class="table table-responsive table-striped text-center table-hover">
								<th class="text-center">Edit</th>
						  		<th class="text-center">Initials</th>
						  		<th class="text-center">Name</th>
						  		<th class="text-center">Delete</th>';
						while($row = $result->fetch_assoc()) {
						   	echo "<tr><td>";
						   	echo "<div type=\"button\" class=\"glyphicon glyphicon-pencil\" style=\"color: green;\" data-toggle=\"modal\" data-target=\"#instructorModal\" data-name=\"".$row['name']."\" data-initials=\"".$row['initials']."\"></div>";
						   	echo "</td><td>";
						   	echo $row['initials'];
						   	echo "</td><td>";
						   	echo $row['name'];
						   	echo "</td><form method=\"POST\" action=\"adminDepartment.php?delete=1&initials=".$row['initials']."\"><td>";
	   						echo "<button type=\"submit\" class=\"btn btn-sm btn-danger\">Delete</button></td></form>";
	   						echo "</tr>";
						}
						echo "</table>";
					} else {
						echo "<h4>No Current Faculty</h4>";
					}
				  ?>
		</div>
		<div class="col-md-6 text-center center-block">
			<legend>Current Visiting/Adjunct:</legend>
				  <?php
					$result=$dbcon->query("Select initials, name from user where visit_adj = 1 and dept='$dept' and active = 1");
					if (mysqli_num_rows($result)>0) {
						echo '<table style="width: 80%; margin: auto;" class="table table-responsive table-striped text-center table-hover">
								<th class="text-center">Edit</th>
						  		<th class="text-center">Initials</th>
						  		<th class="text-center">Name</th>
						  		<th class="text-center">Delete</th>';
						while($row = $result->fetch_assoc()) {
						   	echo "<tr><td>";
						   	echo "<div type=\"button\" class=\"glyphicon glyphicon-pencil\" style=\"color: green;\" data-toggle=\"modal\" data-target=\"#instructorModal\" data-name=\"".$row['name']."\" data-initials=\"".$row['initials']."\"></div>";
						   	echo "</td><td>";
						   	echo $row['initials'];
						   	echo "</td><td>";
						   	echo $row['name'];
						   	echo "</td><form method=\"POST\" action=\"adminDepartment.php?delete=1&initials=".$row['initials']."\"><td>";
	   						echo "<button type=\"submit\" class=\"btn btn-sm btn-danger\">Delete</button></td></form>";
	   						echo "</tr>";
						}
						echo "</table>";
					} else {
						echo "<h4>No Current Visiting/Adjunct</h4>";
					}
				  ?>
		</div>
	</div>	
</div>
</body>
<script>
  $('#exampleModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget) // Button that triggered the modal
    var modal = $(this)
    modal.find('.modal-title').text('Edit Department Information')
  })

  $('#instructorModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget) // Button that triggered the modal
    var modal = $(this)
    var name = button.data('name') // Extract info from data-* attributes
    var initials = button.data('initials') // Extract info from data-* attributes
    modal.find('.modal-title').text('Edit ' + name)  
    modal.find('#name').val(name)
    modal.find('#user').val(initials)
    modal.find('#initials').val(initials)
  })
</script>
</html>
