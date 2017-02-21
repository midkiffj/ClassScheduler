<?php

//create the schedule!

//write the lp file
//shell_exec('php write_lp.php');
require('write_lp.php');

//run lp file with scip and parse the output file and put results in database OR, if the solution is infeasible, relax constraints and rerun
//shell_exec('php optimize_and_parse.php');
require('optimize_and_parse.php');

//transfer data to html table  
//shell_exec('php write_schedule.php');
require('write_schedule.php');

?>

<html>
<head><title>Success!</title></head>
<body background="background.jpg">
<br>
<br>
<br>
<p align="center"><font size="5" style="font-family:trebuchet ms;color:navy">
  The schedule has been created!! View it here:<br>
</font>
<a href="schedule.html"><p align="center">Optimal Schedule</p></a>
</p>
</body>
</html>
