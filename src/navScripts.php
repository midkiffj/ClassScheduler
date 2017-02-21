<?php
  session_start();
  if($_SERVER["HTTPS"] != "on") {
     header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
     exit();
  }
?>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<!-- <script src="http://code.jquery.com/jquery-1.9.1.js"></script> -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
<style>
.navbar .divider-vertical {
    height: 50px;
    margin: 0 0px;
    border-right: 1px solid #bfbfbf;
    border-left: 1px solid #bfbfbf;
}

.navbar-inverse .divider-vertical {
    border-right-color: #737373;
    border-left-color: #737373;
}

@media (max-width: 767px) {
    .navbar-collapse .nav > .divider-vertical {
        display: none;
     }
}
</style>
</head>
<body>
<?php
  if ($title) {
    echo "<title>Course Scheduler: ".$title."</title>";
  }
  else {
    echo "<title>Course Scheduler</title>";
  }
?>

<nav class="navbar navbar-inverse navbar-static-top">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="/classScheduler/options.php">Scheduler
      <?php
        if (isset($_SESSION['dept'])) {
          $dept = $_SESSION['dept'];
          echo "[$dept]";
        }
      ?>
      </a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
      <?php
        $showPrefs = 1;
        $con = new mysqli("localhost","peeping","tom","span_scheduler");
        $sql = "select pref from dept_options where dept='$dept'";
        $prefCheck = $con->query($sql);
        while ($pref = $prefCheck->fetch_assoc()){
          $showPrefs = $pref['pref'];
        }
        if ($active) {
          if ($showPrefs) {
            echo "<li class=\"divider-vertical ";
            if ($active == 'preferences') {
              echo "active";
            }
            echo"\"><a href=\"/classScheduler/preferences.php\">Preferences</a></li>";
          }
          // Courses
          echo "<li class=\"";
          if ($active == 'courses') {
            echo "active";
          }
          echo "\"><a href=\"/classScheduler/courses.php\">Courses</a></li>";
          // Instructors
          echo "<li class=\"";
          if ($active == 'instructors'){
            echo "active";
          }
          echo "\"><a href=\"/classScheduler/instructors.php\">Instructors</a></li>";
          // Program
          echo "<li class=\"";
          if ($active == 'program'){
            echo "active";
          }
          echo "\"><a href=\"/classScheduler/program.php\">Program</a></li>";
          echo "</ul>";
          echo "<ul class=\"nav navbar-nav navbar-right\">";
          if ($_SESSION['username']=='midkiffj' || $_SESSION['username']=='wahlst') {
            echo "<li><a href=\"superAdmin.php\">Super Admin</a></li>";
          }
          // Admin dropdown
          if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1) {
            echo "<li class=\"dropdown ";
            if ($active == 'adminDepartment' || $active == 'adminAdmins'){
              echo "active";
            }
            echo "\">";
            echo "<a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\" role=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\">Admin <span class=\"caret\"></span></a>";
            echo "<ul class=\"dropdown-menu\">";
            // Admin instructors
            echo "<li class=\"";
            if ($active == 'adminDepartment') {
              echo "active";
            }
            echo "\"><a href=\"adminDepartment.php\">Department</a></li>";
            // Admin admins
            echo "<li class=\"";
            if ($active == 'adminAdmins'){
              echo "active";
            }
            echo "\"><a href=\"adminAdmins.php\">Admins</a></li>";
            echo "</ul></li>";
          }
          // Logout
          echo "<li><a href=\"logout.php\">";
          // if (isset($_SESSION['username'])){
          //   // echo "(".$_SESSION['username'].")";
          // }
          echo "Logout (".$_SESSION['username'].")</a></li>";
          echo "</ul>";
        }
        else {
          if ($showPrefs) {
            echo "<li class=\"divider-vertical\"><a href=\"/classScheduler/preferences.php\">Preferences</a></li>";
          }
          echo "<li><a href=\"/classScheduler/courses.php\">Courses</a></li>
                <li><a href=\"/classScheduler/instructors.php\">Instructors</a></li>
                <li><a href=\"/classScheduler/program.php\">Program</a></li>";
          echo "</ul>
                <ul class=\"nav navbar-nav navbar-right\">";
          if ($_SESSION['username']=='midkiffj' || $_SESSION['username']=='wahlst') {
            echo "<li><a href=\"superAdmin.php\">Super Admin</a></li>";
          }
          if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1) {
            echo "<li class=\"dropdown\">
                    <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\" role=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\">Admin <span class=\"caret\"></span></a>
                    <ul class=\"dropdown-menu\">
                      <li><a href=\"adminDepartment.php\">Department</a></li>
                      <li><a href=\"adminAdmins.php\">Admins</a></li>
                    </ul>
                  </li>";
          } 
          echo "<li><a href=\"logout.php\">Logout (".$_SESSION['username'].")</a></li>
                </ul>";
        }
        ?>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>