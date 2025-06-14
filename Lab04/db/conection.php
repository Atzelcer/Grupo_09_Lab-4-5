<?php 
$con= new mysqli("localhost","root","","email_system");
if($con->connect_error){
    die("Error: " . $con->connect_error);
}

?>