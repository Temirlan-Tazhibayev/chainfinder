<?php

class upd_1 extends schema\model\system\BaseUpdate
{
    public function execute()
    {
        $this->conn->add('INSERT INTO users (username, password) VALUES(?, ?)')
            ->execute(['admin', 'admin']);
    }
}