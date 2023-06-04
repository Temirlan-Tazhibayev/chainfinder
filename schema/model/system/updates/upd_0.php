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
                recipient character varying(600),
                type character varying(128),
                script_hex character varying(400),
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
                recipient character varying(600),
                type character varying(128),
                script_hex character varying(400),
                is_from_coinbase integer,
                is_spendable integer,
                spending_block_id bigint,
                spending_transaction_hash character varying(400),
                spending_index integer,
                spending_time time without time zone,
                spending_value_usd numeric,
                spending_sequence bigint,
                spending_signature_hex character varying(400),
                spending_witness character varying(600),
                lifespan bigint,
                cdd numeric
            )",
            "CREATE table IF NOT EXISTS users (
                username VARCHAR(255),
                password VARCHAR(255)
            )"
        ];

        foreach ($initializeQueries as $initializeQuery) {
            $this->conn->add($initializeQuery)->execute([]);
        }
    }
}
?>