<?php
require 'app/core/config.php';
require 'app/core/Database.php';
Database::setup();
foreach(Database::getInstance()->query('SELECT credential_uid FROM credentials') as $r) {
    echo $r['credential_uid'] . "\n";
}
