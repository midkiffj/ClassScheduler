<?php

	$title = 'Admin - Admins';
  	$active = 'adminAdmins';

  	include $_SERVER['DOCUMENT_ROOT'].'/classScheduler/navScripts.php';
  	include $_SERVER['DOCUMENT_ROOT'].'/classScheduler/connection.php';
  	$dept = $_SESSION['dept'];

  	if ($_SESSION['admin']!=1) {
  		header("Location: options.php");
  	}

  	$error = array();
  	$errorSql = array();
  	$deletedSelf = null;

	if (isset($_GET['initials'])) {
  		$initials = $dbcon->real_escape_string($_GET['initials']);
  		if ($initials==$dbcon->real_escape_string($_SESSION['username'])) {
  			$sql = "Select exists(select * from user where initials='$initials' and dept='".$_SESSION['dept']."' and admin=1) as isAdmin";
	    	$result=$dbcon->query($sql);
	    	if (!$result) {
		      $error[] = $dbcon->error;
		      $errorSql[] = $sql;
		    }
		    if ($row = $result->fetch_assoc()) {
  				if ($row['isAdmin'] == 1) {
  					$deletedSelf = 1;
  				}
  			}
  		}
	    $sql = "update user set admin = case when admin = 1 then admin = 0 else 1 end where initials='$initials' and dept='$dept'";
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

	if ($deletedSelf) {
		echo "<div class=\"alert alert-dismissible alert-warning text-center\" role=\"alert\">
			  	<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
			  		<span aria-hidden=\"true\">&times;</span>
				</button>
	  			<p><strong>Warning!</strong> You deleted yourself as an Admin. You will be unable to perform Admin actions the next time you log in.</p>
  			</div>";
	}
  	
?>
	<h1 class="text-center">Admin - Admins</h1>
	<hr>
	<div class="row" style="margin-top: 20px;">
	    <div class="col-md-8 col-md-offset-2" style="">
	    	<div class="col-md-6">
				<legend class="text-center">Add/Delete Admins:</legend>
				<form id="addForm" method="GET" action="adminAdmins.php">
					<div class="row">
						<div class="col-md-9">
							<div class="form-group">
			                	<label for="initials">Instructor:</label><br>
			                    <select class="form-control" name="initials" id="initials" form="addForm" required>
		                	    <?php
								  	$result=$dbcon->query("select initials from user where admin<>1 and dept='$dept'");
								
								  	while($row = $result->fetch_assoc()) {
								     	echo "<option value=\"".$row['initials']."\">";
								     	echo $row['initials'];
								     	echo "</option>";
								  	}
							    ?>
							    </select>
						    </div>	
					    </div>
				    </div>
					<button class="btn btn-success" type="submit">Add Admin</button>
				</form>
				<hr>
				<form id="delForm" method="GET" action="adminAdmins.php">
					<div class="row">
						<div class="col-md-9">
							<div class="form-group">
			                	<label for="initials">Instructor:</label><br>
			                    <select class="form-control" name="initials" id="initials" form="delForm" required>
		                	    <?php
								  	$result=$dbcon->query("select initials from user where admin=1 and dept='$dept'");
								
								  	while($row = $result->fetch_assoc()) {
								     	echo "<option value=\"".$row['initials']."\">";
								     	echo $row['initials'];
								     	echo "</option>";
								  	}
							    ?>
							    </select>
						    </div>	
					    </div>
				    </div>
					<button class="btn btn-danger" type="submit">Delete Admin</button>
				</form>
			</div>
			<div class="col-md-6">
					<legend class="text-center">Current Admin(s):</legend>
					<h4>
					<?php
					  	$result=$dbcon->query("select initials, name from user where admin=1 and dept='$dept'");
						echo "<ol>";
					  	while($row = $result->fetch_assoc()) {
					     	echo "<li>";
					     	echo $row['name']." (".$row['initials'].")";
				    	 	echo "</li>";
					  	}
					  	echo "</ol>";
				    ?>	
				    </h4>
			    </div>	
	    </div>
	</div>	
</div>
</body>

</html>