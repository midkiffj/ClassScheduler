
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

//writes in LP format to model.lp using data in database
$file = "/tmp/model.lp";
$handle = fopen($file, "r");
if ($handle) {
  unlink($file);
}
// // Open desired file
// $file = "/var/www/html/classScheduler/model.lp";
$file = "/tmp/".$dept."model.lp";
// $file = "/tmp/model.lp";
$handle = fopen($file, "r");
if ($handle) {
  unlink($file);
}
$file_handle = fopen($file, 'w') or die('Failed to open model');

// Get initials for all profesors
$sql="SELECT initials FROM user where faculty=1 and active = 1 and dept='$dept'";
$result = $dbcon->query($sql);
$num_profs = mysqli_num_rows($result);
$profs = array();
while($row = $result->fetch_assoc()){
  $profs[] = $row['initials'];
}

// Get initials for all visit_adj
$sql="SELECT initials FROM user where visit_adj=1 and active = 1 and dept='$dept'";
$result = $dbcon->query($sql);
$num_visit_adj = mysqli_num_rows($result);
$visit_adj = array();
while($row = $result->fetch_assoc()){
  $visit_adj[] = $row['initials'];
}

// Get num, fall, and spring count for all classes
$sql="SELECT num, fall, spring FROM class where dept='$dept' order by num";
$result = $dbcon->query($sql);
$num_classes = mysqli_num_rows($result);
$classes = array();
$fall_count = array();
$spring_count = array();
while($row = $result->fetch_assoc()){
  $classes[] = $row['num'];
  $fall_count[] = $row['fall'];
  $spring_count[] = $row['spring'];
}

// Get fall/spring count restrictions for professors
$sql="SELECT initials, fall, spring FROM prof_sems WHERE dept='$dept' ORDER BY initials";
$result = $dbcon->query($sql);
$semInits = array();
$semFall = array();
$semSpring = array();
while($row = $result->fetch_assoc()){
  $semInits[] = $row['initials'];
  $semFall[] = $row['fall'];
  $semSpring[] = $row['spring'];
}

$usePrefs = 1;
$sql = "select pref from dept_options where dept='$dept'";
$prefCheck = $dbcon->query($sql);
while ($pref = $prefCheck->fetch_assoc()){
  $usePrefs = $pref['pref'];
}

if ($usePrefs) {
  // Get fall preferences for all faculty
  $sql="SELECT initials, GROUP_CONCAT(num separator ',') as num FROM prof_pref WHERE sem = 1 and dept='$dept' group by initials";
  $result = $dbcon->query($sql);
  $fp_facs = array();
  $fp_nums = array();
  while($row = $result->fetch_assoc()){
    $fp_facs[] = $row['initials'];
    $fp_nums[] = $row['num'];
  }

  // Get spring preferences for all faculty
  $sql="SELECT initials, GROUP_CONCAT(num separator ',') as num FROM prof_pref WHERE sem = 2 and dept='$dept' group by initials";
  $result = $dbcon->query($sql);
  $sp_facs = array();
  $sp_nums = array();
  while($row = $result->fetch_assoc()){
    $sp_facs[] = $row['initials'];
    $sp_nums[] = $row['num'];
  }
}



// Get five day classes (nums and fall/spring count)
$sql="SELECT num, fall, spring FROM class where one_day = 0 and two_day = 0 and three_day = 0 and five_day = 1 and dept='$dept' order by num";
$result = $dbcon->query($sql);
$num_fives = mysqli_num_rows($result);
$five_day_classes = array();
$five_fall_count = array();
$five_spring_count = array();
while($row = $result->fetch_assoc()){
  $five_day_classes[] = $row['num'];
  $five_fall_count[] = $row['fall'];
  $five_spring_count[] = $row['spring'];
}

// Get one day classes (nums and fall/spring count)
$sql="SELECT num, fall, spring FROM class where one_day = 1 and two_day = 0 and three_day = 0 and five_day = 0 and dept='$dept' order by num";
$result = $dbcon->query($sql);
$one_day_classes = array();
$one_fall_count = array();
$one_spring_count = array();
while($row = $result->fetch_assoc()){
  $one_day_classes[] = $row['num'];
  $one_fall_count[] = $row['fall'];
  $one_spring_count[] = $row['spring'];
}

// Get two/three day classes
$sql="SELECT num FROM class where one_day = 0 and five_day = 0 and dept='$dept' order by num";
$result = $dbcon->query($sql);
$num_regs = mysqli_num_rows($result);
$reg_classes = array();
while($row = $result->fetch_assoc()){
  $reg_classes[] = $row['num'];
}

// Get two day classes (nums and fall/spring count)
$sql="SELECT num, fall, spring FROM class where one_day = 0 and two_day = 1 and three_day = 0 and five_day = 0 and dept='$dept' order by num";
$result = $dbcon->query($sql);
$two_day_classes = array();
$two_fall_count = array();
$two_spring_count = array();
while($row = $result->fetch_assoc()){
  $two_day_classes[] = $row['num'];
  $two_fall_count[] = $row['fall'];
  $two_spring_count[] = $row['spring'];
}

// Get three day classes (num and fall/spring count)
$sql="SELECT num, fall, spring FROM class where one_day = 0 and two_day = 0 and three_day = 1 and five_day = 0 and dept='$dept' order by num";
$result = $dbcon->query($sql);
$three_day_classes = array();
$three_fall_count = array();
$three_spring_count = array();
while($row = $result->fetch_assoc()){
  $three_day_classes[] = $row['num'];
  $three_fall_count[] = $row['fall'];
  $three_spring_count[] = $row['spring'];
}

//Get faculty timeslot restrictions
$sql="SELECT initials, GROUP_CONCAT(id separator ',') as id FROM prof_time natural join user where faculty = 1 and dept='$dept' group by initials order by initials, id";
$result = $dbcon->query($sql);
$fac_restr = array();
$fac_time = array();
while($row = $result->fetch_assoc()){
  $fac_restr[] = $row['initials'];
  $fac_time[] = $row['id'];
}

//Get visit_adj timeslot restrictions
$sql="SELECT initials, GROUP_CONCAT(id separator ',') as id FROM prof_time natural join user where visit_adj = 1 and dept='$dept' group by initials order by initials, id";
$result = $dbcon->query($sql);
$va_restr = array();
$va_time = array();
while($row = $result->fetch_assoc()){
  $va_restr[] = $row['initials'];
  $va_time[] = $row['id'];
}

//Get faculty class restrictions
$sql="SELECT num, GROUP_CONCAT(initials separator ',') as initials FROM prof_class natural join user where faculty = 1 and dept='$dept' group by num order by num, initials";
$result = $dbcon->query($sql);
$fac_nums = array();
$fac_inits = array();
while($row = $result->fetch_assoc()){
  $fac_nums[] = $row['num'];
  $fac_inits[] = $row['initials'];
}

//Get visit_adj class restrictions
$sql="SELECT num, GROUP_CONCAT(initials separator ',') as initials FROM prof_class natural join user where visit_adj = 1 and dept='$dept'  group by num order by num, initials";
$result = $dbcon->query($sql);
$va_nums = array();
$va_inits = array();
while($row = $result->fetch_assoc()){
  $va_nums[] = $row['num'];
  $va_inits[] = $row['initials'];
}

