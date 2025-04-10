<?php
/*
 * Database Connection
 * This file handles the connection to the database
 

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'garage_db');


 * Get database connection
 * @return mysqli Database connection object
 */
function getDbConnection() {
    static $conn;
    
    // If connection already exists, return it
    if ($conn === null) {
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable error reporting
            
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            // Check connection
            if ($conn->connect_error) {
                error_log("Connection failed: " . $conn->connect_error);
                return false;
            }
            
            // Set charset to utf8mb4
            if (!$conn->set_charset("utf8mb4")) {
                error_log("Error setting charset: " . $conn->error);
            }
            
            error_log("Database connection successful");
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            return false;
        }
    }
    
    return $conn;
}

/**
 * Execute a query and return result
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return mixed Result of the query or false on failure
 */
function dbQuery($sql, $params = []) {
    $conn = getDbConnection();
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        return false;
    }
    
    // Bind parameters if they exist
    if (!empty($params)) {
        $types = '';
        $bindParams = [];
        
        // Get parameter types
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
            $bindParams[] = $param;
        }
        
        // Add types to the beginning of the array
        array_unshift($bindParams, $types);
        
        // Call bind_param with reference to each parameter
        $tmp = [];
        foreach ($bindParams as $key => $value) {
            $tmp[$key] = &$bindParams[$key];
        }
        
        call_user_func_array([$stmt, 'bind_param'], $tmp);
    }
    
    // Execute the statement
    if (!$stmt->execute()) {
        return false;
    }
    
    // Get result if available
    $result = $stmt->get_result();
    
    if ($result === false) {
        // If no result set (INSERT, UPDATE, DELETE)
        if ($stmt->affected_rows >= 0) {
            $affectedRows = $stmt->affected_rows;
            $insertId = $stmt->insert_id;
            $stmt->close();
            
            return [
                'affected_rows' => $affectedRows,
                'insert_id' => $insertId
            ];
        }
        
        $stmt->close();
        return false;
    }
    
    // Fetch all results
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return $rows;
}

/**
 * Execute a query and return a single row
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return array|bool Single row or false on failure
 */
function dbQuerySingle($sql, $params = []) {
    $result = dbQuery($sql, $params);
    
    if ($result === false || empty($result)) {
        return false;
    }
    
    return $result[0];
}

/**
 * Check if a record exists
 * @param string $table Table name
 * @param string $column Column name
 * @param mixed $value Value to check
 * @return bool True if exists, false otherwise
 */
function recordExists($table, $column, $value) {
    $sql = "SELECT 1 FROM `$table` WHERE `$column` = ? LIMIT 1";
    $result = dbQuery($sql, [$value]);
    
    return !empty($result);
}

/**
 * Insert a record into a table
 * @param string $table Table name
 * @param array $data Associative array of column => value
 * @return int|bool Insert ID on success, false on failure
 */
function insertRecord($table, $data) {
    $columns = array_keys($data);
    $values = array_values($data);
    
    $placeholders = array_fill(0, count($values), '?');
    
    $sql = "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $placeholders) . ")";
    
    $result = dbQuery($sql, $values);
    
    if ($result === false) {
        return false;
    }
    
    return $result['insert_id'];
}

/**
 * Update a record in a table
 * @param string $table Table name
 * @param array $data Associative array of column => value
 * @param string $whereColumn Column name for WHERE clause
 * @param mixed $whereValue Value for WHERE clause
 * @return int|bool Number of affected rows on success, false on failure
 */
function updateRecord($table, $data, $whereColumn, $whereValue) {
    $columns = array_keys($data);
    $values = array_values($data);
    
    $set = [];
    foreach ($columns as $column) {
        $set[] = "`$column` = ?";
    }
    
    $sql = "UPDATE `$table` SET " . implode(', ', $set) . " WHERE `$whereColumn` = ?";
    
    // Add where value to values array
    $values[] = $whereValue;
    
    $result = dbQuery($sql, $values);
    
    if ($result === false) {
        return false;
    }
    
    return $result['affected_rows'];
}

/**
 * Delete a record from a table
 * @param string $table Table name
 * @param string $whereColumn Column name for WHERE clause
 * @param mixed $whereValue Value for WHERE clause
 * @return int|bool Number of affected rows on success, false on failure
 */
function deleteRecord($table, $whereColumn, $whereValue) {
    $sql = "DELETE FROM `$table` WHERE `$whereColumn` = ?";
    
    $result = dbQuery($sql, [$whereValue]);
    
    if ($result === false) {
        return false;
    }
    
    return $result['affected_rows'];
}
