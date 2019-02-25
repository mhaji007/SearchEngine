<?php
// This is the file for communicating with our database
ob_start(); // saves output of any data until the end. Will only output data when it has finished all of the execution of the code.

try {

	$con = new PDO("mysql:dbname=Doodle;host=localhost", "root", ""); //"" is for password and "root" is for username. Use the username you use for your phpmyadmin if you aare putting it on a live server
	$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

}
catch(PDOExeption $e){
	echo "Connection failed: " . $e->getMessage();
}
?>