//timeslot vars
$timeslots = 20;
$reg_timeslots = [1,2,3,4,5,6,7,8,9,10,11];
$two_timeslots = [6,7,8,9,10,11];
$three_timeslots = [1,2,3,4,5];
$five_timeslots = [12,13,14,15];
$one_timeslots = [16,17,18,19,20];

$morning_timeslots = [1,2,3,4,10,11,12,13,14];
$afternoon_timeslots = [5,6,7,8,9,15,16,17,18,19,20];

$three_row1 = [1,12];
$three_row2 = [2,13];
$three_row3 = [3,14];
$three_row4 = [4,15];
$three_row5 = [5];

//overlap timeslots
$overlap_times1 = [ 1, 2, 3, 6, 6, 6, 7, 7, 8, 8, 8, 9, 9,10,10,11,15,15,15,15,15];
$overlap_times2 = [12,13,14,15,16,19,16,19,15,17,20,17,20,12,13,14,16,17,18,19,20];
$overlap_times = count($overlap_times1);

//semester set
$sem = 2;

// Get Reg timeslot desired average count
$sql="SELECT sum(fall) as sum FROM class where one_day = 0 and five_day = 0 and dept='$dept'";
$result = $dbcon->query($sql);
$fall_reg_avg = count($reg_timeslots);
$num_reg_fall = 0;
while($row = $result->fetch_assoc()){
  $num_reg_fall = (int)$row['sum'];
  $fall_reg_avg = (int)($row['sum'] / $fall_reg_avg);
}
$sql="SELECT sum(spring) as sum FROM class where one_day = 0 and five_day = 0 and dept='$dept'";
$result = $dbcon->query($sql);
$spring_reg_avg = count($reg_timeslots);
$num_reg_spring = 0;
while($row = $result->fetch_assoc()){
  $num_reg_spring = (int)$row['sum'];
  $spring_reg_avg = (int)($row['sum'] / $spring_reg_avg);
}

// Get five timeslot desired average count
$sql="SELECT sum(fall) as sum FROM class where one_day = 0 and two_day = 0 and three_day = 0 and five_day = 1 and dept='$dept'";
$result = $dbcon->query($sql);
$fall_five_avg = count($five_timeslots);
while($row = $result->fetch_assoc()){
  $fall_five_avg = (int)($row['sum'] / $fall_five_avg);
}
$sql="SELECT sum(spring) as sum FROM class where one_day = 0 and two_day = 0 and three_day = 0 and five_day = 1 and dept='$dept'";
$result = $dbcon->query($sql);
$spring_five_avg = count($five_timeslots);
while($row = $result->fetch_assoc()){
  $spring_five_avg = (int)($row['sum'] / $spring_five_avg);
}

// // Get one timeslot desired average count
// $sql="SELECT sum(fall+spring) FROM class where one_day = 1 and two_day = 0 and three_day = 0 and five_day = 0 and dept='$dept' order by num";
// $result = $dbcon->query($sql);
// $one_avg = count($one_timeslots);
// while($row = $result->fetch_assoc()){
//   $one_avg = int($row['sum'] / $one_avg);
// }


fwrite($file_handle, "\ Using Xpress-MP extensions\n") or die('Xpress write failed');

fwrite($file_handle, "Maximize\n") or die('Max write failed');

//objective function

// Faculty vars
$FOBJ = "\n";
for($f=0; $f<$num_profs; $f++){
  for($s=1; $s<=$sem; $s++){
    $key = false;
    $nums = array();
    if ($s==1) {
      if (in_array($profs[$f],$fp_facs)) {
        $key = array_search($profs[$f],$fp_facs);
        $nums = explode(",",$fp_nums[$key]);
      }
    } else {
      if (in_array($profs[$f],$sp_facs)) {
        $key = array_search($profs[$f],$sp_facs);
        $nums = explode(",",$sp_nums[$key]);
      }
    }
    for($t=1; $t<=$timeslots; $t++){
      for($c=0; $c<$num_classes; $c++){
        $class_coef = (int)($classes[$c] / 100);
        if ($f == 0 && $s == 1 && $t == 1 && $c == 0) {
          $FOBJ .= "$class_coef x($t,$classes[$c],$profs[$f],$s)";
        }
        else {
          $FOBJ .= " + $class_coef x($t,$classes[$c],$profs[$f],$s)";
        }
      }
      if (count($nums) > 0) {
        for($i=0; $i<count($nums); $i++){
          $FOBJ .= " + 2 x($t,$nums[$i],$profs[$f],$s)";
        }
      }
      $FOBJ .= "\n";
    }
    $FOBJ .= " - 3 fpo($profs[$f],$s) - fpu($profs[$f],$s) - a($profs[$f],$s) - m($profs[$f],$s) - d($profs[$f],$s) - 3 trf($profs[$f],$s)\n";
  }
}
fwrite($file_handle, $FOBJ."\n") or die('FOBJ write failed');

// VisitAdj vars
$VAOBJ = "\n";
for($v=0; $v<$num_visit_adj; $v++){
  for($s=1; $s<=$sem; $s++){
    $VAOBJ .= " - 3 vpo($visit_adj[$v],$s) - fpu($profs[$f],$s) - b($visit_adj[$v],$s) - n($visit_adj[$v],$s) - e($visit_adj[$v],$s) - 3 trv($visit_adj[$v],$s)";
  }
}
fwrite($file_handle, $VAOBJ."\n") or die('VAOBJ write failed');

// STAFF vars
$SOBJ = "\n";
for($s=1; $s<=$sem; $s++){
  for($t=1; $t<=$timeslots; $t++){
    for($c=0; $c<$num_classes; $c++){
      $SOBJ .= " - 10 w($t,$classes[$c],$s) - 4 ctd($t,$classes[$c],$s) - 0 ctb($t,$classes[$c],$s)";
    }
    $SOBJ .= "\n";
  }
}
fwrite($file_handle, $SOBJ."\n") or die('SOBJ write failed');

// SemRegSpread vars + RegTimeSpread vars
$SRSOBJ = "\n";
for($s=1; $s<=$sem; $s++){
  for($t=0; $t<count($reg_timeslots); $t++){
    $SRSOBJ .= " - srso($s,$reg_timeslots[$t]) - srsu($s,$reg_timeslots[$t]) - 30 rts($reg_timeslots[$t],$s)";
  }
}
fwrite($file_handle, $SRSOBJ."\n") or die('SRSOBJ write failed');

// SemRegSpread vars
$SFSOBJ = "\n";
for($s=1; $s<=$sem; $s++){
  for($t=0; $t<count($five_timeslots); $t++){
    $SFSOBJ .= " - sfso($s,$five_timeslots[$t]) - sfsu($s,$five_timeslots[$t])";
  }
}
fwrite($file_handle, $SFSOBJ."\n") or die('SFSOBJ write failed');

fwrite($file_handle, "Subject To\n") or die('ST write Failed');

