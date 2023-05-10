<?php
$aConfig['db'] = ['postgre', 'postgres', 'qwerty', 'localhost', '5432', 'public', 'Bitcoin'];
$aConfig['app_log_path'] = 'D:\xampp\htdocs\Github\pathfinder\tmp\log';



try {
    $conn = new \PDO('pgsql:host=' . $aConfig['db'][3] . ';port=' . $aConfig['db'][4] . ';dbname=' . $aConfig['db'][6], $aConfig['db'][1], $aConfig['db'][2], array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
    $conn->query('SET search_path TO ' . $aConfig['db'][5]);
}
catch (\Exception $oE) {
    \system\Logger::log($oE);
}