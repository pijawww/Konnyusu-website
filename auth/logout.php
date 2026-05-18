<?php
session_start();
require_once __DIR__ . '/../config/auth.php';
logout();
header('Location: ../home/home.php');
exit;
?>
