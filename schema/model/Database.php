<?php
namespace schema\model;

class Database
{
    private $sQueryString = '';
    private $oResult;
    private $pdoConn;

    public function __construct($aConfig)
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
        $this->conn = null;
        echo "Соединение с базой данных закрыто<br>";
    }

    public function add($sQueryPart)
    {
        $this->sQueryString .= "\n" . $sQueryPart;
        return $this;
    }

    public function fetchAll()
    {
        $aResult = $this->oResult->fetchAll(\PDO::FETCH_ASSOC);
        return false === $aResult ? array() : $aResult;
    }

    public function execute(array $aParams = array())
    {
        $this->oResult = $this->executeQuery($this->sQueryString, $aParams);
        return $this;
    }

    private function executeQuery($sSql, array $aParams = array())
    {
        try {
            $this->cleanQuery();
            $oStatement = $this->pdoConn->prepare($this->replaceQuestionMarks($sSql, $aParams));

            $aPsParams = array();
            $i = 1;
            foreach ($aParams as $sParam) {
                $aPsParams[':' . $i] = $sParam;
                $i++;
            }

            $oStatement->execute($aPsParams);

            $this->oResult = $oStatement;

            return $this->oResult;
        } catch (\PDOException $e) {
            \schema\model\Logger::log($e);
            throw new \Exception($sSql, 0, $e);
        }
    }

    public function cleanQuery()
    {
        $this->sQueryString = '';
        return $this;
    }

    public function executeSql($sql)
    {
        try {
            $statement = $this->pdoConn->prepare($sql);
            $statement->execute();
            return $statement;
        } catch (\PDOException $e) {
            \schema\model\Logger::log($e);
            throw new \Exception("SQL execution failed: " . $e->getMessage(), 0, $e);
        }
    }

    public function replaceQuestionMarks($sSql, array $aParams = array())
    {
        for ($i = 0; $i < count($aParams); $i++) {
            $sSql = preg_replace('/\?/', sprintf(':%s', $i + 1), $sSql, 1);
        }
        return $sSql;
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
