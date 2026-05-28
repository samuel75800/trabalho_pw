<?php

require_once __DIR__ . '/../includes/auth.php';

auth_logout();

_auth_redirect('/pages/login.php');
?>