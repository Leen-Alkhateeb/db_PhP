<?php
session_start();
$conn = new mysqli("localhost", "root", "", "auth_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
