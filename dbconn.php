<?php
$host = 'localhost';
$db_name = 'lostandfound';
$username = 'root';
$password = 'shirochan11';

try {
    $db = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection error: " . $e->getMessage();
    die();
}
?>