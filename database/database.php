<?php

/* ---------------------------------------------------------------------------
 * filename    : database.php
 * author      : Chris Walker
 * description : This class enables PHP to connect to MySQL using 
 *               PDO (PHP Data Objects). 
 * important   : This file contains passwords!
 *               Do not put real version of this file in a public GitHub repo!
 * ---------------------------------------------------------------------------
 */

class Database {

    // declare and initialize variables for connect() function
    private static $dbName         = '355final'; 
    private static $dbHost         = 'localhost';
    private static $dbUsername     = 'root';
    private static $dbUserPassword = '';

    // declare and initialize PDO instance variable: $connection
    private static $connection  = null;

    // method: __construct()
    public function __construct() {
        exit('No constructor required for class: Database');
    } 

    // method: connect()
    public static function connect() {
        if (null == self::$connection) {      
            try {
                self::$connection = new PDO(
                    "mysql:host=".self::$dbHost.";"."dbname=".self::$dbName.";charset=utf8mb4", 
                    self::$dbUsername, 
                    self::$dbUserPassword
                );
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }
        return self::$connection;
    } 

    // method: disconnect()
    public static function disconnect() {
        self::$connection = null;
    } 

} // end class: Database

?>
