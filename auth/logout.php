<?php
require __DIR__ . '/../includes/auth.php';

$_SESSION = [];
session_destroy();

header('Location: /mapoly_bookshop/auth/login.php');
exit;
