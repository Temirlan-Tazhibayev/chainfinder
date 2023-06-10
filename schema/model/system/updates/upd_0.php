<?php


class upd_0 extends schema\model\system\BaseUpdate{
    public function execute()
    {
        $initializeQueries = [
            "CREATE TABLE IF NOT EXISTS outputs
            (
                block_id integer,
                transaction_hash character varying(64),
                index integer,
                time timestamp with time zone,
                value bigint,
                value_usd numeric,
                recipient character varying(62), 
                type character varying(21),
                script_hex character varying(400),  --should be deleted after loading all data
                is_from_coinbase integer,
                is_spendable integer
            )",
            "CREATE TABLE IF NOT EXISTS inputs
            (
                block_id integer,
                transaction_hash character varying(64),
                index integer,
                time timestamp without time zone,
                value bigint,
                value_usd numeric,
                recipient character varying(62),
                type character varying(21),
                script_hex character varying(1000),  --should be deleted after loading all data
                is_from_coinbase integer,
                is_spendable integer,
                spending_block_id bigint,
                spending_transaction_hash character varying(64),
                spending_index integer,
                spending_time time without time zone,
                spending_value_usd numeric,
                spending_sequence bigint,
                spending_signature_hex character varying(1200),   --should be deleted after loading all data
                spending_witness TEXT,   --should be deleted after loading all data
                lifespan bigint,
                cdd numeric
            )",
            "CREATE table IF NOT EXISTS users (
                username VARCHAR(255),
                password VARCHAR(255)
            )"
        ];

        foreach ($initializeQueries as $initializeQuery) {
            $this->conn->executeQuery($initializeQuery, []);
        }
    }
}
?>