<?php
/**
 * Database Class
 * Handles database connection and operations
 */
class Database {
    private $host;
    private $user;
    private $pass;
    private $dbname;
    private $conn;
    private static $instance = null;
    
    /**
     * Constructor - Sets database connection parameters
     */
    private function __construct() {
        // Get database configuration
        $this->host = DB_HOST;
        $this->user = DB_USER;
        $this->pass = DB_PASS;
        $this->dbname = DB_NAME;
        
        // Connect to database
        $this->connect();
    }
    
    /**
     * Get Database instance (Singleton pattern)
     * 
     * @return Database Database instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Connect to the database
     * 
     * @return mysqli|bool Connection object or false on failure
     */
    private function connect() {
        // Create connection
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
        
        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        
        // Set charset to utf8
        $this->conn->set_charset("utf8");
        
        return $this->conn;
    }
    
    /**
     * Get database connection
     * 
     * @return mysqli Database connection
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Prepare a statement
     * 
     * @param string $sql SQL query
     * @return mysqli_stmt Prepared statement
     */
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }
    
    /**
     * Execute a query
     * 
     * @param string $sql SQL query
     * @return mysqli_result|bool Query result
     */
    public function query($sql) {
        return $this->conn->query($sql);
    }
    
    /**
     * Get the last inserted ID
     * 
     * @return int Last inserted ID
     */
    public function lastInsertId() {
        return $this->conn->insert_id;
    }
    
    /**
     * Get the number of affected rows
     * 
     * @return int Number of affected rows
     */
    public function affectedRows() {
        return $this->conn->affected_rows;
    }
    
    /**
     * Escape a string
     * 
     * @param string $string String to escape
     * @return string Escaped string
     */
    public function escapeString($string) {
        return $this->conn->real_escape_string($string);
    }
    
    /**
     * Begin a transaction
     * 
     * @return bool True on success, false on failure
     */
    public function beginTransaction() {
        return $this->conn->begin_transaction();
    }
    
    /**
     * Commit a transaction
     * 
     * @return bool True on success, false on failure
     */
    public function commit() {
        return $this->conn->commit();
    }
    
    /**
     * Rollback a transaction
     * 
     * @return bool True on success, false on failure
     */
    public function rollback() {
        return $this->conn->rollback();
    }
    
    /**
     * Get error information
     * 
     * @return string Error information
     */
    public function error() {
        return $this->conn->error;
    }
    
    /**
     * Close the database connection
     * 
     * @return bool True on success, false on failure
     */
    public function close() {
        return $this->conn->close();
    }
    
    /**
     * Destructor - Close the database connection
     */
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>