// Faculty 5 courses per year
$FYM = "\n";
for($f=0; $f<$num_profs; $f++){
  if (!(in_array($profs[$f],$semInits))) {
    $FYM .= "FacultyYearMax($profs[$f]): ";
    for($t=1; $t<=$timeslots; $t++){
      for($c=0; $c<$num_classes; $c++){
        for($s=1; $s<=$sem; $s++){
          if($t == 1 && $c == 0 && $s == 1) {
            $FYM .= "x($t,$classes[$c],$profs[$f],$s)";
          }
          else {
            $FYM .= " + x($t,$classes[$c],$profs[$f],$s)";
          }
        }
        $FYM .= "\n";
      }
      $FYM .= "\n";
    }
    $FYM .= " = 5\n";
  }
}
fwrite($file_handle, $FYM) or die('FYM write failed');

// Visit/Adj 6 courses per year
$VAYM = "\n";
for($v=0; $v<$num_visit_adj; $v++){
  if (!(in_array($visit_adj[$v],$semInits))) {
    $VAYM .= "VisitAdjYearMax($visit_adj[$v]): ";
    for($t=1; $t<=$timeslots; $t++){
      for($c=0; $c<$num_classes; $c++){
        for($s=1; $s<=$sem; $s++){
          if($t == 1 && $c == 0 && $s == 1) {
            $VAYM .= "y($t,$classes[$c],$visit_adj[$v],$s)";
          }
          else {
            $VAYM .= " + y($t,$classes[$c],$visit_adj[$v],$s)";
          }
        }
        $VAYM .= "\n";
      }
      $VAYM .= "\n";
    }
    $VAYM .= " <= 6\n";
  }
}
fwrite($file_handle, $VAYM) or die('VAYM write failed');

// Faculty 3 course max per semester
$FSMAX = "\n";
for($f=0; $f<$num_profs; $f++){
  if (!(in_array($profs[$f],$semInits))) {
    for($s=1; $s<=$sem; $s++){
      $FSMAX .= "FacultySemMax($profs[$f],$s): ";
      for($t=1; $t<=$timeslots; $t++){
        for($c=0; $c<$num_classes; $c++){
          if($t == 1 && $c == 0) {
            $FSMAX .= "x($t,$classes[$c],$profs[$f],$s)";
          }
          else {
            $FSMAX .= " + x($t,$classes[$c],$profs[$f],$s)";
          }
          $FSMAX .= "\n";
        }
        $FSMAX .= "\n";
      }
      $FSMAX .= " <= 3\n";
    }
  }
}
fwrite($file_handle, $FSMAX) or die('FSMAX write failed');

// Visit_Adj 3 course max per semester
$VASMAX = "\n";
for($v=0; $v<$num_visit_adj; $v++){
  if (!(in_array($visit_adj[$v],$semInits))) {
    for($s=1; $s<=$sem; $s++){
      $VASMAX .= "VisitAdjSemMax($visit_adj[$v],$s): ";
      for($t=1; $t<=$timeslots; $t++){
        for($c=0; $c<$num_classes; $c++){
          if($t == 1 && $c == 0) {
            $VASMAX .= "y($t,$classes[$c],$visit_adj[$v],$s)";
          }
          else {
            $VASMAX .= " + y($t,$classes[$c],$visit_adj[$v],$s)";
          }
        }
        $VASMAX .= "\n";
      }
      $VASMAX .= " <= 3\n";
    }
  }
}
fwrite($file_handle, $VASMAX) or die('VASMAX write failed');

// Faculty 2 course min per semester
$FSMIN = "\n";
for($f=0; $f<$num_profs; $f++){
  if (!(in_array($profs[$f],$semInits))) {
    for($s=1; $s<=$sem; $s++){
      $FSMIN .= "FacultySemMin($profs[$f],$s): ";
      for($t=1; $t<=$timeslots; $t++){
        for($c=0; $c<$num_classes; $c++){
          if($t == 1 && $c == 0) {
            $FSMIN .= "x($t,$classes[$c],$profs[$f],$s)";
          }
          else {
            $FSMIN .= " + x($t,$classes[$c],$profs[$f],$s)";
          }
        }
        $FSMIN .= "\n";
      }
      $FSMIN .= " >= 2\n";
    }
  }
}
fwrite($file_handle, $FSMIN) or die('FSMIN write failed');

// Faculty 1 course per timeslot
$FTMAX = "\n";
for($f=0; $f<$num_profs; $f++){
  for($s=1; $s<=$sem; $s++){
    for($t=1; $t<=$timeslots; $t++){
      $FTMAX .= "FacultyTimeslotMax($profs[$f],$s,$t): ";
      for($c=0; $c<$num_classes; $c++){
        if($c == 0) {
          $FTMAX .= "x($t,$classes[$c],$profs[$f],$s)";
        }
        else {
          $FTMAX .= " + x($t,$classes[$c],$profs[$f],$s)";
        }
      }
      $FTMAX .= " <= 1\n";
    }
  }
}
fwrite($file_handle, $FTMAX) or die('FTMAX write failed');

// Visit_Adj 1 course per timeslot
$VATMAX = "\n";
for($v=0; $v<$num_visit_adj; $v++){
  for($s=1; $s<=$sem; $s++){
    for($t=1; $t<=$timeslots; $t++){
      $VATMAX .= "VisitAdjTimeslotMax($visit_adj[$v],$s,$t): ";
      for($c=0; $c<$num_classes; $c++){
        if($c == 0) {
          $VATMAX .= "y($t,$classes[$c],$visit_adj[$v],$s)";
        }
        else {
          $VATMAX .= " + y($t,$classes[$c],$visit_adj[$v],$s)";
        }
      }
      $VATMAX .= " <= 1\n";
    }
  }
}
fwrite($file_handle, $VATMAX) or die('VATMAX write failed');

// Course offering equality
$CN = "\n";
for($c=0; $c<$num_classes; $c++){
  for($s=1; $s<=$sem; $s++){
    $CN .= "CourseNum($classes[$c],$s): ";
    for($t=1; $t<=$timeslots; $t++){
      for($f=0; $f<$num_profs; $f++){
        if($t == 1 && $f == 0) {
          $CN .= "x($t,$classes[$c],$profs[$f],$s)";
        }
        else {
          $CN .= " + x($t,$classes[$c],$profs[$f],$s)";
        }
      }
      $CN .= "\n";
      for($v=0; $v<$num_visit_adj; $v++){
          $CN .= " + y($t,$classes[$c],$visit_adj[$v],$s)";
      }
      $CN .= " + w($t,$classes[$c],$s)";
      $CN .= "\n";
    }
    if ($s == 1) {
      $CN .= " = $fall_count[$c]\n";
    }
    else {
      $CN .= " = $spring_count[$c]\n";
    }
  }
}
fwrite($file_handle, $CN) or die('CN write failed');


// Five day course in 5 day/wk timeslots
$FDC = "\n";
for($c=0; $c<count($five_day_classes); $c++){
   for($s=1; $s<=$sem; $s++){
     $FDC .= "FiveDayClasses($five_day_classes[$c],$s): ";
     for($t=0; $t<count($five_timeslots); $t++){
       for($f=0; $f<$num_profs; $f++){
         if($t==0 && $f==0){
           $FDC .= "x($five_timeslots[$t],$five_day_classes[$c],$profs[$f],$s)";
         }
         else {
           $FDC .= " + x($five_timeslots[$t],$five_day_classes[$c],$profs[$f],$s)";
         }
       }
       $FDC .= "\n";
       for($v=0; $v<$num_visit_adj; $v++){
         $FDC .= " + y($five_timeslots[$t],$five_day_classes[$c],$visit_adj[$v],$s)";
       }
       $FDC .= " + w($five_timeslots[$t],$five_day_classes[$c],$s)";
       $FDC .= "\n";
     }
     if ($s == 1) {
       $FDC .= " = $five_fall_count[$c]\n";
     }
     else {
       $FDC .= " = $five_spring_count[$c]\n";
     }
   }
 }
 fwrite($file_handle, $FDC) or die('FDC write failed');

