<?php
require_once __DIR__ . '/../bootstrap/bootstrap.php';

$stmt = db()->query("DESCRIBE users");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $c) echo $c['Field'] . "\n";
