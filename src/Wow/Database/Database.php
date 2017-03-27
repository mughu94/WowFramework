<?php

    namespace Wow\Database;

    use Exception;
    use PDO;
    use PDOException;
    use PDOStatement;
    use Wow;

    /**
     * Class Database
     * @package Wow\Database
     * Thanks indieteq for class, see the original: https://github.com/indieteq/indieteq-php-my-sql-pdo-database-class
     */
    class Database {
        /**
         * @var $instance Database;
         */
        protected static $instances = array();

        /**
         * @var $pdo PDO
         */
        private $pdo;

        /**
         * @var $sQuery PDOStatement
         */
        private $sQuery;

        /**
         * @var array $settings Settings
         */
        private $settings;

        /**
         * @var bool $bConnected Is Datadabase Connected
         */
        private $bConnected = FALSE;

        /**
         * @var array $parameters
         */
        private $parameters = array();


        /**
         * @return Database Instance of Database
         */
        public static function getInstance($instanceName = "DefaultConnection") {
            if(!isset(self::$instances[$instanceName])) {
                self::$instances[$instanceName] = new Database($instanceName);
            }

            return self::$instances[$instanceName];
        }

        /**
         *   Default Constructor
         *
         *    1. Connect to database.
         *    2. Creates the parameter array.
         */
        public function __construct($connectionName = "DefaultConnection") {
            // Get database properties from Config File
            if(Wow::has("database/" . $connectionName)) {
                $this->settings = Wow::get("database/" . $connectionName);
            } else {
                throw new Exception("Database properties (named: " . $connectionName . ") could not found in Config file!");
            }
        }

        /**
         *    This method makes connection to the database.
         *
         *    1. Reads the database settings from a ini file.
         *    2. Puts  the ini content into the settings array.
         *    3. Tries to connect to the database.
         *    4. If connection failed, exception is displayed and a log file gets created.
         */
        private function Connect() {
            try {
                switch($this->settings["driver"]) {
                    case "mysql":
                        $dsn = "mysql:host=" . $this->settings["host"];
                        if(!empty($this->settings["port"])) {
                            $dsn .= "; port=" . $this->settings["port"];
                        }
                        $dsn .= "; dbname=" . $this->settings["name"] . "; charset=utf8";
                        break;
                    case "sqlsrv":
                        $dsn = "sqlsrv:server=" . $this->settings["host"];
                        if(!empty($this->settings["port"])) {
                            $dsn .= "; port=" . $this->settings["port"];
                        }
                        $dsn .= "; Database=" . $this->settings["name"];
                        break;
                    case "pgsql":
                        $dsn = "pgsql:host=" . $this->settings["host"];
                        if(!empty($this->settings["port"])) {
                            $dsn .= "; port=" . $this->settings["port"];
                        }
                        $dsn .= "; dbname=" . $this->settings["name"];
                        break;
                    default:
                        $dsn = $this->settings["connection"] . ":host=" . $this->settings["host"];
                        if(!empty($this->settings["port"])) {
                            $dsn .= "; port=" . $this->settings["port"];
                        }
                        $dsn .= "; dbname=" . $this->settings["name"];
                        break;
                }
                $this->pdo = new PDO($dsn, $this->settings["user"], $this->settings["password"]);

                /**
                 * We can now track any exceptions on Fatal error.
                 */
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                /**
                 * Disable emulation of prepared statements, use REAL prepared statements instead. Works for mysql. Does not work in sqlsrv. Not tested others.
                 * TODO test other db drivers.
                 */
                switch($this->settings["driver"]) {
                    case "mysql":
                        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
                        break;
                }

                /**
                 * Connection succeeded, set the boolean to true.
                 */
                $this->bConnected = TRUE;
            } catch(PDOException $e) {
                throw new Exception("Error in Database Connection. Could not connect to Database! Please check your connection parameters.");
            }
        }

        /**
         *   You can use this little method if you want to close the PDO connection
         *
         */
        public function CloseConnection() {
            /**
             * Set the PDO object to null to close the connection
             * http://www.php.net/manual/en/pdo.connections.php
             */
            $this->pdo        = NULL;
            $this->bConnected = FALSE;
        }

        /**
         *    Every method which needs to execute a SQL query uses this method.
         *
         *    1. If not connected, connect to the database.
         *    2. Prepare Query.
         *    3. Parameterize Query.
         *    4. Execute Query.
         *    5. On exception : Write Exception into the log + SQL query.
         *    6. Reset the Parameters.
         */
        private function Init($query, $parameters = "") {
            // Connect to database
            if(!$this->bConnected) {
                $this->Connect();
            }
            try {
                // Prepare query
                $queryOptions = array();
                if($this->settings["driver"] == "sqlsrv") {
                    $queryOptions = array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL);
                }
                $this->sQuery = $this->pdo->prepare($query, $queryOptions);

                // Add parameters to the parameter array
                $this->bindMore($parameters);


                // Bind parameters
                if(!empty($this->parameters)) {
                    foreach($this->parameters as $param => $value) {

                        if(is_int($value[1])) {
                            $type = PDO::PARAM_INT;
                        } elseif(is_bool($value[1])) {
                            $type = PDO::PARAM_BOOL;
                        } elseif(is_null($value[1])) {
                            $type = PDO::PARAM_NULL;
                        } else {
                            $type = PDO::PARAM_STR;
                        }

                        // Add type when binding the values to the column
                        $this->sQuery->bindValue($value[0], $value[1], $type);
                    }
                }

                /**
                 * Execute SQL
                 */
                $this->sQuery->execute();
            } catch(PDOException $e) {
                /**
                 * Throw Exception
                 */
                throw new Exception("An error ocurred in your Sql Query. Please check your sql query.");
            }

            /**
             * Reset the parameters
             */
            $this->parameters = array();
        }

        /**
         * @void
         *
         *    Add the parameter to the parameter array
         *
         * @param string $para
         * @param string $value
         */
        public function bind($para, $value) {
            $this->parameters[sizeof($this->parameters)] = array(
                ":" . $para,
                $value
            );
        }

        /**
         * @void
         *
         *    Add more parameters to the parameter array
         *
         * @param array $parray
         */
        public function bindMore($parray) {
            if(is_array($parray)) {
                $columns = array_keys($parray);
                foreach($columns as $i => &$column) {
                    $this->bind($column, $parray[$column]);
                }
            }
        }

        /**
         *  If query contains a resultset returns it, otherwise returns affected rowCount
         *
         * @param  string $query
         * @param  array  $params
         * @param  int    $fetchmode
         *
         * @return mixed
         */
        public function query($query, $params = NULL, $fetchmode = PDO::FETCH_ASSOC) {
            $query = trim(str_replace("\r", " ", $query));

            $this->Init($query, $params);

            //If there is a resultset return the results, Otherwise return affected rowCount after query
            return $this->sQuery->columnCount() > 0 ? $this->sQuery->fetchAll($fetchmode) : $this->sQuery->rowCount();
        }

        /**
         *  Returns the last inserted id.
         * @return string
         */
        public function lastInsertId() {
            return $this->pdo->lastInsertId();
        }

        /**
         * Starts the transaction
         * @return boolean, true on success or false on failure
         */
        public function beginTransaction() {
            return $this->pdo->beginTransaction();
        }

        /**
         *  Execute Transaction
         * @return boolean, true on success or false on failure
         */
        public function executeTransaction() {
            return $this->pdo->commit();
        }

        /**
         *  Rollback of Transaction
         * @return boolean, true on success or false on failure
         */
        public function rollBack() {
            return $this->pdo->rollBack();
        }

        /**
         *    Returns an array which represents a column from the result set
         *
         * @param  string $query
         * @param  array  $params
         *
         * @return array
         */
        public function column($query, $params = NULL) {
            $this->Init($query, $params);
            $Columns = $this->sQuery->fetchAll(PDO::FETCH_NUM);

            $column = array();

            foreach($Columns as $cells) {
                $column[] = $cells[0];
            }

            return $column;

        }

        /**
         *    Returns an array which represents a row from the result set
         *
         * @param  string $query
         * @param  array  $params
         * @param  int    $fetchmode
         *
         * @return array
         */
        public function row($query, $params = NULL, $fetchmode = PDO::FETCH_ASSOC) {
            $this->Init($query, $params);
            $result = $this->sQuery->fetch($fetchmode);
            $this->sQuery->closeCursor(); // Frees up the connection to the server so that other SQL statements may be issued,

            return $result;
        }

        /**
         *    Returns the value of one single field/column
         *
         * @param  string $query
         * @param  array  $params
         *
         * @return string
         */
        public function single($query, $params = NULL) {
            $this->Init($query, $params);
            $result = $this->sQuery->fetchColumn();
            $this->sQuery->closeCursor(); // Frees up the connection to the server so that other SQL statements may be issued

            return $result;
        }
    }