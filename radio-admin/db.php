<?php
// Compatibility include for existing pages; credentials live in lib/config.local.php.
require_once dirname(__DIR__) . '/lib/database.php';
$pdo = signal_pdo();
