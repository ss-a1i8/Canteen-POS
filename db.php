<?php
$host = 'localhost';
$dbname = 'canteen';
$username = 'root';
$password = '';
 
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 
} catch (PDOException $e) {
    echo "Connection failed". $e->getMessage();
}
//this trys to connect with the database and if there is an error it catches it and shows an error message
?>