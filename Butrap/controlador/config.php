<?php
    $hostname = 'localhost:3306';
    $username = 'root';
    $contrasenia = 'contraseña';
    $database = 'urbin';
    // Establish database connection
    $conn = mysqli_connect($hostname, $username, $contrasenia, $database);
    // Check connection
    if (!$conn) {
        die('Connection failed: ' . mysqli_connect_error());
    }
