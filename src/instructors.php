<?php

	$title = 'Instructors';
  	$active = 'instructors';

  	include $_SERVER['DOCUMENT_ROOT'].'/classScheduler/navScripts.php';
  	include $_SERVER['DOCUMENT_ROOT'].'/classScheduler/connection.php';
  	$showPrefs = 1;
    $con = new mysqli("localhost","peeping","tom","span_scheduler");
    $sql = "select pref from dept_options where dept='$dept'";
    $prefCheck = $con->query($sql);
    while ($pref = $prefCheck->fetch_assoc()){
      $showPrefs = $pref['pref'];
    }
  	$dept = $_SESSION['dept'];

  	$error = array();
  	$errorSql = array();

  	if (isset($_GET['initials']) && isset($_GET['fall']) && isset($_GET['spring'])) {
  		$initials = $dbcon->real_escape_string($_GET['initials']);
     	$fall = $_GET['fall'];
     	$spring = $_GET['spring'];
	    if (isset($_GET['delete'])){
	    	$sql = "delete from prof_sems where initials='$initials' and dept='$dept' and fall=$fall and spring=$spring";
	    } else {
	    	$sql = "insert into prof_sems values('$initials','$dept',$fall,$spring)";
	    }
    	$result=$dbcon->query($sql);
    	if (!$result) {
          $error[] = $dbcon->error;
          $errorSql[] = $sql;
        }
  	}
  	if (isset($_GET['instr']) && isset($_GET['time'])) {
  		$instr = $dbcon->real_escape_string($_GET['instr']);
     	$time = $_GET['time'];
	    if (isset($_GET['delete'])) {
	    	for ($i=0; $i<count($time); $i++){
      			$sql = "delete from prof_time where initials='$instr' and dept='$dept' and id=$time[$i]";
      			$result=$dbcon->query($sql);
      			if (!$result) {
		          $error[] = $dbcon->error;
		          $errorSql[] = $sql;
		        }
      		}
	    } else {
	    	$sql = "insert into prof_time values('$instr','$dept',$time)";
	    	$result=$dbcon->query($sql);
	    	if (!$result) {
	          $error[] = $dbcon->error;
	          $errorSql[] = $sql;
	        }
	    }     
    }

 	echo '<div class="container">';

	if (count($error) > 0) {
		include $_SERVER['DOCUMENT_ROOT'].'/classScheduler/error.php';
	}
?>
<h1 class="text-center">Instructors</h1>
<hr>
<div class="row">
  <div class="col-md-4 col-xs-12">
    <strong style="font-size: 1.5em;"><u>Class Counts:</u></strong><br>
      Add an instructor here if:<br>
      • The instructor MUST teach the specified number of classes in each semester.
  </div> 
  <?php
  	if ($showPrefs) {
  		echo '<div class="col-md-4 col-xs-12">
 				 <strong style="font-size: 1.5em;">
 				 	<u>Preferences:</u>
 				 </strong><br>
    			View the preferences of faculty who have submitted.
 			 </div>';
  	}
  ?>
  
  <div class="col-md-4 col-xs-12">
  <strong style="font-size: 1.5em;"><u>Time Restrictions:</u></strong><br>
    Add an instructor and time to this table if:<br>
    • The instructor cannot teach a class at the specified time.
  </div>
