<?php
// параметры подключения к базе данных MySQL
$host = 'localhost';
$dbname = 'bitcoin';
$user = 'root';
$password = '';

// создаем объект соединения с базой данных
try {
  $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
  echo "Ошибка подключения к базе данных: " . $e->getMessage();
  exit;
}

// выполнение SQL-запроса и получение результата
$query = "SELECT input, hash as transaction_hash, output FROM transaction LIMIT 500";
$result = $conn->query($query);

// преобразование результата в массив ассоциативных массивов
$rows = $result->fetchAll(PDO::FETCH_ASSOC);

// закрытие соединения с базой данных
$conn = null;

// преобразование массива в JSON-строку
$json = json_encode($rows);

// Return the transactions as JSON
header('Content-Type: application/json');
echo $json;

?>