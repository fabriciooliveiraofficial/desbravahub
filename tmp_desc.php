<?php
require 'config/config.php';
require 'helpers/database.php';
$db = db_connect();
$stmt = $db->query('DESCRIBE user_step_responses');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
