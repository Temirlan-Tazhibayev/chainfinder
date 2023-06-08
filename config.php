<?php
define('DB_NAME', 'Chainfinder');
define('DB_DRIVER', 'Chainfinder');

define('DB_USER', 'postgres');
define('DB_PASSWORD', 'postgres'); //put your Database password here
define('DB_HOST', 'localhost');
define('PORT', '5432');
define('SEARCHE_PATH', 'PUBLIC');
define('LOG_PATH', 'tmp/log/');

$aConfig = array(
    'DB_DRIVER' => DB_DRIVER,
    'DB_NAME' => DB_NAME,
    'DB_USER' => DB_USER,
    'DB_PASSWORD' => DB_PASSWORD,
    'DB_HOST' => DB_HOST,
    'PORT' => PORT,
    'SEARCHE_PATH' => SEARCHE_PATH,
    'LOG_PATH' => WWW_PATH . '/' . LOG_PATH,
);

?>