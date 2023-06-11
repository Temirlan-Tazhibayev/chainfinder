<?php
if (isset($_POST['requestType'])){
    $host = 'localhost';
    $db = 'Chainfinder';
    $user = 'postgres';
    $pass = 'postgres';


        // Create a new PDO instance
        $conn = new PDO("pgsql:host=$host;dbname=$db", $user, $pass);

        // Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
           // Base SQL query
           if ($_POST['requestType'] == 'highlight'){
                $query = "SELECT DISTINCT a.recipient as sender, b.recipient as receiver
                    FROM inputs a INNER JOIN outputs b ON a.spending_transaction_hash = b.transaction_hash";
           }else if ($_POST['requestType'] == 'expand'){
                $query = "SELECT b.block_id, b.transaction_hash, b.time, 
                    a.recipient as sender, a.value as sent_value, a.value_usd as sent_usd, 
                    b.recipient as receiver, b.value as received_value, b.value_usd as received_usd
                    FROM inputs a
                    INNER JOIN outputs b ON a.spending_transaction_hash = b.transaction_hash";
           }
    
            $params = [];
            $conditions = [];

            // Build the conditions and parameters based on the provided filters.
            // Only add conditions and parameters for filters that are not empty.

            // Transaction Hash
            if (!empty($_POST['transactionHash'])) {
                $conditions[] = "b.transaction_hash = ?";
                $params[] = $_POST['transactionHash'];
            }
        
            // Sender
            if (!empty($_POST['inputAddress'])) {
                $conditions[] = "a.recipient LIKE ?";
                $params[] = $_POST['inputAddress'] . '%';
            }
        
            // Receiver
            if (!empty($_POST['outputAddress'])) {
                $conditions[] = "b.recipient LIKE ?";
                $params[] = $_POST['outputAddress'] . '%';
            }
        
            // Block ID
            if (!empty($_POST['blockId'])) {
                $conditions[] = "b.block_id = ?";
                $params[] = $_POST['blockId'];
            }
        
            // Start Timestamp
            if (!empty($_POST['startTimestamp'])) {
                $start_date = new \DateTime($_POST['startTimestamp']);  // we added backslash before Datetime to look for this class in the global namespace, where built-in PHP classes are defined.
                $conditions[] = "b.time >= ?";
                $params[] = $start_date->format('Y-m-d H:i:s');
            }
        
            // End Timestamp
            if (!empty($_POST['endTimestamp'])) {
                $end_date = new \DateTime($_POST['endTimestamp']);
                $conditions[] = "b.time <= ?";
                $params[] = $end_date->format('Y-m-d H:i:s');
            }
        
            // Add the conditions to the query
            if ($conditions) {
                $query .= ' WHERE ' . implode(' AND ', $conditions);
            }
            
            // Add the limit to the query
            if (isset($requestData['connectionsCount'])) {
                $query .= " LIMIT ?";
                $params[] = $requestData['connectionsCount'];
            }

            // Add the conditions to the query
            if (count($params) >= 1) {
                
                $statement = $conn->prepare($query);
                $statement->execute($params);
                   
                // Fetch all the results
                $rows = $statement->fetchAll();
                echo json_encode($rows);
            }else if ($_POST['requestType'] == 'highlight'){
                echo json_encode("highlightAllNodes");
            }
            $conn = null;
        } catch (\PDOException $ex) {
            \schema\model\Logger::log($ex);
            d($ex->getMessage() . $ex->getTraceAsString());
        }

}
?>