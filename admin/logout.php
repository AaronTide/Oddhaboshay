<?php
session_start();
session_destroy();              // Clear all session data
header('Location: ../landingpage.php');
exit();
?>
