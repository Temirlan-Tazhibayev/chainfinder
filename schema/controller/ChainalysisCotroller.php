<?php

namespace schema\controller;

class ChainalysisCotroller extends \schema\controller\MainController
{
    protected $conn;

    public function __construct() {
        $this->conn = \schema\model\Registry::get('db');
    }

    public $sViewPath = WWW_PATH . '/schema/view/Main.php';

    public function view()
    {
        require_once $this->sViewPath;
    }

    public function canView()
    {
        return true;
    }


    public function handleRequest() {
        $inputAddress = isset($_POST['input-address']) ? $_POST['input-address'] : '';
        $outputAddress = isset($_POST['output-address']) ? $_POST['output-address'] : '';

        if (isset($_POST['transaction-count']) && is_numeric($_POST['transaction-count'])) {
            $transactioncount = intval($_POST['transaction-count']);
        } else {
            $transactioncount = 50; // default value
        }
        $_SESSION['inputAddress'] = $inputAddress;
        $_SESSION['outputAddress'] = $outputAddress;
        // $_SESSION['transactioncount'] = $transactioncount;
        
        $_SESSION['start-date'] = $_POST['start-date'] ?? '';
        $_SESSION['end-date'] = $_POST['end-date'] ?? '';


        // echo "Parsed transaction count: " . $_SESSION['start_date'];

        $data = $this->getTransactions($inputAddress, $outputAddress, $transactioncount);
        return $this->loadView($data);
    }

    private function getTransactions($inputAddress, $outputAddress, $transactioncount)
    {
        try {
        $additionalCondition = "";
        if(!empty($_SESSION["start-date"]) && !empty($_SESSION["end-date"])) {
            $startDate = date('Y-m-d', strtotime($_SESSION["start-date"]));
            $endDate = date('Y-m-d', strtotime($_SESSION["end-date"]));
            $additionalCondition = " AND a.time BETWEEN '{$startDate}' AND '{$endDate}'";
        } elseif (!empty($_SESSION["start-date"])) {
            $startDate = date('Y-m-d', strtotime($_SESSION["start-date"]));
            $additionalCondition = " AND a.time >= '{$startDate}'";
        } elseif (!empty($_SESSION["end-date"])) {
            $endDate = date('Y-m-d', strtotime($_SESSION["end-date"]));
            $additionalCondition = " AND a.time <= '{$endDate}'";
        }

        $this->conn->add("SELECT a.block_id, a.transaction_hash, a.time, a.value_usd, a.recipient as sender, b.recipient as receiver");
        $this->conn->add("FROM inputs a INNER JOIN outputs b ON a.transaction_hash = b.transaction_hash" );

        if (!empty($inputAddress) && !empty($outputAddress)) {
            $this->conn->add("WHERE (a.recipient LIKE '{$inputAddress}%' OR b.recipient LIKE '{$outputAddress}%')  {$additionalCondition} LIMIT {$transactioncount}");
            // echo "WHERE (a.recipient LIKE '{$inputAddress}%' OR b.recipient LIKE '{$outputAddress}%')  {$additionalCondition} GROUP BY (a.block_id, a.transaction_hash, a.time, a.value_usd, a.recipient, b.recipient) LIMIT {$transactioncount}";
        } elseif (!empty($inputAddress) && empty($outputAddress)) {
            $this->conn->add("WHERE (a.recipient LIKE '{$inputAddress}%')  {$additionalCondition}  GROUP BY (a.block_id, a.transaction_hash, a.time, a.value_usd, a.recipient, b.recipient) LIMIT {$transactioncount}");
            // echo "WHERE (a.recipient LIKE '{$inputAddress}%')  {$additionalCondition} LIMIT {$transactioncount}";
        } elseif (empty($inputAddress) && !empty($outputAddress)) {
            $this->conn->add("WHERE b.recipient LIKE '{$outputAddress}%' GROUP BY (a.block_id, a.transaction_hash, a.time, a.value_usd, a.recipient, b.recipient) LIMIT {$transactioncount}");
            // echo "WHERE (a.recipient LIKE '{$outputAddress}%')  {$additionalCondition} LIMIT {$transactioncount}";
        } else {
            $this->conn->add("WHERE 1=1 {$additionalCondition}  GROUP BY (a.block_id, a.transaction_hash, a.time, a.value_usd, a.recipient, b.recipient) LIMIT {$transactioncount}");
        }



        $this->conn->execute();

    
        $rows = $this->conn->fetchAll();
    
        return $rows;
        } catch (\PDOException $ex) {
            \schema\model\Logger::log($ex);
            d($ex->getMessage() . $ex->getTraceAsString());
        }
    }
    


    private function loadView($data) {
        include WWW_PATH . '/schema/view/Main.php';
    }
}