<?php
// cart/remove-from-cart.php
session_start();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    unset($_SESSION['cart'][$id]);
}

header('Location: cart.php');
exit;
