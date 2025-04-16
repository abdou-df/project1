<?php
/*
 * Database Connection & Functions using PDO
 * This file handles the connection to the database and provides query helpers.
 * Requires config/config.php to be included first for DB constants.
 */

// Database configuration (REMOVED - Now defined in config/config.php)

/**
 * Get database PDO connection (Singleton pattern)
 * @return PDO|null Database connection object or null on failure
 */
function getDbConnection() {
    static $conn = null;

    if ($conn === null) {
        // Database connection details from config
        $host = DB_HOST;
        $dbname = DB_NAME;
        $username = DB_USER;
        $password = DB_PASS;
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Crucial for catching SQL errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $conn = new PDO($dsn, $username, $password, $options);
        } catch (\PDOException $e) {
            // Log the error securely instead of exposing details
            error_log("Database Connection Error: " . $e->getMessage());
            // Return null or throw a custom exception for the application to handle
            // For debugging, you might temporarily echo the error:
            // echo "Connection failed: " . $e->getMessage();
            return null; // Indicate connection failure
        }
    }

    return $conn;
}

/**
 * Execute a query and return results (fetchAll)
 * @param string $sql SQL query with placeholders (?)
 * @param array $params Parameters for prepared statement
 * @return array|false Array of results or false on failure
 */
function dbQuery($sql, $params = []) {
    $pdo = getDbConnection();
    if ($pdo === null) {
        return false; // Connection failed
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        // Check if the query was a SELECT statement (or similar that returns rows)
        // columnCount() is a way to check if the statement produced a result set
        if ($stmt->columnCount() > 0) {
             return $stmt->fetchAll();
        } else {
            // For INSERT, UPDATE, DELETE, return status info
            return [
                 'affected_rows' => $stmt->rowCount(),
                 'insert_id' => $pdo->lastInsertId() // Note: May not be reliable for all drivers/cases
            ];
        }
    } catch (\PDOException $e) {
        error_log("Database Query Error: " . $e->getMessage() . " | SQL: " . $sql);
        return false;
    }
}

/**
 * Execute a query and return a single row (fetch)
 * @param string $sql SQL query with placeholders (?)
 * @param array $params Parameters for prepared statement
 * @return array|false Single row associative array or false on failure/no result
 */
function dbQuerySingle($sql, $params = []) {
     $pdo = getDbConnection();
    if ($pdo === null) {
        return false; // Connection failed
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(); // Returns false if no rows found
    } catch (\PDOException $e) {
        error_log("Database Query Single Error: " . $e->getMessage() . " | SQL: " . $sql);
        return false;
    }
}

/**
 * Execute a query and return a single column value from the first row (fetchColumn)
 * @param string $sql SQL query with placeholders (?)
 * @param array $params Parameters for prepared statement
 * @param int $columnIndex (optional) The 0-indexed column number to fetch
 * @return mixed|false The value of the column or false on failure/no result
 */
function dbQueryValue($sql, $params = [], $columnIndex = 0) {
    $pdo = getDbConnection();
    if ($pdo === null) {
        return false; // Connection failed
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn($columnIndex); // Returns false if no rows/column found
    } catch (\PDOException $e) {
        error_log("Database Query Value Error: " . $e->getMessage() . " | SQL: " . $sql);
        return false;
    }
}

/**
 * Check if a record exists
 * @param string $table Table name
 * @param string $column Column name
 * @param mixed $value Value to check
 * @return bool True if exists, false otherwise
 */
function recordExists($table, $column, $value) {
    // Use backticks for table and column names for safety
    $sql = "SELECT 1 FROM `" . $table . "` WHERE `" . $column . "` = ? LIMIT 1"; 
    $result = dbQueryValue($sql, [$value]); 
    // dbQueryValue returns the value (1) if found, or false if not found/error
    return ($result !== false); 
}

/**
 * Insert a record into a table
 * @param string $table Table name
 * @param array $data Associative array of column => value
 * @return string|false Insert ID (as string) on success, false on failure
 */
function insertRecord($table, $data) {
    $pdo = getDbConnection();
    if ($pdo === null || empty($data)) {
        return false;
    }

    $columns = array_keys($data);
    $placeholders = array_fill(0, count($columns), '?');
    
    // Use backticks for table and column names
    $sql = "INSERT INTO `" . $table . "` (`" . implode("`, `", $columns) . "`) VALUES (" . implode(', ', $placeholders) . ")";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($data));
        return $pdo->lastInsertId(); // Returns the ID of the last inserted row
    } catch (\PDOException $e) {
        error_log("Database Insert Error: " . $e->getMessage() . " | SQL: " . $sql);
        return false;
    }
}

/**
 * Update a record in a table
 * @param string $table Table name
 * @param array $data Associative array of column => value to update
 * @param array $where Associative array for WHERE clause (e.g., ['id' => 1])
 * @return int|false Number of affected rows on success, false on failure
 */
function updateRecord($table, $data, $where) {
    $pdo = getDbConnection();
    if ($pdo === null || empty($data) || empty($where)) {
        return false;
    }

    $setParts = [];
    $whereParts = [];
    $params = [];

    // Prepare SET part
    foreach ($data as $column => $value) {
        $setParts[] = "`" . $column . "` = ?";
        $params[] = $value;
    }

    // Prepare WHERE part
    foreach ($where as $column => $value) {
        $whereParts[] = "`" . $column . "` = ?";
        $params[] = $value;
    }
    
    $sql = "UPDATE `" . $table . "` SET " . implode(', ', $setParts) . " WHERE " . implode(' AND ', $whereParts);
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount(); // Returns the number of affected rows
    } catch (\PDOException $e) {
        error_log("Database Update Error: " . $e->getMessage() . " | SQL: " . $sql);
        return false;
    }
}

/**
 * Delete records from a table
 * @param string $table Table name
 * @param array $where Associative array for WHERE clause (e.g., ['id' => 1])
 * @return int|bool Number of affected rows on success, false on failure
 */
function deleteRecord($table, $where) {
     $pdo = getDbConnection();
    if ($pdo === null || empty($where)) {
        return false;
    }

    $whereParts = [];
    $params = [];

    // Prepare WHERE part
    foreach ($where as $column => $value) {
        $whereParts[] = "`" . $column . "` = ?";
        $params[] = $value;
    }

    $sql = "DELETE FROM `" . $table . "` WHERE " . implode(' AND ', $whereParts);
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount(); // Returns the number of affected rows
    } catch (\PDOException $e) {
        error_log("Database Delete Error: " . $e->getMessage() . " | SQL: " . $sql);
        return false;
    }
}
