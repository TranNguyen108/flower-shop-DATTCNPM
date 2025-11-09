<?php
include '../config.php';
echo "<h3>Session Debug</h3>";
echo "<strong>Session Status:</strong> " . session_status() . " (1=disabled, 2=active)<br>";
echo "<strong>Session ID:</strong> " . session_id() . "<br><br>";
echo "<strong>SESSION Data:</strong><br>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
echo "<br><strong>user_id value:</strong> " . ($_SESSION['user_id'] ?? 'NOT SET');
