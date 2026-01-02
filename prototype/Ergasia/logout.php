<?php
session_start();
//αφαιρεί τις μεταβλητές του session
session_unset();
//καταστρέφει το session
session_destroy();

//κατευθύνει τον χρήστη στην σελίδα index
header("Location: index.php");
exit();
?>

