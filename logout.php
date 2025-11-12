<?php
session_start();
session_unset();
session_destroy();

// ✅ Redirect correctly to main page
header("Location: ../index.html");
exit;
?>
