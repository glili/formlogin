<?php
/* Database credentials. Assuming we are running MySQL
server with default setting (user 'root' with no password) */
define('DB_SERVER', 'mysql');
define('DB_USERNAME', 'loginform');
define('DB_PASSWORD', 'mypass');
define('DB_NAME', 'loginform');
 
/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>
