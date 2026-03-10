<?php
$db = new PDO('sqlite:database/database.sqlite');
$stmt = $db->query("SELECT slug FROM tenants LIMIT 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo $row['slug'] ?? 'clube-demo';
