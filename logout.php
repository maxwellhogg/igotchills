<?php
require_once 'includes/functions.php'; // ensure functions are loaded
logout_user();
header('Location: index.php');
exit;
?>
