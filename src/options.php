<?php
  session_start();

  $error = array();
  $errorSql = array();

  $con = new mysqli("localhost","peeping","tom","span_scheduler");
  if ((!isset($_SESSION['dept'])) && isset($_SESSION['username'])) {
    // $sql = "select dept from user where initials='".$_SESSION['username']."' and dept<>'TEST'";
     $sql = "select dept from user where initials='".$_SESSION['username']."'";
    $result = $con->query($sql);
    $row = $result->fetch_assoc();
    $_SESSION['dept'] = $row['dept'];
    $sql= "Select exists(select * from user where initials='".$_SESSION['username']."' and dept='".$_SESSION['dept']."' and admin=1) as isAdmin";
    $adminCheck=$con->query($sql);
    if (!$result) {
      $error[] = $con->error;
      $errorSql[] = $sql;
    }
    while ($admin=$adminCheck->fetch_assoc()) {
      if ($admin['isAdmin']==0) {
    	  $_SESSION['dbUser'] = "regularguy";
    	  $_SESSION['dbPass'] = "forprefs";
        $_SESSION['admin']=0;
      }else {
    	  $_SESSION['dbUser'] = "bruce";
    	  $_SESSION['dbPass'] = "almighty";
        $_SESSION['admin']=1;
      } 
    }
  }

  if (isset($_GET['dept'])) {
    $_SESSION['dept'] = $_GET['dept'];
    $sql= "Select exists(select * from user where initials='".$_SESSION['username']."' and dept='".$_SESSION['dept']."' and admin=1) as isAdmin";
    $adminCheck=$con->query($sql);
    if (!$adminCheck) {
      $error[] = $con->error;
      $errorSql[] = $sql;
    }
    while ($admin=$adminCheck->fetch_assoc()) {
      if ($admin['isAdmin']==0) {
    	  $_SESSION['dbUser'] = "regularguy";
    	  $_SESSION['dbPass'] = "forprefs";
        $_SESSION['admin']=0;
      }else {
    	  $_SESSION['dbUser'] = "bruce";
    	  $_SESSION['dbPass'] = "almighty";
        $_SESSION['admin']=1;
      } 
    }
  }

  $title = 'Options';
  include $_SERVER['DOCUMENT_ROOT'].'/classScheduler/navScripts.php';
  include $_SERVER['DOCUMENT_ROOT'].'/classScheduler/connection.php';
  $dept = $_SESSION['dept'];
  $showPrefs = 1;
  $sql = "select pref from dept_options where dept='$dept'";
  $prefCheck = $con->query($sql);
  while ($pref = $prefCheck->fetch_assoc()){
    $showPrefs = $pref['pref'];
  }
  echo '<div class="container">';

  if (count($error) > 0) {
    include $_SERVER['DOCUMENT_ROOT'].'/classScheduler/error.php';
  }
?>

  <div class="row">
    <h2 class="text-center">Your Options</h2>
    <hr>
    <?php
      $deptSwitch = '<div class="panel panel-default">
                      <div class="panel-body">
                        <legend class="text-center">Department Switch</legend>
                        <p>Change between your departments.
                        </p>
                        <form class="form-inline" id="deptForm" name="deptForm" method="GET" action="options.php">
                          <div class="form-group">
                            <label for="dept">Department:</label>
                            <select class="form-control" name="dept" id="dept" form="deptForm" required>";';
      // if ($_SESSION['username']=='midkiffj'){
        $sql = "select dept from user where initials='".$_SESSION['username']."'";
      // } else {
        // $sql = "select dept from user where initials='".$_SESSION['username']."' and dept<>'TEST'";
      // }
      $result = $dbcon->query($sql);
      while($row = $result->fetch_assoc()) {
         $deptSwitch .= "<option value=\"".$row['dept']."\">";
         $deptSwitch .= $row['dept'];
         $deptSwitch .= "</option>";
      }
      $deptSwitch .= '</select>
                    </div>
                    <button class="btn btn-primary" type="submit" style="margin-top: 5px;">Change</button>
                  </form>
                </div>
              </div>';
      if ($showPrefs) {
        echo '<div class="col-md-6">
                <div class="pull-right col-xs-12 col-md-8">
                  '.$deptSwitch.'
                </div>
              </div>
              <div class="col-md-6">
                <div class="pull-left col-xs-12 col-md-8">
                  <form class="text-center" action="preferences.php">
                    <button class="panel panel-default submit">
                      <div class="panel-body">
                        <legend class="text-center">Preferences</legend>
                        <ul class="text-left">
                          <li>Add your preferences for the fall and spring.</li>
                          <li>View your previously entered information.</li>
                        </ul>
                      </div>
                    </button>
                  </form>
                </div>
              </div>';
      } else {
        echo '<div class="col-md-12">
                <div class="col-md-4 col-md-offset-4">';
        echo $deptSwitch;
        echo '</div></div>';
      }
    ?>
    
  </div>
  <div class="row">
    <h2 class="text-center">Scheduler Options</h2>
    <hr>
    <div class="col-xs-12 col-md-4">
      <form class="text-center" action="courses.php">
        <button class="panel panel-default submit">
          <div class="panel-body">
            <legend class="text-center">Courses</legend>
            <ul class="text-left">
              <li>Add classes for next year.</li>
              <li>Set time constraints on each class.</li>
              <li>Link professors to classes.</li>
            </ul>
          </div>
        </button>
      </form>
    </div>
    <div class="col-xs-12 col-md-4">
      <form class="text-center" action="instructors.php">
        <button class="panel panel-default submit">
          <div class="panel-body">
            <legend class="text-center">Instructors</legend>
            <ul class="text-left">
              <li>Add instructors course limits.</li>
              <li>View instructor class preferences.</li>
              <li>Limit timeslots that professors can work.</li>
            </ul>
          </div>
        </button>
      </form>
    </div>
    <div class="col-xs-12 col-md-4">
      <form class="text-center" action="program.php">
        <button class="panel panel-default submit">
          <div class="panel-body">
            <legend class="text-center">Program</legend>
            <ul class="text-left">
              <li>View data totals about next year.</li>
              <li>Generate next year's schedule.</li>
              <li>View the schedule and timeslot totals.</li>
            </ul>
          </div>
        </button>
      </form>
    </div>
  </div>
  <?php
    if ($_SESSION['admin']!=1) {
      die();
    }
  ?>
  <div class="row">
    <h2 class="text-center">Admin Options</h2>
    <hr>
    <div class="col-md-6">
      <div class="pull-right col-xs-12 col-md-8">
        <form class="text-center" action="adminDepartment.php">
          <button class="panel panel-default submit">
            <div class="panel-body">
              <legend class="text-center">Department</legend>
              <ul class="text-left">
                <li>Add instructors for next year.</li>
                <li>Set the current academic year.</li>
                <li>Remove current instructors from the schedule.</li>
              </ul>
            </div>
          </button>
        </form>
      </div>
    </div>
    <div class="col-md-6">
      <div class="pull-left col-xs-12 col-md-8">
        <form class="text-center" action="adminAdmins.php">
          <button class="panel panel-default submit">
            <div class="panel-body">
              <legend class="text-center">Admins</legend>
              <ul class="text-left">
                <li>View current admins for your department.</li>
                <li>Add a current instructor as an admin.</li>
                <li>Remove a current admin.</li>
              </ul>
            </div>
          </button>
        </form>
      </div>
    </div>
  </div>

</div>
</body>

</html>
