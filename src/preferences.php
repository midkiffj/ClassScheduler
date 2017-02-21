<?php

  $title = 'Preferences';
  $active = 'preferences';

  include $_SERVER['DOCUMENT_ROOT'].'/classScheduler/navScripts.php';
  include $_SERVER['DOCUMENT_ROOT'].'/classScheduler/connection.php';
  $dept = $_SESSION['dept'];

  if ($_SESSION['username']!='midkiffj') {
    $sql= "Select exists(select * from user where initials='".$_SESSION['username']."' and dept='$dept' and faculty=1) as isFac";
    $facCheck=$dbcon->query($sql);
    while ($fac=$facCheck->fetch_assoc()) {
      if ($fac['isFac']==0) {
        header("Location: options.php");
      }
    }
  }

  $sql = "select pref from dept_options where dept='$dept'";
  $prefCheck = $dbcon->query($sql);
  while ($pref = $prefCheck->fetch_assoc()){
    if ($pref['pref'] == 0) {
      header("Location: options.php");
    }
  }


  $error = array();
  $errorSql = array();

  if (isset($_GET['FA1']) || isset($_GET['FA2']) || isset($_GET['FA3']) || isset($_GET['SP1']) || isset($_GET['SP2']) || isset($_GET['SP2'])) {
      $initials = $_SESSION['username'];
      $sql = "delete from prof_pref where initials='$initials' and dept='$dept'";
      $result=$dbcon->query($sql);
      if (!$result) {
        $error[] = $dbcon->error;
        $errorSql[] = $sql;
      }
      $sql = array();
      $psql = "insert into prof_pref values('$initials','$dept',";
      if (isset($_GET['FA1']) && $_GET['FA1']!='') {
        $num = $_GET['FA1'];
        $sql[] = $psql."$num,1)";
      }
      if (isset($_GET['FA2']) && $_GET['FA2']!='') {
        $num = $_GET['FA2'];
        $sql[] = $psql."$num,1)";
      }
      if (isset($_GET['FA3']) && $_GET['FA3']!='') {
        $num = $_GET['FA3'];
        $sql[] = $psql."$num,1)";
      }
      if (isset($_GET['SP1']) && $_GET['SP1']!='') {
        $num = $_GET['SP1'];
        $sql[] = $psql."$num,2)";
      }
      if (isset($_GET['SP2']) && $_GET['SP2']!='') {
        $num = $_GET['SP2'];
        $sql[] = $psql."$num,2)";
      }
      if (isset($_GET['SP3']) && $_GET['SP3']!='') {
        $num = $_GET['SP3'];
        $sql[] = $psql."$num,2)";
      }
      for ($i=0; $i<count($sql); $i++){
        $result = $dbcon->query($sql[$i]);
        if (!$result) {
          $error[] = $dbcon->error;
          $errorSql[] = $sql[$i];
        }
      }
  }

  echo '<div class="container">';

  if (count($error) > 0) {
    include $_SERVER['DOCUMENT_ROOT'].'/classScheduler/error.php';
  }
?>
  <h1>Preferences</h1>
  <hr>
  <div class="col-md-6">
    <legend>Enter Your Preferences:</legend>
  	<form method="GET" action="preferences.php">
      <div class="form-inline">
      	<div class="form-group">
          <label>Fall Courses:</label><br>
        	  <input class="form-control" type="number" id="FA1" name="FA1" max="999" style="width: 4em;">
         	  <input class="form-control" type="number" id="FA2" name="FA2" max="999" style="width: 4em;">
         	  <input class="form-control" type="number" id="FA3" name="FA3" max="999" style="width: 4em;">
        </div>
      </div>
      <div class="form-inline">
        <div class="form-group">
          <label>Spring Courses:</label><br>
          <input class="form-control" type="number" id="SP1" name="SP1" max="999" style="width: 4em;">
          <input class="form-control" type="number" id="SP2" name="SP2" max="999" style="width: 4em;">
          <input class="form-control" type="number" id="SP3" name="SP3" max="999" style="width: 4em;">
        </div>
      </div>
    	<button type="submit" class="btn btn-warning" style="margin-top: 10px;">Update</button>
  	</form>
  </div>	
  <div class="col-md-6">
  	<legend>Current Preferences:</legend>
    <?php
      $sql= "Select exists(select * from prof_pref where initials='".$_SESSION['username']."' and dept='".$_SESSION['dept']."') as hasPrefs";
      $prefCheck=$dbcon->query($sql);
      $hasPref = 0;
      while ($pref=$prefCheck->fetch_assoc()) {
        $hasPref = $pref['hasPrefs'];
      }
      if ($hasPref) {
        $fall=$dbcon->query("Select GROUP_CONCAT(num separator ', ') as num from prof_pref where initials='".$_SESSION['username']."' and dept='$dept' and sem = 1");
        $spring=$dbcon->query("Select GROUP_CONCAT(num separator ', ') as num from prof_pref where initials='".$_SESSION['username']."' and dept='$dept' and sem = 2");
        echo '<table class="table table-striped table-hover text-center table-responsive">';
        echo '<th class="text-center">Fall</th><th class="text-center">Spring</th>';
        echo "<tr><td>";
        if ($row = $fall->fetch_assoc()){
          echo $row['num'];
        }
        echo "</td><td>";
        if ($row = $spring->fetch_assoc()){
          echo $row['num'];
        }
        echo "</td></tr></table>";
      } else {
        echo "<h4>No Preferences Currently Stored</h4>";
      }
    ?>
    </table>
  </div>	
</div>
</body>

</html>
