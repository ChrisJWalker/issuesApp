<?php
// Desttroys session and sends back to index page page
session_start();
session_destroy();
header("Location: index.php");
exit();
