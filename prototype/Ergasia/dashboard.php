<?php

session_start();

//ελέγχει αν ο χρήστης είναι συνδεμένος
if (!isset($_SESSION["username"])){
  header("Location: login.php");
}

$message = "<h2>Καλωσόρισες ". $_SESSION["username"]."</h2><br>";

?>


<!-- Header -->
<ul class="navbar">
<li style="float:left;"> <a href="index.php"><img class="logo" src="photos/mc-logo.png" alt="metropolitan college logo"></a></li>
<li><a href='index.php'>Αρχική</a></li>
<li><a href='logout.php'>Αποσύνδεση</a></li>
</ul>



<h1><?php echo htmlspecialchars($_SESSION["role_id"]); ?> Dashboard</h1>
<br>


<?php echo $message; ?>

<br>

<section class="container">

<?php
$role = $_SESSION["role_id"];


//Αν ο ρόλος που είναι αποθηκευμένος στο session είναι φοιτητής τότε εφάνιζει επιλογές φοιτητή

if($role === "Student"):?>
<div class="box">
 <ul class="dashboard_list">

 <li><a href="student/view_courses.php">Προβολή Μαθημάτων</a></li>
 <li><a href="student/view_assignments.php">Προβολή Εργασιών</a></li>
 <li><a href="student/submit_assignment.php">Υποβολή Εργασιών</a></li>
 <li><a href="student/view_grades.php">Προβολή Βαθμολογιών</a></li>

 </ul>
</div>

<!--Αν ο ρόλος που είναι αποθηκευμένος στο session είναι καθηγητής τότε εφάνιζει επιλογές καθηγητή-->

<?php elseif($role === "Professor"):?>
  <div class="box">
  <ul class="dashboard_list">

  <li><a href="professor/manage_courses.php">Δημιουργία και Διαχείριση Μαθημάτων</a></li>
  <li><a href="professor/submit_assignments.php">Ανάρτηση Εργασιών</a></li>
  <li><a href="professor/view_submissions.php">Προβολή Υποβολών Φοιτητών</a></li>
  <li><a href="professor/grade_submissions.php">Βαθμολόγηση φοιτητών Ανά Μάθημα</a></li>

  </ul>
  </div>


<!--Αν ο ρόλος είναι άγνωστος τότε εφάνιζει μύνημα οτι ο ρόλος είναι άγνωστος
// I think this is bullshit. I will fix it soon-->

<?php else:?>
<div class="box">
  <p style="color:red">Άγνωστος ρόλος χρήστη.</p>
</div>


<?php endif;?>
  </section>


<?php include "footer.php"?>

