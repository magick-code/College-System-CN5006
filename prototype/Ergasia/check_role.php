<?php
function check($role){
    //ξεκινάει το session αν δεν έχει ήδη ξεκινήσει
    if(session_status()==PHP_SESSION_NONE){
        session_start();
    }
    //έλεγχει αν ο χρήστης είναι συνδεδεμένος
    if(!isset($_SESSION["role_id"])){
        header("Location: login.php");
        exit();
    }
    //έλεγχει αν ο ρόλος δεν είναι σωστός και εμφάνιζει μηνύμα σφάλματος 
    if($_SESSION["role_id"]!==$role){
        echo "Forbidden Action";
        exit();
    }
}
?>
