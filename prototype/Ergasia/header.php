<head>
<link rel="stylesheet" href="style.css">

</head>

<header>
<ul class="navbar">
  <li style="float:left;"> <a href="index.php"><img class="logo" src="photos/mc-logo.png" alt="metropolitan college logo"></a></li>
  
  <?php

    //όταν ο χρήστης συνδέεται το navigation bar αλλάζει όστε να εμφανίζει την επιλόγη για logout και dashboard
    if(isset($_SESSION["username"])){
     echo "<li><a href='dashboard.php'>Dashboard</a></li>";
      echo "<li><a href='logout.php'>Αποσύνδεση</a></li>";
    }else{
     echo "<li><a href='login.php'>Σύνδεση</a></li>";
      echo "<li><a href='register.php'>Εγγραφή</a></li>";
    }
  
  ?>
  </ul>
</header>

