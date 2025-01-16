<?php
// connecttodb.php

class Database {
    private $host = 'localhost';
    private $db_name = 'khatoonbar';
    private $username = 'root';
    private $password = '';
    public $conn;

    // Method to get the database connection
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->db_name}", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            throw new Exception("Database connection failed.");
        }

        return $this->conn;
    }
}
?>