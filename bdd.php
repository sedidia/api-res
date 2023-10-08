<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
$bdd = new PDO('mysql:host=localhost;dbname=unilu_archives;charset=utf8;', 'root', '');
?>