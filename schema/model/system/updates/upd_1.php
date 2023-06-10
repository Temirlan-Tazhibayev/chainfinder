<?php

class upd_1 extends schema\model\system\BaseUpdate
{
    public function execute()
    {
        $this->conn->executeQuery('INSERT INTO users (username, password) VALUES(?, ?)', ['admin', 'admin']);
    }
}