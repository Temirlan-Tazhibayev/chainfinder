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


    /**
     * Handles the incoming request and delegates the processing of the request data.
     * After processing the request, it loads the view with the processed data.
     */
    public function handleRequest() {
        // Collect all request data into an associative array.
        $requestData = [
            'transactionHash' => $_POST['transaction-hash'] ?? '',
            'inputAddress' => $_POST['input-address'] ?? '',
            'outputAddress' => $_POST['output-address'] ?? '',
            'blockId' => $_POST['block-id'] ?? '',
            'transactionCount' => $_POST['transaction-count'] ?? 300, // default value
            'startTimestamp' => $_POST['start-timestamp'] ?? '',
            'endTimestamp' => $_POST['end-timestamp'] ?? '',
        ];

        // Store the request data in session for later use.
        $this->setSessionData($requestData);

        // Process the request data to get the transactions.
        $data = $this->getTransactions($requestData);

        // Load the view with the processed data.
        return $this->loadView($data);
    }

    /**
     * Stores an associative array of data in session.
     */
    private function setSessionData(array $data) {
        foreach ($data as $key => $value) {
            $_SESSION[$key] = $value;
        }
    }

    /**
     * Process the request data to fetch the transactions based on the provided filters.
     */
    private function getTransactions($requestData)
    {
        try {
            // Base SQL query
            $query = "
                SELECT b.block_id, b.transaction_hash, b.time, 
                a.recipient as sender, a.value as sent_value, a.value_usd as sent_usd, 
                b.recipient as receiver, b.value as received_value, b.value_usd as received_usd
                FROM inputs a
                INNER JOIN outputs b ON a.spending_transaction_hash = b.transaction_hash
            ";

            $params = [];
            $conditions = [];

            // Build the conditions and parameters based on the provided filters.
            // Only add conditions and parameters for filters that are not empty.

            // Transaction Hash
            if (!empty($requestData['transactionHash'])) {
                $conditions[] = "b.transaction_hash = ?";
                $params[] = $requestData['transactionHash'];
            }

            // Sender
            if (!empty($requestData['inputAddress'])) {
                $conditions[] = "a.recipient LIKE ?";
                $params[] = $requestData['inputAddress'] . '%';
            }

            // Receiver
            if (!empty($requestData['outputAddress'])) {
                $conditions[] = "b.recipient LIKE ?";
                $params[] = $requestData['outputAddress'] . '%';
            }

            // Block ID
            if (!empty($requestData['blockId'])) {
                $conditions[] = "b.block_id = ?";
                $params[] = $requestData['blockId'];
            }

            // Start Timestamp
            if (!empty($requestData['startTimestamp'])) {
                $start_date = new \DateTime($requestData['startTimestamp']);  // we added backslash before Datetime to look for this class in the global namespace, where built-in PHP classes are defined.
                $conditions[] = "b.time >= ?";
                $params[] = $start_date->format('Y-m-d H:i:s');
            }

            // End Timestamp
            if (!empty($requestData['endTimestamp'])) {
                $end_date = new \DateTime($requestData['endTimestamp']);
                $conditions[] = "b.time <= ?";
                $params[] = $end_date->format('Y-m-d H:i:s');
            }

            // Add the conditions to the query
            if ($conditions) {
                $query .= ' WHERE ' . implode(' AND ', $conditions);
            }

            // Add the limit to the query
            if (isset($requestData['transactionCount'])) {
                $query .= " LIMIT ?";
                $params[] = $requestData['transactionCount'];
            }

            // Execute the query with the parameters
            $this->conn->executeQuery($query, $params);

            // Fetch all the results
            $rows = $this->conn->fetchAll();

            return $rows;
        } catch (\PDOException $ex) {
            \schema\model\Logger::log($ex);
            d($ex->getMessage() . $ex->getTraceAsString());
        }
    }

    /**
     * Loads the Main view with the provided data.
     */
    private function loadView($data) {
        include WWW_PATH . '/schema/view/Main.php';
    }
}