<?php
namespace grp12\database;

include_once("dblogininfo.php");
class Database {

    private $db = null;
    private $connected = false;

    public function connect() {
        try {
            $this -> db = new \PDO(
                'mysql:host=localhost;dbname=database;charset=utf8',
                dbint\dbuser,
                dbint\dbpwd);
            $this -> connected = true;
        } catch(PDOException $ex) {
            echo("Error: ".$ex->getMessage());
        }
        return($this -> db);
    }

    public function disconnect() {
        $this -> db = null;
        $this -> connected = false;
    }

}

?>