// Five class spread

// One day course in 1 day/wk timeslots
$ODC = "\n";
for($c=0; $c<count($one_day_classes); $c++){
  for($s=1; $s<=$sem; $s++){
    $ODC .= "OneDayClasses($one_day_classes[$c],$s): ";
    for($t=0; $t<count($one_timeslots); $t++){
      for($f=0; $f<$num_profs; $f++){
        if($t==0 && $f==0){
          $ODC .= "x($one_timeslots[$t],$one_day_classes[$c],$profs[$f],$s)";
        }
        else {
          $ODC .= " + x($one_timeslots[$t],$one_day_classes[$c],$profs[$f],$s)";
        }
      }
      $ODC .= "\n";
      for($v=0; $v<$num_visit_adj; $v++){
        $ODC .= " + y($one_timeslots[$t],$one_day_classes[$c],$visit_adj[$v],$s)";
      }
      $ODC .= " + w($one_timeslots[$t],$one_day_classes[$c],$s)";
      $ODC .= "\n";
    }
    if ($s == 1) {
      $ODC .= " = $one_fall_count[$c]\n";
    }
    else {
      $ODC .= " = $one_spring_count[$c]\n";
    }
  }
}
fwrite($file_handle, $ODC) or die('ODC write failed');

//Reg courses not taught in 5 day/wk timeslots
$RFS = "\n";
for($c=0; $c<count($reg_classes); $c++){
  for($s=1; $s<=$sem; $s++) {
    $RFS .= "RegFiveSquish($reg_classes[$c],$s): ";
    for($t=0; $t<count($five_timeslots); $t++){
      for($f=0; $f<$num_profs; $f++){
        if($t==0 && $f==0){
          $RFS .= "x($five_timeslots[$t],$reg_classes[$c],$profs[$f],$s)";
        }
        else {
          $RFS .= " + x($five_timeslots[$t],$reg_classes[$c],$profs[$f],$s)";
        }
      }
      $RFS .= "\n";
      for($v=0; $v<$num_visit_adj; $v++){
        $RFS .= " + y($five_timeslots[$t],$reg_classes[$c],$visit_adj[$v],$s)";
      }
      $RFS .= "\n";
      $RFS .= " + w($five_timeslots[$t],$reg_classes[$c],$s)";
    }
    $RFS .= " <= 0\n";
  }
}
fwrite($file_handle, $RFS) or die('RFS write failed');

//Reg/5day courses not taught in one day timeslots
$non_one_classes = array_merge($reg_classes, $five_day_classes);
$ODS = "\n";
for($t=0; $t<count($one_timeslots); $t++){
  for($s=1; $s<=$sem; $s++) {
    $ODS .= "OneDaySquish($one_timeslots[$t],$s): ";
    for($c=0; $c<count($non_one_classes); $c++){
      for($f=0; $f<$num_profs; $f++){
        if($c==0 && $f==0){
          $ODS .= "x($one_timeslots[$t],$non_one_classes[$c],$profs[$f],$s)";
        }
        else {
          $ODS .= " + x($one_timeslots[$t],$non_one_classes[$c],$profs[$f],$s)";
        }
      }
      $ODS .= "\n";
      for($v=0; $v<$num_visit_adj; $v++){
        $ODS .= " + y($one_timeslots[$t],$non_one_classes[$c],$visit_adj[$v],$s)";
      }
      $ODS .= "\n";
      $ODS .= " + w($one_timeslots[$t],$non_one_classes[$c],$s)";
    }
    $ODS .= " <= 0\n";
  }
}
fwrite($file_handle, $ODS) or die('ODS write failed');

//Faculty Overlapping timeslots
$FTO = "\n";
for($o=0; $o<$overlap_times; $o++){
  for($f=0; $f<$num_profs; $f++){
    for($s=1; $s<=$sem; $s++){
      $FTO .= "FacultyTimeslotOverlap($o,$profs[$f],$s): ";
      for($c=0; $c<$num_classes; $c++){
        if ($c==0) {
          $FTO .= "x($overlap_times1[$o],$classes[$c],$profs[$f],$s) + x($overlap_times2[$o],$classes[$c],$profs[$f],$s)";
        }
        else {
          $FTO .= " + x($overlap_times1[$o],$classes[$c],$profs[$f],$s) + x($overlap_times2[$o],$classes[$c],$profs[$f],$s)";
        }
      }
      $FTO .= " <= 1\n";
    }
  }
}
fwrite($file_handle, $FTO) or die('FTO write failed');

// Visit_Adj Overlapping timeslots
$VATO = "\n";
for($o=0; $o<$overlap_times; $o++){
  for($v=0; $v<$num_visit_adj; $v++){
    for($s=1; $s<=$sem; $s++){
      $VATO .= "VisitAdjTimeslotOverlap($o,$visit_adj[$v],$s): ";
      for($c=0; $c<$num_classes; $c++){
        if ($c==0) {
          $VATO .= "y($overlap_times1[$o],$classes[$c],$visit_adj[$v],$s) + y($overlap_times2[$o],$classes[$c],$visit_adj[$v],$s)";
        }
        else {
          $VATO .= " + y($overlap_times1[$o],$classes[$c],$visit_adj[$v],$s) + y($overlap_times2[$o],$classes[$c],$visit_adj[$v],$s)";
        }
      }
      $VATO .= " <= 1\n";
    }
  }
}
fwrite($file_handle, $VATO) or die('VATO write failed');

//Course spread
$RTS = "\n";
for($t=0; $t<count($reg_timeslots); $t++){
  for($s=1; $s<=$sem; $s++) {
    $RTS .= "RegTimeSpread($reg_timeslots[$t],$s): ";
    for($c=0; $c<$num_classes; $c++){
      for($f=0; $f<$num_profs; $f++){
        if($c==0 && $f==0){
          $RTS .= "x($reg_timeslots[$t],$classes[$c],$profs[$f],$s)";
        }
        else {
          $RTS .= " + x($reg_timeslots[$t],$classes[$c],$profs[$f],$s)";
        }
      }
      $RTS .= "\n";
      for($v=0; $v<$num_visit_adj; $v++){
        $RTS .= " + y($reg_timeslots[$t],$classes[$c],$visit_adj[$v],$s)";
      }
      $RTS .= " + w($reg_timeslots[$t],$classes[$c],$s)";
      $RTS .= " + rts($reg_timeslots[$t],$s)";
      $RTS .= "\n";
    }
    if ($s==1) {
      if ($num_reg_fall >= count($reg_timeslots)){
        $RTS .= " >= 1\n";
      } else {
        $RTS .= " <= 1\n";
      }
    } else {
      if ($num_reg_spring >= count($reg_timeslots)){
        $RTS .= " >= 1\n";
      } else {
        $RTS .= " <= 1\n";
      }
    }
    
  }
}
fwrite($file_handle, $RTS) or die('RTS write failed');

