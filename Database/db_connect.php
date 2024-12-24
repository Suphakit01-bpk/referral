<?php
namespace Database;

class Database {
    private $conn = null;
    private $host = '172.29.10.98';
    private $port = '5432';
    private $dbname = 'referral';
    private $user = 'postgres';
    private $password = 'BPK9@support';
    private $message = '';
    
    public function connect() {
        try {
            $this->conn = @pg_connect("host=$this->host port=$this->port dbname=$this->dbname user=$this->user password=$this->password");
            
            if ($this->isConnected()) {
                $this->message = "Successfully connected to database";
                return $this->conn; // Return connection object instead of boolean
            } else {
                $error = pg_last_error();
                $this->message = "Failed to connect to database: " . ($error ? $error : "Unknown error");
                return false;
            }
        } catch (\Exception $e) {
            $this->message = "Connection error: " . $e->getMessage();
            return false;
        }
    }

    public function isConnected() {
        return $this->conn !== null && $this->conn !== false;
    }

    public function close() {
        if ($this->conn) {
            pg_close($this->conn);
        }
    }

    public function getMessage() {
        return $this->message;
    }

    public function getConnection() {
        return $this->conn;
    }

    public function query($sql, $params = []) {
        if (!$this->isConnected()) {
            $this->connect();
        }
        
        try {
            if (empty($params)) {
                return pg_query($this->conn, $sql);
            } else {
                return pg_query_params($this->conn, $sql, $params);
            }
        } catch (\Exception $e) {
            $this->message = "Query error: " . $e->getMessage();
            return false;
        }
    }
}
?>