</div>
<div style="margin-top:10px;"></div>
<ul class="nav nav-tabs" id="tabs" data-tabs="tabs">
			<li class="active">
        	<a href="#edit" data-toggle="tab">Class Counts</a>
			</li>
			<?php
				if ($showPrefs) {
					echo '<li><a href="#pref" data-toggle="tab">Preferences</a>
							</li>';
				}
			?>
			
			<li><a href="#time" data-toggle="tab">Time Restrictions</a>
			</li>
		</ul>

		<div class="tab-content">
		  	<div class="tab-pane active" id="edit">
			  	<div class="row" style="margin-top: 20px;">
			  	<?php
			  		if ($_SESSION['admin']==1){
			  			echo "<div class=\"col-md-4 text-center\">
								<legend>Instructor Class Count:</legend>
								<div class=\"col-md-6 col-md-offset-3\">
									<form id=\"countForm\" method=\"GET\" action=\"instructors.php\">
										<div class=\"form-group\">
						                	<label for=\"initials\">Instructor:</label><br>
						                    <select class=\"form-control\" name=\"initials\" id=\"initials\" form=\"countForm\" required>";
						$result=$dbcon->query("select initials from user where active = 1 and dept='$dept'");
						
						while($row = $result->fetch_assoc()) {
						    echo "<option value=\"".$row['initials']."\">";
						    echo $row['initials'];
						    echo "</option>";
						}
						echo "			   </select>
									    </div>	
									    <div class=\"form-group\">
									  	  <label for=\"fall\">Fall Count:</label>
									  	  <input class=\"form-control\" type=\"number\" id=\"fall\" name=\"fall\" min=\"0\" max=\"5\" required>
									    </div>
									    <div class=\"form-group\">
										    <label for=\"spring\">Spring Count:</label>
										    <input class=\"form-control\" type=\"number\" id=\"spring\" name=\"spring\" min=\"0\" max=\"5\" required>
									    </div>
										<button class=\"btn btn-primary\" type=\"submit\" style=\"margin-top: 5px;\">Add Class Count</button>
									</form>
								</div>
						    </div>";
						    echo "<div class=\"col-md-8 text-center\">";
			  		} else {
			  			echo "<div class=\"col-md-6 col-md-offset-3 text-center\">";
			  		}
			  	?>
						<legend>Instructor Counts:</legend>
						  	<?php
						  		$result=$dbcon->query("SELECT * FROM prof_sems where dept='$dept'");
						  		if (mysqli_num_rows($result)>0) {
							  		echo '<table class="table table-striped table-responsive text-center table-hover">
			                				<th class="text-center">Instructor</th>
			                				<th class="text-center">Fall</th>
			                				<th class="text-center">Spring</th>';
							  		if ($_SESSION['admin']==1) {
							  			echo "<th class=\"text-center\">Delete</th>";
							  		}
									
									while($row = $result->fetch_assoc()) {
									   echo "<tr><td>";
									   echo $row['initials'];
									   echo "</td><td>";
									   echo $row['fall'];
									   echo "</td><td>";
									   echo $row['spring'];
									   echo "</td>";
									   if ($_SESSION['admin']==1) {
										   	echo "<form method=\"POST\" action=\"instructors.php?delete=1&initials=".$row['initials']."&fall=".$row['fall']."&spring=".$row['spring']."\"><td>";
			    							echo "<button type=\"submit\" class=\"btn btn-sm btn-danger\">Delete</button></td></form>";
		    							}
		    							echo "</tr>";
									}
									echo "</table>";
								} else {
									echo "<h4>No Instructor Counts Stored</h4>";
								}
						  	?>
				    </div>
				</div>	
			</div>
				<div class="tab-pane" id="pref">
					<div class="row" style="margin-top: 20px;">
						<div class="col-md-12">
								<?php
									$result=$dbcon->query("Select initials, GROUP_CONCAT(num separator ', ') as num, sem from prof_pref where dept='$dept' group by initials, sem order by initials, sem");
									if (mysqli_num_rows($result)>0) {
										echo '<table class="table table-responsive table-striped table-hover text-center" style="width: 60%;margin-left: auto;margin-right: auto;">
												<th class="text-center">Instructor</th>
												<th class="text-center">Fall</th>
												<th class="text-center">Spring</th>';
										$pastRow = 2;
										$pastIni = "";
										$rowDone = false;
										while($row = $result->fetch_assoc()) {
											if ($row['sem'] == 1 && $pastRow == 2) {
												//new row
												echo "<tr><td>";
												echo $row['initials'];
												echo "</td><td>";
												//fall nums
												echo $row['num'];
												echo "</td><td>";
											} elseif ($row['sem'] == 1 && $pastRow == 1) {
												//finish spring num
												echo "</td></tr>";
												//new row
												echo "<tr><td>";
												echo $row['initials'];
												echo "</td><td>";
												//fall nums
												echo $row['num'];
												echo "</td><td>";
											} elseif ($row['sem'] == 2 && $pastRow == 1 && $row['initials'] == $pastIni) {
												echo $row['num'];
												echo "</td></tr>";
											} elseif ($row['sem'] == 2 && $pastRow == 1) {
												echo "</td></tr>";
												echo "<tr><td>";
												echo $row['initials'];
												echo "</td><td></td><td>";
												echo $row['num'];
												echo "</td></tr>";
											} elseif ($row['sem'] == 2 && $pastRow == 2) {
												//new row
												echo "<tr><td>";
												echo $row['initials'];
												echo "</td><td></td><td>";
												echo $row['num'];
												echo "</td></tr>";
											}
											$pastRow = $row['sem'];
											$pastIni = $row['initials'];
										}
									} else {
										echo "<h4 class=\"text-center\">No Preferences Entered</h4>";
									}
								?>
							</table>	
						</div>
					</div>
				</div>	
        <div class="tab-pane" id="time">
			<div class="row" style="margin-top: 20px;">
				<?php
					if ($_SESSION['admin']==1) {
						echo '<div class="col-xs-12 col-md-4 text-center">
								<legend>Add Restriction:</legend>
			            		<div class="col-md-6 col-md-offset-3">
					              <form id="timeForm" method="GET" action="instructors.php">
					              	<div class="form-group">
					                	<label for="instr">Instructor:</label><br>
					                    <select class="form-control" name="instr" id="instr" form="timeForm" required>';
						$result=$dbcon->query("select initials from user where active = 1 and dept='$dept'");
								
						while($row = $result->fetch_assoc()) {
						    echo "<option value=\"".$row['initials']."\">";
						    echo $row['initials'];
						    echo "</option>";
						}
						echo '</select>
						    </div>	
						    <div class="form-group">
			                	<label for"time">Time:</label>
			                  	<select class="form-control" name="time" id="time" form="timeForm" required>';
						$result=$dbcon->query("select id, time from time");
					
						while($row = $result->fetch_assoc()) {
						   echo "<option value=\"".$row['id']."\">";
						   echo $row['time'];
						   echo "</option>";
						}
						echo '</select>
						  	</div>
			                <button class="btn btn-primary" type="submit" style="margin-top: 5px;">Add Restriction</button>
			              </form>
		                </div>
			            </div>';
			            echo '<div class="col-xs-12 col-md-8 text-center">';
		        	} else {
		        		echo '<div class="col-xs-12 col-md-6 col-md-offset-3 text-center">';
		        	}

		        	echo "<legend>Current Restrictions:</legend>";
		        	$result=$dbcon->query("Select initials, GROUP_CONCAT(time  separator '<br>') as time, GROUP_CONCAT(time.id separator ',') as id from prof_time, time where prof_time.id = time.id and dept='$dept' group by initials");

		        	if (mysqli_num_rows($result)>0) {
		            
		              echo '<table width="100%" class="table table-striped table-responsive text-center table-hover">
		                <th class="text-center">Instructor</th>
		                <th class="text-center">Time</th>';
					  	if ($_SESSION['admin']==1) {
					  		echo '<th class="text-center">Delete</th>';
					  	}
						
						
						while($row = $result->fetch_assoc()) {
						   echo "<tr><td>";
						   echo $row['initials'];
						   echo "</td><td>";
						   echo $row['time'];
						   echo "</td>";
						   if ($_SESSION['admin']==1) {
							   echo "<form method=\"POST\" action=\"instructors.php?delete=1&instr=".$row['initials'];
							   	$ids = explode(",",$row['id']);
								for ($i=0; $i<count($ids); $i++) {
	  								echo "&time[]=".$ids[$i];
								}
								echo "\"><td>";
								echo "<button type=\"submit\" class=\"btn btn-sm btn-danger\">Delete</button></td></form>";
							}
							echo "</tr>";
						}
						echo "</table>";
					} else {
						echo "<h4 class=\"text-center\">No Current Restrictions</h4>";
					}	
			    ?>
		            </div>
				</div>
			</div>
		</div>
  </div>
</div>
</body>

</html>
