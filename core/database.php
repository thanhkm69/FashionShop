<?php
require_once __DIR__ . "/config.php";
class Database
{
    private $servername;
    private $username;
    private $password;
    private $dbname;
    private $connection;

    public function __construct()
    {
        $this->servername = HOST;
        $this->username = USERNAME;
        $this->password = PASSWORD;
        $this->dbname = DBNAME;
    }

    public function connection()
    {
        try {
            $this->connection = new PDO("mysql:host=$this->servername;dbname=$this->dbname;charset=utf8mb4", $this->username, $this->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // echo "Kết nối thành công";
            return $this->connection;
        } catch (PDOException $e) {
            echo "Lỗi kết nối: " . $e->getMessage() . "\n";
            die();
        }
    }
}
