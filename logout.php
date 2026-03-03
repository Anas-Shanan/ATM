<?php
session_start(); // Need to start it before you can destroy it

// Wipe all session data on the server
// Like: req.session.destroy() in Express
session_destroy();

// Send them back to login
header("Location: index.php");
exit;
