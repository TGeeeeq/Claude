<?php
require_once '../config/session.php';
adminLogout();
header('Location: /admin/login.php');
exit;
?>
