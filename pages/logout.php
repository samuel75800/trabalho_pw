<?php
// C:\xampp\htdocs\puppy.co\pages\logout.php
require_once __DIR__ . '/../includes/auth.php';

auth_logout();

_auth_redirect('/puppy.co/pages/login.php');
?>