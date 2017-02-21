
<?php

  $title = 'Courses';
  $active = 'courses';

  include $_SERVER['DOCUMENT_ROOT'].'/classScheduler/navScripts.php';
  include $_SERVER['DOCUMENT_ROOT'].'/classScheduler/connection.php';
  $dept = $_SESSION['dept'];

  $error = array();
  $errorSql = array();

  if (isset($_GET['num']) && isset($_GET['fall']) && isset($_GET['spring'])) {
    $num = $_GET['num'];
    $fall = $_GET['fall'];
    $spring = $_GET['spring'];
    $sql = "";
    if (isset($_GET['edit'])) {
      $sql = "update class set spring = $spring, fall = $fall where num = $num";
    }
    else {
      $sql = "insert into class values(".$num.",'$dept',".$fall.",".$spring.",0,1,1,0)";
    }
    $result=$dbcon->query($sql);
    if (!$result) {
      $error[] = $dbcon->error;
      $errorSql[] = $sql;
    }
  }
  elseif (isset($_GET['delete']) && isset($_GET['num']) && isset($_GET['initials'])) {
    $num = $_GET['num'];
    $initials = $_GET['initials'];
    for ($i=0; $i<count($initials); $i++){
      $init = $dbcon->real_escape_string($initials[$i]);
      $sql = "delete from prof_class where num=".$num." and dept='$dept' and initials=\"".$init."\"";
      $result=$dbcon->query($sql);
      if (!$result) {
        $error[] = $dbcon->error;
        $errorSql[] = $sql;
      }
    }
  }
  elseif (isset($_GET['delete']) && isset($_GET['num'])) {
    $num = $_GET['num'];
    $sql = "delete from class where num=".$num." and dept='$dept'";
    $result=$dbcon->query($sql);
    if (!$result) {
      $error[] = $dbcon->error;
      $errorSql[] = $sql;
    }
  }
  elseif (isset($_GET['num']) && isset($_GET['initials'])) {
    $num = $_GET['num'];
    $initials = $dbcon->real_escape_string($_GET['initials']);
    $sql = "insert into prof_class values(".$num.",'".$initials."','$dept')";
    $result=$dbcon->query($sql);
    if (!$result) {
      $error[] = $dbcon->error;
      $errorSql[] = $sql;
    }
  }
  elseif (isset($_GET['1day']) || isset($_GET['2day']) || isset($_GET['3day']) || isset($_GET['5day'])) {
    $num = -1;
    $oneday = 0;
    $twoday = 0;
    $threeday = 0;
    $fiveday = 0;
    if (isset($_GET['1day'])) {
      $oneday = 1;
      $num = $_GET['1day'];
    }
    if (isset($_GET['2day'])) {
      $twoday = 1;
      $num = $_GET['2day'];
    }
    if (isset($_GET['3day'])) {
      $threeday = 1;
      $num = $_GET['3day'];
    }
    if (isset($_GET['5day'])) {
      $fiveday = 1;
      $num = $_GET['5day'];
    }
    $sql = "update class set one_day = ".$oneday.", two_day = ".$twoday.", three_day = ".$threeday.", five_day = ".$fiveday." where num = ".$num." and dept='$dept'";
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

<h1 class="text-center">Courses</h1>
<hr>
<div class="row">
  <div class="col-md-3 col-xs-12">
    <strong style="font-size: 1.5em;"><u>Edit/View:</u></strong><br>
      Sections taught in the Fall and Spring.
  </div> 
  <div class="col-md-3 col-xs-12">
  <strong style="font-size: 1.5em;"><u>Time Restrictions:</u></strong><br>
    Classes taught 1, 2, 3 or 5 days a week.<br>
    • Classes taught 1 or 5 days a week MUST be exclusively in those timeslots.
  </div>
  <div class="col-md-6 col-xs-12">
  <strong style="font-size: 1.5em;"><u>Instructor Restrictions:</u></strong><br>
    Add a class and instructor to this table if:<br>
    • The instructor MUST teach the class in the selected semester(s).<br>
    • The instructors listed are the ONLY instructors allowed to teach the course.
  </div>
</div>

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="exampleModalLabel"></h4>
      </div>
      <div class="modal-body">
        <form id="modForm" name="modForm" method="GET" action="courses.php">
          <table class="table table-striped text-center">
            <th>1 day/wk</th><th>2 day/wk</th><th>3 day/wk</th><th>5 day/wk</th>
            <tr>
              <td>
                <input class="checkbox" type="checkbox" id="1day" name="1day">
              </td>
              <td>
                <input class="checkbox" type="checkbox" id="2day" name="2day">
              </td>
              <td>
                <input class="checkbox" type="checkbox" id="3day" name="3day">
              </td>
              <td>
                <input class="checkbox" type="checkbox" id="5day" name="5day">
              </td>
            </tr>
          </table>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" form="modForm">Save</button>
      </div>
    </div>
  </div>
</div>
<div style="margin-top:10px;"></div>
<ul class="nav nav-tabs">
	<li role="presentation" class="active">
	  <a href="#edit" data-toggle="tab">Edit/View</a>
	</li>
	<li role="presentation"><a href="#time" data-toggle="tab">Time Restrictions</a>
	</li>
	<li role="presentation"><a href="#instructor" data-toggle="tab">Instructor Restrictions</a>
	</li>
</ul>

<div class="tab-content ">
  <div class="tab-pane active" id="edit">  
  <div class="row" style="margin-top: 20px;">
  <?php
    if ($_SESSION['admin']==1) {
      echo "
            <div class=\"col-xs-12 col-md-2 text-center\">
            	<legend>Add Course:</legend>
            	<form method=\"GET\" action=\"courses.php\">
                <div class=\"form-group\">
            	   <label for=\"num\">Class Number #:</label>
            	   <input class=\"form-control\" type=\"number\" id=\"num\" name=\"num\" min=\"100\" max=\"999\"  required>
                </div>
                <div class=\"form-group\">
              	  <label for=\"fall\">Fall Count:</label>
              	  <input class=\"form-control\" type=\"number\" id=\"fall\" name=\"fall\" max=\"20\" required>
                </div>
                <div class=\"form-group\">
            	    <label for=\"spring\">Spring Count:</label>
            	    <input class=\"form-control\" type=\"number\" id=\"spring\" name=\"spring\" max=\"20\" required>
                </div>
            	 <button class=\"btn btn-primary\" type=\"submit\" style=\"margin-top: 5px;\">Add Course</button>
               <button class=\"btn btn-success\" type=\"submit\" style=\"margin-top: 5px;\" name=\"edit\" id=\"edit\" value=\"1\">Edit Course</button>
            	</form>
            </div>";
      echo "<div class=\"col-xs-12 col-md-10 text-center center-block\">";
    } else {
      echo "<div class=\"col-md-2\"></div>";
      echo "<div class=\"col-xs-12 col-md-8 text-center center-block\">";
    }
  ?>
  
	<legend>Current Courses:</legend>
<?php
  $result=$dbcon->query("Select num,fall,spring from class where dept='$dept'");
  if (mysqli_num_rows($result)>0) {
    
    echo '<table class="table table-striped table-responsive text-center table-hover">';
    echo '<th class="text-center">Class #</th><th class="text-center">Fall</th><th class="text-center">Spring</th>';
    if ($_SESSION['admin']==1){
      echo "<th class=\"text-center\">Delete</th>";
    }

    while($row = $result->fetch_assoc()) {
       echo "<tr><td>";
       echo $row['num'];
       echo "</td><td>";
       echo $row['fall'];
       echo "</td><td>";
       echo $row['spring'];
       echo "</td>";
       if ($_SESSION['admin']==1){
        echo "<form method=\"POST\" action=\"courses.php?delete=1&num=".$row['num']."\"><td>";
        echo "<button type=\"submit\" class=\"btn btn-sm btn-danger\">Delete</button></td></form>";
      }
       echo "</tr>";
    }

    echo "</table>";
  } else {
    echo "<h4>No Classes Currently Stored</h4>";
  }	
  echo "</div>
      </div>
		</div>
		<div class=\"tab-pane\" id=\"time\">
		  <div class=\"row\" style=\"margin-top:20px;\">
        <div class=\"col-xs-12 col-md-12\">";
  
  $result2=$dbcon->query("Select num,one_day,two_day,three_day,five_day from class where dept='$dept'");
  if (mysqli_num_rows($result2)>0) {
    echo "<table class=\"table table-striped table-responsive text-center table-hover\" style=\"width: 60%;margin-left: auto;margin-right: auto;\">";
    if ($_SESSION['admin']==1){
      echo "<th class=\"text-center\">Edit</th>";
    }
    echo "<th class=\"text-center\">Class Num</th><th class=\"text-center\">1 day/wk</th><th class=\"text-center\">2 day/wk</th><th class=\"text-center\">3 day/wk</th><th class=\"text-center\">5 day/wk</th>";
    echo "<form><fieldset>";
    while($row2 = mysqli_fetch_array($result2)) {
      echo "<tr><td>";
      if ($_SESSION['admin']==1){
        echo "<div type=\"button\" class=\"glyphicon glyphicon-pencil\" style=\"color: green;\" data-toggle=\"modal\" data-target=\"#exampleModal\" data-whatever=\"".$row2['num']."\"></div></td><td>";
      }
       echo $row2['num'];
       echo "</td><td>";
       if ($row2['one_day']==1) {
         echo "<div class=\"glyphicon glyphicon-check\" style=\"font-size: 1.4em;\"></div>";
       }
       else {
         echo "<div class=\"glyphicon glyphicon-unchecked\" style=\"font-size: 1.4em;\"></div>";
       }
       echo "</td><td>";
       if ($row2['two_day']==1) {
        echo "<div class=\"glyphicon glyphicon-check\" style=\"font-size: 1.4em;\"></div>";
       }
       else {
         echo "<div class=\"glyphicon glyphicon-unchecked\" style=\"font-size: 1.4em;\"></div>";
       }
       echo "</td><td>";
       if ($row2['three_day']==1) {
        echo "<div class=\"glyphicon glyphicon-check\" style=\"font-size: 1.4em;\"></div>";
       }
       else {
         echo "<div class=\"glyphicon glyphicon-unchecked\" style=\"font-size: 1.4em;\"></div>";
       }
       echo "</td><td>";
       if ($row2['five_day']==1) {
        echo "<div class=\"glyphicon glyphicon-check\" style=\"font-size: 1.4em;\"></div>";
       }
       else {
         echo "<div class=\"glyphicon glyphicon-unchecked\" style=\"font-size: 1.4em;\"></div>";
       }
       echo "</td></tr>";
    }
    echo "</fieldset></form></table>";
  } else {
    echo "<h4 class=\"text-center\">No Classes Currently Stored</h4>";
  }
  echo "</div></div>
				</div>
        <div class=\"tab-pane\" id=\"instructor\">
					<div class=\"row\" style=\"margin-top: 20px;\">";

  if ($_SESSION['admin']==1){
    echo "<div class=\"col-xs-12 col-md-4 text-center\">
            <legend>Add Restrictions</legend>
            <div class=\"col-md-6 col-md-offset-3\">
              <form id=\"classInstrForm\" method=\"GET\" action=\"courses.php\">
                <div class=\"form-group\">
                <label>Class Number #:</label><br>
      <select class=\"form-control\" name=\"num\" id=\"num\" form=\"classInstrForm\" required>";
              $result=$dbcon->query("select num from class where dept='$dept'");

              while($row = $result->fetch_assoc()) {
                 echo "<option value=\"".$row['num']."\">";
                 echo $row['num'];
                 echo "</option>";
              }
    echo "</select>
                  </div>
                  <div class=\"form-group\">
                <label>Instructor:</label><br>
                  <select class=\"form-control\" name=\"initials\" id=\"initials\" form=\"classInstrForm\" required>";
                    $result=mysqli_query($dbcon,"select initials from user where active = 1 and dept='$dept'");

                    while($row = $result->fetch_assoc()) {
                       echo "<option value=\"".$row['initials']."\">";
                       echo $row['initials'];
                       echo "</option>";
                    }
              echo "</select>

      </div>
                <button class=\"btn btn-primary\" type=\"submit\" style=\"margin-top: 5px;\">Add Restriction</button>
              </form>
              </div>
            </div>";
      echo "<div class=\"col-xs-12 col-md-8 text-center\">";
} else {
  echo "<div class=\"col-xs-12 col-md-6 col-md-offset-3 text-center\">";
}
echo "<legend>Current Restrictions</legend>";
            
$result=$dbcon->query("Select num,GROUP_CONCAT(initials separator ', ') as initials from prof_class where dept='$dept' group by num");
if (mysqli_num_rows($result)>0) {
  echo "<table class=\"table table-striped table-responsive text-center table-hover\">
                  <th class=\"text-center\">Class Num</th>
                  <th class=\"text-center\">Instructors</th>";
  if ($_SESSION['admin']==1){
    echo "<th class=\"text-center\">Delete</th>";
  }
                  
  while($row = $result->fetch_assoc()) {
     echo "<tr><td>";
     echo $row['num'];
     echo "</td><td>";
     echo $row['initials'];
     echo "</td>";
     if ($_SESSION['admin']==1){
      echo "<form method=\"POST\" action=\"courses.php?delete=1&num=".$row['num'];
      $inits = explode(", ",$row['initials']);
      for ($i=0; $i<count($inits); $i++) {
        echo "&initials[]=".$inits[$i];
      }
        echo "\"><td>";
      echo "<button type=\"submit\" class=\"btn btn-sm btn-danger\">Delete</button></td></form>";
     }
      echo "</tr>";
   }
  echo "</table>";
} else {
  echo "<h4>No Current Class Restrictions</h4>";
}
echo "    </div>
				</div>
			</div>
</div>
</body>
<script>
  $('#exampleModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget) // Button that triggered the modal
    var recipient = button.data('whatever') // Extract info from data-* attributes
    var modal = $(this)
    modal.find('.modal-title').text('Course ' + recipient)
    modal.find('.modal-body input').val(recipient)
  })
</script>
</html>";
?>