// Faculty prep binaries
$FPB = "\n";
for($f=0; $f<$num_profs; $f++){
  for($c=0; $c<$num_classes; $c++){
    for($s=1; $s<=$sem; $s++){
      $FPB .= "FacultyPrepBin($profs[$f],$classes[$c],$s): ";
      for($t=1; $t<=$timeslots; $t++){
        if ($t == 1) {
          $FPB .= "x($t,$classes[$c],$profs[$f],$s)";
        }
        else {
          $FPB .= " + x($t,$classes[$c],$profs[$f],$s)";
        }
      }
      $FPB .= " - 3 p($profs[$f],$classes[$c],$s) <= 0\n";
    }
  }
}
fwrite($file_handle, $FPB) or die('FPB write failed');

// Visit_Adj prep binaries
$VAPB = "\n";
for($v=0; $v<$num_visit_adj; $v++){
  for($c=0; $c<$num_classes; $c++){
    for($s=1; $s<=$sem; $s++){
      $VAPB .= "VisitAdjPrepBin($visit_adj[$v],$classes[$c],$s): ";
      for($t=1; $t<=$timeslots; $t++){
        if ($t == 1) {
          $VAPB .= "y($t,$classes[$c],$visit_adj[$v],$s)";
        }
        else {
          $VAPB .= " + y($t,$classes[$c],$visit_adj[$v],$s)";
        }
      }
      $VAPB .= " - 3 q($visit_adj[$v],$classes[$c],$s) <= 0\n";
    }
  }
}
fwrite($file_handle, $VAPB) or die('VAPB write failed');

// Faculty prep limit
$FPL = "\n";
for($f=0; $f<$num_profs; $f++){
  for($c=0; $c<$num_classes; $c++){
    for($s=1; $s<=$sem; $s++){
      $FPL .= "FacultyPrepLimit($profs[$f],$classes[$c],$s): ";
      for($t=1; $t<=$timeslots; $t++){
        if ($t == 1) {
          $FPL .= "x($t,$classes[$c],$profs[$f],$s)";
        }
        else {
          $FPL .= " + x($t,$classes[$c],$profs[$f],$s)";
        }
      }
      $FPL .= " - p($profs[$f],$classes[$c],$s) >= 0\n";
    }
  }
}
fwrite($file_handle, $FPL) or die('FPL write failed');

// Visit_Adj prep limit
$VAPL = "\n";
for($v=0; $v<$num_visit_adj; $v++){
  for($c=0; $c<$num_classes; $c++){
    for($s=1; $s<=$sem; $s++){
      $VAPL .= "VisitAdjPrepBin($visit_adj[$v],$classes[$c],$s): ";
      for($t=1; $t<=$timeslots; $t++){
        if ($t == 1) {
          $VAPL .= "y($t,$classes[$c],$visit_adj[$v],$s)";
        }
        else {
          $VAPL .= " + y($t,$classes[$c],$visit_adj[$v],$s)";
        }
      }
      $VAPL .= " - q($visit_adj[$v],$classes[$c],$s) >= 0\n";
    }
  }
}
fwrite($file_handle, $VAPL) or die('VAPL write failed');

// Faculty prep counting
$FPC = "\n";
for($f=0; $f<$num_profs; $f++){
  for($s=1; $s<=$sem; $s++){
    $FPC .= "FacultyPrepCount($profs[$f],$s): ";
    for($c=0; $c<$num_classes; $c++){
      if ($c == 0) {
        $FPC .= "p($profs[$f],$classes[$c],$s)";
      }
      else {
        $FPC .= " + p($profs[$f],$classes[$c],$s)";
      }
    }
    $FPC .= " - fpo($profs[$f],$s) + fpu($profs[$f],$s) = 2\n";
  }
}
fwrite($file_handle, $FPC) or die('FPC write failed');

// Visit_Adj prep counting
$VAPC = "\n";
for($v=0; $v<$num_visit_adj; $v++){
  for($s=1; $s<=$sem; $s++){
    $VAPC .= "VisitAdjPrepCount($visit_adj[$v],$s): ";
    for($c=0; $c<$num_classes; $c++){
      if ($c == 0) {
        $VAPC .= "q($visit_adj[$v],$classes[$c],$s)";
      }
      else {
        $VAPC .= " + q($visit_adj[$v],$classes[$c],$s)";
      }
    }
    $VAPC .= " - vpo($visit_adj[$v],$s) + vpu($visit_adj[$v],$s) = 2\n";
  }
}
fwrite($file_handle, $VAPC) or die('VAPC write failed');

// Faculty class constraints
$FCE = "\n";
for($s=1; $s<=$sem; $s++){
  for($c=0; $c<count($fac_nums); $c++){
    $FacInits = explode(",",$fac_inits[$c]);
    $FCE .= "FacultyClassEnforce($fac_nums[$c],$s): ";
    for($f=0; $f<count($FacInits); $f++){
      for($t=1; $t<=$timeslots; $t++){
        if($f==0 && $t==1){
          $FCE .= "x($t,$fac_nums[$c],$FacInits[$f],$s)";
        }
        else {
          $FCE .= " + x($t,$fac_nums[$c],$FacInits[$f],$s)";
        }
      }
    }
    $key = array_search($fac_nums[$c], $classes);
    if ($s == 1) {
      $FCE .= " = $fall_count[$key]\n";
    }
    else {
      $FCE .= " = $spring_count[$key]\n";
    }
  }
}
fwrite($file_handle, $FCE) or die('FCE write failed');


// Visit/Adj class constraints
$VACE = "\n";
for($s=1; $s<=$sem; $s++){
  for($c=0; $c<count($va_nums); $c++){
    $VaInits = explode(",",$va_inits[$c]);
    $VACE .= "VisitAdjClassEnforce($va_nums[$c],$s): ";
    for($v=0; $v<count($VaInits); $v++){
      for($t=1; $t<=$timeslots; $t++){
        if($v==0 && $t==1){
          $VACE .= "y($t,$va_nums[$c],$VaInits[$v],$s)";
        }
        else {
          $VACE .= " + y($t,$va_nums[$c],$VaInits[$v],$s)";
        }
      }
    }
    $key = array_search($va_nums[$c], $classes);
    if ($s == 1) {
      $VACE .= " = $fall_count[$key]\n";
    }
    else {
      $VACE .= " = $spring_count[$key]\n";
    }
  }
}
fwrite($file_handle, $VACE) or die('VACE write failed');

// Faculty Morning
$FMB = "\n";
for($f=0; $f<$num_profs; $f++){
  for($s=1; $s<=$sem; $s++){
    $FMB .= "FacultyMorningBin($profs[$f],$s): ";
    for($m=0; $m<count($morning_timeslots); $m++) {
      for($c=0; $c<$num_classes; $c++){
        if ($m == 0 && $c == 0) {
          $FMB .= "x($morning_timeslots[$m],$classes[$c],$profs[$f],$s)";
        }
        else {
          $FMB .= " + x($morning_timeslots[$m],$classes[$c],$profs[$f],$s)";
        }
      }
      $FMB .= "\n";
    }
    $FMB .= " - 3 m($profs[$f],$s) <= 0\n";
  }
}
fwrite($file_handle, $FMB) or die('FMB write failed');

