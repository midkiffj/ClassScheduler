
<?php

  $title = 'Program';
  $active = 'program';

  include $_SERVER['DOCUMENT_ROOT'].'/classScheduler/navScripts.php';
  include $_SERVER['DOCUMENT_ROOT'].'/classScheduler/connection.php';
  $dept = $_SESSION['dept'];
  //optimize with SCIP and parse output
?>

<div class="container">
  <h1 class="text-center">Program</h1>
  <hr>
  <h2 class="text-center">
    <i class="fa fa-spinner fa-pulse"></i>
  </h2>
</div>
</body>
</html>

<?php
  
  $file = "/tmp/".$dept."output.txt";
  $handle = fopen($file, "r");
  if ($handle) {
    unlink($file);
  }

  //run SCIP with LP file and pipe to output file
  $cmd = "/usr/local/bin/scip-3.0.2.linux.x86_64.gnu.opt.spx -f /tmp/".$dept."model.lp > /tmp/".$dept."output.txt";
  exec($cmd);

  //delete any entries in teaching tables for new data
  $sql="DELETE FROM teaching where dept='$dept'";
  if (!$dbcon->query($sql)) {
    	die('Error: ' .mysqli_error($dbcon));
  }

  //parse output file
  $handle = fopen($file, "r");
  $feas=0;
  //if file failed to open sleep
  while($handle==FALSE){
    sleep(3);
    $handle=fopen($file,"r");
  }
  while (($line = fgets($handle)) !== false){
      $sub = substr($line, 0, 2);
      if(strcmp($sub, "x(")==0 || strcmp($sub, "y(")==0){//teaching
        $feas=1;
        $output = explode(",", $line);
        $timeslot = substr($output[0],2);
        $num = $output[1];
        $initials = $output[2];
        $sem = substr($output[3],0,1);
        //put results into tutoring table
        $sql="INSERT INTO teaching(initials,dept,num,id,sem) 
          VALUES('$initials', '$dept', $num, $timeslot, $sem)";
        if (!$dbcon->query($sql)) {
    	   die('Error: ' .mysqli_error($dbcon));
        }
      } elseif(strcmp($sub, "w(")==0){
        $output = explode(",", $line);
        $timeslot = substr($output[0],2);
        $num = $output[1];
        $sem = substr($output[2],0,1);
        $sql="INSERT INTO teaching(initials,dept,num,id,sem) 
          VALUES('STAFF', '$dept', $num, $timeslot, $sem)";
        if (!$dbcon->query($sql)) {
         die('Error: ' .mysqli_error($dbcon));
        }
      }
  }

  if($feas==0){//infeasible -- run with alternative LP file
    //shell_exec('php alternative.php');
    // print('INFEASIBLE SOLUTION');
    //require('alternative.php');
  }

  //close the connection
  // mysqli_close($dbcon);

?>
<META http-equiv="refresh" content="10;URL=http://mathcsdev.dickinson.edu/classScheduler/program.php">
