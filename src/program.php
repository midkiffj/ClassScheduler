<?php

	$title = 'Program';
  	$active = 'program';

  	include $_SERVER['DOCUMENT_ROOT'].'/classScheduler/navScripts.php';
	include $_SERVER['DOCUMENT_ROOT'].'/classScheduler/connection.php';
	$dept = $_SESSION['dept'];
?>
<div class="container">
	<h1 class="text-center">Program</h1>
	<hr>
	<div class="row">
		<div class="col-md-4 text-center">
			<legend>Model</legend>
			<form method="GET" action="write_lp.php">
				<button class="btn btn-lg btn-success submit"
				<?php
					if ($_SESSION['admin']!=1) {
						echo " disabled";
					}
				?>
				>Generate Model</button>
			</form>	
			<p class="help-block">The model must be generated before the schedule.</p>
		</div>
		<div class="col-md-4 text-center">
			<legend>Class Scheduler</legend>
			<form method="GET" action="parse.php">
				<button class="btn btn-lg btn-warning submit"
				<?php
					if ($_SESSION['admin']!=1) {
						echo " disabled";
					}
				?>
				>Generate Schedule</button>
			</form>	
			<p class="help-block">This may take some time to compute. If the website stalls for more than 10 minutes, contact an administrator.</p>
		</div>
		<div class="col-md-4 text-center">
			<legend>Department Totals</legend>
			<?php
				$num_fac = 0;
				$num_va = 0;
				$num_class = 0;
				$num_pref = 0;
				$result=$dbcon->query("Select * from user where dept='$dept' and faculty = 1 and active = 1");
				$num_fac = mysqli_num_rows($result);
				$result=$dbcon->query("Select * from user where dept='$dept' and visit_adj = 1 and active = 1");
				$num_va = mysqli_num_rows($result);
				$result=$dbcon->query("Select sum(fall+spring) as sum from class where dept='$dept'");
				$row = $result->fetch_assoc();
				if ($row['sum']) {
					$num_class = $row['sum'];
				}
				//$num_class = mysqli_num_rows($result);
				$result=$dbcon->query("Select * from prof_pref where dept='$dept' group by initials");
				$num_pref = mysqli_num_rows($result);
				echo "Faculty: ".$num_fac."<br>";
				echo "Visiting/Adjunct: ".$num_va."<br>";
				echo "Courses: ".$num_class."<br>";
				echo "Preferences Inputted: ".$num_pref."<br>";
			?>
			<!--Cross_List professors:--> 
		</div>
	</div>
	<h2 class="text-center">Schedule</h2>
	<?php
		$sql = "select begin, end from dept_options where dept = '$dept'";
		$result = $dbcon->query($sql);
		while ($row = $result->fetch_assoc()) {
			echo '<h4 class="text-center">('.$row['begin'].'-'.$row['end'].')</h4>';
		}
	?>
	<hr>
	<?php
		$sql= "Select exists(select * from teaching where dept='".$_SESSION['dept']."') as schedule";
	    $teachCheck=$dbcon->query($sql);
	    while ($teach=$teachCheck->fetch_assoc()) {
	    	if ($teach['schedule']==0) {
	    		echo "<h4 class=\"text-center\">No Current Schedule</h4>";
	      		die();
	      	}
	  	}
	?>
	<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
		<li class="active">
        	<a href="#schedule" data-toggle="tab">Schedule</a>
		</li>
		<li><a href="#timeslot" data-toggle="tab">Timeslot Counts</a>
		</li>
		<li><a href="#profInfo" data-toggle="tab">Professor Info</a>
		</li>
	</ul>
	<div class="tab-content">
	  	<div class="tab-pane active" id="schedule">
			<div class="row" style="margin-top: 20px;">
			    <div class="col-md-6 text-center">
			    <?php
				    $result=$dbcon->query("Select * from teaching natural join time where sem = 1 and dept='$dept' order by num, teaching.id, initials");
				    $num_classes = mysqli_num_rows($result);
					echo "<h3>Fall Schedule ($num_classes)</h3>";
					echo "<a class=\"btn btn-default\" id=\"fallScheduleBtn\" href=\"#\" onClick =\"fnExcelReport('fallSchedule');\" style=\"margin-bottom: 10px;\">Excel Download</a>";
					echo "<table id=\"fallSchedule\"class=\"table table-responsive table-striped table-bordered table-hover text-center\" style=\"margin-left: auto;margin-right: auto;\">
						<th class=\"text-center\">Number</th>
						<th class=\"text-center\">Time</th>
						<th class=\"text-center\">Professor</th>";
					$prev = -1;
					$section = 1;
					while($row = $result->fetch_assoc()) {
						$num = $row['num'];
						if ($num != $prev) {
							$prev = $num;
							$section = 1;
						}
						echo "<tr>
							<td>".$row['num'];
						if ($section < 10) {
							echo "-0$section</td>";
						} else {
							echo "-$section</td>";
						}
						echo "<td>".$row['time']."</td>
							<td>".$row['initials']."</td>
							</tr>";
						$section = $section + 1;
					}
					echo "</table>";
				?>	
			    </div>
			    <div class="col-md-6 text-center">
			    <?php
				    $result=$dbcon->query("Select * from teaching natural join time where sem = 2 and dept='$dept' order by num, teaching.id, initials");
				    $num_classes = mysqli_num_rows($result);
					echo "<h3>Spring Schedule ($num_classes)</h3>";
					echo "<a class=\"btn btn-default\" id=\"springScheduleBtn\" href=\"#\" onClick =\"fnExcelReport('springSchedule');\" style=\"margin-bottom: 10px;\">Excel Download</a>";
					echo "<table id=\"springSchedule\" class=\"table table-responsive table-striped table-bordered table-hover text-center\" style=\"margin-left: auto;margin-right: auto;\">
						<th class=\"text-center\">Number</th>
						<th class=\"text-center\">Time</th>
						<th class=\"text-center\">Professor</th>";
					$prev = -1;
					$section = 1;
					while($row = $result->fetch_assoc()) {
						$num = $row['num'];
						if ($num != $prev) {
							$prev = $num;
							$section = 1;
						}
						echo "<tr>
							<td>".$row['num'];
						if ($section < 10) {
							echo "-0$section</td>";
						} else {
							echo "-$section</td>";
						}
						echo "<td>".$row['time']."</td>
							<td>".$row['initials']."</td>
							</tr>";
						$section = $section + 1;
					}
					echo "</table>";
				?>	
			    </div>
			</div>	
		</div>
		<div class="tab-pane" id="timeslot">
			<div class="row" style="margin-top: 20px;">
				<div class="col-md-2">
				</div>
				<div class="col-md-3 text-center">
					<?php
						echo "<h3>Fall Timeslot Usage</h3>";
						echo "<a class=\"btn btn-default\" id=\"fallTimeslotBtn\" href=\"#\" onClick =\"fnExcelReport('fallTimeslot');\" style=\"margin-bottom: 10px;\">Excel Download</a>";
						echo "<table id=\"fallTimeslot\" class=\"table table-responsive table-striped table-bordered table-hover text-center\" style=\"margin-left: auto;margin-right: auto;\">
								<th class=\"text-center\">Time</th>
								<th class=\"text-center\">Count</th>";
						$result=$dbcon->query("Select time, count(*) as count from teaching natural join time where sem = 1 and dept='$dept' group by time order by id");
						while($row = $result->fetch_assoc()) {
							echo "<tr>
								<td>".$row['time']."</td>
								<td>".$row['count']."</td>
								</tr>";
						}
						echo "</table>";
					?>
				</div>
				<div class="col-md-2">
				</div>
				<div class="col-md-3 text-center">
					<?php
						echo "<h3>Spring Timeslot Usage</h3>";
						echo "<a class=\"btn btn-default\" id=\"springTimeslotBtn\" href=\"#\" onClick =\"fnExcelReport('springTimeslot');\" style=\"margin-bottom: 10px;\">Excel Download</a>";
						echo "<table id=\"springTimeslot\" class=\"table table-responsive table-striped table-bordered table-hover text-center\" style=\"margin-left: auto;margin-right: auto;\">
								<th class=\"text-center\">Time</th>
								<th class=\"text-center\">Count</th>";
						$result=$dbcon->query("Select time, count(*) as count from teaching natural join time where sem = 2 and dept='$dept' group by time order by id");
						while($row = $result->fetch_assoc()) {
							echo "<tr>
								<td>".$row['time']."</td>
								<td>".$row['count']."</td>
								</tr>";
						}
						echo "</table>";
					?>
				</div>
				<div class="col-md-2">
				</div>
			</div>
		</div>	
	    <div class="tab-pane" id="profInfo">
			<div class="row" style="margin-top: 20px;">
	            <div class="col-md-2">
				</div>
				<div class="col-md-3 text-center">
					<?php
						echo "<h3>Fall Classes</h3>";
						echo "<a class=\"btn btn-default\" id=\"fallClassesBtn\" href=\"#\" onClick =\"fnExcelReport('fallClasses');\" style=\"margin-bottom: 10px;\">Excel Download</a>";
						echo "<table id=\"fallClasses\" class=\"table table-responsive table-striped table-bordered table-hover text-center\" style=\"margin-left: auto;margin-right: auto;\">
							<th class=\"text-center\">Professor</th>
							<th class=\"text-center\">Classes</th>";
						$result=$dbcon->query("Select initials, GROUP_CONCAT(num separator ', ') as num from teaching where sem = 1 and dept='$dept' group by initials order by initials,num");
						while($row = $result->fetch_assoc()) {
							echo "<tr>
								<td>".$row['initials']."</td>
								<td>".$row['num']."</td>
								</tr>";
						}
						echo "</table>";
					?>
				</div>
				<div class="col-md-2">
				</div>
				<div class="col-md-3 text-center">
					<?php
						echo "<h3>Spring Classes</h3>";
						echo "<a class=\"btn btn-default\" id=\"springClassesBtn\" href=\"#\" onClick =\"fnExcelReport('springClasses');\" style=\"margin-bottom: 10px;\">Excel Download</a>";
						echo "<table id=\"springClasses\" class=\"table table-responsive table-striped table-bordered table-hover text-center\" style=\"margin-left: auto;margin-right: auto;\">
							<th class=\"text-center\">Professor</th>
							<th class=\"text-center\">Classes</th>";
						$result=$dbcon->query("Select initials, GROUP_CONCAT(num separator ', ') as num from teaching where sem = 2 and dept='$dept' group by initials order by initials,num");
						while($row = $result->fetch_assoc()) {
							echo "<tr>
								<td>".$row['initials']."</td>
								<td>".$row['num']."</td>
								</tr>";
						}
						echo "</table>";
					?>
				</div>
				<div class="col-md-2">
				</div>
			</div>
		</div>
	</div>