// VisitAdj Morning
$VAMB = "\n";
for($v=0; $v<$num_visit_adj; $v++){
  for($s=1; $s<=$sem; $s++){
    $VAMB .= "VisitAdjMorningBin($visit_adj[$v],$s): ";
    for($m=0; $m<count($morning_timeslots); $m++) {
      for($c=0; $c<$num_classes; $c++){
        if ($m == 0 && $c == 0) {
          $VAMB .= "y($morning_timeslots[$m],$classes[$c],$visit_adj[$v],$s)";
        }
        else {
          $VAMB .= " + y($morning_timeslots[$m],$classes[$c],$visit_adj[$v],$s)";
        }
      }
      $VAMB .= "\n";
    }
    $VAMB .= " - 3 n($visit_adj[$v],$s) <= 0\n";
  }
}
fwrite($file_handle, $VAMB) or die('VAMB write failed');

// Faculty Afternoon
$FAB = "\n";
for($f=0; $f<$num_profs; $f++){
  for($s=1; $s<=$sem; $s++){
    $FAB .= "FacultyAfternoonBin($profs[$f],$s): ";
    for($a=0; $a<count($afternoon_timeslots); $a++) {
      for($c=0; $c<$num_classes; $c++){
        if ($a == 0 && $c == 0) {
          $FAB .= "x($afternoon_timeslots[$a],$classes[$c],$profs[$f],$s)";
        }
        else {
          $FAB .= " + x($afternoon_timeslots[$a],$classes[$c],$profs[$f],$s)";
        }
      }
      $FAB .= "\n";
    }
    $FAB .= " - 3 a($profs[$f],$s) <= 0\n";
  }
}
fwrite($file_handle, $FAB) or die('FAB write failed');

// VisitAdj Afternoon
$VAAB = "\n";
for($v=0; $v<$num_visit_adj; $v++){
  for($s=1; $s<=$sem; $s++){
    $VAAB .= "VisitAdjAfternoonBin($visit_adj[$v],$s): ";
    for($a=0; $a<count($afternoon_timeslots); $a++) {
      for($c=0; $c<$num_classes; $c++){
        if ($m == 0 && $c == 0) {
          $VAAB .= "y($afternoon_timeslots[$a],$classes[$c],$visit_adj[$v],$s)";
        }
        else {
          $VAAB .= " + y($afternoon_timeslots[$a],$classes[$c],$visit_adj[$v],$s)";
        }
      }
      $VAAB .= "\n";
    }
    $VAAB .= " - 3 b($visit_adj[$v],$s) <= 0\n";
  }
}
fwrite($file_handle, $VAAB) or die('VAAB write failed');

// Three day
$THD = "\n";
for($c=0; $c<count($three_day_classes); $c++){
  for($s=1; $s<=$sem; $s++){
    $tcount = 0;
    if ($s == 1) {
      $tcount = $three_fall_count[$c];
    }
    else {
      $tcount = $three_spring_count[$c];
    }
    if ($tcount > 0) {
      $THD .= "ThreeDayClass($three_day_classes[$c],$s): ";
      for($t=0; $t<count($three_timeslots); $t++){
        for($f=0; $f<$num_profs; $f++){
          if($t==0 && $f==0){
            $THD .= "x($three_timeslots[$t],$three_day_classes[$c],$profs[$f],$s)";
          }
          else {
            $THD .= " + x($three_timeslots[$t],$three_day_classes[$c],$profs[$f],$s)";
          }
        }
        $THD .= "\n";
        for($v=0; $v<$num_visit_adj; $v++){
          $THD .= " + y($three_timeslots[$t],$three_day_classes[$c],$visit_adj[$v],$s)";
        }
        $THD .= "\n";
        $THD .= " + w($three_timeslots[$t],$three_day_classes[$c],$s)";
      }
      $THD .= " >= 1\n";
    }
  }
}
fwrite($file_handle, $THD) or die('THD write failed');

// Two day
$TWD = "\n";
for($c=0; $c<count($two_day_classes); $c++){
  for($s=1; $s<=$sem; $s++){
    $tcount = 0;
    if ($s == 1) {
      $tcount = $two_fall_count[$c];
    }
    else {
      $tcount = $two_spring_count[$c];
    }
    if ($tcount > 0) {
      $TWD .= "TwoDayClass($two_day_classes[$c],$s): ";
      for($t=0; $t<count($two_timeslots); $t++){
        for($f=0; $f<$num_profs; $f++){
          if($t==0 && $f==0){
            $TWD .= "x($two_timeslots[$t],$two_day_classes[$c],$profs[$f],$s)";
          }
          else {
            $TWD .= " + x($two_timeslots[$t],$two_day_classes[$c],$profs[$f],$s)";
          }
        }
        $TWD .= "\n";
        for($v=0; $v<$num_visit_adj; $v++){
          $TWD .= " + y($two_timeslots[$t],$two_day_classes[$c],$visit_adj[$v],$s)";
        }
        $TWD .= " + w($two_timeslots[$t],$two_day_classes[$c],$s)";
        $TWD .= "\n";
      }
      $TWD .= " >= 1\n";
    }
  }
}
fwrite($file_handle, $TWD) or die('TWD write failed');

// Faculty Time restrictions
$FTR = "\n";
for($f=0; $f<count($fac_restr); $f++){
  for($s=1; $s<=$sem; $s++){
    $FTR .= "FacTimeConst($s,$fac_restr[$f]): ";
    $FacTimes = explode(",",$fac_time[$f]);
    for($t=0; $t<count($FacTimes); $t++){
      for($c=0; $c<$num_classes; $c++){
        if ($t==0 && $c==0) {
          $FTR .= "x($FacTimes[$t],$classes[$c],$fac_restr[$f],$s)";
        }
        else {
          $FTR .= " + x($FacTimes[$t],$classes[$c],$fac_restr[$f],$s)";
        }
      }
      $FTR .= "\n";
    }
    $FTR .= " - d($fac_restr[$f],$s) <= 0\n";
  }
}
fwrite($file_handle, $FTR) or die('FTR write failed');

// Visiting/Adj Time restrictions
$VTR = "\n";
for($v=0; $v<count($va_restr); $v++){
  for($s=1; $s<=$sem; $s++){
    $VaTimes = explode(",",$va_time[$v]);
    $VTR .= "VaTimeConst($s,$va_restr[$v]): ";
    for($t=0; $t<count($VaTimes); $t++){
      for($c=0; $c<$num_classes; $c++){
        if ($t==0 && $c==0) {
          $VTR .= "y($VaTimes[$t],$classes[$c],$va_restr[$v],$s)";
        }
        else {
          $VTR .= " + y($VaTimes[$t],$classes[$c],$va_restr[$v],$s)";
        }
      }
      $VTR .= "\n";
    }
    $VTR .= " - e($va_restr[$v],$s) <= 0\n";
  }
}
fwrite($file_handle, $VTR) or die('VTR write failed');

