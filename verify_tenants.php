<?php
require_once 'bootstrap/bootstrap.php';
$tenants = db_fetch_all("SELECT * FROM tenants");
print_r($tenants);