</div>
</body>
<script>
	function fnExcelReport(name) {
	    var tab_text = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
	    tab_text = tab_text + '<head><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';

	    tab_text = tab_text + '<x:Name>Test Sheet</x:Name>';

	    tab_text = tab_text + '<x:WorksheetOptions><x:Panes></x:Panes></x:WorksheetOptions></x:ExcelWorksheet>';
	    tab_text = tab_text + '</x:ExcelWorksheets></x:ExcelWorkbook></xml></head><body>';

	    tab_text = tab_text + "<table border='1px'>";
	    tab_text = tab_text + $('#'+name).html();
	    tab_text = tab_text + '</table></body></html>';

	    var data_type = 'data:application/vnd.ms-excel';
	    
	    var ua = window.navigator.userAgent;
	    var msie = ua.indexOf("MSIE ");
	    
	    if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
	        if (window.navigator.msSaveBlob) {
	            var blob = new Blob([tab_text], {
	                type: "application/csv;charset=utf-8;"
	            });
	            navigator.msSaveBlob(blob, name+'.xls');
	        }
	    } else {
	        $('#'+name+'Btn').attr('href', data_type + ', ' + encodeURIComponent(tab_text));
	        $('#'+name+'Btn').attr('download', name+'.xls');
	    }
	}
</script>
</html>
