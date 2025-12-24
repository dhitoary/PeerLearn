<?php
session_start();
session_unset();
session_destroy();
header("Location: ../../frontend/pages/public/landing_page.php?status=logged_out");
exit();
?>