// Faculty three in a row constraints
$TRF = "\n";
for($f=0; $f<$num_profs; $f++){
  for($s=1; $s<=$sem; $s++){
    for($t=0; $t<count($three_row1); $t++){
      for($r=0; $r<count($three_row2); $r++){
        for($h=0; $h<count($three_row3); $h++){
          $TRF .= "ThreeRowF123($profs[$f],$s,$three_row1[$t],$three_row2[$r],$three_row3[$h]): ";
          for($c=0; $c<$num_classes; $c++) {
            if ($c==0) {
              $TRF .= "x($three_row1[$t],$classes[$c],$profs[$f],$s)";
            } else {
              $TRF .= "\n + x($three_row1[$t],$classes[$c],$profs[$f],$s)";
            }
            $TRF .= " + x($three_row2[$r],$classes[$c],$profs[$f],$s)";
            $TRF .= " + x($three_row3[$h],$classes[$c],$profs[$f],$s)";
          }
          $TRF .= " - trf($profs[$f],$s) <= 2\n";
        }
      }
    }
    for($t=0; $t<count($three_row2); $t++){
      for($r=0; $r<count($three_row3); $r++){
        for($h=0; $h<count($three_row4); $h++){
          $TRF .= "ThreeRowF234($profs[$f],$s,$three_row2[$t],$three_row3[$r],$three_row4[$h]): ";
          for($c=0; $c<$num_classes; $c++) {
            if ($c==0) {
              $TRF .= "x($three_row2[$t],$classes[$c],$profs[$f],$s)";
            } else {
              $TRF .= "\n + x($three_row2[$t],$classes[$c],$profs[$f],$s)";
            }
            $TRF .= " + x($three_row3[$r],$classes[$c],$profs[$f],$s)";
            $TRF .= " + x($three_row4[$h],$classes[$c],$profs[$f],$s)";
          }
          $TRF .= " - trf($profs[$f],$s) <= 2\n";
        }
      }
    }
    for($t=0; $t<count($three_row3); $t++){
      for($r=0; $r<count($three_row4); $r++){
        for($h=0; $h<count($three_row5); $h++){
          $TRF .= "ThreeRowF345($profs[$f],$s,$three_row3[$t],$three_row4[$r],$three_row5[$h]): ";
          for($c=0; $c<$num_classes; $c++) {
            if ($c==0) {
              $TRF .= "x($three_row3[$t],$classes[$c],$profs[$f],$s)";
            } else {
              $TRF .= "\n + x($three_row3[$t],$classes[$c],$profs[$f],$s)";
            }
            $TRF .= " + x($three_row4[$r],$classes[$c],$profs[$f],$s)";
            $TRF .= " + x($three_row5[$h],$classes[$c],$profs[$f],$s)";
          }
          $TRF .= " - trf($profs[$f],$s) <= 2\n";
        }
      }
    }
  }
} 
fwrite($file_handle, $TRF) or die('TRF write failed');

// Visit Adj Three row constraint
$TRV = "\n";
for($v=0; $v<$num_visit_adj; $v++){
  for($s=1; $s<=$sem; $s++){
    for($t=0; $t<count($three_row1); $t++){
      for($r=0; $r<count($three_row2); $r++){
        for($h=0; $h<count($three_row3); $h++){
          $TRV .= "ThreeRowV123($visit_adj[$v],$s,$three_row1[$t],$three_row2[$r],$three_row3[$h]): ";
          for($c=0; $c<$num_classes; $c++) {
            if ($c==0) {
              $TRV .= "y($three_row1[$t],$classes[$c],$visit_adj[$v],$s)";
            } else {
              $TRV .= "\n + y($three_row1[$t],$classes[$c],$visit_adj[$v],$s)";
            }
            $TRV .= " + y($three_row2[$r],$classes[$c],$visit_adj[$v],$s)";
            $TRV .= " + y($three_row3[$h],$classes[$c],$visit_adj[$v],$s)";
          }
          $TRV .= " - trv($visit_adj[$v],$s) <= 2\n";
        }
      }
    }
    for($t=0; $t<count($three_row2); $t++){
      for($r=0; $r<count($three_row3); $r++){
        for($h=0; $h<count($three_row4); $h++){
          $TRV .= "ThreeRowV234($visit_adj[$v],$s,$three_row2[$t],$three_row3[$r],$three_row4[$h]): ";
          for($c=0; $c<$num_classes; $c++) {
            if ($c==0) {
              $TRV .= "y($three_row2[$t],$classes[$c],$visit_adj[$v],$s)";
            } else {
              $TRV .= "\n + y($three_row2[$t],$classes[$c],$visit_adj[$v],$s)";
            }
            $TRV .= " + y($three_row3[$r],$classes[$c],$visit_adj[$v],$s)";
            $TRV .= " + y($three_row4[$h],$classes[$c],$visit_adj[$v],$s)";
          }
          $TRV .= " - trv($visit_adj[$v],$s) <= 2\n";
        }
      }
    }
    for($t=0; $t<count($three_row3); $t++){
      for($r=0; $r<count($three_row4); $r++){
        for($h=0; $h<count($three_row5); $h++){
          $TRV .= "ThreeRowV345($visit_adj[$v],$s,$three_row3[$t],$three_row4[$r],$three_row5[$h]): ";
          for($c=0; $c<$num_classes; $c++) {
            if ($c==0) {
              $TRV .= "y($three_row3[$t],$classes[$c],$visit_adj[$v],$s)";
            } else {
              $TRV .= "\n + y($three_row3[$t],$classes[$c],$visit_adj[$v],$s)";
            }
            $TRV .= " + y($three_row4[$r],$classes[$c],$visit_adj[$v],$s)";
            $TRV .= " + y($three_row5[$h],$classes[$c],$visit_adj[$v],$s)";
          }
          $TRV .= " - trv($visit_adj[$v],$s) <= 2\n";
        }
      }
    }
  }
} 
fwrite($file_handle, $TRV) or die('TRV write failed');

// Special class count constraints
$CCC = "\n";
for($i=0; $i<count($semInits); $i++){
  for($s=1; $s<=$sem; $s++){
    $var = "";
    if (in_array($semInits[$i],$profs)) {
      $var = "x";
      $CCC .= "FacClassCount($semInits[$i],$s): ";
    } else {
      $var = "y";
      $CCC .= "VAClassCount($semInits[$i],$s): ";
    }
    for($c=0; $c<$num_classes; $c++){
      for($t=1; $t<=$timeslots; $t++){
        if ($c==0 & $t==1){
          $CCC .= "$var($t,$classes[$c],$semInits[$i],$s)";
        } else {
          $CCC .= " + $var($t,$classes[$c],$semInits[$i],$s)";
        }
      }
      $CCC .= "\n";
    }
    if ($s==1){
      $CCC .= " = $semFall[$i]\n";
    } else {
      $CCC .= " = $semSpring[$i]\n";
    }
  }
}
fwrite($file_handle, $CCC) or die('CCC write failed');

// Class Reg Timeslot Spread
$SRS = "\n";
for($s=1; $s<=$sem; $s++){
  for($t=0; $t<count($reg_timeslots); $t++){
    $SRS .= "SemRegSpread($s,$reg_timeslots[$t]): ";
    for($c=0; $c<count($reg_classes); $c++){
      for($f=0; $f<$num_profs; $f++){
        if($f==0 && $c==0){
          $SRS .= "x($reg_timeslots[$t],$reg_classes[$c],$profs[$f],$s) ";
        } else {
          $SRS .= " + x($reg_timeslots[$t],$reg_classes[$c],$profs[$f],$s)";
        }
      }
      for($v=0; $v<$num_visit_adj; $v++){
        $SRS .= " + y($reg_timeslots[$t],$reg_classes[$c],$visit_adj[$v],$s)";
      }
      $SRS .= "\n";
    }
    $SRS .= " - srso($s,$reg_timeslots[$t]) + srsu($s,$reg_timeslots[$t])";
    if ($s==1) {
      $SRS .= " = $fall_reg_avg\n";
     } else {
      $SRS .= " = $spring_reg_avg\n";
     }
  }
}
fwrite($file_handle, $SRS."\n") or die('SRS write failed');

