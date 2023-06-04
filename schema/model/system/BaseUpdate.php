<?php
namespace schema\model\system;

abstract class BaseUpdate
{

    protected $conn = null;

    public function __construct()
    {
        $this->conn = \schema\model\Registry::get('db');
    }

    abstract function execute();
}

?>