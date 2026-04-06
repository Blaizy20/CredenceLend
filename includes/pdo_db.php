<?php
class Database {
    private static $instance = null;
    private $pdo = null;
    
    private $db_host;
    private $db_user;
    private $db_pass;
    private $db_name;
    private $db_port;
    
    private function __construct() {
        $this->db_host = getenv('MYSQLHOST')     ?: 'localhost';
        $this->db_user = getenv('MYSQLUSER')     ?: 'root';
        $this->db_pass = getenv('MYSQLPASSWORD') ?: '';
        $this->db_name = getenv('MYSQLDATABASE') ?: 'loan_management';
        $this->db_port = (int)(getenv('MYSQLPORT') ?: 3306);
        $this->connect();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function connect() {
        try {
            $dsn = "mysql:host={$this->db_host};port={$this->db_port};dbname={$this->db_name};charset=utf8mb4";
            $this->pdo = new PDO($dsn, $this->db_user, $this->db_pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false,
            ]);
            $this->pdo->exec("SET SESSION sql_mode='STRICT_TRANS_TABLES'");
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Unable to connect to database. Please contact support.");
        }
    }
    
    public function getPDO() { return $this->pdo; }
    
    public function prepare($sql) {
        try {
            return $this->pdo->prepare($sql);
        } catch (PDOException $e) {
            error_log("Prepare Error: " . $e->getMessage());
            throw new Exception("Database preparation error");
        }
    }
    
    public function execute($stmt, $params = []) {
        try {
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Execution Error: " . $e->getMessage());
            throw new Exception("Database execution error");
        }
    }
    
    public function fetchOne($stmt) { return $stmt->fetch(PDO::FETCH_ASSOC); }
    public function fetchAll($stmt) { return $stmt->fetchAll(PDO::FETCH_ASSOC); }
    public function lastInsertId() { return $this->pdo->lastInsertId(); }
    public function beginTransaction() { return $this->pdo->beginTransaction(); }
    public function commit() { return $this->pdo->commit(); }
    public function rollback() { return $this->pdo->rollback(); }
    public function inTransaction() { return $this->pdo->inTransaction(); }
    
    public function queryOne($sql, $params = []) {
        $stmt = $this->prepare($sql);
        $this->execute($stmt, $params);
        return $this->fetchOne($stmt);
    }
    
    public function queryAll($sql, $params = []) {
        $stmt = $this->prepare($sql);
        $this->execute($stmt, $params);
        return $this->fetchAll($stmt);
    }
    
    public function exec($sql, $params = []) {
        $stmt = $this->prepare($sql);
        return $this->execute($stmt, $params);
    }
}
?>