// Class Five Timeslot Spread
$SFS = "\n";
for($s=1; $s<=$sem; $s++){
  for($t=0; $t<count($five_timeslots); $t++){
    $SFS .= "SemFiveSpread($s,$five_timeslots[$t]): ";
    for($c=0; $c<count($five_day_classes); $c++){
      for($f=0; $f<$num_profs; $f++){
        if($f==0 && $c==0){
          $SFS .= "x($five_timeslots[$t],$five_day_classes[$c],$profs[$f],$s) ";
        } else {
          $SFS .= " + x($five_timeslots[$t],$five_day_classes[$c],$profs[$f],$s)";
        }
      }
      for($v=0; $v<$num_visit_adj; $v++){
        $SFS .= " + y($five_timeslots[$t],$five_day_classes[$c],$visit_adj[$v],$s)";
      }
      $SFS .= "\n";
    }
    $SFS .= " - sfso($s,$five_timeslots[$t]) + sfsu($s,$five_timeslots[$t])";
    if ($s==1) {
      $SFS .= " = $fall_five_avg\n";
     } else {
      $SFS .= " = $spring_five_avg\n";
     }
  }
}
fwrite($file_handle, $SFS) or die('SFS write failed');

// Class Timeslot Duplicates
$CTD = "\n";
for($t=1; $t<=$timeslots; $t++) {
  for($c=0; $c<$num_classes; $c++){
    for($s=1; $s<=$sem; $s++) {
      $CTD .= "ClassTimeslotDup($t,$classes[$c],$s): ";
      for($f=0; $f<$num_profs; $f++){
        if($f==0){
          $CTD .= "x($t,$classes[$c],$profs[$f],$s) ";
        } else {
          $CTD .= " + x($t,$classes[$c],$profs[$f],$s)";
        }
      }
      for($v=0; $v<$num_visit_adj; $v++){
        $CTD .= " + y($t,$classes[$c],$visit_adj[$v],$s)";
      }
      $CTD .= " + w($t,$classes[$c],$s) + ctb($t,$classes[$c],$s) - ctd($t,$classes[$c],$s) = 1\n";
    }
  }
}
fwrite($file_handle, $CTD) or die('CTD write failed');


// Cross-Dept constraint???

fwrite($file_handle, "Bounds\n") or die('fwrite failed');

fwrite($file_handle, "General\n") or die('fwrite failed');

// SemRegSpread vars
$SRSVars = "\n";
for($s=1; $s<=$sem; $s++){
  for($t=0; $t<count($reg_timeslots); $t++) {
    $SRSVars .= "srso($s,$reg_timeslots[$t]) srsu($s,$reg_timeslots[$t]) ";
  }
  $SRSVars .= "\n";
}
fwrite($file_handle, $SRSVars) or die('SRSVars write failed');

// SemFiveSpread vars
$SFSVars = "\n";
for($s=1; $s<=$sem; $s++){
  for($t=0; $t<count($five_timeslots); $t++) {
    $SFSVars .= "sfso($s,$five_timeslots[$t]) sfsu($s,$five_timeslots[$t]) ";
  }
  $SFSVars .= "\n";
}
fwrite($file_handle, $SFSVars) or die('SFSVars write failed');

// STAFF and classTimeslotCount vars
$SGEN = "\n";
for($s=1; $s<=$sem; $s++){
  for($c=0; $c<$num_classes; $c++){
    for($t=1; $t<=$timeslots; $t++){
      $SGEN .= "w($t,$classes[$c],$s) ctd($t,$classes[$c],$s) ";
    }
    $SGEN .= "\n";
  }
}
fwrite($file_handle, $SGEN."\n") or die('SGEN write failed');

$FLGEN = "\n";
for($f=0; $f<$num_profs; $f++){
  for($s=1; $s<=$sem; $s++){
    $FLGEN .= "fpu($profs[$f],$s) ";
  }
}
fwrite($file_handle, $FLGEN."\n") or die('FLGEN write failed');

$VLGEN = "\n";
for($v=0; $v<$num_visit_adj; $v++){
  for($s=1; $s<=$sem; $s++){
    $VLGEN .= "vpu($visit_adj[$v],$s) ";
  }
}
fwrite($file_handle, $VLGEN."\n") or die('VLGEN write failed');

fwrite($file_handle, "Binaries\n") or die('fwrite failed');

// Faculty binaries
$FBIN = "\n";
for($f=0; $f<$num_profs; $f++){
  for($s=1; $s<=$sem; $s++){
    for($c=0; $c<$num_classes; $c++){
      for($t=1; $t<=$timeslots; $t++){
        $FBIN .= "x($t,$classes[$c],$profs[$f],$s) ";
      }
      $FBIN .= "p($profs[$f],$classes[$c],$s) ";
      $FBIN .= "\n";
    }
    $FBIN .= "fpo($profs[$f],$s) m($profs[$f],$s) a($profs[$f],$s) d($profs[$f],$s) trf($profs[$f],$s) ";
    $FBIN .= "\n";
  }
  $FBIN .= "\n";
}
fwrite($file_handle, $FBIN."\n") or die('FBIN write failed');

// Visit_Adj binaries
$VABIN = "\n";
for($v=0; $v<$num_visit_adj; $v++){
  for($s=1; $s<=$sem; $s++){
    for($c=0; $c<$num_classes; $c++){
      for($t=1; $t<=$timeslots; $t++){
        $VABIN .= "y($t,$classes[$c],$visit_adj[$v],$s) ";
      }
      $VABIN .= "q($visit_adj[$v],$classes[$c],$s) ";
      $VABIN .= "\n";
    }
    $VABIN .= "vpo($visit_adj[$v],$s) n($visit_adj[$v],$s) b($visit_adj[$v],$s) e($visit_adj[$v],$s) trv($visit_adj[$v],$s) ";
    $VABIN .= "\n";
  }
  $VABIN .= "\n";
}
fwrite($file_handle, $VABIN) or die('VABIN write failed');

$RTSBIN = "\n";
for($t=0; $t<count($reg_timeslots); $t++){
  for($s=1; $s<=$sems; $s++){
    $RTSBIN .= "rts($reg_timeslots[$t],$s) ";
  }
}
fwrite($file_handle, $RTSBIN) or die('RTSBIN write failed');

$CTDBIN = "\n";
for($t=1; $t<=$timeslots; $t++){
  for($s=1; $s<=$sem; $s++){
    for($c=0; $c<$num_classes; $c++){
      $CTDBIN .= "ctb($t,$classes[$c],$s) ";
    }
  }
}
fwrite($file_handle, $CTDBIN) or die('CTDBIN write failed');

fwrite($file_handle, "\nEnd") or die('fwrite failed');

//close the file
fclose($file_handle);

//close the connection
// mysqli_close($dbcon);

?>
<META http-equiv="refresh" content="4;URL=http://mathcsdev.dickinson.edu/classScheduler/program.php">
