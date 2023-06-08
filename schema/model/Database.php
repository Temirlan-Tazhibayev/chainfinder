<?php
namespace schema\model;

class Database
{
    private $queryString = '';
    private $result;
    private $pdoConn;

    public function __construct(array $aConfig)
    {
        $this->pdoConn = $this->connect($aConfig);
    }

    public function connect($aConfig)
    {
        $this->pdoConn = null;

        try {
            $this->pdoConn = new \PDO('pgsql:host=' . $aConfig['DB_HOST'] . ';dbname=' . $aConfig['DB_NAME'], $aConfig['DB_USER'], $aConfig['DB_PASSWORD']);
            $this->pdoConn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdoConn->query('SET search_path TO ' . $aConfig['SEARCHE_PATH']);
        } catch (\PDOException $e) {
            \schema\model\Logger::log($e);
            d($e->getMessage() . $e->getTraceAsString());
        }

        return $this->pdoConn;
    }

    public function disconnect()
    {
        $this->pdoConn = null;
        echo "Соединение с базой данных закрыто<br>";
    }

    public function add($queryPart)
    {
        $this->queryString .= "\n" . $queryPart;
        return $this;
    }

    public function fetchAll()
    {
        return $this->result->fetchAll(\PDO::FETCH_ASSOC) ?? [];
    }
    
    public function executeQuery(string $sql, array $params = array())
    {
        try {
            $this->queryString = '';
            $statement = $this->pdoConn->prepare($sql);
            $statement->execute($params);
            $this->result = $statement;
    
        } catch (\PDOException $e) {
            \schema\model\Logger::log($e);
            throw new \Exception("SQL execution failed: " . $e->getMessage(), 0, $e);
        }
    }

    function validateUser($username, $password) {

      // Prepare the SQL statement
      $stmt = $this->pdoConn->prepare("SELECT * FROM users WHERE username = :username");
      $stmt->bindParam(':username', $username);

      // Execute the query
      $stmt->execute();
      
      // Fetch the user record
      $user = $stmt->fetch(\PDO::FETCH_ASSOC);

      // Verify the password
      if ($user && password_verify($password, $user['password'])) {
          return true; // Login successful
      } else {
          return false; // Login failed
      }
    }

}
?>