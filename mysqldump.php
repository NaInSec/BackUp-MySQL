<?php

class MySQLDump {
    public $tables = [];
    public $connected = false;
    public $output;
    public $droptableifexists = false;
    public $mysql_error;
    public function __construct() {}
    public function connect($host, $user, $pass, $db) {
        $conn = @mysqli_connect($host, $user, $pass, $db);
        if (!$conn) { 
            $this->mysql_error = mysqli_connect_error(); 
            return false; 
        }
        $this->connected = true;
        return true;
    }

    public function list_tables() {
        if (!$this->connected) { 
            return false; 
        }
        $this->tables = [];
        $sql = mysqli_query($this->conn, "SHOW TABLES");
        while ($row = mysqli_fetch_array($sql)) {
            array_push($this->tables, $row[0]);
        }
        return true;
    }

    public function list_values($tablename) {
        $sql = mysqli_query($this->conn, "SELECT * FROM $tablename");
        $this->output .= "\n\n-- Dumping data for table: $tablename\n\n";
        while ($row = mysqli_fetch_array($sql)) {
            $this->output .= "INSERT INTO `$tablename` VALUES(";
            $values = array_map(function($value) {
                return is_numeric($value) ? $value : "'" . addslashes($value) . "'";
            }, $row);
            $this->output .= implode(", ", $values) . ");\n";
        }
    }

    public function dump_table($tablename) {
        $this->output = "";
        $this->get_table_structure($tablename);
        $this->list_values($tablename);
    }

    public function get_table_structure($tablename) {
        $this->output .= "\n\n-- Dumping structure for table: $tablename\n\n";
        if ($this->droptableifexists) { 
            $this->output .= "DROP TABLE IF EXISTS `$tablename`;\nCREATE TABLE `$tablename` (\n"; 
        } else { 
            $this->output .= "CREATE TABLE `$tablename` (\n"; 
        }
        $sql = mysqli_query($this->conn, "DESCRIBE $tablename");
        $primary = null;
        while ($row = mysqli_fetch_array($sql)) {
            $name = $row[0];
            $type = $row[1];
            $null = ($row[2] == 'YES') ? 'NULL' : 'NOT NULL';
            $extra = ($row[5] !== "") ? $row[5] . ' ' : '';
            if ($row[3] == "PRI") { 
                $primary = $name; 
            }
            $this->output .= "  `$name` $type $null $extra,\n";
        }
        $this->output .= "  PRIMARY KEY  (`$primary`)\n);\n";
    }
}
?>
