<?php
// cart/clear-cart.php
session_start();
require_once __DIR__ . '/../config/cart.php';

clearCart();

header('Location: cart.php');
exit;