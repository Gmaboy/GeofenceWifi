<?php
session_start();
session_unset();
session_destroy();

/* prevent cache */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

header("Location: ../Frontend/index.php");
exit;
