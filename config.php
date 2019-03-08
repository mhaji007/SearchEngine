<?php
// This is the file for communicating with our database

// does output buffering: saves output of any data until the end. Will only output data when it has finished all of the execution of the code.
ob_start(); 

try {
	
	//"" is for password and "root" is for username. Use the username and password you use for your phpmyadmin if you are putting it on a live server
	$con = new PDO("mysql:dbname=inquire;host=localhost", "root", ""); 

	// PDO::ATTR_ERRMODE says if there is an error, show the error as warning PDO::ERRMODE_WARNING
	$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

}
catch(PDOExeption $e){
	echo "Connection failed: " . $e->getMessage();
}
?>

