<?php
require_once 'config.php';

// Destroy session
session_start();
session_unset();
session_destroy();

// Redirect to login
header('Location: login.php');
exit;
